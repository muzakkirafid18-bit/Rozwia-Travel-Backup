<?php

namespace Tests\Feature;

use App\Mail\PaymentVerified;
use App\Models\Booking;
use App\Models\Dokumen;
use App\Models\Hotel;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\JenisLayanan;
use App\Models\Maskapai;
use App\Models\Notifikasi;
use App\Models\Paket;
use App\Models\RiwayatPembayaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EndToEndFullFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        Storage::fake('private');
    }

    public function test_full_end_to_end_public_jamaah_admin_supervisor_flow(): void
    {
        // ============================================================
        // 1. HALAMAN PUBLIK
        // ============================================================
        $paket = Paket::with('jadwals')->where('status', 'active')->firstOrFail();
        $jadwal = $paket->jadwals->firstOrFail();

        $this->get('/')->assertStatus(200);
        $this->get(route('paket.umroh'))->assertStatus(200);
        $this->get(route('paket.haji'))->assertStatus(200);
        $this->get(route('paket.detail', $paket->id))->assertStatus(200);
        $this->get(route('tentang.kami'))->assertStatus(200);
        $this->get(route('faq'))->assertStatus(200);

        // ============================================================
        // 2. BOOKING PUBLIK (2 JAMAAH) + SIGNED URL DP + SECURITY CHECK
        // ============================================================
        $bookingResponse = $this->post(route('booking.store', $paket->id), [
            'nama_lengkap'   => 'Budi Santoso',
            'email'          => 'budi.e2e@example.com',
            'nomor_telepon'  => '081234567890',
            'alamat'         => 'Jl. Merdeka No. 10, Bogor',
            'jadwal_id'      => $jadwal->id,
            'tipe_kamar'     => 'quad',
            'jumlah_jamaah'  => 2,
            'anggota_jamaah' => ['Siti Aminah'],
        ]);

        $bookingResponse->assertStatus(302);
        $signedDpUrl = $bookingResponse->headers->get('Location');
        $this->assertNotNull($signedDpUrl);
        $this->assertStringContainsString('signature=', $signedDpUrl);

        $booking = Booking::with(['user', 'jamaahDetails', 'invoices'])->latest()->firstOrFail();
        $this->assertEquals(2, $booking->jamaahDetails->count());
        $this->assertEquals('inactive', $booking->user->status_akun);

        // Keamanan: akses DP publik TANPA signature harus 403
        $this->get(route('booking.dp.form', $booking->kode_booking))->assertStatus(403);

        // Akses DP publik DENGAN signature harus 200
        $this->get($signedDpUrl)->assertStatus(200);

        // Upload bukti DP via Signed URL
        $signedPostUrl = URL::temporarySignedRoute('booking.dp.store', now()->addDays(1), [
            'kode_booking' => $booking->kode_booking,
        ]);
        $dpPost = $this->post($signedPostUrl, [
            'amount'   => 10000000,
            'bukti_dp' => UploadedFile::fake()->image('bukti-dp.jpg'),
        ]);
        $dpPost->assertRedirect(route('login'));

        $pembayaran = RiwayatPembayaran::latest()->firstOrFail();
        $this->assertEquals('Menunggu_Verifikasi', $pembayaran->status);

        // ============================================================
        // 3. ADMIN VERIFIKASI DP -> AKUN AKTIF
        // ============================================================
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.dokumen'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.kemenag'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.hotel'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.manifest'))->assertStatus(200);

        $this->actingAs($admin)
            ->post(route('admin.pembayaran.verify', $pembayaran->id))
            ->assertSessionHas('success');

        $budi = $booking->user->fresh();
        $this->assertEquals('active', $budi->status_akun);
        $this->assertTrue((bool) $budi->must_change_password);
        $this->assertEquals('Menunggu_Pengumpulan_Dokumen', $booking->fresh()->status_booking);

        $sentTempPassword = null;
        Mail::assertSent(PaymentVerified::class, function (PaymentVerified $mail) use ($budi, &$sentTempPassword) {
            if ($mail->hasTo($budi->email)) {
                $sentTempPassword = $mail->plainPassword;
                return true;
            }
            return false;
        });
        $this->assertNotNull($sentTempPassword);
        $this->assertNotEquals('Jamaah123!', $sentTempPassword);

        // ============================================================
        // 4. JAMAAH LOGIN PERTAMA -> PAKSA GANTI PASSWORD -> UPLOAD 10 BERKAS
        // ============================================================
        $this->actingAs($budi)->get(route('user.dashboard'))->assertRedirect(route('password.force.show'));
        $this->actingAs($budi)->get(route('password.force.show'))->assertStatus(200);

        $this->actingAs($budi)->post(route('password.force.update'), [
            'current_password'      => $sentTempPassword,
            'password'              => 'PasswordBaru2026!',
            'password_confirmation' => 'PasswordBaru2026!',
        ])->assertRedirect(route('home'));

        $budi->refresh();
        $this->assertFalse((bool) $budi->must_change_password);

        // Akses seluruh halaman Portal Jamaah
        $this->actingAs($budi)->get(route('user.dashboard'))->assertStatus(200);
        $this->actingAs($budi)->get(route('user.profile'))->assertStatus(200);
        $this->actingAs($budi)->get(route('user.dokumen'))->assertStatus(200);
        $this->actingAs($budi)->get(route('user.tracking'))->assertStatus(200);
        $this->actingAs($budi)->get(route('user.formulir.dp'))->assertStatus(200);

        // Update profil anggota ke-2 (Siti Aminah)
        $siti = $booking->jamaahDetails->firstWhere('nama_lengkap', 'Siti Aminah');
        $this->assertNotNull($siti);
        $this->actingAs($budi)->post(route('user.jamaah.update', $siti->id), [
            'nama_lengkap'    => 'Siti Aminah',
            'nik'             => '3201010101010002',
            'passport_number' => 'C9988776',
            'jenis_kelamin'   => 'Perempuan',
            'email'           => 'siti@example.com',
            'nomor_telepon'   => '081299998888',
            'alamat'          => 'Jl. Merdeka No. 10, Bogor',
        ])->assertSessionHas('success');

        // Upload 5 dokumen untuk masing-masing anggota (total 10 berkas)
        foreach ($booking->jamaahDetails as $anggota) {
            $this->actingAs($budi)->post(route('user.dokumen.store'), [
                'jamaah_detail_id' => $anggota->id,
                'passport_number'  => 'C99887' . $anggota->id,
                'ktp'              => UploadedFile::fake()->image("ktp-{$anggota->id}.jpg"),
                'kk'               => UploadedFile::fake()->image("kk-{$anggota->id}.jpg"),
                'paspor'           => UploadedFile::fake()->image("paspor-{$anggota->id}.jpg"),
                'pas_foto'         => UploadedFile::fake()->image("foto-{$anggota->id}.jpg"),
                'buku_nikah'       => UploadedFile::fake()->image("nikah-{$anggota->id}.jpg"),
            ])->assertSessionHas('success');
        }

        $docs = Dokumen::whereIn('jamaah_detail_id', $booking->jamaahDetails->pluck('id'))->get();
        $this->assertCount(10, $docs);

        // ============================================================
        // 5. ADMIN VERIFIKASI DOKUMEN -> KEMENAG/VISA -> HOTEL/MANIFEST
        // ============================================================
        foreach ($docs->take(9) as $doc) {
            $this->actingAs($admin)->post(route('admin.dokumen.verify', $doc->id), [
                'status' => 'Diverifikasi',
            ])->assertSessionHas('success');
        }
        // Baru 9/10 -> belum naik ke Menunggu_Pelunasan
        $this->assertEquals('Menunggu_Pengumpulan_Dokumen', $booking->fresh()->status_booking);

        // Verifikasi dokumen ke-10 -> otomatis naik ke Menunggu_Pelunasan
        $this->actingAs($admin)->post(route('admin.dokumen.verify', $docs->last()->id), [
            'status' => 'Diverifikasi',
        ])->assertSessionHas('success');
        $this->assertEquals('Menunggu_Pelunasan', $booking->fresh()->status_booking);

        // Pelunasan biaya paket di Kelola Pembayaran (wajib lunas sebelum Surat Kemenag & Visa)
        $sisaTagihan = (int) max(0, $booking->total_harga - \App\Helpers\RozwiaHelper::bookingTotalPaid($booking));
        if ($sisaTagihan > 0) {
            $this->actingAs($admin)->post(route('admin.booking.confirm-payment', $booking->id), [
                'amount' => $sisaTagihan,
            ])->assertSessionHas('success');
        }

        // Update Kemenag & Visa + Hotel/Manifest untuk SEMUA anggota rombongan
        $budiDetail = $booking->jamaahDetails->firstWhere('nama_lengkap', 'Budi Santoso');
        $sitiDetail = $booking->jamaahDetails->firstWhere('nama_lengkap', 'Siti Aminah');

        $this->actingAs($admin)->post(route('admin.kemenag.update', $budiDetail->id), [
            'status_kemenag'      => 'Terbit',
            'status_visa'         => 'Terbit',
            'nomor_visa'          => 'V-2026-998877',
            'tanggal_terbit_visa' => '2026-12-01',
        ])->assertSessionHas('success');

        $this->actingAs($admin)->post(route('admin.hotel.jamaah.update', $budiDetail->id), [])->assertSessionHas('success');

        // Update Siti Aminah supaya semua anggota siap -> canSend = true
        if ($sitiDetail) {
            $this->actingAs($admin)->post(route('admin.kemenag.update', $sitiDetail->id), [
                'status_kemenag'      => 'Terbit',
                'status_visa'         => 'Terbit',
                'nomor_visa'          => 'V-2026-998878',
                'tanggal_terbit_visa' => '2026-12-01',
            ])->assertSessionHas('success');

            $this->actingAs($admin)->post(route('admin.hotel.jamaah.update', $sitiDetail->id), [])->assertSessionHas('success');
        }

        // Cek halaman manifest saat jemaah terisi penuh (modal alokasi ter-render)
        $this->actingAs($admin)->get(route('admin.manifest'))->assertStatus(200)->assertSee('Jadwal dan Manifest');

        // Kirim jadwal & manifest terfilter jadwal_id (canSend = true karena semua anggota siap)
        $this->actingAs($admin)->post(route('admin.manifest.send'), [
            'jadwal_id' => $jadwal->id,
        ])->assertSessionHas('success');

        // Cek Status Perjalanan Jamaah menampilkan Visa
        $trackingView = $this->actingAs($budi)->get(route('user.tracking'));
        $trackingView->assertStatus(200);
        $trackingView->assertSee('V-2026-998877');

        // ============================================================
        // 6. SUPERVISOR: DASHBOARD, PROFILE, PAKET UPLOAD+EDIT+DELETE, BIAYA, EXPORT
        // ============================================================
        $supervisor = User::where('role', 'supervisor')->firstOrFail();
        $this->actingAs($supervisor)->get(route('supervisor.dashboard'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('supervisor.profile'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('supervisor.paket'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('supervisor.users'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('supervisor.biaya'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('supervisor.rekening.index'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('supervisor.maskapai.index'))->assertStatus(200);

        $jenis = JenisLayanan::firstOrFail();
        $maskapai = Maskapai::firstOrFail();
        $hotel = Hotel::firstOrFail();

        $this->actingAs($supervisor)->post(route('supervisor.paket.store'), [
            'nama_paket'        => 'Umroh E2E Test Upload',
            'kategori'          => 'Umrah',
            'id_jenis_layanan'  => $jenis->id,
            'harga'             => 33000000,
            'durasi'            => 9,
            'deskripsi'         => 'Paket uji coba end to end lengkap dengan upload gambar resmi.',
            'status'            => 'active',
            'gambar_utama'      => UploadedFile::fake()->image('paket-e2e.jpg'),
            'tanggal_mulai'     => '2027-03-01',
            'tanggal_pulang'    => '2027-03-10',
            'id_maskapai'       => $maskapai->id,
            'nomor_penerbangan' => 'SV-900',
            'kuota'             => 30,
            'id_hotel_makkah'   => $hotel->id,
        ])->assertRedirect(route('supervisor.paket'));

        $createdPaket = Paket::where('nama_paket', 'Umroh E2E Test Upload')->firstOrFail();
        $this->assertStringStartsWith('images/pakets/', $createdPaket->gambar_utama);
        $uploadedFullPath = public_path($createdPaket->gambar_utama);
        $this->assertTrue(File::exists($uploadedFullPath));

        // Hapus paket uji coba -> file gambar juga harus ikut terhapus bersih
        $this->actingAs($supervisor)->delete(route('supervisor.paket.delete', $createdPaket->id))
            ->assertSessionHas('success');
        $this->assertFalse(File::exists($uploadedFullPath));

        // Export CSV dengan filter
        $this->actingAs($supervisor)->get(route('supervisor.biaya.export', [
            'tanggal_mulai' => now()->subDay()->format('Y-m-d'),
            'tanggal_akhir' => now()->addDay()->format('Y-m-d'),
        ]))->assertStatus(200);

        $this->actingAs($supervisor)->get(route('supervisor.users.export', [
            'status' => 'active',
        ]))->assertStatus(200);
    }
}
