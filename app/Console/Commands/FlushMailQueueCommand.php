<?php

namespace App\Console\Commands;

use App\Services\AsyncMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FlushMailQueueCommand extends Command
{
    protected $signature = 'rozwia:flush-mail-queue';

    protected $description = 'Kirim seluruh email yang tertunda di antrean file secara asynchronous di background.';

    public function handle(): int
    {
        $dir = AsyncMailer::queueDir();
        $lockPath = $dir . DIRECTORY_SEPARATOR . '.worker.lock';
        $lockFp = @fopen($lockPath, 'c+');

        if (!$lockFp || !@flock($lockFp, LOCK_EX | LOCK_NB)) {
            if ($lockFp) {
                @fclose($lockFp);
            }
            return self::SUCCESS;
        }

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

                    try {
                        $payload = unserialize($raw);
                        if (!is_array($payload)) {
                            continue;
                        }

                        if (($payload['kind'] ?? '') === 'mailable' && !empty($payload['mailable'])) {
                            $mailer = !empty($payload['to_name'])
                                ? Mail::to($payload['to_email'], $payload['to_name'])
                                : Mail::to($payload['to_email']);
                            $mailer->send($payload['mailable']);
                        } elseif (($payload['kind'] ?? '') === 'raw') {
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
                            } finally {
                                foreach ($attachments as $att) {
                                    if (!empty($att['path']) && is_file($att['path'])) {
                                        @unlink($att['path']);
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        } finally {
            @flock($lockFp, LOCK_UN);
            @fclose($lockFp);
        }

        return self::SUCCESS;
    }
}
