<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="$propertyProject->title" :index-url="route('agency.property-projects.index')" index-label="Listing Saya">
            @unless ($propertyProject->is_verified)
                <a href="{{ route('agency.property-projects.edit', $propertyProject) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Pengajuan</a>
            @endunless
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            @if ($propertyProject->is_verified)
                <div class="p-4 rounded-xl bg-primary-50 border border-primary-200 text-primary-800 text-sm flex items-center justify-between gap-4">
                    <span>Listing ini sudah diverifikasi dan tayang di katalog.</span>
                    <a href="{{ route('catalog.show', $propertyProject) }}" class="font-semibold hover:underline">Lihat di katalog →</a>
                </div>
            @else
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Menunggu verifikasi admin. Anda masih dapat mengubah pengajuan ini selama belum ditayangkan.
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Developer">{{ $propertyProject->developer_name }}</x-admin.detail>
                    <x-admin.detail label="Tipe / Skema">
                        {{ \App\Models\PropertyProject::PROPERTY_TYPES[$propertyProject->property_type] ?? $propertyProject->property_type }} ·
                        {{ \App\Models\PropertyProject::SYARIAH_SCHEMES[$propertyProject->syariah_scheme] ?? $propertyProject->syariah_scheme }}
                    </x-admin.detail>
                    <x-admin.detail label="Harga">Rp {{ number_format($propertyProject->price, 0, ',', '.') }}</x-admin.detail>
                    <x-admin.detail label="Komisi Agen">{{ $propertyProject->commission_rate }}%</x-admin.detail>
                    <x-admin.detail label="Lokasi">{{ collect([$propertyProject->district, $propertyProject->city, $propertyProject->province])->filter()->implode(', ') }}</x-admin.detail>
                    <x-admin.detail label="Alamat Lengkap">{{ $propertyProject->address_detail }}</x-admin.detail>
                    <x-admin.detail label="Deskripsi">{{ $propertyProject->description }}</x-admin.detail>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <h3 class="font-bold text-gray-800 mb-3">Berkas Terlampir</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($propertyProject->files as $file)
                        <li class="py-2 flex items-center justify-between gap-4 text-sm">
                            <span class="text-gray-800">{{ $file->caption ?: basename($file->file_path) }}</span>
                            <span class="text-xs text-gray-500">{{ \App\Models\PropertyFile::FILE_TYPES[$file->file_type] ?? $file->file_type }}{{ $file->is_primary ? ' · Utama' : '' }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400 italic">Belum ada berkas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
