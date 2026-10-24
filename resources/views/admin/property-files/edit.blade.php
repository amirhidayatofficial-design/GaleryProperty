<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Ubah Berkas" :index-url="route('admin.property-files.index')" index-label="Berkas Properti" />
    </x-slot>

    <x-admin.form-card
        :action="route('admin.property-files.update', $propertyFile)"
        method="PUT"
        :cancel-url="route('admin.property-files.index')"
        submit-label="Simpan Perubahan"
        files
    >
        @include('admin.property-files._form')
    </x-admin.form-card>
</x-app-layout>
