<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use App\Models\PropertyProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pengajuan listing oleh agency partner (US-07).
 * Listing tersimpan sebagai draft; hanya admin yang dapat menayangkannya.
 */
class PropertyProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $agencyPartner = $request->user()->agencyPartner;

        $propertyProjects = $agencyPartner
            ? $agencyPartner->propertyProjects()->withCount('legalDocs')->latest()->paginate(10)
            : null;

        return view('agency.property-projects.index', compact('agencyPartner', 'propertyProjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->verifiedAgencyOf($request);

        return view('agency.property-projects.create', [
            'propertyProject' => new PropertyProject(['commission_rate' => 2.50, 'status' => 'available']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $agencyPartner = $this->verifiedAgencyOf($request);

        $validated = $request->validate([
            ...PropertyProject::validationRules(),
            'legal_doc' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $propertyProject = $agencyPartner->propertyProjects()->create([
            ...collect($validated)->except(['legal_doc', 'photos'])->all(),
            'slug' => PropertyProject::uniqueSlug($validated['title']),
            'is_verified' => false,
        ]);

        $this->storeFiles($request, $propertyProject);

        return redirect()
            ->route('agency.property-projects.index')
            ->with('success', "Listing '{$propertyProject->title}' berhasil diajukan dan menunggu verifikasi admin.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, PropertyProject $propertyProject): View
    {
        $this->ensureOwnedBy($request, $propertyProject);
        $propertyProject->load('files');

        return view('agency.property-projects.show', compact('propertyProject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, PropertyProject $propertyProject): View
    {
        $this->ensureEditable($request, $propertyProject);

        return view('agency.property-projects.edit', compact('propertyProject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyProject $propertyProject): RedirectResponse
    {
        $this->ensureEditable($request, $propertyProject);

        $validated = $request->validate([
            ...PropertyProject::validationRules(),
            'legal_doc' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $data = collect($validated)->except(['legal_doc', 'photos'])->all();
        if ($data['title'] !== $propertyProject->title) {
            $data['slug'] = PropertyProject::uniqueSlug($data['title'], $propertyProject->id);
        }

        $propertyProject->update($data);
        $this->storeFiles($request, $propertyProject);

        return redirect()
            ->route('agency.property-projects.show', $propertyProject)
            ->with('success', 'Pengajuan listing berhasil diperbarui.');
    }

    /**
     * Simpan dokumen kuasa developer dan foto yang diunggah bersama form.
     */
    private function storeFiles(Request $request, PropertyProject $propertyProject): void
    {
        if ($request->hasFile('legal_doc')) {
            $propertyProject->files()->create([
                'file_path' => $request->file('legal_doc')->store('properties/legal', 'public'),
                'file_type' => 'legal_doc',
                'caption' => 'PKS / Surat Kuasa Developer',
                'is_primary' => false,
            ]);
        }

        $hasPrimary = $propertyProject->files()->where('is_primary', true)->exists();

        foreach ($request->file('photos', []) as $photo) {
            $propertyProject->files()->create([
                'file_path' => $photo->store('properties/photos', 'public'),
                'file_type' => 'photo',
                'caption' => null,
                'is_primary' => ! $hasPrimary,
            ]);
            $hasPrimary = true;
        }
    }

    /**
     * Hanya agency partner berstatus terverifikasi yang boleh mengajukan listing.
     */
    private function verifiedAgencyOf(Request $request): AgencyPartner
    {
        $agencyPartner = $request->user()->agencyPartner;

        abort_unless($agencyPartner?->isVerified(), 403, 'Profil agency Anda belum diverifikasi admin, sehingga belum dapat mengajukan listing.');

        return $agencyPartner;
    }

    private function ensureOwnedBy(Request $request, PropertyProject $propertyProject): void
    {
        abort_unless(
            $propertyProject->agency_partner_id !== null
                && $propertyProject->agency_partner_id === $request->user()->agencyPartner?->id,
            403
        );
    }

    /**
     * Listing hanya bisa diubah oleh pemiliknya selama belum ditayangkan admin.
     */
    private function ensureEditable(Request $request, PropertyProject $propertyProject): void
    {
        $this->ensureOwnedBy($request, $propertyProject);
        $this->verifiedAgencyOf($request);

        abort_if($propertyProject->is_verified, 403, 'Listing yang sudah tayang tidak dapat diubah. Hubungi admin untuk perubahan.');
    }
}
