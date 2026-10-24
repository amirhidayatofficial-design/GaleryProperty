<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Profil Agency"
            :subtitle="$agencyPartner->exists
                ? 'Informasi kantor agency dan rekening pencairan komisi Anda.'
                : 'Lengkapi profil agency Anda. Admin akan memverifikasinya sebelum Anda dapat mengajukan listing.'"
        />
    </x-slot>

    <div class="pt-8 -mb-2">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (! $agencyPartner->exists)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Akun Anda belum memiliki profil agency. Isi formulir di bawah ini, lalu tunggu verifikasi admin.
                </div>
            @else
                <div class="p-4 rounded-xl bg-white border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">Status verifikasi:</span>
                        <x-admin.badge :value="$agencyPartner->verification_status" :labels="\App\Models\AgencyPartner::VERIFICATION_STATUSES" />
                    </div>
                    <span class="text-gray-500">
                        @if ($agencyPartner->isVerified())
                            Terverifikasi sejak {{ $agencyPartner->verified_at?->format('d M Y') }}
                        @elseif ($agencyPartner->verification_status === 'rejected')
                            Hubungi admin lewat menu <a href="{{ route('support.show') }}" class="font-semibold text-primary-700 hover:underline">Bantuan</a> untuk mengetahui alasannya.
                        @else
                            Menunggu pemeriksaan admin.
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </div>

    <x-admin.form-card
        :action="route('agency.profile.update')"
        method="PUT"
        :cancel-url="route('dashboard')"
        :submit-label="$agencyPartner->exists ? 'Simpan Perubahan' : 'Kirim untuk Verifikasi'"
    >
        @include('agency-partners._fields')

        <p class="text-xs text-gray-500">
            Nama dan email akun login diubah di halaman <a href="{{ route('profile.edit') }}" class="font-semibold text-primary-700 hover:underline">Profil</a>.
        </p>
    </x-admin.form-card>
</x-app-layout>
