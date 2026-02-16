-- ============================================
-- DATABASE SCHEMA
-- Sistem Pengurusan Bilik Stor dan Inventori
-- ============================================
-- Purpose: Create all tables and constraints for fresh installation
-- Database: storeroom_db
--
-- INSTRUCTIONS:
-- 1. Create database: CREATE DATABASE storeroom_db;
-- 2. Run this script: mysql -u root storeroom_db < schema.sql
-- 3. Then run seed_data.sql for default admin/staff accounts
-- ============================================

CREATE DATABASE IF NOT EXISTS storeroom_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE storeroom_db;

-- ============================================
-- 1. JABATAN (Departments)
-- ============================================
CREATE TABLE jabatan (
    ID_jabatan INT AUTO_INCREMENT PRIMARY KEY,
    nama_jabatan VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. STAF (Users/Staff)
-- ============================================
CREATE TABLE staf (
    ID_staf VARCHAR(10) PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    kata_laluan VARCHAR(255) NOT NULL,
    is_admin TINYINT NOT NULL DEFAULT 0 COMMENT '0=Staff, 1=Admin',
    emel VARCHAR(255) UNIQUE,
    no_telefon VARCHAR(20),
    jawatan VARCHAR(255),
    ID_jabatan INT NULL,
    gambar_profil VARCHAR(500),
    is_first_login TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. KATEGORI (Product Categories)
-- ============================================
CREATE TABLE KATEGORI (
    ID_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(255) NOT NULL UNIQUE,
    parent_id INT NULL COMMENT 'NULL = main category, value = subcategory of parent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. BARANG (Products/Inventory)
-- ============================================
CREATE TABLE barang (
    no_kod VARCHAR(20) PRIMARY KEY,
    perihal_stok VARCHAR(500) NOT NULL,
    ID_kategori INT NULL,
    kategori VARCHAR(255) COMMENT 'Denormalized category name for historical record',
    unit_pengukuran VARCHAR(50),
    harga_seunit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    nama_pembekal VARCHAR(255),
    baki_semasa INT NOT NULL DEFAULT 0,
    gambar_produk VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. PERMOHONAN (Request Headers)
-- ============================================
CREATE TABLE permohonan (
    ID_permohonan INT AUTO_INCREMENT PRIMARY KEY,
    tarikh_mohon DATE NOT NULL,
    masa_mohon DATETIME,
    status VARCHAR(20) NOT NULL DEFAULT 'Baru' COMMENT 'Baru, Diluluskan, Ditolak, Diterima',
    ID_pemohon VARCHAR(10) NOT NULL,
    nama_pemohon VARCHAR(255) NOT NULL,
    jawatan_pemohon VARCHAR(255),
    ID_jabatan INT NULL,
    catatan TEXT COMMENT 'Staff remarks',
    catatan_admin TEXT COMMENT 'Admin approval/rejection remarks',
    ID_pelulus VARCHAR(10) NULL,
    nama_pelulus VARCHAR(255),
    jawatan_pelulus VARCHAR(255),
    tarikh_lulus DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. PERMOHONAN_BARANG (Request Line Items)
-- ============================================
CREATE TABLE permohonan_barang (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    ID_permohonan INT NOT NULL,
    no_kod VARCHAR(20) NOT NULL,
    kuantiti_mohon INT NOT NULL,
    kuantiti_lulus INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. TRANSAKSI_STOK (Stock Transaction Log)
-- ============================================
CREATE TABLE transaksi_stok (
    ID_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    no_kod VARCHAR(20) NOT NULL,
    jenis_transaksi VARCHAR(10) NOT NULL COMMENT 'Masuk (In) or Keluar (Out)',
    kuantiti INT NOT NULL,
    baki_selepas_transaksi INT NOT NULL,
    ID_rujukan_permohonan INT NULL COMMENT 'Related request ID (NULL for manual adjustments)',
    ID_pegawai VARCHAR(10) NULL COMMENT 'Officer who processed',
    terima_dari_keluar_kepada VARCHAR(255) COMMENT 'Department/unit reference',
    tarikh_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    catatan TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. FOREIGN KEY CONSTRAINTS
-- ============================================

-- KATEGORI self-reference (subcategories)
ALTER TABLE KATEGORI
ADD CONSTRAINT fk_kategori_parent
FOREIGN KEY (parent_id) REFERENCES KATEGORI(ID_kategori)
ON DELETE SET NULL ON UPDATE CASCADE;

-- staf → jabatan
ALTER TABLE staf
ADD CONSTRAINT fk_staf_jabatan
FOREIGN KEY (ID_jabatan) REFERENCES jabatan(ID_jabatan)
ON DELETE SET NULL ON UPDATE CASCADE;

-- barang → KATEGORI
ALTER TABLE barang
ADD CONSTRAINT fk_barang_kategori
FOREIGN KEY (ID_kategori) REFERENCES KATEGORI(ID_kategori)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- permohonan → staf (pemohon)
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_pemohon
FOREIGN KEY (ID_pemohon) REFERENCES staf(ID_staf)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- permohonan → staf (pelulus)
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_pelulus
FOREIGN KEY (ID_pelulus) REFERENCES staf(ID_staf)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- permohonan → jabatan
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_jabatan
FOREIGN KEY (ID_jabatan) REFERENCES jabatan(ID_jabatan)
ON DELETE SET NULL ON UPDATE CASCADE;

-- permohonan_barang → permohonan
ALTER TABLE permohonan_barang
ADD CONSTRAINT fk_permohonan_barang_permohonan
FOREIGN KEY (ID_permohonan) REFERENCES permohonan(ID_permohonan)
ON DELETE CASCADE ON UPDATE CASCADE;

-- permohonan_barang → barang
ALTER TABLE permohonan_barang
ADD CONSTRAINT fk_permohonan_barang_barang
FOREIGN KEY (no_kod) REFERENCES barang(no_kod)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- transaksi_stok → barang
ALTER TABLE transaksi_stok
ADD CONSTRAINT fk_transaksi_stok_barang
FOREIGN KEY (no_kod) REFERENCES barang(no_kod)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================
-- 9. TELEGRAM REMINDER LOG (auto-created by system)
-- ============================================
CREATE TABLE IF NOT EXISTS telegram_reminder_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reminder_type VARCHAR(50) NOT NULL,
    reminder_date DATE NOT NULL,
    sent_at DATETIME NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY unique_reminder (reminder_type, reminder_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- SCHEMA COMPLETE
-- ============================================
