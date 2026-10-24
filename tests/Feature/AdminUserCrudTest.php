<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $buyer;
    private User $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        $this->admin = User::factory()->create([
            'name' => 'Super Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->buyer = User::factory()->create([
            'name' => 'Buyer Test',
            'email' => 'buyer@test.com',
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $this->agency = User::factory()->create([
            'name' => 'Agency Test',
            'email' => 'agency@test.com',
            'role' => 'agency_partner',
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_admin_user_crud(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect('/login');

        $response = $this->get(route('admin.users.create'));
        $response->assertRedirect('/login');
    }

    public function test_non_admin_users_receive_403_forbidden(): void
    {
        // Buyer akses admin/users
        $response = $this->actingAs($this->buyer)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Agency Partner akses admin/users
        $response = $this->actingAs($this->agency)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_user_list_and_search(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Super Admin Test');
        $response->assertSee('Buyer Test');
        $response->assertSee('Agency Test');

        // Test search filter
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Buyer']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Buyer Test');
        $searchResponse->assertDontSee('Agency Test');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Pengguna Baru');
    }

    public function test_admin_can_store_new_user(): void
    {
        $userData = [
            'name' => 'Zaid bin Tsabit',
            'email' => 'zaid@keluargamuslim.com',
            'phone' => '081299998888',
            'role' => 'buyer',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'zaid@keluargamuslim.com',
            'name' => 'Zaid bin Tsabit',
            'phone' => '081299998888',
            'role' => 'buyer',
            'status' => 'active',
        ]);
    }

    public function test_store_validation_requires_mandatory_fields_and_unique_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => '',
            'email' => 'buyer@test.com', // duplicate
            'role' => 'invalid_role',
            'status' => 'invalid_status',
            'password' => '123', // too short
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'role', 'status', 'password']);
    }

    public function test_admin_can_view_user_detail(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $this->buyer));
        $response->assertStatus(200);
        $response->assertSee($this->buyer->name);
        $response->assertSee($this->buyer->email);
    }

    public function test_admin_can_view_edit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->buyer));
        $response->assertStatus(200);
        $response->assertSee($this->buyer->name);
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $oldPassword = $this->buyer->password;

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->buyer), [
            'name' => 'Buyer Updated Name',
            'email' => 'buyer.updated@test.com',
            'phone' => '081233334444',
            'role' => 'buyer',
            'status' => 'active',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->buyer->refresh();
        $this->assertEquals('Buyer Updated Name', $this->buyer->name);
        $this->assertEquals('buyer.updated@test.com', $this->buyer->email);
        $this->assertEquals($oldPassword, $this->buyer->password);
    }

    public function test_admin_cannot_register_new_admin_account(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.create'))
            ->assertOk()
            ->assertDontSee('<option value="admin"', false);

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Admin Kedua',
            'email' => 'admin.kedua@test.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'admin.kedua@test.com']);
    }

    public function test_admin_cannot_promote_other_user_to_admin(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.edit', $this->buyer))
            ->assertOk()
            ->assertDontSee('<option value="admin"', false);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->buyer), [
            'name' => $this->buyer->name,
            'email' => $this->buyer->email,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertEquals('buyer', $this->buyer->fresh()->role);
    }

    public function test_admin_cannot_demote_own_role(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => 'Super Admin Test',
            'email' => 'admin@test.com',
            'phone' => null,
            'role' => 'buyer', // coba demote sendiri
            'status' => 'active',
        ]);

        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertEquals('admin', $this->admin->role);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'User to delete',
            'email' => 'delete.me@test.com',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }
}
