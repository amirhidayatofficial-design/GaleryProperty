<x-app-layout>
    <x-slot name="title">Katalog Properti</x-slot>

    <x-slot name="header">
        <x-admin.header title="Katalog Properti Syariah" subtitle="Properti terkurasi tanpa riba, tanpa denda, dan tanpa sita paksa." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.filter
                :action="route('catalog.index')"
                placeholder="Nama projek, kota, atau provinsi..."
                :keys="['search', 'property_type', 'syariah_scheme', 'max_price']"
            >
                <x-admin.filter-select name="property_type" label="Tipe" :options="\App\Models\PropertyProject::PROPERTY_TYPES" />
                <x-admin.filter-select name="syariah_scheme" label="Skema" :options="\App\Models\PropertyProject::SYARIAH_SCHEMES" />
                <x-admin.filter-select
                    name="max_price"
                    label="Harga Maksimal"
                    :options="[500000000 => '≤ Rp 500 juta', 750000000 => '≤ Rp 750 juta', 1000000000 => '≤ Rp 1 miliar', 1500000000 => '≤ Rp 1,5 miliar']"
                    placeholder="Semua Harga"
                />
            </x-admin.filter>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($propertyProjects as $project)
                    <x-property-card :project="$project" />
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-cream-dark shadow-sm px-6 py-12 text-center">
                        <p class="text-base font-semibold text-gray-600">Belum ada properti yang cocok</p>
                        <p class="text-sm text-gray-400 mt-1">Coba ubah kata kunci pencarian atau reset filter.</p>
                    </div>
                @endforelse
            </div>

            {{ $propertyProjects->links() }}
        </div>
    </div>
</x-app-layout>
