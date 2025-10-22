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

        $revenue = Booking::query()
            ->where('status', 'confirmed')
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->sum('amount');

        return response()->json([
            'occupancy_rate_percent' => $occupancyRate,
            'revenue' => $revenue,
            'occupied_rooms' => $occupiedCount,
            'total_rooms' => $totalRooms,
            'period' => compact('checkIn', 'checkOut'),
        ]);
    }
}