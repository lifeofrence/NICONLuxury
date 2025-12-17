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
        $query = Booking::query()->with(['roomType', 'room']);

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

            $requestedCount = (int) ($data['number_of_rooms'] ?? 1);

            // Gather available rooms for the type
            $availableRooms = Room::query()
                ->where('room_type_id', $roomType->id)
                ->where('status', 'Available')
                ->inRandomOrder()
                ->limit($requestedCount)
                ->get();

            if ($availableRooms->count() < $requestedCount) {
                return response()->json([
                    'message' => 'Sorry, selected number of rooms are not available',
                    'requested' => $requestedCount,
                    'available' => $availableRooms->count(),
                ], 422);
            }

            $nights = (new \DateTime($data['check_in_date']))->diff(new \DateTime($data['check_out_date']))->days;
            $perRoomAmount = $nights * $roomType->base_price;
            $totalAmount = $perRoomAmount * $requestedCount;

            $createdBookings = [];
            $assignedRoomsPayload = [];

            foreach ($availableRooms as $room) {
                $booking = Booking::create([
                    'room_id' => $room->id,
                    'room_type_id' => $roomType->id,
                    'guest_name' => $data['guest_name'],
                    'guest_email' => $data['guest_email'],
                    'guest_phone' => $data['guest_phone'],
                    'check_in_date' => $data['check_in_date'],
                    'check_out_date' => $data['check_out_date'],
                    'status' => 'pending',
                    'amount' => $perRoomAmount,
                ]);

                // Mark the room as occupied immediately
                $room->status = 'Occupied';
                $room->save();

                $createdBookings[] = $booking->load('roomType', 'room');
                $assignedRoomsPayload[] = [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'status' => $room->status,
                ];
            }

            // Send emails (use the first booking to avoid multiple emails to the guest)
            try {
                $primaryBooking = $createdBookings[0];
                Mail::to($primaryBooking->guest_email)->send(
                    new BookingConfirmed($primaryBooking, $requestedCount, $assignedRoomsPayload, (float) $totalAmount)
                );
                $adminEmail = 'lifeofrence@gmail.com';
                if ($adminEmail) {
                    Mail::to($adminEmail)->send(new NewBookingNotification($primaryBooking));
                }
            } catch (\Throwable $e) {
                Log::error('Booking email send failed', [
                    'booking_id' => $createdBookings[0]->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Bookings created and rooms assigned.',
                'number_of_rooms' => $requestedCount,
                'total_amount' => $totalAmount,
                // For backward compatibility
                'booking' => $createdBookings[0],
                // New comprehensive payloads
                'bookings' => $createdBookings,
                'assigned_rooms' => $assignedRoomsPayload,
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

        $query = RoomType::query()->withCount([
            'rooms' => function ($q) {
                $q->where('status', 'Available');
            }
        ]);
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

    public function checkout(int $id)
    {
        $booking = Booking::with('room')->findOrFail($id);

        // Can only checkout confirmed bookings
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed bookings can be checked out'
            ], 400);
        }

        // Update booking status
        $booking->update(['status' => 'checked-out']);

        // Mark room as available if assigned
        if ($booking->room) {
            $booking->room->update(['status' => 'Available']);
        }

        return response()->json([
            'message' => 'Guest checked out successfully',
            'booking' => $booking
        ]);
    }

    public function sendEmail(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            \Mail::to($booking->guest_email)->send(
                new \App\Mail\CustomGuestEmail(
                    $validated['subject'],
                    $validated['message'],
                    $booking->guest_name
                )
            );

            return response()->json([
                'message' => 'Email sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}
