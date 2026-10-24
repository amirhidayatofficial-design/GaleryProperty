<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Tambah Lead" :index-url="route('admin.buyer-leads.index')" index-label="Leads Calon Pembeli" />
    </x-slot>

    <x-admin.form-card :action="route('admin.buyer-leads.store')" :cancel-url="route('admin.buyer-leads.index')" submit-label="Simpan Lead">
        @include('admin.buyer-leads._form')
    </x-admin.form-card>
</x-app-layout>
