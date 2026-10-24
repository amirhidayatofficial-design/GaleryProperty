<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Leads Calon Pembeli"
            subtitle="Profiling kebutuhan keluarga muslim dan pelacakan status prospek."
            :create-url="route('admin.buyer-leads.create')"
            create-label="Tambah Lead"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <x-admin.filter
                :action="route('admin.buyer-leads.index')"
                placeholder="Nama, nomor WhatsApp, atau email..."
                :keys="['search', 'status', 'agency_partner_id']"
            >
                <x-admin.filter-select name="status" label="Status" :options="\App\Models\BuyerLead::STATUSES" />
                <x-admin.filter-select name="agency_partner_id" label="Agency" :options="$agencyOptions" placeholder="Semua Agency" />
            </x-admin.filter>

            <x-admin.table :headers="['Calon Pembeli', 'Projek Diminati', 'Anggaran', 'Agency', 'Status']" :paginator="$buyerLeads">
                @forelse ($buyerLeads as $lead)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $lead->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $lead->phone }}{{ $lead->email ? ' · ' . $lead->email : '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $lead->propertyProject?->title ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if ($lead->budget_min || $lead->budget_max)
                                Rp {{ number_format($lead->budget_min ?? 0, 0, ',', '.') }} – {{ number_format($lead->budget_max ?? 0, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $lead->agencyPartner?->agency_name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$lead->status" :labels="\App\Models\BuyerLead::STATUSES" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-admin.row-actions
                                :show="route('admin.buyer-leads.show', $lead)"
                                :edit="route('admin.buyer-leads.edit', $lead)"
                                :destroy="route('admin.buyer-leads.destroy', $lead)"
                                :confirm="'Hapus lead ' . $lead->full_name . '?'"
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
