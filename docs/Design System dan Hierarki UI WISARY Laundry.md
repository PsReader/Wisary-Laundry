# Design System dan Hierarki UI WISARY Laundry

**Status:** Peta UI yang sudah diselaraskan dengan aplikasi per 8 November 2026. Prinsip visual tetap menjadi panduan; bagian aksesibilitas dan QA perangkat yang belum diuji manual adalah target, bukan klaim bahwa seluruh kriteria telah lulus.

## 1. Identitas Produk

**Nama aplikasi:** WISARY Laundry  
**Jenis produk:** Web app operasional laundry  
**Bahasa antarmuka:** Bahasa Indonesia  
**Target pengguna:** Super Admin dan Staf laundry  
**Karakter brand:** Bersih, tepercaya, cepat, rapi, dan mudah digunakan.

> **Arah visual:** WISARY Laundry menggunakan warna biru sebagai identitas utama untuk menyampaikan rasa tepercaya dan profesional, dengan aksen cyan sebagai simbol kebersihan dan kecepatan layanan.

## 2. Tujuan Desain

Desain harus membantu staf menyelesaikan pekerjaan utama dengan cepat dan minim kesalahan. Informasi pesanan, status proses, pembayaran, dan serah terima harus dapat dipahami dalam sekali lihat.

Prioritas pengalaman pengguna adalah:

1. Menemukan pesanan dengan cepat.
2. Membuat pesanan tanpa langkah yang membingungkan.
3. Mengetahui status pekerjaan secara jelas.
4. Melihat pesanan siap diambil atau diantar.
5. Mencegah kesalahan berat, harga, pembayaran, dan status.
6. Memberikan umpan balik langsung setelah setiap tindakan.

## 3. Hierarki Informasi

### Tingkat 1: Informasi kritis

Informasi berikut harus paling menonjol pada halaman terkait:

- Nomor pesanan.
- Nama dan nomor WhatsApp pelanggan.
- Total harga.
- Status pesanan.
- Status pembayaran.
- Metode serah terima.
- Berat aktual dan berat yang ditagihkan.
- Pesanan siap diambil atau sudah diantar.

### Tingkat 2: Informasi operasional

Informasi berikut mendukung proses kerja:

- Layanan yang dipilih.
- Estimasi selesai.
- Alamat delivery.
- Catatan pesanan.
- Staf pembuat pesanan.
- Riwayat perubahan status.
- Riwayat pembayaran.

### Tingkat 3: Informasi administratif

Informasi berikut tersedia ketika dibutuhkan, tetapi tidak mendominasi layar:

- Waktu pembuatan dan perubahan.
- Aktivitas staf dan audit log.
- Pengelolaan layanan dan akun.
- Data akun pengguna.
- Laporan untuk Super Admin.

## 4. Skema Warna Brand

### 4.1 Warna utama

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `primary-900` | Navy Wisary | `#0B1F3A` | Teks utama pada area gelap, footer, heading kuat |
| `primary-800` | Deep Blue | `#123B68` | Sidebar, header, elemen brand |
| `primary-700` | Wisary Blue | `#1559A6` | Tombol utama, link, fokus navigasi |
| `primary-600` | Clean Blue | `#1D70C9` | Hover, highlight, progress, grafik |
| `primary-500` | Sky Blue | `#2E8AE6` | Aksen sekunder dan elemen interaktif ringan |
| `primary-100` | Pale Blue | `#DCEEFF` | Background kartu informasi dan selected state |
| `primary-50` | Ice Blue | `#F1F8FF` | Background halaman dan area kosong |

### 4.2 Warna fungsional

| Token | Hex | Penggunaan |
|---|---|---|
| `success-700` | `#18794E` | Berhasil, lunas, selesai |
| `success-100` | `#DDF7E8` | Background status berhasil |
| `warning-700` | `#A15C00` | Perhatian, pembayaran sebagian, mendekati jatuh tempo |
| `warning-100` | `#FFF0C2` | Background peringatan |
| `danger-700` | `#B42318` | Error, batal, data berisiko |
| `danger-100` | `#FEE4E2` | Background error atau status batal |
| `info-700` | `#0B6E99` | Informasi umum dan delivery |
| `info-100` | `#DFF4FF` | Background informasi |

### 4.3 Warna netral

| Token | Hex | Penggunaan |
|---|---|---|
| `neutral-950` | `#17212B` | Teks utama |
| `neutral-700` | `#475467` | Teks sekunder |
| `neutral-500` | `#667085` | Placeholder dan metadata |
| `neutral-300` | `#D0D5DD` | Border dan divider |
| `neutral-100` | `#F2F4F7` | Background input atau tabel |
| `white` | `#FFFFFF` | Kartu, modal, dan permukaan utama |

### 4.4 Aturan kontras

Teks utama harus menggunakan `neutral-950` atau `primary-900` pada background terang. Teks putih hanya digunakan pada background biru yang cukup gelap. Warna status tidak boleh menjadi satu-satunya cara untuk menyampaikan informasi; setiap status juga harus memiliki label teks.

## 5. Tipografi

Gunakan keluarga font sans-serif **Inter**, sesuai konfigurasi Tailwind aplikasi.

| Gaya | Ukuran desktop | Berat | Penggunaan |
|---|---:|---:|---|
| Display | 32 px | 700 | Judul halaman utama |
| Heading 1 | 24 px | 700 | Judul bagian |
| Heading 2 | 20 px | 700 | Judul kartu atau panel |
| Heading 3 | 16 px | 600 | Label bagian |
| Body | 14–16 px | 400 | Isi dan deskripsi |
| Label | 12–14 px | 600 | Label input dan badge |
| Caption | 12 px | 400 | Metadata dan bantuan |

Gunakan kalimat dan label yang singkat. Contoh: **Buat Pesanan**, **Simpan Pesanan**, **Tandai Sudah Diantar**.

## 6. Strategi Mobile-First

WISARY Laundry biasanya digunakan melalui ponsel oleh staf saat menerima, memproses, dan menyerahkan laundry. Karena itu, desain dimulai dari mobile web sebagai konteks utama, kemudian diperluas untuk tablet dan desktop. Semua fungsi penting harus tetap dapat digunakan tanpa membutuhkan layar besar.

| Fitur | Mobile web | Desktop web |
|---|---|---|
| Navigasi | Drawer akun dan bottom navigation lima item | Sidebar navigation |
| Interaksi | Tap, swipe, scroll, dan input sentuh | Click, hover, keyboard, dan scroll |
| Target interaksi | Minimal 48 × 48 px | Target umum juga dijaga minimal 48 px pada kontrol aplikasi |
| Struktur konten | Satu kolom vertikal | Multi-kolom sesuai kebutuhan |
| Daftar data | Kartu ringkas atau tabel yang dapat digeser | Tabel multi-kolom |
| Aksi utama | Tombol lebar dan mudah dijangkau ibu jari | Tombol mengikuti hierarki desktop |

### Prinsip mobile utama

- Gunakan satu kolom vertikal sebagai default.
- Letakkan aksi utama di area yang mudah dijangkau ibu jari.
- Gunakan tombol minimal 48 × 48 px untuk target sentuh.
- Hindari hover sebagai satu-satunya cara menemukan informasi.
- Jangan mengandalkan pinch atau gestur kompleks untuk fungsi penting.
- Gunakan sticky action bar pada form panjang jika tombol simpan tidak selalu terlihat.

## 7. Tata Letak dan Grid

### Desktop

- Sidebar tetap di sisi kiri dengan lebar 248 px.
- Area konten menggunakan lebar fleksibel.
- Header konten memiliki judul halaman, breadcrumb, dan aksi utama.
- Dashboard menggunakan grid dua kolom pada layar kecil dan empat kolom mulai breakpoint `lg`.
- Kartu menggunakan padding responsif, 16 px pada layar kecil dan 24 px mulai `sm`.

### Tablet

- Sebelum breakpoint desktop (`lg`), navigasi tetap menggunakan drawer dan bottom navigation.
- Grid halaman mengikuti layout responsif masing-masing fitur.
- Tabel panjang dapat digeser secara horizontal.

### Mobile

- Sidebar berubah menjadi drawer atau bottom navigation.
- Grid berubah menjadi satu kolom.
- Aksi utama menggunakan tombol lebar penuh jika diperlukan.
- Tabel berubah menjadi kartu ringkas atau menyediakan scroll horizontal.
- Form pesanan tetap satu halaman dengan bagian pelanggan, layanan, dan serah terima.
- Bottom navigation berisi Dashboard, Buat Pesanan, Daftar Pesanan, Pelanggan, dan Layanan & Harga; drawer menyediakan navigasi dan akun.
- Semua elemen interaktif memiliki target sentuh minimal 48 × 48 px.
- Konten penting tidak boleh tersembunyi hanya karena pengguna tidak mengarahkan kursor.

Breakpoints yang disarankan:

| Nama | Lebar minimum |
|---|---:|
| Mobile | 0 px |
| Tablet | 640 px |
| Desktop | 1024 px |
| Wide desktop | 1280 px |

## 8. Struktur Navigasi

Menu utama disusun berdasarkan frekuensi penggunaan:

1. **Dashboard**
2. **Buat Pesanan**
3. **Daftar Pesanan**
4. **Pelanggan**
5. **Layanan & Harga**
6. **Laporan** (Super Admin)
7. **Audit Log** (Super Admin)
8. **Manajemen Staf** (Super Admin)
9. **Profil / Keluar** melalui menu akun

Tidak ada halaman Pengaturan atau menu Pesanan Batal tersendiri. Pesanan batal merupakan tab di Daftar Pesanan. Menu aktif menggunakan penanda visual; akses Super Admin juga diperiksa di server.

## 9. Halaman Utama

### 9.1 Dashboard

Dasbor harus memprioritaskan kondisi operasional hari ini. Metrik utama ditempatkan paling atas dalam bentuk KPI cards sehingga staf dapat memahami kondisi toko sebelum membuka detail.

Komponen yang ada saat ini:

1. Judul **Dashboard** dan tombol **Buat Pesanan**.
2. Kartu Pesanan Hari Ini, Sedang Diproses, Siap Diambil, Belum Lunas, dan Terlambat.
3. Kartu Pendapatan Hari Ini hanya untuk Super Admin.
4. Daftar Menunggu Serah Terima dan Belum Lunas.

Kartu status menggunakan angka, label, ikon/tone, dan teks indikator. Grafik tidak ditampilkan saat ini.

### 9.2 Buat Pesanan

Form menggunakan satu kolom sebagai default, terutama pada mobile. Label selalu berada di atas input dan tidak digantikan oleh placeholder. Pada layar yang lebih besar, bagian yang tidak saling bergantung dapat ditempatkan berdampingan tanpa mengorbankan urutan pengisian.

Form yang tersedia saat ini:

1. **Pelanggan**: cari pelanggan atau tambah pelanggan baru.
2. **Layanan**: cari dan pilih satu atau beberapa layanan kilogram/unit.
3. **Berat dan jumlah**: masukkan berat aktual atau jumlah unit; setiap baris dapat diberi catatan.
4. **Serah terima**: pilih Diambil Pelanggan atau Diantar dan isi alamat jika diperlukan.
5. **Catatan pesanan**.
6. **Ringkasan**: tampilkan estimasi selesai dan total.
7. **Simpan Pesanan**.

Pembayaran dicatat setelah pesanan dibuat, dari Dashboard atau detail pesanan; pemilihan pembayaran bukan bagian dari form pembuatan. Ringkasan harga dibuat sticky pada layar panjang. Validasi tetap dijalankan di server saat submit, dan validasi field tertentu berlangsung saat blur.

### 9.3 Daftar Pesanan

Daftar pesanan memiliki tab **Daftar Pesanan** dan **Pesanan Batal**. Untuk daftar aktif tersedia:

- Pencarian nomor pesanan, nama pelanggan, atau nomor WhatsApp.
- Pill status dengan jumlah yang mengikuti filter aktif; status tanpa hasil disembunyikan.
- Chip tanggal Hari Ini, Kemarin, dan 7 Hari.
- Panel Filter untuk tanggal tepat, status pembayaran, serah terima, dan metode.
- Pagination.

Di desktop, data ditampilkan sebagai tabel; pada layar kecil daftar memakai kartu. Tabel desktop saat ini mencakup:

| Kolom | Keterangan |
|---|---|
| Nomor | Nomor pesanan; dapat diurutkan |
| Pelanggan | Nama dan nomor WhatsApp |
| Status | Status pengerjaan |
| Pembayaran | Status pembayaran |
| Total | Total tagihan |
| Tanggal | Tanggal diterima; dapat diurutkan |
| Metode | Diambil pelanggan atau diantar |

Layanan dan estimasi tidak menjadi kolom pada daftar saat ini; buka detail pesanan untuk informasi lengkap. Baris tabel dan kartu mobile membuka detail pesanan.

### 9.4 Detail Pesanan

Bagian paling atas menampilkan nomor pesanan, nama pelanggan, total, status pengerjaan, dan status pembayaran.

Bagian berikutnya menampilkan detail layanan, berat aktual, berat yang ditagihkan, estimasi selesai, metode serah terima, alamat delivery, serta catatan.

Riwayat status dan pembayaran ditampilkan dalam bentuk timeline agar urutan aktivitas mudah dipahami.

### 9.5 Pelanggan

Halaman pelanggan menggunakan tabel dengan pencarian cepat. Detail pelanggan menampilkan kontak utama, alamat, catatan, total pesanan, dan riwayat pesanan.

### 9.6 Layanan & Harga

Halaman ini menyediakan pencarian dan tabel nama layanan, kategori, tipe harga, harga, estimasi selesai, serta status aktif. Semua pengguna terautentikasi dapat melihatnya; Staf mendapat tampilan baca saja.

Tambah, edit, aktif/nonaktif hanya tersedia untuk Super Admin. Layanan yang sudah digunakan tidak dihapus permanen; tindakan **Nonaktifkan** menjaga riwayat pesanan lama.

### 9.7 Laporan

Laporan hanya tersedia untuk Super Admin. Filter periode menyediakan Hari Ini, 7 Hari, Bulan Ini, Bulan Lalu, serta tanggal Dari/Sampai; preset yang rentangnya cocok ditandai aktif. Konten dibagi ke tab **Keuangan**, **Pengiriman & Serah Terima**, dan **Aktivitas**. Link ekspor CSV tidak ditampilkan pada halaman, walaupun endpoint ekspor tetap tersedia untuk Super Admin.

## 10. Komponen UI

### Tombol

- Tombol utama: background `primary-700`, teks putih.
- Tombol sekunder: background putih, border `neutral-300`.
- Tombol destruktif: background `danger-700`, digunakan hanya untuk tindakan berisiko.
- Tombol teks: digunakan untuk aksi sekunder.
- Setiap tombol memiliki keadaan default, hover, focus, disabled, dan loading.

### Badge status

Status harus menggunakan kombinasi warna dan teks.

| Status | Gaya |
|---|---|
| Diterima | Biru muda |
| Sedang diproses | Cyan atau biru |
| Siap Diambil | Hijau |
| Belum Lunas | Kuning |
| Lunas | Hijau |
| Dibatalkan | Merah |
| Sudah Diantar | Ungu-biru atau cyan |
| Sudah Diambil | Hijau tua |

### Form

- Label selalu terlihat di atas input.
- Placeholder tidak menggantikan label.
- Field wajib diberi indikator yang jelas.
- Validasi ditampilkan dekat field yang bermasalah.
- Nilai harga menggunakan format Rupiah, misalnya `Rp20.000`.
- Input berat menampilkan bantuan bahwa sistem membulatkan berat ke atas dan menerapkan minimum 4 kg.

### Modal dan konfirmasi

Gunakan modal untuk konfirmasi tindakan yang berisiko, seperti membatalkan pesanan, menonaktifkan layanan, atau menyesuaikan harga final. Teks modal harus menjelaskan dampak tindakan dan menyediakan tombol batal yang mudah ditemukan.

## 11. Feedback dan System Status

Setiap tindakan pengguna harus menghasilkan feedback yang jelas.

| Kondisi | Feedback |
|---|---|
| Data berhasil disimpan | Toast sukses di kanan atas atau area yang terlihat |
| Data gagal disimpan | Alert error dengan penyebab dan langkah perbaikan |
| Proses sedang berjalan | Tombol submit dinonaktifkan selama request; spinner tidak digunakan secara konsisten |
| Status berhasil diubah | Toast dan pembaruan badge secara langsung |
| Sesi berakhir karena tidak aktif | Pengguna harus login kembali |
| Data kosong | Empty state dengan penjelasan dan aksi berikutnya |
| Hasil pencarian kosong | Pesan pencarian tidak ditemukan dan opsi menghapus filter |

Pesan feedback harus menggunakan Bahasa Indonesia yang langsung dan tidak menyalahkan pengguna.

Contoh:

> **Pesanan berhasil dibuat.** Nomor pesanan: WL-20260907-001.

> **Berat aktual kurang dari 4 kg.** Pesanan tetap diterima dan akan dihitung menggunakan minimum 4 kg.

> **Harga final berhasil diubah.** Alasan perubahan wajib tersimpan dalam riwayat.

## 12. Responsiveness

Desain harus dapat digunakan pada layar mobile, tablet, dan desktop tanpa kehilangan fungsi utama.

- Aksi utama selalu mudah dijangkau.
- Form tidak boleh melebihi lebar layar.
- Tabel panjang menyediakan scroll horizontal atau tampilan kartu.
- Sidebar dapat berubah menjadi drawer.
- Ringkasan harga tetap terlihat saat pembuatan pesanan.
- Target sentuh minimal 48 × 48 px pada perangkat mobile.
- Ukuran teks tidak boleh terlalu kecil untuk menghemat ruang.
- Konten harus dapat digunakan dengan tap dan scroll tanpa bergantung pada hover.
- Bottom navigation atau hamburger menu harus tetap menyediakan akses ke fungsi inti.

## 13. Konsistensi dan Standar

Gunakan pola yang sama di seluruh aplikasi:

- Posisi tombol simpan konsisten.
- Label status konsisten.
- Format tanggal konsisten, misalnya `7 September 2026`.
- Format waktu konsisten, misalnya `09.30 WIB`.
- Format uang konsisten, misalnya `Rp40.000`.
- Ikon selalu disertai tooltip atau label jika maknanya tidak umum.
- Aksi hapus, batalkan, dan nonaktifkan tidak boleh menggunakan ikon yang membingungkan.

## 14. Accessibility

Aplikasi menerapkan sebagian aksesibilitas dasar. Daftar berikut menjadi target yang harus diuji sebelum menyatakan QA aksesibilitas selesai:

- Gunakan elemen HTML semantik seperti `nav`, `main`, `header`, `form`, dan `button`.
- Semua input memiliki label yang terhubung secara teknis.
- Semua fungsi utama dapat digunakan dengan keyboard.
- Fokus keyboard terlihat jelas.
- Teks normal memiliki rasio kontras minimal 4,5:1 terhadap latar belakang.
- Teks besar memiliki rasio kontras minimal 3:1 terhadap latar belakang.
- Status tidak hanya dibedakan melalui warna; gunakan label, ikon, bentuk, atau teks tambahan.
- Pesan error menggunakan teks yang jelas dan dapat dilengkapi ikon seperti tanda silang atau simbol peringatan.
- Modal dapat ditutup dengan tombol Escape dan mengembalikan fokus ke elemen pemanggil.
- Pesan error dapat dibaca screen reader.
- Ikon dekoratif diberi atribut yang sesuai agar tidak dibaca secara keliru.
- Jangan menggunakan teks abu-abu terlalu terang untuk informasi penting.
- Pastikan kombinasi warna tetap dapat dibedakan oleh pengguna dengan buta warna.
- Validasi aksesibilitas harus dilakukan pada mobile dan desktop.

## 15. Error Prevention dan Recovery

Form harus mencegah kesalahan sebelum data disimpan.

- Validasi berat harus menerima angka yang valid.
- Sistem menjelaskan perbedaan berat aktual dan berat tagihan.
- Tipe layanan menentukan apakah input menggunakan kg atau unit.
- Harga final manual hanya tersedia untuk Super Admin.
- Alasan wajib diisi saat harga final diubah.
- Delivery mewajibkan alamat pengiriman.
- Pembayaran sebagian tidak boleh melebihi total tagihan kecuali ada aturan khusus.
- Pesanan yang dibatalkan memerlukan konfirmasi.
- Layanan yang sudah digunakan tidak dihapus permanen.
- Setelah penyimpanan gagal, data form tidak boleh hilang.
- Tampilkan validasi real-time setelah pengguna selesai mengisi field.
- Sediakan pesan sukses atau error yang terlihat tanpa perlu menebak hasil tindakan.

## 16. Prinsip Visual yang Diterapkan

### Contrast

Gunakan perbedaan warna, ukuran, dan ketebalan untuk menonjolkan total harga, status, serta tombol utama. Kontras harus membantu pemindaian informasi tanpa membuat halaman terlihat ramai.

### Balance

Seimbangkan area navigasi, kartu informasi, tabel, dan ruang kosong. Sidebar yang stabil dipadukan dengan konten utama yang fleksibel.

### Emphasis

Gunakan satu aksi utama per konteks. Pada halaman pesanan, fokus utama adalah membuat pesanan, memperbarui status, atau menyelesaikan serah terima.

### Hierarchy

Susun informasi dari identitas pesanan dan status utama menuju detail layanan, pembayaran, dan riwayat.

### Alignment

Gunakan grid dan garis tepi yang konsisten. Label, input, angka, dan tombol harus memiliki alignment yang mudah dipindai.

### Repetition

Ulangi bentuk kartu, radius, badge, warna status, dan gaya tombol untuk menciptakan rasa familiar.

### White Space

Gunakan ruang kosong yang cukup di antara kelompok informasi. Jangan memenuhi layar dengan semua data sekaligus; detail sekunder dapat dikelompokkan dalam panel atau tab.

## 17. Arahan Implementasi Laravel

Komponen desain ini dapat diterapkan menggunakan Blade, Livewire, dan Tailwind CSS.

- Simpan token warna sebagai konfigurasi Tailwind.
- Buat komponen Blade reusable untuk tombol, badge, input, alert, modal, tabel, dan kartu statistik.
- Gunakan Livewire untuk pencarian, filter, perubahan status, dan validasi form.
- Gunakan layout utama dengan sidebar, header, breadcrumb, dan area konten.
- Gunakan policy atau middleware untuk membatasi fitur Super Admin.
- Gunakan flash session atau event Livewire untuk toast sukses dan error.
- Gunakan validasi server-side Laravel sebagai sumber kebenaran utama.

## 18. Kesimpulan Desain

WISARY Laundry harus tampil sebagai aplikasi yang bersih, profesional, dan mudah digunakan oleh staf operasional. Penggunaan melalui ponsel menjadi prioritas utama, sehingga layout satu kolom, navigasi hamburger atau bottom navigation, target sentuh 48 × 48 px, dan form yang ringkas menjadi standar dasar. Biru tua menjadi fondasi kepercayaan dan struktur navigasi, sedangkan biru terang dan cyan memberikan aksen kebersihan serta kecepatan. Hierarki halaman menempatkan status, total harga, pembayaran, dan tindakan utama sebagai fokus visual.

Desain responsif, feedback yang jelas, aksesibilitas, validasi real-time, kontras WCAG, dan pola komponen yang konsisten menjadi bagian dari desain inti, bukan tambahan setelah aplikasi selesai.

## Referensi

[1]: https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html "Web Content Accessibility Guidelines: Contrast Minimum"

Dokumen ini dibuat berdasarkan arahan desain dan kebutuhan aplikasi yang diberikan oleh pemilik WISARY Laundry. Rasio kontras aksesibilitas mengacu pada panduan WCAG [1].
