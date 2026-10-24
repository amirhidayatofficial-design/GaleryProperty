# Naskah Demo GalleryProperty

Panduan langkah demi langkah untuk mendemokan aplikasi kepada trainer atau tim. Ada dua bagian:

- **Bagian A — Demo per peran** (± 3–5 menit per peran): menunjukkan apa yang bisa dilakukan setiap peran.
- **Bagian B — Skenario lengkap** (± 15 menit): satu cerita utuh yang menyambungkan semua peran sesuai [Alur Bisnis](../README.md#d-alur-bisnis), dari agency mengajukan listing sampai komisi dicairkan.

---

## Persiapan (sebelum demo)

1. **Reset data ke kondisi awal** agar hasil demo selalu sama.
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```
   > ⚠️ `migrate:fresh` menghapus **semua** data lalu mengisinya ulang dengan data contoh.
2. **Jalankan server**: `php artisan serve`, lalu buka `http://127.0.0.1:8000`.
3. **Siapkan dua jendela browser**: jendela biasa dan jendela **incognito** (Ctrl+Shift+N). Dengan begitu dua peran bisa login bersamaan, misalnya pembeli dan agency saat demo chat.
4. **Siapkan satu file PDF apa saja** di komputer (misalnya `PKS-Developer.pdf`). File ini dipakai sebagai PKS / surat kuasa developer saat agency mengajukan listing.

### Akun demo

| Peran | Email | Kata sandi | Catatan |
| :--- | :--- | :--- | :--- |
| Admin | `admin@galerypropertysyariah.com` | `admin123` | Super Admin |
| Agency | `agency1@galerypropertysyariah.com` | `password123` | PT Berkah Properti Syariah (terverifikasi) |
| Agency lain | `agency2@galerypropertysyariah.com` | `password123` | Amanah Islamic Land Agency |
| Pembeli | `pembeli1@keluargamuslim.com` | `password123` | Fulan bin Abdullah |

---

## Bagian A — Demo per peran

### A1. Pengunjung (belum login)

Tujuan: menunjukkan bahwa siapa pun bisa mencari properti, tapi kontak agency tidak dibuka ke publik.

| # | Langkah | Yang ditunjukkan |
| :-: | :--- | :--- |
| 1 | Buka halaman depan `http://127.0.0.1:8000` | Landing page: hero, jumlah projek tayang / agency / kota diambil dari database, prinsip tanpa riba, listing terbaru, cara kerja, penjelasan 4 skema syariah. |
| 2 | Ketik **Bogor** di kotak pencarian hero, klik **Cari Properti** | Langsung masuk Katalog dengan hasil yang sudah tersaring. |
| 3 | Di Katalog, coba filter **Tipe = Tanah Kavling** atau **Harga Maksimal ≤ Rp 500 juta** | Filter bekerja; tombol ✕ untuk reset filter. |
| 4 | Buka salah satu projek, misalnya **Cluster Madinah Green Village Phase 1** | Detail projek. Kartu kanan hanya menampilkan **nama agency**, tanpa nomor WhatsApp atau email. |
| 5 | Klik **Login untuk Chat Agency** | Pengunjung harus login dulu sebelum bisa chat. |

### A2. Pembeli (`pembeli1@keluargamuslim.com`)

Tujuan: menunjukkan cara pembeli menghubungi agency, yaitu hanya lewat chat.

| # | Langkah | Yang ditunjukkan |
| :-: | :--- | :--- |
| 1 | Login sebagai pembeli | Dashboard pembeli: sapaan, jumlah chat belum dibaca, jumlah percakapan, dan pintasan ke katalog. |
| 1b | Coba buka halaman depan `http://127.0.0.1:8000` | Otomatis diarahkan ke Dashboard: landing page hanya untuk pengunjung yang belum login (berlaku untuk semua peran). |
| 2 | Buka **Katalog** → **Ruko Syariah Al-Barokah Boulevard Bandung** | Tombol **Chat Agency** muncul karena login sebagai pembeli. |
| 3 | Klik **Chat Agency**, kirim pesan: *"Assalamu'alaikum, unit masih tersedia?"* | Ruang chat dengan agency. Pesan pertama ini otomatis tercatat sebagai **lead baru** untuk agency. |
| 4 | Klik **Chat Agency** lagi dari halaman projek yang sama | Masuk ke percakapan yang sama (satu percakapan per projek), riwayat pesan tetap ada. |
| 5 | Coba buka menu admin dengan mengetik `http://127.0.0.1:8000/admin/users` | Ditolak dengan **403**: pembeli tidak punya akses admin. |

### A3. Agency Partner (`agency1@galerypropertysyariah.com`)

Tujuan: menunjukkan bahwa agency bisa mengajukan listing dan membalas chat, tapi tidak bisa menayangkan listing sendiri.

| # | Langkah | Yang ditunjukkan |
| :-: | :--- | :--- |
| 1 | Login sebagai agency (di jendela incognito) | Dashboard agency: chat belum dibaca, listing tayang / menunggu verifikasi, total komisi bersih yang sudah dibayar. |
| 2 | Buka **Chat** | Pesan dari Fulan bin Abdullah (demo A2) muncul dengan **penanda belum dibaca**. |
| 3 | Buka percakapan, balas: *"Wa'alaikumussalam, masih tersedia. Mau jadwalkan survei?"* | Di jendela pembeli, balasan muncul sendiri dalam ± 5 detik tanpa reload. |
| 4 | Buka **Listing Saya** | Daftar listing milik agency ini saja, beserta status pengajuan dan jumlah dokumen kuasa. |
| 5 | Klik **Ajukan Listing**, isi data, coba simpan **tanpa** PKS | Ditolak: PKS / surat kuasa developer wajib dilampirkan. |
| 6 | Lampirkan file PDF, klik **Ajukan ke Admin** | Listing tersimpan dengan status **Menunggu Verifikasi** dan **belum** tampil di katalog. |
| 7 | Buka salah satu listing yang **sudah tayang**, misalnya Cluster Madinah | Tidak ada tombol Ubah: listing yang sudah tayang hanya bisa diubah admin. |

### A4. Admin (`admin@galerypropertysyariah.com`)

Tujuan: menunjukkan peran admin sebagai penjaga kualitas dan kepatuhan syariah.

| # | Langkah | Yang ditunjukkan |
| :-: | :--- | :--- |
| 1 | Login sebagai admin | Dashboard admin: listing dan agency yang menunggu verifikasi, lead baru, komisi belum dibayar. |
| 2 | **Kelola Data → Projek Properti**, buka listing yang diajukan agency (demo A3), klik **Ubah**, centang **Verifikasi & tayangkan di katalog**, simpan | Listing kini tayang di katalog. Coba juga pada projek baru tanpa Dokumen Legalitas: ditolak. |
| 3 | **Kelola Data → Leads Pembeli** | Lead dari chat demo A2 muncul dengan catatan *"Lead otomatis dari chat website."* |
| 4 | **Kelola Data → Agency Partner**, coba hapus **PT Berkah Properti Syariah** | Ditolak karena agency masih memiliki riwayat komisi. |
| 5 | **Kelola Data → Akad Digital**, buka salah satu akad | Nomor akad otomatis `AKAD/YYYYMM/XXXX` dan status tanda tangan 3 pihak. |
| 6 | **Kelola Data → Komisi**, buka salah satu komisi | Total komisi = nilai transaksi × persentase, komisi bersih = total − biaya platform. |
| 7 | **Kelola Pengguna** | CRUD akun semua peran. Ubah status pembeli menjadi **Suspended**, lalu coba login sebagai pembeli itu: ditolak dengan pesan akun ditangguhkan. Mengubah status ke **Aktif** sama dengan memverifikasi email secara manual. |
| 8 | **Kelola Pengguna → Tambah Pengguna Baru**, buka pilihan Peran | Hanya ada Calon Pembeli dan Mitra Agency. Akun Super Admin tidak bisa dibuat atau dinaikkan dari aplikasi. |

---

## Bagian B — Skenario lengkap (end-to-end)

Cerita: *PT Berkah Properti Syariah mendapat kuasa dari developer untuk memasarkan projek baru. Seorang calon pembeli baru tertarik, bertanya lewat chat, lalu terjadi akad, dan agency menerima komisi.*

Gunakan jendela biasa untuk **admin** dan **pembeli**, serta jendela incognito untuk **agency**.

### Langkah 1 — Agency mengajukan listing *(jendela incognito, login agency1)*
1. **Listing Saya → Ajukan Listing**, isi:
   - Judul: **Griya Tahfidz Residence Bekasi**
   - Developer: **PT Barakah Land**
   - Tipe: **Rumah** · Skema: **Istishna** · Status: **Tersedia**
   - Harga: **1200000000** · Komisi Agen: **3**
   - Provinsi: **Jawa Barat** · Kota: **Bekasi** · Alamat & Deskripsi: bebas
   - PKS / Surat Kuasa Developer: **file PDF yang sudah disiapkan**
2. Klik **Ajukan ke Admin** → status **Menunggu Verifikasi**.
3. *(Opsional)* Buka **Katalog**: listing ini belum muncul.

### Langkah 2 — Admin memverifikasi dan menayangkan *(jendela biasa, login admin)*
1. **Dashboard**: kartu *Listing menunggu verifikasi* bertambah.
2. **Kelola Data → Projek Properti** → **Griya Tahfidz Residence Bekasi** → **Ubah**.
3. Centang **Verifikasi & tayangkan di katalog** → **Simpan Perubahan**.
4. Logout.

### Langkah 3 — Calon pembeli baru mendaftar dan bertanya *(jendela biasa)*
1. **Daftar** dengan nama **Ahmad Demo**, email `ahmad.demo@example.com`, kata sandi bebas (minimal 8 karakter). Akun berstatus **pending** dan diarahkan ke halaman *Cek email Anda*.
   - Buka inbox **Mailtrap**, klik **Verifikasi Alamat Email** di email dari GalleryProperty. Akun menjadi **active** dan masuk ke Dashboard.
   - *(Opsional)* Sebelum verifikasi, coba buka **Chat** atau **Bantuan**: selalu diarahkan kembali ke halaman verifikasi.
2. Buka **Katalog** → **Griya Tahfidz Residence Bekasi**. Kartu kanan menampilkan *Dipasarkan oleh PT Berkah Properti Syariah*, tanpa kontak pribadi.
3. Klik **Chat Agency**, kirim: *"Assalamu'alaikum, apakah masih ada unit hook? Skema istishna-nya berapa tahap?"*

### Langkah 4 — Agency membalas *(jendela incognito)*
1. Menu **Chat** menampilkan penanda merah (1 percakapan belum dibaca).
2. Buka percakapan dengan **Ahmad Demo**, balas: *"Wa'alaikumussalam, masih ada. Pembayaran 4 tahap sesuai progres bangun. Bisa survei Sabtu ini?"*
3. Biarkan jendela pembeli terbuka: balasan muncul sendiri dalam ± 5 detik.

### Langkah 5 — Admin menerbitkan akad *(jendela biasa: logout pembeli, login admin)*

> Survei dan booking (langkah 4 di alur bisnis) terjadi di luar sistem. Admin hanya mencatat hasilnya.

1. **Kelola Data → Leads Pembeli**: lead **Ahmad Demo** sudah otomatis tercatat dari chat. Bisa diubah statusnya menjadi **Proses Akad**. Saat menyimpan, nomor WhatsApp wajib diisi angka yang valid, misalnya `081234567890`, karena lead dari chat belum punya nomor.
2. **Kelola Data → Akad Digital → Terbitkan Akad**, isi:
   - Calon Pembeli: **Ahmad Demo** · Projek: **Griya Tahfidz Residence Bekasi** · Agency: **PT Berkah Properti Syariah**
   - Jenis Akad: **Akad Istishna** · Status: **Ditandatangani**
   - Nilai Transaksi: **1200000000** · DP: **240000000** · Tenor: **24**
   - Isi Akad: bebas
   - TTD Pembeli, TTD Agency, TTD Developer: isi ketiganya dengan tanggal hari ini
3. Klik **Terbitkan Akad** → nomor akad otomatis, misalnya `AKAD/202610/0007`.
4. *(Opsional)* Coba isi DP lebih besar dari nilai transaksi: ditolak.

### Langkah 6 — Admin mencatat komisi *(jendela biasa, masih admin)*
1. **Kelola Data → Komisi → Tambah Komisi**. Dropdown Akad Digital hanya berisi akad yang sudah ditandatangani 3 pihak dan belum punya komisi, yaitu akad Ahmad Demo.
2. Isi: Agency **PT Berkah Properti Syariah** · Nilai Transaksi **1200000000** · Persentase **3** · Biaya Platform **500000** · Status **Dibayar**.
3. Simpan → hasil hitung otomatis:
   - Total komisi: **Rp 36.000.000** (1,2 miliar × 3%)
   - Komisi bersih: **Rp 35.500.000** (36 juta − 500 ribu)
   - Tanggal dibayar terisi otomatis.

### Langkah 7 — Agency melihat komisinya *(jendela incognito)*
1. Buka **Dashboard** agency (refresh): kartu *Komisi bersih dibayar* bertambah **Rp 35.500.000**.

**Selesai.** Seluruh alur bisnis sudah ditunjukkan: pengajuan → verifikasi → tayang → chat → lead → akad → komisi.

---

## Pertanyaan yang mungkin muncul

| Pertanyaan | Jawaban singkat |
| :--- | :--- |
| Kenapa pembeli tidak bisa langsung WhatsApp agency? | Keputusan produk: semua komunikasi lewat chat website agar tercatat dan lead otomatis masuk ke sistem. |
| Siapa yang tanda tangan atas nama developer? | Agency, berdasarkan PKS / surat kuasa (*wakalah*) yang dilampirkan saat pengajuan listing. Developer belum punya akun di MVP ini. |
| Apakah sistem memproses pembayaran? | Tidak. Pembayaran properti dan komisi terjadi di luar sistem; sistem hanya mencatat nilai, status, dan bukti transfer. |
| Bagaimana jika pembeli lupa kata sandi? | Klik **Lupa kata sandi?** di halaman login; tautan atur ulang dikirim lewat email (saat demo masuk ke inbox Mailtrap). Admin juga bisa mengganti kata sandinya lewat Kelola Pengguna. |
| Bagaimana agency baru bergabung? | Admin membuat akun ber-role Mitra Agency, lalu membuat dan memverifikasi profil agency-nya di Kelola Data → Agency Partner. |
