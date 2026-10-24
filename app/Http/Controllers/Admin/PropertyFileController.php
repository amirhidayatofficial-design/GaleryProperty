<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyFile;
use App\Models\PropertyProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyFileController extends Controller
{
    /**
     * Folder penyimpanan (disk public) untuk setiap tipe berkas.
     */
    private const FOLDERS = [
        'photo' => 'properties/photos',
        'siteplan' => 'properties/siteplans',
        'brochure_pdf' => 'properties/brochures',
        'legal_doc' => 'properties/legal',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = PropertyFile::with('propertyProject');

        // Filter: Pencarian keterangan, nama berkas, atau judul projek
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('caption', 'like', "%{$search}%")
                    ->orWhere('file_path', 'like', "%{$search}%")
                    ->orWhereHas('propertyProject', fn ($p) => $p->where('title', 'like', "%{$search}%"));
            });
        }

        if ($fileType = $request->input('file_type')) {
            $query->where('file_type', $fileType);
        }

        if ($projectId = $request->input('property_project_id')) {
            $query->where('property_project_id', $projectId);
        }

        $propertyFiles = $query->latest()->paginate(10)->withQueryString();
        $projectOptions = $this->projectOptions();

        return view('admin.property-files.index', compact('propertyFiles', 'projectOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('admin.property-files.create', [
            'propertyFile' => new PropertyFile([
                'file_type' => 'photo',
                'property_project_id' => $request->input('property_project_id'),
            ]),
            'projectOptions' => $this->projectOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request, true);

        $validated['file_path'] = $request->file('file')->store(self::FOLDERS[$validated['file_type']], 'public');
        unset($validated['file']);

        $propertyFile = PropertyFile::create($validated);
        $this->ensureSinglePrimary($propertyFile);

        return redirect()
            ->route('admin.property-files.index')
            ->with('success', 'Berkas properti berhasil diunggah.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyFile $propertyFile): View
    {
        $propertyFile->load('propertyProject');
        $fileExists = Storage::disk('public')->exists($propertyFile->file_path);

        return view('admin.property-files.show', compact('propertyFile', 'fileExists'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PropertyFile $propertyFile): View
    {
        return view('admin.property-files.edit', [
            'propertyFile' => $propertyFile,
            'projectOptions' => $this->projectOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyFile $propertyFile): RedirectResponse
    {
        $validated = $this->validateRequest($request, false);

        // Ganti berkas fisik hanya jika admin mengunggah berkas baru
        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($propertyFile->file_path);
            $validated['file_path'] = $request->file('file')->store(self::FOLDERS[$validated['file_type']], 'public');
        }
        unset($validated['file']);

        $propertyFile->update($validated);
        $this->ensureSinglePrimary($propertyFile);

        return redirect()
            ->route('admin.property-files.index')
            ->with('success', 'Data berkas properti berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyFile $propertyFile): RedirectResponse
    {
        Storage::disk('public')->delete($propertyFile->file_path);
        $propertyFile->delete();

        return redirect()
            ->route('admin.property-files.index')
            ->with('success', 'Berkas properti telah berhasil dihapus.');
    }

    private function validateRequest(Request $request, bool $fileRequired): array
    {
        $validated = $request->validate([
            'property_project_id' => ['required', 'exists:property_projects,id'],
            'file' => [$fileRequired ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'file_type' => ['required', Rule::in(array_keys(PropertyFile::FILE_TYPES))],
            'caption' => ['nullable', 'string', 'max:150'],
            'is_primary' => ['boolean'],
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

        return $validated;
    }

    /**
     * Satu projek hanya boleh punya satu foto utama.
     */
    private function ensureSinglePrimary(PropertyFile $propertyFile): void
    {
        if ($propertyFile->is_primary) {
            PropertyFile::where('property_project_id', $propertyFile->property_project_id)
                ->where('id', '!=', $propertyFile->id)
                ->update(['is_primary' => false]);
        }
    }

    private function projectOptions(): array
    {
        return PropertyProject::orderBy('title')->pluck('title', 'id')->all();
    }
}
