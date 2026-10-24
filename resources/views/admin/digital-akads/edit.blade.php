<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="'Ubah: ' . $digitalAkad->akad_number" :index-url="route('admin.digital-akads.index')" index-label="Akad Digital" />
    </x-slot>

    <x-admin.form-card
        :action="route('admin.digital-akads.update', $digitalAkad)"
        method="PUT"
        :cancel-url="route('admin.digital-akads.index')"
        submit-label="Simpan Perubahan"
        files
    >
        @include('admin.digital-akads._form')
    </x-admin.form-card>
</x-app-layout>
