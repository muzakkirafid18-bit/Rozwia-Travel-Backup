<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Kirim email di background biar request admin/jamaah tetap cepat.
 * Di hosting PHP-FPM/LiteSpeed pakai terminating callback setelah respons terkirim.
 */
class AsyncMailer
{
    private static bool $terminatingRegistered = false;

    public static function sendMailable(string $toEmail, Mailable $mailable, ?string $toName = null): void
    {
        if (app()->runningUnitTests()) {
            try {
                self::dispatchPayload([
                    'kind'     => 'mailable',
                    'to_email' => $toEmail,
                    'to_name'  => $toName,
                    'mailable' => $mailable,
                ]);
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
                self::dispatchPayload([
                    'kind'        => 'raw',
                    'to_email'    => $toEmail,
                    'to_name'     => $toName,
                    'subject'     => $subject,
                    'body'        => $body,
                    'attachments' => $attachments,
                ], unlinkAttachments: false);
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

    /**
     * Proses semua file antrean email yang tertunda.
     */
    public static function flushQueueNow(): int
    {
        $dir = self::queueDir();
        $lockPath = $dir . DIRECTORY_SEPARATOR . '.worker.lock';
        $lockFp = @fopen($lockPath, 'c+');

        if (!$lockFp || !@flock($lockFp, LOCK_EX | LOCK_NB)) {
            if ($lockFp) {
                @fclose($lockFp);
            }
            return 0;
        }

        $processed = 0;

        try {
            while (true) {
                $files = glob($dir . DIRECTORY_SEPARATOR . '*.ser') ?: [];
                sort($files);

                if (empty($files)) {
                    break;
                }

                foreach ($files as $file) {
                    $raw = @file_get_contents($file);
                    @unlink($file);

                    if (!$raw) {
                        continue;
                    }

                    $payload = null;
                    try {
                        $payload = unserialize($raw);
                        if (!is_array($payload)) {
                            continue;
                        }

                        if (self::dispatchPayload($payload)) {
                            $processed++;
                        }
                    } catch (\Throwable $e) {
                        Log::error('AsyncMailer failed sending queued email', [
                            'to_email' => is_array($payload) ? ($payload['to_email'] ?? null) : null,
                            'kind'     => is_array($payload) ? ($payload['kind'] ?? null) : null,
                            'error'    => $e->getMessage(),
                        ]);
                        report($e);
                    }
                }
            }
        } finally {
            @flock($lockFp, LOCK_UN);
            @fclose($lockFp);
        }

        return $processed;
    }

    private static function dispatchPayload(array $payload, bool $unlinkAttachments = true): bool
    {
        if (($payload['kind'] ?? '') === 'mailable' && !empty($payload['mailable'])) {
            $mailer = !empty($payload['to_name'])
                ? Mail::to($payload['to_email'], $payload['to_name'])
                : Mail::to($payload['to_email']);
            $mailer->send($payload['mailable']);
            return true;
        }

        if (($payload['kind'] ?? '') === 'raw') {
            $attachments = $payload['attachments'] ?? [];
            try {
                Mail::raw((string) ($payload['body'] ?? ''), function ($mail) use ($payload, $attachments) {
                    $mail->to($payload['to_email'], $payload['to_name'] ?? null)
                        ->subject((string) ($payload['subject'] ?? 'ROZWIA Islamic Travel'));

                    foreach ($attachments as $att) {
                        if (!empty($att['path']) && is_file($att['path'])) {
                            $mail->attach($att['path'], [
                                'as'   => $att['as'] ?? basename($att['path']),
                                'mime' => $att['mime'] ?? 'application/octet-stream',
                            ]);
                        }
                    }
                });
                return true;
            } finally {
                if ($unlinkAttachments) {
                    foreach ($attachments as $att) {
                        if (!empty($att['path']) && is_file($att['path'])) {
                            @unlink($att['path']);
                        }
                    }
                }
            }
        }

        return false;
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
            try {
                self::dispatchPayload($payload);
            } catch (\Throwable $e) {
                Log::error('AsyncMailer fallback synchronous send failed', [
                    'to_email' => $payload['to_email'] ?? null,
                    'error'    => $e->getMessage(),
                ]);
                report($e);
            }
            return;
        }

        // Di PHP-FPM / LiteSpeed langsung kirim pas request selesai biar gak mentok exec() disabled
        if (self::canFinishRequestInBackground()) {
            self::registerTerminatingFlush();
            return;
        }

        if (!self::spawnBackgroundWorker()) {
            self::registerTerminatingFlush();
        }
    }

    private static function canFinishRequestInBackground(): bool
    {
        return PHP_OS_FAMILY !== 'Windows'
            && (function_exists('fastcgi_finish_request') || function_exists('litespeed_finish_request'));
    }

    private static function registerTerminatingFlush(): void
    {
        if (app()->runningInConsole()) {
            self::flushQueueNow();
            return;
        }

        if (self::$terminatingRegistered) {
            return;
        }

        self::$terminatingRegistered = true;

        app()->terminating(function (): void {
            self::$terminatingRegistered = false;

            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            } elseif (function_exists('litespeed_finish_request')) {
                @litespeed_finish_request();
            }

            self::flushQueueNow();
        });
    }

    private static function spawnBackgroundWorker(): bool
    {
        $artisan = base_path('artisan');

        if (PHP_OS_FAMILY === 'Windows') {
            if (!function_exists('popen') || self::isFunctionDisabled('popen')) {
                return false;
            }
            $php = self::resolveCliPhpBinary();
            if ($php === null) {
                return false;
            }

            $cmd = 'cmd /c start "" /B "' . $php . '" "' . $artisan . '" rozwia:flush-mail-queue > NUL 2>&1';
            $handle = @popen($cmd, 'r');
            if (is_resource($handle)) {
                @pclose($handle);
                return true;
            }
            return false;
        }

        if (!function_exists('exec') || self::isFunctionDisabled('exec')) {
            return false;
        }

        $php = self::resolveCliPhpBinary();
        if ($php === null) {
            return false;
        }

        $exitCode = 1;
        $output = [];
        @exec(escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' rozwia:flush-mail-queue >/dev/null 2>&1 &', $output, $exitCode);

        return $exitCode === 0;
    }

    private static function isFunctionDisabled(string $fn): bool
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return in_array(strtolower($fn), array_map('strtolower', $disabled), true);
    }

    private static function resolveCliPhpBinary(): ?string
    {
        $candidates = [];

        if (PHP_SAPI === 'cli' && PHP_BINARY !== '') {
            $candidates[] = PHP_BINARY;
        }

        if (defined('PHP_BINDIR') && PHP_BINDIR !== '') {
            $ext = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
            $candidates[] = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php' . $ext;
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            $majorMinor = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
            $candidates = array_merge($candidates, [
                '/usr/local/bin/ea-php' . PHP_MAJOR_VERSION . PHP_MINOR_VERSION,
                "/usr/bin/php{$majorMinor}",
                "/usr/local/bin/php{$majorMinor}",
                '/usr/bin/php',
                '/usr/local/bin/php',
            ]);
        }

        foreach (array_unique($candidates) as $candidate) {
            if ($candidate === '' || !@is_file($candidate)) {
                continue;
            }
            if (PHP_OS_FAMILY !== 'Windows' && !@is_executable($candidate)) {
                continue;
            }
            $name = strtolower(basename($candidate));
            if (str_contains($name, 'fpm') || str_contains($name, 'cgi') || str_contains($name, 'httpd') || str_contains($name, 'apache')) {
                continue;
            }
            return $candidate;
        }

        return null;
    }
}
