# Panduan Codebase WISARY Laundry

Peta ini membantu menemukan pemilik alur aplikasi. Ikuti alur dari route ke halaman, komponen Livewire, lalu model atau kelas domain sebelum mengubah perilaku.

## Struktur utama

| Lokasi | Tanggung jawab |
|---|---|
| `routes/web.php`, `routes/auth.php` | URL, middleware, dan nama route. |
| `app/Livewire/` | Halaman interaktif dan aksi formulir. Setiap halaman biasanya punya Blade dengan nama yang sama di `resources/views/livewire/`. |
| `app/Http/Controllers/` | Endpoint HTTP biasa, ekspor laporan, profil, dan akun staf. |
| `app/Models/` | Model Eloquent, relasi, dan operasi domain yang harus konsisten lintas halaman. |
| `app/Policies/`, `app/Http/Middleware/` | Pemeriksaan izin di server. |
| `app/Support/` | Perhitungan dan layanan domain tanpa tanggung jawab tampilan. |
| `resources/views/components/` | Komponen Blade bersama untuk tombol, input, badge, kartu, modal, dan pesan. |
| `resources/views/layouts/` | Kerangka halaman terautentikasi dan halaman tamu. |
| `database/migrations/`, `database/seeders/` | Skema database dan data awal. |
| `tests/Feature/`, `tests/Unit/` | Spesifikasi perilaku fitur dan unit domain. |

## Peta route dan batas akses saat ini

| Route / halaman | Akses dan catatan |
|---|---|
| `/login`, `/forgot-password`, `/reset-password` | Autentikasi; reset password pengguna memakai OTP email. |
| `/dashboard`, `/orders`, `/orders/create`, `/orders/{order}`, `/customers`, `/customers/{customer}`, `/services`, `/profile` | Pengguna yang sudah login. Staf dapat melihat katalog layanan; pengelolaan layanan hanya Super Admin. |
| `/reports`, `/reports/export/*`, `/audit-logs`, `/staff` | Super Admin melalui middleware `super.admin`. Link CSV tidak ditampilkan di halaman Laporan, tetapi endpoint ekspor tetap terdaftar. |
| `/orders` tab **Pesanan Batal** | Daftar dan pencarian pesanan batal; tidak ada route `/orders/cancelled` terpisah. Pemulihan dilakukan pada detail pesanan. |

Tidak ada halaman Pengaturan. Dashboard pendapatan hanya dirender untuk Super Admin. Jumlah akun aktif tidak dibatasi; minimal satu akun Super Admin harus tetap aktif.

## Temukan fitur

| Fitur | Mulai membaca | Lanjutan |
|---|---|---|
| Dashboard | `routes/web.php`, `resources/views/dashboard.blade.php` | `app/Livewire/Dashboard/`, `resources/views/livewire/dashboard/`; pendapatan hanya tampil untuk Super Admin |
| Buat pesanan | `app/Livewire/Orders/Create.php` | `resources/views/livewire/orders/create.blade.php`, `app/Support/PricingCalculator.php`, `app/Support/EstimateCalculator.php`, `app/Models/Order.php` |
| Daftar dan detail pesanan | `app/Livewire/Orders/Index.php`, `app/Livewire/Orders/Show.php` | `resources/views/livewire/orders/index.blade.php`, `show.blade.php`, partial detail di `orders/partials/`, `app/Models/Order.php`, `app/Support/StatusFlow.php` |
| Pembatalan dan pemulihan | Tab batal di `app/Livewire/Orders/Index.php`; aksi di `app/Livewire/Orders/Show.php` | `resources/views/livewire/orders/partials/cancel-modal.blade.php`, `app/Models/Order.php`, `app/Policies/OrderPolicy.php` |
| Pelanggan dan layanan | `app/Livewire/Customers/`, `app/Livewire/Services/` | `app/Models/Customer.php`, `app/Models/Service.php`, policy terkait |
| Pembayaran dan refund | `app/Models/Order.php`, `app/Livewire/Concerns/PaymentActions.php` | `app/Models/Payment.php`, `app/Models/Refund.php`, modal bersama pada halaman pesanan dan dasbor |
| Serah terima | `app/Livewire/Concerns/HandoverActions.php` | `app/Models/Order.php`, `app/Models/OrderHandoverHistory.php` |
| Laporan dan ekspor | `app/Livewire/Reports/Index.php` | `app/Http/Controllers/ReportExportController.php`, `app/Support/Csv.php` |
| Akun dan autentikasi | `routes/auth.php`, `app/Http/Controllers/Auth/` | `app/Http/Middleware/`, `app/Policies/UserPolicy.php` |
| Audit aktivitas | `app/Support/Audit.php` | `app/Models/AuditLog.php`, `app/Support/AuditFeed.php` |

## Alur perubahan umum

1. Route memilih halaman Livewire atau controller dan menerapkan middleware akses.
2. Livewire memvalidasi input, menyiapkan respons, lalu meminta operasi domain atau menyimpan model.
3. Model dan kelas `Support` menjaga aturan bisnis yang perlu berlaku di semua halaman.
4. Blade menampilkan state dan mengirim interaksi kembali ke komponen.
5. Policy dan middleware tetap menjadi batas izin; menyembunyikan tombol saja bukan kontrol akses.

Untuk perubahan aturan harga, berat, status, pembayaran, atau nomor pesanan, periksa spesifikasi dan model/domain terkait sebelum mengubah tampilan. Pembayaran tidak dicatat pada form pembuatan pesanan; gunakan `PaymentActions` dan operasi domain di `Order`.

## Aturan penamaan

- Pertahankan nama konvensional Laravel untuk kelas dan berkas (`Order`, `Create`, `OrderPolicy`, `create.blade.php`).
- Gunakan nama aksi yang menyebut hasil atau tujuan (`recordPayment`, `selectCustomer`), bukan nama umum seperti `process` atau `handle` jika konteksnya bisa disebut.
- Nama Livewire dan Blade yang berpasangan harus tetap sejalan.
- Tambahkan komentar untuk alasan atau aturan yang tidak jelas dari kode; jangan mengulang apa yang sudah dinyatakan nama fungsi.
- Ekstrak bagian hanya saat ia punya tanggung jawab sendiri atau dipakai ulang. Jangan memecah fungsi pendek sekadar mengecilkan jumlah baris.
