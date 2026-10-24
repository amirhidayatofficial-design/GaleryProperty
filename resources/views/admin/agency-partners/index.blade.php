<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Agency Partner"
            subtitle="Kelola kantor mitra agency, izin usaha, dan rekening bank syariah."
            :create-url="route('admin.agency-partners.create')"
            create-label="Tambah Agency"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <x-admin.filter
                :action="route('admin.agency-partners.index')"
                placeholder="Nama agency, nomor izin, atau akun pemilik..."
                :keys="['search', 'verification_status']"
            >
                <x-admin.filter-select name="verification_status" label="Status Verifikasi" :options="\App\Models\AgencyPartner::VERIFICATION_STATUSES" />
            </x-admin.filter>

            <x-admin.table :headers="['Agency', 'Akun Pemilik', 'Bank', 'Listing / Leads', 'Verifikasi']" :paginator="$agencyPartners">
                @forelse ($agencyPartners as $agencyPartner)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $agencyPartner->agency_name }}</div>
                            <div class="text-xs text-gray-400">{{ $agencyPartner->license_number ?? 'Belum ada nomor izin' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">{{ $agencyPartner->user?->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $agencyPartner->user?->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $agencyPartner->bank_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $agencyPartner->bank_account_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $agencyPartner->property_projects_count }} projek / {{ $agencyPartner->buyer_leads_count }} leads
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$agencyPartner->verification_status" :labels="\App\Models\AgencyPartner::VERIFICATION_STATUSES" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-admin.row-actions
                                :show="route('admin.agency-partners.show', $agencyPartner)"
                                :edit="route('admin.agency-partners.edit', $agencyPartner)"
                                :destroy="route('admin.agency-partners.destroy', $agencyPartner)"
                                :confirm="'Hapus agency ' . $agencyPartner->agency_name . '? Listing, leads, dan akad yang terhubung akan kehilangan relasi agency-nya.'"
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
