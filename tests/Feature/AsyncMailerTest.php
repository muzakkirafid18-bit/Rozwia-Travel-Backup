<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMail;
use App\Models\User;
use App\Services\AsyncMailer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AsyncMailerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_flush_queue_dispatches_queued_mailable_and_raw_emails(): void
    {
        Mail::fake();

        // Bersihkan sisa file antrean sebelum uji
        $queueDir = AsyncMailer::queueDir();
        foreach (glob($queueDir . DIRECTORY_SEPARATOR . '*.ser') ?: [] as $existing) {
            @unlink($existing);
        }

        $user = User::where('email', 'jamaah@rozwia.com')->firstOrFail();

        $mailableFile = $queueDir . DIRECTORY_SEPARATOR . '001_test_mailable.ser';
        file_put_contents($mailableFile, serialize([
            'kind'     => 'mailable',
            'to_email' => $user->email,
            'to_name'  => $user->name,
            'mailable' => new AccountCredentialsMail($user, 'TempPass123!', 'Uji Kirim Email'),
        ]));

        $rawFile = $queueDir . DIRECTORY_SEPARATOR . '002_test_raw.ser';
        file_put_contents($rawFile, serialize([
            'kind'        => 'raw',
            'to_email'    => 'test-raw@rozwia.com',
            'to_name'     => 'Admin Uji',
            'subject'     => 'Subjek Uji Raw',
            'body'        => 'Isi email uji coba.',
            'attachments' => [],
        ]));

        $processed = AsyncMailer::flushQueueNow();

        $this->assertSame(2, $processed);
        $this->assertFileDoesNotExist($mailableFile);
        $this->assertFileDoesNotExist($rawFile);

        Mail::assertSent(AccountCredentialsMail::class, function (AccountCredentialsMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_flush_mail_queue_artisan_command_succeeds(): void
    {
        $this->artisan('rozwia:flush-mail-queue')
            ->assertExitCode(0);
    }
}
