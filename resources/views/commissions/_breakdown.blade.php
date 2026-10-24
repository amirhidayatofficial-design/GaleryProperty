{{-- Rincian perhitungan satu termin komisi (dipakai di halaman admin dan agency) --}}
@php($rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.'))
@php($percent = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ','))

<dl class="divide-y divide-gray-100 text-sm">
    <div class="py-2.5 flex justify-between gap-4">
        <dt class="text-gray-500">Nilai transaksi (harga di akad)</dt>
        <dd class="font-medium text-gray-900">{{ $rupiah($commission->base_transaction_amount) }}</dd>
    </div>
    <div class="py-2.5 flex justify-between gap-4">
        <dt class="text-gray-500">Persentase komisi (dikunci saat akad)</dt>
        <dd class="font-medium text-gray-900">{{ $percent($commission->commission_rate) }}%</dd>
    </div>
    <div class="py-2.5 flex justify-between gap-4">
        <dt class="text-gray-500">{{ $commission->terminLabel() }} ({{ $percent($commission->termin_percentage) }}% dari total komisi)</dt>
        <dd class="font-semibold text-gray-900">{{ $rupiah($commission->total_commission) }}</dd>
    </div>
    <div class="py-2.5 flex justify-between gap-4">
        <dt class="text-gray-500">Platform fee / ujrah wakalah ({{ $percent($commission->platform_fee_rate) }}%)</dt>
        <dd class="font-medium text-rose-600">− {{ $rupiah($commission->platform_fee) }}</dd>
    </div>
    <div class="py-3 flex justify-between gap-4">
        <dt class="font-bold text-gray-800">Bagian agency</dt>
        <dd class="text-base font-bold text-primary-800">{{ $rupiah($commission->net_commission) }}</dd>
    </div>
</dl>
