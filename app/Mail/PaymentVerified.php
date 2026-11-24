<?php

namespace App\Mail;

use App\Helpers\RozwiaHelper;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerified extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $sapaan;

    public function __construct(
        public User $user,
        public Booking $booking,
        public ?string $plainPassword = null,
        public string $paymentType = 'DP',
        public ?string $trackingToken = null,
    ) {
        $this->loginUrl = RozwiaHelper::dynamicLoginUrl();
        $this->sapaan   = RozwiaHelper::userSalutation($user);
    }

    public function envelope(): Envelope
    {
        $label = match ($this->paymentType) {
            'Lunas'   => 'Pembayaran Pelunasan Diverifikasi ✅',
            'Cicilan' => 'Pembayaran Cicilan Diverifikasi ✅',
            default   => 'Pembayaran DP Diverifikasi ✅',
        };

        return new Envelope(
            subject: "{$label} - {$this->booking->kode_booking} | ROZWIA Islamic Travel",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-verified',
        );
    }
}
