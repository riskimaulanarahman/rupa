<?php

namespace App\Mail;

use App\Models\OutletInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PaymentSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OutletInvoice $invoice,
        public string $approveUrl,
        public string $rejectUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Tenant Menunggu Verifikasi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-submitted',
        );
    }

    public function attachments(): array
    {
        if (! $this->invoice->payment_proof) {
            return [];
        }

        if (! Storage::disk('public')->exists($this->invoice->payment_proof)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->invoice->payment_proof)
                ->as('bukti-pembayaran-tenant-'.$this->invoice->id.'.'.pathinfo($this->invoice->payment_proof, PATHINFO_EXTENSION)),
        ];
    }
}
