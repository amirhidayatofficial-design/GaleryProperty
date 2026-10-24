<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Listing Saya"
            subtitle="Ajukan listing projek dari developer yang Anda wakili. Listing tayang di katalog setelah diverifikasi admin."
            :create-url="$agencyPartner?->isVerified() ? route('agency.property-projects.create') : null"
            create-label="Ajukan Listing"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            @if (! $agencyPartner)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Akun Anda belum memiliki profil agency. <a href="{{ route('agency.profile.edit') }}" class="font-semibold underline">Lengkapi profil agency</a> agar admin dapat memverifikasinya.
                </div>
            @elseif (! $agencyPartner->isVerified())
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Profil agency <strong>{{ $agencyPartner->agency_name }}</strong> belum diverifikasi admin, sehingga belum dapat mengajukan listing.
                </div>
            @endif

            @if ($propertyProjects)
                <x-admin.table :headers="['Projek', 'Lokasi', 'Harga', 'Dokumen Kuasa', 'Status Pengajuan']" :paginator="$propertyProjects">
                    @forelse ($propertyProjects as $project)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $project->title }}</div>
                                <div class="text-xs text-gray-400">{{ $project->developer_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $project->city }}, {{ $project->province }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp {{ number_format($project->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $project->legal_docs_count ? 'text-primary-700' : 'text-rose-600' }}">
                                {{ $project->legal_docs_count ? $project->legal_docs_count . ' dokumen' : 'Belum ada' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($project->is_verified)
                                    <x-admin.badge value="active" :labels="['active' => 'Sudah Tayang']" />
                                @else
                                    <x-admin.badge value="pending" :labels="['pending' => 'Menunggu Verifikasi']" />
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="{{ route('agency.property-projects.show', $project) }}" class="text-primary-700 hover:underline">Detail</a>
                                @unless ($project->is_verified)
                                    <a href="{{ route('agency.property-projects.edit', $project) }}" class="text-gold-700 hover:underline">Ubah</a>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <p class="text-base font-semibold text-gray-600">Belum ada listing</p>
                                <p class="text-sm text-gray-400 mt-1">Klik "Ajukan Listing" untuk mengajukan projek pertama Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-admin.table>
            @endif
        </div>
    </div>
</x-app-layout>
