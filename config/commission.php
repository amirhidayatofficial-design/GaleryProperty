<?php

/*
|--------------------------------------------------------------------------
| Aturan komisi agency (sesuai prinsip syariah)
|--------------------------------------------------------------------------
|
| Developer ↔ Agency : samsarah/wasathah berkarakter ju'alah. Imbalan hanya
|                      menjadi hak agency setelah hasil tercapai, sehingga
|                      komisi dibayar per termin.
| Agency ↔ Platform  : wakalah bil ujrah. Platform menagih komisi atas nama
|                      agency dan mengambil platform fee sebagai ujrah.
|
| Semua angka di bawah wajib tercantum di PKS agar besarannya jelas (ma'lum)
| sejak awal. Mengubah angka hanya berlaku untuk akad yang ditandatangani
| setelahnya; komisi yang sudah terbit tidak ikut berubah.
|
*/

return [

    // Termin pembayaran komisi. Jumlah persentase harus 100.
    'termins' => [
        1 => [
            'percentage' => 50,
            'label' => 'Termin 1',
            'condition' => 'Akad jual beli ditandatangani 3 pihak dan DP lunas',
        ],
        2 => [
            'percentage' => 50,
            'label' => 'Termin 2',
            'condition' => 'Serah terima unit (atau AJB untuk cash keras)',
        ],
    ],

    // Hanya akad jual beli yang menghasilkan komisi; MOU belum merupakan penjualan.
    'eligible_akad_types' => ['akad_istishna', 'akad_murabahah'],

    // Ujrah platform: persentase dari komisi setiap termin.
    'platform_fee_rate' => 20,

    // Jatuh tempo invoice ke developer (hari kalender). Lewat jatuh tempo: pengingat, tanpa denda.
    'invoice_due_days' => 14,

    // Batas meneruskan bagian agency setelah dana diterima (hari kerja), sebagai amanah wakil.
    'payout_due_business_days' => 3,

];
