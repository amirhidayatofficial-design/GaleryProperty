<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="'Ubah: ' . $agencyPartner->agency_name" :index-url="route('admin.agency-partners.index')" index-label="Agency Partner" />
    </x-slot>

    <x-admin.form-card
        :action="route('admin.agency-partners.update', $agencyPartner)"
        method="PUT"
        :cancel-url="route('admin.agency-partners.index')"
        submit-label="Simpan Perubahan"
    >
        @include('admin.agency-partners._form')
    </x-admin.form-card>
</x-app-layout>
