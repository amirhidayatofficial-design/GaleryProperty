<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            :title="$commission->digitalAkad?->akad_number . ' · ' . $commission->terminLabel()"
            :subtitle="$commission->digitalAkad?->propertyProject?->title"
            :index-url="route('agency.commissions.index')"
            index-label="Komisi Saya"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 rounded-xl bg-white border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-gray-500">Status:</span>
                    <x-admin.badge :value="$commission->status" :labels="\App\Models\Commission::STATUSES" />
                </div>
                <span class="text-gray-600">
                    @switch($commission->status)
                        @case(\App\Models\Commission::STATUS_PENDING) Menunggu syarat: {{ $commission->terminCondition() }}. @break
                        @case(\App\Models\Commission::STATUS_DUE) Syarat terpenuhi; invoice segera diterbitkan ke developer. @break
                        @case(\App\Models\Commission::STATUS_INVOICED) Ditagihkan ke developer, jatuh tempo {{ $commission->due_at?->format('d M Y') }}. @break
                        @case(\App\Models\Commission::STATUS_PAID) Dana diterima platform; diteruskan ke rekening Anda paling lambat {{ $commission->payoutDueAt()?->format('d M Y') }}. @break
                        @case(\App\Models\Commission::STATUS_SETTLED) Sudah diteruskan ke rekening Anda pada {{ $commission->settled_at?->format('d M Y') }}. @break
                        @case(\App\Models\Commission::STATUS_VOID) Gugur karena akad batal sebelum syarat termin terpenuhi. @break
                    @endswitch
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Rincian</h3>
                    @include('commissions._breakdown')
                    @if ($commission->payout_proof_path)
                        <a href="{{ Storage::url($commission->payout_proof_path) }}" target="_blank" rel="noopener" class="inline-block mt-3 text-sm font-semibold text-primary-700 hover:underline">Lihat bukti transfer</a>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Riwayat Status</h3>
                    @include('commissions._timeline')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
