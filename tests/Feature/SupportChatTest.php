<?php

namespace Tests\Feature;

use App\Models\PropertyProject;
use App\Models\SupportConversation;
use App\Models\User;
use App\Support\SupportAutoReply;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Chat customer support (Bantuan) pembeli/agency ke admin, dan katalog tertutup untuk admin.
 */
class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agencyUser;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@galerypropertysyariah.com')->firstOrFail();
        $this->agencyUser = User::where('email', 'agency1@galerypropertysyariah.com')->firstOrFail();
        $this->buyer = User::factory()->create(['role' => 'buyer']);
    }

    // ---------- Katalog tertutup untuk admin ----------

    public function test_admin_cannot_open_catalog_and_has_no_catalog_menu(): void
    {
        $project = PropertyProject::published()->firstOrFail();

        $this->actingAs($this->admin)->get(route('catalog.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('catalog.show', $project))->assertForbidden();
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('catalog.index'));
    }

    public function test_guest_buyer_and_agency_can_still_open_catalog(): void
    {
        $this->get(route('catalog.index'))->assertOk();
        $this->actingAs($this->buyer)->get(route('catalog.index'))->assertOk();
        $this->actingAs($this->agencyUser)->get(route('catalog.index'))->assertOk();
    }

    // ---------- Bantuan: sisi pembeli & agency ----------

    public function test_buyer_and_agency_see_help_menu_but_admin_cannot_open_it(): void
    {
        $this->actingAs($this->buyer)->get(route('dashboard'))->assertSee(route('support.show'));
        $this->actingAs($this->agencyUser)->get(route('support.show'))->assertOk()->assertSee('Bantuan');

        $this->actingAs($this->admin)->get(route('support.show'))->assertForbidden();

        auth()->logout();
        $this->get(route('support.show'))->assertRedirect(route('login'));
    }

    public function test_opening_help_page_does_not_create_empty_conversation(): void
    {
        $this->actingAs($this->buyer)->get(route('support.show'))->assertOk();

        $this->assertDatabaseCount('support_conversations', 0);
    }

    public function test_user_message_reaches_admin_inbox_and_admin_reply_reaches_user(): void
    {
        $this->actingAs($this->agencyUser)
            ->post(route('support.send'), ['body' => '  Bagaimana cara verifikasi profil agency?  '])
            ->assertRedirect(route('support.show'));

        $conversation = SupportConversation::where('user_id', $this->agencyUser->id)->firstOrFail();
        $this->assertSame('Bagaimana cara verifikasi profil agency?', $conversation->messages()->first()->body);

        // Admin melihat pesan baru di inbox (badge belum dibaca)
        $this->assertSame(1, SupportConversation::unreadConversationCountFor($this->admin));
        $this->actingAs($this->admin)->get(route('admin.support.index'))
            ->assertOk()
            ->assertSee($this->agencyUser->name)
            ->assertSee('Mitra Agency');

        // Membuka percakapan menandai sudah dibaca
        $this->actingAs($this->admin)->get(route('admin.support.show', $conversation))
            ->assertOk()
            ->assertSee('Bagaimana cara verifikasi profil agency?');
        $this->assertSame(0, SupportConversation::unreadConversationCountFor($this->admin));

        // Admin membalas → pengguna mendapat badge belum dibaca
        $this->actingAs($this->admin)
            ->post(route('admin.support.send', $conversation), ['body' => 'Lengkapi izin usaha di profil Anda.'])
            ->assertRedirect(route('admin.support.show', $conversation));

        $this->assertSame(1, SupportConversation::unreadConversationCountFor($this->agencyUser));
        $this->actingAs($this->agencyUser)->get(route('support.show'))
            ->assertOk()
            ->assertSee('Lengkapi izin usaha di profil Anda.')
            ->assertSee('Tim Support');
        $this->assertSame(0, SupportConversation::unreadConversationCountFor($this->agencyUser));
    }

    public function test_each_user_only_sees_own_support_conversation(): void
    {
        $this->actingAs($this->agencyUser)->post(route('support.send'), ['body' => 'Pesan rahasia agency']);

        $this->actingAs($this->buyer)->get(route('support.show'))->assertDontSee('Pesan rahasia agency');
        $this->actingAs($this->buyer)->get(route('support.messages'))->assertDontSee('Pesan rahasia agency');
    }

    public function test_non_admin_cannot_open_admin_support_inbox(): void
    {
        $this->actingAs($this->agencyUser)->post(route('support.send'), ['body' => 'Halo admin']);
        $conversation = SupportConversation::firstOrFail();

        $this->actingAs($this->buyer)->get(route('admin.support.index'))->assertForbidden();
        $this->actingAs($this->buyer)->get(route('admin.support.show', $conversation))->assertForbidden();
        $this->actingAs($this->buyer)->post(route('admin.support.send', $conversation), ['body' => 'x'])->assertForbidden();
    }

    public function test_message_is_required_and_limited(): void
    {
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => str_repeat('a', 1001)])->assertSessionHasErrors('body');

        $this->assertDatabaseCount('support_messages', 0);
    }

    // ---------- Balasan otomatis ----------

    public function test_help_page_greets_user_by_first_name(): void
    {
        $this->buyer->update(['name' => 'Fatimah Azzahra']);

        $greeting = SupportAutoReply::greetingFor($this->buyer);
        $this->assertStringContainsString('Fatimah', $greeting);
        $this->assertStringNotContainsString(':name', $greeting);

        // Sapaan stabil antar-refresh supaya polling tidak mengganti isi chat terus-menerus
        $this->assertSame($greeting, SupportAutoReply::greetingFor($this->buyer));
        $this->actingAs($this->buyer)->get(route('support.show'))->assertSee($greeting, false);
        $this->actingAs($this->buyer)->get(route('support.messages'))->assertSee($greeting, false);
    }

    public function test_first_message_gets_one_acknowledgement_until_admin_replies(): void
    {
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => 'Pertanyaan pertama']);
        $conversation = SupportConversation::firstOrFail();

        $autoReplies = fn () => $conversation->messages()->whereNull('sender_id')->pluck('body');

        $this->assertCount(1, $autoReplies());
        $this->assertContains($autoReplies()->first(), SupportAutoReply::ACKNOWLEDGEMENTS);

        // Pesan susulan saat masih menunggu tidak dibalas otomatis lagi
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => 'Tambahan info']);
        $this->assertCount(1, $autoReplies());

        // Setelah admin membalas, pertanyaan baru mendapat konfirmasi dengan kalimat berbeda
        $this->actingAs($this->admin)->post(route('admin.support.send', $conversation), ['body' => 'Baik, kami cek.']);
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => 'Pertanyaan lain']);

        $this->assertCount(2, $autoReplies());
        $this->assertNotSame($autoReplies()[0], $autoReplies()[1]);
    }

    public function test_auto_reply_is_labelled_and_does_not_count_as_unread(): void
    {
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => 'Halo']);
        $conversation = SupportConversation::firstOrFail();

        // Admin tetap melihat 1 percakapan belum dibaca (pesan pengguna), bukan dari balasan otomatis
        $this->assertSame(1, SupportConversation::unreadConversationCountFor($this->admin));
        $this->assertSame(0, SupportConversation::unreadConversationCountFor($this->buyer));

        $this->actingAs($this->buyer)->get(route('support.show'))->assertSee('Tim Support · otomatis');
        $this->actingAs($this->admin)->get(route('admin.support.show', $conversation))->assertSee('Balasan otomatis');
        $this->actingAs($this->admin)->get(route('admin.support.index'))->assertOk()->assertSee('Balasan otomatis:');
    }

    public function test_admin_inbox_filters_by_role(): void
    {
        $this->actingAs($this->agencyUser)->post(route('support.send'), ['body' => 'Dari agency']);
        $this->actingAs($this->buyer)->post(route('support.send'), ['body' => 'Dari pembeli']);

        $this->actingAs($this->admin)->get(route('admin.support.index', ['role' => 'buyer']))
            ->assertSee($this->buyer->name)
            ->assertDontSee($this->agencyUser->name);
    }
}
