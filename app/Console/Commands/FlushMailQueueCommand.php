<?php

namespace App\Console\Commands;

use App\Services\AsyncMailer;
use Illuminate\Console\Command;

class FlushMailQueueCommand extends Command
{
    protected $signature = 'rozwia:flush-mail-queue';

    protected $description = 'Kirim seluruh email yang tertunda di antrean file secara asynchronous di background.';

    public function handle(): int
    {
        AsyncMailer::flushQueueNow();

        return self::SUCCESS;
    }
}
