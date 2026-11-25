<?php

namespace App\Mail;

use App\Helpers\RozwiaHelper;
use App\Models\Booking;
use App\Models\JamaahDetail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KemenagVisaUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $sapaan;

    public function __construct(
        public User $user,
        public Booking $booking,
        public JamaahDetail $jamaah,
    ) {
        $this->loginUrl = RozwiaHelper::dynamicLoginUrl();
        $this->sapaan   = RozwiaHelper::userSalutation($user, $jamaah);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pembaruan Status Surat Kemenag & Visa - {$this->jamaah->nama_lengkap} | ROZWIA",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.kemenag-visa-updated');
    }
}
