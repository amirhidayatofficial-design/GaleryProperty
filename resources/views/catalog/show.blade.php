<x-app-layout>
    <x-slot name="title">{{ $propertyProject->title }}</x-slot>

    <x-slot name="header">
        <x-admin.header :title="$propertyProject->title" :index-url="route('catalog.index')" index-label="Katalog" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                @php($photos = $propertyProject->photos->filter(fn ($photo) => Storage::disk('public')->exists($photo->file_path)))
                @if ($photos->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($photos as $photo)
                            <img src="{{ Storage::url($photo->file_path) }}" alt="{{ $photo->caption ?? $propertyProject->title }}" class="rounded-xl border border-gray-100 object-cover w-full h-48 {{ $loop->first ? 'col-span-2 h-72' : '' }}" />
                        @endforeach
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                    <dl class="divide-y divide-gray-100">
                        <x-admin.detail label="Tipe / Skema">
                            {{ \App\Models\PropertyProject::PROPERTY_TYPES[$propertyProject->property_type] ?? $propertyProject->property_type }} ·
                            {{ \App\Models\PropertyProject::SYARIAH_SCHEMES[$propertyProject->syariah_scheme] ?? $propertyProject->syariah_scheme }}
                        </x-admin.detail>
                        <x-admin.detail label="Developer">{{ $propertyProject->developer_name }}</x-admin.detail>
                        <x-admin.detail label="Lokasi">{{ collect([$propertyProject->district, $propertyProject->city, $propertyProject->province])->filter()->implode(', ') }}</x-admin.detail>
                        <x-admin.detail label="Alamat">{{ $propertyProject->address_detail }}</x-admin.detail>
                        <x-admin.detail label="Spesifikasi">
                            {{ $propertyProject->bedroom ?? '-' }} KT · {{ $propertyProject->bathroom ?? '-' }} KM ·
                            LB {{ $propertyProject->building_area ?? '-' }} m² · LT {{ $propertyProject->land_area ?? '-' }} m²
                        </x-admin.detail>
                        <x-admin.detail label="Deskripsi">{{ $propertyProject->description }}</x-admin.detail>
                        <x-admin.detail label="Jaminan Halal">{{ $propertyProject->halal_guarantee_notes }}</x-admin.detail>
                    </dl>
                </div>
            </div>

            {{-- Kartu harga & kontak: hanya nama agency, tanpa WhatsApp/email (kontak lewat chat) --}}
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 lg:sticky lg:top-6">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</div>
                        <div class="text-2xl font-bold text-primary-700">Rp {{ number_format($propertyProject->price, 0, ',', '.') }}</div>
                    </div>
                    <x-admin.badge :value="$propertyProject->status" :labels="\App\Models\PropertyProject::STATUSES" />

                    @if ($propertyProject->agencyPartner)
                        <div class="pt-4 border-t border-gray-100">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Dipasarkan oleh</div>
                            <div class="font-semibold text-gray-900">{{ $propertyProject->agencyPartner->agency_name }}</div>
                        </div>

                        @guest
                            <a href="{{ route('login') }}" class="block text-center w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition">
                                Login untuk Chat Agency
                            </a>
                            <p class="text-xs text-gray-400 text-center">Belum punya akun? <a href="{{ route('register') }}" class="text-primary-700 hover:underline">Daftar sebagai calon pembeli</a></p>
                        @else
                            @if (auth()->user()->isBuyer())
                                <form method="POST" action="{{ route('chat.start', $propertyProject) }}">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition">
                                        Chat Agency
                                    </button>
                                </form>
                            @else
                                <p class="text-xs text-gray-400">Fitur chat hanya tersedia untuk akun calon pembeli.</p>
                            @endif
                        @endguest
                    @else
                        <p class="text-sm text-gray-500 pt-4 border-t border-gray-100">Projek ini belum memiliki agency penanggung jawab.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
