<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="$buyerLead->full_name" :index-url="route('admin.buyer-leads.index')" index-label="Leads Calon Pembeli">
            <a href="{{ route('admin.buyer-leads.edit', $buyerLead) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Data</a>
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Status">
                        <x-admin.badge :value="$buyerLead->status" :labels="\App\Models\BuyerLead::STATUSES" />
                    </x-admin.detail>
                    <x-admin.detail label="Nomor WhatsApp">{{ $buyerLead->phone }}</x-admin.detail>
                    <x-admin.detail label="Email">{{ $buyerLead->email }}</x-admin.detail>
                    <x-admin.detail label="Akun Pembeli">
                        @if ($buyerLead->user)
                            <a href="{{ route('admin.users.show', $buyerLead->user) }}" class="text-primary-700 hover:underline">{{ $buyerLead->user->name }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Projek Diminati">
                        @if ($buyerLead->propertyProject)
                            <a href="{{ route('admin.property-projects.show', $buyerLead->propertyProject) }}" class="text-primary-700 hover:underline">{{ $buyerLead->propertyProject->title }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Agency">
                        @if ($buyerLead->agencyPartner)
                            <a href="{{ route('admin.agency-partners.show', $buyerLead->agencyPartner) }}" class="text-primary-700 hover:underline">{{ $buyerLead->agencyPartner->agency_name }}</a>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Anggaran">
                        @if ($buyerLead->budget_min || $buyerLead->budget_max)
                            Rp {{ number_format($buyerLead->budget_min ?? 0, 0, ',', '.') }} – Rp {{ number_format($buyerLead->budget_max ?? 0, 0, ',', '.') }}
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Lokasi Diminati">{{ $buyerLead->preferred_location }}</x-admin.detail>
                    <x-admin.detail label="Skema Diminati">{{ \App\Models\PropertyProject::SYARIAH_SCHEMES[$buyerLead->preferred_scheme] ?? $buyerLead->preferred_scheme }}</x-admin.detail>
                    <x-admin.detail label="Catatan">{{ $buyerLead->notes }}</x-admin.detail>
                    <x-admin.detail label="Dibuat">{{ $buyerLead->created_at?->format('d M Y, H:i') }}</x-admin.detail>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <h3 class="font-bold text-gray-800 mb-3">Akad Digital</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($buyerLead->digitalAkads as $akad)
                        <li class="py-2 flex items-center justify-between gap-4">
                            <a href="{{ route('admin.digital-akads.show', $akad) }}" class="text-sm text-primary-700 hover:underline">{{ $akad->akad_number }}</a>
                            <x-admin.badge :value="$akad->status" :labels="\App\Models\DigitalAkad::STATUSES" />
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400 italic">Belum ada akad.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
