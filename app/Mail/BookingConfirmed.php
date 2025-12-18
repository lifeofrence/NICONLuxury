<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public array $allBookings;
    public int $numberOfRooms;
    /** @var array<int, array{id:int, room_number:string, status:string}> */
    public array $assignedRooms;
    public float $totalAmount;

    public function __construct(
        Booking $booking,
        int $numberOfRooms = 1,
        array $assignedRooms = [],
        float $totalAmount = 0.0,
        array $allBookings = []
    ) {
        $this->booking = $booking;
        $this->numberOfRooms = $numberOfRooms;
        $this->assignedRooms = $assignedRooms;
        $this->totalAmount = $totalAmount > 0 ? $totalAmount : (float) $booking->amount;
        $this->allBookings = !empty($allBookings) ? $allBookings : [$booking];
    }

    public function build()
    {
        return $this->subject('Your reservation details')
            ->view('emails.booking_confirmed')
            ->with([
                'booking' => $this->booking,
                'all_bookings' => $this->allBookings,
                'number_of_rooms' => $this->numberOfRooms,
                'assigned_rooms' => $this->assignedRooms,
                'total_amount' => $this->totalAmount,
            ]);
    }
}