<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Pengirim email non-blocking agar aksi Admin/Jamaah merespons instan (<100ms)
 * tanpa tertahan handshake SMTP (10-15 detik) di single-threaded `php artisan serve`.
 */
class AsyncMailer
{
    public static function sendMailable(string $toEmail, Mailable $mailable, ?string $toName = null): void
    {
        if (app()->runningUnitTests()) {
            try {
                $mailer = $toName ? Mail::to($toEmail, $toName) : Mail::to($toEmail);
                $mailer->send($mailable);
            } catch (\Throwable $e) {
                report($e);
            }
            return;
        }

        self::enqueuePayload([
            'kind'     => 'mailable',
            'to_email' => $toEmail,
            'to_name'  => $toName,
            'mailable' => $mailable,
        ]);
    }

    /**
     * @param  array<int, array{path:string, as:string, mime:string}>  $attachments
     */
    public static function sendRaw(string $toEmail, string $subject, string $body, ?string $toName = null, array $attachments = []): void
    {
        if (app()->runningUnitTests()) {
            try {
                Mail::raw($body, function ($mail) use ($toEmail, $toName, $subject, $attachments) {
                    $mail->to($toEmail, $toName)->subject($subject);
                    foreach ($attachments as $att) {
                        if (!empty($att['path']) && is_file($att['path'])) {
                            $mail->attach($att['path'], [
                                'as'   => $att['as'] ?? basename($att['path']),
                                'mime' => $att['mime'] ?? 'application/octet-stream',
                            ]);
                        }
                    }
                });
            } catch (\Throwable $e) {
                report($e);
            }
            return;
        }

        $copiedAttachments = [];
        $dir = self::attachmentTempDir();
        foreach ($attachments as $att) {
            if (!empty($att['path']) && is_file($att['path'])) {
                $copyPath = $dir . DIRECTORY_SEPARATOR . Str::uuid() . '_' . basename((string) ($att['as'] ?? 'file'));
                if (@copy($att['path'], $copyPath)) {
                    $copiedAttachments[] = [
                        'path' => $copyPath,
                        'as'   => $att['as'] ?? basename($att['path']),
                        'mime' => $att['mime'] ?? 'application/octet-stream',
                    ];
                }
            }
        }

        self::enqueuePayload([
            'kind'        => 'raw',
            'to_email'    => $toEmail,
            'to_name'     => $toName,
            'subject'     => $subject,
            'body'        => $body,
            'attachments' => $copiedAttachments,
        ]);
    }

    public static function queueDir(): string
    {
        $dir = storage_path('app/mail_queue');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private static function attachmentTempDir(): string
    {
        $dir = storage_path('app/mail_queue/attachments');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private static function enqueuePayload(array $payload): void
    {
        $file = self::queueDir() . DIRECTORY_SEPARATOR . sprintf(
            '%s_%s.ser',
            now()->format('YmdHis'),
            Str::random(12)
        );

        $written = @file_put_contents($file, serialize($payload), LOCK_EX);
        if ($written === false) {
            // Fallback jika storage tidak bisa ditulis
            try {
                if ($payload['kind'] === 'mailable') {
                    $mailer = $payload['to_name']
                        ? Mail::to($payload['to_email'], $payload['to_name'])
                        : Mail::to($payload['to_email']);
                    $mailer->send($payload['mailable']);
                }
            } catch (\Throwable $e) {
                report($e);
            }
            return;
        }

        self::spawnBackgroundWorker();
    }

    private static function spawnBackgroundWorker(): void
    {
        $php = PHP_BINARY;
        $artisan = base_path('artisan');

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'cmd /c start "" /B "' . $php . '" "' . $artisan . '" rozwia:flush-mail-queue > NUL 2>&1';
            $handle = @popen($cmd, 'r');
            if (is_resource($handle)) {
                @pclose($handle);
            }
            return;
        }

        @exec(escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' rozwia:flush-mail-queue >/dev/null 2>&1 &');
    }
}
