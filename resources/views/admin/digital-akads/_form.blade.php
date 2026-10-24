@if ($digitalAkad->exists)
    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 text-sm">
        <span class="text-gray-500">Nomor Akad:</span>
        <span class="font-semibold text-gray-900">{{ $digitalAkad->akad_number }}</span>
    </div>
@else
    <p class="text-sm text-gray-500">Nomor akad (format <code>AKAD/YYYYMM/XXXX</code>) dibuat otomatis saat disimpan.</p>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.select name="buyer_lead_id" label="Calon Pembeli (Lead)" :options="$leadOptions" :value="$digitalAkad->buyer_lead_id" placeholder="-- Pilih lead --" required />
    <x-admin.select name="property_project_id" label="Projek Properti" :options="$projectOptions" :value="$digitalAkad->property_project_id" placeholder="-- Pilih projek --" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <x-admin.select name="agency_partner_id" label="Agency" :options="$agencyOptions" :value="$digitalAkad->agency_partner_id" placeholder="-- Tanpa agency --" />
    <x-admin.select name="akad_type" label="Jenis Akad" :options="\App\Models\DigitalAkad::AKAD_TYPES" :value="$digitalAkad->akad_type" placeholder="-- Pilih --" required />
    <x-admin.select name="status" label="Status" :options="\App\Models\DigitalAkad::STATUSES" :value="$digitalAkad->status" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
    <x-admin.input type="number" min="0" step="1" name="transaction_amount" label="Nilai Transaksi (Rp)" :value="$digitalAkad->transaction_amount" required />
    <x-admin.input type="number" min="0" step="1" name="dp_amount" label="Uang Muka / DP (Rp)" :value="$digitalAkad->dp_amount" required />
    <x-admin.input type="number" min="0" max="600" name="tenor_months" label="Tenor (bulan)" :value="$digitalAkad->tenor_months" required />
</div>

<x-admin.textarea name="terms_content" label="Isi Akad / Ketentuan" :value="$digitalAkad->terms_content" rows="6" required />

<x-admin.input
    type="file"
    name="document_pdf"
    label="Dokumen Akad (PDF)"
    accept=".pdf"
    :hint="$digitalAkad->document_pdf_path
        ? 'Dokumen saat ini: ' . $digitalAkad->document_pdf_path . '. Kosongkan jika tidak ingin mengganti. Maks 5 MB.'
        : 'Opsional. Maks 5 MB.'"
    class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
/>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
    <x-admin.input type="datetime-local" name="buyer_signed_at" label="TTD Pembeli" :value="$digitalAkad->buyer_signed_at?->format('Y-m-d\TH:i')" />
    <x-admin.input type="datetime-local" name="agency_signed_at" label="TTD Agency" :value="$digitalAkad->agency_signed_at?->format('Y-m-d\TH:i')" />
    <x-admin.input type="datetime-local" name="developer_signed_at" label="TTD Developer" :value="$digitalAkad->developer_signed_at?->format('Y-m-d\TH:i')" />
</div>
