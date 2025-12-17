<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoCheckout extends Command
{
    protected $signature = 'bookings:auto-checkout';
    protected $description = 'Automatically checkout guests whose checkout date has passed';

    public function handle()
    {
        $today = Carbon::today();

        // Find all bookings where checkout date has passed and status is still confirmed
        $expiredBookings = Booking::where('status', 'confirmed')
            ->where('check_out_date', '<', $today)
            ->with('room')
            ->get();

        $checkedOut = 0;
        $roomsFreed = 0;

        foreach ($expiredBookings as $booking) {
            // Update booking status to checked-out
            $booking->update(['status' => 'checked-out']);
            $checkedOut++;

            // If room is assigned, mark it as available
            if ($booking->room) {
                $booking->room->update(['status' => 'Available']);
                $roomsFreed++;

                $this->info("Room {$booking->room->room_number} marked as Available");
            }

            $this->info("Auto-checked out booking #{$booking->id} - Guest: {$booking->guest_name}");
        }

        $this->info("================================");
        $this->info("Auto-checkout completed!");
        $this->info("Bookings checked out: {$checkedOut}");
        $this->info("Rooms freed: {$roomsFreed}");
        $this->info("================================");

        return 0;
    }
}
