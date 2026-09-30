# AGENTS.md

## Coding Philosophy

Adopt the mindset of the laziest senior dev. The best code is the code you never wrote.

- **Minimalism** — Keep code tight. If Blade/Livewire can handle it natively, don't pull in extra packages.
- **Safety First** — Data-loss handling, financial accuracy, and security are never negotiable. Prices, weights, and payments must be exact.
- **Trace the Flow** — Read and understand the code before editing. Be lazy about writing, never about reading.

## Tech Stack

| Layer | Choice |
|---|---|
| Backend framework | Laravel (latest stable) |
| Templating | Blade |
| Interactivity | Livewire |
| CSS | Tailwind CSS |
| Database | MySQL / MariaDB |
| Authentication | Laravel Breeze |
| Timezone | `Asia/Jakarta` (WIB) |
| Locale | `id` (Bahasa Indonesia) |

No React, no Vue, no SPA. Server-rendered with Livewire for dynamic behavior.

## Commands

```bash
php artisan serve              # dev server
npm run dev                    # Vite asset build (watch mode)
npm run build                  # Vite production build
php artisan migrate            # run pending migrations
php artisan migrate:fresh --seed  # reset DB with seed data
php artisan test               # run feature/unit tests
```

## Project Documentation

Three reference documents live in the `docs/` folder:

| File | Purpose |
|---|---|
| `docs/Spesifikasi Final Aplikasi Manajemen Laundry (2).md` | Full functional specification — pricing, orders, payments, roles, DB schema, acceptance criteria |
| `docs/Design System dan Hierarki UI WISARY Laundry.md` | UI/UX design system — colors, typography, layout, components, accessibility |
| `docs/Milestones Implementasi WISARY Laundry.md` | Implementation roadmap — 20 sequential milestones with checklists |

**Read these before building any feature.** The spec is the source of truth for business logic. The design system is the source of truth for UI. The milestones define the build order.

## Key Business Rules (Invariants)

These are locked-in decisions from the spec. Never violate them:

- **Weight billing:** Round up to whole kg. Minimum billing is 4 kg (e.g., 3.5 kg → 4 kg, 4.6 kg → 5 kg).
- **Unit services:** No minimum kg rule. Charged per unit.
- **Order number format:** `{sequential_number}{DDMMYYYY}` with no separators. Example: `3702972026` = order #3702 on 7 Sep 2026.
- **Status flow:** Diterima → Sedang Dicuci → Sedang Dikeringkan → Sedang Disetrika → Sedang Dilipat → Siap Diambil. Also: Dibatalkan. There is **no "Selesai" status** — completion is tracked via handover status.
- **Handover statuses:** Belum Diserahkan, Sudah Diambil, Sudah Diantar.
- **Kilat:** Always promises same-day completion — no cutoff, no warning, no manual date picker (whole `settings` stack removed 15 Sep 2026).
- **Estimates:** Calculated in calendar days (including weekends). Reguler = 3 days, Express = 1 day, Kilat = same day (before cutoff).
- **Old prices preserved:** When default service prices change, existing orders retain their original prices.
- **No permanent deletion:** Services used in orders can only be deactivated, never deleted. Orders are never permanently deleted.
- **Payment statuses:** Belum Lunas, Lunas Sebagian, Lunas, Dikembalikan.
- **Refunds:** Separate records, not modifications to original payments.
- **Price adjustments:** Super Admin only. Price is recorded as-given (never recalculated), with mandatory reason logging. Staff own customer/service/weight/status edits but cannot touch final price.
- **Delivery:** Free, no courier data needed. Failed delivery recorded with reason.
- **Timezone:** All timestamps and calculations use WIB (Asia/Jakarta).

## Design System Summary

Full details in `docs/Design System dan Hierarki UI WISARY Laundry.md`. Key rules:

### Colors

| Token | Hex | Use |
|---|---|---|
| `primary-700` (Wisary Blue) | `#1559A6` | Primary buttons, links, focus |
| `primary-800` (Deep Blue) | `#123B68` | Sidebar, header, brand |
| `primary-900` (Navy Wisary) | `#0B1F3A` | Dark text, headings |
| `primary-50` (Ice Blue) | `#F1F8FF` | Page background |
| `success-700` | `#18794E` | Paid, completed |
| `warning-700` | `#A15C00` | Partial payment, attention |
| `danger-700` | `#B42318` | Cancelled, errors |
| `info-700` | `#0B6E99` | Delivery, general info |

Status badges always combine color **and** text label. Never color alone.

### Typography

Single sans-serif family: **Inter** or **Plus Jakarta Sans**. Short, action-oriented labels (e.g., "Buat Pesanan", "Simpan Pesanan").

### Mobile-First

- Single-column default on mobile.
- 48 × 48 px minimum touch targets.
- Hamburger menu or bottom navigation on mobile, sidebar on desktop.
- Sticky price summary on long forms.
- No hover-only interactions.
- Breakpoints: Mobile 0px, Tablet 640px, Desktop 1024px, Wide 1280px.

### Forms

- Labels always visible above inputs, never replaced by placeholders.
- Real-time validation after field blur, not just on submit.
- Rupiah format: `Rp40.000`.
- Weight input shows helper text about rounding and 4 kg minimum.
- Required fields have clear indicators.

### Feedback

Every user action gets visible feedback: success toast, error alert with cause, loading spinner on buttons, empty states with guidance. All messages in clear Bahasa Indonesia.

## UI Component Conventions

Build reusable Blade components for: buttons, inputs, selects, badges, cards, modals, toasts, tables, stat cards. Use Tailwind utility classes. Livewire handles search, filters, status changes, and form validation.

### Button Variants

| Variant | Style |
|---|---|
| Primary | `primary-700` background, white text |
| Secondary | White background, `neutral-300` border |
| Destructive | `danger-700` background, for risky actions only |
| Text | No background, for secondary actions |

### Status Badge Colors

| Status | Color |
|---|---|
| Diterima | Light blue |
| Sedang Diproses | Cyan / blue |
| Siap Diambil | Green |
| Belum Lunas | Yellow |
| Lunas | Green |
| Dibatalkan | Red |
| Sudah Diantar | Blue-purple / cyan |
| Sudah Diambil | Dark green |

## Access Control

| Role | Permissions |
|---|---|
| Super Admin (max 1) | Full access: manage services, prices, staff accounts, settings, all CRUD, restore cancelled orders |
| Staff (max 4 active) | Operate on orders and customers; cannot manage services, prices, accounts, or settings |

Enforce via Laravel middleware and policies. Never rely on frontend hiding alone.

## Development Workflow

Follow the 20 milestones in `docs/Milestones Implementasi WISARY Laundry.md` sequentially. Status convention:

- `[ ]` Not started
- `[~]` In progress
- `[x]` Done and verified
- `[!]` Blocked or needs decision

No milestone starts until the previous one passes its acceptance criteria.

## Session Checkpoint — 24 Sep 2026

Update this section at the end of every session: mark what changed, what's verified, and what's next. It's the first-read map so a fresh session doesn't need to re-explore.

### State
- M1–M16 done & verified. M17 in progress ([~]): 4 code items marked [x] 15 Sep 2026 (reset password, session timeout, logout, login activity); remaining (backup, SSL, hosting, domain) are INFRA, not code. M18 in progress ([~]): all automated-test items marked [x]; remaining are manual device/accessibility QA. M19–M20 pending.
- Git: seven commits — `f7f76df feat: Wisary Laundry initial implementation (M1-M16)`, `e57a373 fix: block payment on cancelled orders and duplicate handover`, the 30 Sep 2026 T0–T4 + hardening commit, the 30 Sep 2026 dead-code trim commit, `1e27853 style: design-system conformance pass`, `017e834 feat: streamline dashboard`, and `36a6e83 fix: fill dashboard KPI grid leftover cells` (see checkpoint below).
- **15 Sep 2026:** Kilat cutoff concept REMOVED ENTIRELY — Kilat always promises same-day, no warning, no manual date picker. `/settings` page + whole settings stack deleted (App\Livewire\Settings, Setting model, SettingPolicy, settings migration, seeder entry). Docs (spec + milestones) amended with Ratifikasi 15 Sep 2026. Local DB still has a stale `settings` table until `migrate:fresh --seed`. EstimateCalculator now has no cutoff params: `completionDate(Service, Carbon)` / `orderCompletionDate(Collection, Carbon)`.
- **24 Sep 2026 (ponytail cleanup):** Deleted 19 dead files + 1 dep (grep-verified zero live callers): Breeze email-verification stack (3 controllers + verify-email.blade, no routes), Breeze `layouts/navigation.blade.php`, 10 unused Breeze components (application-logo/dropdown/dropdown-link/responsive-nav-link/primary-button/secondary-button/danger-button/text-input/input-label/input-error), stock `welcome.blade.php` + 2 ExampleTest.php, placeholder CLAUDE.md, and dormant `@tailwindcss/vite` v4 dep (npm install re-pruned lockfile). No behavior change; suite green.
- **24 Sep 2026 (mail live):** `.env` switched `MAIL_MAILER=log` → `smtp` (smtp.gmail.com:587, `MAIL_SCHEME=smtp` — `tls` invalid in Laravel 13, use `smtp`/`smtps`), Gmail app password, `MAIL_FROM_NAME="Wisary Laundry"`. Smoke test via `Mail::raw` returned `SentMessage`. Forgot-password emails now deliver.
- **24 Sep 2026 (OTP reset):** Forgot-password replaced email *link* (Breeze token flow) with a **6-digit OTP via email** — no URL in the email, so APP_URL/localhost-link problems vanish. Flow: POST `/forgot-password` → `random_int(100000,999999)`, `Hash::make`, stored in **Cache** key `password_reset_code.{email}` (hash + expires_at + attempts), sent via new `App\Notifications\PasswordResetOtp`; POST `/reset-password` verifies code (10-min expiry, max 5 attempts then invalidated, generic "tidak valid" errors, neutral "kode terkirim" for unknown email against enumeration), resets password, audits `user.password_reset`, redirects to login. Removed `GET /reset-password/{token}` + `auth/reset-password.blade.php`; `auth/forgot-password.blade.php` is two-state (email → code+password). `config/auth.php` `passwords.users.expire` = **10** (single source for TTL + mail text). Routes throttled: forgot-password `3,2`, reset-password `10,5`. `PasswordResetTest` rewritten for OTP (7 tests). Suite green. Docs/milestone line about "reset via email" is stale-historical; keeper of the rule is this checkpoint.
- **24 Sep 2026 (order service picker):** Create-Order Layanan rows now use a **grouped chip picker** instead of the flat 16-option dropdown — `<x-select>` fully removed from create page (still used in services admin). `Create.php`: `serviceOptions()` deleted; added `groupedServices()` (per-category: id/name/price_label/unit), `pickService($index,$id)` (set service + resetValidation), `changeService($index)` (clear + reset to 1 qty), `serviceIsUsed($index,$id)` (disables chips already taken by another row). Picked row collapses to selected-service summary + "Ganti". Tests untouched & green (204/715).
- **24 Sep 2026 (single shared service panel):** Create-Order picker slimmed from N grids to **one "Tambah layanan" panel** for the whole order (scale-safe as services grow). Rows = chosen-service cards only (name+price+berat/jumlah+subtotal+catatan+Hapus); empty `service_id` row shows placeholder, not a grid. `Create.php`: `mount()` starts `items=[]`; `addItem()`+`pickService()` deleted → `addService($serviceId)` (appends row with service preset); `serviceIsUsed()` → `serviceSelected($serviceId)` (index-free). View: `@forelse` + empty-state, panel below groups chips (disabled if used, categories with all-used chips hidden), "+ Tambah Layanan" button removed. `OrderCreationTest`: `test_staff_can_add_and_remove_item_rows` → `test_staff_can_add_change_and_remove_service_rows`. Suite green (204/717).
- **24 Sep 2026 (flow buttons filtered by service):** Order-detail status buttons hide stages irrelevant to the order's services. Rule = service_name substring (snapshot `OrderItem`, case-insensitive): "Sedang Disetrika" shown iff any item name contains `setrika` OR its Service category is `Barang khusus` ("ikut setrika"); "Sedang Dilipat" shown iff any item name contains `lipat` (Cuci Lipat only). `Show.php`: `nextStatuses()` → `#[Computed] flowButtons()` (+ private `orderHasIroning()`/`orderHasFolding()`), `order()` eager-loads `items.service`. `show.blade.php` uses `$this->flowButtons`. Server rules unchanged. `StatusTransitionTest` +5 tests. Suite green (209/732).
- **24 Sep 2026 (order list quick filters):** Orders index swapped the status `<select>` for **status pills with live counts** — `Semua (N)` + pill per status styled primary when active, tap toggles (`setStatusFilter` clears on re-tap). Date area got quick chips **Hari Ini / Kemarin / 7 Hari** (`setDateFilter`; today/yesterday set `receivedDateFilter`, 7d uses new `recent7Filter` column — exact-date and range are mutually exclusive via `updated*` hooks). `Index.php`: extracted `baseQuery()` (search + non-status filters), `#[Computed] statusCounts()` (groupBy status under current context), `recent7Filter` in queryString. Filter grid now 3 selects (`sm:grid-cols-3`). `StatusTransitionTest` +2 tests. Suite green (211/742).
- **24 Sep 2026 (order list declutter):** Orders index filter area slimmed without cutting features: 3 secondary selects (pembayaran/penyerahan/metode) wrapped in a native `<details>` "Filter" disclosure (active count badge `(N)` on summary, closed by default); status pills single-line horizontal scroll on mobile (`flex-nowrap overflow-x-auto scrollbar-chip sm:flex-wrap`); filter container `gap-3`→`gap-2`. Status/date/search stay one-tap; build + suite green (211/742).
- **24 Sep 2026 (create page mobile polish):** (1) iOS auto-zoom killed — form controls bumped to `text-base sm:text-sm` in `x-input`/`x-select` `$base` + 4 raw textareas on create page (16px on <sm = no Safari zoom, desktop stays 14px). (2) "Tambah layanan" chips: base `flex-nowrap overflow-x-auto scrollbar-chip` (single-line strip per category on phones), `sm:flex-wrap sm:overflow-visible`, chips `shrink-0 sm:flex-1 sm:min-w-[8rem]` (desktop still fills width); old `count>5` branch deleted. (3) `x-card` padding `p-6`→`p-4 sm:p-6` (all pages gain mobile width). Build + suite green (211/742).
- **24 Sep 2026 (mobile nav rework):** Bottom nav `grid-cols-4`→`grid-cols-5` + item ke-5 = `services` (Layanan, ikon hamburger, label mengikuti `nav-items`). Item `orders.create` di-render sebagai **FAB tengah**: lingkaran `w-14 h-14 -mt-6 bg-primary-700` plus putih `w-7 h-7` (`stroke-width-2.5`) `ring-4 ring-white` naik keluar bar + label "Buat"; kolom ke-3 dari 5 = tengah persis. Mobile drawer: blok Akun (avatar+nama+rol) + tombol Keluar dipindah dari footer bawah ke **atas** (setelah header logo/close, `border-b`), footer bawah dihapus — sidebar desktop tidak berubah. Build + suite green (211/742).
- **24 Sep 2026 (dashboard rework):** `x-stat-card`(+`components/stat-card.blade.php`) redesain — prop baru opsional `icon` (SVG path) + `tone` (primary/success/warning/danger/info/neutral) + `solid` (kartu highlight `bg-primary-700 text-white`); ikon di lingkaran soft `${tone}-50`, angka `text-2xl sm:text-3xl`, padding `p-4 sm:p-5`, indikator di bawah angka (kompatibel dengan pemakaian lama). Dashboard blade: grid `grid-cols-2 lg:grid-cols-4` + gap-3 sm:gap-4, tiap KPI diisi ikon/tone (baru: "Menunggu Serah Terima" menggantikan label "Belum Diserahterimakan"), **Pendapatan Hari Ini** = kartu solid `col-span-2 lg:col-span-1` begitu pula kartu terakhir. Judul 2 kartu list diberi badge jumlah `(N)`. `PendingHandover`/`PendingPayment` dibatasi `->limit(5)`; dua blade pending dapat empty-state berikon + footer "Lihat semua →" (`route('orders')`, hanya jika list tidak kosong). Grafik batang tidak dibuat (keputusan: YAGNI, butuh JS/dep). Build + suite green (211/742).

- **29 Sep 2026 (money-path hardening):** M17–M20 hold 47 unchecked items, **all manual/infra — zero code**. Build is code-complete; confirmed 228 `[x]` / 0 `[~]` / 47 `[ ]` in the milestones doc. Two real money defects closed: (1) `Order::recordPayment()` never checked `status`, so cash could be taken on a `dibatalkan` order — and `ReportExportController` counts revenue from `payments.received_at`, so that money landed in the books as revenue on a void order. Guard added; **asymmetric on purpose: payment blocked, refund still allowed** (returning money for a voided order is exactly what a refund is for). (2) `Order::recordHandover()` gated only on `status === SIAP_DIAMBIL`, so a second `sudah_diambil` wrote a duplicate history row, and `gagal` could be recorded after a *successful* handover (`gagal` deliberately doesn't clear `handover_status`, `:412`). One guard blocks every action once handed over; retry-after-`gagal` still works because `gagal` leaves it `BELUM_DISERAHKAN`. `PricingCalculatorTest` boundaries closed — 4.0→4, 4.5→5, 5.0→5 added to the existing method (code was already correct; this stops a future edit breaking it). **Refuted during review:** the "refund lets you over-collect" case is *not* a bug — `PaymentRefundTest:107` uses reason "Kelebihan bayar" and `:124` deliberately asserts `remainingAmount === 50000` after a full refund. The ledger model is *refund = money back, balance re-opens*, and it is tested. Don't "fix" it. Spec L280 + L500 corrected to Super Admin-only, Milestone 8 ratification note added. Suite green (213/753, was 211/742). Committed as `e57a373`.
- **30 Sep 2026 (dashboard streamlined):** Dashboard slimmed in one commit (`017e834`). Drops the duplicate `Ringkasan {date}` header row (layout h1 `Dasbor` already there) — "Buat Pesanan" moved into the layout `headerActions` slot via `<x-button href>`. KPI grid 8→6 cards: removed `Delivery` and `Menunggu Serah Terima` stat cards (redundant with the two action lists below), deleted `delivery`/`notHandedOver` from the `$kpi` blade array (2 fewer queries/load). **Pendapatan Hari Ini card now gated `@if (auth()->user()->isSuperAdmin())`** — revenue is admin-only, staff never sees financials (won't leak via the rendered page source either because the card is server-side `@if`, not CSS). List card titles shortened: "Perlu Ditindaklanjuti — Menunggu Serah Terima (N)" → "Menunggu Serah Terima", "…Belum Lunas (N)" → "Belum Lunas" (count badges removed; KPI cards above own the numbers). Kept `Terlambat`. Staff grid = 5 cards (last row single Terlambat card, left natural). Tests untouched (assert "Menunggu Serah Terima" via list title at `OrderHandoverTest:260`). Suite green (211/760). **Grid-fill follow-up (`36a6e83`):** trailing card spans absorb leftover cells so every row fills at every breakpoint — Terlambat `col-span-2 lg:col-span-4` for staff (full-width overdue band) / `col-span-2 lg:col-span-2` paired with Pendapatan's `col-span-2 lg:col-span-2` for admin; `$isSuperAdmin` precomputed once in the `@php` block. Staff desktop = `4 + Terlambat-full`, admin desktop = `4 + (Terlambat|Pendapatan)`; mobile `2+2+2` both roles. Suite green (211/760).
- **29 Sep 2026 (UI formatting pass, T0–T4):** Design-system conformance pass against `docs/Design System dan Hierarki UI WISARY Laundry.md`. **T0 (root cause):** `neutral` was **never defined in `tailwind.config.js`**, so every `border-neutral-*`/`text-neutral-*` in the app silently fell back to Tailwind's default gray ramp. Full 50–950 scale now defined, anchored on the doc's five §4.3 hexes (`950 #17212B`, `700 #475467`, `500 #667085`, `300 #D0D5DD`, `100 #F2F4F7`; rest interpolated). Fixes `text-neutral-500`-on-`primary-50` **4.43:1 → 4.64:1 PASS** in one edit. `modal.blade.php` `bg-gray-500`→`bg-neutral-900`; `app.css` scrollbar `#d4d4d8`→`#D0D5DD`. **Verify palette by grepping the source for `#` hexes (that IS the source of truth) and `rgb(` in the built CSS — BUT `view:clear` first: `tailwind.config.js` content-globs `storage/framework/views/*.php`, and 98 stale compiled views inject dead default-ramp classes into the build and produce false positives. That glob entry was removed 30 Sep 2026 (see below).**
- **29 Sep 2026 (T1 page titles):** `<h1>` was inside `lg:hidden` in `layouts/app.blade.php:141` and the desktop header rendered a bare `<span></span>` → **desktop showed no page title on any page**. Page header is now the single home of the title at every breakpoint (one `<h1>`, no per-breakpoint duplicate). Profile link got `hidden lg:inline-flex` so the mobile drawer stays the account surface. 5 route-bound Livewire components had **no** `#[Title]` (not 4 — only `CancelledOrders` had one): added to `Orders\Index` ("Pesanan"), `Orders\Create` ("Buat Pesanan"), `Reports\Index` ("Laporan"), `AuditLogs\Index` ("Log Aktivitas"). **`Show` uses `->title('Pesanan '.$this->order->order_number)` in `render()` instead of `#[Title]`** — the attribute is static and *overwrites* any `->title()` in render (see `SupportPageComponents.php:102-104`). Duplicate view `<h1>`s removed from `show.blade.php` (order number now comes from the header) and `cancelled-orders.blade.php` (live count demoted to a `<p>`). The 8 wrapper views already passed `title=` to `x-app-layout`. Every route now has a title.
- **29 Sep 2026 (T2 text/touch):** Only 3 sub-12px text in the whole app — `text-[11px]`×2 (bottom nav) and `text-[10px]`×1 (create service unit badge) — all → `text-xs`. **Input/select error text `text-xs`→`text-sm`**: errors were rendering *smaller* than the `text-sm` label they annotate. **`min-h-12 lg:min-h-10` → `min-h-12` app-wide** (13 files, scripted) — desktop was 40px, and `sm` button size was ~32px, both under the 44px rule; the `lg:` override was also a mixed-size bug generator. All sub-44px targets raised to 48px: date chips (32→48), pagination (wrapped in `overflow-x-auto scrollbar-chip` + `[&_a]/[&_span]:min-h-12` child selectors since the vendor view can't be edited), `min-h-10` summary/detail toggles + dashboard links + report filter chips, logos `h-10`/`h-11`→`h-12`, WhatsApp link → `inline-flex min-h-11`. **Zero `min-h-8|9|10` remain.**
- **29 Sep 2026 (T3 mobile breaks):** Order-detail service table had `min-w-[480px]` in a ~311px box — the 4 money columns (Harga/Berat/Ditagihkan/Subtotal) were **entirely off-screen**. It now has a **mobile card list** (`sm:hidden`, dl of Berat/Jumlah + Ditagihkan + subtotal + total) mirroring the `orders/index` pattern; table is `hidden sm:block`. Pagination no longer causes document-level horizontal scroll. `index:15-46` had a **0px gap** between the 48px date input and the chips (only raw HTML whitespace) — wrapped in `flex flex-wrap gap-2`. Create-page method radios `grid-cols-2`→`grid-cols-1 sm:grid-cols-2` ("Diambil Pelanggan" needed 184px in a 165px cell). Sticky summary `pb-44`→`pb-48`. **Skipped berat-tagihan in the create sticky bar** (doc §9.2): no `billedWeightPreview` computed exists and `itemBilledWeight()` is per-row; adding a computed for one summary line is not worth it, and the weight is already on each row.
- **29 Sep 2026 (T4 modal a11y):** `x-modal` gained `role="dialog"` + `aria-modal="true"` (0 of 8 had them) and **focus return** — `open()`/`close()` methods capture `document.activeElement` and restore it on close, so the watcher and the three `open-modal`/`close-modal`/`close` entry points all route through one place (the drawer's focus-return in `app.js:28` was the existing pattern). Panel got `max-h-[85vh] overflow-y-auto` — previously only the *backdrop* scrolled, so the dialog traveled with the content. **Label linkage root-fixed:** `$fieldId = $id ?? $name` emitted `id=""`/`for=""` for the 18 `x-input`/`x-select` calls that bind via `wire:model` with no name; both components now derive the id from the `wire:model`/`wire:model.live`/`wire:model.blur` binding, so 18 call sites needed no edit (verified in isolation: `for="weight"` ↔ `id="weight"`). **Skipped the submit spinner:** the `loading` prop exists and the 13 submit buttons carry `wire:loading.attr="disabled"`, but binding a Blade *prop* to `wire:loading` state needs JS; the disabled guard already prevents double-submit. A scripted attempt at this produced `wire:target=""` and was reverted — don't re-try it blind. `x-select` still contains the 6px `mt-1.5` empty-div spacer (functional, left alone); `x-input`'s was removed in the pass.
- **30 Sep 2026 (UI pass hardening):** Post-audit fixes before committing T0–T4. (1) **Mojibake corruption fixed** — the pass had double-encoded UTF-8 (Windows-1252 misread re-saved as UTF-8) across 13 views: 116 tokens total, all six corrupted variants of `—` (em-dash), `–` (en-dash), `№` (numero), `·` (middot), `─` (box-drawing dividers), and `→` (arrow); user-visible as garbage on dashboard cards, payment/refund history, order-number № prefixes, phone helper text, and error-page `<title>`s. Byte-safe `.Replace()` per file, then byte-verified all clean while untouched clean-UTF8 files (`customers/show`, `layouts/app` ✓/✕, `pending-handover` №, `orders/index` ▾↑↓№) were left alone. (2) **13 removed `wire:loading.attr="disabled"` guards restored** — `git grep` showed the pass had silently deleted every submit-button guard (0 left) while the checkpoint still *cited them as the reason for skipping the spinner*; money forms (payment/refund/handover) were double-click-unsafe with no server dedup in `recordPayment()`. Re-added to HEAD parity: customers(1), pending-payment(2), cancelled(1), create(2), show(5), services(1), handover-modal(1). (3) **`tailwind.config.js` content-glob fixed** — removed `./storage/framework/views/*.php`; 98 stale compiled views were injecting dead default-ramp classes into the CSS build, making the T0 verification method unreliable. Build shrank 46KB→37KB. (4) **Last off-token grays mopped up** — error-page inline `#E5E7EB`→`#EAECF0`/`#52525B`→`#5A6478` (neutral-200/-600, was Tailwind gray/zinc), `bg-red-50/60`→`bg-danger-50/60` ×2 (show + pending-payment). (5) **Unaccounted-for work kept and documented**: toast auto-dismiss policy (success auto, error/warning click-to-dismiss + `role="alert"`, `app.js:52`), success-palette hue tweaks in `tailwind.config.js` (`100` `#E3F6EC`→`#DDF7E8`, `200` case-fix + `#C5EDd9`→`#C5ECD9`), and Orders\Index `resetFilters()`/"Hapus filter" (Index.php:75-91) + its test. Milestone M18 stale note (203/694) updated to 214/764. Suite green (214 tests/764 assertions, was 213/753).
- **30 Sep 2026 (state after UI-pass commit):** See Git line below — T0–T4 + hardening committed in one commit. Only remaining blocker to full M17–M20 sign-off is the **rendered visual pass at 375/768/1440** (grey shift, overlay correctness) plus the 47 manual-QA/INFRA milestone items — all zero-code.
- **30 Sep 2026 (dead-code trim):** Repo-wide over-engineering audit (`ponytail-audit`) applied the safe bucket in one commit. Deleted: Breeze **confirm-password stack** (controller + `auth/confirm-password.blade.php` + 2 routes in `routes/auth.php` + `PasswordConfirmationTest` — no route/middleware ever referenced `password.confirm`; profile updates post straight to `password.update`), dead `format_time_wib()` helper (legacy of removed Kilat-cutoff era), `User::isStaff()` (never called), `Service::orderItems()` relation (zero callers), and custom `GET /health` (duplicates Laravel's built-in `/up` from `withRouting(health: '/up')`). **NOT applied (separate decision):** `Refund.payment_id` dead schema linkage, payment/refund modal duplication between Show/PendingPayment (suggested `PaymentActions` concern), `orders.payment_method` denormalized column, `laravel/pao` dev dep. Verified NOT dead: `concurrently`/`@laravel/multiplex` are used by `php artisan dev` (DevCommand Windows→concurrently, non-Windows→multiplex). Suite green (211 tests/760 assertions, was 214/764).
- **Deliberately NOT done in the UI pass** (user decisions): the 202-token 8pt normalization sweep (the app uses consistent tokens so it reads as rhythm, not randomness — a 1000-line density-changing diff for ~zero visual gain), the 3.5 missing orders-index columns (Layanan/Estimasi/Aksi + handover-vs-method in Serah terima — that's a feature, not formatting), the 20 missing `focus-visible` rings (browsers supply a default focus ring, so that's branding not a11y), and the dashboard bar chart (AGENTS.md already records it as a deliberate YAGNI call). Build green. **The T0 grey shift needs a rendered visual pass at 375/768/1440 before sign-off — it cannot be verified from the code alone.**


### Build stack facts
- Laravel 13 + Livewire 4 + Tailwind 3.4 (PostCSS v3 pipe) + Vite 8; migrations (settings table migration removed), 13 models (minus Setting), 11 (minus Settings\Index) Livewire components, 12 Blade components, 4 seeders.
- Working pipe is postcss.config.js + @tailwindcss/forms. Keep it: any "tailwind v4 upgrade" advice is wrong until the pipe migrates wholesale.
- config/database.php defaults sqlite, config/app.php locale defaults 'en' — both overridden by .env (mysql, id, Asia/Jakarta). Idle timeout = config('app.idle_timeout', 30).
- Key invariants: no "Selesai" status; handover Belum/Diambil/Diantar; Kilat = same day always, no cutoff; min billing 4 kg; order_items snapshots preserve old prices; refunds separate from payments.

### Route map (routes/web.php)
- auth: /dashboard, /orders(+create/{order}), /customers(+/{customer}), /services, /profile
- super.admin group: /orders/cancelled, /reports(+exports), /audit-logs, /staff(+store/toggle-active/password-reset)

### Conventions to keep
- Forms are redirect-based: redirect()->route(...)->with('success') + Blade toast; Livewire classes use #[Computed] + plain rules() arrays.
- Audit every important mutation: App\Support\Audit::record($user, $action, $model, $before, $after).
- Authz = `super.admin` middleware alias AND policy methods (Order/Service/Customer/User) — never frontend-only.
- Money/weights exact (integer rupiah, ceil weight); all times WIB helpers format_*_wib.
- Each new feature ships with a matching tests/Feature file (RefreshDatabase), pattern per StaffAccountTest.

### Known debt
- sqlite default foot-gun; no initial commit; rate-limiter name not globally registered.
- **`nextOrderNumber()` race guard is untested.** `Order.php` uses `lockForUpdate()` on the highest-id row, which is a *no-op on SQLite* — and the whole suite runs SQLite in-memory (`phpunit.xml:26-27`) while production runs MySQL. Accepted 29 Sep 2026: on MySQL/InnoDB the lock is a genuine current read so it serializes correctly, and `orders.order_number` is UNIQUE so a collision raises a DB error rather than silently duplicating. Also note the sequence is **global, not per-day** — it reads the latest order overall, not today's. Upgrade path if concurrency ever matters: a dedicated `order_sequences` row, or a retry loop on the UNIQUE violation so behavior is identical on both engines.
- **No DB-level CHECK constraints.** `orders.status`, `handover_status`, `payment_status`, `method` and `order_items.pricing_type` are PHP-only strings. A direct SQL write can put the ledger in a state the app can never produce. Needs a migration; not worth it at this shop's scale.
- **Missing indexes on hot paths:** `orders.received_at` (every report, export, and list date filter), `orders.payment_status` and `orders.handover_status` (both dashboard widgets). Only `orders(status, created_at)` is indexed today.
- `DevSeeder` writes 7-char order numbers (missing zero-padding), which poisons the `nextOrderNumber()` counter if seeded orders hold the highest ids.
- Deleted-on-15-Sep settings left a stale local `settings` table until migrate:fresh --seed; keeper of "no settings module" invariant is docs, not code.
- Hosting/SSL/backup-schedule/domain items in M17 are INFRA, not code.

### Suggested next actions each session
1. git status/log — confirm no drift; commit staged WIP once approved.
2. Progress per docs/Milestones md; update [~]/[x] flags when a milestone passes its acceptance criteria.
3. Run php artisan test before/after changes; verify changed pages via php artisan serve.

## Font & Asset Sources

| Source | What | URL |
|---|---|---|
| Fontshare | Professional-grade free fonts with CSS API | https://www.fontshare.com |
| Free Faces | Curated free typefaces | https://freefaces.gallery |
| Uncut | Contemporary fonts, free commercial use | https://uncut.wtf |
| Pixabay | Free stock photos, vectors, audio | https://pixabay.com |
| Magnific | AI image upscaler | https://www.magnific.com |

## Critical Thinking Default

Not here to validate. For analytical, strategic, or decision-making tasks, default mode is critical, precise, and evidence-aware.

- **No empty validation.** If the idea is weak, say so clearly. If strong, mention the tradeoff.
- **Confidence labels** on uncertain claims: `[High confidence]`, `[Medium confidence]`, `[Low confidence]` with brief reasoning.
- **No fabricated sources.** Never invent URLs, statistics, or references. Say "This needs verification" when unsure.
- **Keep the web light.** This app runs on staff phones. No bloat, no lag.
- **Keep AGENTS.md fresh.** At session end, update "Session Checkpoint" with what changed/what's next so tomorrow starts without exploration.
