# Perbincangan Ciri: Gambar Produk (Product Photo)

**Tarikh:** 3 Februari 2026
**Dicadangkan oleh:** Penyelia
**Status:** Dalam Perbincangan

---

## 1. RINGKASAN CADANGAN

Penyelia mencadangkan supaya setiap item/produk dalam sistem mempunyai gambar. Ciri ini membolehkan pengguna mengenal pasti item dengan lebih mudah, terutamanya apabila kod item atau nama produk sahaja tidak mencukupi.

---

## 2. SKOP PERUBAHAN

### 2.1 Pangkalan Data

Perlu tambah satu lajur baru dalam jadual `barang`:

```sql
ALTER TABLE barang ADD COLUMN gambar_produk VARCHAR(255) NULL AFTER nama_pembekal;
```

- Menyimpan laluan fail (contoh: `uploads/product_images/ABC01.jpeg`)
- `NULL` bermaksud tiada gambar (boleh guna gambar placeholder)

### 2.2 Direktori Storan

Cipta folder baru:

```
uploads/
  ├── profile_pictures/     (sedia ada - gambar profil staf)
  └── product_images/       (BARU - gambar produk)
```

---

## 3. FAIL YANG PERLU DIUBAH

### Wajib Diubah (Teras):

| No. | Fail | Perubahan | Tahap Kesukaran |
|-----|------|-----------|-----------------|
| 1 | `admin_add_product.php` | Tambah input upload gambar dalam borang | Sederhana |
| 2 | `admin_add_product_process.php` | Proses muat naik gambar + simpan ke DB | Sederhana |
| 3 | `admin_edit_product.php` | Papar gambar sedia ada + tukar/padam gambar | Sederhana |
| 4 | `admin_edit_product_process.php` | Proses kemaskini/ganti gambar | Sederhana |
| 5 | `admin_products.php` | Tambah lajur thumbnail gambar dalam jadual | Rendah |
| 6 | `admin_delete_product.php` | Padam fail gambar apabila produk dipadam | Rendah |

### Patut Diubah (Pengalaman Pengguna):

| No. | Fail | Perubahan | Tahap Kesukaran |
|-----|------|-----------|-----------------|
| 7 | `kewps8_form.php` | Papar gambar kecil semasa staf pilih item | Rendah |
| 8 | `admin_request_edit.php` | Papar gambar item dalam senarai permohonan | Rendah |
| 9 | `manage_requests.php` | Papar gambar item dalam butiran permohonan | Rendah |

### Pilihan (Boleh Ditangguhkan):

| No. | Fail | Perubahan | Catatan |
|-----|------|-----------|---------|
| 10 | `kewps8_print.php` | Gambar kecil pada cetakan KEW.PS-8 | Mungkin tidak sesuai untuk borang rasmi |
| 11 | `kewps3_print.php` | Gambar pada cetakan KEW.PS-3 | Mungkin tidak sesuai untuk borang rasmi |
| 12 | `report_inventory.php` | Gambar dalam laporan inventori | Pilihan |
| 13 | `report_inventory_view.php` | Gambar dalam cetakan inventori | Pilihan |
| 14 | `admin_dashboard.php` | Gambar dalam amaran stok rendah | Pilihan |
| 15 | `staff_dashboard.php` | Gambar dalam aktiviti terkini | Pilihan |

### Fail Baru Yang Perlu Dicipta:

| No. | Fail | Tujuan |
|-----|------|--------|
| 1 | `upload_product_picture.php` | Endpoint AJAX untuk muat naik gambar produk |
| 2 | `delete_product_picture.php` | Endpoint AJAX untuk padam gambar produk |

---

## 4. KELEBIHAN YANG ADA

Sistem sudah mempunyai fungsi muat naik gambar profil yang boleh dijadikan rujukan:

- `upload_profile_picture.php` - Sudah ada validasi, pemprosesan GD, dan simpan ke DB
- `delete_profile_picture.php` - Sudah ada padam fail + kemaskini DB
- PHP GD library sudah digunakan untuk pemprosesan imej
- Corak yang sama boleh diulang untuk gambar produk

---

## 5. SOALAN PERBINCANGAN

### Soalan Utama:

- [ ] **S1:** Adakah gambar produk wajib untuk setiap produk, atau pilihan?
  > Cadangan: Pilihan (dengan gambar placeholder lalai)

- [ ] **S2:** Adakah gambar perlu dipaparkan dalam borang rasmi (KEW.PS-8, KEW.PS-3)?
  > Cadangan: Tidak -- borang rasmi kerajaan tidak biasanya mempunyai gambar produk

- [ ] **S3:** Saiz maksimum fail gambar?
  > Cadangan: 2MB (sama seperti gambar profil)

- [ ] **S4:** Format fail yang diterima?
  > Cadangan: JPEG dan PNG sahaja (sama seperti gambar profil)

- [ ] **S5:** Adakah perlu fungsi crop/potong seperti gambar profil?
  > Cadangan: Tidak perlu -- gambar produk boleh terus dimuat naik tanpa crop

- [ ] **S6:** Adakah produk sedia ada perlu ditambah gambar semua sekali, atau secara berperingkat?
  > Cadangan: Berperingkat -- pentadbir boleh tambah gambar bila-bila masa melalui halaman edit

---

## 6. PENDEKATAN PELAKSANAAN

### Fasa 1 - Teras (Keutamaan Tinggi)
1. Ubah pangkalan data (tambah lajur `gambar_produk`)
2. Cipta `uploads/product_images/` dan fail upload/delete
3. Ubah borang tambah produk (upload gambar)
4. Ubah borang edit produk (lihat/tukar/padam gambar)
5. Papar thumbnail dalam senarai produk (`admin_products.php`)
6. Padam gambar apabila produk dipadam

### Fasa 2 - Pengalaman Pengguna (Keutamaan Sederhana)
7. Papar gambar dalam borang permohonan staf (`kewps8_form.php`)
8. Papar gambar dalam semakan permohonan admin
9. Gambar placeholder lalai untuk produk tanpa gambar

### Fasa 3 - Pilihan (Keutamaan Rendah)
10. Gambar dalam laporan inventori
11. Gambar dalam dashboard (amaran stok)
12. Kemaskini USER_MANUAL.md

---

## 7. CONTOH PAPARAN (MOCKUP)

### Senarai Produk (admin_products.php):

```
| Bil. | Gambar | Kod Item | Nama Produk     | Kategori | ... |
|------|--------|----------|-----------------|----------|-----|
|  1   | [img]  | ABC01    | Pen Pilot       | ATK      | ... |
|  2   | [img]  | ABC02    | Kertas A4       | ATK      | ... |
|  3   | [---]  | ABC03    | Toner HP        | IT       | ... |  <-- placeholder
```

### Borang Tambah/Edit Produk:

```
+----------------------------------+
| Gambar Produk                    |
|  +----------+                    |
|  |          |  [Pilih Gambar]    |
|  | (preview)|  [Padam Gambar]    |
|  |          |                    |
|  +----------+                    |
|                                  |
| Kod Item:  [________]           |
| Nama:      [________]           |
| ...                              |
+----------------------------------+
```

### Borang Permohonan Staf (kewps8_form.php):

```
Perihal Stok: [Dropdown pilihan]
  +------+
  | [img] | Pen Pilot (ABC01) - Baki: 50 unit
  +------+
```

---

## 8. IMPAK TERHADAP JADUAL

### Jadual Lajur Semasa (admin_products.php):
Bil. | Kod Item | Nama Produk | Kategori | Pembekal | Harga | Stok | Status | Tindakan
**(9 lajur)**

### Jadual Lajur Baru:
Bil. | **Gambar** | Kod Item | Nama Produk | Kategori | Pembekal | Harga | Stok | Status | Tindakan
**(10 lajur)**

> **Nota:** Jadual menjadi lebih lebar. Thumbnail gambar perlu kecil (~40x40px) supaya tidak mengganggu paparan.

---

## 9. RISIKO DAN PERTIMBANGAN

| Risiko | Penjelasan | Langkah Mitigasi |
|--------|------------|------------------|
| Saiz storan | Banyak gambar = ruang storan besar | Hadkan saiz fail (2MB), kompres gambar |
| Prestasi halaman | Banyak gambar dalam senarai = halaman lambat | Guna thumbnail kecil, lazy loading |
| Produk sedia ada | Produk lama tiada gambar | Gambar placeholder lalai |
| Borang rasmi | KEW.PS-8/PS-3 tidak sepatutnya ada gambar | Jangan tambah gambar dalam borang rasmi |
| Backup | Gambar perlu disertakan dalam backup | Sertakan folder uploads/ dalam rutin backup |

---

## 10. KEPUTUSAN

| Perkara | Keputusan | Tarikh | Catatan |
|---------|-----------|--------|---------|
| Pelaksanaan ciri | Belum diputuskan | - | Menunggu perbincangan lanjut |
| Gambar wajib/pilihan | Belum diputuskan | - | - |
| Gambar dalam borang rasmi | Belum diputuskan | - | - |
| Fasa pelaksanaan | Belum diputuskan | - | - |

---

*Dokumen ini akan dikemaskini selepas setiap sesi perbincangan.*
