<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Akad Digital"
            subtitle="Penerbitan akad syariah dan MOU dengan penandatanganan tiga pihak."
            :create-url="route('admin.digital-akads.create')"
            create-label="Terbitkan Akad"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <x-admin.filter
                :action="route('admin.digital-akads.index')"
                placeholder="Nomor akad atau nama pembeli..."
                :keys="['search', 'akad_type', 'status']"
            >
                <x-admin.filter-select name="akad_type" label="Jenis Akad" :options="\App\Models\DigitalAkad::AKAD_TYPES" />
                <x-admin.filter-select name="status" label="Status" :options="\App\Models\DigitalAkad::STATUSES" />
            </x-admin.filter>

            <x-admin.table :headers="['Nomor Akad', 'Pembeli / Projek', 'Nilai Transaksi', 'Tanda Tangan', 'Status']" :paginator="$digitalAkads">
                @forelse ($digitalAkads as $akad)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $akad->akad_number }}</div>
                            <div class="text-xs text-gray-500">{{ \App\Models\DigitalAkad::AKAD_TYPES[$akad->akad_type] ?? $akad->akad_type }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">{{ $akad->buyerLead?->full_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $akad->propertyProject?->title ?? 'Projek sudah dihapus' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($akad->transaction_amount, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">DP Rp {{ number_format($akad->dp_amount, 0, ',', '.') }} · {{ $akad->tenor_months }} bln</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php($signedCount = collect([$akad->buyer_signed_at, $akad->agency_signed_at, $akad->developer_signed_at])->filter()->count())
                            <span class="{{ $signedCount === 3 ? 'text-primary-700 font-semibold' : 'text-gray-600' }}">{{ $signedCount }}/3 pihak</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$akad->status" :labels="\App\Models\DigitalAkad::STATUSES" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-admin.row-actions
                                :show="route('admin.digital-akads.show', $akad)"
                                :edit="route('admin.digital-akads.edit', $akad)"
                                :destroy="route('admin.digital-akads.destroy', $akad)"
                                :confirm="'Hapus akad ' . $akad->akad_number . '? Data komisi yang terhubung juga akan ikut terhapus.'"
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
