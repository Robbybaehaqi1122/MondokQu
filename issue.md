# MondokQu — Issue Tracker

> Daftar issue hasil analisis codebase MondokQu.
> Setiap issue bisa dibuat sebagai GitHub Issue untuk dilacak dan diselesaikan.

---

## 🚨 High Priority — Security & Critical Bugs

### H1. Missing Rate Limiting pada Login, Register, dan Reset Password

**Lokasi:** `app/Modules/Auth/Routes/web.php`
- `POST /login` (line 28) — tidak ada throttle
- `POST /register` (line 18) — tidak ada throttle
- `POST /reset-password` (line 41) — tidak ada throttle

**Masalah:** Endpoint login, register, dan reset password tidak memiliki rate limiting. Brute-force attack dapat dilakukan tanpa batasan.

**Saran:** Tambahkan middleware `throttle:5,1` pada login/reset-password, `throttle:3,1` pada register.

**Severity:** 🔴 HIGH

---

### H2. Authorization Kosong pada Role & Permission Management

**Lokasi:**
- `app/Http/Controllers/Admin/RoleManagementController.php`
- `app/Http/Controllers/Admin/PermissionManagementController.php`

**Masalah:** Semua method (index, store, update, delete) tidak memiliki pengecekan authorization. `authorize()` di FormRequest mengembalikan `true` unconditionally. User manapun bisa membuat/mengubah role dan permission.

**Severity:** 🔴 HIGH

---

### H3. Authorization Kosong pada Payment/Invoice CRUD

**Lokasi:** `app/Http/Controllers/SantriPaymentController.php`

**Masalah:** `StoreSantriInvoiceRequest`, `UpdateSantriInvoiceRequest`, `StoreSantriPaymentRequest`, `UpdateSantriPaymentRequest` — semua mengembalikan `authorize() = true`. Tidak ada permission check. User manapun dalam satu tenant bisa membuat invoice, mencatat pembayaran, mengedit, dan menghapus.

**Severity:** 🔴 HIGH

---

### H4. `APP_DEBUG=true` di Production

**Lokasi:** `.env`

**Masalah:** `APP_DEBUG=true` akan menampilkan stack trace, environment variables, dan informasi sensitif lainnya ke user saat terjadi error di production.

**Saran:** Set `APP_DEBUG=false` di production.

**Severity:** 🔴 HIGH

---

### H5. Default Password Hardcoded (`Password123!`)

**Lokasi:** `.env.example` (line 72), `config/auth.php` (line 117)

**Masalah:** Default password `Password123!` disimpan di version control (`.env.example`). Password ini lemah dan diketahui publik. Jika developer tidak menggantinya, user baru memiliki password yang mudah ditebak.

**Saran:** Hapus dari `.env.example`, generate random password saat user creation, atau gunakan placeholder `changeme`.

**Severity:** 🔴 HIGH

---

### H6. `destroyAll()` Activity Log — Bisa Hapus Semua Log Tanpa Otorisasi

**Lokasi:** `app/Http/Controllers/Admin/ActivityLogController.php` (lines 112-123)

**Masalah:** `destroyAll()` mengeksekusi `ActivityLog::query()->visibleTo($currentUser)->delete()` tanpa pengecekan authorization lebih lanjut. User manapun dalam tenant bisa menghapus seluruh history aktivitas tanpa konfirmasi.

**Saran:** Tambahkan `$this->authorize('deleteAll', ActivityLog::class)` dan password confirmation step.

**Severity:** 🔴 HIGH

---

### H7. Room Auto-Creation Tanpa Permission Check

**Lokasi:** `app/Http/Controllers/SantriManagementController.php` (lines 489-502)

**Masalah:** Method `resolveRoomForSantri()` menggunakan `Room::query()->firstOrCreate()` yang secara otomatis membuat Room baru jika `room_name` tidak ditemukan. Ini terjadi tanpa pengecekan permission `manage kamar`.

**Saran:** Gunakan `firstOrFail` atau tambahkan gate authorization sebelum create.

**Severity:** 🔴 HIGH

---

### H8. Missing Index pada Foreign Key `santri_payments.santri_invoice_id`

**Lokasi:** `database/migrations/2026_05_03_100100_create_santri_payments_table.php`

**Masalah:** `santri_invoice_id` adalah foreign key tanpa index. Saat invoice dihapus, database harus scan semua baris untuk cascade delete — menyebabkan performance issue pada dataset besar.

**Saran:** Tambahkan `$table->index('santri_invoice_id')`.

**Severity:** 🔴 HIGH

---

### H9. Missing Composite Index pada `activity_logs` (Polymorphic Lookup)

**Lokasi:** `database/migrations/2026_04_05_210000_create_activity_logs_table.php`

**Masalah:** Tidak ada composite index pada `[target_type, target_id]`. Query pencarian aktivitas berdasarkan target tertentu akan full table scan.

**Saran:** Tambahkan `$table->index(['target_type', 'target_id'])`.

**Severity:** 🔴 HIGH

---

## 🟡 Medium Priority

### M1. Semua Data-Modifying Routes Tidak Punya Throttling

**Lokasi:** `routes/web.php` (lines 42-191)

**Masalah:** Tidak ada middleware `throttle` pada route manapun selain login/register. Attacker yang sudah authenticated bisa membanjiri endpoint secara bebas.

**Saran:** Tambahkan `throttle:60,1` pada group `auth` di `routes/web.php`.

---

### M2. `SESSION_ENCRYPT=false`

**Lokasi:** `config/session.php`, `.env`

**Masalah:** Session data disimpan di database dalam bentuk plaintext. Jika database compromised, data session bisa dibaca langsung.

**Saran:** Set `SESSION_ENCRYPT=true`.

---

### M3. `SESSION_SECURE_COOKIE` Tidak Dikonfigurasi

**Lokasi:** `config/session.php` (line 172)

**Masalah:** `SESSION_SECURE_COOKIE` tidak di-set di `.env` / `.env.example`. Di production tanpa HTTPS, cookie bisa dikirim lewat koneksi tidak terenkripsi.

**Saran:** Set `SESSION_SECURE_COOKIE=true` di production.

---

### M4. Soft Deletes Tidak Ada di Seluruh Tabel

**Lokasi:** Semua migration files

**Masalah:** Tidak ada satupun tabel yang menggunakan `softDeletes()`. Data yang terhapus secara permanen akan menghilangkan audit trail, terutama untuk data finansial (invoices, payments) dan data santri.

**Saran:** Tambahkan `$table->softDeletes()` pada tabel: `santris`, `users`, `rooms`, `santri_invoices`, `attendance_activities`, `tenants`.

---

### M5. `submitted_by` Menggunakan `cascadeOnDelete()` — Risk Audit Trail

**Lokasi:** `database/migrations/2026_05_23_120000_create_santri_payment_confirmations_table.php`

**Masalah:** `submitted_by` foreign key menggunakan `cascadeOnDelete()`. Jika user dihapus, semua payment confirmation submissions-nya ikut terhapus — menghilangkan audit trail.

**Saran:** Ganti ke `nullOnDelete()`.

---

### M6. Tabler UI Pakai `@latest` — Risk Breaking Change

**Lokasi:** `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php`

**Masalah:** CSS dan JS Tabler menggunakan `@latest`. Auto-upgrade bisa memecah UI tanpa peringatan saat deploy.

**Saran:** Pin versi spesifik Tabler.

---

### M7. Tidak Ada API Layer

**Lokasi:** N/A — `routes/api.php` tidak ada

**Masalah:** Aplikasi hanya menyediakan session-based web routes. Tidak ada API endpoint untuk mobile app atau SPA.

**Saran:** Buat `routes/api.php` dengan token authentication (Laravel Sanctum) dan throttle 100:1.

---

### M8. Impersonation Route Tidak Punya Throttling

**Lokasi:** `app/Modules/Saas/Routes/web.php` (line 25)

**Masalah:** `POST /saas/tenants/{tenant}/users/{user}/impersonate` tidak punya throttle. Abuse impersonasi bisa terjadi.

**Saran:** Tambahkan `throttle:10,1`.

---

### M9. Activity Log Export Tidak Punya Throttling

**Lokasi:** `routes/web.php` (line 86)

**Masalah:** `GET /admin/activity-logs/export` tidak punya throttle. Superadmin yang akunnya compromised bisa mengekspor data sensitif dalam jumlah besar.

**Saran:** Tambahkan `throttle:5,1` atau password confirmation.

---

### M10. Float Precision pada Monetary Comparison

**Lokasi:** `app/Http/Controllers/SantriPaymentController.php` (lines 412-419, 491-498)

**Masalah:** Perbandingan uang menggunakan `(float)` casting. Floating-point arithmetic bisa menyebabkan error di edge case (misal: 0.1 + 0.2 != 0.3).

**Saran:** Simpan nilai dalam integer cents (sen), atau gunakan `bccomp()`.

---

### M11. `proof_path` NOT NULL — Tidak Support Cash Payment

**Lokasi:** `database/migrations/2026_05_23_120000_create_santri_payment_confirmations_table.php`

**Masalah:** `proof_path` adalah NOT NULL. Pembayaran tunai yang tidak memiliki bukti upload tidak bisa dimasukkan.

**Saran:** Buat nullable: `$table->string('proof_path')->nullable()->change()`.

---

### M12. Missing Casts pada Model

**Lokasi:**
- `app/Models/Santri.php` — missing `'entry_year' => 'integer'`
- `app/Models/SantriInvoice.php` — missing `'period_month' => 'integer'`, `'period_year' => 'integer'`
- `app/Models/Room.php` — missing `'capacity' => 'integer'`

**Masalah:** Kolom integer di database dikembalikan sebagai string oleh Eloquent, berpotensi menyebabkan type-strict comparison bugs.

---

## 🟢 Low Priority — Technical Debt

### L1. Missing Inverse Relationships

**Lokasi:** `app/Models/User.php`, `app/Models/Santri.php`, `app/Models/Tenant.php`

**Masalah:** Banyak model yang memiliki `BelongsTo` ke User/Santri/Tenant tetapi tidak memiliki inverse `HasMany`. Contoh: `Santri` tidak punya `leaveRequests()`, `User` tidak punya `recordedPayments()`.

**Saran:** Tambahkan inverse relationships yang diperlukan.

---

### L2. `room_name` Redundan di Tabel `santris`

**Lokasi:** `database/migrations/2026_04_18_100000_add_master_fields_to_santris_table.php`

**Masalah:** Kolom `room_name` (varchar) masih ada di tabel `santris` setelah `room_id` FK ditambahkan. Dual source of truth — jika berbeda, tidak diketahui mana yang benar.

**Saran:** Backfill data, lalu drop kolom `room_name`.

---

### L3. Guardian Data Duplikasi

**Lokasi:** `santris` table (guardian_name, guardian_phone_number) dan `santri_guardians` table

**Masalah:** Data wali tersimpan di dua tempat (denormalized di `santris` dan normalized di `santri_guardians`). Jika wali update nomor telepon, harus diubah di dua tempat.

**Saran:** Treat `santris.guardian_*` sebagai legacy, rencanakan penghapusan.

---

### L4. Invoice Number Generation Race Condition

**Lokasi:** `app/Http/Controllers/SantriPaymentController.php` (lines 634-646)

**Masalah:** `count() + 1` untuk generate nomor invoice tidak atomic. Di concurrent request, nomor bisa duplikat.

**Saran:** Gunakan UUID, DB sequence, atau `lockForUpdate()`.

---

### L5. Tidak Ada `loading="lazy"` pada Gambar

**Lokasi:** Semua file view

**Masalah:** Tidak ada gambar yang menggunakan atribut `loading="lazy"`, menyebabkan semua gambar di-load di awal.

---

### L6. Inline `onerror` JS Handler — CSP Incompatible

**Lokasi:** `resources/views/santri/show.blade.php` (line 33)

**Masalah:** Menggunakan `onerror="this.classList.add('d-none')"` yang tidak kompatibel dengan Content-Security-Policy jika inline scripts diblokir.

**Saran:** Gunakan `addEventListener` via JavaScript terpisah.

---

### L7. Email Berubah Tapi Tidak Kirim Verifikasi

**Lokasi:** `app/Http/Controllers/ProfileController.php` (lines 54-56)

**Masalah:** Saat user mengganti email, `email_verified_at` di-set ke null tetapi tidak ada email verifikasi yang dikirim. User stuck dengan email unverified.

**Saran:** Kirim verification notification saat email berubah.

---

### L8. Password Reset Tidak Random

**Lokasi:** `app/Http/Controllers/Admin/UserManagementController.php` (lines 623-629)

**Masalah:** `updatePassword()` mereset password ke `config('auth.default_user_password')`. Password default yang lemah.

**Saran:** Generate random cryptographically secure password, atau minta admin set password baru.

---

### L9. Missing Factory untuk Beberapa Model

**Lokasi:** `SantriPaymentConfirmation`, `ActivityLog`, `SantriGuardian`

**Masalah:** Model-model ini tidak memiliki `HasFactory` trait, sehingga tidak bisa dibuat instance-nya di test.

---

### L10. Welcome Page Inline Tailwind (~50KB)

**Lokasi:** `resources/views/welcome.blade.php`

**Masalah:** Seluruh CSS Tailwind v4.0.7 di-hardcode di `<style>` block — tidak cacheable, tidak maintainable, membesarkan response size.

---

## 💡 Feature Requests

### F1. API Layer untuk Mobile App
Buat REST API dengan token authentication (Laravel Sanctum) untuk mendukung mobile client.

### F2. Soft Deletes untuk Financial Records
Implementasi soft deletes untuk `santri_invoices`, `santri_payments`, `santri_payment_confirmations` untuk menjaga audit trail keuangan.

### F3. Migrasi ke Integer-Cents untuk Monetary Values
Simpan semua nilai uang dalam integer (sen) untuk menghindari floating-point precision issues.

### F4. Email Verification on Email Change
Kirim notifikasi verifikasi saat user mengganti alamat email.

### F5. Queue Driver Upgrade
Migrasi dari `database` queue driver ke `redis` untuk performance yang lebih baik.

### F6. Cache Driver Upgrade
Migrasi dari `database` cache driver ke `redis` atau `file`.

### F7. Export Download Re-validation
Cek status subscription saat download export, bukan hanya saat generate.

---

## ⚠️ Catatan Penting

Beberapa temuan di atas memiliki **dampak keamanan serius** (H1-H9, M1-M5, M8-M9) dan sebaiknya ditangani sebelum aplikasi digunakan di production.
