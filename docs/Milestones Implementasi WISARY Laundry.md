# Milestones Implementasi WISARY Laundry

**Proyek:** WISARY Laundry  
**Platform:** Web app mobile-first  
**Teknologi:** Laravel, Blade, Livewire, Tailwind CSS, dan MySQL/MariaDB  
**Dokumen acuan:** `laundry-spec.md` dan `wisary-laundry-design.md`

## Cara Menggunakan Dokumen Ini

Implementasi dilakukan secara berurutan. Milestone berikutnya dimulai setelah milestone sebelumnya selesai dan kriteria penerimaannya terpenuhi. Setiap milestone sebaiknya dibuat dalam branch atau checkpoint terpisah agar perubahan mudah dilacak dan dikembalikan jika diperlukan.

Status yang digunakan:

- `[ ]` Belum dikerjakan.
- `[~]` Sedang dikerjakan.
- `[x]` Selesai dan sudah diverifikasi.
- `[!]` Tertunda atau membutuhkan keputusan.

## Ringkasan Progres

| Milestone | Status |
|---|---|
| 1 â€” Inisialisasi Project dan Lingkungan Pengembangan | [x] Selesai |
| 2 â€” Layout Mobile-First dan Design System | [x] Selesai |
| 3 â€” Autentikasi dan Hak Akses Pengguna | [x] Selesai |
| 4 â€” Database, Model, dan Migration Inti | [x] Selesai |
| 5 â€” CRUD Pelanggan | [x] Selesai |
| 6 â€” CRUD Layanan dan Harga | [x] Selesai |
| 7 â€” Pembuatan Pesanan dan Nomor Pesanan | [x] Selesai |
| 8 â€” Mesin Perhitungan Harga | [x] Selesai |
| 9 â€” Estimasi Selesai | [x] Selesai |
| 10 â€” Status Laundry dan Riwayat Aktivitas | [x] Selesai |
| 11 â€” Pickup, Delivery, dan Serah Terima | [x] Selesai |
| 12 â€” Pembayaran, Partial Payment, dan Refund | [x] Selesai |
| 13 â€” Pembatalan dan Pemulihan Pesanan | [x] Selesai |
| 14 â€” Daftar Pesanan, Detail, dan Dashboard | [x] Selesai |
| 15 â€” Laporan dan Ekspor Data | [x] Selesai |
| 16 â€” Audit Log, Keamanan, dan Perlindungan Data | [x] Selesai |
| 17 â€” Backup, Reset Password, dan Kesiapan Produksi | [~] Sedang dikerjakan |
| 18 â€” Pengujian Fungsional dan Mobile | [ ] Belum dikerjakan |
| 19 â€” UAT dan Persiapan Launch | [ ] Belum dikerjakan |
| 20 â€” Launch dan Pemeliharaan | [ ] Belum dikerjakan |

---

## Milestone 1 â€” Inisialisasi Project dan Lingkungan Pengembangan

### Tujuan

Menyiapkan project Laravel yang dapat dijalankan secara lokal dengan konfigurasi dasar yang konsisten.

### Pekerjaan

- [x] Membuat project Laravel.
- [x] Mengonfigurasi file `.env` untuk database.
- [x] Menyiapkan MySQL atau MariaDB.
- [x] Menginstal dan mengonfigurasi Blade.
- [x] Menginstal Livewire.
- [x] Menginstal Tailwind CSS.
- [x] Menentukan timezone aplikasi ke `Asia/Jakarta`.
- [x] Menentukan locale Bahasa Indonesia jika diperlukan.
- [x] Menyiapkan format Rupiah di helper atau service khusus.
- [x] Menyiapkan struktur folder dan standar penamaan.
- [x] Menyiapkan Git repository dan branch utama.
- [x] Membuat halaman health check sederhana.

### Kriteria penerimaan

- Project dapat dijalankan secara lokal.
- Halaman Laravel dapat dibuka tanpa error.
- Koneksi database berhasil.
- Livewire dan Tailwind dapat digunakan.
- Waktu aplikasi menggunakan WIB.
- Perubahan project tercatat dalam Git.

---

## Milestone 2 â€” Layout Mobile-First dan Design System

### Tujuan

Membangun kerangka tampilan WISARY Laundry yang nyaman digunakan terutama melalui ponsel.

### Pekerjaan

- [x] Membuat layout utama aplikasi.
- [x] Membuat sidebar desktop.
- [x] Membuat hamburger menu atau drawer untuk mobile.
- [x] Menyiapkan bottom navigation untuk fungsi inti jika diperlukan.
- [x] Menambahkan logo atau wordmark WISARY Laundry.
- [x] Menerapkan warna brand biru WISARY.
- [x] Membuat komponen tombol.
- [x] Membuat komponen input dan select.
- [x] Membuat komponen badge status.
- [x] Membuat komponen card.
- [x] Membuat komponen modal konfirmasi.
- [x] Membuat komponen toast sukses dan error.
- [x] Menetapkan target sentuh minimal 48 Ã— 48 px pada mobile.
- [x] Menetapkan breakpoint mobile, tablet, dan desktop.
- [x] Memastikan layout satu kolom pada mobile.
- [x] Menambahkan focus state dan keyboard navigation dasar.

### Kriteria penerimaan

- Layout terlihat baik pada mobile dan desktop.
- Navigasi mobile dapat dibuka dan ditutup dengan jelas.
- Aksi utama mudah dijangkau dengan tap.
- Warna, tombol, form, badge, dan card konsisten.
- Tidak ada horizontal overflow yang tidak disengaja.
- Kontras teks memenuhi standar aksesibilitas yang ditetapkan.

---

## Milestone 3 â€” Autentikasi dan Hak Akses Pengguna

### Tujuan

Membuat sistem login yang membedakan Super Admin dan Staf.

### Pekerjaan

- [x] Membuat halaman login.
- [x] Membuat logout.
- [x] Membuat middleware autentikasi.
- [x] Menambahkan role `super_admin` dan `staff`.
- [x] Membuat pembatasan maksimal satu Super Admin dan empat Staf aktif.
- [x] Membuat middleware atau policy untuk fitur Super Admin-only.
- [x] Membuat halaman profil pengguna.
- [x] Membuat perubahan password.
- [x] Menyiapkan reset password Super Admin.
- [x] Menambahkan session timeout.
- [x] Mencatat login berhasil, login gagal, dan logout.
- [x] Membuat seed akun awal untuk development.

### Kriteria penerimaan

- Pengguna dapat login dan logout.
- User tanpa autentikasi tidak dapat membuka halaman internal.
- Staf tidak dapat membuka fitur Super Admin-only melalui URL langsung.
- Jumlah akun aktif mematuhi batas satu Super Admin dan empat Staf.
- Session timeout berfungsi.
- Aktivitas login tercatat.

---

## Milestone 4 â€” Database, Model, dan Migration Inti

### Tujuan

Membuat struktur database yang mendukung pelanggan, layanan, pesanan, pembayaran, serah terima, dan audit.

### Pekerjaan

- [x] Membuat migration `users` dan role pengguna.
- [x] Membuat migration `customers`.
- [x] Membuat migration `services`.
- [x] Membuat migration `orders`.
- [x] Membuat migration `order_items`.
- [x] Membuat migration `payments`.
- [x] Membuat migration `refunds`.
- [x] Membuat migration `order_status_histories`.
- [x] Membuat migration `order_handover_histories`.
- [x] Membuat migration `order_price_adjustments`.
- [x] Membuat migration `audit_logs`.
- [x] Menambahkan foreign key dan index pencarian.
- [x] Membuat model dan relasi Eloquent.
- [x] Menambahkan enum atau konstanta untuk status.
- [x] Membuat factory dan seeder untuk data development.

### Kriteria penerimaan

- Semua migration dapat dijalankan dari database kosong.
- Semua migration dapat di-rollback dengan aman.
- Relasi utama dapat digunakan melalui Eloquent.
- Data historis memiliki foreign key atau strategi penyimpanan yang jelas.
- Seeder membuat data awal untuk pengujian.

---

## Milestone 5 â€” CRUD Pelanggan

### Tujuan

Menyediakan pengelolaan data pelanggan dan pencarian pelanggan dengan cepat.

### Pekerjaan

- [x] Membuat daftar pelanggan.
- [x] Membuat form tambah pelanggan.
- [x] Membuat form edit pelanggan.
- [x] Membuat detail pelanggan.
- [x] Menambahkan pencarian nama dan nomor WhatsApp.
- [x] Menambahkan alamat dan catatan pelanggan.
- [x] Menampilkan riwayat pesanan pelanggan.
- [x] Mencegah penghapusan pelanggan yang memiliki riwayat pesanan.
- [x] Menambahkan validasi nomor telepon.
- [x] Menambahkan empty state dan feedback sukses/error.

### Kriteria penerimaan

- Staf dapat menambah dan mengedit pelanggan.
- Pengguna dapat mencari pelanggan melalui ponsel.
- Detail pelanggan menampilkan riwayat pesanan.
- Data pelanggan dengan riwayat tidak hilang karena tindakan hapus.
- Validasi form tampil dekat field yang bermasalah.

---

## Milestone 6 â€” CRUD Layanan dan Harga

### Tujuan

Menyediakan menu layanan yang dapat dikelola Super Admin dan digunakan staf saat membuat pesanan.

### Pekerjaan

- [x] Membuat daftar layanan.
- [x] Membuat form tambah layanan.
- [x] Membuat form edit layanan.
- [x] Membuat tindakan aktifkan/nonaktifkan layanan.
- [x] Menambahkan kategori layanan.
- [x] Menambahkan tipe harga `kg` atau `unit`.
- [x] Menetapkan unit layanan sebagai `Unit` untuk layanan unit.
- [x] Menambahkan harga.
- [x] Menambahkan estimasi selesai.
- [x] Menambahkan opsi same-day.
- [x] Memasukkan seluruh layanan dan harga awal.
- [x] Mencegah penghapusan permanen layanan yang sudah dipakai.
- [x] Membatasi perubahan katalog dan harga default ke Super Admin.

### Kriteria penerimaan

- Semua layanan awal tersedia sesuai spesifikasi.
- Super Admin dapat menambah, mengubah, mengaktifkan, dan menonaktifkan layanan.
- Layanan nonaktif tidak muncul pada pesanan baru.
- Riwayat pesanan tetap menampilkan data layanan lama.
- Layanan unit menggunakan satuan `Unit`.
- Staf tidak dapat mengubah harga default melalui akses langsung.

---

## Milestone 7 â€” Pembuatan Pesanan dan Nomor Pesanan

### Tujuan

Membuat alur utama untuk menerima laundry dan menghasilkan pesanan baru.

### Pekerjaan

- [x] Membuat form **Buat Pesanan** mobile-first.
- [x] Memilih pelanggan yang sudah ada.
- [x] Menambahkan pelanggan baru dari form pesanan.
- [x] Menambahkan satu atau beberapa layanan.
- [x] Memasukkan berat aktual atau jumlah unit.
- [x] Menambahkan catatan pesanan.
- [x] Memilih metode Diambil Pelanggan atau Diantar.
- [x] Menampilkan nomor WhatsApp pelanggan.
- [x] Menyimpan alamat delivery jika metode Diantar.
- [x] Membuat nomor pesanan berdasarkan nomor urut total dan tanggal.
- [x] Menggunakan format seperti `3702972026`.
- [x] Memastikan nomor pesanan unik dan tidak berubah.
- [x] Menyimpan salinan nama layanan, tipe harga, harga, dan aturan harga pada detail pesanan.

### Kriteria penerimaan

- Staf dapat membuat pesanan dari ponsel.
- Satu pesanan dapat memiliki beberapa layanan.
- Pesanan pickup tidak mewajibkan alamat delivery.
- Pesanan delivery mewajibkan alamat delivery.
- Nomor pesanan terbentuk sesuai format yang ditetapkan.
- Data pesanan tidak hilang ketika validasi gagal.

---

## Milestone 8 â€” Mesin Perhitungan Harga

### Tujuan

Menerapkan aturan harga kilogram, unit, minimum empat kilogram, pembulatan, dan penyesuaian final.

### Pekerjaan

- [x] Membuat service atau class kalkulator harga.
- [x] Membulatkan berat aktual ke angka bulat ke atas.
- [x] Menerapkan minimum penagihan empat kilogram.
- [x] Menghitung layanan unit tanpa aturan minimum kilogram.
- [x] Menampilkan berat aktual.
- [x] Menampilkan berat yang ditagihkan.
- [x] Menghitung subtotal tiap detail layanan.
- [x] Menghitung total pesanan.
- [x] Mengizinkan beberapa layanan dalam satu pesanan.
- [x] Mengizinkan Super Admin mengubah harga final transaksi dengan alasan.
- [x] Menyimpan riwayat perubahan harga final.
- [x] Menjaga harga pesanan lama ketika harga default layanan berubah.

> **Ratifikasi 09 September 2026:** Penyesuaian harga final sengaja dibuat sebagai *pencatatan murni* (aplikasi hanya mencatat, bukan menghitung). Harga baru diterima apa adanya; alasan tetap wajib. UI penyesuaian dibangun pada Milestone 14 (halaman detail pesanan).

> **Ratifikasi 29 September 2026:** Item di atas dikoreksi dari "Staf" menjadi "Super Admin". Kode (`OrderPolicy::adjustPrice`), Design System, dan `AuditLogTest` sudah membatasi penyesuaian harga final hanya untuk Super Admin; dua baris Spesifikasi yang sebelumnya memberi hak ini kepada Staf diselaraskan ke implementasi. Rujukan pelanggan, layanan, jumlah/berat, dan status laundry tetap milik Staf.

### Kriteria penerimaan

- Berat 3 kg ditagihkan sebagai 4 kg.
- Berat 4,6 kg ditagihkan sebagai 5 kg.
- Layanan unit dihitung berdasarkan jumlah unit.
- Harga pesanan lama tidak berubah setelah harga default diperbarui.
- Penyesuaian harga final menyimpan nilai lama, nilai baru, alasan, pengguna, dan waktu.
- Total harga yang ditampilkan konsisten dengan detail pesanan.

---

## Milestone 9 â€” Estimasi Selesai

### Tujuan

Menghitung tanggal estimasi berdasarkan hari kalender; Kilat selalu dijanjikan selesai pada hari yang sama.

### Pekerjaan

- [x] Menggunakan timezone WIB dalam seluruh perhitungan.
- [x] Menghitung Reguler sebagai tiga hari kalender.
- [x] Menghitung Express sebagai satu hari kalender.
- [x] Menghitung Kilat sebagai hari yang sama tanpa konsep cutoff.
- [x] Mengizinkan Super Admin mengubah estimasi selesai secara manual — UI di halaman detail pesanan (Milestone 14).

> **Ratifikasi 09 September 2026:** Estimasi pesanan diambil dari tanggal TERAKHIR di antara semua layanan (jika tercampur). Dalam praktik, pesanan campuran dipisah oleh staf.
>
> **Ratifikasi 15 September 2026:** Konsep cutoff layanan Kilat DIHAPUS SELURUHNYA. Kilat dijanjikan selesai pada hari yang sama kapan pun pesanan dibuat. Tidak ada peringatan, tidak ada pemilihan tanggal manual, dan tidak ada halaman Pengaturan — seluruh infrastruktur settings (migrasi `create_settings_table`, model, policy, seeder) dihapus. Aman karena repo belum memiliki commit.

### Kriteria penerimaan

- Semua tanggal dihitung berdasarkan WIB.
- Reguler, Express, dan Kilat menghasilkan tanggal yang benar.
- Pesanan Kilat (kapan pun dibuat) memiliki estimasi hari yang sama.

---

## Milestone 10 â€” Status Laundry dan Riwayat Aktivitas

### Tujuan

Menyediakan alur status pengerjaan tanpa status â€œSelesaiâ€.

### Status pengerjaan

- Diterima.
- Sedang Dicuci.
- Sedang Dikeringkan.
- Sedang Disetrika.
- Sedang Dilipat.
- Siap Diambil.
- Dibatalkan.

### Pekerjaan

- [x] Membuat kontrol perubahan status.
- [x] Mencatat status sebelumnya dan status baru.
- [x] Mencatat waktu perubahan dalam WIB.
- [x] Mencatat pengguna yang mengubah status.
- [x] Menyediakan catatan perubahan status.
- [x] Mencegah alur status yang tidak valid jika aturan bisnis melarangnya.
- [x] Menampilkan status dalam badge berwarna dan label teks.
- [x] Menampilkan timeline riwayat status.

### Kriteria penerimaan

- Tidak ada status pengerjaan â€œSelesaiâ€.
- Status akhir operasional ditentukan melalui status serah terima.
- Riwayat perubahan status dapat dilihat.
- Status dapat diperbarui dengan nyaman melalui mobile.

> **Keputusan (ratifikasi 09 September 2026):** Halaman `/orders` dibangun sebagai **layar Proses** mobile-first (transisi maju dibolehkan melompat, mundur dilarang; entri awal `Diterima` dicatat otomatis saat pesanan dibuat). Layar ini akan digantikan Daftar Pesanan + Detail lengkap pada Milestone 14. Pembatalan pesanan tetap dijadwalkan di Milestone 13 dan tidak termasuk dalam alur M10 ini.

---

## Milestone 11 â€” Pickup, Delivery, dan Serah Terima

### Tujuan

Mencatat proses pengambilan atau pengantaran laundry tanpa biaya delivery.

> **Keputusan metode serah terima (ratifikasi 09 September 2026):** Metode pesanan (`diambil`/`diantar`) di form pembuatan pesanan adalah rencana awal dan dapat berubah. Saat serah terima dicatat dan aksi berbeda dari metode awal (mis. awal `Diambil`, selesai `Diantar`), sistem otomatis menyesuaikan metode mengikuti realita dan mencatat aksi beserta pengubah, waktu, dan catatan di riwayat serah terima. Alamat delivery wajib dilengkapi saat aksi `Sudah Diantar` jika belum ada.

### Pekerjaan

- [x] Membuat status serah terima Belum Diserahkan.
- [x] Membuat status Sudah Diambil.
- [x] Membuat status Sudah Diantar.
- [x] Menyimpan tanggal serah terima.
- [x] Menyimpan waktu serah terima.
- [x] Menyimpan staf yang mencatat serah terima.
- [x] Menyimpan catatan delivery.
- [x] Menyimpan alasan gagal delivery.
- [x] Tidak menambahkan field delivery person.
- [x] Menampilkan pesanan yang belum diserahterimakan pada dashboard.
- [x] Menampilkan aksi handover yang mudah dijangkau pada mobile.

### Kriteria penerimaan

- Pesanan pickup dapat ditandai Sudah Diambil.
- Pesanan delivery dapat ditandai Sudah Diantar.
- Delivery tidak menambahkan biaya.
- Delivery gagal dapat disimpan dengan alasannya.
- Tanggal, waktu, dan staf serah terima tercatat.

> **Keputusan (ratifikasi 09 September 2026):** Aksi serah terima hanya dapat dilakukan saat status pengerjaan **Siap Diambil**. Aksi `gagal` tidak mengubah status serah terima sehingga delivery dapat dicoba kembali. Logika inti di `Order::recordHandover()`; UI handover ditampilkan di kartu layar Proses `/orders` dan di dashboard "Perlu Ditindaklanjuti — Menunggu Serah Terima". Dashboard penuh tetap dibangun di Milestone 14.

---

## Milestone 12 â€” Pembayaran, Partial Payment, dan Refund

### Tujuan

Mengelola pembayaran penuh, pembayaran sebagian, sisa tagihan, dan refund.

### Pekerjaan

- [x] Membuat pencatatan pembayaran.
- [x] Mendukung beberapa pembayaran sebagian.
- [x] Menghitung total pembayaran otomatis.
- [x] Menghitung sisa pembayaran otomatis.
- [x] Menentukan status Belum Lunas, Lunas Sebagian, dan Lunas.
- [x] Mencegah pembayaran melebihi total tagihan.
- [x] Membuat pencatatan refund terpisah.
- [x] Menyimpan alasan refund.
- [x] Menyimpan metode refund.
- [x] Menyimpan pengguna, tanggal, dan waktu refund.
- [x] Menampilkan timeline pembayaran dan refund.

### Kriteria penerimaan

- Pembayaran sebagian dapat dilakukan lebih dari satu kali.
- Sisa pembayaran selalu akurat.
- Pembayaran berlebih ditolak atau diproses melalui alur refund yang sah.
- Refund tidak menghapus riwayat pembayaran awal.
- Semua aktivitas pembayaran memiliki waktu dan pengguna.

> **Keputusan (ratifikasi 09 September 2026):** Anti-pembayaran berlebih (aturan yang ditunda dari Milestone 8) diberlakukan di `Order::recordPayment()` — nominal tidak boleh melebihi sisa tagihan. Refund dibatasi maksimal total yang pernah dibayar dan tidak mengubah/menghapus pembayaran asli; status menjadi `Dikembalikan` saat uang kembali penuh (net bayar ≤ 0). UI pencatatan bayar & refund dibangun di dashboard ("Perlu Ditindaklanjuti — Belum Lunas") bersama timeline; halaman detail pesanan yang menyatukan semua aksi menyusul di Milestone 14.

---

## Milestone 13 â€” Pembatalan dan Pemulihan Pesanan

### Status: Selesai ✔

### Tujuan

Menyediakan pembatalan yang aman tanpa menghilangkan data historis.

### Pekerjaan

- [x] Membuat form pembatalan dengan alasan wajib.
- [x] Menyimpan pengguna dan waktu pembatalan.
- [x] Menyimpan pesanan batal dalam database.
- [x] Mencegah penghapusan permanen pesanan.
- [x] Membuat aksi pemulihan untuk Super Admin.
- [x] Mencatat pemulihan dalam audit log.
- [x] Menentukan status yang digunakan setelah pesanan dipulihkan.
- [x] Menampilkan pesanan batal pada laporan. <sup>ditunda ke Milestone 15</sup>

### Kriteria penerimaan

- Pesanan tidak dapat dibatalkan tanpa alasan.
- Pesanan batal tetap dapat dicari dan dilaporkan.
- Staf tidak dapat menghapus pesanan secara permanen.
- Super Admin dapat memulihkan pesanan batal.
- Pemulihan tidak menghilangkan riwayat pembatalan.

> **Keputusan (ratifikasi 09 September 2026):** Pesanan yang dipulihkan kembali ke status **Diterima** (memulai ulang proses; riwayat lama tetap tersimpan). Pembatalan **diblokir setelah serah terima** (Sudah Diambil/Sudah Diantar). Daftar pesanan batal + pemulihan tersedia di halaman admin `/orders/cancelled` (nav "Pesanan Batal", hanya Super Admin, pencarian per no. pesanan/pelanggan); item "Menampilkan pesanan batal pada laporan" dijadwalkan ke Milestone 15 seiring laporan resmi. Pembatalan dan pemulihan dicatat di status history maupun audit log (`order.cancelled` / `order.restored`).

---

## Milestone 14 â€” Daftar Pesanan, Detail, dan Dashboard

### Tujuan

Menyediakan tampilan operasional untuk memantau seluruh aktivitas laundry.

### Pekerjaan

- [x] Membuat dashboard KPI.
- [x] Menampilkan pesanan hari ini.
- [x] Menampilkan pesanan yang sedang diproses
- [x] Menampilkan pesanan siap diambil.
- [x] Menampilkan pesanan delivery.
- [x] Menampilkan pesanan belum lunas.
- [x] Menampilkan pesanan terlambat.
- [x] Menampilkan pesanan belum diserahterimakan.
- [x] Membuat daftar pesanan dengan pencarian (nomor, nama, nomor WhatsApp).
- [x] Menambahkan filter status, pembayaran, metode serah terima, dan tanggal.
- [x] Membuat halaman detail pesanan.
- [x] UI penyesuaian harga final (warisan Milestone 8; alasan wajib, catat riwayat).
- [x] UI mengubah estimasi selesai manual oleh Super Admin (warisan Milestone 9).
- [x] Pemilihan tanggal manual untuk Kilat — dibatalkan 15 Sep 2026 bersama konsep cutoff (warisan Milestone 9).
- [x] Membuat timeline status, pembayaran, refund, dan handover.
- [x] Membuat tampilan kartu untuk mobile.
- [x] Membuat tabel responsif untuk desktop.

### Kriteria penerimaan

- Staf dapat menemukan pesanan berdasarkan nomor, nama, atau nomor WhatsApp.
- Dashboard menampilkan KPI utama di bagian atas.
- Daftar pesanan dapat digunakan pada layar ponsel.
- Detail pesanan menampilkan semua informasi penting tanpa membingungkan pengguna.
- Filter tidak menghilangkan data secara permanen.

---

## Milestone 15 â€” Laporan dan Ekspor Data

### Tujuan

Menyediakan laporan operasional dan ekspor data untuk kebutuhan owner.

### Pekerjaan

- [x] Membuat laporan jumlah pesanan berdasarkan periode.
- [x] Membuat laporan pendapatan harian, mingguan, dan bulanan.
- [x] Membuat laporan layanan paling sering digunakan.
- [x] Membuat laporan pesanan belum lunas.
- [x] Membuat laporan pickup dan delivery.
- [x] Membuat laporan pesanan yang sudah diserahterimakan.
- [x] Membuat laporan pesanan dibatalkan.
- [x] Membuat laporan delivery gagal.
- [x] Membuat laporan refund.
- [x] Membuat laporan aktivitas staf.
- [x] Menambahkan ekspor pelanggan.
- [x] Menambahkan ekspor pesanan.
- [x] Menambahkan ekspor pembayaran.
- [x] Membatasi ekspor berdasarkan hak akses.

### Kriteria penerimaan

- Laporan dapat difilter berdasarkan periode.
- Nilai laporan konsisten dengan data transaksi.
- Data dapat diekspor dalam format yang dapat dibuka kembali.
- Ekspor tidak membocorkan data kepada pengguna yang tidak berwenang.

---

## Milestone 16 â€” Audit Log, Keamanan, dan Perlindungan Data

### Tujuan

Melindungi data operasional dan menyediakan jejak perubahan yang dapat ditelusuri.

### Pekerjaan

- [x] Mencatat perubahan pelanggan penting.
- [x] Mencatat perubahan layanan dan harga.
- [x] Mencatat perubahan pesanan.
- [x] Mencatat penyesuaian harga final.
- [x] Mencatat perubahan status.
- [x] Mencatat pembayaran dan refund.
- [x] Mencatat pembatalan dan pemulihan.
- [x] Menambahkan policy dan middleware Laravel.
- [x] Mencegah akses langsung ke fitur Super Admin-only.
- [x] Mencegah penghapusan data historis.
- [x] Menambahkan error logging.
- [x] Menyamarkan informasi sensitif pada pesan error.
- [x] Menambahkan rate limiting login.
- [x] Menambahkan validasi server-side pada semua form.

### Kriteria penerimaan

- Semua tindakan penting memiliki pengguna dan waktu.
- Staf tidak dapat melewati pembatasan melalui URL atau request manual.
- Data historis tidak dapat dihapus sembarangan.
- Error teknis tidak menampilkan informasi sensitif.
- Audit log dapat difilter dan dibaca oleh Super Admin.

### Status: Selesai

Halaman `/audit-logs` menggabungkan riwayat pesanan (status, serah terima,
pembayaran, refund, penyesuaian harga), jejak audit (pelanggan, layanan, akun,
ubah estimasi), dan aktivitas login/keluar/gagal dalam satu alur yang dapat
difilter (periode, pengguna, jenis aksi). Pengamanan ditambahkan melalui policy
Laravel (Order, Service, Customer, User) di atas middleware `super.admin`,
rate limit login (5 percobaan), konteks error berisi `user_id`/`ip`/`url`, dan
halaman error khusus 403/404/419/500 tanpa detail teknis.

---

## Milestone 17 â€” Backup, Reset Password, dan Kesiapan Produksi

### Tujuan

Menyiapkan aplikasi agar aman untuk digunakan dalam operasional nyata.

### Pekerjaan

- [ ] Menentukan jadwal backup database.
- [x] Membuat halaman Pengaturan lalu menghapusnya 15 Sep 2026 — tidak ada konten tersisa; infrastruktur settings dibatalkan bersama konsep cutoff.
- [x] Editor cutoff Kilat di Pengaturan — dibatalkan 15 Sep 2026 (konsep cutoff dihapus).
- [ ] Menyimpan backup di lokasi terpisah dari server utama.
- [ ] Menguji proses restore backup.
- [x] Membuat alur reset password Super Admin (Breeze forgot-password via email + `StaffPasswordResetController` untuk akun staf dari sisi admin).
- [x] Menentukan durasi session timeout (config `app.idle_timeout` = 30 menit + middleware `EnsureSessionIsActive` + `SessionTimeoutTest`).
- [x] Memastikan logout menutup sesi dengan aman (logout dicatat ke `login_activities`).
- [x] Menyiapkan login activity (login/gagal/logout tercatat; digabung di `/audit-logs` dan laporan aktivitas).
- [ ] Menyiapkan error logging produksi.
- [ ] Menyiapkan hosting produksi.
- [ ] Menyiapkan domain.
- [ ] Mengaktifkan HTTPS/SSL.
- [ ] Mengonfigurasi environment produksi tanpa membocorkan secret.
- [ ] Menyiapkan queue atau scheduler jika dibutuhkan.
- [ ] Menyiapkan monitoring uptime dasar.

### Kriteria penerimaan

- Backup dapat dibuat dan dipulihkan.
- Reset password dapat digunakan dengan aman.
- Aplikasi berjalan melalui HTTPS.
- Environment produksi tidak menggunakan debug mode.
- Error dapat ditelusuri dari log tanpa ditampilkan kepada pelanggan.
- Domain produksi dapat membuka aplikasi dengan stabil.

---

## Milestone 18 â€” Pengujian Fungsional dan Mobile

### Tujuan

Memastikan seluruh alur bisnis berjalan benar pada desktop dan terutama perangkat mobile.

> **Pembaruan 15 September 2026:** Item test otomatis di bawah ditandai selesai karena seluruhnya sudah memiliki file test yang lolos (203 test / 694 asersi). **Pembaruan 30 September 2026:** jumlah test sekarang 214 (753 asersi) — angka 203/694 di atas hanya sejarah; aturan mainnya adalah "seluruh file test lolos". Sisa item adalah pengujian manual perangkat.

### Pekerjaan

- [x] Menulis feature test untuk autentikasi (`AuthTest`, `tests/Feature/Auth/*`).
- [x] Menulis test hak akses Super Admin dan Staf (`AuthTest`).
- [x] Menulis test perhitungan kilogram (`PricingCalculatorTest`).
- [x] Menulis test minimum empat kilogram (`PricingCalculatorTest`).
- [x] Menulis test pembulatan 4,6 kg menjadi 5 kg (`PricingCalculatorTest`).
- [x] Menulis test layanan unit (`PricingCalculatorTest`).
- [x] Menulis test format nomor pesanan (`OrderCreationTest`).
- [x] Menulis test WIB dan estimasi kalender (`EstimateCalculatorTest`).
- [x] Menulis test estimasi Kilat hari yang sama (konsep cutoff dihapus 15 Sep 2026).
- [x] Menulis test status laundry (`StatusTransitionTest`, `StatusFlowTest`).
- [x] Menulis test pickup dan delivery (`OrderHandoverTest`).
- [x] Menulis test delivery gagal (`OrderHandoverTest`).
- [x] Menulis test partial payment dan refund (`PaymentRefundTest`).
- [x] Menulis test pembatalan dan pemulihan (`OrderCancellationTest`).
- [x] Menulis test audit log (`AuditLogTest`).
- [ ] Menguji Android dengan layar kecil.
- [ ] Menguji Android dengan layar besar.
- [ ] Menguji tablet.
- [ ] Menguji desktop.
- [ ] Menguji keyboard navigation.
- [ ] Menguji validasi dan error recovery.
- [ ] Menguji kontras dan label aksesibilitas.

### Kriteria penerimaan

- Test otomatis utama berhasil.
- Alur pembuatan pesanan dapat selesai melalui ponsel.
- Tidak ada tombol penting yang terlalu kecil untuk disentuh.
- Tidak ada layout rusak pada ukuran layar yang didukung.
- Validasi tampil jelas dan data form tidak hilang ketika terjadi error.
- Owner atau perwakilan staf menyetujui alur utama.

---

## Milestone 19 â€” UAT dan Persiapan Launch

### Tujuan

Memvalidasi aplikasi menggunakan skenario operasional nyata sebelum digunakan penuh.

### Pekerjaan

- [ ] Menyiapkan data uji yang menyerupai kondisi toko.
- [ ] Menjalankan skenario pickup.
- [ ] Menjalankan skenario delivery berhasil.
- [ ] Menjalankan skenario delivery gagal.
- [ ] Menjalankan skenario layanan Kilat (janji selesai hari yang sama).
- [ ] Menjalankan skenario berat di bawah 4 kg.
- [ ] Menjalankan skenario berat desimal.
- [ ] Menjalankan skenario beberapa layanan dalam satu pesanan.
- [ ] Menjalankan skenario pembayaran sebagian.
- [ ] Menjalankan skenario refund.
- [ ] Menjalankan skenario pembatalan dan pemulihan.
- [ ] Memeriksa laporan dan ekspor data.
- [ ] Memeriksa role Super Admin dan Staf.
- [ ] Mencatat bug dan keputusan perbaikan.
- [ ] Melakukan final backup sebelum launch.
- [ ] Menyiapkan panduan singkat untuk staf.

### Kriteria penerimaan

- Skenario operasional utama disetujui.
- Tidak ada bug kritis atau blocker.
- Data awal layanan sudah benar.
- Akun pengguna sudah disiapkan.
- Backup sebelum launch berhasil.
- Staf memahami cara membuat pesanan, mengubah status, mencatat pembayaran, dan melakukan handover.

---

## Milestone 20 â€” Launch dan Pemeliharaan

### Tujuan

Menjalankan WISARY Laundry di lingkungan produksi dan menjaga kestabilannya.

### Pekerjaan

- [ ] Membuka aplikasi untuk penggunaan operasional.
- [ ] Memantau error log dan uptime.
- [ ] Memeriksa backup terjadwal.
- [ ] Mengumpulkan feedback dari staf.
- [ ] Mencatat kebutuhan perubahan baru.
- [ ] Memperbaiki bug berdasarkan prioritas.
- [ ] Meninjau laporan secara berkala.
- [ ] Menguji proses restore backup secara berkala.
- [ ] Meninjau akun staf aktif.
- [ ] Menjaga dependency Laravel tetap diperbarui secara aman.

### Kriteria penerimaan

- Aplikasi dapat digunakan untuk transaksi nyata.
- Backup berjalan sesuai jadwal.
- Error kritis memiliki proses penanganan.
- Feedback staf terdokumentasi.
- Perubahan setelah launch tidak menghilangkan data historis.

---

## Checklist Go-Live Minimum

Sebelum aplikasi digunakan untuk transaksi nyata, semua item berikut harus selesai:

- [x] Login dan role sudah diuji.
- [x] Data layanan dan harga sudah benar.
- [x] Perhitungan minimum 4 kg sudah diuji.
- [x] Pembulatan berat ke atas sudah diuji.
- [x] Format nomor pesanan sudah diuji.
- [x] WIB dan format Rupiah sudah benar.
- [x] Estimasi Kilat hari yang sama sudah diuji.
- [x] Pickup dan delivery sudah diuji.
- [x] Delivery gagal dapat dicatat.
- [x] Status serah terima sudah diuji.
- [x] Partial payment dan refund sudah diuji.
- [x] Pembatalan dan pemulihan sudah diuji.
- [x] Audit log aktif.
- [x] Ekspor data berjalan.
- [ ] Backup dan restore sudah diuji.
- [x] Reset password tersedia.
- [x] Session timeout dan logout berjalan.
- [ ] HTTPS/SSL aktif.
- [ ] Pengujian mobile Android selesai.
- [ ] Tidak ada blocker yang terbuka.

## Referensi

Dokumen ini merupakan roadmap implementasi berdasarkan spesifikasi kebutuhan dan design system WISARY Laundry. Tidak ada sumber eksternal yang digunakan.
