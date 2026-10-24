<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgencyPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = AgencyPartner::with('user')->withCount(['propertyProjects', 'buyerLeads']);

        // Filter: Pencarian nama agency, nomor izin, atau akun pemilik
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('agency_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        // Filter: Status verifikasi
        if ($status = $request->input('verification_status')) {
            $query->where('verification_status', $status);
        }

        $agencyPartners = $query->latest()->paginate(10)->withQueryString();

        return view('admin.agency-partners.index', compact('agencyPartners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.agency-partners.create', [
            'agencyPartner' => new AgencyPartner(['verification_status' => 'pending']),
            'userOptions' => $this->userOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['verified_at'] = $validated['verification_status'] === 'verified' ? now() : null;

        $agencyPartner = AgencyPartner::create($validated);

        return redirect()
            ->route('admin.agency-partners.index')
            ->with('success', "Agency partner '{$agencyPartner->agency_name}' berhasil ditambahkan.");
    }

    /**
     * Display the specified resource.
     */
    public function show(AgencyPartner $agencyPartner): View
    {
        $agencyPartner->load(['user', 'propertyProjects'])
            ->loadCount(['buyerLeads', 'digitalAkads', 'commissions']);

        return view('admin.agency-partners.show', compact('agencyPartner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AgencyPartner $agencyPartner): View
    {
        return view('admin.agency-partners.edit', [
            'agencyPartner' => $agencyPartner,
            'userOptions' => $this->userOptions($agencyPartner),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AgencyPartner $agencyPartner): RedirectResponse
    {
        $validated = $this->validateRequest($request, $agencyPartner);

        // Tanggal verifikasi hanya diisi saat status berubah menjadi terverifikasi
        if ($validated['verification_status'] !== 'verified') {
            $validated['verified_at'] = null;
        } elseif (! $agencyPartner->verified_at) {
            $validated['verified_at'] = now();
        }

        $agencyPartner->update($validated);

        return redirect()
            ->route('admin.agency-partners.index')
            ->with('success', "Data agency partner '{$agencyPartner->agency_name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AgencyPartner $agencyPartner): RedirectResponse
    {
        // Proteksi: tabel commissions memakai restrictOnDelete ke agency_partners
        if ($agencyPartner->commissions()->exists()) {
            return redirect()
                ->route('admin.agency-partners.index')
                ->with('error', "Agency partner '{$agencyPartner->agency_name}' tidak dapat dihapus karena masih memiliki riwayat komisi.");
        }

        $agencyName = $agencyPartner->agency_name;
        $agencyPartner->delete();

        return redirect()
            ->route('admin.agency-partners.index')
            ->with('success', "Agency partner '{$agencyName}' telah berhasil dihapus.");
    }

    private function validateRequest(Request $request, ?AgencyPartner $agencyPartner = null): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'agency_partner'),
                Rule::unique('agency_partners', 'user_id')->ignore($agencyPartner?->id),
            ],
            'agency_name' => ['required', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'company_address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', Rule::in(AgencyPartner::BANKS)],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'verification_status' => ['required', Rule::in(array_keys(AgencyPartner::VERIFICATION_STATUSES))],
        ]);
    }

    /**
     * Akun ber-role agency_partner yang belum terhubung ke profil agency lain.
     */
    private function userOptions(?AgencyPartner $agencyPartner = null): array
    {
        return User::where('role', 'agency_partner')
            ->where(function ($q) use ($agencyPartner) {
                $q->doesntHave('agencyPartner');
                if ($agencyPartner) {
                    $q->orWhere('id', $agencyPartner->user_id);
                }
            })
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => "{$user->name} ({$user->email})"])
            ->all();
    }
}
