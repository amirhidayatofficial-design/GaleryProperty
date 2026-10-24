<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="$agencyPartner->agency_name" :index-url="route('admin.agency-partners.index')" index-label="Agency Partner">
            <a href="{{ route('admin.agency-partners.edit', $agencyPartner) }}" class="inline-flex items-center px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">Ubah Data</a>
        </x-admin.header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ([
                    'Projek Properti' => $agencyPartner->propertyProjects->count(),
                    'Leads Pembeli' => $agencyPartner->buyer_leads_count,
                    'Akad Digital' => $agencyPartner->digital_akads_count,
                    'Komisi' => $agencyPartner->commissions_count,
                ] as $label => $count)
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $label }}</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $count }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <dl class="divide-y divide-gray-100">
                    <x-admin.detail label="Status Verifikasi">
                        <x-admin.badge :value="$agencyPartner->verification_status" :labels="\App\Models\AgencyPartner::VERIFICATION_STATUSES" />
                    </x-admin.detail>
                    <x-admin.detail label="Tanggal Verifikasi">{{ $agencyPartner->verified_at?->format('d M Y, H:i') }}</x-admin.detail>
                    <x-admin.detail label="Akun Pemilik">
                        @if ($agencyPartner->user)
                            <a href="{{ route('admin.users.show', $agencyPartner->user) }}" class="text-primary-700 hover:underline">{{ $agencyPartner->user->name }}</a>
                            <span class="text-gray-500">({{ $agencyPartner->user->email }})</span>
                        @endif
                    </x-admin.detail>
                    <x-admin.detail label="Nomor Izin Usaha">{{ $agencyPartner->license_number }}</x-admin.detail>
                    <x-admin.detail label="Alamat Kantor">{{ $agencyPartner->company_address }}</x-admin.detail>
                    <x-admin.detail label="Bank">{{ $agencyPartner->bank_name }}</x-admin.detail>
                    <x-admin.detail label="Nomor Rekening">{{ $agencyPartner->bank_account_number }}</x-admin.detail>
                    <x-admin.detail label="Atas Nama">{{ $agencyPartner->bank_account_holder }}</x-admin.detail>
                    <x-admin.detail label="Dibuat">{{ $agencyPartner->created_at?->format('d M Y, H:i') }}</x-admin.detail>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <h3 class="font-bold text-gray-800 mb-3">Projek Properti yang Dikelola</h3>
                <ul class="divide-y divide-gray-100">
                    @forelse ($agencyPartner->propertyProjects as $project)
                        <li class="py-2 flex items-center justify-between gap-4">
                            <a href="{{ route('admin.property-projects.show', $project) }}" class="text-sm text-primary-700 hover:underline">{{ $project->title }}</a>
                            <span class="text-sm text-gray-500">Rp {{ number_format($project->price, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400 italic">Belum ada projek.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
