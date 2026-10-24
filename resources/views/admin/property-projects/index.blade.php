<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Projek Properti"
            subtitle="Kelola listing rumah, kavling, dan ruko syariah yang terkurasi."
            :create-url="route('admin.property-projects.create')"
            create-label="Tambah Projek"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <x-admin.filter
                :action="route('admin.property-projects.index')"
                placeholder="Judul, developer, atau kota..."
                :keys="['search', 'property_type', 'syariah_scheme', 'status']"
            >
                <x-admin.filter-select name="property_type" label="Tipe" :options="\App\Models\PropertyProject::PROPERTY_TYPES" />
                <x-admin.filter-select name="syariah_scheme" label="Skema" :options="\App\Models\PropertyProject::SYARIAH_SCHEMES" />
                <x-admin.filter-select name="status" label="Status" :options="\App\Models\PropertyProject::STATUSES" />
            </x-admin.filter>

            <x-admin.table :headers="['Projek', 'Lokasi', 'Harga', 'Tipe / Skema', 'Status']" :paginator="$propertyProjects">
                @forelse ($propertyProjects as $project)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900 flex items-center gap-2">
                                <span>{{ $project->title }}</span>
                                @if ($project->is_verified)
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">Terverifikasi</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">{{ $project->developer_name }} · {{ $project->agencyPartner?->agency_name ?? 'Tanpa agency' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $project->city }}, {{ $project->province }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($project->price, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">Komisi {{ rtrim(rtrim($project->commission_rate, '0'), '.') }}%</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \App\Models\PropertyProject::PROPERTY_TYPES[$project->property_type] ?? $project->property_type }}
                            <div class="text-xs text-gray-500">{{ \App\Models\PropertyProject::SYARIAH_SCHEMES[$project->syariah_scheme] ?? $project->syariah_scheme }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$project->status" :labels="\App\Models\PropertyProject::STATUSES" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-admin.row-actions
                                :show="route('admin.property-projects.show', $project)"
                                :edit="route('admin.property-projects.edit', $project)"
                                :destroy="route('admin.property-projects.destroy', $project)"
                                :confirm="'Hapus projek ' . $project->title . '?'"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-row colspan="6" />
                @endforelse
            </x-admin.table>
        </div>
    </div>
</x-app-layout>
