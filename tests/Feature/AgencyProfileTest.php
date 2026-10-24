<?php

namespace Tests\Feature;

use App\Models\AgencyPartner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Agency partner mengelola profil agency-nya sendiri.
 */
class AgencyProfileTest extends TestCase
{
    use RefreshDatabase;

    private function profilePayload(array $overrides = []): array
    {
        return [
            'agency_name' => 'PT Amanah Properti Baru',
            'license_number' => 'SIU/2026/0099',
            'company_address' => 'Jl. Masjid Raya No. 9, Depok',
            'bank_name' => 'Bank Muamalat',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT Amanah Properti Baru',
            ...$overrides,
        ];
    }

    public function test_verified_agency_can_edit_profile_without_losing_verification(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agencyUser = User::where('email', 'agency1@galerypropertysyariah.com')->firstOrFail();
        $agency = $agencyUser->agencyPartner;
        $verifiedAt = $agency->verified_at;

        $this->actingAs($agencyUser)->get(route('agency.profile.edit'))
            ->assertOk()
            ->assertSee($agency->agency_name)
            ->assertSee('Terverifikasi');

        $this->actingAs($agencyUser)->put(route('agency.profile.update'), $this->profilePayload())
            ->assertRedirect(route('agency.profile.edit'))
            ->assertSessionHas('success');

        $agency->refresh();
        $this->assertSame('PT Amanah Properti Baru', $agency->agency_name);
        $this->assertSame('1234567890', $agency->bank_account_number);
        $this->assertSame('verified', $agency->verification_status);
        $this->assertEquals($verifiedAt, $agency->verified_at);
    }

    public function test_agency_without_profile_can_create_one_pending_verification(): void
    {
        $agencyUser = User::factory()->agencyPartner()->create();

        $this->actingAs($agencyUser)->get(route('agency.profile.edit'))
            ->assertOk()
            ->assertSee('Kirim untuk Verifikasi');

        $this->actingAs($agencyUser)->put(route('agency.profile.update'), $this->profilePayload());

        $agency = $agencyUser->fresh()->agencyPartner;
        $this->assertNotNull($agency);
        $this->assertSame('pending', $agency->verification_status);
        $this->assertNull($agency->verified_at);

        // Masih menunggu verifikasi: belum bisa mengajukan listing
        $this->actingAs($agencyUser->fresh())->get(route('agency.property-projects.create'))->assertForbidden();
    }

    public function test_agency_cannot_change_verification_status_or_owner(): void
    {
        $agencyUser = User::factory()->agencyPartner()->create();
        $otherUser = User::factory()->agencyPartner()->create();

        $this->actingAs($agencyUser)->put(route('agency.profile.update'), $this->profilePayload([
            'verification_status' => 'verified',
            'verified_at' => now()->toDateTimeString(),
            'user_id' => $otherUser->id,
        ]));

        $agency = AgencyPartner::where('agency_name', 'PT Amanah Properti Baru')->firstOrFail();
        $this->assertSame('pending', $agency->verification_status);
        $this->assertSame($agencyUser->id, $agency->user_id);
    }

    public function test_profile_validation(): void
    {
        $agencyUser = User::factory()->agencyPartner()->create();

        $this->actingAs($agencyUser)->put(route('agency.profile.update'), $this->profilePayload([
            'agency_name' => '',
            'bank_name' => 'Bank Konvensional',
        ]))->assertSessionHasErrors(['agency_name', 'bank_name']);

        $this->assertNull($agencyUser->fresh()->agencyPartner);
    }

    public function test_only_agency_partners_can_open_agency_profile(): void
    {
        $buyer = User::factory()->buyer()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($buyer)->get(route('agency.profile.edit'))->assertForbidden();
        $this->actingAs($admin)->put(route('agency.profile.update'), $this->profilePayload())->assertForbidden();

        $this->assertDatabaseMissing('agency_partners', ['agency_name' => 'PT Amanah Properti Baru']);
    }
}
