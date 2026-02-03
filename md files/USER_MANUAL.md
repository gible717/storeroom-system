# MANUAL PENGGUNA
# InventStor - Sistem Pengurusan Bilik Stor dan Inventori

---

**Versi:** 2.0
**Tarikh:** Februari 2026
**Disediakan oleh:** Unit Teknologi Maklumat
**Jabatan:** Majlis Perbandaran Kangar

---

## ISI KANDUNGAN

1. [Pengenalan](#1-pengenalan)
2. [Keperluan Sistem](#2-keperluan-sistem)
3. [Akses Sistem](#3-akses-sistem)
4. [Modul Pentadbir (Admin)](#4-modul-pentadbir-admin)
   - 4.1 Dashboard Pentadbir
   - 4.2 Pengurusan Produk
   - 4.3 Kemaskini Stok
   - 4.4 Pengurusan Permohonan
   - 4.5 Pengurusan Pengguna
   - 4.6 Laporan
   - 4.7 Profil Pentadbir
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
Manual ini disediakan sebagai panduan kepada pengguna untuk menggunakan **InventStor - Sistem Pengurusan Bilik Stor dan Inventori** dengan berkesan.

### 1.2 Latar Belakang Sistem
InventStor adalah sistem berasaskan web yang dibangunkan untuk:
- Mengurus inventori stok alat tulis dan bekalan pejabat
- Memproses permohonan stok daripada kakitangan (KEW.PS-8)
- Menjana laporan stok dan transaksi (KEW.PS-3, Inventori, Analisis Jabatan)
- Memantau paras stok minimum dan memberi amaran stok rendah

### 1.3 Pengguna Sasaran

| Peranan | Tanggungjawab |
|---------|---------------|
| **Pentadbir (Admin)** | Mengurus produk, stok, pengguna, kategori, meluluskan permohonan, dan menjana laporan |
| **Staf** | Membuat permohonan stok, melihat status permohonan, dan mengurus profil |

---

## 2. KEPERLUAN SISTEM

### 2.1 Keperluan Perkakasan
- Komputer dengan akses internet/intranet
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
| **ID Staf** | No. gaji 5 aksara (diberikan oleh Pentadbir Sistem atau semasa pendaftaran) |
| **Kata Laluan** | Diberikan oleh Pentadbir Sistem atau ditetapkan semasa pendaftaran |

---

## 3. AKSES SISTEM

### 3.1 Halaman Utama

Halaman utama memaparkan latar belakang slideshow dengan dua pilihan:

![Halaman Utama](screenshots/01_landing_page.png)
*Rajah 3.1: Halaman Utama Sistem*

| Butang | Fungsi |
|--------|--------|
| **Log Masuk** | Pergi ke halaman log masuk |
| **Daftar Akaun Baru** | Daftar akaun staf baru (lihat Seksyen 3.4) |

### 3.2 Log Masuk

**Langkah-langkah:**

1. Buka pelayar web (Google Chrome disyorkan)
2. Taip alamat URL sistem di bar alamat
3. Klik butang **"Log Masuk"** di halaman utama
4. Masukkan **ID Staf** (no. gaji, 5 aksara) dan **Kata Laluan**
5. Tandakan **"Ingat Saya"** jika mahu (pilihan)
6. Klik butang **"Log Masuk"**

   ![Halaman Log Masuk](screenshots/02_login_page.png)
   *Rajah 3.2: Halaman Log Masuk*

7. Sistem akan membawa anda ke Dashboard mengikut peranan anda:
   - **Pentadbir** - Dashboard Admin
   - **Staf** - Dashboard Staf

> **Nota:** Jika ini adalah log masuk pertama anda (akaun dicipta oleh Pentadbir), sistem akan meminta anda menukar kata laluan sementara.

   ![Tukar Kata Laluan Pertama](screenshots/15_first_login_change_password.png)
   *Rajah 3.5: Halaman Tukar Kata Laluan Pertama Kali*

### 3.3 Log Keluar

**Langkah-langkah:**

1. Klik nama anda di bahagian atas kanan skrin
2. Klik **"Log Keluar"**
3. Sistem akan membawa anda ke halaman log masuk

### 3.4 Pendaftaran Akaun Baru

Staf boleh mendaftar akaun sendiri melalui halaman utama:

![Halaman Pendaftaran](screenshots/09_registration_page.png)
*Rajah 3.3: Halaman Pendaftaran Akaun Baru*

**Langkah-langkah:**

1. Klik butang **"Daftar Akaun Baru"** di halaman utama
2. Isi maklumat pendaftaran:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | ID Staf | No. gaji (5 aksara, unik) | Ya |
   | Nama Penuh | Nama penuh anda | Ya |
   | Emel | Alamat emel (pilihan) | Tidak |
   | Jabatan/Unit | Pilih jabatan dari senarai | Ya |
   | Kata Laluan | Kata laluan anda | Ya |
   | Sahkan Kata Laluan | Masukkan semula kata laluan | Ya |

3. Klik **"Daftar"**
4. Mesej berjaya akan dipaparkan dan anda akan dialihkan ke halaman log masuk

### 3.5 Terlupa Kata Laluan

Jika anda terlupa kata laluan:

![Halaman Lupa Kata Laluan](screenshots/14_forgot_password.png)
*Rajah 3.4: Halaman Lupa Kata Laluan*

1. Klik pautan **"Lupa Kata Laluan?"** di halaman log masuk
2. Masukkan **ID Staf** anda (no. gaji)
3. Klik **"Seterusnya"**
4. Ikut arahan untuk menetapkan semula kata laluan baru

> **Nota:** Kata laluan baru tidak boleh sama dengan kata laluan lama.

---

## 4. MODUL PENTADBIR (ADMIN)

### 4.1 Dashboard Pentadbir

Selepas log masuk, Pentadbir akan melihat Dashboard utama yang memaparkan:

![Dashboard Admin](screenshots/03_admin_dashboard.png)
*Rajah 4.1: Dashboard Pentadbir*

**Kad Statistik Utama:**

| Komponen | Penerangan |
|----------|------------|
| **Jumlah Produk** | Bilangan produk dalam sistem (klik untuk lihat senarai produk) |
| **Permohonan Tertunda** | Bilangan permohonan menunggu kelulusan (klik untuk lihat senarai tertunda) |
| **Pantau Stok** | Bilangan produk dengan stok rendah atau habis (klik untuk lihat butiran) |

**Permohonan Terkini:**

- Memaparkan 6 permohonan terbaru, diutamakan mengikut status (Baru dahulu)
- Permohonan "Baru" ditandakan dengan animasi pulsing kuning
- Paparan masa pintar: "sebentar tadi", "X minit yang lalu" untuk hari ini, atau tarikh untuk hari sebelumnya

**Statistik Mini:**

| Komponen | Penerangan |
|----------|------------|
| **Jumlah Pengguna** | Bilangan pengguna berdaftar |
| **Permohonan Bulan Ini** | Bilangan permohonan untuk bulan semasa |
| **Kadar Kelulusan** | Peratusan permohonan yang diluluskan |
| **Jabatan Aktif** | Bilangan jabatan dalam sistem |

**Menu Sisi Kiri (Sidebar):**

| Menu | Halaman |
|------|---------|
| Dashboard Admin | Halaman utama pentadbir |
| Produk | Pengurusan produk dan kategori |
| Kemaskini Stok | Kemaskini stok secara manual |
| Permohonan | Urus dan lulus permohonan |
| Laporan | Dashboard laporan dan jana laporan |
| Pengguna | Pengurusan pengguna |
| Profil Saya | Kemaskini profil pentadbir |

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
5. Klik **"Urus Kategori"** untuk mengurus senarai kategori

#### 4.2.2 Tambah Produk Baru

**Langkah-langkah:**

1. Klik butang **"Tambah Produk Baru"**
2. Isi maklumat produk:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | Nama Produk | Nama/penerangan produk | Ya |
   | ID Produk / SKU | Kod unik produk (cth: A4-PAPER-001) | Ya |
   | Kategori | Kategori produk (pilih dari senarai) | Ya |
   | Nama Pembekal | Nama pembekal untuk tujuan rekod | Tidak |
   | Harga Seunit (RM) | Harga seunit dalam Ringgit Malaysia | Tidak |
   | Kuantiti Stok Awal | Kuantiti stok permulaan | Ya |

   ![Tambah Produk](screenshots/05_add_product.png)
   *Rajah 4.3: Borang Tambah Produk*

3. Klik **"Simpan Produk"**
4. Mesej berjaya akan dipaparkan

> **Nota:** ID Produk / SKU mestilah unik. Sistem akan memaparkan ralat jika ID sudah wujud.

#### 4.2.3 Kemaskini Produk

**Langkah-langkah:**

1. Cari produk yang ingin dikemaskini
2. Klik ikon **pensel/edit** di lajur Tindakan

   ![Kemaskini Produk](screenshots/16_edit_product.png)
   *Rajah 4.4: Borang Kemaskini Produk*

3. Kemaskini maklumat yang diperlukan
4. Klik **"Kemaskini"**

#### 4.2.4 Padam Produk

**Langkah-langkah:**

1. Cari produk yang ingin dipadam
2. Klik ikon **tong sampah** di lajur Tindakan

   ![Pengesahan Padam Produk](screenshots/17_delete_product_confirm.png)
   *Rajah 4.5: Dialog Pengesahan Padam Produk*

3. Klik **"Ya"** pada pengesahan
4. Produk akan dipadam dari sistem

> **Amaran:** Produk yang telah digunakan dalam permohonan tidak boleh dipadam.

#### 4.2.5 Pengurusan Kategori

**Langkah-langkah:**

1. Dari halaman Senarai Produk, klik **"Urus Kategori"**

   ![Pengurusan Kategori](screenshots/18_category_management.png)
   *Rajah 4.6: Halaman Pengurusan Kategori*

2. Tambah, kemaskini, atau padam kategori mengikut keperluan

---

### 4.3 Kemaskini Stok

#### 4.3.1 Kemaskini Stok Manual (Terima Stok)

Fungsi ini digunakan untuk **menambah stok masuk** sahaja (cth: penerimaan stok dari pembekal).

**Langkah-langkah:**

1. Klik **"Kemaskini Stok"** di menu sisi kiri
2. Pilih **Kategori** untuk menapis senarai item (pilihan)

   ![Kemaskini Stok](screenshots/06_stock_update.png)
   *Rajah 4.4: Borang Kemaskini Stok*

3. Pilih **Nama Item** dari dropdown
4. **Stok Semasa** akan dipaparkan secara automatik
5. Masukkan **Kuantiti Masuk** (bilangan stok yang diterima)
6. Masukkan **Catatan** (pilihan - cth: nombor invois atau nama pembekal)
7. Klik **"Tambah Stok"**

> **Nota:** Stok keluar diproses secara automatik melalui kelulusan permohonan (KEW.PS-8). Pentadbir tidak perlu merekod stok keluar secara manual.

---

### 4.4 Pengurusan Permohonan

#### 4.4.1 Lihat Semua Permohonan

**Langkah-langkah:**

1. Klik **"Permohonan"** di menu sisi kiri
2. Senarai semua permohonan akan dipaparkan (diutamakan mengikut status "Baru")

   ![Senarai Permohonan](screenshots/07_request_list.png)
   *Rajah 4.5: Senarai Permohonan*

3. Gunakan penapis status atau kategori untuk menapis senarai
4. Status permohonan ditunjukkan dengan warna:

   | Warna | Status |
   |-------|--------|
   | Kuning (animasi pulsing) | Baru (Menunggu Kelulusan) |
   | Hijau | Diluluskan |
   | Merah | Ditolak |

5. Pentadbir juga boleh klik **"Buat Permohonan"** untuk membuat permohonan bagi pihak sendiri

#### 4.4.2 Semak dan Lulus Permohonan

**Langkah-langkah:**

1. Cari permohonan dengan status **"Baru"**
2. Klik pada permohonan untuk membuka halaman semakan

   ![Semak Permohonan](screenshots/08_review_request.png)
   *Rajah 4.6: Halaman Semak Permohonan*

3. Semak butiran permohonan:
   - ID Permohonan dan Tarikh Mohon
   - Nama Pemohon
   - Jawatan Pemohon
   - Jabatan/Unit
   - Catatan Pemohon (jika ada)

4. Semak senarai item yang dimohon:
   - **Perihal Stok** - Nama item
   - **Baki Semasa** - Stok semasa dalam sistem
   - **Kuantiti Mohon** - Kuantiti yang diminta oleh pemohon
   - **Kuantiti Lulus** - Kuantiti yang diluluskan (boleh diubah oleh pentadbir)

5. **Kelulusan Separa:** Pentadbir boleh meluluskan kuantiti yang kurang daripada yang dimohon berdasarkan ketersediaan stok. Kuantiti lulus tidak boleh melebihi baki semasa.

6. Masukkan **Catatan Admin** jika perlu (pilihan)

7. Untuk **meluluskan**:
   - Klik butang **"Luluskan"**
   - Sistem akan menolak stok secara automatik

8. Untuk **menolak**:
   - Klik butang **"Tolak"**
   - Masukkan sebab penolakan dalam catatan admin

> **Nota Penting:**
> - Pentadbir **tidak boleh** meluluskan permohonan yang dibuat oleh diri sendiri. Permohonan tersebut perlu diluluskan oleh pentadbir lain.
> - Apabila permohonan diluluskan, stok akan ditolak secara automatik dan transaksi direkodkan dalam KEW.PS-3.

#### 4.4.3 Cetak KEW.PS-8

**Langkah-langkah:**

1. Cari permohonan yang telah **diluluskan**
2. Klik ikon **pencetak** di lajur Tindakan
3. Dokumen KEW.PS-8 rasmi akan dibuka dalam halaman cetak

   ![Cetakan KEW.PS-8](screenshots/19_kewps8_print.png)
   *Rajah 4.7: Halaman Cetakan KEW.PS-8*

**Format Dokumen Cetakan KEW.PS-8:**

Dokumen dicetak dalam format **A4 landscape** mengikut format rasmi Pekeliling Perbendaharaan Malaysia.

**Kepala Dokumen:**

| Bahagian | Kandungan |
|----------|-----------|
| Kiri atas | "Pekeliling Perbendaharaan Malaysia" |
| Kanan atas | "AM 6.5 Lampiran B" |
| Kanan (di bawah) | **KEW.PS-8** (tebal) |
| Kanan (di bawah) | "No. BPSI:" |
| Tengah | **BORANG PERMOHONAN STOK (INDIVIDU KEPADA STOR)** |
| Bawah tajuk | "Jabatan / Unit: [Nama Jabatan]" |

**Jadual Utama (3 Seksyen):**

Jadual dibahagikan kepada 3 seksyen utama yang dipisahkan oleh garisan tebal:

| Seksyen | Lajur | Penerangan |
|---------|-------|------------|
| **Permohonan** | No. Kod | Kod item yang dimohon |
| | Perihal Stok | Nama/penerangan item |
| | Kuantiti Dimohon | Kuantiti yang dimohon oleh pemohon |
| **Pegawai Pelulus** | Baki Sedia Ada | Baki stok semasa kelulusan |
| | Kuantiti Diluluskan | Kuantiti yang diluluskan (dipaparkan dalam **merah tebal**) |
| | Catatan | Catatan pegawai pelulus |
| **Perakuan Penerimaan** | Kuantiti Diterima | Kuantiti yang diterima (dipaparkan dalam **merah tebal**) |
| | Catatan | Catatan penerimaan |

> **Nota:** Jadual memaparkan minimum 7 baris. Jika item kurang dari 7, baris kosong akan ditambah secara automatik.

**Blok Tandatangan (3 Bahagian di bahagian bawah jadual):**

| Blok | Kandungan Auto-isi |
|------|-------------------|
| **Pemohon** | Nama, Jawatan (jika ada), dan Tarikh Mohon auto-isi dari sistem |
| **Pegawai Pelulus** | Nama dan Tarikh Lulus auto-isi dari sistem |
| **Pemohon / Wakil** | Kosong - untuk diisi semasa penerimaan fizikal |

Setiap blok menyediakan ruang tandatangan bertitik-titik.

**Nota Kaki:** "M.S. 12/13"

4. Klik butang **"Cetak Dokumen"** atau tekan **Ctrl + P** untuk mencetak
5. Klik **"Kembali"** untuk kembali ke senarai permohonan

> **Tip:** Semasa mencetak, pastikan tetapan pencetak ditetapkan kepada **A4 Landscape** untuk hasil cetakan yang betul.

---

### 4.5 Pengurusan Pengguna

#### 4.5.1 Lihat Senarai Pengguna

**Langkah-langkah:**

1. Klik **"Pengguna"** di menu sisi kiri
2. Senarai pengguna akan dipaparkan

   ![Senarai Pengguna](screenshots/20_user_list.png)
   *Rajah 4.8: Senarai Pengguna*

#### 4.5.2 Tambah Pengguna Baru

**Langkah-langkah:**

1. Klik butang **"Tambah Pengguna"**

   ![Tambah Pengguna](screenshots/21_add_user.png)
   *Rajah 4.9: Borang Tambah Pengguna Baru*

2. Isi maklumat pengguna:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | ID Staf | No. gaji pekerja (unik, 5 aksara) | Ya |
   | Nama Penuh | Nama penuh pekerja | Ya |
   | Emel | Alamat emel | Tidak |
   | Jabatan/Unit | Jabatan atau unit pekerja (pilih dari senarai) | Ya |
   | Peranan | Admin atau Staf (pilih dari dropdown) | Ya |

3. **Kata Laluan Sementara:** Sistem menetapkan kata laluan sementara **"User123"** secara automatik
4. Klik **"Simpan"**

> **Nota:** Pengguna akan dipaksa menukar kata laluan sementara semasa log masuk pertama.

#### 4.5.3 Kemaskini Pengguna

**Langkah-langkah:**

1. Klik ikon **pensel** pada pengguna yang ingin dikemaskini

   ![Kemaskini Pengguna](screenshots/32_edit_user.png)
   *Rajah 4.10 (Pengguna): Borang Kemaskini Pengguna*

2. Kemaskini maklumat yang diperlukan
3. Klik **"Kemaskini"**

#### 4.5.4 Padam Pengguna

**Langkah-langkah:**

1. Klik ikon **tong sampah** pada pengguna yang ingin dipadam

   ![Pengesahan Padam Pengguna](screenshots/33_delete_user_confirm.png)
   *Rajah 4.11 (Pengguna): Dialog Pengesahan Padam Pengguna*

2. Klik **"Ya, padamkan!"** pada pengesahan

---

### 4.6 Laporan

Halaman Laporan memaparkan **Dashboard Ringkas** dengan ringkasan statistik dan pautan ke laporan terperinci.

#### 4.6.1 Dashboard Ringkas Laporan

**Langkah-langkah:**

1. Klik **"Laporan"** di menu sisi kiri
2. Dashboard Ringkas akan dipaparkan dengan:

   ![Dashboard Laporan](screenshots/22_reports_dashboard.png)
   *Rajah 4.10: Dashboard Ringkas Laporan*

**Kad Ringkasan:**

| Kad | Penerangan |
|-----|------------|
| **Jumlah Permohonan** | Bilangan permohonan dalam tempoh dipilih |
| **Diluluskan** | Bilangan permohonan yang diluluskan |
| **Ditolak** | Bilangan permohonan yang ditolak |
| **Belum Diproses** | Bilangan permohonan yang belum diproses |

**Penapis:**

- **Tempoh:** Minggu ini, Bulan ini (lalai), Tahun ini, atau Pilih Tempoh (tarikh khusus)
- **Kategori:** Tapis mengikut kategori produk

**Carta:**

- **Pecahan Status Permohonan** - Carta donut menunjukkan peratusan setiap status
- **Top 5 Item Paling Diminta** - Carta bar mendatar menunjukkan item paling kerap dimohon

#### 4.6.2 Jana KEW.PS-3 Bahagian B (Kad Kawalan Stok)

Laporan rasmi transaksi stok mengikut item dan tempoh, berformat Pekeliling Perbendaharaan Malaysia (AM 6.3 Lampiran A).

**Langkah-langkah:**

1. Dari Dashboard Laporan, klik **"Jana KEW.PS-3"**
2. Borang Jana Laporan akan dipaparkan:

   ![Borang Jana KEW.PS-3](screenshots/23_kewps3_form.png)
   *Rajah 4.11: Borang Jana Laporan KEW.PS-3*

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | Tapis Mengikut Kategori | Tapis senarai barang mengikut kategori | Tidak |
   | Pilih Barang | Pilih item dari senarai dropdown (menunjukkan nama, kod, dan kategori) | Ya |
   | Tarikh Mula | Tarikh mula tempoh laporan (lalai: 30 hari lalu) | Ya |
   | Tarikh Akhir | Tarikh akhir tempoh laporan (lalai: hari ini) | Ya |

3. Klik **"Jana & Cetak Laporan"**

4. Dokumen KEW.PS-3 Bahagian B akan dibuka dalam halaman cetak

   ![Cetakan KEW.PS-3](screenshots/24_kewps3_print.png)
   *Rajah 4.12: Halaman Cetakan KEW.PS-3 Bahagian B*

**Format Dokumen Cetakan KEW.PS-3:**

Dokumen dicetak dalam format **A4 portrait** mengikut format rasmi Pekeliling Perbendaharaan Malaysia.

**Kepala Dokumen (setiap halaman):**

| Bahagian | Kandungan |
|----------|-----------|
| Kiri atas | "Pekeliling Perbendaharaan Malaysia" |
| Kanan atas | "AM 6.3 Lampiran A" |
| Tengah | **BAHAGIAN B** (tebal) |
| Bawah tajuk | **Transaksi Stok** (kiri, tebal) |

**Jadual Transaksi (Dua Aras Kepala Lajur):**

Kepala jadual mempunyai dua baris:

| Lajur Utama | Sub-Lajur | Penerangan |
|-------------|-----------|------------|
| Tarikh | - | Tarikh transaksi (format: dd/mm/yyyy) |
| No.PK/BTB/BPSS/BPSI/BPIN | - | Nombor rujukan dokumen |
| Terima Daripada / Keluar Kepada | - | Nama jabatan/unit terlibat |
| **TERIMAAN** | Kuantiti | Bilangan unit stok masuk |
| | Seunit (RM) | Harga seunit dalam RM |
| | Jumlah (RM) | Jumlah nilai stok masuk |
| **KELUARAN** | Kuantiti | Bilangan unit stok keluar |
| | Jumlah (RM) | Jumlah nilai stok keluar |
| **BAKI** | Kuantiti | Baki kuantiti selepas transaksi |
| | Jumlah (RM) | Jumlah nilai baki stok |
| Nama Pegawai | - | Nama pegawai yang meluluskan transaksi |

**Baris Data:**

- **Baris pertama (halaman pertama sahaja):** Memaparkan **"Baki dibawa ke hadapan"** - iaitu baki pembukaan sebelum tempoh yang dipilih, dengan kuantiti dan nilai RM
- **Baris transaksi:** Setiap transaksi memaparkan baki berjalan (*running balance*) yang dikira secara automatik
- **Pagination:** Laporan dipecahkan kepada halaman dengan **28 baris setiap muka surat**. Baris kosong diisi secara automatik jika transaksi kurang dari 28

**Nota Singkatan (halaman terakhir sahaja):**

Di bahagian bawah jadual pada halaman terakhir, nota berikut dipaparkan:

| Singkatan | Maksud |
|-----------|--------|
| PK | Pesanan Kerajaan |
| BTB | Borang Terimaan Barang-barang |
| BPSS | Borang Permohonan Stok (KEW.PS-7) |
| BPSI | Borang Permohonan Stok (KEW.PS-8) |
| BPIN | Borang Pindahan Stok (KEW.PS-17) |

**Nama Fail PDF:** Apabila anda menyimpan sebagai PDF, nama fail automatik adalah: `KEW.PS-3_[Nama Item]_[Tarikh Mula]_hingga_[Tarikh Akhir]`

5. Klik butang **"Cetak Dokumen"** atau tekan **Ctrl + P** untuk mencetak
6. Klik **"Kembali"** untuk kembali ke borang jana laporan

> **Tip:** Semasa mencetak, pastikan tetapan pencetak ditetapkan kepada **A4 Portrait** untuk hasil cetakan yang betul.

#### 4.6.3 Analisis Mengikut Jabatan

Infografik permohonan mengikut jabatan.

**Langkah-langkah:**

1. Dari Dashboard Laporan, klik **"Lihat Analisis"**

   ![Analisis Jabatan](screenshots/25_department_analysis.png)
   *Rajah 4.13: Laporan Analisis Mengikut Jabatan*

2. Laporan memaparkan analisis permohonan mengikut jabatan, kadar kelulusan, dan trend bulanan

#### 4.6.4 Laporan Inventori

Analisis inventori bulanan dengan nilai stok dan pergerakan stok masuk/keluar.

**Langkah-langkah:**

1. Dari Dashboard Laporan, klik **"Lihat Inventori"**

   ![Laporan Inventori](screenshots/26_inventory_report.png)
   *Rajah 4.14: Dashboard Laporan Inventori*

2. Pilih penapis laporan:

   | Penapis | Penerangan | Lalai |
   |---------|------------|-------|
   | Bulan | Pilih bulan untuk laporan | Bulan semasa |
   | Tahun | Pilih tahun untuk laporan | Tahun semasa |
   | Kategori | Tapis mengikut kategori produk (pilihan) | Semua Kategori |

3. Klik **"Tapis"** untuk menjana laporan

**Kad Ringkasan:**

| Kad | Penerangan |
|-----|------------|
| **Jumlah Item** | Bilangan produk dalam inventori |
| **Jumlah Stok** | Jumlah keseluruhan unit stok |
| **Jumlah Nilai** | Jumlah nilai stok dalam RM |

**Jadual Inventori:**

| Lajur | Penerangan |
|-------|------------|
| Bil. | Nombor turutan |
| No. Kod | Kod item produk |
| Nama Item | Nama/penerangan produk |
| Kategori | Kategori produk |
| Harga Unit (RM) | Harga seunit dalam RM |
| Baki Bln Lepas | Baki stok pada akhir bulan sebelumnya |
| Masuk | Kuantiti stok diterima dalam bulan dipilih |
| Keluar | Kuantiti stok dikeluarkan dalam bulan dipilih |
| Baki Semasa | Baki stok terkini |
| Jumlah (RM) | Nilai stok semasa (Baki Semasa × Harga Unit) |

**Eksport Laporan:**

Laporan inventori boleh dieksport dalam **dua format**:

##### Cetak PDF

1. Klik butang **"Cetak PDF"** (biru) di bahagian atas kanan

   ![Cetakan PDF Inventori](screenshots/34_inventory_print.png)
   *Rajah 4.15 (Inventori): Halaman Cetakan PDF Laporan Inventori*

2. Halaman cetak akan dibuka dengan format rasmi:
   - Kepala surat MPK (logo dan nama organisasi)
   - Tajuk dokumen: **"Laporan Inventori Stor"**
   - Jadual ringkas dengan lajur: Bil, ID Produk, Nama Produk, Kategori, Stok Semasa, Harga Seunit, Nilai Semasa
   - Jumlah keseluruhan di bahagian bawah jadual
3. Klik **"Cetak Dokumen"** atau tekan **Ctrl + P** untuk mencetak
4. Klik **"Kembali"** untuk kembali ke laporan inventori

> **Tip:** Semasa mencetak, pastikan tetapan pencetak ditetapkan kepada **A4 Portrait** untuk hasil cetakan yang betul.

##### Export Excel

1. Klik butang **"Export Excel"** (hijau) di bahagian atas kanan
2. Fail CSV akan dimuat turun secara automatik dengan nama: `Laporan_Inventori_[Tahun]-[Bulan].csv`
3. Buka fail dalam Microsoft Excel atau perisian hamparan lain
4. Fail mengandungi:
   - Kepala surat rasmi MPK
   - Maklumat bulan dan kategori yang dipilih
   - Jadual penuh dengan semua lajur termasuk pergerakan stok (Baki Bln Lepas, Masuk, Keluar)
   - Ringkasan jumlah keseluruhan

> **Nota:** Fail Excel mengandungi lebih banyak maklumat berbanding cetakan PDF, termasuk pergerakan stok masuk/keluar bulanan.

---

### 4.7 Profil Pentadbir

**Langkah-langkah:**

1. Klik **"Profil Saya"** di menu sisi kiri

   ![Profil Pentadbir](screenshots/27_admin_profile.png)
   *Rajah 4.15: Halaman Profil Pentadbir*

2. Kemaskini maklumat yang diperlukan:
   - Gambar profil (dengan fungsi potong/crop)
   - Nama Penuh
   - Jawatan
   - Emel
   - Jabatan/Unit (paparan sahaja)
3. Klik **"Simpan"**
4. Untuk menukar kata laluan, klik **"Tukar Kata Laluan"**

---

## 5. MODUL STAF

### 5.1 Dashboard Staf

Selepas log masuk, Staf akan melihat Dashboard dengan komponen berikut:

![Dashboard Staf](screenshots/10_staff_dashboard.png)
*Rajah 5.1: Dashboard Staf*

**Kad Selamat Datang:**
- Paparan ucapan mengikut nama staf
- Tarikh hari ini dalam format Bahasa Melayu
- Latar belakang berubah mengikut waktu (pagi/tengahari/petang/malam)

**Tindakan Pantas (3 Kad):**

| Kad | Fungsi |
|-----|--------|
| **Permohonan Baru** | Buat permohonan stok baru (KEW.PS-8) |
| **Permohonan Saya** | Lihat senarai dan status permohonan anda |
| **Profil Saya** | Kemaskini profil dan kata laluan |

**Aktiviti Terkini:**
- Senarai 5 permohonan terbaru anda dengan status dan tarikh

**Statistik Peribadi (4 Kad):**

| Kad | Penerangan |
|-----|------------|
| **Tertunda** | Bilangan permohonan anda yang menunggu kelulusan |
| **Diluluskan** | Bilangan permohonan anda yang telah diluluskan |
| **Ditolak** | Bilangan permohonan anda yang ditolak |
| **Jumlah** | Jumlah keseluruhan permohonan anda |

> **Nota:** Klik pada mana-mana kad statistik untuk melihat senarai terperinci permohonan mengikut status.

---

### 5.2 Buat Permohonan Baru (KEW.PS-8)

**Langkah-langkah:**

1. Klik kad **"Permohonan Baru"** di Dashboard

2. Borang KEW.PS-8 akan dipaparkan

   ![Borang KEW.PS-8](screenshots/11_kewps8_form.png)
   *Rajah 5.2: Borang Permohonan KEW.PS-8*

3. Maklumat borang:

   | Medan | Penerangan | Wajib |
   |-------|------------|-------|
   | Nama Pemohon | Auto-isi dari profil anda (tidak boleh diubah) | - |
   | Jabatan / Unit | Auto-isi dari profil anda (tidak boleh diubah) | - |
   | Jawatan | Jawatan anda (dengan cadangan automatik) | Tidak |
   | Tapisan mengikut Kategori | Tapis senarai item mengikut kategori | Tidak |
   | Perihal Stok | Pilih item dari senarai dropdown | Ya |
   | Kuantiti Dimohon | Kuantiti yang ingin dimohon | Ya |
   | Catatan | Catatan tambahan untuk permohonan | Tidak |

4. Tambah item yang diperlukan:
   - Pilih kategori untuk menapis senarai (pilihan)
   - Pilih item dari dropdown **"Perihal Stok"**
   - Masukkan **kuantiti** yang diperlukan
   - Klik butang **"Tambah Item"** di bahagian atas

5. Ulang langkah 4 untuk menambah lebih banyak item

> **Nota:** Kuantiti tidak boleh melebihi stok semasa yang tersedia. Item yang kehabisan stok tidak boleh ditambah.

6. Klik butang **"Sahkan"** apabila selesai

7. Modal pengesahan akan memaparkan senarai item anda:
   - Semak senarai item
   - Kemaskini kuantiti jika perlu
   - Padam item jika perlu (klik ikon tong sampah)

   ![Senarai Item](screenshots/12_item_list.png)
   *Rajah 5.3: Modal Pengesahan Permohonan*

8. Klik **"Hantar"** untuk menghantar permohonan

9. Mesej berjaya akan dipaparkan

---

### 5.3 Lihat Senarai Permohonan

**Langkah-langkah:**

1. Klik kad **"Permohonan Saya"** di Dashboard
2. Senarai permohonan anda akan dipaparkan

   ![Senarai Permohonan Staf](screenshots/13_my_requests.png)
   *Rajah 5.4: Senarai Permohonan Saya*

3. Klik pada **ID Permohonan** untuk melihat butiran penuh (termasuk catatan admin jika ada)

4. Status permohonan:

   | Status | Maksud | Tindakan |
   |--------|--------|----------|
   | Baru (kuning) | Menunggu kelulusan pentadbir | Boleh edit/padam |
   | Diluluskan (hijau) | Telah diluluskan oleh pentadbir | Boleh cetak KEW.PS-8 |
   | Ditolak (merah) | Permohonan ditolak oleh pentadbir | Lihat sebab penolakan dalam catatan admin |

#### 5.3.1 Edit Permohonan

> **Nota:** Hanya permohonan dengan status **"Baru"** boleh diedit.

**Langkah-langkah:**

1. Cari permohonan dengan status "Baru"
2. Klik butang **"Edit"**

   ![Edit Permohonan](screenshots/28_edit_request.png)
   *Rajah 5.5: Halaman Edit Permohonan*

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
3. Dokumen KEW.PS-8 rasmi akan dibuka dalam halaman cetak

   ![Cetakan KEW.PS-8](screenshots/35_kewps8_staff_print.png)
   *Rajah 5.5b: Halaman Cetakan Borang KEW.PS-8*

**Format Dokumen Cetakan KEW.PS-8:**

Dokumen dicetak dalam format **A4 landscape** mengikut format rasmi Pekeliling Perbendaharaan Malaysia.

**Kepala Dokumen:**

| Bahagian | Kandungan |
|----------|-----------|
| Kiri atas | "Pekeliling Perbendaharaan Malaysia" |
| Kanan atas | "AM 6.5 Lampiran B" |
| Kanan (di bawah) | **KEW.PS-8** (tebal) |
| Kanan (di bawah) | "No. BPSI:" |
| Tengah | **BORANG PERMOHONAN STOK (INDIVIDU KEPADA STOR)** |
| Bawah tajuk | "Jabatan / Unit: [Nama Jabatan]" |

**Jadual Utama (3 Seksyen):**

Jadual dibahagikan kepada 3 seksyen utama yang dipisahkan oleh garisan tebal:

| Seksyen | Lajur | Penerangan |
|---------|-------|------------|
| **Permohonan** | No. Kod | Kod item yang dimohon |
| | Perihal Stok | Nama/penerangan item |
| | Kuantiti Dimohon | Kuantiti yang dimohon oleh pemohon |
| **Pegawai Pelulus** | Baki Sedia Ada | Baki stok semasa kelulusan |
| | Kuantiti Diluluskan | Kuantiti yang diluluskan (dipaparkan dalam **merah tebal**) |
| | Catatan | Catatan pegawai pelulus |
| **Perakuan Penerimaan** | Kuantiti Diterima | Kuantiti yang diterima (dipaparkan dalam **merah tebal**) |
| | Catatan | Catatan penerimaan |

> **Nota:** Jadual memaparkan minimum 7 baris. Jika item kurang dari 7, baris kosong akan ditambah secara automatik.

**Blok Tandatangan (3 Bahagian di bahagian bawah jadual):**

| Blok | Kandungan Auto-isi |
|------|-------------------|
| **Pemohon** | Nama, Jawatan (jika ada), dan Tarikh Mohon auto-isi dari sistem |
| **Pegawai Pelulus** | Nama dan Tarikh Lulus auto-isi dari sistem |
| **Pemohon / Wakil** | Kosong - untuk diisi semasa penerimaan fizikal |

Setiap blok menyediakan ruang tandatangan bertitik-titik.

**Nota Kaki:** "M.S. 12/13"

4. Klik butang **"Cetak Dokumen"** atau tekan **Ctrl + P** untuk mencetak
5. Klik **"Kembali"** untuk kembali ke senarai permohonan

> **Tip:** Semasa mencetak, pastikan tetapan pencetak ditetapkan kepada **A4 Landscape** untuk hasil cetakan yang betul.

---

### 5.4 Kemaskini Profil

**Langkah-langkah:**

1. Klik kad **"Profil Saya"** di Dashboard
2. Halaman profil akan memaparkan maklumat semasa anda

   ![Profil Staf](screenshots/29_staff_profile.png)
   *Rajah 5.6: Halaman Profil Staf*

3. Kemaskini maklumat yang diperlukan:

   | Medan | Penerangan | Boleh Ubah |
   |-------|------------|------------|
   | Gambar Profil | Muat naik dan potong (crop) gambar | Ya |
   | Nama Penuh | Nama penuh anda | Ya |
   | Jawatan | Jawatan anda | Ya |
   | Emel | Alamat emel anda | Ya |
   | Jabatan/Unit | Jabatan anda (paparan sahaja) | Tidak |

4. Klik **"Simpan"** setelah membuat perubahan

#### 5.4.1 Tukar Gambar Profil

**Langkah-langkah:**

1. Klik ikon pensel pada gambar profil
2. Pilih **"Tukar Gambar Profil"**
3. Pilih fail gambar (PNG, JPEG, atau GIF)
4. Gunakan alat potong (crop) untuk melaraskan gambar

   ![Potong Gambar Profil](screenshots/30_crop_profile_picture.png)
   *Rajah 5.7: Alat Potong (Crop) Gambar Profil*

5. Klik **"Potong & Muat Naik"**

#### 5.4.2 Tukar Kata Laluan

**Langkah-langkah:**

1. Dari halaman profil, klik butang **"Tukar Kata Laluan"**

   ![Tukar Kata Laluan](screenshots/31_change_password.png)
   *Rajah 5.8: Borang Tukar Kata Laluan*

2. Masukkan **Kata Laluan Lama**
3. Masukkan **Kata Laluan Baru**
4. Masukkan **Sahkan Kata Laluan**
5. Klik **"Kemaskini"**

---

## 6. SOALAN LAZIM (FAQ)

### S1: Saya terlupa kata laluan. Apa yang perlu saya lakukan?

**Jawapan:**
Klik pautan "Lupa Kata Laluan?" di halaman log masuk dan masukkan ID Staf anda untuk menetapkan semula kata laluan. Jika masih menghadapi masalah, hubungi Pentadbir Sistem.

---

### S2: Mengapa saya tidak boleh edit permohonan saya?

**Jawapan:**
Permohonan hanya boleh diedit jika statusnya masih **"Baru"**. Permohonan yang telah diluluskan atau ditolak tidak boleh diedit.

---

### S3: Bagaimana untuk mencetak borang KEW.PS-8?

**Jawapan:**
Permohonan perlu diluluskan terlebih dahulu. Selepas diluluskan, klik ikon pencetak dan tekan Ctrl + P.

---

### S4: Mengapa produk yang saya cari tidak muncul dalam borang permohonan?

**Jawapan:**
Cuba gunakan penapis kategori untuk mencari produk dalam kategori yang betul. Jika masih tidak muncul, produk tersebut mungkin belum didaftarkan oleh Pentadbir atau telah kehabisan stok.

---

### S5: Pentadbir tidak boleh meluluskan permohonan. Mengapa?

**Jawapan:**
Pentadbir tidak boleh meluluskan permohonan yang dibuat oleh diri sendiri. Permohonan tersebut perlu diluluskan oleh Pentadbir lain.

---

### S6: Bagaimana untuk mengetahui permohonan saya ditolak?

**Jawapan:**
Status permohonan akan bertukar kepada **"Ditolak"** (merah). Klik pada permohonan untuk melihat sebab penolakan yang diberikan oleh Pentadbir dalam catatan admin.

---

### S7: Kuantiti yang diluluskan kurang daripada yang dimohon. Mengapa?

**Jawapan:**
Pentadbir boleh meluluskan kuantiti yang kurang daripada yang dimohon (kelulusan separa) berdasarkan ketersediaan stok semasa. Kuantiti lulus tidak boleh melebihi baki stok yang tersedia.

---

### S8: Bagaimana untuk mendaftar akaun baru?

**Jawapan:**
Klik butang "Daftar Akaun Baru" di halaman utama dan isi maklumat yang diperlukan. Anda juga boleh meminta Pentadbir untuk mendaftarkan akaun anda.

---

### S9: Sistem lambat/tidak boleh diakses. Apa yang perlu dilakukan?

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
| **Unit** | Unit Teknologi Maklumat |
| **Organisasi** | Majlis Perbandaran Kangar |
| **Emel** | [Alamat Emel] |
| **No. Telefon** | [No. Telefon] |
| **Lokasi** | [Alamat Pejabat] |

---

## SENARAI TANGKAPAN SKRIN (SCREENSHOT CHECKLIST)

Berikut adalah senarai penuh tangkapan skrin yang diperlukan untuk manual ini. Sila simpan semua fail dalam folder `screenshots/`.

| No. | Nama Fail | Halaman/Skrin | Rajah | Status |
|-----|-----------|---------------|-------|--------|
| 01 | `01_landing_page.png` | Halaman Utama (Landing Page) | Rajah 3.1 | |
| 02 | `02_login_page.png` | Halaman Log Masuk | Rajah 3.2 | |
| 03 | `03_admin_dashboard.png` | Dashboard Pentadbir | Rajah 4.1 | |
| 04 | `04_product_list.png` | Senarai Produk | Rajah 4.2 | |
| 05 | `05_add_product.png` | Borang Tambah Produk | Rajah 4.3 | |
| 06 | `06_stock_update.png` | Borang Kemaskini Stok | Rajah 4.4 (Stok) | |
| 07 | `07_request_list.png` | Senarai Permohonan (Admin) | Rajah 4.5 | |
| 08 | `08_review_request.png` | Halaman Semak Permohonan | Rajah 4.6 | |
| 09 | `09_registration_page.png` | Halaman Pendaftaran Akaun Baru | Rajah 3.3 | |
| 10 | `10_staff_dashboard.png` | Dashboard Staf | Rajah 5.1 | |
| 11 | `11_kewps8_form.png` | Borang Permohonan KEW.PS-8 | Rajah 5.2 | |
| 12 | `12_item_list.png` | Modal Pengesahan Permohonan (Senarai Item) | Rajah 5.3 | |
| 13 | `13_my_requests.png` | Senarai Permohonan Saya (Staf) | Rajah 5.4 | |
| 14 | `14_forgot_password.png` | Halaman Lupa Kata Laluan | Rajah 3.4 | |
| 15 | `15_first_login_change_password.png` | Halaman Tukar Kata Laluan Pertama Kali | Rajah 3.5 | |
| 16 | `16_edit_product.png` | Borang Kemaskini Produk | Rajah 4.4 | |
| 17 | `17_delete_product_confirm.png` | Dialog Pengesahan Padam Produk | Rajah 4.5 | |
| 18 | `18_category_management.png` | Halaman Pengurusan Kategori | Rajah 4.6 | |
| 19 | `19_kewps8_print.png` | Halaman Cetakan KEW.PS-8 | Rajah 4.7 | |
| 20 | `20_user_list.png` | Senarai Pengguna | Rajah 4.8 | |
| 21 | `21_add_user.png` | Borang Tambah Pengguna Baru | Rajah 4.9 | |
| 22 | `22_reports_dashboard.png` | Dashboard Ringkas Laporan | Rajah 4.10 | |
| 23 | `23_kewps3_form.png` | Borang Jana Laporan KEW.PS-3 | Rajah 4.11 | |
| 24 | `24_kewps3_print.png` | Halaman Cetakan KEW.PS-3 Bahagian B | Rajah 4.12 | |
| 25 | `25_department_analysis.png` | Laporan Analisis Mengikut Jabatan | Rajah 4.13 | |
| 26 | `26_inventory_report.png` | Laporan Inventori Bulanan | Rajah 4.14 | |
| 27 | `27_admin_profile.png` | Halaman Profil Pentadbir | Rajah 4.15 | |
| 28 | `28_edit_request.png` | Halaman Edit Permohonan (Staf) | Rajah 5.5 | |
| 29 | `29_staff_profile.png` | Halaman Profil Staf | Rajah 5.6 | |
| 30 | `30_crop_profile_picture.png` | Alat Potong (Crop) Gambar Profil | Rajah 5.7 | |
| 31 | `31_change_password.png` | Borang Tukar Kata Laluan | Rajah 5.8 | |
| 32 | `32_edit_user.png` | Borang Kemaskini Pengguna | Rajah 4.10 (Pengguna) | |
| 33 | `33_delete_user_confirm.png` | Dialog Pengesahan Padam Pengguna | Rajah 4.11 (Pengguna) | |
| 34 | `34_inventory_print.png` | Halaman Cetakan PDF Laporan Inventori | Rajah 4.15 (Inventori) | |
| 35 | `35_kewps8_staff_print.png` | Halaman Cetakan KEW.PS-8 (Staf) | Rajah 5.5b | |

> **Jumlah tangkapan skrin diperlukan: 35**

---

## SEJARAH DOKUMEN

| Versi | Tarikh | Penulis | Perubahan |
|-------|--------|---------|-----------|
| 1.0 | Januari 2026 | [Nama Anda] | Versi awal |
| 2.0 | Februari 2026 | Unit Teknologi Maklumat | Kemaskini menyeluruh mengikut sistem terkini - pembetulan medan borang, aliran kerja, dan penambahan ciri baharu (pendaftaran staf, pengurusan kategori, laporan analisis jabatan, profil gambar, statistik dashboard) |

---

*Manual ini disediakan untuk kegunaan dalaman Majlis Perbandaran Kangar sahaja.*

---

**&copy; 2026 Unit Teknologi Maklumat, Majlis Perbandaran Kangar. Hak Cipta Terpelihara.**
