<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Berkas Properti"
            subtitle="Foto, siteplan, brosur, dan dokumen legalitas tiap projek."
            :create-url="route('admin.property-files.create')"
            create-label="Unggah Berkas"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-admin.flash />

            <x-admin.filter
                :action="route('admin.property-files.index')"
                placeholder="Keterangan, nama berkas, atau judul projek..."
                :keys="['search', 'file_type', 'property_project_id']"
            >
                <x-admin.filter-select name="file_type" label="Tipe Berkas" :options="\App\Models\PropertyFile::FILE_TYPES" />
                <x-admin.filter-select name="property_project_id" label="Projek" :options="$projectOptions" placeholder="Semua Projek" />
            </x-admin.filter>

            <x-admin.table :headers="['Berkas', 'Projek', 'Tipe', 'Diunggah']" :paginator="$propertyFiles">
                @forelse ($propertyFiles as $file)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900 flex items-center gap-2">
                                <span>{{ $file->caption ?: 'Tanpa keterangan' }}</span>
                                @if ($file->is_primary)
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-gold-50 text-gold-700 border border-gold-200">Utama</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">{{ $file->file_path }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $file->propertyProject?->title ?? 'Projek sudah dihapus' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-admin.badge :value="$file->file_type" :labels="\App\Models\PropertyFile::FILE_TYPES" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->created_at?->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-admin.row-actions
                                :show="route('admin.property-files.show', $file)"
                                :edit="route('admin.property-files.edit', $file)"
                                :destroy="route('admin.property-files.destroy', $file)"
                                confirm="Hapus berkas ini? Berkas fisiknya juga akan dihapus dari penyimpanan."
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-row colspan="5" />
                @endforelse
            </x-admin.table>
        </div>
    </div>
</x-app-layout>
