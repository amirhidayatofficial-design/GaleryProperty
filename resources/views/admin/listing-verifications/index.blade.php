<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Verifikasi Listing"
            subtitle="Tinjau pengajuan listing dari agency sebelum ditayangkan di katalog publik."
        />
    </x-slot>

    @php($tabs = ['pending' => 'Menunggu Verifikasi', 'published' => 'Sudah Tayang'])

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('admin.listing-verifications.index', $value === 'pending' ? [] : ['tab' => $value]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition {{ $tab === $value ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary-300' }}">
                        {{ $label }}
                        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-[11px] font-bold {{ $tab === $value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts[$value] }}</span>
                    </a>
                @endforeach
            </div>

            <x-admin.table :headers="['Projek', 'Agency', 'Harga', 'Kelengkapan', $tab === 'pending' ? 'Diajukan' : 'Terakhir Diubah']" :paginator="$propertyProjects">
                @forelse ($propertyProjects as $project)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $project->title }}</div>
                            <div class="text-xs text-gray-400">{{ $project->developer_name }} · {{ $project->city }}, {{ $project->province }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $project->agencyPartner?->agency_name ?? 'Tanpa agency' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp {{ number_format($project->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="{{ $project->legal_docs_count ? 'text-primary-700' : 'text-rose-600 font-semibold' }}">
                                {{ $project->legal_docs_count ? '✓ ' . $project->legal_docs_count . ' dokumen legalitas' : '✗ Dokumen legalitas belum ada' }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $project->photos_count }} foto</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ($tab === 'pending' ? $project->created_at : $project->updated_at)?->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.listing-verifications.show', $project) }}"
                               class="inline-flex items-center px-4 py-2 {{ $tab === 'pending' ? 'bg-primary-600 hover:bg-primary-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }} text-sm font-semibold rounded-lg transition">
                                {{ $tab === 'pending' ? 'Tinjau' : 'Lihat' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="text-base font-semibold text-gray-600">{{ $tab === 'pending' ? 'Tidak ada listing yang menunggu verifikasi' : 'Belum ada listing yang tayang' }}</p>
                            @if ($tab === 'pending')
                                <p class="text-sm text-gray-400 mt-1">Pengajuan baru dari agency akan muncul di sini.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>
        </div>
    </div>
</x-app-layout>
