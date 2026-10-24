<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Tambah Agency" :index-url="route('admin.agency-partners.index')" index-label="Agency Partner" />
    </x-slot>

    <x-admin.form-card :action="route('admin.agency-partners.store')" :cancel-url="route('admin.agency-partners.index')" submit-label="Simpan Agency">
        @include('admin.agency-partners._form')
    </x-admin.form-card>
</x-app-layout>
