<?php

namespace Tests\Feature;

use App\Mail\DocumentStatusMail;
use App\Mail\KemenagVisaUpdatedMail;
use App\Mail\PaymentVerified;
use App\Models\Booking;
use App\Models\Dokumen;
use App\Models\Hotel;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\JenisLayanan;
use App\Models\Maskapai;
use App\Models\Paket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItRevisionsVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        Storage::fake('private');
    }

    public function test_booking_rejects_empty_or_invalid_whatsapp_digits(): void
    {
        $paket = Paket::where('status', 'active')->firstOrFail();

        $this->post(route('booking.store', $paket->id), [
            'nama_lengkap'  => 'Tester Kosong HP',
            'email'         => 'hp.kosong@example.com',
            'nomor_telepon' => '   ',
            'jumlah_jamaah' => 1,
        ])->assertSessionHasErrors('nomor_telepon');

        $this->post(route('booking.store', $paket->id), [
            'nama_lengkap'  => 'Tester Pendek HP',
            'email'         => 'hp.pendek@example.com',
            'nomor_telepon' => '1234',
            'jumlah_jamaah' => 1,
        ])->assertSessionHasErrors('nomor_telepon');
    }

    public function test_supervisor_paket_uses_all_in_room_prices_without_step1_price_or_flight_number(): void
    {
        $supervisor = User::where('role', 'supervisor')->firstOrFail();
        $layanan    = JenisLayanan::firstOrFail();
        $maskapai   = Maskapai::firstOrFail();
        $makkah     = Hotel::where('lokasi', 'Makkah')->firstOrFail();
        $madinah    = Hotel::where('lokasi', 'Madinah')->firstOrFail();

        $this->actingAs($supervisor)
            ->post(route('supervisor.paket.store'), [
                'nama_paket'       => 'Umrah Hemat All-In 40 Juta',
                'kategori'         => 'Umrah',
                'id_jenis_layanan' => $layanan->id,
                'status'           => 'active',
                'tanggal_mulai'    => now()->addDays(30)->format('Y-m-d'),
                'tanggal_pulang'   => now()->addDays(39)->format('Y-m-d'),
                'id_maskapai'      => $maskapai->id,
                'kuota'            => 35,
                'id_hotel_makkah'  => $makkah->id,
                'id_hotel_madinah' => $madinah->id,
                'harga_quad'       => 40000000,
                'harga_triple'     => 43000000,
                'harga_double'     => 47000000,
            ])
            ->assertRedirect(route('supervisor.paket'))
            ->assertSessionHas('success');

        $paket = Paket::where('nama_paket', 'Umrah Hemat All-In 40 Juta')->firstOrFail();
        $this->assertEquals(40000000, (float) $paket->harga_dasar);

        // Pastikan halaman detail publik menampilkan 40 Juta dari Quad
        $detail = $this->get(route('paket.detail', $paket->id));
        $detail->assertStatus(200);
        $detail->assertSee('40.000.000');
    }

    public function test_administrator_role_matrix_updates_roles_and_protects_self(): void
    {
        $administrator = User::where('role', 'administrator')->firstOrFail();
        $jamaah        = User::where('role', 'jamaah')->where('status_akun', 'active')->firstOrFail();
        $admin         = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($administrator)
            ->get(route('administrator.role-matrix'))
            ->assertStatus(200)
            ->assertSee('Matriks Ceklis Role Akun');

        // Ubah jamaah -> admin, admin -> supervisor
        $this->actingAs($administrator)
            ->post(route('administrator.role-matrix.update'), [
                'roles' => [
                    $administrator->id => 'administrator',
                    $jamaah->id        => 'admin',
                    $admin->id         => 'supervisor',
                ],
            ])
            ->assertSessionHas('success');

        $this->assertSame('admin', $jamaah->fresh()->role);
        $this->assertSame('supervisor', $admin->fresh()->role);

        // Cegah administrator menurunkan role akun sendiri
        $this->actingAs($administrator)
            ->post(route('administrator.role-matrix.update'), [
                'roles' => [
                    $administrator->id => 'jamaah',
                ],
            ])
            ->assertSessionHas('error');

        $this->assertSame('administrator', $administrator->fresh()->role);
    }

    public function test_last_login_timestamp_is_recorded_and_shown_in_aktivasi_akun(): void
    {
        $admin  = User::where('role', 'admin')->firstOrFail();
        $jamaah = User::where('role', 'jamaah')->where('status_akun', 'active')->firstOrFail();

        $this->assertNull($jamaah->last_login_at);

        $this->post('/login', [
            'email'    => $jamaah->email,
            'password' => 'Jamaah123!',
        ])->assertRedirect();

        $jamaah->refresh();
        $this->assertNotNull($jamaah->last_login_at);

        // Admin melihat kolom Login Terakhir & tombol DP Verified
        $this->actingAs($admin)
            ->get(route('admin.aktivasi'))
            ->assertStatus(200)
            ->assertSee('Login Terakhir')
            ->assertSee('WIB');
    }

    public function test_jamaah_profile_redirects_to_dokumen_and_dokumen_requires_passport_and_multi_popup(): void
    {
        $paket  = Paket::where('status', 'active')->firstOrFail();
        $jadwal = Jadwal::where('id_paket', $paket->id)->firstOrFail();
        $jamaah = User::where('role', 'jamaah')->where('status_akun', 'active')->firstOrFail();

        $booking = Booking::create([
            'id_pengguna'    => $jamaah->id,
            'id_jadwal'      => $jadwal->id,
            'kode_booking'   => 'BK-FLOW-01',
            'jumlah_jamaah'  => 2,
            'total_harga'    => 70000000,
            'status_booking' => 'Menunggu_Pengumpulan_Dokumen',
        ]);

        $d1 = JamaahDetail::create([
            'id_booking'    => $booking->id,
            'nama_lengkap'  => 'Rudi Hartono',
            'nik'           => '3201010101010001',
            'tempat_lahir'  => 'Bogor',
            'tanggal_lahir' => '1988-03-10',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '+6281234567890',
        ]);
        $d2 = JamaahDetail::create([
            'id_booking'    => $booking->id,
            'nama_lengkap'  => 'Anggota Jamaah 2',
            'jenis_kelamin' => 'Perempuan',
        ]);

        // Update anggota ke-2 hingga seluruh profil lengkap -> otomatis redirect ke user.dokumen
        $this->actingAs($jamaah)
            ->post(route('user.jamaah.update', $d2->id), [
                'nama_lengkap'  => 'Sari Dewi',
                'nik'           => '3201010101010002',
                'tempat_lahir'  => 'Bandung',
                'tanggal_lahir' => '1990-05-12',
                'jenis_kelamin' => 'Perempuan',
                'nomor_telepon' => '081234567891',
            ])
            ->assertRedirect(route('user.dokumen', ['jamaah_id' => $d2->id]));

        // Upload dokumen tanpa passport_number -> wajib gagal validasi
        $this->actingAs($jamaah)
            ->post(route('user.dokumen.store'), [
                'jamaah_detail_id' => $d1->id,
                'ktp'              => UploadedFile::fake()->image('ktp.jpg'),
            ])
            ->assertSessionHasErrors('passport_number');

        // Upload dokumen dengan passport_number pada booking 2 jamaah -> muncul rombongan_popup
        $this->actingAs($jamaah)
            ->post(route('user.dokumen.store'), [
                'jamaah_detail_id' => $d1->id,
                'passport_number'  => 'A1234567',
                'ktp'              => UploadedFile::fake()->image('ktp.jpg'),
            ])
            ->assertSessionHas('rombongan_popup');
    }

    public function test_kemenag_visa_gate_blocks_unpaid_and_blocks_visa_number_unless_terbit_and_sends_gender_mail(): void
    {
        $admin  = User::where('role', 'admin')->firstOrFail();
        $paket  = Paket::where('status', 'active')->firstOrFail();
        $jadwal = Jadwal::where('id_paket', $paket->id)->firstOrFail();
        $jamaah = User::where('role', 'jamaah')->where('status_akun', 'active')->firstOrFail();

        $booking = Booking::create([
            'id_pengguna'    => $jamaah->id,
            'id_jadwal'      => $jadwal->id,
            'kode_booking'   => 'BK-GATE-01',
            'jumlah_jamaah'  => 1,
            'total_harga'    => 35000000,
            'status_booking' => 'Menunggu_Pengumpulan_Dokumen',
        ]);

        $detail = JamaahDetail::create([
            'id_booking'    => $booking->id,
            'nama_lengkap'  => 'Siti Fatimah',
            'jenis_kelamin' => 'Perempuan',
            'nomor_telepon' => '+6281234567890',
        ]);

        // 1. Belum lunas -> update Kemenag/Visa wajib diblokir
        $this->actingAs($admin)
            ->post(route('admin.kemenag.update', $detail->id), [
                'status_kemenag' => 'Terbit',
                'status_visa'    => 'Diproses',
            ])
            ->assertSessionHas('error');

        // 2. Lunasi booking via admin.booking.confirm-payment
        $this->actingAs($admin)
            ->post(route('admin.booking.confirm-payment', $booking->id), [
                'amount' => 35000000,
            ])
            ->assertSessionHas('success');

        Mail::assertSent(PaymentVerified::class, function (PaymentVerified $mail) {
            return $mail->sapaan === 'Ibu' && str_contains($mail->loginUrl, '/login');
        });

        // 3. Lunas tapi Visa = Diproses dan coba isi nomor_visa -> wajib ditolak validasi
        $this->actingAs($admin)
            ->post(route('admin.kemenag.update', $detail->id), [
                'status_kemenag' => 'Diproses',
                'status_visa'    => 'Diproses',
                'nomor_visa'     => 'V-ILLEGAL-99',
            ])
            ->assertSessionHasErrors('nomor_visa');
        $this->assertNull($detail->fresh()->nomor_visa);

        // 4. Ubah Visa = Terbit dengan nomor_visa -> berhasil & kirim email dengan sapaan Ibu
        $this->actingAs($admin)
            ->post(route('admin.kemenag.update', $detail->id), [
                'status_kemenag'      => 'Terbit',
                'status_visa'         => 'Terbit',
                'nomor_visa'          => 'V-VALID-888',
                'tanggal_terbit_visa' => '2026-12-01',
            ])
            ->assertSessionHas('success');

        $this->assertSame('V-VALID-888', $detail->fresh()->nomor_visa);
        Mail::assertSent(KemenagVisaUpdatedMail::class, function (KemenagVisaUpdatedMail $mail) {
            return $mail->sapaan === 'Ibu';
        });

        // 5. Dokumentasi revisi vs verifikasi semua
        $doc1 = Dokumen::create([
            'jamaah_detail_id' => $detail->id,
            'tipe_dokumen'     => 'KTP',
            'file_path'        => 'dokumen/ktp.jpg',
            'status'           => 'Menunggu_Verifikasi',
        ]);
        $this->actingAs($admin)->post(route('admin.dokumen.verify', $doc1->id), [
            'status'      => 'Perlu_Diperbaiki',
            'admin_notes' => 'Foto buram',
        ]);
        Mail::assertSent(DocumentStatusMail::class, fn (DocumentStatusMail $m) => $m->statusType === 'needs_revision' && $m->sapaan === 'Ibu');

        // Saat masih ada berkas berstatus Perlu_Diperbaiki -> verify-all wajib diblokir
        $this->actingAs($admin)->post(route('admin.dokumen.verify-all', $detail->id))->assertSessionHas('error');

        // Lengkapi 5 dokumen dan verifikasi semua -> email all_verified terkirim sekali
        $doc1->refresh()->update(['status' => 'Menunggu_Verifikasi']);
        foreach (['Kartu Keluarga', 'Paspor', 'Pas Foto 4x6', 'Buku Nikah / Akta Lahir'] as $t) {
            Dokumen::create([
                'jamaah_detail_id' => $detail->id,
                'tipe_dokumen'     => $t,
                'file_path'        => 'dokumen/test.jpg',
                'status'           => 'Menunggu_Verifikasi',
            ]);
        }
        $this->actingAs($admin)->post(route('admin.dokumen.verify-all', $detail->id))->assertSessionHas('success');
        Mail::assertSent(DocumentStatusMail::class, fn (DocumentStatusMail $m) => $m->statusType === 'all_verified' && $m->sapaan === 'Ibu');
    }

    public function test_admin_cannot_activate_account_before_dp_verified(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $unpaidUser = User::create([
            'name'        => 'Jamaah Belum DP',
            'email'       => 'belum.dp@example.com',
            'password'    => bcrypt('Password123!'),
            'role'        => 'jamaah',
            'status_akun' => 'inactive',
        ]);

        // Gagal aktivasi jika belum ada pembayaran Berhasil (DP Verified)
        $this->actingAs($admin)
            ->post(route('admin.aktivasi.store', $unpaidUser->id), [
                'status_akun' => 'active',
            ])
            ->assertSessionHas('error');

        $this->assertSame('inactive', $unpaidUser->fresh()->status_akun);
    }

    public function test_manifest_selects_by_paket_queues_incomplete_and_sends_only_to_kemenag_visa_terbit(): void
    {
        $admin  = User::where('role', 'admin')->firstOrFail();
        $paket  = Paket::where('status', 'active')->firstOrFail();
        $jadwal = Jadwal::where('id_paket', $paket->id)->firstOrFail();

        $userReady = User::create([
            'name'        => 'Akun Siap Berangkat',
            'email'       => 'siap.manifest@example.com',
            'password'    => bcrypt('Password123!'),
            'role'        => 'jamaah',
            'status_akun' => 'active',
        ]);
        $bReady = Booking::create([
            'id_pengguna'    => $userReady->id,
            'id_jadwal'      => $jadwal->id,
            'kode_booking'   => 'BK-MNF-RDY',
            'jumlah_jamaah'  => 1,
            'total_harga'    => 35000000,
            'status_booking' => 'Menunggu_Pengumpulan_Dokumen',
        ]);
        JamaahDetail::create([
            'id_booking'     => $bReady->id,
            'nama_lengkap'   => 'Ahmad Siap',
            'status_kemenag' => 'Terbit',
            'status_visa'    => 'Terbit',
            'nomor_visa'     => 'V-111',
        ]);

        $userQueued = User::create([
            'name'        => 'Akun Dalam Antrian',
            'email'       => 'antrian.manifest@example.com',
            'password'    => bcrypt('Password123!'),
            'role'        => 'jamaah',
            'status_akun' => 'active',
        ]);
        $bQueued = Booking::create([
            'id_pengguna'    => $userQueued->id,
            'id_jadwal'      => $jadwal->id,
            'kode_booking'   => 'BK-MNF-QUE',
            'jumlah_jamaah'  => 1,
            'total_harga'    => 35000000,
            'status_booking' => 'Menunggu_Pengumpulan_Dokumen',
        ]);
        JamaahDetail::create([
            'id_booking'     => $bQueued->id,
            'nama_lengkap'   => 'Budi Belum Lengkap',
            'status_kemenag' => 'Diproses',
            'status_visa'    => 'Belum Diajukan',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.manifest', ['paket_id' => $paket->id]));
        $res->assertStatus(200);
        $res->assertSee('Akun Dalam Antrian');
        $res->assertSee('Belum Memenuhi Syarat');
        $res->assertSee('Akun Siap Berangkat');

        $this->actingAs($admin)
            ->post(route('admin.manifest.send'), [
                'paket_id' => $paket->id,
                'subjek'   => 'Manifest Resmi ' . $paket->nama_paket,
                'pesan'    => 'Jadwal siap untuk Bapak/Ibu [nama lengkap jamaah].',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifikasi', [
            'id_pengguna' => $userReady->id,
            'title'       => 'Jadwal & Manifest Keberangkatan Terbit',
        ]);
        $this->assertDatabaseMissing('notifikasi', [
            'id_pengguna' => $userQueued->id,
            'title'       => 'Jadwal & Manifest Keberangkatan Terbit',
        ]);
    }

    public function test_supervisor_paket_saves_linkedin_fasilitas_and_ui_revisions_present(): void
    {
        $supervisor = User::where('role', 'supervisor')->firstOrFail();
        $layanan    = JenisLayanan::firstOrFail();
        $maskapai   = Maskapai::firstOrFail();
        $makkah     = Hotel::where('lokasi', 'Makkah')->firstOrFail();
        $madinah    = Hotel::where('lokasi', 'Madinah')->firstOrFail();

        $this->actingAs($supervisor)
            ->post(route('supervisor.paket.store'), [
                'nama_paket'       => 'Umrah Fasilitas Dinamis',
                'kategori'         => 'Umrah',
                'id_jenis_layanan' => $layanan->id,
                'fasilitas'        => ['Visa Umrah', 'Katering Nusantara', 'Hotel Bintang 5'],
                'status'           => 'active',
                'tanggal_mulai'    => now()->addDays(20)->format('Y-m-d'),
                'tanggal_pulang'   => now()->addDays(29)->format('Y-m-d'),
                'id_maskapai'      => $maskapai->id,
                'kuota'            => 30,
                'id_hotel_makkah'  => $makkah->id,
                'id_hotel_madinah' => $madinah->id,
                'harga_quad'       => '36.000.000',
                'harga_triple'     => '39.000.000',
                'harga_double'     => '43.000.000',
            ])
            ->assertRedirect(route('supervisor.paket'))
            ->assertSessionHas('success');

        $paket = Paket::where('nama_paket', 'Umrah Fasilitas Dinamis')->firstOrFail();
        $this->assertSame(['Visa Umrah', 'Katering Nusantara', 'Hotel Bintang 5'], $paket->fasilitas);
        $this->assertEquals(36000000, $paket->quadPrice());

        // Cek halaman DP tanpa badge "Kode: BK-"
        $dpPage = $this->post(route('booking.store', $paket->id), [
            'nama_lengkap'  => 'Siti Aminah',
            'email'         => 'siti.dp@example.com',
            'nomor_telepon' => '81234567899',
            'jumlah_jamaah' => 1,
            'tipe_kamar'    => 'quad',
        ]);
        $dpPage->assertRedirect();
        $this->get($dpPage->headers->get('Location'))
            ->assertStatus(200)
            ->assertDontSee('Kode: BK-');
    }
}
