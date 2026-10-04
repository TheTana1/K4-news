<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $text,
        public string $header = 'Новый сотрудник',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '👤 ' . $this->header,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user',
            with: [
                'text' => $this->text,
                'header' => $this->header,
            ],
        );
    }
}
