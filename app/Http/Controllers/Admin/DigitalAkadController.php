<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use App\Models\BuyerLead;
use App\Models\DigitalAkad;
use App\Models\PropertyProject;
use App\Support\CommissionSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DigitalAkadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = DigitalAkad::with(['propertyProject', 'buyerLead', 'agencyPartner']);

        // Filter: Pencarian nomor akad atau nama pembeli
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('akad_number', 'like', "%{$search}%")
                    ->orWhereHas('buyerLead', fn ($b) => $b->where('full_name', 'like', "%{$search}%"));
            });
        }

        foreach (['akad_type', 'status'] as $filter) {
            if ($value = $request->input($filter)) {
                $query->where($filter, $value);
            }
        }

        $digitalAkads = $query->latest()->paginate(10)->withQueryString();

        return view('admin.digital-akads.index', compact('digitalAkads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.digital-akads.create', [
            'digitalAkad' => new DigitalAkad(['status' => 'draft', 'dp_amount' => 0, 'tenor_months' => 0]),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['akad_number'] = $this->nextAkadNumber();

        if ($request->hasFile('document_pdf')) {
            $validated['document_pdf_path'] = $request->file('document_pdf')->store('documents/akads', 'public');
        }
        unset($validated['document_pdf']);

        $digitalAkad = DigitalAkad::create($validated);
        $termins = CommissionSchedule::createForAkad($digitalAkad, $request->user())->count();

        return redirect()
            ->route('admin.digital-akads.index')
            ->with('success', "Akad '{$digitalAkad->akad_number}' berhasil diterbitkan." . $this->commissionMessage($termins, 0));
    }

    /**
     * Display the specified resource.
     */
    public function show(DigitalAkad $digitalAkad): View
    {
        $digitalAkad->load(['propertyProject', 'buyerLead', 'agencyPartner', 'commissions']);

        return view('admin.digital-akads.show', compact('digitalAkad'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DigitalAkad $digitalAkad): View
    {
        return view('admin.digital-akads.edit', [
            'digitalAkad' => $digitalAkad,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DigitalAkad $digitalAkad): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        if ($request->hasFile('document_pdf')) {
            if ($digitalAkad->document_pdf_path) {
                Storage::disk('public')->delete($digitalAkad->document_pdf_path);
            }
            $validated['document_pdf_path'] = $request->file('document_pdf')->store('documents/akads', 'public');
        }
        unset($validated['document_pdf']);

        $digitalAkad->update($validated);

        // Akad sah → terbitkan termin komisi; akad batal → termin yang belum jatuh tempo gugur
        $created = CommissionSchedule::createForAkad($digitalAkad, $request->user())->count();
        $voided = $digitalAkad->status === 'cancelled' ? CommissionSchedule::voidPendingForAkad($digitalAkad, $request->user()) : 0;

        return redirect()
            ->route('admin.digital-akads.index')
            ->with('success', "Akad '{$digitalAkad->akad_number}' berhasil diperbarui." . $this->commissionMessage($created, $voided));
    }

    /**
     * Remove the specified resource from storage.
     * Komisi yang belum menyangkut dana ikut terhapus (cascadeOnDelete).
     */
    public function destroy(DigitalAkad $digitalAkad): RedirectResponse
    {
        if ($digitalAkad->hasFinancialRecords()) {
            return redirect()
                ->route('admin.digital-akads.index')
                ->with('error', "Akad '{$digitalAkad->akad_number}' tidak dapat dihapus karena komisinya sudah menyangkut dana yang diterima atau diteruskan. Batalkan akad jika transaksi tidak jadi.");
        }

        if ($digitalAkad->document_pdf_path) {
            Storage::disk('public')->delete($digitalAkad->document_pdf_path);
        }

        $akadNumber = $digitalAkad->akad_number;
        $digitalAkad->delete();

        return redirect()
            ->route('admin.digital-akads.index')
            ->with('success', "Akad '{$akadNumber}' beserta komisinya telah dihapus.");
    }

    private function commissionMessage(int $created, int $voided): string
    {
        return ($created ? " {$created} termin komisi diterbitkan." : '')
            . ($voided ? " {$voided} termin komisi yang belum jatuh tempo digugurkan." : '');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'property_project_id' => ['required', 'exists:property_projects,id'],
            'buyer_lead_id' => ['required', 'exists:buyer_leads,id'],
            'agency_partner_id' => ['nullable', 'exists:agency_partners,id'],
            'akad_type' => ['required', Rule::in(array_keys(DigitalAkad::AKAD_TYPES))],
            'transaction_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'dp_amount' => ['required', 'numeric', 'min:0', 'lte:transaction_amount'],
            'tenor_months' => ['required', 'integer', 'min:0', 'max:600'],
            'terms_content' => ['required', 'string'],
            'document_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'buyer_signed_at' => ['nullable', 'date'],
            'agency_signed_at' => ['nullable', 'date'],
            'developer_signed_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(DigitalAkad::STATUSES))],
        ]);
    }

    /**
     * Nomor akad berformat AKAD/YYYYMM/XXXX, berurutan per bulan.
     */
    private function nextAkadNumber(): string
    {
        $prefix = 'AKAD/' . now()->format('Ym') . '/';
        $sequence = DigitalAkad::where('akad_number', 'like', "{$prefix}%")->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DigitalAkad::where('akad_number', $number)->exists());

        return $number;
    }

    private function formOptions(): array
    {
        return [
            'projectOptions' => PropertyProject::orderBy('title')->pluck('title', 'id')->all(),
            'leadOptions' => BuyerLead::orderBy('full_name')->get()
                ->mapWithKeys(fn (BuyerLead $lead) => [$lead->id => "{$lead->full_name} ({$lead->phone})"])
                ->all(),
            'agencyOptions' => AgencyPartner::orderBy('agency_name')->pluck('agency_name', 'id')->all(),
        ];
    }
}
