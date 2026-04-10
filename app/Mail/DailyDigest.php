<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $digest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Daily CRM Digest — ' . now()->format('M j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.daily-digest',
        );
    }
}
