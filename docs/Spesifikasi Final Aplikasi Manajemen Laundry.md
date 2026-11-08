# Spesifikasi Final Aplikasi Manajemen Laundry

**Versi:** 1.2
**Bahasa aplikasi:** Bahasa Indonesia  
**Platform:** Web app  
**Teknologi yang disepakati:** Laravel, Blade, Livewire, Tailwind CSS, dan MySQL/MariaDB  
**Status:** Diselaraskan dengan implementasi aplikasi per 8 November 2026.

Dokumen ini mencatat aturan bisnis dan perilaku yang benar-benar tersedia di aplikasi. Daftar deployment dan QA yang belum selesai ditandai sebagai pekerjaan lanjutan, bukan dianggap sudah diterapkan.

## 1. Ringkasan

Aplikasi ini adalah web app berbahasa Indonesia untuk mengelola pelanggan dan pesanan laundry. Sistem mendukung layanan berbasis kilogram dan unit, perhitungan harga otomatis, status pengerjaan, pembayaran, pickup, serta delivery gratis.

Aplikasi ditujukan untuk satu usaha laundry. Jumlah akun Super Admin dan Staf tidak dibatasi.

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
- Audit aktivitas dan endpoint ekspor data untuk Super Admin.
- Alur reset password dengan kode OTP email.
- Kesiapan backup dan deployment produksi sebagai pekerjaan operasional lanjutan.

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

Tanggal estimasi selesai dihitung otomatis ketika pesanan dibuat. Super Admin dapat mengubah tanggal estimasi dari halaman detail pesanan. Pengubahan ini adalah override administratif; tidak mengubah estimasi default layanan.

## 6. Status Pesanan

Status pengerjaan yang tersedia adalah:

1. Diterima.
2. Sedang Dicuci.
3. Sedang Dikeringkan.
4. Sedang Disetrika.
5. Sedang Dilipat.
6. Siap Diambil.
7. Dibatalkan.

Setiap perubahan status harus menyimpan status sebelumnya, status baru, tanggal dan waktu perubahan, pengguna yang melakukan perubahan, serta catatan jika ada. Pada halaman detail, tombol tahap Setrika hanya ditampilkan jika layanan pesanan memerlukan setrika; tahap Lipat hanya ditampilkan untuk layanan yang namanya menunjukkan cuci-lipat. Validasi transisi tetap dilakukan di server.

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

Serah terima hanya dapat dicatat setelah pesanan siap dan tidak dapat dicatat ulang setelah berhasil. Jika delivery gagal, alasan kegagalan dicatat dan percobaan serah terima berikutnya tetap dapat dilakukan.

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

Refund disimpan secara terpisah dengan jumlah refund, alasan, pengguna yang memproses, metode pengembalian, serta tanggal dan waktu refund. Refund tidak menghapus pembayaran awal; nilai refund mengurangi pembayaran bersih dan dapat membuka kembali sisa tagihan. Pesanan yang dibatalkan tidak dapat menerima pembayaran baru, tetapi refund tetap dapat dicatat.

## 11. Perubahan Harga dan Penyesuaian Manual

Harga pada pesanan lama tidak berubah ketika Super Admin mengubah harga layanan. Sistem harus menyimpan salinan nama layanan, jenis satuan, harga satuan, dan aturan harga pada detail pesanan saat pesanan dibuat.

Super Admin dapat mengubah harga final suatu pesanan secara manual. Setiap perubahan harus mencatat:

- Harga sebelum perubahan.
- Harga setelah perubahan.
- Alasan perubahan.
- Nama Super Admin.
- Tanggal dan waktu perubahan.

Staf dapat mengelola data pelanggan, membuat pesanan, mencatat pembayaran/refund dan serah terima, serta mengubah status pengerjaan. Form edit item pesanan yang sudah tersimpan tidak tersedia. Perubahan harga final dan estimasi selesai adalah tindakan Super Admin.

Super Admin memiliki seluruh hak operasional Staf dan juga dapat mengelola layanan/harga default, akun Staf, laporan, audit log, perubahan harga final, estimasi selesai, dan pemulihan pesanan batal. Pembatasan diterapkan di server melalui middleware/policy, bukan hanya dengan menyembunyikan tombol.

## 12. CRUD Layanan

Menu **Layanan & Harga** menyediakan daftar dan pencarian layanan untuk semua pengguna terautentikasi. Aksi tambah, edit, aktif/nonaktif hanya tersedia untuk Super Admin. Menu ini tidak menyediakan penghapusan permanen.

- **Create:** menambahkan layanan.
- **Read:** melihat daftar layanan.
- **Update:** mengubah nama, kategori, jenis perhitungan, satuan, harga, atau estimasi selesai.
- **Delete:** tidak tersedia; layanan dinonaktifkan agar riwayat pesanan tetap utuh.

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

Pelanggan dapat dibuat dan diperbarui, lalu dihapus hanya jika tidak memiliki riwayat pesanan. Pesanan dibuat dan diproses melalui status, pembayaran, refund, serah terima, pembatalan, serta pemulihan; UI tidak menyediakan edit item pesanan yang telah disimpan maupun penghapusan permanen. Pengelolaan akun tersedia untuk Super Admin.

## 13. Akun dan Hak Akses

Aplikasi tidak membatasi jumlah akun aktif Super Admin maupun Staf. Super Admin dapat membuat akun Staf tanpa batas jumlah; akun Super Admin disiapkan melalui seeder/CLI. Setidaknya satu Super Admin harus tetap aktif dan pengguna tidak dapat menonaktifkan akunnya sendiri.

### 13.1 Super Admin

Super Admin dapat mengelola pelanggan, pesanan, layanan, harga, akun staf, laporan, audit log, penyesuaian harga final, override estimasi selesai, dan pemulihan pesanan.

### 13.2 Staf

Staf dapat menambah dan mengedit pelanggan, membuat pesanan, mengubah status pengerjaan, mencatat pembayaran/refund, memperbarui status serah terima, serta melihat layanan, pelanggan, dan pesanan. Staf tidak dapat mengubah item pesanan yang sudah dibuat, mengubah harga final, atau mengubah estimasi selesai.

Staf tidak dapat mengelola akun pengguna atau mengubah katalog/harga default layanan. Halaman layanan tersedia untuk dibaca, tetapi aksi pengelolaan hanya untuk Super Admin. Tidak ada halaman Pengaturan di aplikasi.

## 14. Menu Aplikasi

Menu aplikasi saat ini:

- Dashboard.
- Buat Pesanan.
- Daftar Pesanan.
- Pelanggan.
- Layanan & Harga.
- Laporan (Super Admin).
- Audit Log (Super Admin).
- Manajemen Staf (Super Admin).
- Profil dan Keluar melalui menu akun.

Tidak ada menu Pengaturan atau menu terpisah Pesanan Batal. Pesanan batal dapat dilihat pada tab di Daftar Pesanan; pemulihan dilakukan dari detail pesanan.

Dashboard menampilkan Pesanan Hari Ini, Sedang Diproses, Siap Diambil, Belum Lunas, dan Terlambat. Super Admin juga melihat Pendapatan Hari Ini. Daftar Menunggu Serah Terima dan Belum Lunas tampil di bawah kartu. Nilai pendapatan tidak dirender untuk Staf.

## 15. Pencarian dan Penyaringan

Pada Daftar Pesanan, pesanan aktif dapat dicari atau disaring berdasarkan:

- Nomor pesanan.
- Nama pelanggan.
- Nomor WhatsApp atau nomor telepon.
- Status pengerjaan.
- Status pembayaran.
- Metode pickup atau delivery.
- Tanggal pesanan.

Tersedia tab Daftar Pesanan/Pesanan Batal, chip tanggal Hari Ini/Kemarin/7 Hari, pill status dengan jumlah yang tersedia, dan panel Filter untuk pembayaran, serah terima, metode, serta tanggal tepat.

## 16. Laporan

Halaman Laporan hanya untuk Super Admin dan menyediakan tab Keuangan, Pengiriman & Serah Terima, serta Aktivitas. Periode dapat dipilih melalui preset Hari Ini, 7 Hari, Bulan Ini, Bulan Lalu atau tanggal Dari/Sampai. Tombol preset menandai pilihan yang sesuai dengan rentang aktif. Data laporan mencakup:

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

Endpoint ekspor CSV pesanan, pembayaran, dan pelanggan tetap tersedia untuk Super Admin, tetapi tautan ekspor tidak ditampilkan pada halaman Laporan saat ini.

## 17. Pembatalan dan Pemulihan Pesanan

Pembatalan pesanan wajib memerlukan alasan. Pesanan yang dibatalkan tidak boleh dihapus permanen karena tetap diperlukan untuk laporan dan audit.

- Pesanan yang dibatalkan tetap tersedia dalam daftar dan laporan.
- Alasan pembatalan wajib disimpan.
- Waktu pembatalan dan pengguna yang membatalkan harus dicatat.
- Super Admin dapat memulihkan pesanan yang dibatalkan dari halaman detail.
- Pemulihan pesanan harus dicatat dalam audit log.
- Daftar pesanan batal merupakan tab pada halaman Daftar Pesanan, bukan halaman atau route terpisah.

## 18. Kebutuhan Keamanan dan Operasional

Kebutuhan berikut merupakan bagian penting dari aplikasi produksi:

| Kebutuhan | Aturan |
|---|---|
| Backup database | Backup terjadwal, lokasi backup terpisah, dan prosedur pemulihan yang diuji. |
| Reset password | Pengguna dapat meminta kode OTP 6 digit melalui email; kode disimpan sebagai hash, berlaku 10 menit, maksimal 5 percobaan, dan dibatasi rate limit. Super Admin juga dapat mengatur ulang kata sandi akun dari halaman Manajemen Staf. |
| Timeout sesi | Sesi pengguna berakhir setelah periode tidak aktif yang dikonfigurasi dan pengguna diarahkan untuk login kembali. |
| Logout | Pengguna dapat logout dari semua halaman dan sesi aktif ditutup dengan aman. |
| Login activity | Sistem mencatat login berhasil, login gagal, logout, waktu, pengguna, dan informasi teknis yang diperlukan. |
| Permission protection | Aksi Super Admin-only dilindungi policy atau middleware Laravel, bukan hanya disembunyikan dari menu. |
| Error logging | Error aplikasi dicatat tanpa menampilkan detail sensitif kepada pengguna. |
| Ekspor data | Endpoint CSV pelanggan, pesanan, dan pembayaran dibatasi untuk Super Admin; link unduhan tidak ditampilkan pada halaman Laporan. |
| Data historis | Pesanan, pembayaran, refund, dan audit log tidak dapat dihapus sembarangan. |
| Hosting produksi | Aplikasi memiliki hosting dan domain produksi yang sesuai. |
| HTTPS/SSL | Seluruh koneksi produksi menggunakan HTTPS dengan sertifikat SSL aktif. |
| Pengujian mobile | Aplikasi diuji pada browser mobile dan beberapa perangkat Android umum. |

## 19. Rancangan Teknologi Laravel

Teknologi yang digunakan dalam aplikasi saat ini:

| Komponen | Pilihan |
|---|---|
| Backend | Laravel 13 |
| Tampilan | Blade |
| Interaksi dinamis | Livewire 4 |
| UI dan styling | Tailwind CSS 3.4 |
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
| `login_activities` | Menyimpan aktivitas login berhasil, gagal, dan logout. |

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
- Staf dapat mengelola pelanggan dan menjalankan operasi pesanan; item pesanan tersimpan tidak dapat diedit dari UI. Penyesuaian harga final dan override estimasi selesai hanya untuk Super Admin.
- Super Admin dapat mengelola harga default, layanan, akun Staf, laporan, dan audit log; UI tidak menyediakan edit item pesanan tersimpan.
- Jumlah akun aktif tidak dibatasi; minimal satu Super Admin harus tetap aktif. UI Manajemen Staf membuat akun Staf, bukan akun Super Admin.
- Halaman Pengaturan dan konfigurasi cutoff Kilat tidak ada. Kilat dijanjikan selesai pada hari yang sama.
- Reset password pengguna menggunakan kode OTP email; Super Admin dapat mengatur ulang kata sandi akun dari Manajemen Staf.
- Pesanan batal ditampilkan pada tab Daftar Pesanan, bukan halaman terpisah.
- Laporan dan ekspor dibatasi untuk Super Admin. Endpoint ekspor CSV tetap aktif walaupun link ekspor di halaman Laporan telah dihapus.
- Pembatalan memerlukan alasan, tidak menghapus data, dan dapat dipulihkan oleh Super Admin.
- Pembayaran sebagian, refund, delivery gagal, dan audit aktivitas memiliki riwayat tersendiri.
- Backup, ekspor data, reset password, timeout sesi, login activity, error logging, hosting, domain, dan HTTPS/SSL termasuk kebutuhan teknis penting.
- Aplikasi tidak membutuhkan cetak nota.
- Notifikasi otomatis tidak termasuk dalam ruang lingkup.

## Referensi

Dokumen ini merupakan spesifikasi kebutuhan berdasarkan keputusan dan aturan bisnis yang diberikan oleh pemilik aplikasi. Tidak ada sumber eksternal yang digunakan.
