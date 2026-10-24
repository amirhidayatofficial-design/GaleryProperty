<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="$digitalAkad->akad_number" :index-url="route('admin.digital-akads.index')" index-label="Akad Digital">
            <a href="{{ route('admin.digital-akads.edit', $digitalAkad) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Data</a>
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Status">
                        <x-admin.badge :value="$digitalAkad->status" :labels="\App\Models\DigitalAkad::STATUSES" />
                        <span class="ml-2 text-xs {{ $digitalAkad->isFullySigned() ? 'text-primary-700' : 'text-gray-400' }}">
                            {{ $digitalAkad->isFullySigned() ? 'Lengkap ditandatangani 3 pihak' : 'Tanda tangan belum lengkap' }}
                        </span>
                    </x-admin.detail>
                    <x-admin.detail label="Jenis Akad">{{ \App\Models\DigitalAkad::AKAD_TYPES[$digitalAkad->akad_type] ?? $digitalAkad->akad_type }}</x-admin.detail>
                    <x-admin.detail label="Calon Pembeli">
                        @if ($digitalAkad->buyerLead)
                            <a href="{{ route('admin.buyer-leads.show', $digitalAkad->buyerLead) }}" class="text-primary-700 hover:underline">{{ $digitalAkad->buyerLead->full_name }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Projek">
                        @if ($digitalAkad->propertyProject)
                            <a href="{{ route('admin.property-projects.show', $digitalAkad->propertyProject) }}" class="text-primary-700 hover:underline">{{ $digitalAkad->propertyProject->title }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Agency">
                        @if ($digitalAkad->agencyPartner)
                            <a href="{{ route('admin.agency-partners.show', $digitalAkad->agencyPartner) }}" class="text-primary-700 hover:underline">{{ $digitalAkad->agencyPartner->agency_name }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Nilai Transaksi">Rp {{ number_format($digitalAkad->transaction_amount, 0, ',', '.') }}</x-admin.detail>
                    <x-admin.detail label="Uang Muka (DP)">Rp {{ number_format($digitalAkad->dp_amount, 0, ',', '.') }}</x-admin.detail>
                    <x-admin.detail label="Tenor">{{ $digitalAkad->tenor_months }} bulan</x-admin.detail>
                    <x-admin.detail label="TTD Pembeli">{{ $digitalAkad->buyer_signed_at?->format('d M Y, H:i') }}</x-admin.detail>
                    <x-admin.detail label="TTD Agency">{{ $digitalAkad->agency_signed_at?->format('d M Y, H:i') }}</x-admin.detail>
                    <x-admin.detail label="TTD Developer">{{ $digitalAkad->developer_signed_at?->format('d M Y, H:i') }}</x-admin.detail>
                    <x-admin.detail label="Dokumen PDF">
                        @if ($digitalAkad->document_pdf_path && Storage::disk('public')->exists($digitalAkad->document_pdf_path))
                            <a href="{{ Storage::url($digitalAkad->document_pdf_path) }}" target="_blank" class="text-primary-700 hover:underline">Buka dokumen</a>
                        @elseif ($digitalAkad->document_pdf_path)
                            <span class="text-gray-500">{{ $digitalAkad->document_pdf_path }} (berkas tidak ditemukan)</span>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Komisi">
                        @foreach ($digitalAkad->commissions as $commission)
                            <div class="flex flex-wrap items-center gap-2 {{ $loop->first ? '' : 'mt-1.5' }}">
                                <a href="{{ route('admin.commissions.show', $commission) }}" class="text-primary-700 hover:underline">
                                    {{ $commission->terminLabel() }} · Rp {{ number_format($commission->total_commission, 0, ',', '.') }}
                                </a>
                                <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                            </div>
                        @endforeach
                        @if ($digitalAkad->commissions->isEmpty() && in_array($digitalAkad->akad_type, config('commission.eligible_akad_types'), true))
                            <span class="text-gray-400">Terbit otomatis setelah akad berstatus Ditandatangani oleh ketiga pihak.</span>
                        @endif
                    </x-admin.detail>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <h3 class="font-bold text-gray-800 mb-3">Isi Akad</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $digitalAkad->terms_content }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
