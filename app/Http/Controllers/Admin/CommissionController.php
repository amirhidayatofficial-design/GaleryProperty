<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use App\Models\Commission;
use App\Models\DigitalAkad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Commission::with(['digitalAkad', 'agencyPartner']);

        // Filter: Pencarian nomor akad atau nama agency
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('digitalAkad', fn ($a) => $a->where('akad_number', 'like', "%{$search}%"))
                    ->orWhereHas('agencyPartner', fn ($a) => $a->where('agency_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $commissions = $query->latest()->paginate(10)->withQueryString();

        return view('admin.commissions.index', compact('commissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.commissions.create', [
            'commission' => new Commission(['status' => 'pending', 'platform_fee' => 500000]),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        if ($request->hasFile('payment_proof')) {
            $validated['payment_proof_path'] = $request->file('payment_proof')->store('receipts/commissions', 'public');
        }
        unset($validated['payment_proof']);

        Commission::create($validated);

        return redirect()
            ->route('admin.commissions.index')
            ->with('success', 'Data komisi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Commission $commission): View
    {
        $commission->load(['digitalAkad.propertyProject', 'agencyPartner']);

        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commission $commission): View
    {
        return view('admin.commissions.edit', [
            'commission' => $commission,
            ...$this->formOptions($commission),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Commission $commission): RedirectResponse
    {
        $validated = $this->validateRequest($request, $commission);

        if ($request->hasFile('payment_proof')) {
            if ($commission->payment_proof_path) {
                Storage::disk('public')->delete($commission->payment_proof_path);
            }
            $validated['payment_proof_path'] = $request->file('payment_proof')->store('receipts/commissions', 'public');
        }
        unset($validated['payment_proof']);

        $commission->update($validated);

        return redirect()
            ->route('admin.commissions.index')
            ->with('success', 'Data komisi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Commission $commission): RedirectResponse
    {
        if ($commission->payment_proof_path) {
            Storage::disk('public')->delete($commission->payment_proof_path);
        }

        $commission->delete();

        return redirect()
            ->route('admin.commissions.index')
            ->with('success', 'Data komisi telah berhasil dihapus.');
    }

    /**
     * Validasi input lalu hitung komisi total & bersih secara otomatis.
     */
    private function validateRequest(Request $request, ?Commission $commission = null): array
    {
        $validated = $request->validate([
            'digital_akad_id' => [
                'required',
                'exists:digital_akads,id',
                Rule::unique('commissions', 'digital_akad_id')->ignore($commission?->id),
            ],
            'agency_partner_id' => ['required', 'exists:agency_partners,id'],
            'base_transaction_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'platform_fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(Commission::STATUSES))],
            'paid_at' => ['nullable', 'date'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        // Komisi hanya boleh dibuat dari akad yang sah (ditandatangani ketiga pihak)
        $akad = DigitalAkad::find($validated['digital_akad_id']);
        if (! $akad->isFullySigned() || $akad->status !== 'signed') {
            throw ValidationException::withMessages([
                'digital_akad_id' => 'Akad ini belum berstatus Ditandatangani oleh pembeli, agency, dan developer.',
            ]);
        }

        $validated['total_commission'] = round($validated['base_transaction_amount'] * $validated['commission_rate'] / 100, 2);
        $validated['net_commission'] = round($validated['total_commission'] - $validated['platform_fee'], 2);

        if ($validated['net_commission'] < 0) {
            throw ValidationException::withMessages([
                'platform_fee' => 'Biaya platform tidak boleh melebihi total komisi (Rp ' . number_format($validated['total_commission'], 0, ',', '.') . ').',
            ]);
        }

        // Komisi berstatus dibayar wajib punya tanggal pembayaran
        if ($validated['status'] === 'paid' && empty($validated['paid_at'])) {
            $validated['paid_at'] = now();
        }

        return $validated;
    }

    /**
     * Akad sah yang belum punya komisi (relasi 1-to-1), ditambah akad milik komisi yang sedang diubah.
     */
    private function formOptions(?Commission $commission = null): array
    {
        return [
            'akadOptions' => DigitalAkad::with('buyerLead')
                ->where(function ($q) use ($commission) {
                    $q->where(fn ($signed) => $signed
                        ->doesntHave('commission')
                        ->where('status', 'signed')
                        ->whereNotNull(['buyer_signed_at', 'agency_signed_at', 'developer_signed_at']));
                    if ($commission) {
                        $q->orWhere('id', $commission->digital_akad_id);
                    }
                })
                ->orderByDesc('id')
                ->get()
                ->mapWithKeys(fn (DigitalAkad $akad) => [
                    $akad->id => "{$akad->akad_number} - " . ($akad->buyerLead?->full_name ?? 'Tanpa pembeli')
                        . ' (Rp ' . number_format($akad->transaction_amount, 0, ',', '.') . ')',
                ])
                ->all(),
            'agencyOptions' => AgencyPartner::orderBy('agency_name')->pluck('agency_name', 'id')->all(),
        ];
    }
}
