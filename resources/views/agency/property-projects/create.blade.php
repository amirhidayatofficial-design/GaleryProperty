<x-app-layout>
    <x-slot name="header">
        <x-admin.header title="Ajukan Listing" :index-url="route('agency.property-projects.index')" index-label="Listing Saya" />
    </x-slot>

    <x-admin.form-card :action="route('agency.property-projects.store')" :cancel-url="route('agency.property-projects.index')" submit-label="Ajukan ke Admin" files>
        @include('agency.property-projects._form')
    </x-admin.form-card>
</x-app-layout>
