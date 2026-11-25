<?php

namespace App\Mail;

use App\Helpers\RozwiaHelper;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Paket;
use App\Models\RekeningPerusahaan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $sapaan;

    public function __construct(
        public User $user,
        public Booking $booking,
        public Invoice $invoice,
        public ?RekeningPerusahaan $rekening,
        public Paket $paket,
        public string $tempPassword,
    ) {
        $this->loginUrl = RozwiaHelper::dynamicLoginUrl();
        $this->sapaan   = RozwiaHelper::userSalutation($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Konfirmasi Pendaftaran - {$this->booking->kode_booking} | ROZWIA Islamic Travel",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
        );
    }
}
