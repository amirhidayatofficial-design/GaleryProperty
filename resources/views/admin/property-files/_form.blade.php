<x-admin.select
    name="property_project_id"
    label="Projek Properti"
    :options="$projectOptions"
    :value="$propertyFile->property_project_id"
    placeholder="-- Pilih projek --"
    required
/>

<x-admin.input
    type="file"
    name="file"
    label="Berkas"
    accept=".jpg,.jpeg,.png,.webp,.pdf"
    :hint="$propertyFile->exists
        ? 'Berkas saat ini: ' . $propertyFile->file_path . '. Kosongkan jika tidak ingin mengganti. Format JPG/PNG/WEBP/PDF, maks 5 MB.'
        : 'Format JPG/PNG/WEBP/PDF, maks 5 MB.'"
    :required="! $propertyFile->exists"
    class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
/>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.select name="file_type" label="Tipe Berkas" :options="\App\Models\PropertyFile::FILE_TYPES" :value="$propertyFile->file_type" required />
    <x-admin.input name="caption" label="Keterangan" :value="$propertyFile->caption" placeholder="Foto tampak depan" />
</div>

<x-admin.checkbox
    name="is_primary"
    label="Jadikan berkas utama projek"
    :checked="$propertyFile->is_primary"
    hint="Berkas utama lain pada projek yang sama otomatis dilepas."
/>
