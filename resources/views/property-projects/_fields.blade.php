{{-- Field data listing yang dipakai bersama oleh form admin dan form pengajuan agency --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.input name="title" label="Judul Projek" :value="$propertyProject->title" placeholder="Cluster Madinah Green Village" hint="Slug URL dibuat otomatis dari judul." required />
    <x-admin.input name="developer_name" label="Nama Developer" :value="$propertyProject->developer_name" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <x-admin.select name="property_type" label="Tipe Properti" :options="\App\Models\PropertyProject::PROPERTY_TYPES" :value="$propertyProject->property_type" placeholder="-- Pilih --" required />
    <x-admin.select name="syariah_scheme" label="Skema Syariah" :options="\App\Models\PropertyProject::SYARIAH_SCHEMES" :value="$propertyProject->syariah_scheme" placeholder="-- Pilih --" required />
    <x-admin.select name="status" label="Status" :options="\App\Models\PropertyProject::STATUSES" :value="$propertyProject->status" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.input type="number" step="1" min="0" name="price" label="Harga (Rp)" :value="$propertyProject->price" required />
    <x-admin.input type="number" step="0.01" min="0" max="100" name="commission_rate" label="Komisi Agen (%)" :value="$propertyProject->commission_rate" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
    <x-admin.input name="province" label="Provinsi" :value="$propertyProject->province" required />
    <x-admin.input name="city" label="Kota / Kabupaten" :value="$propertyProject->city" required />
    <x-admin.input name="district" label="Kecamatan" :value="$propertyProject->district" />
</div>

<x-admin.textarea name="address_detail" label="Alamat Lengkap" :value="$propertyProject->address_detail" rows="2" required />

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2 border-t border-gray-100">
    <x-admin.input type="number" min="0" name="bedroom" label="Kamar Tidur" :value="$propertyProject->bedroom" />
    <x-admin.input type="number" min="0" name="bathroom" label="Kamar Mandi" :value="$propertyProject->bathroom" />
    <x-admin.input type="number" min="0" name="building_area" label="Luas Bangunan (m²)" :value="$propertyProject->building_area" />
    <x-admin.input type="number" min="0" name="land_area" label="Luas Tanah (m²)" :value="$propertyProject->land_area" />
</div>

<x-admin.textarea name="description" label="Deskripsi" :value="$propertyProject->description" required />
<x-admin.textarea name="halal_guarantee_notes" label="Catatan Jaminan Halal" :value="$propertyProject->halal_guarantee_notes" rows="3" />
