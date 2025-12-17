<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::orderBy('order')->get();
        return response()->json($images);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|in:rooms,dining,facilities,events,exterior',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
                'order' => 'sometimes|integer',
            ]);

            // Handle file upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('gallery', 'public');
                $validated['image_path'] = $imagePath;
            }

            unset($validated['image']);
            $image = GalleryImage::create($validated);

            return response()->json($image, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $image = GalleryImage::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'sometimes|in:rooms,dining,facilities,events,exterior',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120',
            'order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle file upload if new image provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
            $imagePath = $request->file('image')->store('gallery', 'public');
            $validated['image_path'] = $imagePath;
        }

        unset($validated['image']);
        $image->update($validated);

        return response()->json($image);
    }

    public function destroy(int $id)
    {
        $image = GalleryImage::findOrFail($id);

        // Delete image file
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json(['message' => 'Gallery image deleted successfully']);
    }
}
