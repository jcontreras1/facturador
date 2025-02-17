<?php

namespace App\Mail;

use App\Models\Arca\Comprobante;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Attachment;

class NuevoComprobante extends Mailable
{
    use Queueable, SerializesModels;
    public $pdf;

    /**
     * Create a new message instance.
     */
    public function __construct( public Comprobante $comprobante)
    {
        $this->pdf = PDF::loadView('comprobantes.pdf', ['comprobante' => $this->comprobante]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Comprobante',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.comprobante',
            with: ['comprobante' => $this->comprobante],
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
            Attachment::fromData( fn() => 
            $this->pdf->output(), 
            $this->comprobante->tipoComprobante->codigo . 
            str_pad($this->comprobante->punto_venta, 5, '0', STR_PAD_LEFT) .
            str_pad($this->comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT) . 
            '.pdf')
        ->withMime('application/pdf')];
    }
}
