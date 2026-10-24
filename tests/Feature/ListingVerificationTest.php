<?php

namespace Tests\Feature;

use App\Models\PropertyProject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman khusus verifikasi listing: admin meninjau lalu menayangkan lewat tombol.
 */
class ListingVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@galerypropertysyariah.com')->firstOrFail();
    }

    private function pendingProject(bool $withLegalDoc = true): PropertyProject
    {
        $project = PropertyProject::factory()->create(['title' => 'Cluster Menunggu', 'is_verified' => false]);

        if ($withLegalDoc) {
            $project->files()->create(['file_path' => 'properties/legal/pks.pdf', 'file_type' => 'legal_doc', 'caption' => 'PKS Developer']);
        }

        return $project;
    }

    public function test_queue_lists_pending_and_published_listings_in_separate_tabs(): void
    {
        $pending = $this->pendingProject();
        $published = PropertyProject::published()->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.listing-verifications.index'))
            ->assertOk()
            ->assertSee($pending->title)
            ->assertDontSee($published->title);

        $this->actingAs($this->admin)->get(route('admin.listing-verifications.index', ['tab' => 'published']))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($pending->title);
    }

    public function test_review_page_shows_button_not_checkbox(): void
    {
        $project = $this->pendingProject();

        $this->actingAs($this->admin)->get(route('admin.listing-verifications.show', $project))
            ->assertOk()
            ->assertSee('Verifikasi &amp; Tayangkan', false)
            ->assertSee('PKS Developer')
            ->assertDontSee('type="checkbox"', false);

        // Form Ubah Data juga tidak lagi punya checkbox verifikasi
        $this->actingAs($this->admin)->get(route('admin.property-projects.edit', $project))
            ->assertOk()
            ->assertDontSee('name="is_verified"', false);
    }

    public function test_admin_approves_listing_and_it_appears_in_catalog(): void
    {
        $project = $this->pendingProject();

        $this->actingAs($this->admin)->post(route('admin.listing-verifications.approve', $project))
            ->assertRedirect(route('admin.listing-verifications.index'))
            ->assertSessionHas('success');

        $this->assertTrue($project->fresh()->is_verified);

        auth()->logout();
        $this->get(route('catalog.show', $project))->assertOk();
    }

    public function test_listing_without_legal_document_cannot_be_approved(): void
    {
        $project = $this->pendingProject(withLegalDoc: false);

        $this->actingAs($this->admin)->get(route('admin.listing-verifications.show', $project))
            ->assertSee('Lengkapi Dokumen Legalitas');

        $this->actingAs($this->admin)->post(route('admin.listing-verifications.approve', $project))
            ->assertSessionHas('error');

        $this->assertFalse($project->fresh()->is_verified);
    }

    public function test_admin_can_unpublish_listing(): void
    {
        $project = PropertyProject::published()->firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.listing-verifications.unpublish', $project))
            ->assertRedirect(route('admin.listing-verifications.show', $project));

        $this->assertFalse($project->fresh()->is_verified);

        auth()->logout();
        $this->get(route('catalog.show', $project))->assertNotFound();
    }

    public function test_new_project_created_by_admin_starts_unpublished(): void
    {
        $this->actingAs($this->admin)->post(route('admin.property-projects.store'), [
            'title' => 'Projek Admin Baru',
            'developer_name' => 'PT Dev',
            'property_type' => 'rumah',
            'syariah_scheme' => 'istishna',
            'price' => 500000000,
            'commission_rate' => 2.5,
            'province' => 'Jawa Barat',
            'city' => 'Bogor',
            'address_detail' => 'Jl. Uji',
            'description' => 'Deskripsi',
            'status' => 'available',
            'is_verified' => '1',
        ]);

        $this->assertFalse(PropertyProject::where('title', 'Projek Admin Baru')->firstOrFail()->is_verified);
    }

    public function test_non_admin_cannot_access_listing_verification(): void
    {
        $project = $this->pendingProject();
        $agency = User::where('email', 'agency1@galerypropertysyariah.com')->firstOrFail();

        $this->actingAs($agency)->get(route('admin.listing-verifications.index'))->assertForbidden();
        $this->actingAs($agency)->post(route('admin.listing-verifications.approve', $project))->assertForbidden();

        $this->assertFalse($project->fresh()->is_verified);
    }
}
