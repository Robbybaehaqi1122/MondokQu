# Mondok Qu

Mondok Qu adalah aplikasi SaaS operasional pondok berbasis Laravel. Aplikasi ini memakai model multi-tenant, role/permission, subscription gate, billing note, activity log, dan modul awal untuk manajemen santri.

## Stack Utama

- PHP 8.2+
- Laravel 12
- SQLite/MySQL/PostgreSQL via konfigurasi Laravel
- Spatie Laravel Permission
- Blade, Tailwind CSS, Alpine.js, Tabler UI
- Pest untuk test
- Vite untuk asset frontend

## Setup Lokal

1. Install dependency PHP dan JavaScript.

```bash
composer install
npm install
```

2. Siapkan file environment.

```bash
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell, gunakan `Copy-Item .env.example .env` bila `cp` tidak tersedia.

3. Konfigurasi database (SQLite default atau MySQL/PostgreSQL via .env). Untuk MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mondok_qu
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan migration dan seeder.

```bash
php artisan migrate --seed
```

5. Buat symbolic link storage agar avatar dan foto santri bisa diakses dari public path.

```bash
php artisan storage:link
```

6. Jalankan aplikasi.

```bash
php artisan serve
npm run dev
```

Jika PowerShell memblokir `npm`, gunakan:

```powershell
npm.cmd run dev
```

## Akun Demo Seeder

Seeder membuat tenant demo dan dua akun utama:

| Role | Email | Username | Password |
| --- | --- | --- | --- |
| Superadmin | `admin@example.com` | `superadmin` | `password` |
| Admin Pondok Demo | `pondok-admin@example.com` | `adminpondok` | `password` |

Superadmin mengelola panel SaaS, tenant, subscription, billing, user, role, permission, dan bisa melihat data lintas tenant. Admin Pondok Demo berada di tenant `Pondok Demo` dan dipakai untuk alur operasional pondok.

## Seed Ulang Data

Untuk reset database lokal dan mengisi ulang data demo:

```bash
php artisan migrate:fresh --seed
```

Seeder yang penting:

- `RoleSeeder`: membuat role `Superadmin`, `Admin`, `Pengurus`, `Bendahara`, `Musyrif/Ustadz`, dan `Wali Santri`.
- `PermissionSeeder`: membuat permission sistem dan mapping awal per role.
- `DatabaseSeeder`: membuat tenant demo, akun Superadmin, dan akun Admin Pondok Demo.

## Menjalankan Test

Jalankan seluruh test:

```bash
composer test
```

Atau langsung lewat Artisan:

```bash
php artisan test
```

Jalankan file test tertentu:

```bash
php artisan test tests/Feature/Saas/TenantManagementTest.php
```

Test memakai SQLite in-memory sesuai `phpunit.xml`, jadi tidak menyentuh database lokal `.env`.

## Catatan Internal Teknis

### Multi-tenancy Global Scope

Model yang memiliki data milik tenant memakai trait `App\Models\Concerns\BelongsToTenant`. Trait ini memasang global scope tenant secara otomatis:

- User tenant biasa hanya melihat data dengan `tenant_id` miliknya.
- Superadmin tetap bisa melihat data lintas tenant.
- Query tanpa user login tidak mengembalikan data tenant.
- Jika alur internal benar-benar perlu lintas tenant, gunakan `withoutTenantScope()` secara eksplisit.

Contoh:

```php
Santri::query()->get(); // otomatis tenant aktif
Santri::query()->withoutTenantScope()->get(); // hanya untuk alur tepercaya
```

Catatan: validasi database seperti `Rule::unique()` tetap perlu filter `tenant_id` eksplisit, karena rule tersebut memakai database query builder, bukan Eloquent global scope.

### Lifecycle Subscription Otomatis

Command berikut menyelaraskan status tenant yang sudah melewati masa akses:

```bash
php artisan saas:expire-subscriptions
```

Perilaku command:

- Tenant `trial` dengan `trial_ends_at` yang sudah lewat menjadi `expired`.
- Tenant `active` dengan `subscription_ends_at` yang sudah lewat masuk `grace` sesuai `SAAS_GRACE_DAYS`.
- Tenant `grace` dengan `grace_ends_at` yang sudah lewat menjadi `expired`.
- Setiap perubahan dicatat ke subscription history dan activity log.

Scheduler Laravel sudah mendaftarkan command ini setiap hari pukul `00:30` di `routes/console.php`. Agar scheduler berjalan di server, pasang cron berikut:

```cron
* * * * * cd /path/to/mondok-qu && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path/to/mondok-qu` dengan path aplikasi di server.

### Validasi Nomor Telepon Indonesia

Validasi nomor telepon Indonesia dipusatkan di rule `App\Rules\IndonesiaPhoneNumber`. Gunakan rule ini untuk field yang harus menerima nomor Indonesia dengan awalan `0`, `62`, atau `+62`.

Contoh:

```php
'guardian_phone_number' => ['nullable', 'string', 'max:20', new IndonesiaPhoneNumber],
```

Jika format nomor berubah, ubah regex di rule tersebut saja.

## Checklist Deploy Singkat

Gunakan checklist ini saat deploy ke server production atau staging:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan juga:

- Backup database dibuat sebelum deploy.
- `.env` production berisi `APP_KEY`, koneksi database, mail, queue, `SAAS_*`, dan `SANTRI_INVOICE_PERIOD_YEAR_FUTURE_LIMIT` yang benar.
- Cron Laravel scheduler aktif: `* * * * * cd /path/to/mondok-qu && php artisan schedule:run >> /dev/null 2>&1`.
- Queue worker aktif jika production memakai queue database/redis untuk email atau pekerjaan latar:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=900
```

Di production, jalankan worker lewat process manager seperti Supervisor atau systemd agar otomatis hidup kembali setelah restart atau error.
- Permission folder `storage` dan `bootstrap/cache` bisa ditulis oleh user web server.
- Setelah deploy, jalankan smoke test: login superadmin, buka dashboard SaaS, login user tenant aktif, dan pastikan tenant expired diarahkan ke halaman status akses.

## Catatan `.env` Penting

Nilai lokal boleh berbeda, tapi beberapa key ini penting untuk Mondok Qu:

```env
APP_NAME="Mondok Qu"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
# DB_DATABASE boleh dikosongkan/dikomentari untuk memakai database/database.sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@mondokqu.test"
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local

AUTH_DEFAULT_USER_PASSWORD=Password123!
SAAS_TRIAL_DAYS=14
SAAS_GRACE_DAYS=5
SAAS_DEFAULT_PLAN=trial
SANTRI_INVOICE_PERIOD_YEAR_FUTURE_LIMIT=5
```

Penjelasan singkat:

- `MAIL_MAILER=log` aman untuk lokal karena email verifikasi dan reset password masuk ke log, bukan dikirim sungguhan.
- `AUTH_DEFAULT_USER_PASSWORD` dipakai saat admin mereset password user dari panel.
- `SAAS_TRIAL_DAYS`, `SAAS_GRACE_DAYS`, dan `SAAS_DEFAULT_PLAN` mengatur default trial dan subscription tenant baru.
- Upload avatar dan foto santri disimpan di disk `public`, jadi `php artisan storage:link` tetap diperlukan.
- Jangan commit file `.env` karena bisa berisi password database, mail credential, atau secret production.

## Alur Operasional Singkat

1. Login sebagai Superadmin.
2. Buat tenant baru dari menu SaaS.
3. Opsional buat akun owner/admin tenant saat provisioning tenant.
4. Catat billing note dari menu SaaS.
5. Jika pembayaran harus langsung mengaktifkan akses, centang opsi aktivasi subscription pada billing note.
6. Login sebagai Admin Pondok untuk mengelola user tenant dan data santri.

## Command Harian

```bash
php artisan serve
npm run dev
composer test
php artisan migrate:fresh --seed
```
