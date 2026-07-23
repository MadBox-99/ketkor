<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Product $product,
        public string $subjectLine,
        public string $body,
        public ?string $bookingUrl,
        public ?string $contactPhone,
        public ?string $contactEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.maintenance-reminder',
            with: [
                'body' => $this->body,
                'bookingUrl' => $this->bookingUrl,
                'contactPhone' => $this->contactPhone,
                'contactEmail' => $this->contactEmail,
            ],
        );
    }
}
