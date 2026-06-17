# Panduan Deployment Mondok Qu

## Daftar Isi

1. [Persyaratan Server](#1-persyaratan-server)
2. [Opsi Deployment](#2-opsi-deployment)
3. [Deployment dengan Docker](#3-deployment-dengan-docker)
4. [Deployment Manual (Non-Docker)](#4-deployment-manual-non-docker)
5. [CI/CD Pipeline](#5-cicd-pipeline)
6. [Konfigurasi Environment](#6-konfigurasi-environment)
7. [Manajemen Queue & Scheduler](#7-manajemen-queue--scheduler)
8. [Backup & Restore](#8-backup--restore)
9. [Monitoring & Logging](#9-monitoring--logging)
10. [Troubleshooting](#10-troubleshooting)
11. [Checklist Deployment](#11-checklist-deployment)

---

## 1. Persyaratan Server

### Minimum Production
| Komponen | Spesifikasi |
|---|---|
| CPU | 2 core |
| RAM | 4 GB |
| Storage | 20 GB + storage untuk upload/file |
| OS | Ubuntu 22.04+ / Debian 12 / Rocky Linux 9 |

### Software Requirements
| Software | Versi | Keterangan |
|---|---|---|
| PHP | 8.2+ | Dengan ekstensi: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, curl, gd |
| Composer | 2.x | PHP dependency manager |
| MySQL | 8.0+ | Database utama |
| Node.js | 20+ | Untuk build frontend (hanya saat deploy) |
| NPM | 10+ | Package manager frontend |
| Nginx | 1.24+ | Web server (atau Apache) |
| Supervisor | 4.x | Process manager untuk queue worker |
| Redis | 7+ | Opsional, untuk cache/queue yang lebih cepat |

---

## 2. Opsi Deployment

Ada 3 opsi deployment yang didukung:

| Opsi | Cocok Untuk | Tingkat Kesulitan |
|---|---|---|
| **Docker Compose** | Production / Staging full-container | Sedang |
| **Manual (Bare-metal)** | Server tradisional / existing infra | Mudah |
| **CI/CD GitHub Actions** | Auto-deploy dari GitHub | Mudah (setelah setup awal) |

---

## 3. Deployment dengan Docker

### 3.1 Prasyarat

- Docker Engine 24+ dan Docker Compose v2+
- Domain sudah terarah ke IP server

### 3.2 Setup

```bash
# Clone repositori
git clone https://github.com/username/mondok-qu.git
cd mondok-qu

# Copy environment file
cp .env.example .env

# Edit .env sesuai production
nano .env
```

### 3.3 Konfigurasi `.env` untuk Docker

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mondokqu.sch.id

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=mondok_qu
DB_USERNAME=mondok_qu
DB_PASSWORD=GantiPasswordKuat123!
DB_ROOT_PASSWORD=GantiRootPasswordKuat123!

# Port MySQL di host (biarkan 3307 agar tidak bentrok dengan MySQL lokal)
DB_PORT_EXTERNAL=3307

APP_PORT=80
```

> **Penting**: Jangan commit `.env` ke repositori. Gunakan GitHub Secrets untuk CI/CD.

### 3.4 Build & Jalankan

```bash
# Build image dan jalankan semua service
docker compose build
docker compose up -d

# Generate APP_KEY (hanya sekali)
docker exec mondok-qu-app php artisan key:generate

# Jalankan migration & seeder
docker exec mondok-qu-app php artisan migrate --seed

# Buat storage link
docker exec mondok-qu-app php artisan storage:link

# Lihat logs
docker compose logs -f
```

### 3.5 Service dalam Docker Compose

| Service | Container | Fungsi |
|---|---|---|
| `app` | `mondok-qu-app` | Nginx + PHP-FPM + Queue Worker (via Supervisor) |
| `db` | `mondok-qu-db` | MySQL 8.0 |
| `queue` | `mondok-qu-queue` | Queue worker dedicated (job background) |
| `scheduler` | `mondok-qu-scheduler` | Laravel scheduler (cron replacement) |

### 3.6 Perintah Berguna untuk Docker

```bash
# Restart semua service
docker compose restart

# Lihat status
docker compose ps

# Akses shell ke container app
docker exec -it mondok-qu-app sh

# Melihat log queue worker
docker compose logs -f queue

# Update aplikasi (pull & rebuild)
git pull origin main
docker compose build app
docker compose up -d --no-deps app queue scheduler

# Backup database
docker exec mondok-qu-db mysqldump -u mondok_qu -p mondok_qu > backup.sql
```

---

## 4. Deployment Manual (Non-Docker)

### 4.1 Setup Awal Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 + ekstensi
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl \
    php8.2-bcmath php8.2-gd php8.2-zip php8.2-imagick

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Install Nginx
sudo apt install -y nginx

# Install MySQL 8.0
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Node.js 20+
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor
sudo apt install -y supervisor
```

### 4.2 Clone & Konfigurasi Aplikasi

```bash
# Clone repositori
sudo mkdir -p /var/www/mondok-qu
sudo chown -R $USER:$USER /var/www/mondok-qu
git clone https://github.com/username/mondok-qu.git /var/www/mondok-qu
cd /var/www/mondok-qu

# Setup environment
cp .env.example .env
nano .env
# Isi APP_KEY, DB_*, MAIL_*, dll
# APP_KEY digenerate nanti

# Generate APP_KEY
php artisan key:generate

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Setup database
php artisan migrate --seed
php artisan storage:link
```

### 4.3 Konfigurasi Nginx

Buat file `/etc/nginx/sites-available/mondok-qu`:

```nginx
server {
    listen 80;
    server_name mondokqu.sch.id;
    root /var/www/mondok-qu/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log /var/log/nginx/mondok-qu-access.log;
    error_log  /var/log/nginx/mondok-qu-error.log error;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ \.env$ {
        deny all;
    }

    location /storage/ {
        expires max;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 20M;
}
```

```bash
# Aktifkan site
sudo ln -s /etc/nginx/sites-available/mondok-qu /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 4.4 Konfigurasi Supervisor (Queue Worker)

Buat file `/etc/supervisor/conf.d/mondok-qu-worker.conf`:

```ini
[program:mondok-qu-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mondok-qu/artisan queue:work --sleep=3 --tries=3 --timeout=900
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/mondok-qu/storage/logs/queue-worker.log
stdout_logfile_maxbytes=50MB
```

```bash
# Aktifkan supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mondok-qu-worker:*
```

### 4.5 Setup Scheduler (Cron)

```bash
# Buka crontab untuk user www-data
sudo crontab -u www-data -e
# Tambahkan baris berikut:
# * * * * * cd /var/www/mondok-qu && php artisan schedule:run >> /dev/null 2>&1
```

### 4.6 SSL dengan Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d mondokqu.sch.id
```

### 4.7 Deploy Update (Manual)

Gunakan script deploy yang sudah disediakan:

```bash
# Dari direktori aplikasi
bash deploy.sh production
```

Atau jalankan step-by-step:

```bash
php artisan down --retry=30
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

---

## 5. CI/CD Pipeline

### 5.1 CI Pipeline (`.github/workflows/ci.yml`)

Berjalan otomatis setiap push ke `main` / `develop` dan pull request ke `main`:

| Job | Deskripsi |
|---|---|
| `lint` | PHP Pint coding style check |
| `tests` | PHPUnit/Pest test suite (multi-versi PHP 8.2, 8.3, 8.4) |
| `security` | Security check dependencies |

### 5.2 CD Pipeline (`.github/workflows/deploy.yml`)

Berjalan otomatis setiap push ke `main`. Bisa juga di-trigger manual via GitHub UI.

**Persiapan GitHub Secrets:**

| Secret | Deskripsi |
|---|---|
| `DEPLOY_HOST` | IP address atau domain server |
| `DEPLOY_PORT` | SSH port (default: 22) |
| `DEPLOY_USER` | SSH username |
| `DEPLOY_SSH_KEY` | Private SSH key untuk akses server |
| `DEPLOY_PATH` | Path absolut ke direktori aplikasi (misal: `/var/www/mondok-qu`) |

### 5.3 Cara Setup SSH Key untuk Deploy

```bash
# Di server production
ssh-keygen -t ed25519 -f ~/.ssh/deploy-key -N ""
cat ~/.ssh/deploy-key.pub >> ~/.ssh/authorized_keys

# Tampilkan private key
cat ~/.ssh/deploy-key
```

Copy output private key ke GitHub Secrets `DEPLOY_SSH_KEY`.

---

## 6. Konfigurasi Environment

### 6.1 Variabel Wajib

| Variabel | Contoh Value | Keterangan |
|---|---|---|
| `APP_ENV` | `production` | Jangan pakai `local` di production |
| `APP_DEBUG` | `false` | WAJIB `false` di production |
| `APP_KEY` | (auto-generate) | Generate dengan `php artisan key:generate` |
| `APP_URL` | `https://mondokqu.sch.id` | URL lengkap aplikasi |
| `DB_CONNECTION` | `mysql` | Gunakan MySQL untuk production |
| `DB_HOST` | `127.0.0.1` | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `mondok_qu` | Nama database |
| `DB_USERNAME` | `mondok_qu` | User database |
| `DB_PASSWORD` | (strong password) | Password database |

### 6.2 Variabel Session & Security

| Variabel | Contoh Value | Keterangan |
|---|---|---|
| `SESSION_DRIVER` | `database` | Gunakan database, jangan file |
| `SESSION_ENCRYPT` | `true` | Enkripsi session |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only cookie |
| `SESSION_SAME_SITE` | `lax` | CSRF protection |

### 6.3 Variabel Queue & Cache

| Variabel | Contoh Value | Keterangan |
|---|---|---|
| `QUEUE_CONNECTION` | `database` | Default, bisa diganti `redis` |
| `CACHE_STORE` | `database` | Default, bisa diganti `redis` |

### 6.4 Variabel SaaS & Export

| Variabel | Contoh Value | Keterangan |
|---|---|---|
| `SAAS_TRIAL_DAYS` | `14` | Masa trial tenant baru |
| `SAAS_GRACE_DAYS` | `5` | Masa tenggang setelah subscription habis |
| `SAAS_DEFAULT_PLAN` | `trial` | Plan default tenant baru |
| `SAAS_ADMIN_WHATSAPP` | `085117511220` | Nomor WA admin untuk upgrade |
| `EXPORT_INLINE_THRESHOLD` | `5000` | Threshold export inline vs background |
| `EXPORT_RETENTION_DAYS` | `7` | Berapa lama file export disimpan |

### 6.5 Contoh `.env` Production Lengkap

```env
APP_NAME="Mondok Qu"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://mondokqu.sch.id

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mondok_qu
DB_USERNAME=mondok_qu
DB_PASSWORD=GantiPasswordKuat123!

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@mondokqu.sch.id
MAIL_PASSWORD=GantiEmailPassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mondokqu.sch.id
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local

EXPORT_INLINE_THRESHOLD=5000
EXPORT_DISK=local
EXPORT_RETENTION_DAYS=7

AUTH_DEFAULT_USER_PASSWORD=Password123!
PONPES_NAME="Mondok Qu"
PONPES_ADDRESS="Jl. Pendidikan No. 1"
PONPES_PHONE="(021) 1234-5678"
PONPES_EMAIL="info@mondokqu.sch.id"
PONPES_CITY="Kota Santri"

SAAS_TRIAL_DAYS=14
SAAS_GRACE_DAYS=5
SAAS_DEFAULT_PLAN=trial
SAAS_ADMIN_WHATSAPP=085117511220
SAAS_MAX_USERS=50
SAAS_MAX_SANTRI=200
SAAS_MAX_STORAGE_MB=1024

SANTRI_INVOICE_PERIOD_YEAR_FUTURE_LIMIT=5
```

---

## 7. Manajemen Queue & Scheduler

### 7.1 Queue Worker

Aplikasi menggunakan queue/database untuk background job:

- Export data besar
- Import santri
- Generate invoice bulanan
- Notifikasi

**Menjalankan worker:**

```bash
# Single worker
php artisan queue:work --sleep=3 --tries=3 --timeout=900

# Production: jalankan via Supervisor (2 process)
```

**Restart worker setelah deploy:**

```bash
php artisan queue:restart
```

**Memantau queue:**

```bash
# Lihat jumlah job pending
php artisan queue:monitor default

# Lihat failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Hapus semua failed jobs
php artisan queue:flush
```

### 7.2 Scheduler (Cron)

Pastikan cron entry berikut ada di server:

```cron
* * * * * cd /var/www/mondok-qu && php artisan schedule:run >> /dev/null 2>&1
```

**Jadwal task yang berjalan otomatis:**

| Waktu | Task | Fungsi |
|---|---|---|
| 00:30 daily | `saas:expire-subscriptions` | Mematikan akses tenant expired |
| 01:00 daily | `exports:prune` | Hapus file export kadaluarsa |
| 02:00 Sunday | `backup:tenant --all` | Backup semua tenant |
| 03:00 daily | `backups:prune` | Hapus backup lama |
| 04:00 daily | `komunikasi:purge-trash` | Bersihkan pesan terhapus |

---

## 8. Backup & Restore

### 8.1 Backup Database

```bash
# MySQL
mysqldump -u mondok_qu -p mondok_qu > backup_$(date +%Y%m%d).sql

# SQLite
cp database/database.sqlite backup_$(date +%Y%m%d).sqlite
```

### 8.2 Backup File Storage

```bash
# Backup folder storage (upload, foto, dll)
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/
```

### 8.3 Restore

```bash
# Restore database MySQL
mysql -u mondok_qu -p mondok_qu < backup.sql

# Restore storage files
tar -xzf storage_backup.tar.gz -C /
```

### 8.4 Backup Otomatis via Aplikasi

Tenant backup sudah tersedia secara built-in:

```bash
# Backup tenant tertentu
php artisan backup:tenant --tenant=1

# Backup semua tenant
php artisan backup:tenant --all
```

---

## 9. Monitoring & Logging

### 9.1 Log Aplikasi

```bash
# Laravel log (single file)
tail -f storage/logs/laravel.log

# Queue worker log
tail -f storage/logs/queue-worker.log

# Backup scheduler log
tail -f storage/logs/backup-schedule.log
```

### 9.2 Nginx Logs

```bash
tail -f /var/log/nginx/mondok-qu-access.log
tail -f /var/log/nginx/mondok-qu-error.log
```

### 9.3 Health Check Endpoint

Aplikasi memiliki endpoint `/up` untuk health check:

```bash
curl https://mondokqu.sch.id/up
# Response: {"status":"OK"}
```

### 9.4 Resource Monitoring

```bash
# Cek queue worker
supervisorctl status

# Cek PHP-FPM
php-fpm8.2 -t

# Cek disk usage
df -h

# Cek memory
free -h
```

---

## 10. Troubleshooting

### 10.1 White Screen / 500 Error

```bash
# Cek Laravel log
tail -100 storage/logs/laravel.log

# Cek Nginx error log
tail -100 /var/log/nginx/error.log

# Cek PHP-FPM
sudo systemctl status php8.2-fpm

# Pastikan permission storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 10.2 Queue Worker Tidak Jalan

```bash
# Cek status Supervisor
sudo supervisorctl status

# Restart worker
sudo supervisorctl restart mondok-qu-worker:*

# Cek log worker
tail -100 storage/logs/queue-worker.log
```

### 10.3 Login Tidak Bisa / Session Error

```bash
# Pastikan session table sudah termigrasi
php artisan migrate --force

# Cache config
php artisan config:cache

# Pastikan SESSION_DRIVER terkonfigurasi
# Cek .env: SESSION_DRIVER=database
```

### 10.4 Upload Foto/Dokumen Tidak Muncul

```bash
# Pastikan storage link sudah dibuat
php artisan storage:link

# Cek permission
ls -la public/storage
# Harusnya symlink ke ../../storage/app/public

# Cek webserver bisa read
sudo -u www-data ls -la storage/app/public/
```

### 10.5 Halaman Subscription Expired Terus Muncul

```bash
# Pastikan APP_URL sudah benar di .env
# Cek tenant status
php artisan tinker
# Tenant::where('id', 1)->value('subscription_status');

# Jalankan expire subscriptions
php artisan saas:expire-subscriptions

# Atau manual update
php artisan tinker
# $t = Tenant::find(1);
# $t->update(['subscription_status' => 'active', 'subscription_ends_at' => now()->addYear()]);
```

### 10.6 Email Tidak Terkirim

```bash
# Cek konfigurasi MAIL_* di .env
# Test dengan tinker
php artisan tinker
# Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Cek log mail (jika MAIL_MAILER=log)
tail -50 storage/logs/laravel.log
```

---

## 11. Checklist Deployment

### Sebelum Deploy

- [ ] Semua perubahan sudah di-commit dan di-push ke `main`
- [ ] Semua test sudah lulus (CI pipeline hijau)
- [ ] `.env` production sudah siap (APP_KEY, DB, MAIL, SAAS, dll)
- [ ] Backup database terakhir sudah dibuat
- [ ] Domain sudah terarah ke IP server
- [ ] SSL certificate sudah aktif (Let's Encrypt)

### Saat Deploy

- [ ] Maintenance mode aktif (pastikan ada halaman 503 kustom)
- [ ] Code ter-pull dari repositori
- [ ] Composer install --no-dev berhasil
- [ ] Frontend build sukses
- [ ] Migration berjalan tanpa error
- [ ] Config, route, view cache sudah di-rebuild
- [ ] Queue worker sudah di-restart
- [ ] Maintenance mode non-aktif

### Setelah Deploy

- [ ] Login superadmin berhasil
- [ ] Dashboard SaaS bisa diakses
- [ ] Bisa login sebagai admin tenant
- [ ] Data santri tampil normal
- [ ] Halaman tenant expired redirect sesuai
- [ ] Cek logs tidak ada error baru
- [ ] Queue worker berjalan (supervisorctl status)
- [ ] Scheduler berjalan (cek storage/logs/*.log)

---

## Referensi

- **Dokumentasi Laravel**: https://laravel.com/docs/12.x/deployment
- **Laravel Forge**: https://forge.laravel.com (deployment managed)
- **GitHub Actions**: https://docs.github.com/en/actions
- **Docker Compose**: https://docs.docker.com/compose/
