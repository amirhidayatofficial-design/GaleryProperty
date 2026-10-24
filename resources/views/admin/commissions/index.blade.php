<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Komisi Agency"
            subtitle="Perhitungan dan pencairan bagi hasil komisi agen per akad."
            :create-url="route('admin.commissions.create')"
            create-label="Tambah Komisi"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <x-admin.filter
                :action="route('admin.commissions.index')"
                placeholder="Nomor akad atau nama agency..."
                :keys="['search', 'status']"
            >
                <x-admin.filter-select name="status" label="Status" :options="\App\Models\Commission::STATUSES" />
            </x-admin.filter>

            <x-admin.table :headers="['Akad', 'Agency', 'Total Komisi', 'Komisi Bersih', 'Status']" :paginator="$commissions">
                @forelse ($commissions as $commission)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $commission->digitalAkad?->akad_number ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Transaksi Rp {{ number_format($commission->base_transaction_amount, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $commission->agencyPartner?->agency_name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Rp {{ number_format($commission->total_commission, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ $commission->commission_rate }}% · fee Rp {{ number_format($commission->platform_fee, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-700">Rp {{ number_format($commission->net_commission, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                            @if ($commission->paid_at)
                                <div class="text-xs text-gray-400 mt-1">{{ $commission->paid_at->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-admin.row-actions
                                :show="route('admin.commissions.show', $commission)"
                                :edit="route('admin.commissions.edit', $commission)"
                                :destroy="route('admin.commissions.destroy', $commission)"
                                confirm="Hapus data komisi ini?"
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
