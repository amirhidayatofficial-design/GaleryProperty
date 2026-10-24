<x-app-layout>
    <x-slot name="header">
        <x-admin.header :title="'Ubah: ' . $buyerLead->full_name" :index-url="route('admin.buyer-leads.index')" index-label="Leads Calon Pembeli" />
    </x-slot>

    <x-admin.form-card
        :action="route('admin.buyer-leads.update', $buyerLead)"
        method="PUT"
        :cancel-url="route('admin.buyer-leads.index')"
        submit-label="Simpan Perubahan"
    >
        @include('admin.buyer-leads._form')
    </x-admin.form-card>
</x-app-layout>
