<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $checkIn = $request->query('check_in_date', now()->toDateString());
        $checkOut = $request->query('check_out_date', now()->addDay()->toDateString());

        $totalRooms = RoomType::sum('total_rooms');

        $occupiedCount = Booking::query()
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->count();

        $occupancyRate = $totalRooms > 0 ? round(($occupiedCount / $totalRooms) * 100, 2) : 0;

        // Total revenue (all time) - includes confirmed and checked-out bookings
        $totalRevenue = Booking::query()
            ->whereIn('status', ['confirmed', 'checked-out'])
            ->sum('amount');

        // Total bookings (all time)
        $totalBookings = Booking::count();

        // Recent bookings (last 5)
        $recentBookings = Booking::with(['roomType', 'room'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Room type breakdown
        $roomTypeBookings = RoomType::withCount([
            'bookings' => function ($q) {
                $q->whereIn('status', ['confirmed', 'pending']);
            }
        ])->get();

        // Room status breakdown (from physical rooms)
        $roomStatuses = \App\Models\Room::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // All physical rooms with their current status
        $allRooms = \App\Models\Room::with('roomType')->get();

        return response()->json([
            'occupancy_rate_percent' => $occupancyRate,
            'total_revenue' => $totalRevenue,
            'occupied_rooms' => $occupiedCount,
            'total_rooms' => $totalRooms,
            'total_bookings' => $totalBookings,
            'recent_bookings' => $recentBookings,
            'room_type_bookings' => $roomTypeBookings,
            'room_statuses' => $roomStatuses,
            'all_rooms' => $allRooms,
            'period' => compact('checkIn', 'checkOut'),
        ]);
    }
}