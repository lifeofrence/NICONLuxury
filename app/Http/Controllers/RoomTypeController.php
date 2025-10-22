<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
    public function index(Request $request)
    {
        $checkIn = $request->query('check_in_date');
        $checkOut = $request->query('check_out_date');
        $types = RoomType::query()->withCount('rooms', 'images')->get();

        $result = $types->map(function (RoomType $type) use ($checkIn, $checkOut) {
            $count = Booking::query()
                ->where('room_type_id', $type->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->when($checkIn && $checkOut, function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
                }, function ($q) {
                    $today = now()->toDateString();
                    $tomorrow = now()->addDay()->toDateString();
                    $q->where('check_in_date', '<', $tomorrow)
                      ->where('check_out_date', '>', $today);
                })
                ->count();

            $available = max(0, $type->total_rooms - $count);

            return [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'base_price' => $type->base_price,
                // 'total_rooms' => $type->total_rooms,
                'amenities' => $type->amenities,
                'rooms_count' => $type->rooms_count,
                // 'available' => $available,
                     'images' => $type->images,
            ];
        });

        return response()->json($result);
    }

    public function listRoom(Request $requ)
    {
        $types = RoomType::with(['rooms', 'images'])->get();

        $result = $types->map(function (RoomType $type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'base_price' => $type->base_price,
                'amenities' => $type->amenities,
                'rooms' => $type->rooms,
                'images' => $type->images,
            ];
        });

        return response()->json($result);
    }

    public function show(Request $request, int $id)
    {
        $type = RoomType::with('rooms', 'images')->findOrFail($id);
        $checkIn = $request->query('check_in_date');
        $checkOut = $request->query('check_out_date');

        $count = Booking::query()
            ->where('room_type_id', $type->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($checkIn && $checkOut, function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in_date', '<', $checkOut)
                  ->where('check_out_date', '>', $checkIn);
            }, function ($q) {
                $today = now()->toDateString();
                $tomorrow = now()->addDay()->toDateString();
                $q->where('check_in_date', '<', $tomorrow)
                  ->where('check_out_date', '>', $today);
            })
            ->count();

        $available = max(0, $type->total_rooms - $count);

        return response()->json([
            'id' => $type->id,
            'name' => $type->name,
            'description' => $type->description,
            'base_price' => $type->base_price,
            'total_rooms' => $type->total_rooms,
            'amenities' => $type->amenities,
            'rooms' => $type->rooms,
            'available' => $available,
            'images' => $type->images,

        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'total_rooms' => 'required|integer|min:0',
            'amenities' => 'nullable|array',
        ]);

        $type = RoomType::create($validated);
        return response()->json($type, 201);
    }

    public function updateRoomType(Request $request, int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $type = RoomType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'base_price' => 'sometimes|numeric|min:0',
                'total_rooms' => 'sometimes|integer|min:0',
                'amenities' => 'nullable|array',
            ]);

            if (array_key_exists('total_rooms', $validated)) {
                $linkedRoomsCount = Room::where('room_type_id', $id)->count();
                if ($validated['total_rooms'] < $linkedRoomsCount) {
                    return response()->json([
                        'message' => 'total_rooms cannot be less than the number of physical rooms linked to this room type.',
                    ], 422);
                }
            }

            $type->update($validated);
            return response()->json($type);
        });
    }


    public function availability(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);
        $checkIn = $validated['check_in_date'];
        $checkOut = $validated['check_out_date'];

        // Precompute physically available rooms per type and eager-load images
        $types = RoomType::query()
            ->withCount(['rooms' => function ($q) {
                $q->where('status', 'Available');
            }])
            ->with('images')
            ->get();

        $availableTypes = [];

        foreach ($types as $type) {
            $physicallyAvailable = $type->rooms_count;

            // Count bookings that overlap the requested period
            $occupiedBookings = Booking::query()
                ->where('room_type_id', $type->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_out_date', '>', $checkIn)
                ->where('check_in_date', '<', $checkOut)
                ->count();

            $availableCount = max(0, $physicallyAvailable - $occupiedBookings);

            if ($availableCount > 0) {
                $availableTypes[] = [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'base_price' => $type->base_price,
                    'amenities' => $type->amenities,
                    'available_rooms' => $availableCount,
                    'period' => [
                        'check_in_date' => $checkIn,
                        'check_out_date' => $checkOut,
                    ],
                    'images' => $type->images,
                ];
            }
        }

        if (empty($availableTypes)) {
            return response()->json(['message' => 'No rooms available for the selected dates.'], 404);
        }

        return response()->json($availableTypes);
    }

}