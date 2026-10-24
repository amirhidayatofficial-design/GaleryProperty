<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthSeededUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed();
    }
    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk');
    }

    public function test_admin_can_login_and_logout(): void
    {
        // 1. Login dengan Admin
        $response = $this->post('/login', [
            'email' => 'admin@galerypropertysyariah.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        // 2. Akses Dashboard
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee("Assalamu'alaikum", false);
        $dashboardResponse->assertSee('Listing menunggu verifikasi');

        // 3. Logout
        $logoutResponse = $this->post('/logout');
        $this->assertGuest();
        $logoutResponse->assertRedirect('/');
    }

    public function test_agency_partner_can_login_and_logout(): void
    {
        $response = $this->post('/login', [
            'email' => 'agency1@galerypropertysyariah.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $logoutResponse = $this->post('/logout');
        $this->assertGuest();
        $logoutResponse->assertRedirect('/');
    }

    public function test_buyer_can_login_and_logout(): void
    {
        $response = $this->post('/login', [
            'email' => 'pembeli1@keluargamuslim.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $logoutResponse = $this->post('/logout');
        $this->assertGuest();
        $logoutResponse->assertRedirect('/');
    }

    public function test_invalid_password_is_rejected(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@galerypropertysyariah.com',
            'password' => 'passwordsalah',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
