<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="'Ubah: ' . $propertyProject->title" :index-url="route('agency.property-projects.index')" index-label="Listing Saya" />
    </x-slot>

    <x-admin.form-card
        :action="route('agency.property-projects.update', $propertyProject)"
        method="PUT"
        :cancel-url="route('agency.property-projects.show', $propertyProject)"
        submit-label="Simpan Pengajuan"
        files
    >
        @include('agency.property-projects._form')
    </x-admin.form-card>
</x-app-layout>
