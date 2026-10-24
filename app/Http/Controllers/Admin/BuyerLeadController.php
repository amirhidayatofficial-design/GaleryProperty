<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use App\Models\BuyerLead;
use App\Models\PropertyProject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BuyerLeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = BuyerLead::with(['agencyPartner', 'propertyProject']);

        // Filter: Pencarian nama, nomor WhatsApp, atau email calon pembeli
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($agencyId = $request->input('agency_partner_id')) {
            $query->where('agency_partner_id', $agencyId);
        }

        $buyerLeads = $query->latest()->paginate(10)->withQueryString();
        $agencyOptions = AgencyPartner::orderBy('agency_name')->pluck('agency_name', 'id')->all();

        return view('admin.buyer-leads.index', compact('buyerLeads', 'agencyOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.buyer-leads.create', [
            'buyerLead' => new BuyerLead(['status' => 'new_lead']),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $buyerLead = BuyerLead::create($this->validateRequest($request));

        return redirect()
            ->route('admin.buyer-leads.index')
            ->with('success', "Lead calon pembeli '{$buyerLead->full_name}' berhasil ditambahkan.");
    }

    /**
     * Display the specified resource.
     */
    public function show(BuyerLead $buyerLead): View
    {
        $buyerLead->load(['user', 'agencyPartner', 'propertyProject', 'digitalAkads']);

        return view('admin.buyer-leads.show', compact('buyerLead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BuyerLead $buyerLead): View
    {
        return view('admin.buyer-leads.edit', [
            'buyerLead' => $buyerLead,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BuyerLead $buyerLead): RedirectResponse
    {
        $buyerLead->update($this->validateRequest($request));

        return redirect()
            ->route('admin.buyer-leads.index')
            ->with('success', "Data lead '{$buyerLead->full_name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BuyerLead $buyerLead): RedirectResponse
    {
        // Proteksi: tabel digital_akads memakai restrictOnDelete ke buyer_leads
        if ($buyerLead->digitalAkads()->exists()) {
            return redirect()
                ->route('admin.buyer-leads.index')
                ->with('error', "Lead '{$buyerLead->full_name}' tidak dapat dihapus karena sudah memiliki akad digital.");
        }

        $fullName = $buyerLead->full_name;
        $buyerLead->delete();

        return redirect()
            ->route('admin.buyer-leads.index')
            ->with('success', "Lead '{$fullName}' telah berhasil dihapus.");
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'buyer')],
            'agency_partner_id' => ['nullable', 'exists:agency_partners,id'],
            'property_project_id' => ['nullable', 'exists:property_projects,id'],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', ...User::phoneRules()],
            'email' => ['nullable', 'email', 'max:150'],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'max:9999999999999', 'gte:budget_min'],
            'preferred_location' => ['nullable', 'string', 'max:150'],
            'preferred_scheme' => ['nullable', Rule::in(array_keys(PropertyProject::SYARIAH_SCHEMES))],
            'status' => ['required', Rule::in(array_keys(BuyerLead::STATUSES))],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function formOptions(): array
    {
        return [
            'userOptions' => User::where('role', 'buyer')->orderBy('name')->get()
                ->mapWithKeys(fn (User $user) => [$user->id => "{$user->name} ({$user->email})"])
                ->all(),
            'agencyOptions' => AgencyPartner::orderBy('agency_name')->pluck('agency_name', 'id')->all(),
            'projectOptions' => PropertyProject::orderBy('title')->pluck('title', 'id')->all(),
        ];
    }
}
