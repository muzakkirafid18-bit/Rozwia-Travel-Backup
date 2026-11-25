<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang Kembali');
    }

    public function test_admin_can_authenticate(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@rozwia.com',
            'password' => 'Admin123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/home');

        // Test follow redirect to home
        $homeResponse = $this->get('/home');
        $homeResponse->assertRedirect(route('admin.dashboard'));
    }

    public function test_supervisor_redirected_to_supervisor_dashboard(): void
    {
        $user = User::where('email', 'supervisor@rozwia.com')->first();

        $response = $this->actingAs($user)->get('/home');
        $response->assertRedirect(route('supervisor.dashboard'));
    }

    public function test_jamaah_redirected_to_jamaah_dashboard(): void
    {
        $user = User::where('email', 'jamaah@rozwia.com')->first();

        $response = $this->actingAs($user)->get('/home');
        $response->assertRedirect(route('jamaah.dashboard'));
    }

    public function test_jamaah_cannot_access_admin_dashboard(): void
    {
        $user = User::where('email', 'jamaah@rozwia.com')->first();

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'pending@rozwia.com',
            'password' => 'Jamaah123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
