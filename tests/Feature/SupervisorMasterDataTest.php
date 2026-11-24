<?php

namespace Tests\Feature;

use App\Helpers\RozwiaHelper;
use App\Models\Invoice;
use App\Models\Maskapai;
use App\Models\RekeningPerusahaan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorMasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $admin;
    protected User $jamaah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->supervisor = User::where('email', 'supervisor@rozwia.com')->first();
        $this->admin = User::where('email', 'admin@rozwia.com')->first();
        $this->jamaah = User::where('email', 'jamaah@rozwia.com')->first();
    }

    public function test_guest_cannot_access_supervisor_rekening(): void
    {
        $response = $this->get(route('supervisor.rekening.index'));
        $response->assertRedirect('/login');
    }

    public function test_jamaah_forbidden_from_supervisor_master_data(): void
    {
        $response = $this->actingAs($this->jamaah)->get(route('supervisor.rekening.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->jamaah)->get(route('supervisor.maskapai.index'));
        $response->assertStatus(403);
    }

    public function test_admin_forbidden_from_supervisor_master_data(): void
    {
        $response = $this->actingAs($this->admin)->get(route('supervisor.rekening.index'));
        $response->assertStatus(403);
    }

    public function test_supervisor_can_view_rekening_and_maskapai_index(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.rekening.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Rekening Resmi Travel');

        $response = $this->actingAs($this->supervisor)->get(route('supervisor.maskapai.index'));
        $response->assertStatus(200);
        $response->assertSee('Maskapai Penerbangan');
    }

    public function test_supervisor_can_create_rekening_perusahaan(): void
    {
        $data = [
            'nama_bank' => 'Bank Syariah Indonesia (BSI)',
            'nomor_rekening' => '7123456789',
            'atas_nama' => 'PT Pangrango Berkah Digital',
            'nama_perusahaan' => 'PT Pangrango Berkah Digital',
            'alamat_perusahaan' => 'Jl. Pajajaran No. 123, Bogor',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->supervisor)->post(route('supervisor.rekening.store'), $data);

        $response->assertRedirect(route('supervisor.rekening.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rekening_perusahaan', [
            'nama_bank' => 'Bank Syariah Indonesia (BSI)',
            'nomor_rekening' => '7123456789',
        ]);
    }

    public function test_rekening_validation_rejects_invalid_input(): void
    {
        $invalidData = [
            'nama_bank' => '',
            'nomor_rekening' => 'INVALID_REK!#@$',
            'atas_nama' => '',
            'nama_perusahaan' => '',
            'status' => 'unknown_status',
        ];

        $response = $this->actingAs($this->supervisor)->post(route('supervisor.rekening.store'), $invalidData);

        $response->assertSessionHasErrors(['nama_bank', 'nomor_rekening', 'atas_nama', 'nama_perusahaan', 'status']);
    }

    public function test_supervisor_can_update_rekening(): void
    {
        $rekening = RekeningPerusahaan::create([
            'nama_bank' => 'BCA',
            'nomor_rekening' => '8823123456',
            'atas_nama' => 'PT Rozwia Travel',
            'nama_perusahaan' => 'PT Rozwia Travel',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->supervisor)->put(route('supervisor.rekening.update', $rekening), [
            'nama_bank' => 'BCA Syariah',
            'nomor_rekening' => '8823123456',
            'atas_nama' => 'PT Rozwia Travel Berkah',
            'nama_perusahaan' => 'PT Rozwia Travel Berkah',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('supervisor.rekening.index'));
        $this->assertDatabaseHas('rekening_perusahaan', [
            'id' => $rekening->id,
            'nama_bank' => 'BCA Syariah',
            'status' => 'inactive',
        ]);
    }

    public function test_supervisor_can_crud_maskapai(): void
    {
        // 1. Create
        $response = $this->actingAs($this->supervisor)->post(route('supervisor.maskapai.store'), [
            'nama_maskapai' => 'Turkish Airlines',
        ]);
        $response->assertRedirect(route('supervisor.maskapai.index'));
        $this->assertDatabaseHas('maskapai', ['nama_maskapai' => 'Turkish Airlines']);

        // 2. Duplicate validation
        $responseDup = $this->actingAs($this->supervisor)->post(route('supervisor.maskapai.store'), [
            'nama_maskapai' => 'Turkish Airlines',
        ]);
        $responseDup->assertSessionHasErrors('nama_maskapai');

        // 3. Update
        $maskapai = Maskapai::where('nama_maskapai', 'Turkish Airlines')->first();
        $responseUpdate = $this->actingAs($this->supervisor)->put(route('supervisor.maskapai.update', $maskapai), [
            'nama_maskapai' => 'Etihad Airways',
        ]);
        $responseUpdate->assertRedirect(route('supervisor.maskapai.index'));
        $this->assertDatabaseHas('maskapai', ['nama_maskapai' => 'Etihad Airways']);

        // 4. Delete
        $responseDelete = $this->actingAs($this->supervisor)->delete(route('supervisor.maskapai.destroy', $maskapai));
        $responseDelete->assertRedirect(route('supervisor.maskapai.index'));
        $this->assertDatabaseMissing('maskapai', ['id' => $maskapai->id]);
    }

    public function test_rozwia_helpers(): void
    {
        // Test Rupiah format
        $this->assertEquals('Rp 35.000.000', RozwiaHelper::formatRupiah(35000000));
        $this->assertEquals('35.000.000', RozwiaHelper::formatRupiah(35000000, false));

        // Test Code generator
        $bookingCode = RozwiaHelper::generateBookingCode();
        $this->assertStringStartsWith('BK-', $bookingCode);

        $invoiceCode = RozwiaHelper::generateInvoiceNumber();
        $this->assertStringStartsWith('INV-', $invoiceCode);
    }
}
