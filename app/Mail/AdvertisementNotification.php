<?php

namespace App\Mail;

use App\Models\Advertisement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdvertisementNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Advertisement $advertisement,
        public string $header = 'Новое объявление',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📢 ' . $this->header . ' #' . $this->advertisement->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.advertisement',
            with: [
                'advertisement' => $this->advertisement,
                'header' => $this->header,
            ],
        );
    }
}
