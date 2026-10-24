<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="$propertyProject->title" :index-url="route('admin.property-projects.index')" index-label="Projek Properti">
            <a href="{{ route('admin.listing-verifications.show', $propertyProject) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                {{ $propertyProject->is_verified ? 'Kelola Penayangan' : 'Tinjau Verifikasi' }}
            </a>
            <a href="{{ route('admin.property-projects.edit', $propertyProject) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Data</a>
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Status">
                        <x-admin.badge :value="$propertyProject->status" :labels="\App\Models\PropertyProject::STATUSES" />
                        <span class="ml-2 text-xs {{ $propertyProject->is_verified ? 'text-primary-700' : 'text-gray-400' }}">
                            {{ $propertyProject->is_verified ? 'Terverifikasi admin' : 'Belum diverifikasi' }}
                        </span>
                    </x-admin.detail>
                    <x-admin.detail label="Slug">{{ $propertyProject->slug }}</x-admin.detail>
                    <x-admin.detail label="Developer">{{ $propertyProject->developer_name }}</x-admin.detail>
                    <x-admin.detail label="Agency">
                        @if ($propertyProject->agencyPartner)
                            <a href="{{ route('admin.agency-partners.show', $propertyProject->agencyPartner) }}" class="text-primary-700 hover:underline">{{ $propertyProject->agencyPartner->agency_name }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Tipe / Skema">
                        {{ \App\Models\PropertyProject::PROPERTY_TYPES[$propertyProject->property_type] ?? $propertyProject->property_type }} ·
                        {{ \App\Models\PropertyProject::SYARIAH_SCHEMES[$propertyProject->syariah_scheme] ?? $propertyProject->syariah_scheme }}
                    </x-admin.detail>
                    <x-admin.detail label="Harga">Rp {{ number_format($propertyProject->price, 0, ',', '.') }}</x-admin.detail>
                    <x-admin.detail label="Komisi Agen">{{ $propertyProject->commission_rate }}%</x-admin.detail>
                    <x-admin.detail label="Lokasi">{{ collect([$propertyProject->district, $propertyProject->city, $propertyProject->province])->filter()->implode(', ') }}</x-admin.detail>
                    <x-admin.detail label="Alamat Lengkap">{{ $propertyProject->address_detail }}</x-admin.detail>
                    <x-admin.detail label="Spesifikasi">
                        {{ $propertyProject->bedroom ?? '-' }} KT · {{ $propertyProject->bathroom ?? '-' }} KM ·
                        LB {{ $propertyProject->building_area ?? '-' }} m² · LT {{ $propertyProject->land_area ?? '-' }} m²
                    </x-admin.detail>
                    <x-admin.detail label="Deskripsi">{{ $propertyProject->description }}</x-admin.detail>
                    <x-admin.detail label="Jaminan Halal">{{ $propertyProject->halal_guarantee_notes }}</x-admin.detail>
                    <x-admin.detail label="Leads / Akad">{{ $propertyProject->buyer_leads_count }} leads · {{ $propertyProject->digital_akads_count }} akad</x-admin.detail>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-gray-800">Berkas Projek</h3>
                    <a href="{{ route('admin.property-files.create', ['property_project_id' => $propertyProject->id]) }}" class="text-sm font-semibold text-primary-700 hover:underline">+ Unggah Berkas</a>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($propertyProject->files as $file)
                        <li class="py-2 flex items-center justify-between gap-4">
                            <a href="{{ route('admin.property-files.show', $file) }}" class="text-sm text-primary-700 hover:underline">{{ $file->caption ?: basename($file->file_path) }}</a>
                            <span class="text-xs text-gray-500">
                                {{ \App\Models\PropertyFile::FILE_TYPES[$file->file_type] ?? $file->file_type }}{{ $file->is_primary ? ' · Utama' : '' }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400 italic">Belum ada berkas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
