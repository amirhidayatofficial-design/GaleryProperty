<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.select
        name="digital_akad_id"
        label="Akad Digital"
        :options="$akadOptions"
        :value="$commission->digital_akad_id"
        placeholder="-- Pilih akad --"
        hint="Hanya akad berstatus Ditandatangani oleh ketiga pihak dan belum punya komisi yang bisa dipilih."
        required
    />
    <x-admin.select name="agency_partner_id" label="Agency Penerima" :options="$agencyOptions" :value="$commission->agency_partner_id" placeholder="-- Pilih agency --" required />
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
    <x-admin.input type="number" min="0" step="1" name="base_transaction_amount" label="Nilai Transaksi (Rp)" :value="$commission->base_transaction_amount" required />
    <x-admin.input type="number" min="0" max="100" step="0.01" name="commission_rate" label="Persentase Komisi (%)" :value="$commission->commission_rate" required />
    <x-admin.input type="number" min="0" step="1" name="platform_fee" label="Biaya Platform (Rp)" :value="$commission->platform_fee" required />
</div>

<p class="text-sm text-gray-500 -mt-2">
    Total komisi = nilai transaksi × persentase. Komisi bersih = total komisi − biaya platform. Keduanya dihitung otomatis saat disimpan.
    @if ($commission->exists)
        <br>Saat ini: total <strong>Rp {{ number_format($commission->total_commission, 0, ',', '.') }}</strong>,
        bersih <strong>Rp {{ number_format($commission->net_commission, 0, ',', '.') }}</strong>.
    @endif
</p>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-admin.select name="status" label="Status" :options="\App\Models\Commission::STATUSES" :value="$commission->status" required />
    <x-admin.input
        type="datetime-local"
        name="paid_at"
        label="Tanggal Dibayar"
        :value="$commission->paid_at?->format('Y-m-d\TH:i')"
        hint="Terisi otomatis dengan waktu sekarang jika status Dibayar dan kolom ini kosong."
    />
</div>

<x-admin.input
    type="file"
    name="payment_proof"
    label="Bukti Transfer"
    accept=".jpg,.jpeg,.png,.webp,.pdf"
    :hint="$commission->payment_proof_path
        ? 'Bukti saat ini: ' . $commission->payment_proof_path . '. Kosongkan jika tidak ingin mengganti. Maks 5 MB.'
        : 'Opsional. Format JPG/PNG/WEBP/PDF, maks 5 MB.'"
    class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
/>

<x-admin.textarea name="notes" label="Catatan" :value="$commission->notes" rows="3" />
