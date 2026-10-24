<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Unggah Berkas" :index-url="route('admin.property-files.index')" index-label="Berkas Properti" />
    </x-slot>

    <x-admin.form-card :action="route('admin.property-files.store')" :cancel-url="route('admin.property-files.index')" submit-label="Unggah Berkas" files>
        @include('admin.property-files._form')
    </x-admin.form-card>
</x-app-layout>
