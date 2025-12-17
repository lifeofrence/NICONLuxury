<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::where('is_active', true)
            ->orderBy('order')
            ->get();
        return response()->json($offers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'discount' => 'required|string|max:100',
            'description' => 'required|string',
            'valid_until' => 'required|string|max:100',
            'terms' => 'required|array',
            'terms.*' => 'string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'badge' => 'nullable|string|max:50',
            'badge_variant' => 'sometimes|in:default,destructive,secondary',
            'offer_type' => 'required|in:main,seasonal',
            'icon' => 'nullable|string|max:50',
            'order' => 'sometimes|integer',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('offers', 'public');
            $validated['image_path'] = $imagePath;
        }

        unset($validated['image']);
        $offer = Offer::create($validated);

        return response()->json($offer, 201);
    }

    public function update(Request $request, int $id)
    {
        $offer = Offer::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'discount' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'valid_until' => 'sometimes|string|max:100',
            'terms' => 'sometimes|array',
            'terms.*' => 'string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120',
            'badge' => 'nullable|string|max:50',
            'badge_variant' => 'sometimes|in:default,destructive,secondary',
            'offer_type' => 'sometimes|in:main,seasonal',
            'icon' => 'nullable|string|max:50',
            'order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle file upload if new image provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($offer->image_path) {
                Storage::disk('public')->delete($offer->image_path);
            }
            $imagePath = $request->file('image')->store('offers', 'public');
            $validated['image_path'] = $imagePath;
        }

        unset($validated['image']);
        $offer->update($validated);

        return response()->json($offer);
    }

    public function destroy(int $id)
    {
        $offer = Offer::findOrFail($id);

        // Delete image file
        if ($offer->image_path) {
            Storage::disk('public')->delete($offer->image_path);
        }

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully']);
    }
}
