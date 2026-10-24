<?php

namespace Tests\Feature;

use App\Models\BuyerLead;
use App\Models\Commission;
use App\Models\DigitalAkad;
use App\Models\PropertyProject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * State machine komisi per termin sesuai prinsip syariah (ju'alah + wakalah bil ujrah).
 */
class CommissionStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agencyUser;
    private PropertyProject $project;
    private BuyerLead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@galerypropertysyariah.com')->firstOrFail();
        $this->agencyUser = User::where('email', 'agency1@galerypropertysyariah.com')->firstOrFail();
        $this->project = $this->agencyUser->agencyPartner->propertyProjects()->firstOrFail();
        $this->project->update(['commission_rate' => 3, 'developer_name' => 'PT Developer Amanah']);
        $this->lead = BuyerLead::doesntHave('digitalAkads')->firstOrFail();
    }

    private function akadPayload(array $overrides = []): array
    {
        return [
            'property_project_id' => $this->project->id,
            'buyer_lead_id' => $this->lead->id,
            'agency_partner_id' => $this->project->agency_partner_id,
            'akad_type' => 'akad_istishna',
            'transaction_amount' => 1200000000,
            'dp_amount' => 240000000,
            'tenor_months' => 60,
            'terms_content' => 'Isi akad uji',
            'buyer_signed_at' => '2026-10-20T10:00',
            'agency_signed_at' => '2026-10-20T10:00',
            'developer_signed_at' => '2026-10-20T10:00',
            'status' => 'signed',
            ...$overrides,
        ];
    }

    private function signedAkad(array $overrides = []): DigitalAkad
    {
        $this->actingAs($this->admin)->post(route('admin.digital-akads.store'), $this->akadPayload($overrides));

        return DigitalAkad::where('buyer_lead_id', $this->lead->id)->latest('id')->firstOrFail();
    }

    // ---------- Penerbitan otomatis ----------

    public function test_signed_sale_akad_creates_two_pending_termins_with_locked_amounts(): void
    {
        $akad = $this->signedAkad();
        $termins = $akad->commissions;

        $this->assertCount(2, $termins);
        $this->assertSame([1, 2], $termins->pluck('termin')->all());
        $this->assertSame(['pending', 'pending'], $termins->pluck('status')->all());

        // 3% × 1,2 miliar = 36 juta → 18 juta per termin; platform fee 20% = 3,6 juta; agency 14,4 juta
        $this->assertEquals(18000000, $termins[0]->total_commission);
        $this->assertEquals(3600000, $termins[0]->platform_fee);
        $this->assertEquals(14400000, $termins[0]->net_commission);
        $this->assertEquals(36000000, $termins->sum('total_commission'));

        // Persentase dikunci: mengubah listing tidak mengubah komisi yang sudah terbit
        $this->project->update(['commission_rate' => 5]);
        $this->assertEquals(3, $termins[0]->fresh()->commission_rate);

        $this->assertSame(1, $termins[0]->statusLogs()->count());
    }

    public function test_unsigned_akad_and_mou_do_not_create_commission(): void
    {
        $this->signedAkad(['status' => 'pending_signature', 'developer_signed_at' => null]);
        $this->assertSame(0, Commission::where('digital_akad_id', DigitalAkad::latest('id')->first()->id)->count());

        $this->lead = BuyerLead::doesntHave('digitalAkads')->firstOrFail();
        $mou = $this->signedAkad(['akad_type' => 'mou_peminatan']);
        $this->assertSame(0, $mou->commissions()->count());
    }

    public function test_commission_created_when_akad_becomes_signed_and_only_once(): void
    {
        $akad = $this->signedAkad(['status' => 'pending_signature']);
        $this->assertSame(0, $akad->commissions()->count());

        $this->actingAs($this->admin)->put(route('admin.digital-akads.update', $akad), $this->akadPayload());
        $this->actingAs($this->admin)->put(route('admin.digital-akads.update', $akad), $this->akadPayload(['terms_content' => 'Revisi']));

        $this->assertSame(2, $akad->commissions()->count());
    }

    // ---------- Alur lengkap sampai selesai ----------

    public function test_full_lifecycle_from_pending_to_settled_with_audit_log(): void
    {
        Storage::fake('public');
        $commission = $this->signedAkad()->commissions()->where('termin', 1)->firstOrFail();

        // pending → due
        $this->actingAs($this->admin)->post(route('admin.commissions.issue-invoice', $commission))->assertSessionHas('error');
        $this->actingAs($this->admin)->post(route('admin.commissions.mark-due', $commission), ['note' => ''])->assertSessionHasErrors('note');
        $this->actingAs($this->admin)->post(route('admin.commissions.mark-due', $commission), ['note' => 'DP lunas 20 Okt 2026'])
            ->assertRedirect(route('admin.commissions.show', $commission));
        $this->assertSame('due', $commission->fresh()->status);

        // due → invoiced
        $this->actingAs($this->admin)->post(route('admin.commissions.issue-invoice', $commission));
        $commission->refresh();
        $this->assertSame('invoiced', $commission->status);
        $this->assertMatchesRegularExpression('#^INV/\d{6}/\d{4}$#', $commission->invoice_number);
        $this->assertTrue($commission->due_at->isSameDay(now()->addDays(14)));
        $this->actingAs($this->admin)->get(route('admin.commissions.invoice', $commission))
            ->assertOk()
            ->assertSee('PT Developer Amanah')
            ->assertSee('tidak dikenakan denda keterlambatan');

        // invoiced → paid (bukti bayar wajib)
        $this->actingAs($this->admin)->post(route('admin.commissions.record-payment', $commission), ['paid_at' => now()->toDateString()])
            ->assertSessionHasErrors('payment_proof');
        $this->actingAs($this->admin)->post(route('admin.commissions.record-payment', $commission), [
            'paid_at' => now()->toDateString(),
            'payment_proof' => UploadedFile::fake()->create('transfer.pdf', 50, 'application/pdf'),
        ]);
        $commission->refresh();
        $this->assertSame('paid', $commission->status);
        Storage::disk('public')->assertExists($commission->payment_proof_path);

        // paid → settled (bukti payout wajib)
        $this->actingAs($this->admin)->post(route('admin.commissions.record-payout', $commission), [
            'payout_proof' => UploadedFile::fake()->image('payout.jpg'),
        ]);
        $commission->refresh();
        $this->assertSame('settled', $commission->status);
        $this->assertNotNull($commission->settled_at);

        // Final: tidak bisa diubah lagi, termasuk digugurkan
        $this->actingAs($this->admin)->post(route('admin.commissions.void', $commission), ['reason' => 'Coba tarik'])->assertSessionHas('error');
        $this->assertSame('settled', $commission->fresh()->status);

        $this->assertSame(
            [null, 'pending', 'due', 'invoiced', 'paid'],
            $commission->statusLogs()->reorder('id')->pluck('from_status')->all()
        );
        $this->assertSame($this->admin->id, $commission->statusLogs()->first()->user_id);
    }

    // ---------- Akad batal: tanpa clawback ----------

    public function test_cancelling_akad_voids_only_termins_not_yet_due(): void
    {
        $akad = $this->signedAkad();
        [$termin1, $termin2] = $akad->commissions->all();
        $termin1->transitionTo(Commission::STATUS_DUE, $this->admin, 'DP lunas');

        $this->actingAs($this->admin)->put(route('admin.digital-akads.update', $akad), $this->akadPayload(['status' => 'cancelled']));

        // Termin 1 sudah jatuh tempo: tetap hak agency. Termin 2 belum: gugur.
        $this->assertSame('due', $termin1->fresh()->status);
        $this->assertSame('void', $termin2->fresh()->status);
    }

    // ---------- Jatuh tempo: tanpa denda, sanksi non-uang ----------

    public function test_overdue_invoice_blocks_developer_new_listings_without_changing_amount(): void
    {
        $commission = $this->signedAkad()->commissions()->firstOrFail();
        $commission->transitionTo(Commission::STATUS_DUE, $this->admin, 'DP lunas');
        $commission->transitionTo(Commission::STATUS_INVOICED, $this->admin, 'Invoice', [
            'invoice_number' => 'INV/UJI/0001',
            'invoiced_at' => now()->subDays(20),
            'due_at' => now()->subDays(6),
        ]);
        $amountBefore = $commission->total_commission;

        $this->assertTrue($commission->fresh()->isOverdue());
        $this->assertEquals($amountBefore, $commission->fresh()->total_commission);

        Storage::fake('public');
        $listing = fn (string $developer) => [
            'title' => 'Listing Baru ' . $developer,
            'developer_name' => $developer,
            'property_type' => 'rumah',
            'syariah_scheme' => 'istishna',
            'price' => 500000000,
            'commission_rate' => 2.5,
            'province' => 'Jawa Barat',
            'city' => 'Bogor',
            'address_detail' => 'Jl. Uji',
            'description' => 'Deskripsi',
            'status' => 'available',
            'legal_doc' => UploadedFile::fake()->create('pks.pdf', 10, 'application/pdf'),
        ];

        $this->actingAs($this->agencyUser)->post(route('agency.property-projects.store'), $listing('  pt developer amanah '))
            ->assertSessionHasErrors('developer_name');
        $this->actingAs($this->agencyUser)->post(route('agency.property-projects.store'), $listing('PT Developer Lain'))
            ->assertSessionHasNoErrors();
    }

    // ---------- Transparansi untuk agency ----------

    public function test_agency_sees_only_own_commissions_with_history(): void
    {
        $own = $this->signedAkad()->commissions()->firstOrFail();
        $other = Commission::where('agency_partner_id', '!=', $own->agency_partner_id)->firstOrFail();

        $this->actingAs($this->agencyUser)->get(route('agency.commissions.index'))
            ->assertOk()
            ->assertSee($own->digitalAkad->akad_number);

        $this->actingAs($this->agencyUser)->get(route('agency.commissions.show', $own))
            ->assertOk()
            ->assertSee('Riwayat Status')
            ->assertDontSee('oleh ' . $this->admin->name);

        $this->actingAs($this->agencyUser)->get(route('agency.commissions.show', $other))->assertForbidden();
        $this->actingAs($this->agencyUser)->post(route('admin.commissions.mark-due', $own), ['note' => 'x'])->assertForbidden();
    }
}
