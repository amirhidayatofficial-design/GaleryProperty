@php($akad = $commission->digitalAkad)
@php($rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.'))
<!DOCTYPE html>
<html lang="id">
    <head>
        @include('layouts.partials.head', ['title' => 'Invoice ' . $commission->invoice_number])
        <style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-cream">
        <div class="no-print max-w-3xl mx-auto px-4 pt-6 flex justify-between items-center gap-3">
            <a href="{{ route('admin.commissions.show', $commission) }}" class="text-sm font-semibold text-gray-600 hover:text-primary-800">← Kembali ke komisi</a>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-primary-700 hover:bg-primary-800 text-white text-sm font-semibold rounded-lg">Cetak / Simpan PDF</button>
        </div>

        <main class="max-w-3xl mx-auto my-6 bg-white border border-gray-200 rounded-2xl p-8 sm:p-10 space-y-8">
            <header class="flex flex-wrap justify-between items-start gap-6">
                <div>
                    <x-brand />
                    <p class="text-xs text-gray-500 mt-2">Platform properti syariah · {{ config('mail.from.address') }}</p>
                </div>
                <div class="text-right">
                    <h1 class="font-display text-2xl font-bold text-primary-950">INVOICE KOMISI</h1>
                    <p class="text-sm font-semibold mt-1">{{ $commission->invoice_number }}</p>
                    <p class="text-xs text-gray-500 mt-1">Terbit: {{ $commission->invoiced_at?->format('d M Y') }}</p>
                    <p class="text-xs text-gray-500">Jatuh tempo: <strong>{{ $commission->due_at?->format('d M Y') }}</strong></p>
                </div>
            </header>

            <section class="grid sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Ditagihkan kepada</p>
                    <p class="font-semibold">{{ $akad?->propertyProject?->developer_name }}</p>
                    <p class="text-gray-600">Developer projek {{ $akad?->propertyProject?->title }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Atas nama agency (wakalah)</p>
                    <p class="font-semibold">{{ $commission->agencyPartner?->agency_name }}</p>
                    <p class="text-gray-600">{{ $commission->agencyPartner?->license_number }}</p>
                </div>
            </section>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="py-2">Keterangan</th>
                        <th class="py-2 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-3">
                            <p class="font-medium">Komisi keperantaraan (samsarah) · {{ $commission->terminLabel() }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Akad {{ $akad?->akad_number }} · pembeli {{ $akad?->buyerLead?->full_name }}<br>
                                {{ rtrim(rtrim(number_format((float) $commission->commission_rate, 2, ',', '.'), '0'), ',') }}% × {{ $rupiah($commission->base_transaction_amount) }}
                                × {{ rtrim(rtrim(number_format((float) $commission->termin_percentage, 2, ',', '.'), '0'), ',') }}% ({{ $commission->terminCondition() }})
                            </p>
                        </td>
                        <td class="py-3 text-right font-semibold">{{ $rupiah($commission->total_commission) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200">
                        <td class="py-3 font-bold">Total yang harus dibayar</td>
                        <td class="py-3 text-right text-lg font-bold text-primary-800">{{ $rupiah($commission->total_commission) }}</td>
                    </tr>
                </tfoot>
            </table>

            <section class="text-xs text-gray-600 space-y-1.5 border-t border-gray-100 pt-6">
                <p>Pembayaran ditransfer ke rekening bank syariah GalleryProperty. Cantumkan nomor invoice pada berita transfer.</p>
                <p>Komisi ini sesuai besaran yang disepakati dalam perjanjian kerja sama (PKS) dan tidak dikenakan denda keterlambatan.</p>
                <p>GalleryProperty menagih komisi ini sebagai wakil agency (wakalah bil ujrah) dan meneruskan bagian agency setelah dana diterima.</p>
            </section>
        </main>
    </body>
</html>
