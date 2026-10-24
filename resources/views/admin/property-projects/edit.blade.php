<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="'Ubah: ' . $propertyProject->title" :index-url="route('admin.property-projects.index')" index-label="Projek Properti" />
    </x-slot>

    <x-admin.form-card
        :action="route('admin.property-projects.update', $propertyProject)"
        method="PUT"
        :cancel-url="route('admin.property-projects.index')"
        submit-label="Simpan Perubahan"
    >
        @include('admin.property-projects._form')
    </x-admin.form-card>
</x-app-layout>
