# Klinik Gigi Senyum Sehat — Full-Stack Demo (React + Laravel + Filament)

Website dan sistem reservasi online terpadu untuk klinik gigi. Dirancang untuk mendemokan alur lengkap: pasien melakukan booking di website, data langsung masuk ke admin panel dalam status *Menunggu verifikasi*, pasien mengonfirmasi via WhatsApp, dan resepsionis/admin memverifikasi jadwal dengan satu klik.

---

## Arsitektur Sistem

- **Frontend (Website Pasien):** React 18, Vite, Tailwind CSS, Lucide Icons. Berada di root `/`.
- **Backend API:** Laravel 11/12 REST API under `/api`.
- **Admin Panel:** Filament v3 under `/admin`.
- **Database:** MySQL 8.0 dengan locking transaksi dan index unik `slot_key` untuk mencegah *double-booking*.
- **Timezone:** `Asia/Jakarta` (WIB).

---

## 1. Menjalankan di Lokal (Local Development)

### Prasyarat
- PHP 8.3+ beserta ekstensi: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `intl`, `gd`
- Composer 2.x
- Node.js 18+ (disarankan Node 20+)
- MySQL Server 8.0

---

### Langkah Setup Backend (Laravel + Filament)

1. Masuk ke direktori `backend/`:
   ```bash
   cd backend
   composer install
   ```

2. Konfigurasi file `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Pastikan pengaturan database di `.env`:
   ```ini
   APP_NAME="Klinik Gigi Senyum Sehat"
   APP_ENV=local
   APP_DEBUG=true
   APP_TIMEZONE=Asia/Jakarta
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=klinik_gigi
   DB_USERNAME=root
   DB_PASSWORD=root

   ADMIN_EMAIL=admin@kliniksenyumsehat.com
   ADMIN_PASSWORD=admin123
   ```

3. Buat database MySQL & jalankan migration + seeder:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS klinik_gigi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   php artisan migrate:fresh --seed
   ```

4. Jalankan automated test suite untuk memastikan semua fungsi valid:
   ```bash
   php artisan test
   ```

5. Jalankan backend development server:
   ```bash
   php artisan serve --port=8000
   ```
   - API endpoint: `http://localhost:8000/api`
   - Admin panel: `http://localhost:8000/admin` (Login: `admin@kliniksenyumsehat.com` / `admin123`)

---

### Langkah Setup Frontend (React + Vite)

1. Kembali ke direktori root proyek:
   ```bash
   cd ..
   npm install
   ```

2. Buat file `.env.local` di root proyek untuk menyambungkan frontend ke API backend:
   ```ini
   VITE_API_URL=http://localhost:8000/api
   ```
   *(Catatan: Jika `VITE_API_URL` tidak diisi atau dihapus, frontend akan otomatis kembali menggunakan mock data statis).*

3. Jalankan development server:
   ```bash
   npm run dev
   ```
   Akses website di: `http://localhost:5173/`

---

## 2. Panduan Deployment VPS (Single Domain)

Sesuai spesifikasi PRD, seluruh aplikasi dideploy pada **1 VPS** dengan domain yang sama:
- Frontend React SPA diakses pada `/`
- Backend API diakses pada `/api`
- Admin panel Filament diakses pada `/admin`

### A. Persiapan Server VPS (Ubuntu 24.04 LTS)

1. Update package dan install tools pendukung:
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install -y nginx mysql-server certbot python3-certbot-nginx git curl unzip
   ```

2. Install PHP 8.3 & ekstensi yang dibutuhkan:
   ```bash
   sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl \
       php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl \
       php8.3-gd php8.3-sqlite3
   ```

3. Install Composer & Node.js 20:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt install -y nodejs
   ```

---

### B. Konfigurasi MySQL Server di VPS

1. Masuk ke MySQL console:
   ```bash
   sudo mysql
   ```
2. Buat user dan database untuk produksi:
   ```sql
   CREATE DATABASE klinik_gigi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'klinik_user'@'localhost' IDENTIFIED BY 'PasswordProduksiKuat123#';
   GRANT ALL PRIVILEGES ON klinik_gigi.* TO 'klinik_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

---

### C. Deploy Source Code di Server

1. Letakkan kode proyek di `/var/www/klinik`:
   ```bash
   sudo git clone <URL_REPOSITORY> /var/www/klinik
   cd /var/www/klinik
   ```

2. **Build Frontend React:**
   Buat file `.env.production` di root `/var/www/klinik`:
   ```ini
   VITE_API_URL=/api
   ```
   Lakukan build aplikasi:
   ```bash
   npm install --omit=dev
   npm run build
   ```
   Output file HTML/CSS/JS statis akan berada di `/var/www/klinik/dist/`.

3. **Setup Backend Laravel:**
   ```bash
   cd /var/www/klinik/backend
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env` produksi:
   ```ini
   APP_NAME="Klinik Gigi Senyum Sehat"
   APP_ENV=production
   APP_DEBUG=false
   APP_TIMEZONE=Asia/Jakarta
   APP_URL=https://senyumsehat.example.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=klinik_gigi
   DB_USERNAME=klinik_user
   DB_PASSWORD=PasswordProduksiKuat123#

   ADMIN_EMAIL=admin@senyumsehat.example.com
   ADMIN_PASSWORD=GantiDenganPasswordAdminYangKuat!
   ```

4. Jalankan migration & seeder:
   ```bash
   php artisan migrate --force --seed
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. Atur permission folder storage:
   ```bash
   sudo chown -R www-data:www-data /var/www/klinik/backend/storage /var/www/klinik/backend/bootstrap/cache
   sudo chmod -R 775 /var/www/klinik/backend/storage /var/www/klinik/backend/bootstrap/cache
   ```

---

### D. Konfigurasi Nginx (Unified Single Domain)

Buat file konfigurasi Nginx:
```bash
sudo nano /etc/nginx/sites-available/klinik.conf
```

Isi konfigurasi berikut:
```nginx
server {
    listen 80;
    server_name senyumsehat.example.com; # Ganti dengan domain Anda

    # Direktori frontend static build
    root /var/www/klinik/dist;
    index index.html;

    charset utf-8;
    client_max_body_size 20M;

    # 1. Routing Frontend React (SPA)
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Static assets cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    # 2. Routing Backend API & Filament Admin ke Laravel public/index.php
    location ^~ /api {
        root /var/www/klinik/backend/public;
        try_files $uri $uri/ /index.php?$query_string;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/klinik/backend/public/index.php;
            include fastcgi_params;
        }
    }

    location ^~ /admin {
        root /var/www/klinik/backend/public;
        try_files $uri $uri/ /index.php?$query_string;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/klinik/backend/public/index.php;
            include fastcgi_params;
        }
    }

    # Livewire internal endpoints
    location ^~ /livewire {
        root /var/www/klinik/backend/public;
        try_files $uri $uri/ /index.php?$query_string;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/klinik/backend/public/index.php;
            include fastcgi_params;
        }
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/klinik.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### E. Konfigurasi SSL / HTTPS Gratis (Let's Encrypt)

```bash
sudo certbot --nginx -d senyumsehat.example.com
```
Certbot akan memperbarui sertifikat SSL secara otomatis dan mengarahkan seluruh traffic HTTP ke HTTPS secara aman.

---

### F. Konfigurasi Cron Scheduler (Otomatisasi Hold & Reset)

PRD mengharuskan:
1. `bookings:expire` berjalan setiap menit untuk melepaskan slot yang melewati batas hold (`hold_minutes`).
2. `demo:reset` berjalan setiap malam pukul 03.00 WIB untuk mengembalikan database ke kondisi awal demo.

Buka crontab user `www-data`:
```bash
sudo crontab -u www-data -e
```
Tambahkan baris berikut:
```cron
* * * * * cd /var/www/klinik/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## 3. Checklist Penerimaan (Acceptance Checklist)

- [x] Pasien melakukan booking di website, data langsung masuk ke admin panel dalam status *Menunggu verifikasi*.
- [x] Permintaan ganda pada dokter, tanggal, dan jam yang sama menghasilkan status 409 pada salah satu request (*automated test verified*).
- [x] Booking pending menahan slot selama `hold_minutes` (default 120 menit) dan otomatis terlepas jika kedaluwarsa.
- [x] Aksi tombol "Verifikasi" di admin panel mengubah status menjadi *Terverifikasi* dan slot tetap terkunci.
- [x] Aksi tombol "Batalkan" melepaskan slot kembali ke jadwal tersedia.
- [x] Aksi tombol "Chat Pasien" membuka `wa.me` dengan pesan terformat otomatis.
- [x] Perubahan harga layanan atau jadwal dokter di Admin Panel langsung tampil di website.
- [x] Rate limit aktif: maksimal 5 request booking per jam per IP (status 429), dan maksimal 2 pending booking aktif per nomor WhatsApp.
- [x] Tidak ada endpoint publik yang mengekspos data pribadi pasien, dan produksi berjalan dengan `APP_DEBUG=false`.
- [x] Command `php artisan demo:reset` berhasil merestorasi seluruh data demo dengan tanggal relatif terkini.
- [x] Frontend tetap dapat berjalan penuh dengan mock data saat `VITE_API_URL` tidak ditentukan.
