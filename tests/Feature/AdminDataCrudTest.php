<?php

namespace Tests\Feature;

use App\Models\AgencyPartner;
use App\Models\BuyerLead;
use App\Models\Commission;
use App\Models\DigitalAkad;
use App\Models\PropertyFile;
use App\Models\PropertyProject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDataCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    public function test_all_admin_pages_render_for_admin(): void
    {
        $resources = [
            'agency-partners' => AgencyPartner::first(),
            'property-projects' => PropertyProject::first(),
            'property-files' => PropertyFile::first(),
            'buyer-leads' => BuyerLead::first(),
            'digital-akads' => DigitalAkad::first(),
        ];

        foreach ($resources as $name => $model) {
            $this->actingAs($this->admin)->get(route("admin.{$name}.index"))->assertOk();
            $this->actingAs($this->admin)->get(route("admin.{$name}.index", ['search' => 'a']))->assertOk();
            $this->actingAs($this->admin)->get(route("admin.{$name}.create"))->assertOk();
            $this->actingAs($this->admin)->get(route("admin.{$name}.show", $model))->assertOk();
            $this->actingAs($this->admin)->get(route("admin.{$name}.edit", $model))->assertOk();
        }

        // Komisi tidak punya form tambah/ubah: hanya daftar, detail, dan invoice
        $invoiced = Commission::whereNotNull('invoice_number')->firstOrFail();
        $this->actingAs($this->admin)->get(route('admin.commissions.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.commissions.index', ['status' => 'overdue']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.commissions.show', Commission::firstOrFail()))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.commissions.invoice', $invoiced))->assertOk()->assertSee($invoiced->invoice_number);
    }

    public function test_non_admin_receives_403(): void
    {
        $buyer = User::where('role', 'buyer')->firstOrFail();

        $this->actingAs($buyer)->get(route('admin.property-projects.index'))->assertForbidden();
        $this->actingAs($buyer)->post(route('admin.commissions.mark-due', Commission::firstOrFail()), ['note' => 'x'])->assertForbidden();
    }

    public function test_agency_partner_crud(): void
    {
        $owner = User::factory()->create(['role' => 'agency_partner']);

        $this->actingAs($this->admin)->post(route('admin.agency-partners.store'), [
            'user_id' => $owner->id,
            'agency_name' => 'Agency Uji',
            'bank_name' => 'Bank Muamalat',
            'verification_status' => 'verified',
        ])->assertRedirect(route('admin.agency-partners.index'));

        $agency = AgencyPartner::where('agency_name', 'Agency Uji')->firstOrFail();
        $this->assertNotNull($agency->verified_at);

        $this->actingAs($this->admin)->put(route('admin.agency-partners.update', $agency), [
            'user_id' => $owner->id,
            'agency_name' => 'Agency Uji Baru',
            'verification_status' => 'pending',
        ])->assertRedirect(route('admin.agency-partners.index'));

        $agency->refresh();
        $this->assertSame('Agency Uji Baru', $agency->agency_name);
        $this->assertNull($agency->verified_at);

        $this->actingAs($this->admin)->delete(route('admin.agency-partners.destroy', $agency));
        $this->assertModelMissing($agency);
    }

    public function test_agency_with_commissions_cannot_be_deleted(): void
    {
        $agency = Commission::firstOrFail()->agencyPartner;

        $this->actingAs($this->admin)->delete(route('admin.agency-partners.destroy', $agency))
            ->assertSessionHas('error');

        $this->assertModelExists($agency);
    }

    public function test_property_project_crud_generates_unique_slug_and_soft_deletes(): void
    {
        $payload = [
            'title' => 'Cluster Uji Coba',
            'developer_name' => 'PT Uji',
            'property_type' => 'rumah',
            'syariah_scheme' => 'istishna',
            'price' => 500000000,
            'commission_rate' => 2.5,
            'province' => 'Jawa Barat',
            'city' => 'Bogor',
            'address_detail' => 'Jl. Uji No. 1',
            'description' => 'Deskripsi uji',
            'status' => 'available',
            'is_verified' => '0',
        ];

        $this->actingAs($this->admin)->post(route('admin.property-projects.store'), $payload);
        $this->actingAs($this->admin)->post(route('admin.property-projects.store'), $payload);

        $slugs = PropertyProject::where('title', 'Cluster Uji Coba')->pluck('slug')->all();
        $this->assertSame(['cluster-uji-coba', 'cluster-uji-coba-2'], $slugs);

        $project = PropertyProject::where('slug', 'cluster-uji-coba')->firstOrFail();

        // Status tayang tidak bisa diubah lewat form Ubah Data (hanya lewat menu Verifikasi Listing)
        $this->actingAs($this->admin)->put(route('admin.property-projects.update', $project), [
            ...$payload,
            'title' => 'Cluster Uji Ganti',
            'is_verified' => '1',
        ])->assertSessionHasNoErrors();

        $project->refresh();
        $this->assertSame('cluster-uji-ganti', $project->slug);
        $this->assertFalse($project->is_verified);

        $this->actingAs($this->admin)->delete(route('admin.property-projects.destroy', $project));
        $this->assertSoftDeleted($project);
    }

    public function test_property_file_upload_replace_and_delete(): void
    {
        Storage::fake('public');
        $project = PropertyProject::firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.property-files.store'), [
            'property_project_id' => $project->id,
            'file' => UploadedFile::fake()->create('brosur.pdf', 100, 'application/pdf'),
            'file_type' => 'brochure_pdf',
            'caption' => 'Brosur Uji',
            'is_primary' => '1',
        ])->assertRedirect(route('admin.property-files.index'));

        $file = PropertyFile::where('caption', 'Brosur Uji')->firstOrFail();
        Storage::disk('public')->assertExists($file->file_path);
        $this->assertSame(1, $project->files()->where('is_primary', true)->count());

        $oldPath = $file->file_path;
        $this->actingAs($this->admin)->put(route('admin.property-files.update', $file), [
            'property_project_id' => $project->id,
            'file' => UploadedFile::fake()->create('brosur-baru.pdf', 100, 'application/pdf'),
            'file_type' => 'brochure_pdf',
            'is_primary' => '1',
        ]);

        $file->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($file->file_path);

        $this->actingAs($this->admin)->delete(route('admin.property-files.destroy', $file));
        $this->assertModelMissing($file);
        Storage::disk('public')->assertMissing($file->file_path);
    }

    public function test_buyer_lead_crud_and_delete_protection(): void
    {
        $this->actingAs($this->admin)->post(route('admin.buyer-leads.store'), [
            'full_name' => 'Pembeli Uji',
            'phone' => '0811111111',
            'budget_min' => 300000000,
            'budget_max' => 200000000,
            'status' => 'new_lead',
        ])->assertSessionHasErrors('budget_max');

        $this->actingAs($this->admin)->post(route('admin.buyer-leads.store'), [
            'full_name' => 'Pembeli Uji',
            'phone' => '0811111111',
            'status' => 'new_lead',
        ])->assertRedirect(route('admin.buyer-leads.index'));

        $lead = BuyerLead::where('full_name', 'Pembeli Uji')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.buyer-leads.update', $lead), [
            'full_name' => 'Pembeli Uji',
            'phone' => '0811111111',
            'status' => 'closed',
        ]);
        $this->assertSame('closed', $lead->fresh()->status);

        $this->actingAs($this->admin)->delete(route('admin.buyer-leads.destroy', $lead));
        $this->assertModelMissing($lead);

        $leadWithAkad = DigitalAkad::firstOrFail()->buyerLead;
        $this->actingAs($this->admin)->delete(route('admin.buyer-leads.destroy', $leadWithAkad))
            ->assertSessionHas('error');
        $this->assertModelExists($leadWithAkad);
    }

    public function test_digital_akad_crud_generates_number_and_protects_financial_records(): void
    {
        $lead = BuyerLead::doesntHave('digitalAkads')->firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.digital-akads.store'), [
            'property_project_id' => $lead->property_project_id,
            'buyer_lead_id' => $lead->id,
            'akad_type' => 'akad_murabahah',
            'transaction_amount' => 600000000,
            'dp_amount' => 120000000,
            'tenor_months' => 60,
            'terms_content' => 'Isi akad uji',
            'buyer_signed_at' => '2026-10-01T10:00',
            'status' => 'draft',
        ])->assertRedirect(route('admin.digital-akads.index'));

        $akad = DigitalAkad::where('buyer_lead_id', $lead->id)->firstOrFail();
        $this->assertMatchesRegularExpression('#^AKAD/\d{6}/\d{4}$#', $akad->akad_number);
        $this->assertNotNull($akad->buyer_signed_at);

        $this->actingAs($this->admin)->put(route('admin.digital-akads.update', $akad), [
            'property_project_id' => $lead->property_project_id,
            'buyer_lead_id' => $lead->id,
            'akad_type' => 'akad_murabahah',
            'transaction_amount' => 600000000,
            'dp_amount' => 700000000,
            'tenor_months' => 60,
            'terms_content' => 'Isi akad uji',
            'status' => 'signed',
        ])->assertSessionHasErrors('dp_amount');

        // Akad yang komisinya sudah menyangkut dana tidak bisa dihapus
        $settledAkad = Commission::where('status', Commission::STATUS_SETTLED)->firstOrFail()->digitalAkad;
        $this->actingAs($this->admin)->delete(route('admin.digital-akads.destroy', $settledAkad))->assertSessionHas('error');
        $this->assertModelExists($settledAkad);

        // Akad yang komisinya belum menyangkut dana boleh dihapus beserta komisinya
        $dueAkad = Commission::where('status', Commission::STATUS_DUE)->firstOrFail()->digitalAkad;
        $this->actingAs($this->admin)->delete(route('admin.digital-akads.destroy', $dueAkad));
        $this->assertModelMissing($dueAkad);
        $this->assertSame(0, Commission::where('digital_akad_id', $dueAkad->id)->count());
    }

}
