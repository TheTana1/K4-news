<?php

namespace App\Mail;

use App\Models\Advertisement;
use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public News $news,
        public string $header = 'Новое объявление',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📢 ' . $this->header . ' #' . $this->news->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.news',
            with: [
                'news' => $this->news,
                'header' => $this->header,
            ],
        );
    }
}
