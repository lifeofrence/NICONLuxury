<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingStoreRequest;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmed;
use App\Mail\NewBookingNotification;
use Illuminate\Support\Facades\Log;


class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::query()->with('roomType');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($checkIn = $request->query('check_in_date')) {
            $query->where('check_out_date', '>', $checkIn);
        }

        if ($checkOut = $request->query('check_out_date')) {
            $query->where('check_in_date', '<', $checkOut);
        }

        return response()->json($query->orderByDesc('id')->paginate(20));
    }

      public function show(int $id)
    {
        $booking = Booking::with(['roomType.images', 'room'])->findOrFail($id);
        return response()->json($booking);
    }

    public function store(BookingStoreRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $roomType = RoomType::findOrFail($data['room_type_id']);

            // Pick a random physically available room for this type
            $room = Room::query()
                ->where('room_type_id', $roomType->id)
                ->where('status', 'Available')
                ->inRandomOrder()
                ->first();

            if (!$room) {
                return response()->json([
                    'message' => 'No available rooms for selected room type.',
                ], 422);
            }

            $nights = (new \DateTime($data['check_in_date']))->diff(new \DateTime($data['check_out_date']))->days;
            $amount = $nights * $roomType->base_price;

            $booking = Booking::create([
                'room_id' => $room->id,
                'room_type_id' => $roomType->id,
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'status' => 'pending',
                'amount' => $amount,
            ]);

            // Mark the room as occupied immediately
            $room->status = 'Occupied';
            $room->save();

            // Load relations for email and response payload
            $bookingWithRelations = $booking->load('roomType', 'room');

            // Send emails to guest and admin
            try {
                Mail::to($booking->guest_email)->send(new BookingConfirmed($bookingWithRelations));
                $adminEmail = 'lifeofrence@gmail.com';
                if ($adminEmail) {
                    Mail::to($adminEmail)->send(new NewBookingNotification($bookingWithRelations));
                }
            } catch (\Throwable $e) {
                Log::error('Booking email send failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Booking created and room assigned. Proceed to payment initiation.',
                'booking' => $bookingWithRelations,
                'assigned_room' => [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'status' => $room->status,
                ],
                'payment_initiate_endpoint' => '/api/payments/initiate',
                'cancel_reservation' => '/api/bookings/cancel/' . $booking->id,
            ], 201);
        });
    }

    public function update(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'guest_name' => 'sometimes|string|max:255',
            'guest_email' => 'sometimes|email|max:255',
            'guest_phone' => 'sometimes|string|max:20',
            'check_in_date' => 'sometimes|date',
            'check_out_date' => 'sometimes|date',
            'room_id' => 'sometimes|exists:rooms,id',
            'room_type_id' => 'sometimes|exists:room_types,id',
        ]);
        $booking->update($validated);

        // If booking is cancelled, free up the assigned room
        if (isset($validated['status']) && $validated['status'] === 'cancelled' && $booking->room_id) {
            $room = Room::find($booking->room_id);
            if ($room) {
                $room->status = 'Available';
                $room->save();
            }
        }

        return response()->json($booking);
    }

      public function cancelled(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            
        ]);
        $booking->update($validated);

        // If booking is cancelled, free up the assigned room
        if (isset($validated['status']) && $validated['status'] === 'cancelled' && $booking->room_id) {
            $room = Room::find($booking->room_id);
            if ($room) {
                $room->status = 'Available';
                $room->save();
            }
        }

        return response()->json($booking);
    }

    public function availability(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_type_id' => 'sometimes|exists:room_types,id',
        ]);

        $checkIn = $validated['check_in_date'];
        $checkOut = $validated['check_out_date'];
        $roomTypeId = $validated['room_type_id'] ?? null;

        $query = RoomType::query()->withCount(['rooms' => function ($q) {
            $q->where('status', 'Available');
        }]);
        if ($roomTypeId) {
            $query->where('id', $roomTypeId);
        }
        $types = $query->get();

        $availableTypes = [];
        foreach ($types as $type) {
            $physicallyAvailable = $type->rooms_count;

            $overlappingBookings = Booking::query()
                ->where('room_type_id', $type->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
                ->count();

            $availableCount = max(0, $physicallyAvailable - $overlappingBookings);

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
                    'booking_endpoint' => '/api/bookings',
                ];
            }
        }

        return response()->json($availableTypes);
    }
}
