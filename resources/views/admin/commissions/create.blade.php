<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Tambah Komisi" :index-url="route('admin.commissions.index')" index-label="Komisi Agency" />
    </x-slot>

    <x-admin.form-card :action="route('admin.commissions.store')" :cancel-url="route('admin.commissions.index')" submit-label="Simpan Komisi" files>
        @include('admin.commissions._form')
    </x-admin.form-card>
</x-app-layout>
