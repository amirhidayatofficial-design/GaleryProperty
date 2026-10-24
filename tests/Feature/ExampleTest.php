<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('GalleryProperty');
    }

    public function test_logged_in_users_are_redirected_from_landing_page_to_dashboard(): void
    {
        foreach (['admin', 'agency_partner', 'buyer'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
        }
    }
}
