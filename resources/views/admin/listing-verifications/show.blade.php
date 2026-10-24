<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            :title="$propertyProject->title"
            :subtitle="$propertyProject->is_verified ? 'Listing ini sudah tayang di katalog.' : 'Tinjau kelengkapan listing sebelum menayangkannya.'"
            :index-url="route('admin.listing-verifications.index', $propertyProject->is_verified ? ['tab' => 'published'] : [])"
            index-label="Verifikasi Listing"
        />
    </x-slot>

    @php($canApprove = collect($checks)->where('required', true)->every('passed'))

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- Kolom kiri: data listing & berkas --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h3 class="font-bold text-gray-800 mb-3">Data Listing</h3>
                        <dl class="divide-y divide-gray-100">
                            <x-admin.detail label="Developer">{{ $propertyProject->developer_name }}</x-admin.detail>
                            <x-admin.detail label="Agency">
                                @if ($propertyProject->agencyPartner)
                                    <a href="{{ route('admin.agency-partners.show', $propertyProject->agencyPartner) }}" class="text-primary-700 hover:underline">{{ $propertyProject->agencyPartner->agency_name }}</a>
                                @else
                                    <span class="text-gray-400">Tanpa agency</span>
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
                        </dl>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-gray-800">Berkas Terlampir</h3>
                            <a href="{{ route('admin.property-files.create', ['property_project_id' => $propertyProject->id]) }}" class="text-sm font-semibold text-primary-700 hover:underline">+ Unggah Berkas</a>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            @forelse ($propertyProject->files->sortBy(fn ($file) => $file->file_type !== 'legal_doc') as $file)
                                <li class="py-2.5 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm text-gray-800 truncate">{{ $file->caption ?: basename($file->file_path) }}</div>
                                        <div class="text-xs {{ $file->file_type === 'legal_doc' ? 'text-primary-700 font-semibold' : 'text-gray-500' }}">
                                            {{ \App\Models\PropertyFile::FILE_TYPES[$file->file_type] ?? $file->file_type }}{{ $file->is_primary ? ' · Utama' : '' }}
                                        </div>
                                    </div>
                                    @if (Storage::disk('public')->exists($file->file_path))
                                        <a href="{{ Storage::url($file->file_path) }}" target="_blank" rel="noopener" class="shrink-0 text-sm font-semibold text-primary-700 hover:underline">Buka</a>
                                    @else
                                        <span class="shrink-0 text-xs text-gray-400">Berkas tidak ditemukan</span>
                                    @endif
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-400 italic">Belum ada berkas.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Kolom kanan: hasil pemeriksaan & tombol keputusan --}}
                <div class="space-y-6 lg:sticky lg:top-24">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="font-bold text-gray-800 mb-4">Kelengkapan</h3>
                        <ul class="space-y-4">
                            @foreach ($checks as $check)
                                <li class="flex gap-3">
                                    <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full flex items-center justify-center text-[11px] font-bold
                                        {{ $check['passed'] ? 'bg-primary-100 text-primary-700' : ($check['required'] ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $check['passed'] ? '✓' : ($check['required'] ? '✗' : '!') }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">{{ $check['label'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $check['hint'] }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Status saat ini</span>
                            @if ($propertyProject->is_verified)
                                <x-admin.badge value="active" :labels="['active' => 'Sudah Tayang']" />
                            @else
                                <x-admin.badge value="pending" :labels="['pending' => 'Menunggu Verifikasi']" />
                            @endif
                        </div>

                        @if ($propertyProject->is_verified)
                            <a href="{{ route('admin.property-projects.show', $propertyProject) }}" class="block w-full text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                                Lihat Data Projek
                            </a>
                            <form method="POST" action="{{ route('admin.listing-verifications.unpublish', $propertyProject) }}"
                                  onsubmit="return confirm('Tarik listing ini dari katalog? Calon pembeli tidak akan bisa melihatnya lagi.');">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                                    Batalkan Penayangan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.listing-verifications.approve', $propertyProject) }}">
                                @csrf
                                <button type="submit" @disabled(! $canApprove)
                                        class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg shadow-sm transition disabled:bg-gray-300 disabled:cursor-not-allowed disabled:shadow-none">
                                    Verifikasi &amp; Tayangkan
                                </button>
                            </form>
                            @unless ($canApprove)
                                <p class="text-xs text-rose-600">Lengkapi Dokumen Legalitas terlebih dahulu sebelum menayangkan listing.</p>
                            @endunless
                            <a href="{{ route('admin.property-projects.edit', $propertyProject) }}" class="block w-full text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                                Perbaiki Data Listing
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
