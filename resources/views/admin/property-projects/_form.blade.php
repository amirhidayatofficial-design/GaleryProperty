<x-admin.select name="agency_partner_id" label="Agency Penanggung Jawab" :options="$agencyOptions" :value="$propertyProject->agency_partner_id" placeholder="-- Tanpa agency --" />

@include('property-projects._fields')

<x-admin.checkbox
    name="is_verified"
    label="Verifikasi & tayangkan di katalog"
    :checked="$propertyProject->is_verified"
    hint="Hanya bisa dicentang jika projek sudah memiliki Dokumen Legalitas (PKS / surat kuasa developer). Untuk projek baru, simpan dulu, unggah dokumennya, lalu tayangkan."
/>
