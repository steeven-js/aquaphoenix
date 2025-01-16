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

/**
 * Classe pour l'envoi des bons de livraison par email
 */
class DeliveryNoteMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crée une nouvelle instance du mail
     *
     * @param Order $order La commande concernée
     * @param string $pdfPath Le chemin vers le fichier PDF du bon de livraison
     */
    public function __construct(public Order $order, public string $pdfPath)
    {
    }

    /**
     * Configure l'enveloppe du mail
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau bon de livraison - {$this->order->number}",
            cc: [
                new Address('jacques.steeven@gmail.com', 'Steeven JACQUES'),
            ],
        );
    }

    /**
     * Configure le contenu du mail
     *
     * @return Content
     */
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

    /**
     * Configure les pièces jointes du mail
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as("BL-{$this->order->number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
