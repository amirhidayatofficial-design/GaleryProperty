<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            :title="$commission->digitalAkad?->akad_number . ' · ' . $commission->terminLabel()"
            :subtitle="$commission->terminCondition()"
            :index-url="route('admin.commissions.index')"
            index-label="Komisi"
        />
    </x-slot>

    @php($akad = $commission->digitalAkad)
    @php($fileInput = 'block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100')

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h3 class="font-bold text-gray-800 mb-2">Rincian Komisi</h3>
                        @include('commissions._breakdown')
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h3 class="font-bold text-gray-800 mb-3">Transaksi</h3>
                        <dl class="divide-y divide-gray-100">
                            <x-admin.detail label="Akad">
                                @if ($akad)
                                    <a href="{{ route('admin.digital-akads.show', $akad) }}" class="text-primary-700 hover:underline">{{ $akad->akad_number }}</a>
                                    · {{ \App\Models\DigitalAkad::AKAD_TYPES[$akad->akad_type] ?? $akad->akad_type }}
                                @endif
                            </x-admin.detail>
                            <x-admin.detail label="Projek / Developer">{{ $akad?->propertyProject?->title }} · {{ $akad?->propertyProject?->developer_name }}</x-admin.detail>
                            <x-admin.detail label="Pembeli">{{ $akad?->buyerLead?->full_name }}</x-admin.detail>
                            <x-admin.detail label="Agency">
                                @if ($commission->agencyPartner)
                                    <a href="{{ route('admin.agency-partners.show', $commission->agencyPartner) }}" class="text-primary-700 hover:underline">{{ $commission->agencyPartner->agency_name }}</a>
                                @endif
                            </x-admin.detail>
                            <x-admin.detail label="Rekening Agency">
                                {{ collect([$commission->agencyPartner?->bank_name, $commission->agencyPartner?->bank_account_number, $commission->agencyPartner?->bank_account_holder ? 'a.n. ' . $commission->agencyPartner->bank_account_holder : null])->filter()->implode(' · ') }}
                            </x-admin.detail>
                            @if ($commission->invoice_number)
                                <x-admin.detail label="Invoice">
                                    {{ $commission->invoice_number }} · terbit {{ $commission->invoiced_at?->format('d M Y') }} · jatuh tempo {{ $commission->due_at?->format('d M Y') }}
                                </x-admin.detail>
                            @endif
                            @if ($commission->payment_proof_path)
                                <x-admin.detail label="Bukti Bayar Developer">
                                    <a href="{{ Storage::url($commission->payment_proof_path) }}" target="_blank" rel="noopener" class="text-primary-700 hover:underline">Buka</a>
                                    · diterima {{ $commission->paid_at?->format('d M Y') }}
                                </x-admin.detail>
                            @endif
                            @if ($commission->payout_proof_path)
                                <x-admin.detail label="Bukti Payout Agency">
                                    <a href="{{ Storage::url($commission->payout_proof_path) }}" target="_blank" rel="noopener" class="text-primary-700 hover:underline">Buka</a>
                                    · diteruskan {{ $commission->settled_at?->format('d M Y') }}
                                </x-admin.detail>
                            @endif
                        </dl>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h3 class="font-bold text-gray-800 mb-4">Riwayat Status</h3>
                        @include('commissions._timeline', ['showActor' => true])
                    </div>
                </div>

                {{-- Panel aksi: hanya transisi yang sah dari status saat ini --}}
                <div class="space-y-4 lg:sticky lg:top-24">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Status</span>
                            <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                        </div>

                        @switch($commission->status)
                            @case(\App\Models\Commission::STATUS_PENDING)
                                <form method="POST" action="{{ route('admin.commissions.mark-due', $commission) }}" class="space-y-3">
                                    @csrf
                                    <x-admin.textarea name="note" label="Bukti syarat terpenuhi" rows="3" required :hint="'Syarat: ' . $commission->terminCondition() . '. Contoh: DP lunas 20 Okt 2026, kuitansi no. ...'" />
                                    <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg shadow-sm transition">Tandai Syarat Terpenuhi</button>
                                </form>

                                <details class="pt-3 border-t border-gray-100">
                                    <summary class="text-sm font-semibold text-rose-600 cursor-pointer">Gugurkan termin ini</summary>
                                    <form method="POST" action="{{ route('admin.commissions.void', $commission) }}" class="space-y-3 mt-3"
                                          onsubmit="return confirm('Gugurkan termin komisi ini? Status gugur bersifat final.');">
                                        @csrf
                                        <x-admin.textarea name="reason" label="Alasan" rows="2" required hint="Hanya untuk akad yang batal sebelum syarat termin terpenuhi." />
                                        <button type="submit" class="w-full px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg transition">Gugurkan</button>
                                    </form>
                                </details>
                                @break

                            @case(\App\Models\Commission::STATUS_DUE)
                                <p class="text-sm text-gray-600">Syarat sudah terpenuhi. Terbitkan invoice ke developer dengan jatuh tempo {{ config('commission.invoice_due_days') }} hari.</p>
                                <form method="POST" action="{{ route('admin.commissions.issue-invoice', $commission) }}">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg shadow-sm transition">Terbitkan Invoice</button>
                                </form>
                                @break

                            @case(\App\Models\Commission::STATUS_INVOICED)
                                <a href="{{ route('admin.commissions.invoice', $commission) }}" target="_blank" class="block w-full text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">Cetak Invoice</a>
                                @if ($commission->isOverdue())
                                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">Lewat jatuh tempo sejak {{ $commission->due_at->format('d M Y') }}. Kirim pengingat ke developer. Tidak ada denda; developer ini tidak dapat menambah listing baru sampai lunas.</p>
                                @endif
                                <form method="POST" action="{{ route('admin.commissions.record-payment', $commission) }}" enctype="multipart/form-data" class="space-y-3 pt-3 border-t border-gray-100">
                                    @csrf
                                    <x-admin.input type="date" name="paid_at" label="Tanggal dana diterima" :value="now()->toDateString()" required />
                                    <x-admin.input type="file" name="payment_proof" label="Bukti pembayaran developer" accept=".jpg,.jpeg,.png,.webp,.pdf" required :class="$fileInput" />
                                    <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg shadow-sm transition">Catat Pembayaran Developer</button>
                                </form>
                                @break

                            @case(\App\Models\Commission::STATUS_PAID)
                                <p class="text-sm text-gray-600">
                                    Dana diterima dan berstatus amanah milik agency. Teruskan <strong>Rp {{ number_format($commission->net_commission, 0, ',', '.') }}</strong>
                                    paling lambat <strong>{{ $commission->payoutDueAt()?->format('d M Y') }}</strong>.
                                </p>
                                @if ($commission->isPayoutLate())
                                    <p class="text-xs text-rose-700 bg-rose-50 border border-rose-200 rounded-lg p-3">Payout ke agency sudah melewati batas waktu.</p>
                                @endif
                                <form method="POST" action="{{ route('admin.commissions.record-payout', $commission) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <x-admin.input type="file" name="payout_proof" label="Bukti transfer ke agency" accept=".jpg,.jpeg,.png,.webp,.pdf" required :class="$fileInput" />
                                    <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg shadow-sm transition">Catat Payout ke Agency</button>
                                </form>
                                @break

                            @case(\App\Models\Commission::STATUS_SETTLED)
                                <p class="text-sm text-gray-600">Termin ini selesai. Bagian agency sudah diteruskan dan menjadi hak agency sepenuhnya.</p>
                                @break

                            @case(\App\Models\Commission::STATUS_VOID)
                                <p class="text-sm text-gray-600">Termin ini gugur karena akad batal sebelum syaratnya terpenuhi. Tidak ada yang ditagihkan.</p>
                                @break
                        @endswitch

                        @if ($commission->invoice_number && $commission->status !== \App\Models\Commission::STATUS_INVOICED)
                            <a href="{{ route('admin.commissions.invoice', $commission) }}" target="_blank" class="block text-center text-sm font-semibold text-primary-700 hover:underline">Lihat invoice {{ $commission->invoice_number }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
