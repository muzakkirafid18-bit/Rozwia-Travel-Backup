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

class DocumentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $sapaan;

    public function __construct(
        public User $user,
        public Booking $booking,
        public string $statusType, // 'all_verified' | 'needs_revision'
        public ?JamaahDetail $jamaah = null,
        public ?string $documentName = null,
        public ?string $adminNotes = null,
    ) {
        $this->loginUrl = RozwiaHelper::dynamicLoginUrl();
        $this->sapaan   = RozwiaHelper::userSalutation($user, $jamaah);
    }

    public function envelope(): Envelope
    {
        $subject = $this->statusType === 'all_verified'
            ? "Seluruh Berkas Jamaah Terverifikasi ✅ - {$this->booking->kode_booking} | ROZWIA"
            : "Perbaikan Berkas {$this->documentName} Diperlukan ⚠️ - {$this->booking->kode_booking} | ROZWIA";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.document-status');
    }
}
