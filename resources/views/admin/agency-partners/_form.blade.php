<x-admin.select
    name="user_id"
    label="Akun Pemilik (Role Agency Partner)"
    :options="$userOptions"
    :value="$agencyPartner->user_id"
    placeholder="-- Pilih akun --"
    hint="Hanya akun ber-role Mitra Agency yang belum punya profil agency. Buat akunnya dulu di menu Kelola Pengguna jika belum ada."
    required
/>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.input name="agency_name" label="Nama Agency" :value="$agencyPartner->agency_name" placeholder="PT Berkah Properti Syariah" required />
    <x-admin.input name="license_number" label="Nomor Izin Usaha" :value="$agencyPartner->license_number" placeholder="SIU-P4/100/APS/2026" />
</div>

<x-admin.textarea name="company_address" label="Alamat Kantor" :value="$agencyPartner->company_address" rows="3" />

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
    <x-admin.select
        name="bank_name"
        label="Bank Syariah"
        :options="array_combine(\App\Models\AgencyPartner::BANKS, \App\Models\AgencyPartner::BANKS)"
        :value="$agencyPartner->bank_name"
        placeholder="-- Pilih bank --"
    />
    <x-admin.input name="bank_account_number" label="Nomor Rekening" :value="$agencyPartner->bank_account_number" />
    <x-admin.input name="bank_account_holder" label="Atas Nama" :value="$agencyPartner->bank_account_holder" />
</div>

<x-admin.select
    name="verification_status"
    label="Status Verifikasi"
    :options="\App\Models\AgencyPartner::VERIFICATION_STATUSES"
    :value="$agencyPartner->verification_status"
    hint="Tanggal verifikasi terisi otomatis saat status diubah menjadi Terverifikasi."
    required
/>
