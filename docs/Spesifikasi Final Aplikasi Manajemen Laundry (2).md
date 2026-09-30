# Spesifikasi Final Aplikasi Manajemen Laundry

**Versi:** 1.1  
**Bahasa aplikasi:** Bahasa Indonesia  
**Platform:** Web app  
**Teknologi yang disepakati:** Laravel, Blade, Livewire, Tailwind CSS, dan MySQL/MariaDB  
**Penyusun:** Manus AI

## 1. Ringkasan

Aplikasi ini adalah web app berbahasa Indonesia untuk mengelola pelanggan dan pesanan laundry. Sistem mendukung layanan berbasis kilogram dan unit, perhitungan harga otomatis, status pengerjaan, pembayaran, pickup, serta delivery gratis.

Aplikasi ditujukan untuk satu usaha laundry dengan maksimal lima akun pengguna, terdiri dari satu Super Admin dan empat Staf.

## 2. Ruang Lingkup

### Fitur yang termasuk

- Manajemen pelanggan.
- Manajemen layanan dan harga.
- Pembuatan dan pengelolaan pesanan.
- Perhitungan layanan berbasis kilogram dan unit.
- Pembulatan berat ke angka bulat ke atas.
- Status pengerjaan dan riwayat perubahan status.
- Pembayaran dan riwayat pembayaran.
- Pickup pelanggan dan delivery gratis.
- Status serah terima pesanan.
- Manajemen akun Super Admin dan Staf.
- Dasbor operasional.
- Laporan sederhana.
- Penyesuaian harga final oleh Super Admin dengan alasan yang tercatat.
- Nomor pesanan otomatis berdasarkan nomor urut total dan tanggal pesanan.
- Pengaturan waktu Indonesia Barat (WIB).
- Audit aktivitas, backup, ekspor data, dan kesiapan deployment produksi.

### Fitur di luar ruang lingkup

- Manajemen stok bahan laundry.
- Pencatatan pakaian secara individual atau menggunakan tag per pakaian.
- Notifikasi otomatis melalui WhatsApp, SMS, atau email.
- Cetak nota atau pembuatan PDF nota.
- Diskon khusus.
- Biaya tambahan untuk noda berat.
- Biaya tambahan untuk barang besar.
- Biaya delivery.
- Integrasi pembayaran online.

Owner atau staf akan menghubungi pelanggan secara manual melalui WhatsApp.

## 3. Layanan dan Harga Awal

### 3.1 Layanan berbasis kilogram

| Kategori | Nama layanan | Harga | Estimasi selesai |
|---|---|---:|---|
| Cuci Lipat | Cuci Lipat Reguler | Rp3.000/kg | 3 hari kalender |
| Cuci Lipat | Cuci Lipat Express | Rp6.000/kg | 1 hari kalender |
| Cuci Lipat | Cuci Lipat Kilat | Rp8.000/kg | Hari yang sama |
| Setrika | Setrika Reguler | Rp4.000/kg | 3 hari kalender |
| Setrika | Setrika Express | Rp8.000/kg | 1 hari kalender |
| Setrika | Setrika Kilat | Rp10.000/kg | Hari yang sama |
| Cuci Setrika | Cuci Setrika Reguler | Rp5.000/kg | 3 hari kalender |
| Cuci Setrika | Cuci Setrika Express | Rp10.000/kg | 1 hari kalender |
| Cuci Setrika | Cuci Setrika Kilat | Rp12.000/kg | Hari yang sama |

### 3.2 Layanan berbasis unit

| Kategori | Nama layanan | Harga | Satuan |
|---|---|---:|---|
| Barang khusus | Bedcover Besar | Rp25.000 | Unit |
| Barang khusus | Bedcover Kecil/Sedang | Rp20.000 | Unit |
| Barang khusus | Seprei | Rp12.000 | Unit |
| Barang khusus | Seprei Set | Rp15.000 | Unit |
| Barang khusus | Selimut Besar | Rp15.000 | Unit |
| Barang khusus | Selimut Kecil/Sedang | Rp12.000 | Unit |

Daftar layanan tersebut merupakan data awal. Super Admin dapat menambahkan layanan baru secara manual setelah aplikasi digunakan.

## 4. Aturan Perhitungan Harga

### 4.1 Layanan berbasis kilogram

Berat aktual boleh berasal dari timbangan dalam bentuk desimal, tetapi berat tagihan dihitung sebagai angka bulat ke atas. Sistem kemudian menerapkan minimum penagihan sebesar empat kilogram.

```text
Berat dibulatkan = pembulatan ke atas berat aktual
Berat ditagihkan = nilai terbesar antara berat dibulatkan dan 4 kg
Total harga = berat ditagihkan × harga per kilogram
```

Contoh perhitungan:

| Berat aktual | Berat ditagihkan | Contoh harga Rp5.000/kg | Total |
|---:|---:|---:|---:|
| 2 kg | 4 kg | Rp5.000 | Rp20.000 |
| 3 kg | 4 kg | Rp5.000 | Rp20.000 |
| 3,5 kg | 4 kg | Rp5.000 | Rp20.000 |
| 4 kg | 4 kg | Rp5.000 | Rp20.000 |
| 4,1 kg | 5 kg | Rp5.000 | Rp25.000 |
| 4,6 kg | 5 kg | Rp5.000 | Rp25.000 |
| 5 kg | 5 kg | Rp5.000 | Rp25.000 |
| 5,2 kg | 6 kg | Rp5.000 | Rp30.000 |

Sistem harus menampilkan berat aktual dan berat yang ditagihkan agar staf dapat menjelaskan perhitungan kepada pelanggan. Contohnya:

```text
Berat aktual: 3 kg
Berat yang ditagihkan: 4 kg
Harga: Rp5.000/kg
Total: Rp20.000
```

### 4.2 Layanan berbasis unit

Untuk layanan berbasis unit, sistem menghitung harga berdasarkan jumlah unit tanpa menerapkan minimum empat kilogram dan tanpa pembulatan kilogram.

```text
Total harga = jumlah unit × harga per unit
```

Satu pesanan dapat berisi beberapa layanan berbasis kilogram dan unit sekaligus.

## 5. Nomor Pesanan, Waktu, dan Estimasi Selesai

### 5.1 Format nomor pesanan

Nomor pesanan yang diterbitkan sistem berupa gabungan nomor urut total dan tanggal diterimanya pesanan tanpa pemisah. Nomor urut di-pad ke kiri menjadi 4 digit (mis. `0001`, `3702`), dan komponen tanggal memakai `DDMMYYYY` dengan hari dan bulan masing-masing 2 digit (mis. `07092026` untuk 7 September 2026). Format:

```text
{nomor urut 4 digit}{tanggal DD}{bulan MM}{tahun YYYY}
```

Contoh nomor pesanan:

```text
370207092026
```

Contoh tersebut dibaca sebagai nomor urut total `3702` dan tanggal `7/9/2026`. Sistem harus memastikan nomor pesanan tetap unik dan tidak berubah setelah pesanan dibuat. Detail tanggal dan nomor urut tetap ditampilkan terpisah pada halaman detail pesanan untuk mencegah ambiguitas ketika nomor urut semakin panjang. (Ralat 30 Sep 2026: contoh nota lama `3702972026` tanpa padding nol adalah format nota sebelum aplikasi; sistem menerbitkan format ber-padding di atas.)

### 5.2 Zona waktu dan format tampilan

Seluruh tanggal dan waktu aplikasi menggunakan zona waktu **Asia/Jakarta (WIB)**. Format tampilan yang digunakan adalah:

- Tanggal: `7 September 2026`.
- Waktu: `09.30 WIB`.
- Mata uang: `Rp40.000`.

### 5.3 Estimasi selesai

Perhitungan estimasi menggunakan **hari kalender**, termasuk Sabtu, Minggu, dan hari libur.

- Reguler: tiga hari kalender.
- Express: satu hari kalender.
- Kilat: selesai pada hari yang sama.

Tanggal estimasi selesai dihitung otomatis ketika pesanan dibuat. Super Admin dapat mengubah tanggal estimasi secara manual jika diperlukan.

## 6. Status Pesanan

Status pengerjaan yang tersedia adalah:

1. Diterima.
2. Sedang Dicuci.
3. Sedang Dikeringkan.
4. Sedang Disetrika.
5. Sedang Dilipat.
6. Siap Diambil.
7. Dibatalkan.

Setiap perubahan status harus menyimpan status sebelumnya, status baru, tanggal dan waktu perubahan, pengguna yang melakukan perubahan, serta catatan jika ada.

## 7. Pickup, Delivery, dan Serah Terima

Pesanan memiliki metode serah terima berikut:

- **Diambil pelanggan**.
- **Diantar**.

Delivery bersifat gratis dan tidak menambahkan biaya pada pesanan.

Untuk pesanan delivery, sistem menyimpan alamat pengiriman dan catatan pengiriman. Nomor WhatsApp pelanggan tetap tersedia untuk komunikasi manual.

Status serah terima yang digunakan adalah:

- **Belum diserahkan**.
- **Sudah diambil**.
- **Sudah diantar**.

Status serah terima menjadi penanda akhir proses dan tidak memerlukan status pengerjaan **Selesai**. Pesanan pickup menggunakan status **Sudah diambil** setelah pelanggan mengambil laundry. Pesanan delivery menggunakan status **Sudah diantar** setelah laundry diterima pelanggan. Sistem menyimpan tanggal, waktu, dan staf yang mencatat serah terima.

Untuk delivery, sistem juga menyimpan:

- Tanggal delivery.
- Waktu delivery.
- Catatan delivery.
- Alasan gagal delivery jika pengantaran tidak berhasil.

Delivery tidak memerlukan data nama kurir atau delivery person.

## 8. Data Pelanggan

Data pelanggan minimal terdiri dari:

- Nama pelanggan.
- Nomor WhatsApp atau nomor telepon.
- Alamat utama.
- Catatan pelanggan.
- Riwayat pesanan.

Pelanggan dapat dicari berdasarkan nama, nomor WhatsApp, atau nomor telepon.

## 9. Data Pesanan

Setiap pesanan harus menyimpan:

- Nomor pesanan otomatis.
- Pelanggan.
- Tanggal dan waktu diterima.
- Satu atau beberapa layanan.
- Berat aktual untuk layanan kilogram.
- Berat yang ditagihkan untuk layanan kilogram.
- Jumlah unit untuk layanan unit.
- Nama layanan dan harga saat pesanan dibuat.
- Total harga.
- Metode pickup atau delivery.
- Alamat delivery jika diperlukan.
- Estimasi selesai.
- Status pengerjaan.
- Status serah terima.
- Status pembayaran.
- Metode pembayaran.
- Catatan pesanan.
- Staf pembuat pesanan.
- Riwayat perubahan status.
- Tanggal, waktu, dan catatan serah terima.
- Alasan pembatalan jika pesanan dibatalkan.

Contoh satu pesanan:

```text
Cuci Lipat Reguler — 5 kg
Bedcover Besar — 1 unit
Seprei Set — 1 unit
```

## 10. Pembayaran

Status pembayaran yang tersedia:

- Belum Lunas.
- Lunas Sebagian.
- Lunas.
- Dikembalikan.

Metode pembayaran yang tersedia:

- Tunai.
- Transfer bank.
- Kartu.
- E-wallet.
- Lainnya.

Untuk pembayaran sebagian, sistem menyimpan total tagihan, jumlah yang telah dibayar, sisa pembayaran, serta riwayat pembayaran. Sistem mengizinkan beberapa pembayaran sebagian dan menghitung sisa pembayaran secara otomatis. Jumlah pembayaran tidak boleh melebihi total tagihan kecuali diproses sebagai refund atau koreksi yang memiliki riwayat.

Refund disimpan secara terpisah dengan jumlah refund, alasan, pengguna yang memproses, metode pengembalian, serta tanggal dan waktu refund.

## 11. Perubahan Harga dan Penyesuaian Manual

Harga pada pesanan lama tidak berubah ketika Super Admin mengubah harga layanan. Sistem harus menyimpan salinan nama layanan, jenis satuan, harga satuan, dan aturan harga pada detail pesanan saat pesanan dibuat.

Super Admin dapat mengubah harga final suatu pesanan secara manual. Setiap perubahan harus mencatat:

- Harga sebelum perubahan.
- Harga setelah perubahan.
- Alasan perubahan.
- Nama Super Admin.
- Tanggal dan waktu perubahan.

Staf dapat mengubah detail pesanan dalam operasional toko, termasuk data pelanggan, layanan, jumlah atau berat, dan status laundry.

Super Admin memiliki seluruh hak Staf dan juga dapat menentukan harga layanan, menambah atau menonaktifkan layanan, mengelola akun Staf, serta mengedit seluruh data. Hak Super Admin-only tetap diperlukan untuk pengelolaan layanan utama, akun, dan tindakan administratif.

## 12. CRUD Layanan

Menu **Layanan & Harga** harus menyediakan fungsi CRUD:

- **Create:** menambahkan layanan.
- **Read:** melihat daftar layanan.
- **Update:** mengubah nama, kategori, jenis perhitungan, satuan, harga, atau estimasi selesai.
- **Delete:** menghapus atau menonaktifkan layanan.

Layanan yang sudah digunakan dalam pesanan tidak sebaiknya dihapus secara permanen. Layanan tersebut harus dapat dinonaktifkan sehingga tidak muncul pada pesanan baru, tetapi tetap tersedia dalam riwayat pesanan lama.

Data layanan minimal terdiri dari:

- Nama layanan.
- Kategori.
- Jenis perhitungan: kilogram atau unit.
- Satuan: kg atau unit.
- Harga.
- Estimasi selesai.
- Status aktif atau tidak aktif.
- Opsi layanan Kilat atau same-day.

CRUD juga berlaku untuk data pelanggan dan data pesanan. Pengelolaan akun staf hanya tersedia untuk Super Admin.

## 13. Akun dan Hak Akses

Jumlah akun dibatasi maksimal lima akun aktif:

- Satu Super Admin.
- Empat Staf.

### 13.1 Super Admin

Super Admin dapat mengelola pelanggan, pesanan, layanan, harga, akun staf, laporan, penyesuaian harga final, dan seluruh riwayat aktivitas.

### 13.2 Staf

Staf dapat menambah dan mengedit pelanggan, membuat pesanan, mengubah layanan dan kuantitas pesanan, mengubah status pengerjaan, mencatat pembayaran, memperbarui status serah terima, memperbarui harga final beserta alasannya, serta melihat data pelanggan dan pesanan.

Staf tidak dapat mengelola akun staf, mengubah daftar layanan utama, mengubah harga default layanan, atau mengubah pengaturan keamanan dan sistem. Staf boleh mengubah harga final pada transaksi tertentu karena mereka menjalankan operasional toko.

## 14. Menu Aplikasi

Menu utama aplikasi:

- Dasbor.
- Buat Pesanan.
- Daftar Pesanan.
- Pelanggan.
- Layanan & Harga.
- Laporan.
- Manajemen Staf.
- Keluar.

Dasbor menampilkan pesanan hari ini, pesanan yang sedang diproses, pesanan siap diambil, pesanan delivery, pesanan belum lunas, pesanan terlambat, pendapatan hari ini, dan pesanan yang belum diserahkan.

## 15. Pencarian dan Penyaringan

Pesanan dapat dicari atau disaring berdasarkan:

- Nomor pesanan.
- Nama pelanggan.
- Nomor WhatsApp atau nomor telepon.
- Status pengerjaan.
- Status pembayaran.
- Metode pickup atau delivery.
- Tanggal pesanan.

## 16. Laporan

Laporan awal yang diperlukan adalah:

- Jumlah pesanan berdasarkan periode.
- Pendapatan harian, mingguan, dan bulanan.
- Layanan yang paling sering digunakan.
- Pesanan belum lunas.
- Pesanan pickup dan delivery.
- Pesanan yang sudah diserahterimakan.
- Pesanan dibatalkan.
- Aktivitas staf.
- Pesanan delivery gagal dan alasannya.
- Riwayat refund.

## 17. Pembatalan dan Pemulihan Pesanan

Pembatalan pesanan wajib memerlukan alasan. Pesanan yang dibatalkan tidak boleh dihapus permanen karena tetap diperlukan untuk laporan dan audit.

- Pesanan yang dibatalkan tetap tersedia dalam daftar dan laporan.
- Alasan pembatalan wajib disimpan.
- Waktu pembatalan dan pengguna yang membatalkan harus dicatat.
- Super Admin dapat memulihkan pesanan yang dibatalkan.
- Pemulihan pesanan harus dicatat dalam audit log.

## 18. Kebutuhan Keamanan dan Operasional

Kebutuhan berikut merupakan bagian penting dari aplikasi produksi:

| Kebutuhan | Aturan |
|---|---|
| Backup database | Backup terjadwal, lokasi backup terpisah, dan prosedur pemulihan yang diuji. |
| Reset password | Super Admin memiliki alur reset password yang aman. |
| Timeout sesi | Sesi pengguna berakhir setelah periode tidak aktif yang dikonfigurasi dan pengguna diarahkan untuk login kembali. |
| Logout | Pengguna dapat logout dari semua halaman dan sesi aktif ditutup dengan aman. |
| Login activity | Sistem mencatat login berhasil, login gagal, logout, waktu, pengguna, dan informasi teknis yang diperlukan. |
| Permission protection | Aksi Super Admin-only dilindungi policy atau middleware Laravel, bukan hanya disembunyikan dari menu. |
| Error logging | Error aplikasi dicatat tanpa menampilkan detail sensitif kepada pengguna. |
| Ekspor data | Data pelanggan, pesanan, dan pembayaran dapat diekspor oleh pengguna yang berwenang. |
| Data historis | Pesanan, pembayaran, refund, dan audit log tidak dapat dihapus sembarangan. |
| Hosting produksi | Aplikasi memiliki hosting dan domain produksi yang sesuai. |
| HTTPS/SSL | Seluruh koneksi produksi menggunakan HTTPS dengan sertifikat SSL aktif. |
| Pengujian mobile | Aplikasi diuji pada browser mobile dan beberapa perangkat Android umum. |

## 19. Rancangan Teknologi Laravel

Teknologi yang digunakan:

| Komponen | Pilihan |
|---|---|
| Backend | Laravel |
| Tampilan | Blade |
| Interaksi dinamis | Livewire |
| UI dan styling | Tailwind CSS |
| Database | MySQL atau MariaDB |
| Autentikasi | Laravel Breeze atau mekanisme autentikasi Laravel yang setara |
| Bahasa aplikasi | Bahasa Indonesia |
| Mata uang | Rupiah |

Blade dan Livewire dipilih karena sesuai untuk aplikasi internal dengan banyak form dan CRUD. Pendekatan ini mengurangi kebutuhan frontend yang kompleks dan memudahkan pemeliharaan aplikasi.

## 20. Rancangan Data Utama

Tabel utama yang disarankan:

| Tabel | Fungsi |
|---|---|
| `users` | Menyimpan akun Super Admin dan Staf. |
| `customers` | Menyimpan data pelanggan. |
| `services` | Menyimpan layanan, harga, satuan, dan estimasi selesai. |
| `orders` | Menyimpan data utama pesanan. |
| `order_items` | Menyimpan detail layanan dalam pesanan. |
| `payments` | Menyimpan pembayaran dan riwayat pembayaran. |
| `order_status_histories` | Menyimpan riwayat perubahan status pesanan. |
| `order_price_adjustments` | Menyimpan perubahan harga final oleh pengguna yang berwenang. |
| `order_handover_histories` | Menyimpan pickup, delivery, waktu serah terima, dan alasan gagal delivery. |
| `refunds` | Menyimpan pengembalian pembayaran. |
| `audit_logs` | Menyimpan aktivitas penting pengguna dan perubahan data. |

## 21. Tahapan Pengembangan

1. Membuat project Laravel dan konfigurasi database.
2. Menyiapkan autentikasi dan role pengguna.
3. Membuat migrasi dan model database.
4. Membuat CRUD pelanggan.
5. Membuat CRUD layanan dan harga.
6. Memasukkan data layanan awal.
7. Membuat pembuatan pesanan dengan beberapa detail layanan.
8. Menerapkan perhitungan kilogram, pembulatan ke atas, dan minimum empat kilogram.
9. Menerapkan perhitungan layanan unit.
10. Membuat status pesanan dan riwayat status.
11. Membuat pembayaran dan status pembayaran.
12. Membuat pickup, delivery, dan status serah terima.
13. Membuat penyesuaian harga final dengan alasan.
14. Membuat dasbor dan laporan.
15. Menambahkan audit log dan perlindungan penghapusan data historis.
16. Menambahkan backup, ekspor data, reset password, timeout sesi, dan login activity.
17. Menyiapkan logging error, hosting produksi, domain, dan HTTPS/SSL.
18. Melakukan pengujian alur lengkap dan pengujian hak akses.
19. Melakukan pengujian mobile browser pada perangkat Android umum.

## 22. Kriteria Penerimaan Utama

Aplikasi dianggap memenuhi kebutuhan utama apabila:

- Pengguna dapat membuat pesanan dalam Bahasa Indonesia.
- Sistem dapat menangani layanan kilogram dan unit dalam satu pesanan.
- Berat 4,6 kg ditagihkan sebagai 5 kg.
- Berat 3 kg tetap diterima dan ditagihkan sebagai 4 kg.
- Layanan unit tidak terkena aturan minimum empat kilogram.
- Estimasi reguler menggunakan tiga hari kalender.
- Harga pesanan lama tidak berubah setelah harga layanan diperbarui.
- Super Admin dapat menambahkan, mengubah, dan menonaktifkan layanan.
- Super Admin dapat mengubah harga final dengan alasan yang tercatat.
- Nomor pesanan mengikuti format nomor urut total dan tanggal, seperti `370207092026`.
- Seluruh tanggal dan waktu menggunakan WIB.
- Pesanan Kilat dijanjikan selesai pada hari yang sama.
- Pesanan dapat ditandai Sudah Diambil atau Sudah Diantar tanpa status Selesai tambahan.
- Delivery menyimpan tanggal, waktu, catatan, dan alasan gagal delivery jika ada.
- Sistem membedakan akses Super Admin dan Staf.
- Pengguna dapat melihat riwayat status dan pembayaran.
- Beberapa pembayaran sebagian dapat dicatat dan sisa pembayaran dihitung otomatis.
- Refund tercatat terpisah.
- Pesanan yang dibatalkan memerlukan alasan, tetap tersedia untuk laporan, dan tidak dapat dihapus permanen.
- Super Admin dapat memulihkan pesanan yang dibatalkan.
- Sistem tidak memerlukan cetak nota atau notifikasi otomatis.
- Data pelanggan, pesanan, dan pembayaran dapat diekspor.
- Sistem memiliki backup, reset password Super Admin, timeout sesi, login activity, error logging, dan HTTPS.
- Aplikasi diuji pada browser mobile dan perangkat Android umum.

## 23. Keputusan yang Sudah Final

Semua keputusan utama berikut telah ditetapkan:

- Aplikasi menggunakan Laravel.
- Aplikasi berbasis web.
- Antarmuka menggunakan Bahasa Indonesia.
- Pesanan tidak mencatat pakaian satu per satu.
- Berat laundry dibulatkan ke angka bulat ke atas.
- Minimum penagihan layanan kilogram adalah 4 kg.
- Estimasi waktu menggunakan hari kalender.
- Waktu dan tanggal menggunakan WIB.
- Format nomor pesanan mengikuti nomor urut total dan tanggal, dengan contoh `370207092026`.
- Layanan Kilat selesai pada hari yang sama; tidak ada konsep cutoff.
- Delivery gratis.
- Status serah terima adalah Sudah Diambil atau Sudah Diantar dan menjadi penanda akhir proses.
- Layanan memiliki CRUD.
- Harga lama tetap berlaku untuk pesanan lama.
- Staf dapat mengubah detail transaksi operasional. Penyesuaian harga final adalah hak Super Admin saja dan alasannya wajib dicatat.
- Super Admin dapat mengelola harga default, layanan, akun Staf, dan seluruh data.
- Pembatalan memerlukan alasan, tidak menghapus data, dan dapat dipulihkan oleh Super Admin.
- Pembayaran sebagian, refund, delivery gagal, dan audit aktivitas memiliki riwayat tersendiri.
- Backup, ekspor data, reset password, timeout sesi, login activity, error logging, hosting, domain, dan HTTPS/SSL termasuk kebutuhan teknis penting.
- Aplikasi tidak membutuhkan cetak nota.
- Notifikasi otomatis tidak termasuk dalam ruang lingkup.

## Referensi

Dokumen ini merupakan spesifikasi kebutuhan berdasarkan keputusan dan aturan bisnis yang diberikan oleh pemilik aplikasi. Tidak ada sumber eksternal yang digunakan.
