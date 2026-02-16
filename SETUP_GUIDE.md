# Panduan Setup Sistem Stor MPK (XAMPP)

Panduan ini untuk setup sistem **InventStor - Sistem Pengurusan Bilik Stor dan Inventori** pada komputer baru menggunakan XAMPP.

---

## 1. Perisian yang Diperlukan

| Perisian | Muat Turun |
|----------|-----------|
| **XAMPP** (PHP 8.0+) | https://www.apachefriends.org/download.html |
| **Notepad++** | https://notepad-plus-plus.org/downloads/ |
| **Git** | https://git-scm.com/download/win |

> **Penting:** Pilih XAMPP versi PHP 8.x (bukan 7.x).

---

## 2. Clone Repository

Buka **Git Bash** atau **Command Prompt**, navigate ke folder XAMPP htdocs:

```bash
cd C:\xampp\htdocs
git clone https://github.com/gible717/storeroom-system.git storeroom
```

Ini akan muat turun semua fail ke `C:\xampp\htdocs\storeroom\`.

> **Untuk update terkini:** Jalankan `git pull` dalam folder storeroom bila-bila masa.

---

## 3. Setup Database

### 3.1 Start MySQL
1. Buka **XAMPP Control Panel**
2. Klik **Start** pada **Apache** dan **MySQL**

### 3.2 Import Database
1. Buka browser, pergi ke **http://localhost/phpmyadmin**
2. Klik tab **SQL** di bahagian atas
3. Import fail schema (struktur jadual):
   - Klik tab **Import** → **Choose File**
   - Pilih `C:\xampp\htdocs\storeroom\database\schema.sql`
   - Klik **Go**
4. Import fail seed data (akaun default):
   - Klik tab **Import** → **Choose File**
   - Pilih `C:\xampp\htdocs\storeroom\database\seed_data.sql`
   - Klik **Go**

> **Nota:** `schema.sql` akan cipta database dan semua 7 jadual. `seed_data.sql` akan cipta akaun admin dan staf default.

---

## 4. Konfigurasi Environment (.env)

### 4.1 Cipta fail .env
1. Buka folder `C:\xampp\htdocs\storeroom\` dalam **Notepad++**
2. Buka fail `.env.example`
3. **Save As** → namakan sebagai `.env` (dalam folder yang sama)

### 4.2 Edit .env
Tukar nilai berikut mengikut setup anda:

```env
# Database Configuration
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=storeroom_db

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_ADMIN_CHAT_IDS=123456789
TELEGRAM_ENABLED=true

# Monthly Reminder Settings
MONTHLY_REMINDER_ENABLED=true
MONTHLY_REMINDER_TIME=09:00

# System URL
SYSTEM_BASE_URL=http://localhost/storeroom

# Application Settings
APP_DEBUG=false
APP_ENV=production
```

**Nota XAMPP:**
- `DB_USERNAME` biasanya `root`
- `DB_PASSWORD` biasanya kosong (tiada password) untuk XAMPP default
- Jika anda set password MySQL, masukkan di sini

> **JANGAN** commit fail `.env` ke GitHub. Ia mengandungi maklumat sensitif.

---

## 5. Konfigurasi Telegram Bot

### 5.1 Cipta fail telegram_config.php
1. Buka `telegram_config.example.php` dalam **Notepad++**
2. **Save As** → namakan sebagai `telegram_config.php`

### 5.2 Dapatkan Bot Token
1. Buka Telegram, cari **@BotFather**
2. Hantar `/newbot` dan ikut arahan
3. BotFather akan beri token seperti: `7123456789:AAF...`
4. Salin token ini ke dalam `telegram_config.php`:

```php
define('TELEGRAM_BOT_TOKEN', '7123456789:AAFxxxxxxxxxxxxxxxxxxxxxxxxx');
```

### 5.3 Dapatkan Chat ID
1. Buka chat dengan bot anda di Telegram
2. Hantar sebarang mesej (cth: "hello")
3. Buka URL ini dalam browser (ganti TOKEN dengan token sebenar):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
4. Cari `"chat":{"id":123456789}` — nombor itu ialah Chat ID anda
5. Masukkan ke `telegram_config.php`:

```php
define('TELEGRAM_ADMIN_CHAT_IDS', [
    '123456789',  // Chat ID admin
]);
```

### 5.4 Juga update .env
Pastikan `.env` juga ada token dan chat ID yang sama:
```env
TELEGRAM_BOT_TOKEN=7123456789:AAFxxxxxxxxxxxxxxxxxxxxxxxxx
TELEGRAM_ADMIN_CHAT_IDS=123456789
TELEGRAM_ENABLED=true
```

---

## 6. Setup Windows Task Scheduler (Peringatan Bulanan)

Sistem ini ada ciri peringatan stok bulanan automatik melalui Telegram. Untuk aktifkan:

### 6.1 Buka Task Scheduler
1. Tekan **Win + R**, taip `taskschd.msc`, tekan Enter

### 6.2 Cipta Task Baru
1. Klik **Create Basic Task** (panel kanan)
2. **Name:** `InventStor Monthly Reminder`
3. **Description:** `Hantar peringatan stok bulanan melalui Telegram`
4. Klik **Next**

### 6.3 Trigger (Bila)
1. Pilih **Daily**
2. Set masa: **9:00:00 AM**
3. Recur every: **1** day
4. Klik **Next**

### 6.4 Action (Apa)
1. Pilih **Start a program**
2. **Program/script:** Browse ke PHP executable XAMPP anda:
   ```
   C:\xampp\php\php.exe
   ```
3. **Add arguments:**
   ```
   C:\xampp\htdocs\storeroom\cron_monthly_reminder.php
   ```
4. Klik **Next** → **Finish**

### 6.5 Settings Tambahan (Recommended)
1. Klik kanan task yang baru dibuat → **Properties**
2. Tab **General**: Tick **Run whether user is logged on or not**
3. Tab **Settings**: Tick **Run task as soon as possible after a scheduled start is missed**
4. Klik **OK**

> **Nota:** Script ini akan hantar peringatan pada hari bekerja (Isnin-Jumaat) minggu pertama setiap bulan sahaja. Ia selamat dijalankan setiap hari kerana ia akan skip secara automatik jika bukan masa yang sesuai.

---

## 7. Test Sistem

### 7.1 Akses Sistem
1. Pastikan Apache dan MySQL running dalam XAMPP
2. Buka browser: **http://localhost/storeroom/**

### 7.2 Akaun Default

| Peranan | ID | Kata Laluan |
|---------|-----|-------------|
| Admin | A001 | User123 |
| Staff | S001 | User123 |

> **Tukar kata laluan** selepas log masuk pertama!

### 7.3 Test Telegram
1. Log masuk sebagai Staff
2. Buat permohonan stok baru
3. Semak Telegram — notifikasi patut diterima

### 7.4 Test Peringatan Bulanan (Manual)
Buka browser:
```
http://localhost/storeroom/test_telegram_reminder.php
```
Ini akan hantar peringatan terus tanpa menunggu jadual.

---

## 8. Troubleshooting

### Masalah Biasa

| Masalah | Penyelesaian |
|---------|-------------|
| **"Database connection failed"** | Pastikan MySQL running di XAMPP. Semak `.env` DB_USERNAME dan DB_PASSWORD. |
| **Halaman blank/putih** | Set `APP_DEBUG=true` dalam `.env` untuk lihat error. |
| **Telegram tak hantar** | Semak token dan chat ID betul. Cuba test manual dulu. |
| **Port 80 conflict** | Tukar port Apache di XAMPP (httpd.conf) atau tutup program lain yang guna port 80 (Skype, IIS). |
| **PHP version error** | Pastikan XAMPP anda versi PHP 8.0+. Semak di http://localhost/dashboard/ |

### Lihat Error Log
- XAMPP error log: `C:\xampp\apache\logs\error.log`
- PHP error log: `C:\xampp\php\logs\php_error_log`
- Sistem log: `C:\xampp\htdocs\storeroom\logs\`

---

## 9. Struktur Fail Penting

```
storeroom/
├── .env                    ← Config utama (CIPTA SENDIRI dari .env.example)
├── .env.example            ← Template config
├── telegram_config.php     ← Config Telegram (CIPTA SENDIRI dari .example)
├── config.php              ← Loader untuk .env
├── db.php                  ← Sambungan database
├── .htaccess               ← Config Apache (jangan ubah)
├── assets/img/             ← Logo dan gambar
├── uploads/                ← Gambar produk dan profil
└── logs/                   ← Log fail sistem
```

**Fail yang JANGAN commit ke GitHub:**
- `.env` (ada password database dan token Telegram)
- `telegram_config.php` (ada token Telegram)

---

## 10. Untuk Update dari GitHub

Bila ada update baru dari pembangun:

```bash
cd C:\xampp\htdocs\storeroom
git pull origin main
```

> Pastikan anda tidak ubah fail PHP secara terus. Jika ada conflict, hubungi pembangun.

---

*Panduan ini disediakan untuk handoff Sistem Pengurusan Bilik Stor dan Inventori, Majlis Perbandaran Kangar.*
