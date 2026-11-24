<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\JenisLayanan;
use App\Models\Maskapai;
use App\Models\Paket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorFullFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $jamaah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        @unlink(\App\Helpers\RozwiaHelper::companyProfilePath());

        $this->supervisor = User::where('email', 'supervisor@rozwia.com')->first();
        $this->jamaah = User::where('email', 'jamaah@rozwia.com')->first();
    }

    public function test_supervisor_can_access_dashboard_with_all_metrics(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang di Dashboard');
        $response->assertSee('Jumlah Paket Tersedia');
        $response->assertSee('Jumlah Total Pendapatan');
        $response->assertSee('Daftar Akun Terdaftar');
        $response->assertSee('Jumlah Pendaftaran Jamaah');
    }

    public function test_supervisor_can_view_and_update_company_profile(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.profile'));
        $response->assertStatus(200);
        $response->assertSee('Profile Perusahaan');
        $response->assertSee('Rozwia Islamic Travel');

        $updateData = [
            'nama_perusahaan'    => 'PT ROZWIA Berkah Utama',
            'no_izin'            => 'PPIU No. 9999/2026',
            'email_perusahaan'   => 'info@rozwia.co.id',
            'telepon_perusahaan' => '081234567899',
            'alamat_perusahaan'  => 'Gedung Wisma Rozwia Lt. 5, Jakarta Selatan',
            'nama_bank'          => 'BSI',
            'nomor_rekening'     => '1234567890',
            'atas_nama'          => 'PT ROZWIA Berkah Utama',
        ];

        $postResponse = $this->actingAs($this->supervisor)->post(route('supervisor.profile.update'), $updateData);
        $postResponse->assertSessionHas('success');

        $reloadResponse = $this->actingAs($this->supervisor)->get(route('supervisor.profile'));
        $reloadResponse->assertSee('PT ROZWIA Berkah Utama');
        $reloadResponse->assertSee('info@rozwia.co.id');
    }

    public function test_supervisor_can_view_packages_and_create_new_package(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.paket'));
        $response->assertStatus(200);
        $response->assertSee('Kelola Paket Haji & Umroh');

        $jenis = JenisLayanan::first();
        $maskapai = Maskapai::first();
        $hotel = Hotel::first();

        $paketData = [
            'nama_paket'        => 'Umroh Awal Tahun 2027 Super VIP',
            'kategori'          => 'Umrah',
            'id_jenis_layanan'  => $jenis->id,
            'harga'             => 42000000,
            'durasi'            => 12,
            'deskripsi'         => 'Paket umroh eksklusif awal tahun dengan fasilitas hotel bintang lima dekat masjid.',
            'status'            => 'active',
            'tanggal_mulai'     => '2027-01-15',
            'tanggal_pulang'    => '2027-01-27',
            'id_maskapai'       => $maskapai->id,
            'nomor_penerbangan' => 'SV-820',
            'kuota'             => 45,
            'id_hotel_makkah'   => $hotel->id,
            'harga_quad'        => 42000000,
            'harga_triple'      => 46000000,
            'harga_double'      => 50000000,
        ];

        $postResponse = $this->actingAs($this->supervisor)->post(route('supervisor.paket.store'), $paketData);
        $postResponse->assertRedirect(route('supervisor.paket'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('paket', [
            'nama_paket' => 'Umroh Awal Tahun 2027 Super VIP',
            'kategori'   => 'Umrah',
        ]);
    }

    public function test_supervisor_can_create_package_with_short_or_default_description(): void
    {
        $jenis = JenisLayanan::first();
        $maskapai = Maskapai::first();

        $paketData = [
            'nama_paket'        => 'Haji Testing Dari Supervisor',
            'kategori'          => 'Haji',
            'id_jenis_layanan'  => $jenis->id,
            'harga'             => 50000000,
            'durasi'            => 25,
            'deskripsi'         => '',
            'status'            => 'active',
            'tanggal_mulai'     => '2027-06-01',
            'tanggal_pulang'    => '2027-06-26',
            'id_maskapai'       => $maskapai->id,
            'nomor_penerbangan' => 'SV-819',
            'kuota'             => 40,
            'harga_quad'        => 30000000,
            'harga_triple'      => 35000000,
            'harga_double'      => 40000000,
        ];

        $response = $this->actingAs($this->supervisor)->post(route('supervisor.paket.store'), $paketData);
        $response->assertRedirect(route('supervisor.paket'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('paket', [
            'nama_paket' => 'Haji Testing Dari Supervisor',
            'kategori'   => 'Haji',
        ]);
    }

    public function test_supervisor_can_toggle_package_status_and_delete_package(): void
    {
        $jenis = JenisLayanan::first();
        $freshPaket = Paket::create([
            'id_jenis_layanan' => $jenis->id,
            'nama_paket'       => 'Paket Uji Coba Hapus',
            'kategori'         => 'Umrah',
            'harga_dasar'      => 30000000,
            'status'           => 'active',
        ]);

        $initialStatus = $freshPaket->status;

        $toggleResponse = $this->actingAs($this->supervisor)->post(route('supervisor.paket.toggle', $freshPaket->id));
        $toggleResponse->assertSessionHas('success');

        $freshPaket->refresh();
        $this->assertNotEquals($initialStatus, $freshPaket->status);

        $deleteResponse = $this->actingAs($this->supervisor)->delete(route('supervisor.paket.delete', $freshPaket->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('paket', ['id' => $freshPaket->id]);
    }

    public function test_supervisor_can_view_users_read_only_and_export_excel(): void
    {
        // Supervisor dapat melihat halaman users (read-only monitoring)
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.users'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Pengguna');
        $response->assertSee('Total Akun');

        // Supervisor TIDAK memiliki route toggle status akun (read-only by design)
        $this->assertFalse(
            collect(app('router')->getRoutes()->getRoutesByName())->has('supervisor.users.toggle'),
            'Supervisor must NOT have an account toggle route (read-only panel)'
        );

        // Export Excel tetap bisa diakses supervisor
        $exportResponse = $this->actingAs($this->supervisor)->get(route('supervisor.users.export'));
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('content-disposition'), 'daftar_akun_jamaah_rozwia_'));
    }

    public function test_supervisor_can_view_financial_report_and_export_csv(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.biaya'));
        $response->assertStatus(200);
        $response->assertSee('Report Total Biaya');
        $response->assertSee('Laporan Jamaah & Keuangan');
        $response->assertSee('Total Biaya Keseluruhan');

        $exportResponse = $this->actingAs($this->supervisor)->get(route('supervisor.biaya.export'));
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('content-disposition'), 'laporan_keuangan_paket_rozwia_'));
    }

    public function test_supervisor_can_update_package_and_access_rekening_maskapai_pages(): void
    {
        $paket = Paket::first();
        $jenis = JenisLayanan::first();
        $maskapai = Maskapai::first();
        $hotel = Hotel::first();

        $updateResponse = $this->actingAs($this->supervisor)->put(route('supervisor.paket.update', $paket->id), [
            'nama_paket'        => 'Umrah Ramadhan Barokah Edited',
            'kategori'          => 'Umrah',
            'id_jenis_layanan'  => $jenis->id,
            'harga'             => 39500000,
            'durasi'            => 12,
            'deskripsi'         => 'Deskripsi paket yang sudah diperbarui oleh supervisor dengan fasilitas lengkap.',
            'status'            => 'active',
            'tanggal_mulai'     => '2027-02-10',
            'tanggal_pulang'    => '2027-02-22',
            'id_maskapai'       => $maskapai->id,
            'nomor_penerbangan' => 'GA-999',
            'kuota'             => 35,
            'id_hotel_makkah'   => $hotel->id,
            'harga_quad'        => 39500000,
        ]);
        $updateResponse->assertRedirect(route('supervisor.paket'));
        $this->assertDatabaseHas('paket', ['id' => $paket->id, 'nama_paket' => 'Umrah Ramadhan Barokah Edited']);

        $this->actingAs($this->supervisor)->get(route('supervisor.rekening.index'))->assertStatus(200);
        $this->actingAs($this->supervisor)->get(route('supervisor.maskapai.index'))->assertStatus(200);
    }
}
