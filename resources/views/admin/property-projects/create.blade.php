<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Tambah Projek" :index-url="route('admin.property-projects.index')" index-label="Projek Properti" />
    </x-slot>

    <x-admin.form-card :action="route('admin.property-projects.store')" :cancel-url="route('admin.property-projects.index')" submit-label="Simpan Projek">
        @include('admin.property-projects._form')
    </x-admin.form-card>
</x-app-layout>
