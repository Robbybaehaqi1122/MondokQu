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

3. Jika memakai SQLite lokal, buat file database.

```bash
mkdir -p database
touch database/database.sqlite
```

Di Windows PowerShell:

```powershell
New-Item -ItemType File -Force database/database.sqlite
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
