<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="$propertyFile->caption ?: 'Detail Berkas'" :index-url="route('admin.property-files.index')" index-label="Berkas Properti">
            <a href="{{ route('admin.property-files.edit', $propertyFile) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Data</a>
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                @if (! $fileExists)
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                        Berkas fisik tidak ditemukan di penyimpanan. Data contoh dari seeder memang hanya berisi nama berkas; unggah berkas baru lewat tombol Ubah Data.
                    </div>
                @elseif (str_ends_with(strtolower($propertyFile->file_path), '.pdf'))
                    <a href="{{ Storage::url($propertyFile->file_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-black text-white text-sm font-semibold rounded-lg transition">Buka PDF</a>
                @else
                    <img src="{{ Storage::url($propertyFile->file_path) }}" alt="{{ $propertyFile->caption }}" class="max-h-96 rounded-xl border border-gray-100" />
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Projek">
                        @if ($propertyFile->propertyProject)
                            <a href="{{ route('admin.property-projects.show', $propertyFile->propertyProject) }}" class="text-primary-700 hover:underline">{{ $propertyFile->propertyProject->title }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Tipe Berkas">
                        <x-admin.badge :value="$propertyFile->file_type" :labels="\App\Models\PropertyFile::FILE_TYPES" />
                    </x-admin.detail>
                    <x-admin.detail label="Berkas Utama">{{ $propertyFile->is_primary ? 'Ya' : 'Tidak' }}</x-admin.detail>
                    <x-admin.detail label="Keterangan">{{ $propertyFile->caption }}</x-admin.detail>
                    <x-admin.detail label="Lokasi Berkas">{{ $propertyFile->file_path }}</x-admin.detail>
                    <x-admin.detail label="Diunggah">{{ $propertyFile->created_at?->format('d M Y, H:i') }}</x-admin.detail>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
