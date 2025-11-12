<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorksheetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Product $product,
        public ProductLog $productLog,
        public string $pdfContent,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $workType = match ($this->productLog->what) {
            'commissioning' => __('Commissioning'),
            'maintenance' => __('Maintenance'),
            default => __('Repair'),
        };

        return new Envelope(
            subject: __('Work Sheet') . ' - ' . $workType . ' - ' . $this->product->serial_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.worksheet',
            with: [
                'product' => $this->product,
                'productLog' => $this->productLog,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn (): string => $this->pdfContent, 'munkalap-' . $this->product->serial_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
