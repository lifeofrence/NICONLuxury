<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\RoomTypeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RoomTypeImageController extends Controller
{
    protected function cloudName(): string
    {
        return (string) config('services.cloudinary.cloud_name');
    }

    protected function apiKey(): string
    {
        return (string) config('services.cloudinary.api_key');
    }

    protected function apiSecret(): string
    {
        return (string) config('services.cloudinary.api_secret');
    }

    protected function defaultFolder(): string
    {
        return (string) config('services.cloudinary.folder', 'room_types');
    }

    protected function sign(array $params): string
    {
        ksort($params);
        $toSign = [];
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') { continue; }
            $toSign[] = $key.'='.$value;
        }
        $toSignStr = implode('&', $toSign);
        return sha1($toSignStr . $this->apiSecret());
    }

    private function normalizeUrl(?string $url): ?string
    {
        if ($url === null) { return null; }
        return trim($url, " \t\n\r\0\x0B`");
    }

    // Upload one or multiple images for a room type
    public function store(Request $request, int $roomTypeId)
    {
        $type = RoomType::findOrFail($roomTypeId);
        $validated = $request->validate([
            'images.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'sometimes|string|nullable',
            // 'is_primary' => 'sometimes|boolean',
        ]);
        $files = $request->file('images');
        if (!$files || count($files) === 0) {
            return response()->json(['message' => 'No images provided'], 422);
        }

        $uploaded = [];
        foreach ($files as $file) {
            $timestamp = time();
            $folder = $this->defaultFolder().'/'.$type->id;
            $publicId = null; // Let Cloudinary generate; optionally set: $folder.'/'.Str::random(16)
            $params = [
                'timestamp' => $timestamp,
                'folder' => $folder,
                // 'public_id' => $publicId,
                // 'overwrite' => true,
            ];
            $signature = $this->sign($params);

            $response = Http::asMultipart()
                ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                ->post('https://api.cloudinary.com/v1_1/'.$this->cloudName().'/image/upload', array_merge($params, [
                    'api_key' => $this->apiKey(),
                    'signature' => $signature,
                ]));

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Cloudinary upload failed',
                    'error' => $response->json(),
                ], 502);
            }

            $json = $response->json();

            $image = RoomTypeImage::create([
                'room_type_id' => $type->id,
                'public_id' => $json['public_id'] ?? ($publicId ?? ''),
                'url' => $this->normalizeUrl($json['url'] ?? null),
                'secure_url' => $this->normalizeUrl($json['secure_url'] ?? null),
                'format' => $json['format'] ?? null,
                'width' => $json['width'] ?? null,
                'height' => $json['height'] ?? null,
                'bytes' => $json['bytes'] ?? null,
                'caption' => $validated['caption'] ?? null,
                'sort_order' => 0,
                'is_primary' => (bool) ($validated['is_primary'] ?? false),
            ]);

            $uploaded[] = $image;
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploaded,
        ], 201);
    }

    // Update metadata or replace the image
    public function update(Request $request, int $roomTypeId, int $imageId)
    {
        $type = RoomType::findOrFail($roomTypeId);
        // Ensure the image belongs to this room type
        $image = RoomTypeImage::where('room_type_id', $type->id)
            ->where('id', $imageId)
            ->firstOrFail();

        $validated = $request->validate([
            'caption' => 'sometimes|string|nullable',
            'sort_order' => 'sometimes|integer|min:0',
            'is_primary' => 'sometimes|boolean',
            'image' => 'sometimes|file|mimes:jpg,jpeg,png,webp|max:5120',
            'file' => 'sometimes|file|mimes:jpg,jpeg,png,webp|max:5120',
            'image_url' => 'sometimes|url',
            'file_url' => 'sometimes|url',
            'new_public_id' => 'sometimes|string|nullable',
        ]);

        $hasImageFile = $request->hasFile('image');
        $hasFileFile = $request->hasFile('file');
        $remoteUrl = $request->input('image_url') ?? $request->input('file_url');

        // Fallback: accept URL provided in 'image' or 'file' fields when not sent as files
        if (!$hasImageFile && !$hasFileFile) {
            $imageInput = $request->input('image');
            $fileInput = $request->input('file');
            if (is_string($imageInput) && filter_var($imageInput, FILTER_VALIDATE_URL)) {
                $remoteUrl = $imageInput;
            } elseif (is_string($fileInput) && filter_var($fileInput, FILTER_VALIDATE_URL)) {
                $remoteUrl = $fileInput;
            }
        }

        $shouldReplace = $hasImageFile || $hasFileFile || !empty($remoteUrl);

        Log::info('RoomTypeImage update detection', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'has_image' => $hasImageFile,
            'has_file' => $hasFileFile,
            'remote_url' => $remoteUrl,
            'should_replace' => $shouldReplace,
        ]);

        if ($shouldReplace) {
            // Guard: ensure Cloudinary is configured
            if (empty($this->cloudName()) || empty($this->apiKey()) || empty($this->apiSecret())) {
                return response()->json([
                    'message' => 'Cloudinary not configured',
                ], 500);
            }
            $folderPrefix = $this->defaultFolder().'/'.$type->id.'/';
            $newPublicId = null;

            if (!empty($validated['new_public_id'])) {
                $newPublicId = trim($validated['new_public_id']);
                if (!str_starts_with($newPublicId, $folderPrefix)) {
                    $newPublicId = $folderPrefix . ltrim($newPublicId, '/');
                }
            } else {
                $newPublicId = $folderPrefix . Str::lower(Str::random(16));
            }

            // Try to delete the old asset to avoid cache issues, continue if it fails
            $adminUrl = 'https://api.cloudinary.com/v1_1/'.$this->cloudName().'/resources/image/upload/'.rawurlencode($image->public_id);
            $adminResponse = Http::withBasicAuth($this->apiKey(), $this->apiSecret())
                ->delete($adminUrl.'?invalidate=true');

            if (!$adminResponse->ok()) {
                $timestampDestroy = time();
                $destroyParams = [
                    'timestamp' => $timestampDestroy,
                    'public_id' => $image->public_id,
                    'invalidate' => true,
                ];
                $destroySignature = $this->sign($destroyParams);

                $uploadDestroy = Http::asForm()->post('https://api.cloudinary.com/v1_1/'.$this->cloudName().'/image/destroy', [
                    'api_key' => $this->apiKey(),
                    'signature' => $destroySignature,
                    'timestamp' => $timestampDestroy,
                    'public_id' => $image->public_id,
                    'invalidate' => true,
                ]);

                Log::warning('Cloudinary destroy during update replace', [
                    'status_admin' => $adminResponse->status(),
                    'admin_error' => $adminResponse->json() ?? $adminResponse->body(),
                    'status_upload' => $uploadDestroy->status(),
                    'upload_error' => $uploadDestroy->json() ?? $uploadDestroy->body(),
                ]);
            }

            // Upload new asset with the new public id
            $timestamp = time();
            $params = [
                'timestamp' => $timestamp,
                'public_id' => $newPublicId,
                'overwrite' => 1,
                'invalidate' => 1,
            ];
            $signature = $this->sign($params);

            if ($hasImageFile || $hasFileFile) {
                $file = $request->file('image') ?? $request->file('file');
                $response = Http::asMultipart()
                    ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                    ->post('https://api.cloudinary.com/v1_1/'.$this->cloudName().'/image/upload', array_merge($params, [
                        'api_key' => $this->apiKey(),
                        'signature' => $signature,
                    ]));
            } else {
                $response = Http::asForm()->post('https://api.cloudinary.com/v1_1/'.$this->cloudName().'/image/upload', array_merge($params, [
                    'api_key' => $this->apiKey(),
                    'signature' => $signature,
                    'file' => $remoteUrl,
                ]));
            }

            if ($response->failed()) {
                Log::error('Cloudinary upload (replace) failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'json' => $response->json(),
                ]);
                $err = $response->json();
                return response()->json([
                    'message' => 'Cloudinary upload (replace) failed',
                    'error' => ($err['error']['message'] ?? $err ?? $response->body()),
                ], 502);
            }

            $json = $response->json();
            $image->public_id = trim(($json['public_id'] ?? $newPublicId), " \t\n\r\0\x0B`");
            $image->url = $this->normalizeUrl($json['url'] ?? null);
            $image->secure_url = $this->normalizeUrl($json['secure_url'] ?? null);
            $image->format = $json['format'] ?? null;
            $image->width = $json['width'] ?? null;
            $image->height = $json['height'] ?? null;
            $image->bytes = $json['bytes'] ?? null;
        }

        // Update metadata fields
        if (array_key_exists('caption', $validated)) {
            $image->caption = $validated['caption'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $image->sort_order = $validated['sort_order'];
        }
        if (array_key_exists('is_primary', $validated)) {
            $image->is_primary = (bool) $validated['is_primary'];
        }

        $image->save();

        return response()->json($image);
    }


    // Delete the image both in Cloudinary and locally
    public function destroy(int $roomTypeId, int $imageId)
    {
        $type = RoomType::findOrFail($roomTypeId);
        $image = RoomTypeImage::where('room_type_id', $type->id)->findOrFail($imageId);

        // First try Admin API deletion with Basic Auth
        $adminUrl = 'https://api.cloudinary.com/v1_1/'.$this->cloudName().'/resources/image/upload/'.rawurlencode($image->public_id);
        $adminResponse = Http::withBasicAuth($this->apiKey(), $this->apiSecret())
            ->delete($adminUrl.'?invalidate=true');

        if ($adminResponse->ok()) {
            $image->delete();
            return response()->json(['message' => 'Image deleted']);
        }

        // Fallback: Upload API destroy with signed params
        $timestamp = time();
        $params = [
            'timestamp' => $timestamp,
            'public_id' => $image->public_id,
            'invalidate' => true,
        ];
        $signature = $this->sign($params);

        $uploadResponse = Http::asForm()->post('https://api.cloudinary.com/v1_1/'.$this->cloudName().'/image/destroy', [
            'api_key' => $this->apiKey(),
            'signature' => $signature,
            'timestamp' => $timestamp,
            'public_id' => $image->public_id,
            'invalidate' => true,
        ]);

        if ($uploadResponse->ok()) {
            $image->delete();
            return response()->json(['message' => 'Image deleted']);
        }

        return response()->json([
            'message' => 'Cloudinary destroy failed',
            'status_admin' => $adminResponse->status(),
            'admin_error' => $adminResponse->json() ?? $adminResponse->body(),
            'status_upload' => $uploadResponse->status(),
            'upload_error' => $uploadResponse->json() ?? $uploadResponse->body(),
        ], 502);
    }
}