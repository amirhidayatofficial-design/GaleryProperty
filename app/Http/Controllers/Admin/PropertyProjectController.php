<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use App\Models\PropertyProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = PropertyProject::with('agencyPartner');

        // Filter: Pencarian judul, developer, atau kota
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('developer_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        foreach (['property_type', 'syariah_scheme', 'status'] as $filter) {
            if ($value = $request->input($filter)) {
                $query->where($filter, $value);
            }
        }

        $propertyProjects = $query->latest()->paginate(10)->withQueryString();

        return view('admin.property-projects.index', compact('propertyProjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.property-projects.create', [
            'propertyProject' => new PropertyProject(['commission_rate' => 2.50, 'status' => 'available']),
            'agencyOptions' => $this->agencyOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['slug'] = PropertyProject::uniqueSlug($validated['title']);
        // Projek baru selalu menunggu verifikasi; penayangan lewat menu Verifikasi Listing
        $validated['is_verified'] = false;

        $propertyProject = PropertyProject::create($validated);

        return redirect()
            ->route('admin.property-projects.index')
            ->with('success', "Projek '{$propertyProject->title}' berhasil ditambahkan.");
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyProject $propertyProject): View
    {
        $propertyProject->load(['agencyPartner', 'files'])
            ->loadCount(['buyerLeads', 'digitalAkads']);

        return view('admin.property-projects.show', compact('propertyProject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PropertyProject $propertyProject): View
    {
        return view('admin.property-projects.edit', [
            'propertyProject' => $propertyProject,
            'agencyOptions' => $this->agencyOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyProject $propertyProject): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        if ($validated['title'] !== $propertyProject->title) {
            $validated['slug'] = PropertyProject::uniqueSlug($validated['title'], $propertyProject->id);
        }

        $propertyProject->update($validated);

        return redirect()
            ->route('admin.property-projects.index')
            ->with('success', "Data projek '{$propertyProject->title}' berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(PropertyProject $propertyProject): RedirectResponse
    {
        $title = $propertyProject->title;
        $propertyProject->delete();

        return redirect()
            ->route('admin.property-projects.index')
            ->with('success', "Projek '{$title}' telah dihapus (soft delete).");
    }

    /**
     * Status tayang (is_verified) sengaja tidak diterima di sini; diubah lewat ListingVerificationController.
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'agency_partner_id' => ['nullable', 'exists:agency_partners,id'],
            ...PropertyProject::validationRules(),
        ]);
    }

    private function agencyOptions(): array
    {
        return AgencyPartner::orderBy('agency_name')->pluck('agency_name', 'id')->all();
    }
}
