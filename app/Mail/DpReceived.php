<?php

namespace App\Mail;

use App\Helpers\RozwiaHelper;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DpReceived extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $sapaan;

    public function __construct(
        public User $user,
        public Booking $booking,
        public Invoice $invoice,
    ) {
        $this->loginUrl = RozwiaHelper::dynamicLoginUrl();
        $this->sapaan   = RozwiaHelper::userSalutation($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bukti DP Diterima - {$this->booking->kode_booking} | ROZWIA Islamic Travel",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dp-received',
        );
    }
}
