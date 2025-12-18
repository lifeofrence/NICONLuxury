<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public array $allBookings;
    public int $numberOfRooms;
    public array $assignedRooms;
    public float $totalAmount;

    public function __construct(
        Booking $booking,
        array $allBookings = [],
        int $numberOfRooms = 1,
        array $assignedRooms = [],
        float $totalAmount = 0.0
    ) {
        $this->booking = $booking;
        $this->allBookings = !empty($allBookings) ? $allBookings : [$booking];
        $this->numberOfRooms = $numberOfRooms;
        $this->assignedRooms = $assignedRooms;
        $this->totalAmount = $totalAmount > 0 ? $totalAmount : (float) $booking->amount;
    }

    public function build()
    {
        return $this->subject('New booking notification for customer')
            ->view('emails.new_booking_notification')
            ->with([
                'booking' => $this->booking,
                'all_bookings' => $this->allBookings,
                'number_of_rooms' => $this->numberOfRooms,
                'assigned_rooms' => $this->assignedRooms,
                'total_amount' => $this->totalAmount,
            ]);
    }
}