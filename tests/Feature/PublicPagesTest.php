<?php

namespace Tests\Feature;

use App\Models\Paket;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_home_page_can_be_accessed(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Satu Niat, Satu Tujuan');
        $response->assertSee('Menuju Baitullah');
        $response->assertSee('Pilihan Paket Umroh');
    }

    public function test_public_umroh_page_can_be_accessed(): void
    {
        $response = $this->get(route('paket.umroh'));

        $response->assertStatus(200);
        $response->assertSee('Paket Perjalanan Umroh');
    }

    public function test_public_haji_page_can_be_accessed(): void
    {
        $response = $this->get(route('paket.haji'));

        $response->assertStatus(200);
        $response->assertSee('Paket Haji Khusus');
        $response->assertSee('Perjalanan Haji Penuh Makna');
        $response->assertSee('Mabrur Hajj Journey');
    }

    public function test_newly_created_supervisor_package_is_displayed_on_public_catalog(): void
    {
        $jenis = \App\Models\JenisLayanan::first();

        $newHaji = Paket::create([
            'id_jenis_layanan' => $jenis->id,
            'nama_paket'       => 'Haji Testing Mandiri 2027',
            'kategori'         => 'Haji',
            'harga_dasar'      => 85000000,
            'deskripsi'        => 'Paket haji baru yang dibuat dari supervisor.',
            'status'           => 'active',
            'gambar_utama'     => 'images/paket haji.jpg',
        ]);

        $responseHaji = $this->get(route('paket.haji'));
        $responseHaji->assertStatus(200);
        $responseHaji->assertSee('Haji Testing Mandiri 2027');

        $newUmroh = Paket::create([
            'id_jenis_layanan' => $jenis->id,
            'nama_paket'       => 'Umrah Testing Spesial 2027',
            'kategori'         => 'Umroh',
            'harga_dasar'      => 32000000,
            'deskripsi'        => 'Paket umrah baru yang dibuat dari supervisor.',
            'status'           => 'active',
            'gambar_utama'     => 'images/pilihan paket.jpg',
        ]);

        $responseUmroh = $this->get(route('paket.umroh'));
        $responseUmroh->assertStatus(200);
        $responseUmroh->assertSee('Umrah Testing Spesial 2027');

        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('Umrah Testing Spesial 2027');
    }

    public function test_public_detail_page_shows_package_info_and_rangkaian_ibadah(): void
    {
        $paket = Paket::where('nama_paket', 'January Umroh & Istanbul')->first();
        $this->assertNotNull($paket);

        $response = $this->get(route('paket.detail', $paket->id));

        $response->assertStatus(200);
        $response->assertSee('January Umroh & Istanbul');
        $response->assertSee('Gambaran Perjalanan');
        // Check dynamic rangkaian ibadah from database
        $response->assertSee('Keberangkatan dari Indonesia');
        $response->assertSee('Tiba di Madinah & Ziarah Raudhah');
    }

    public function test_public_tentang_and_faq_can_be_accessed(): void
    {
        $responseTentang = $this->get(route('tentang.kami'));
        $responseTentang->assertStatus(200);

        $responseFaq = $this->get(route('faq'));
        $responseFaq->assertStatus(200);
    }

    public function test_nonexistent_package_detail_returns_404(): void
    {
        $response = $this->get('/paket/detail/999999');

        $response->assertStatus(404);
    }
}
