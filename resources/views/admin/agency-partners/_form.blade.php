<x-admin.select
    name="user_id"
    label="Akun Pemilik (Role Agency Partner)"
    :options="$userOptions"
    :value="$agencyPartner->user_id"
    placeholder="-- Pilih akun --"
    hint="Hanya akun ber-role Mitra Agency yang belum punya profil agency. Buat akunnya dulu di menu Kelola Pengguna jika belum ada."
    required
/>

@include('agency-partners._fields')

<x-admin.select
    name="verification_status"
    label="Status Verifikasi"
    :options="\App\Models\AgencyPartner::VERIFICATION_STATUSES"
    :value="$agencyPartner->verification_status"
    hint="Tanggal verifikasi terisi otomatis saat status diubah menjadi Terverifikasi."
    required
/>
