<?php

namespace App\Mail;

use App\Helpers\RozwiaHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $sapaan;

    public function __construct(
        public User $user,
        public string $plainPassword,
        public string $reason = 'Aktivasi Akun Portal Jamaah',
        public ?string $trackingToken = null,
    ) {
        $this->loginUrl = RozwiaHelper::dynamicLoginUrl();
        $this->sapaan   = RozwiaHelper::userSalutation($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->reason} | ROZWIA Islamic Travel",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-credentials',
        );
    }
}
