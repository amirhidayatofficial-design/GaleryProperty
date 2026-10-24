<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Detail Komisi" :index-url="route('admin.commissions.index')" index-label="Komisi Agency">
            <a href="{{ route('admin.commissions.edit', $commission) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Data</a>
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Komisi</div>
                    <div class="mt-2 text-xl font-bold text-gray-900">Rp {{ number_format($commission->total_commission, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $commission->commission_rate }}% × Rp {{ number_format($commission->base_transaction_amount, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Biaya Platform</div>
                    <div class="mt-2 text-xl font-bold text-rose-700">Rp {{ number_format($commission->platform_fee, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-primary-600 uppercase tracking-wider">Komisi Bersih</div>
                    <div class="mt-2 text-xl font-bold text-primary-700">Rp {{ number_format($commission->net_commission, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Status">
                        <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                    </x-admin.detail>
                    <x-admin.detail label="Tanggal Dibayar">{{ $commission->paid_at?->format('d M Y, H:i') }}</x-admin.detail>
                    <x-admin.detail label="Akad">
                        @if ($commission->digitalAkad)
                            <a href="{{ route('admin.digital-akads.show', $commission->digitalAkad) }}" class="text-primary-700 hover:underline">{{ $commission->digitalAkad->akad_number }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Projek">{{ $commission->digitalAkad?->propertyProject?->title }}</x-admin.detail>
                    <x-admin.detail label="Agency Penerima">
                        @if ($commission->agencyPartner)
                            <a href="{{ route('admin.agency-partners.show', $commission->agencyPartner) }}" class="text-primary-700 hover:underline">{{ $commission->agencyPartner->agency_name }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Bukti Transfer">
                        @if ($commission->payment_proof_path && Storage::disk('public')->exists($commission->payment_proof_path))
                            <a href="{{ Storage::url($commission->payment_proof_path) }}" target="_blank" class="text-primary-700 hover:underline">Buka bukti transfer</a>
                        @elseif ($commission->payment_proof_path)
                            <span class="text-gray-500">{{ $commission->payment_proof_path }} (berkas tidak ditemukan)</span>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Catatan">{{ $commission->notes }}</x-admin.detail>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
