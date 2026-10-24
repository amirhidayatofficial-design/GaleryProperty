<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.input name="full_name" label="Nama Lengkap" :value="$buyerLead->full_name" required />
    <x-admin.select name="user_id" label="Akun Pembeli" :options="$userOptions" :value="$buyerLead->user_id" placeholder="-- Belum punya akun --" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.input
        type="tel"
        name="phone"
        label="Nomor WhatsApp"
        :value="$buyerLead->phone"
        placeholder="081234567890"
        inputmode="numeric"
        pattern="[0-9]*"
        minlength="10"
        maxlength="15"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        hint="Hanya angka, 10–15 digit, diawali 0 atau 62."
        required
    />
    <x-admin.input type="email" name="email" label="Email" :value="$buyerLead->email" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
    <x-admin.select name="property_project_id" label="Projek Diminati" :options="$projectOptions" :value="$buyerLead->property_project_id" placeholder="-- Belum ada --" />
    <x-admin.select name="agency_partner_id" label="Agency Penanggung Jawab" :options="$agencyOptions" :value="$buyerLead->agency_partner_id" placeholder="-- Belum ada --" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.input type="number" min="0" step="1" name="budget_min" label="Anggaran Minimum (Rp)" :value="$buyerLead->budget_min" />
    <x-admin.input type="number" min="0" step="1" name="budget_max" label="Anggaran Maksimum (Rp)" :value="$buyerLead->budget_max" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <x-admin.input name="preferred_location" label="Lokasi Diminati" :value="$buyerLead->preferred_location" />
    <x-admin.select name="preferred_scheme" label="Skema Diminati" :options="\App\Models\PropertyProject::SYARIAH_SCHEMES" :value="$buyerLead->preferred_scheme" placeholder="-- Bebas --" />
    <x-admin.select name="status" label="Status Lead" :options="\App\Models\BuyerLead::STATUSES" :value="$buyerLead->status" required />
</div>

<x-admin.textarea name="notes" label="Catatan" :value="$buyerLead->notes" rows="3" />
