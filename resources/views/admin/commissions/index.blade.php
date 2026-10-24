<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Komisi"
            subtitle="Komisi agency per termin. Diterbitkan otomatis saat akad jual beli ditandatangani 3 pihak."
        />
    </x-slot>

    @php($tabs = ['' => 'Semua', ...\App\Models\Commission::STATUSES])

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            @if ($overdueCount)
                <a href="{{ route('admin.commissions.index', ['status' => 'overdue']) }}" class="block p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm hover:bg-amber-100 transition">
                    <strong>{{ $overdueCount }} invoice</strong> lewat jatuh tempo. Kirim pengingat ke developer; developer terkait tidak dapat menambah listing baru sampai tagihannya lunas (tanpa denda).
                </a>
            @endif

            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('admin.commissions.index', array_filter(['status' => $value])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition {{ (string) $status === (string) $value ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary-300' }}">
                        {{ $label }}
                        @if ($value !== '')
                            <span class="text-xs opacity-75">{{ $counts[$value] ?? 0 }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <x-admin.filter :action="route('admin.commissions.index')" placeholder="Nomor invoice, nomor akad, atau nama agency..." :keys="['search', 'status']">
                @if ($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
            </x-admin.filter>

            <x-admin.table :headers="['Akad / Termin', 'Agency', 'Komisi Termin', 'Bagian Agency', 'Status']" :paginator="$commissions">
                @forelse ($commissions as $commission)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $commission->digitalAkad?->akad_number }} · {{ $commission->terminLabel() }}</div>
                            <div class="text-xs text-gray-400">{{ $commission->digitalAkad?->propertyProject?->title }}</div>
                            @if ($commission->invoice_number)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $commission->invoice_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $commission->agencyPartner?->agency_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($commission->total_commission, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-700">Rp {{ number_format($commission->net_commission, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                            @if ($commission->isOverdue())
                                <div class="text-[11px] font-semibold text-amber-700 mt-1">Lewat jatuh tempo</div>
                            @elseif ($commission->isPayoutLate())
                                <div class="text-[11px] font-semibold text-rose-700 mt-1">Payout terlambat</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.commissions.show', $commission) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">Kelola</a>
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-row colspan="6" />
                @endforelse
            </x-admin.table>
        </div>
    </div>
</x-app-layout>
