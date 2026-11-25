<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Dokumen;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\Paket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserRevisionChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_three_failed_login_attempts_lock_login_for_five_minutes(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $resp = $this->post('/login', [
                'email'    => 'jamaah@rozwia.com',
                'password' => 'SalahPassword' . $i . '!',
            ]);
            $resp->assertSessionHasErrors('email');
            $this->assertStringNotContainsString('diblokir sementara', session('errors')->first('email'));
        }

        // Percobaan ke-3 salah pada email yang sama -> email tersebut terblokir 5 menit
        $third = $this->post('/login', [
            'email'    => 'jamaah@rozwia.com',
            'password' => 'SalahPassword3!',
        ]);
        $third->assertSessionHasErrors('email');
        $this->assertStringContainsString('diblokir sementara', session('errors')->first('email'));

        // Percobaan berikutnya pada email yang sama dengan password BENAR tetap diblokir selama 5 menit
        $fourth = $this->post('/login', [
            'email'    => 'jamaah@rozwia.com',
            'password' => 'Jamaah123!',
        ]);
        $this->assertGuest();
        $fourth->assertSessionHasErrors('email');
        $this->assertStringContainsString('diblokir sementara', session('errors')->first('email'));

        // Email lain (misal administrator/admin) TIDAK ikut terblokir
        $other = $this->post('/login', [
            'email'    => 'administrator@rozwia.com',
            'password' => 'Administrator123!',
        ]);
        $this->assertAuthenticated();
        $other->assertRedirect('/home');
    }

    public function test_active_session_is_logged_out_on_refresh_after_super_admin_resets_password(): void
    {
        Mail::fake();

        $jamaah = User::where('email', 'jamaah@rozwia.com')->firstOrFail();
        $superAdmin = User::where('role', 'administrator')->firstOrFail();

        // 1. User login terlebih dahulu secara sah
        $this->post('/login', [
            'email'    => $jamaah->email,
            'password' => 'Jamaah123!',
        ])->assertRedirect('/home');
        $this->assertAuthenticatedAs($jamaah);

        // 2. Super Admin melakukan reset password dari tempat lain (DB hash & must_change_password berubah)
        $jamaah->forceFill([
            'password'             => Hash::make('TempReset999!'),
            'must_change_password' => true,
            'password_changed_at'  => null,
        ])->save();

        // Simulasikan refresh HTTP baru (clear guard in-memory cache agar baca ulang dari DB)
        $this->app['auth']->forgetGuards();

        // 3. Saat user me-refresh halaman (termasuk /akun/ganti-password), wajib ter-logout ke /login
        $refreshResp = $this->get(route('user.dashboard'));
        $refreshResp->assertRedirect(route('login'));
        $this->assertGuest();

        // 4. Setelah login dengan password sementara baru dari email, baru diarahkan ke halaman ganti password
        $this->post('/login', [
            'email'    => $jamaah->email,
            'password' => 'TempReset999!',
        ])->assertRedirect('/home');

        $this->get(route('user.dashboard'))->assertRedirect(route('password.force.show'));
    }

    public function test_booking_with_gender_radio_updates_dashboard_and_invoice_salutation(): void
    {
        $paket = Paket::where('status', 'active')->firstOrFail();
        $jadwal = Jadwal::where('id_paket', $paket->id)->firstOrFail();

        $this->post(route('booking.store', $paket->id), [
            'nama_lengkap'   => 'Siti Aisyah',
            'jenis_kelamin'  => 'Perempuan',
            'nomor_telepon'  => '81234567890',
            'email'          => 'siti.aisyah@example.com',
            'tipe_kamar'     => 'quad',
            'jumlah_jamaah'  => 2,
            'jadwal_id'      => $jadwal->id,
            'anggota_jamaah' => ['Ahmad Fauzi'],
            'anggota_gender' => ['Laki-laki'],
        ])->assertRedirect();

        $user = User::where('email', 'siti.aisyah@example.com')->firstOrFail();
        $user->update(['status_akun' => 'active', 'must_change_password' => false]);

        $booking = Booking::where('id_pengguna', $user->id)->with('jamaahDetails')->firstOrFail();
        $this->assertSame('Perempuan', $booking->jamaahDetails[0]->jenis_kelamin);
        $this->assertSame('Laki-laki', $booking->jamaahDetails[1]->jenis_kelamin);

        $dash = $this->actingAs($user)->get(route('user.dashboard'));
        $dash->assertStatus(200);
        $dash->assertSee('Ibu Siti Aisyah');
        $dash->assertDontSee('Bapak/Ibu Siti Aisyah');

        $kuitansi = $this->actingAs($user)->get(route('user.kuitansi'));
        $kuitansi->assertStatus(200);
        $kuitansi->assertSee('Ibu Siti Aisyah');
        $kuitansi->assertDontSee('Bapak/Ibu Siti Aisyah');
    }

    public function test_admin_views_reflect_sidebar_order_kpi_akun_kemenag_order_and_no_bukti_menunggu(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $jamaah = User::where('email', 'jamaah@rozwia.com')->firstOrFail();
        $paket = Paket::where('status', 'active')->firstOrFail();
        $jadwal = Jadwal::where('id_paket', $paket->id)->firstOrFail();

        Booking::create([
            'id_pengguna'    => $jamaah->id,
            'id_jadwal'      => $jadwal->id,
            'kode_booking'   => 'BK-PAY-01',
            'jumlah_jamaah'  => 1,
            'total_harga'    => 35000000,
            'status_booking' => 'Menunggu_DP',
        ]);

        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('0 Akun');

        // Cek urutan sidebar: Kelola Pembayaran di atas Status Surat Kemenag & Visa
        $html = $dashboard->getContent();
        $posPembayaran = strpos($html, 'Kelola Pembayaran');
        $posKemenag = strpos($html, 'Status Surat Kemenag');
        $this->assertNotFalse($posPembayaran);
        $this->assertNotFalse($posKemenag);
        $this->assertLessThan($posKemenag, $posPembayaran);

        // Cek Admin Kemenag: Perlu tindakan paling kiri & Diproses berwarna kuning
        $kemenagPage = $this->actingAs($admin)->get(route('admin.kemenag'));
        $kemenagPage->assertStatus(200);
        $kHtml = $kemenagPage->getContent();
        $this->assertLessThan(strpos($kHtml, 'Surat Kemenag Terbit'), strpos($kHtml, 'Perlu tindakan'));
        $kemenagPage->assertSee('bg-yellow-100 text-yellow-800 border-yellow-300');

        // Cek Kelola Pembayaran: Bukti Menunggu dihapus & input nominal Rp + titik
        $payPage = $this->actingAs($admin)->get(route('admin.pembayaran'));
        $payPage->assertStatus(200);
        $payPage->assertDontSee('Bukti Menunggu');
        $payPage->assertDontSee('Bukti Pembayaran Menunggu');
        $payPage->assertSee('Rp 35.000.000');
        $payPage->assertSee('placeholder="5.000.000"', false);

        // Cek label tombol Keluar menggantikan Logout di semua sidebar
        $dashboard->assertSee('Keluar');
        $dashboard->assertDontSee('>Logout<', false);

        $superAdmin = User::where('role', 'administrator')->firstOrFail();
        $saPage = $this->actingAs($superAdmin)->get(route('administrator.dashboard'));
        $saPage->assertSee('Keluar');
        $saPage->assertDontSee('>Logout<', false);

        $supervisor = User::where('role', 'supervisor')->firstOrFail();
        $spPage = $this->actingAs($supervisor)->get(route('supervisor.dashboard'));
        $spPage->assertSee('Keluar');
        $spPage->assertDontSee('>Logout<', false);

        // Cek link login langsung dari email mengeluarkan sesi lama dan mengarah ke /login
        $directResp = $this->actingAs($admin)->get(route('login.direct'));
        $directResp->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
