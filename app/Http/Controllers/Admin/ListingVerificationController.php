<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman khusus admin untuk meninjau dan menayangkan listing projek properti.
 * Hanya dari sini listing bisa ditayangkan ke katalog atau ditarik kembali.
 */
class ListingVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab') === 'published' ? 'published' : 'pending';

        $propertyProjects = PropertyProject::query()
            ->with('agencyPartner')
            ->withCount(['legalDocs', 'photos'])
            ->where('is_verified', $tab === 'published')
            // Antrean diurutkan dari pengajuan terlama agar tidak ada yang terlewat
            ->when($tab === 'pending', fn ($query) => $query->oldest(), fn ($query) => $query->latest('updated_at'))
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'pending' => PropertyProject::where('is_verified', false)->count(),
            'published' => PropertyProject::where('is_verified', true)->count(),
        ];

        return view('admin.listing-verifications.index', compact('propertyProjects', 'tab', 'counts'));
    }

    public function show(PropertyProject $propertyProject): View
    {
        $propertyProject->load(['agencyPartner', 'files']);

        return view('admin.listing-verifications.show', [
            'propertyProject' => $propertyProject,
            'checks' => $this->checks($propertyProject),
        ]);
    }

    public function approve(PropertyProject $propertyProject): RedirectResponse
    {
        if (! $propertyProject->legalDocs()->exists()) {
            return back()->with('error', 'Listing belum bisa ditayangkan: unggah dulu Dokumen Legalitas (PKS / surat kuasa developer).');
        }

        $propertyProject->update(['is_verified' => true]);

        return redirect()
            ->route('admin.listing-verifications.index')
            ->with('success', "Listing '{$propertyProject->title}' telah diverifikasi dan tayang di katalog.");
    }

    public function unpublish(PropertyProject $propertyProject): RedirectResponse
    {
        $propertyProject->update(['is_verified' => false]);

        return redirect()
            ->route('admin.listing-verifications.show', $propertyProject)
            ->with('success', "Listing '{$propertyProject->title}' ditarik dari katalog dan kembali menunggu verifikasi.");
    }

    /**
     * Daftar periksa yang ditampilkan saat meninjau. Hanya dokumen legalitas yang wajib.
     *
     * @return list<array{label: string, passed: bool, required: bool, hint: string}>
     */
    private function checks(PropertyProject $propertyProject): array
    {
        return [
            [
                'label' => 'Dokumen Legalitas (PKS / surat kuasa developer)',
                'passed' => $propertyProject->files->where('file_type', 'legal_doc')->isNotEmpty(),
                'required' => true,
                'hint' => 'Wajib. Dasar agency mewakili developer saat menandatangani akad.',
            ],
            [
                'label' => 'Agency penanggung jawab terverifikasi',
                'passed' => (bool) $propertyProject->agencyPartner?->isVerified(),
                'required' => false,
                'hint' => 'Pastikan agency sudah lolos verifikasi profil.',
            ],
            [
                'label' => 'Memiliki foto projek',
                'passed' => $propertyProject->files->where('file_type', 'photo')->isNotEmpty(),
                'required' => false,
                'hint' => 'Listing tanpa foto tetap bisa tayang, tetapi kurang menarik di katalog.',
            ],
        ];
    }
}
