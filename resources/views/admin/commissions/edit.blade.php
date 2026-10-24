<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Ubah Komisi" :index-url="route('admin.commissions.index')" index-label="Komisi Agency" />
    </x-slot>

    <x-admin.form-card
        :action="route('admin.commissions.update', $commission)"
        method="PUT"
        :cancel-url="route('admin.commissions.index')"
        submit-label="Simpan Perubahan"
        files
    >
        @include('admin.commissions._form')
    </x-admin.form-card>
</x-app-layout>
