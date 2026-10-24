<?php

namespace Tests\Feature;

use App\Models\AgencyPartner;
use App\Models\BuyerLead;
use App\Models\Conversation;
use App\Models\PropertyProject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * US-06 (chat pembeli - agency) dan US-07 (pengajuan listing oleh agency).
 */
class ListingChatFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $agencyUser;
    private User $buyer;
    private PropertyProject $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->agencyUser = User::where('email', 'agency1@galerypropertysyariah.com')->firstOrFail();
        $this->buyer = User::factory()->create(['role' => 'buyer', 'phone' => '081299990000']);
        $this->project = $this->agencyUser->agencyPartner->propertyProjects()->firstOrFail();
    }

    private function listingPayload(array $overrides = []): array
    {
        return [
            'title' => 'Cluster Pengajuan Agency',
            'developer_name' => 'PT Developer Mitra',
            'property_type' => 'rumah',
            'syariah_scheme' => 'murabahah',
            'price' => 650000000,
            'commission_rate' => 2.5,
            'province' => 'Jawa Barat',
            'city' => 'Depok',
            'address_detail' => 'Jl. Uji No. 7',
            'description' => 'Deskripsi pengajuan',
            'status' => 'available',
            ...$overrides,
        ];
    }

    // ---------- US-07: Pengajuan listing ----------

    public function test_verified_agency_submits_listing_as_draft_with_legal_doc(): void
    {
        Storage::fake('public');

        $this->actingAs($this->agencyUser)->post(route('agency.property-projects.store'), $this->listingPayload([
            'is_verified' => '1', // diabaikan: agency tidak bisa menayangkan sendiri
        ]))->assertSessionHasErrors('legal_doc');

        $this->actingAs($this->agencyUser)->post(route('agency.property-projects.store'), $this->listingPayload([
            'is_verified' => '1',
            'legal_doc' => UploadedFile::fake()->create('pks.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('agency.property-projects.index'));

        $listing = PropertyProject::where('title', 'Cluster Pengajuan Agency')->firstOrFail();
        $this->assertFalse($listing->is_verified);
        $this->assertSame($this->agencyUser->agencyPartner->id, $listing->agency_partner_id);
        $this->assertSame(1, $listing->legalDocs()->count());
        Storage::disk('public')->assertExists($listing->legalDocs()->first()->file_path);

        // Draft belum tampil di katalog
        $this->get(route('catalog.index'))->assertDontSee('Cluster Pengajuan Agency');
        $this->get(route('catalog.show', $listing))->assertNotFound();
    }

    public function test_unverified_agency_cannot_submit_listing(): void
    {
        $this->agencyUser->agencyPartner->update(['verification_status' => 'pending']);

        $this->actingAs($this->agencyUser)->get(route('agency.property-projects.index'))
            ->assertOk()
            ->assertSee('belum diverifikasi admin');
        $this->actingAs($this->agencyUser)->get(route('agency.property-projects.create'))->assertForbidden();
        $this->actingAs($this->agencyUser)->post(route('agency.property-projects.store'), $this->listingPayload())->assertForbidden();
    }

    public function test_agency_can_only_edit_own_unpublished_listing(): void
    {
        $draft = $this->agencyUser->agencyPartner->propertyProjects()->create([
            ...$this->listingPayload(),
            'slug' => 'draft-agency',
            'is_verified' => false,
        ]);

        $this->actingAs($this->agencyUser)->get(route('agency.property-projects.edit', $draft))->assertOk();
        $this->actingAs($this->agencyUser)->put(route('agency.property-projects.update', $draft), $this->listingPayload([
            'title' => 'Draft Sudah Diubah',
        ]))->assertRedirect(route('agency.property-projects.show', $draft));
        $this->assertSame('Draft Sudah Diubah', $draft->fresh()->title);

        // Listing yang sudah tayang tidak bisa diubah agency
        $this->assertTrue($this->project->is_verified);
        $this->actingAs($this->agencyUser)->get(route('agency.property-projects.edit', $this->project))->assertForbidden();

        // Listing milik agency lain tidak bisa dilihat
        $otherAgencyUser = User::where('email', 'agency2@galerypropertysyariah.com')->firstOrFail();
        $this->actingAs($otherAgencyUser)->get(route('agency.property-projects.show', $draft))->assertForbidden();

        // Pembeli tidak bisa masuk area agency
        $this->actingAs($this->buyer)->get(route('agency.property-projects.index'))->assertForbidden();
    }

    // ---------- Katalog ----------

    public function test_catalog_shows_published_listing_without_agency_contact(): void
    {
        $agency = $this->agencyUser->agencyPartner;

        $this->get(route('catalog.index'))->assertOk()->assertSee($this->project->title);

        $this->get(route('catalog.show', $this->project))
            ->assertOk()
            ->assertSee($agency->agency_name)
            ->assertSee('Login untuk Chat Agency')
            ->assertDontSee($this->agencyUser->phone)
            ->assertDontSee($this->agencyUser->email);
    }

    // ---------- US-06: Chat ----------

    public function test_guest_and_non_buyer_cannot_start_chat(): void
    {
        $this->post(route('chat.start', $this->project))->assertRedirect('/login');
        $this->actingAs($this->agencyUser)->post(route('chat.start', $this->project))->assertForbidden();
    }

    public function test_buyer_chats_with_agency_and_first_message_creates_lead(): void
    {
        $this->actingAs($this->buyer)->post(route('chat.start', $this->project))
            ->assertRedirect();

        $conversation = Conversation::where('buyer_id', $this->buyer->id)->firstOrFail();
        $this->assertSame($this->project->agency_partner_id, $conversation->agency_partner_id);

        // Membuka chat untuk projek yang sama melanjutkan percakapan lama
        $this->actingAs($this->buyer)->post(route('chat.start', $this->project))
            ->assertRedirect(route('chat.show', $conversation));
        $this->assertSame(1, Conversation::where('buyer_id', $this->buyer->id)->count());

        // Validasi isi pesan
        $this->actingAs($this->buyer)->post(route('chat.send', $conversation), ['body' => ''])
            ->assertSessionHasErrors('body');
        $this->actingAs($this->buyer)->post(route('chat.send', $conversation), ['body' => str_repeat('a', 1001)])
            ->assertSessionHasErrors('body');

        $this->actingAs($this->buyer)->post(route('chat.send', $conversation), ['body' => 'Assalamualaikum, unit masih tersedia?']);
        $this->actingAs($this->buyer)->post(route('chat.send', $conversation), ['body' => 'Bisa survei hari Sabtu?']);

        // Pesan pertama membuat satu lead baru
        $leads = BuyerLead::where('user_id', $this->buyer->id)->where('property_project_id', $this->project->id)->get();
        $this->assertCount(1, $leads);
        $this->assertSame('new_lead', $leads->first()->status);
        $this->assertSame($this->project->agency_partner_id, $leads->first()->agency_partner_id);

        // Agency melihat percakapan dengan penanda belum dibaca
        $this->assertSame(1, Conversation::unreadConversationCountFor($this->agencyUser));
        $this->assertSame(2, $conversation->unreadCountFor($this->agencyUser));

        $this->actingAs($this->agencyUser)->get(route('chat.index'))
            ->assertOk()
            ->assertSee($this->buyer->name);

        $this->actingAs($this->agencyUser)->get(route('chat.show', $conversation))
            ->assertOk()
            ->assertSee('Bisa survei hari Sabtu?');
        $this->assertSame(0, Conversation::unreadConversationCountFor($this->agencyUser));

        // Agency membalas, pembeli mendapat penanda belum dibaca
        $this->actingAs($this->agencyUser)->post(route('chat.send', $conversation), ['body' => "Wa'alaikumussalam, masih tersedia."]);
        $this->assertSame(1, Conversation::unreadConversationCountFor($this->buyer));

        $this->actingAs($this->buyer)->get(route('chat.messages', $conversation))
            ->assertOk()
            ->assertSee('masih tersedia.');
    }

    public function test_only_participants_can_open_conversation(): void
    {
        $conversation = Conversation::create([
            'buyer_id' => $this->buyer->id,
            'agency_partner_id' => $this->project->agency_partner_id,
            'property_project_id' => $this->project->id,
        ]);

        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        $otherAgencyUser = User::where('email', 'agency2@galerypropertysyariah.com')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();

        foreach ([$otherBuyer, $otherAgencyUser, $admin] as $outsider) {
            $this->actingAs($outsider)->get(route('chat.show', $conversation))->assertForbidden();
            $this->actingAs($outsider)->post(route('chat.send', $conversation), ['body' => 'halo'])->assertForbidden();
        }

        $this->actingAs($this->buyer)->get(route('chat.show', $conversation))->assertOk();
        $this->actingAs($this->agencyUser)->get(route('chat.show', $conversation))->assertOk();
    }

    public function test_project_without_agency_has_no_chat_button(): void
    {
        $this->project->update(['agency_partner_id' => null]);

        $this->actingAs($this->buyer)->get(route('catalog.show', $this->project))
            ->assertOk()
            ->assertDontSee('Chat Agency')
            ->assertSee('belum memiliki agency');
        $this->actingAs($this->buyer)->post(route('chat.start', $this->project))->assertNotFound();
    }
}
