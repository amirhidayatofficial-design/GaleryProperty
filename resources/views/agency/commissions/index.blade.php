<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Komisi Saya"
            subtitle="Komisi dibayar per termin sesuai syarat yang disepakati. Platform menagih ke developer dan meneruskan bagian Anda."
        />
    </x-slot>

    @php($rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.'))

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (! $agencyPartner)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Akun Anda belum memiliki profil agency. <a href="{{ route('agency.profile.edit') }}" class="font-semibold underline">Lengkapi profil agency</a> terlebih dahulu.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ([
                        ['Sudah diterima', $summary['settled'], 'text-primary-800'],
                        ['Dalam proses penagihan', $summary['in_progress'], 'text-gray-900'],
                        ['Menunggu syarat termin', $summary['pending'], 'text-gray-500'],
                    ] as [$label, $value, $color])
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <p class="text-sm text-gray-500">{{ $label }}</p>
                            <p class="text-xl font-bold mt-1 {{ $color }}">{{ $rupiah($value) }}</p>
                        </div>
                    @endforeach
                </div>

                <x-admin.table :headers="['Akad / Termin', 'Syarat Termin', 'Bagian Anda', 'Status']" :paginator="$commissions">
                    @forelse ($commissions as $commission)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $commission->digitalAkad?->akad_number }} · {{ $commission->terminLabel() }}</div>
                                <div class="text-xs text-gray-400">{{ $commission->digitalAkad?->propertyProject?->title }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $commission->terminCondition() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-700">{{ $rupiah($commission->net_commission) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('agency.commissions.show', $commission) }}" class="text-sm font-semibold text-primary-700 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-base font-semibold text-gray-600">Belum ada komisi</p>
                                <p class="text-sm text-gray-400 mt-1">Komisi terbit otomatis saat akad jual beli ditandatangani pembeli, agency, dan developer.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-admin.table>
            @endif
        </div>
    </div>
</x-app-layout>
