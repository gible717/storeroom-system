# MANUAL PENGGUNA
# Sistem Pengurusan Bilik Stor

---

**Versi:** 1.0
**Tarikh:** Januari 2026
**Disediakan oleh:** [Nama Anda]
**Jabatan:** [Nama Jabatan/Organisasi]

---

## ISI KANDUNGAN

1. [Pengenalan](#1-pengenalan)
2. [Keperluan Sistem](#2-keperluan-sistem)
3. [Akses Sistem](#3-akses-sistem)
4. [Modul Pentadbir (Admin)](#4-modul-pentadbir-admin)
   - 4.1 Dashboard
   - 4.2 Pengurusan Produk
   - 4.3 Kemaskini Stok
   - 4.4 Pengurusan Permohonan
   - 4.5 Pengurusan Pengguna
   - 4.6 Laporan
5. [Modul Staf](#5-modul-staf)
   - 5.1 Dashboard Staf
   - 5.2 Buat Permohonan Baru (KEW.PS-8)
   - 5.3 Lihat Senarai Permohonan
   - 5.4 Kemaskini Profil
6. [Soalan Lazim (FAQ)](#6-soalan-lazim-faq)
7. [Hubungi Kami](#7-hubungi-kami)

---

## 1. PENGENALAN

### 1.1 Tujuan Dokumen
Manual ini disediakan sebagai panduan kepada pengguna untuk menggunakan **Sistem Pengurusan Bilik Stor** dengan berkesan.

### 1.2 Latar Belakang Sistem
Sistem Pengurusan Bilik Stor adalah sistem berasaskan web yang dibangunkan untuk:
- Mengurus inventori stok alat tulis dan bekalan pejabat
- Memproses permohonan stok daripada kakitangan (KEW.PS-8)
- Menjana laporan stok dan transaksi
- Memantau paras stok minimum

### 1.3 Pengguna Sasaran

| Peranan | Tanggungjawab |
|---------|---------------|
| **Pentadbir (Admin)** | Mengurus produk, stok, pengguna, dan meluluskan permohonan |
| **Staf** | Membuat permohonan stok dan melihat status permohonan |

---

## 2. KEPERLUAN SISTEM

### 2.1 Keperluan Perkakasan
- Komputer dengan akses internet
- Paparan minimum 1024 x 768 piksel

### 2.2 Keperluan Perisian
| Komponen | Keperluan |
|----------|-----------|
| **Pelayar Web** | Google Chrome (disyorkan), Mozilla Firefox, Microsoft Edge |
| **Sambungan Internet** | Diperlukan untuk akses sistem |

### 2.3 Maklumat Akses
| Item | Maklumat |
|------|----------|
| **URL Sistem** | http://[alamat-server]/storeroom/ |
| **Nama Pengguna** | Diberikan oleh Pentadbir Sistem |
| **Kata Laluan** | Diberikan oleh Pentadbir Sistem |

---

## 3. AKSES SISTEM

### 3.1 Log Masuk

**Langkah-langkah:**

1. Buka pelayar web (Google Chrome disyorkan)
2. Taip alamat URL sistem di bar alamat
3. Klik butang **"Log Masuk Ke Sistem"** di halaman utama

   ![Halaman Utama](screenshots/01_landing_page.png)
   *Rajah 3.1: Halaman Utama Sistem*

4. Masukkan **Nama Pengguna** dan **Kata Laluan**
5. Klik butang **"Log Masuk"**

   ![Halaman Log Masuk](screenshots/02_login_page.png)
   *Rajah 3.2: Halaman Log Masuk*

6. Sistem akan membawa anda ke Dashboard mengikut peranan anda

### 3.2 Log Keluar

**Langkah-langkah:**

1. Klik nama anda di bahagian atas kanan skrin
2. Klik **"Log Keluar"**
3. Sistem akan membawa anda ke halaman log masuk

### 3.3 Terlupa Kata Laluan

Jika anda terlupa kata laluan:
1. Klik pautan **"Lupa Kata Laluan?"** di halaman log masuk
2. Masukkan alamat emel berdaftar anda
3. Klik **"Hantar"**
4. Semak emel anda untuk pautan tetapan semula kata laluan
5. Klik pautan dan masukkan kata laluan baru

---

## 4. MODUL PENTADBIR (ADMIN)

### 4.1 Dashboard Pentadbir

Selepas log masuk, Pentadbir akan melihat Dashboard utama yang memaparkan:

![Dashboard Admin](screenshots/03_admin_dashboard.png)
*Rajah 4.1: Dashboard Pentadbir*

| Komponen | Penerangan |
|----------|------------|
| **Jumlah Produk** | Bilangan produk dalam sistem |
| **Permohonan Tertunda** | Bilangan permohonan menunggu kelulusan |
| **Pantau Stok** | Bilangan produk dengan stok rendah |
| **Permohonan Terkini** | Senarai 6 permohonan terbaru |
| **Statistik Mini** | Jumlah pengguna, permohonan bulan ini, dll. |

---

### 4.2 Pengurusan Produk

#### 4.2.1 Lihat Senarai Produk

**Langkah-langkah:**

1. Klik **"Produk"** di menu sisi kiri
2. Senarai produk akan dipaparkan

   ![Senarai Produk](screenshots/04_product_list.png)
   *Rajah 4.2: Senarai Produk*

3. Gunakan kotak carian untuk mencari produk tertentu
4. Gunakan penapis kategori untuk menapis mengikut kategori

#### 4.2.2 Tambah Produk Baru

**Langkah-langkah:**

1. Klik butang **"Tambah Produk Baru"**
2. Isi maklumat produk:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | No Kod | Kod unik produk | Ya |
   | Perihal | Nama/penerangan produk | Ya |
   | Kategori | Kategori produk | Ya |
   | Baki Awal | Kuantiti stok awal | Ya |
   | Unit | Unit ukuran (Unit, Kotak, Rim, dll.) | Ya |

   ![Tambah Produk](screenshots/05_add_product.png)
   *Rajah 4.3: Borang Tambah Produk*

3. Klik **"Simpan"**
4. Mesej berjaya akan dipaparkan

#### 4.2.3 Kemaskini Produk

**Langkah-langkah:**

1. Cari produk yang ingin dikemaskini
2. Klik ikon **pensel/edit** di lajur Tindakan
3. Kemaskini maklumat yang diperlukan
4. Klik **"Kemaskini"**

#### 4.2.4 Padam Produk

**Langkah-langkah:**

1. Cari produk yang ingin dipadam
2. Klik ikon **tong sampah** di lajur Tindakan
3. Klik **"Ya"** pada pengesahan
4. Produk akan dipadam dari sistem

> **Amaran:** Produk yang telah digunakan dalam permohonan tidak boleh dipadam.

---

### 4.3 Kemaskini Stok

#### 4.3.1 Kemaskini Stok Manual

**Langkah-langkah:**

1. Klik **"Kemaskini Stok"** di menu sisi kiri
2. Pilih produk dari dropdown

   ![Kemaskini Stok](screenshots/06_stock_update.png)
   *Rajah 4.4: Borang Kemaskini Stok*

3. Pilih jenis transaksi:
   - **Terima** - Stok masuk (tambah stok)
   - **Keluar** - Stok keluar (tolak stok)

4. Masukkan kuantiti
5. Masukkan catatan (jika perlu)
6. Klik **"Kemaskini"**

---

### 4.4 Pengurusan Permohonan

#### 4.4.1 Lihat Semua Permohonan

**Langkah-langkah:**

1. Klik **"Pengurusan Permohonan"** di menu sisi kiri
2. Senarai permohonan akan dipaparkan

   ![Senarai Permohonan](screenshots/07_request_list.png)
   *Rajah 4.5: Senarai Permohonan*

3. Status permohonan ditunjukkan dengan warna:

   | Warna | Status |
   |-------|--------|
   | 🟡 Kuning | Baru (Menunggu Kelulusan) |
   | 🟢 Hijau | Diluluskan |
   | 🔴 Merah | Ditolak |
   | 🔵 Biru | Selesai |

#### 4.4.2 Semak dan Lulus Permohonan

**Langkah-langkah:**

1. Cari permohonan dengan status **"Baru"**
2. Klik butang **"Semak"**

   ![Semak Permohonan](screenshots/08_review_request.png)
   *Rajah 4.6: Halaman Semak Permohonan*

3. Semak butiran permohonan:
   - Nama Pemohon
   - Jawatan
   - Senarai item yang dimohon
   - Catatan pemohon

4. Untuk **meluluskan**:
   - Klik butang **"Luluskan"**
   - Masukkan catatan pentadbir (pilihan)
   - Klik **"Sahkan"**

5. Untuk **menolak**:
   - Klik butang **"Tolak"**
   - Masukkan sebab penolakan (**wajib**)
   - Klik **"Sahkan"**

> **Nota:** Pentadbir tidak boleh meluluskan permohonan sendiri. Permohonan pentadbir perlu diluluskan oleh pentadbir lain.

#### 4.4.3 Cetak KEW.PS-8

**Langkah-langkah:**

1. Cari permohonan yang telah **diluluskan**
2. Klik ikon **pencetak** di lajur Tindakan
3. Halaman KEW.PS-8 akan dibuka
4. Tekan **Ctrl + P** untuk mencetak

---

### 4.5 Pengurusan Pengguna

#### 4.5.1 Lihat Senarai Pengguna

**Langkah-langkah:**

1. Klik **"Pengguna"** di menu sisi kiri
2. Senarai pengguna akan dipaparkan

#### 4.5.2 Tambah Pengguna Baru

**Langkah-langkah:**

1. Klik butang **"Tambah Pengguna"**
2. Isi maklumat pengguna:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | ID Staf | ID pekerja | Ya |
   | Nama | Nama penuh | Ya |
   | Username | Nama pengguna untuk log masuk | Ya |
   | Password | Kata laluan | Ya |
   | Peranan | Admin atau Staf | Ya |
   | Emel | Alamat emel | Tidak |

3. Klik **"Simpan"**

#### 4.5.3 Kemaskini Pengguna

**Langkah-langkah:**

1. Klik ikon **pensel** pada pengguna yang ingin dikemaskini
2. Kemaskini maklumat yang diperlukan
3. Klik **"Kemaskini"**

#### 4.5.4 Padam Pengguna

**Langkah-langkah:**

1. Klik ikon **tong sampah** pada pengguna yang ingin dipadam
2. Klik **"Ya"** pada pengesahan

---

### 4.6 Laporan

#### 4.6.1 Laporan KEW.PS-3 (Rekod Stok)

**Langkah-langkah:**

1. Klik **"Laporan"** > **"KEW.PS-3"** di menu sisi kiri
2. Pilih julat tarikh (Dari - Hingga)
3. Pilih kategori atau produk (pilihan)
4. Klik **"Jana Laporan"**

   ![Laporan KEW.PS-3](screenshots/09_kewps3_report.png)
   *Rajah 4.7: Laporan KEW.PS-3*

5. Untuk mencetak, tekan **Ctrl + P**

#### 4.6.2 Laporan Inventori

**Langkah-langkah:**

1. Klik **"Laporan"** > **"Inventori"** di menu sisi kiri
2. Laporan inventori semasa akan dipaparkan
3. Gunakan carian atau penapis untuk menapis data
4. Untuk mencetak, tekan **Ctrl + P**

---

## 5. MODUL STAF

### 5.1 Dashboard Staf

Selepas log masuk, Staf akan melihat Dashboard dengan 3 pilihan utama:

![Dashboard Staf](screenshots/10_staff_dashboard.png)
*Rajah 5.1: Dashboard Staf*

| Kad | Fungsi |
|-----|--------|
| **Permohonan Baru** | Buat permohonan stok baru |
| **Permohonan Saya** | Lihat senarai permohonan anda |
| **Profil Saya** | Kemaskini maklumat profil |

---

### 5.2 Buat Permohonan Baru (KEW.PS-8)

**Langkah-langkah:**

1. Klik kad **"Permohonan Baru"**
2. Borang KEW.PS-8 akan dipaparkan

   ![Borang KEW.PS-8](screenshots/11_kewps8_form.png)
   *Rajah 5.2: Borang Permohonan KEW.PS-8*

3. Isi maklumat borang:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | Nama Pemohon | Auto-isi dari profil anda | - |
   | Jawatan | Jawatan anda | Ya |
   | Catatan | Catatan tambahan | Tidak |

4. Tambah item yang diperlukan:
   - Taip nama produk di kotak carian
   - Pilih produk dari senarai
   - Masukkan kuantiti
   - Klik **"Tambah Item"**

5. Ulang langkah 4 untuk menambah lebih banyak item

6. Semak senarai item di jadual bawah

   ![Senarai Item](screenshots/12_item_list.png)
   *Rajah 5.3: Senarai Item Permohonan*

7. Untuk membuang item, klik ikon **X** merah

8. Klik butang **"Sahkan"**

9. Pada modal pengesahan, klik **"Hantar"**

10. Mesej berjaya akan dipaparkan

---

### 5.3 Lihat Senarai Permohonan

**Langkah-langkah:**

1. Klik kad **"Permohonan Saya"**
2. Senarai permohonan anda akan dipaparkan

   ![Senarai Permohonan Staf](screenshots/13_my_requests.png)
   *Rajah 5.4: Senarai Permohonan Saya*

3. Klik pada **ID Permohonan** untuk melihat butiran

4. Status permohonan:

   | Status | Maksud | Tindakan |
   |--------|--------|----------|
   | 🟡 Baru | Menunggu kelulusan | Boleh edit/padam |
   | 🟢 Diluluskan | Telah diluluskan | Boleh cetak KEW.PS-8 |
   | 🔴 Ditolak | Permohonan ditolak | Lihat sebab penolakan |

#### 5.3.1 Edit Permohonan

> **Nota:** Hanya permohonan dengan status **"Baru"** boleh diedit.

**Langkah-langkah:**

1. Cari permohonan dengan status "Baru"
2. Klik butang **"Edit"**
3. Kemaskini maklumat atau item
4. Klik **"Kemaskini"**

#### 5.3.2 Padam Permohonan

> **Nota:** Hanya permohonan dengan status **"Baru"** boleh dipadam.

**Langkah-langkah:**

1. Cari permohonan dengan status "Baru"
2. Klik butang **"Padam"**
3. Klik **"Ya"** pada pengesahan

#### 5.3.3 Cetak KEW.PS-8

**Langkah-langkah:**

1. Cari permohonan yang telah **diluluskan**
2. Klik ikon **pencetak**
3. Halaman KEW.PS-8 akan dibuka
4. Tekan **Ctrl + P** untuk mencetak

---

### 5.4 Kemaskini Profil

**Langkah-langkah:**

1. Klik kad **"Profil Saya"** atau klik nama anda di atas kanan
2. Kemaskini maklumat yang diperlukan:
   - Nama
   - Emel
   - Kata Laluan (jika ingin tukar)

3. Klik **"Kemaskini"**

---

## 6. SOALAN LAZIM (FAQ)

### S1: Saya terlupa kata laluan. Apa yang perlu saya lakukan?

**Jawapan:**
Klik pautan "Lupa Kata Laluan?" di halaman log masuk dan ikut arahan. Jika masih menghadapi masalah, hubungi Pentadbir Sistem.

---

### S2: Mengapa saya tidak boleh edit permohonan saya?

**Jawapan:**
Permohonan hanya boleh diedit jika statusnya masih **"Baru"**. Permohonan yang telah diluluskan atau ditolak tidak boleh diedit.

---

### S3: Bagaimana untuk mencetak borang KEW.PS-8?

**Jawapan:**
Permohonan perlu diluluskan terlebih dahulu. Selepas diluluskan, klik ikon pencetak dan tekan Ctrl + P.

---

### S4: Mengapa produk yang saya cari tidak muncul?

**Jawapan:**
Pastikan anda menaip sekurang-kurangnya 2 aksara. Jika masih tidak muncul, produk tersebut mungkin belum didaftarkan oleh Pentadbir.

---

### S5: Pentadbir tidak boleh meluluskan permohonan. Mengapa?

**Jawapan:**
Pentadbir tidak boleh meluluskan permohonan yang dibuat oleh diri sendiri. Permohonan tersebut perlu diluluskan oleh Pentadbir lain.

---

### S6: Bagaimana untuk mengetahui permohonan saya ditolak?

**Jawapan:**
Status permohonan akan bertukar kepada **"Ditolak"** (merah). Klik pada permohonan untuk melihat sebab penolakan yang diberikan oleh Pentadbir.

---

### S7: Sistem lambat/tidak boleh diakses. Apa yang perlu dilakukan?

**Jawapan:**
1. Pastikan sambungan internet anda stabil
2. Cuba refresh halaman (tekan F5)
3. Cuba gunakan pelayar lain
4. Jika masih bermasalah, hubungi Pentadbir Sistem

---

## 7. HUBUNGI KAMI

Jika anda menghadapi sebarang masalah atau memerlukan bantuan, sila hubungi:

| Maklumat | Butiran |
|----------|---------|
| **Nama** | [Nama Pentadbir Sistem] |
| **Jawatan** | [Jawatan] |
| **No. Telefon** | [No. Telefon] |
| **Emel** | [Alamat Emel] |
| **Lokasi** | [Alamat Pejabat] |

---

## SEJARAH DOKUMEN

| Versi | Tarikh | Penulis | Perubahan |
|-------|--------|---------|-----------|
| 1.0 | Januari 2026 | [Nama Anda] | Versi awal |

---

*Manual ini disediakan untuk kegunaan dalaman [Nama Jabatan/Organisasi] sahaja.*

---

**© 2026 [Nama Jabatan/Organisasi]. Hak Cipta Terpelihara.**
