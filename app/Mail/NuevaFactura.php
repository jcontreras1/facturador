<?php

namespace App\Mail;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;


class NuevaFactura extends Mailable
{
    use Queueable, SerializesModels;
    public $pdf;
    public function __construct(
        public Factura $factura,
    )
    {
        $avatar = base64_encode(file_get_contents(variable_global('AVATAR')));
        $this->pdf = PDF::loadView('facturacion.pdf', ['factura' => $this->factura, 'avatar' => $avatar]);
    }
        
        /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Factura',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.factura',
            with: ['factura' => $this->factura],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments()
    {
        return [
            Attachment::fromData( fn() => $this->pdf->output(), 'F' . str_pad($this->factura->nro_factura, 8, '0', STR_PAD_LEFT) . '.pdf')
            ->withMime('application/pdf')
        ];
    }
}
