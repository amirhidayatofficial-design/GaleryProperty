@include('property-projects._fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
    <x-admin.input
        type="file"
        name="legal_doc"
        label="PKS / Surat Kuasa Developer"
        accept=".pdf,.jpg,.jpeg,.png"
        :hint="$propertyProject->exists
            ? 'Opsional: unggah dokumen tambahan. Dokumen yang sudah ada tetap tersimpan.'
            : 'Wajib. Dasar agency mewakili developer saat menandatangani akad. PDF/JPG/PNG, maks 5 MB.'"
        :required="! $propertyProject->exists"
        class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
    />
    <x-admin.input
        type="file"
        name="photos[]"
        label="Foto Projek"
        accept=".jpg,.jpeg,.png,.webp"
        multiple
        hint="Opsional, maksimal 5 foto sekaligus. Foto pertama menjadi foto utama."
        class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
    />
</div>
@if ($errors->has('photos') || $errors->has('photos.*'))
    <p class="text-xs text-rose-600 -mt-4">{{ $errors->first('photos') ?: $errors->first('photos.*') }}</p>
@endif
