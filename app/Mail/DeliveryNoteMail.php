<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Address;

class DeliveryNoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $pdfPath)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau bon de livraison - {$this->order->number}",
            cc: [
                new Address('jacques.steeven@gmail.com', 'Steeven JACQUES'),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.delivery-note',
            with: [
                'order' => $this->order,
                'url' => $this->order->url,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as("BL-{$this->order->number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
