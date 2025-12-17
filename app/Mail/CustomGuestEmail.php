<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomGuestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $messageContent;
    public $guestName;

    public function __construct($subject, $messageContent, $guestName)
    {
        $this->subject = $subject;
        $this->messageContent = $messageContent;
        $this->guestName = $guestName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom_guest_email',
        );
    }
}
