<?php

namespace App\Mail;

use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class LivraisonMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $storage;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct($storage, $url)
    {
        $this->mailData = $mailData;
        $this->storage = $storage;
        $this->url = $url;
        dd($this->mailData, $this->storage, $this->url);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bon de livraison',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.livraison',
            with: [
                'orderUrl' => $this->mailData['orderUrl'],
                'url' => $this->mailData['url'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path($this->storage))
                ->as('Bon de livraison'.' '.$this->mailData['number'].'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
