<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMail;
use App\Models\Booking;
use App\Models\Dokumen;
use App\Models\EmailLog;
use App\Models\Hotel;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\JenisLayanan;
use App\Models\Maskapai;
use App\Models\Paket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdministratorUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        Storage::fake('private');
    }

    public function test_non_administrator_roles_cannot_access_administrator_panel(): void
    {
        // Guest redirected to login
        $this->get(route('administrator.dashboard'))->assertRedirect(route('login'));

        $admin      = User::where('role', 'admin')->firstOrFail();
        $supervisor = User::where('role', 'supervisor')->firstOrFail();
        $jamaah     = User::where('role', 'jamaah')->where('status_akun', 'active')->firstOrFail();

        foreach ([$admin, $supervisor, $jamaah] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->get(route('administrator.dashboard'))
                ->assertStatus(403);

            $this->actingAs($unauthorizedUser)
                ->post(route('administrator.users.store'), [
                    'name'        => 'Hacker Escalation',
                    'email'       => 'hacker@example.com',
                    'role'        => 'administrator',
                    'status_akun' => 'active',
                ])
                ->assertStatus(403);
        }
    }

    public function test_administrator_login_redirect_and_dashboard_visibility(): void
    {
        $administrator = User::where('role', 'administrator')->firstOrFail();

        $this->actingAs($administrator)
            ->get(route('home'))
            ->assertRedirect(route('administrator.dashboard'));

        $response = $this->actingAs($administrator)
            ->get(route('administrator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Akun');
        $response->assertSee('Super Admin');
        $response->assertSee('admin@rozwia.com');
        $response->assertSee('supervisor@rozwia.com');
        $response->assertSee('jamaah@rozwia.com');
    }

    public function test_administrator_can_create_staff_and_jamaah_with_strong_temp_password(): void
    {
        $administrator = User::where('role', 'administrator')->firstOrFail();

        $response = $this->actingAs($administrator)
            ->post(route('administrator.users.store'), [
                'name'          => 'Admin Operasional Baru',
                'email'         => 'ADMIN.BARU@ROZWIA.COM',
                'nomor_telepon' => '081355556666',
                'role'          => 'admin',
                'status_akun'   => 'active',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $created = User::where('email', 'admin.baru@rozwia.com')->firstOrFail();
        $this->assertSame('admin', $created->role);
        $this->assertSame('active', $created->status_akun);
        $this->assertTrue((bool) $created->must_change_password);
        $this->assertFalse(Hash::check('Admin123!', $created->password));

        Mail::assertSent(AccountCredentialsMail::class, function (AccountCredentialsMail $mail) use ($created) {
            return $mail->hasTo($created->email)
                && strlen($mail->plainPassword) >= 14;
        });

        $this->assertDatabaseHas('email_logs', [
            'id_pengguna' => $created->id,
            'email'       => $created->email,
            'type'        => 'credentials',
        ]);
    }

    public function test_administrator_can_update_toggle_and_reset_password_of_other_users(): void
    {
        $administrator = User::where('role', 'administrator')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();

        // Update role & name
        $this->actingAs($administrator)
            ->put(route('administrator.users.update', $admin->id), [
                'name'          => 'Operasional Senior',
                'email'         => $admin->email,
                'nomor_telepon' => '081200009999',
                'role'          => 'supervisor',
                'status_akun'   => 'active',
            ])
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertSame('Operasional Senior', $admin->name);
        $this->assertSame('supervisor', $admin->role);

        // Toggle status to inactive
        $this->actingAs($administrator)
            ->post(route('administrator.users.toggle', $admin->id))
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertSame('inactive', $admin->status_akun);

        // Reset password
        $oldHash = $admin->password;
        $this->actingAs($administrator)
            ->post(route('administrator.users.reset-password', $admin->id))
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertNotSame($oldHash, $admin->password);
        $this->assertTrue((bool) $admin->must_change_password);
        Mail::assertSent(AccountCredentialsMail::class);
    }

    public function test_administrator_cannot_lock_out_self_or_last_active_administrator(): void
    {
        $administrator = User::where('role', 'administrator')->firstOrFail();

        // Cannot delete self
        $this->actingAs($administrator)
            ->delete(route('administrator.users.destroy', $administrator->id))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $administrator->id]);

        // Cannot deactivate self
        $this->actingAs($administrator)
            ->post(route('administrator.users.toggle', $administrator->id))
            ->assertSessionHas('error');
        $this->assertSame('active', $administrator->fresh()->status_akun);

        // Cannot demote self
        $this->actingAs($administrator)
            ->put(route('administrator.users.update', $administrator->id), [
                'name'        => $administrator->name,
                'email'       => $administrator->email,
                'role'        => 'admin',
                'status_akun' => 'active',
            ])
            ->assertSessionHas('error');
        $this->assertSame('administrator', $administrator->fresh()->role);

        // Create second active administrator and try demoting/deleting the first via second
        $secondAdmin = User::create([
            'name'        => 'Second Administrator',
            'email'       => 'admin2@rozwia.com',
            'role'        => 'administrator',
            'status_akun' => 'inactive', // inactive, so $administrator is still the ONLY active administrator
            'password'    => Hash::make('StrongPass123!'),
        ]);

        // Even if another inactive administrator exists, deleting the only ACTIVE administrator must be blocked
        $this->actingAs($administrator)
            ->delete(route('administrator.users.destroy', $secondAdmin->id))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $secondAdmin->id]);
    }

    public function test_administrator_deleting_jamaah_cleans_up_private_files(): void
    {
        $administrator = User::where('role', 'administrator')->firstOrFail();
        $jamaah        = User::where('role', 'jamaah')->where('status_akun', 'active')->firstOrFail();
        $paket         = Paket::firstOrFail();
        $jadwal        = Jadwal::where('id_paket', $paket->id)->firstOrFail();

        Storage::disk('private')->put('dokumen/ktp-test-cleanup.pdf', 'dummy-content');

        $booking = Booking::create([
            'id_pengguna'   => $jamaah->id,
            'id_jadwal'     => $jadwal->id,
            'kode_booking'  => 'BK-TEST-DEL-01',
            'jumlah_jamaah' => 1,
            'total_harga'   => 30000000,
            'status'        => 'Pending',
        ]);

        $detail = JamaahDetail::create([
            'id_booking'    => $booking->id,
            'nama_lengkap'  => $jamaah->name,
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '08123456789',
        ]);

        Dokumen::create([
            'jamaah_detail_id' => $detail->id,
            'tipe_dokumen'     => 'KTP',
            'file_path'        => 'dokumen/ktp-test-cleanup.pdf',
            'status'           => 'Menunggu_Verifikasi',
        ]);

        $this->actingAs($administrator)
            ->delete(route('administrator.users.destroy', $jamaah->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $jamaah->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        Storage::disk('private')->assertMissing('dokumen/ktp-test-cleanup.pdf');
    }
}
