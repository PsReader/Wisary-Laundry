# WISARY Laundry

Aplikasi operasional laundry berbasis Laravel, Blade, Livewire, dan Tailwind CSS.
Antarmuka berbahasa Indonesia dan menggunakan zona waktu `Asia/Jakarta`.

Fitur saat ini mencakup Dashboard, pembuatan dan pengelolaan pesanan, pelanggan,
katalog layanan, pembayaran/refund, serah terima, laporan, audit log, dan
manajemen akun Staf. Laporan, audit log, dan manajemen akun hanya untuk Super
Admin. Tidak ada halaman Pengaturan; pesanan batal tersedia sebagai tab pada
Daftar Pesanan.

## Menjalankan secara lokal

1. Siapkan PHP 8.3+, Composer, Node.js, MySQL atau MariaDB, lalu salin `.env.example` menjadi `.env`.
2. Atur koneksi database dan SMTP di `.env`.
3. Jalankan `composer install` dan `npm install`.
4. Jalankan `php artisan key:generate`.
5. Siapkan skema dengan `php artisan migrate --seed`.
6. Bangun aset dengan `npm run build`.
7. Jalankan `composer run dev` untuk server lokal dan Vite.

Untuk database kosong yang boleh dihapus, `php artisan migrate:fresh --seed` membuat ulang seluruh skema dan data awal.

## Peta codebase

Mulai dari [panduan codebase](docs/Panduan%20Codebase.md) untuk menemukan alur fitur,
aturan bisnis, dan berkas yang perlu dibaca saat mengubah tiap bagian aplikasi.

Dokumen rujukan utama:

- [Spesifikasi fungsional](docs/Spesifikasi%20Final%20Aplikasi%20Manajemen%20Laundry.md) — sumber aturan bisnis dan kriteria penerimaan.
- [Design system](docs/Design%20System%20dan%20Hierarki%20UI%20WISARY%20Laundry.md) — sumber aturan tampilan dan aksesibilitas.
- [Milestone implementasi](docs/Milestones%20Implementasi%20WISARY%20Laundry.md) — urutan pengerjaan dan status penerimaan.

Dokumen tersebut diselaraskan dengan aplikasi per 8 November 2026. Checklist
QA perangkat dan infrastruktur yang belum diverifikasi tetap ditandai terbuka.

## Perintah umum

```bash
php artisan serve
npm run dev
npm run build
php artisan migrate
php artisan test
```

Jangan jalankan `migrate:fresh` pada database yang berisi data yang perlu dipertahankan.
