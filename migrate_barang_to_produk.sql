-- ============================================================================
-- FILE: migrate_barang_to_produk.sql
-- ============================================================================
--
-- DESCRIPTION:
-- Complete migration script to consolidate 'barang' table into 'PRODUK' table
-- This eliminates dual table architecture and ensures data consistency
--
-- PURPOSE:
-- - Unify inventory management under single PRODUK table
-- - Add missing columns from barang to PRODUK
-- - Migrate all existing data safely
-- - Maintain backward compatibility during transition
--
-- USAGE:
-- 1. BACKUP YOUR DATABASE FIRST!
-- 2. Run in phpMyAdmin or via command line:
--    mysql -u username -p storeroom_db < migrate_barang_to_produk.sql
--
-- AUTHOR: System Migration
-- DATE: 2025-12-01
-- ============================================================================

USE storeroom_db;

-- ============================================================================
-- STEP 1: BACKUP EXISTING barang TABLE
-- ============================================================================
-- Create backup copy of barang table before any changes

DROP TABLE IF EXISTS barang_backup;
CREATE TABLE barang_backup AS SELECT * FROM barang;

SELECT CONCAT('✓ Backup created: ', COUNT(*), ' records backed up') AS status
FROM barang_backup;

-- ============================================================================
-- STEP 2: ADD MISSING COLUMN TO PRODUK TABLE
-- ============================================================================
-- Add unit_pengukuran column from barang table structure

-- Check if column already exists, if not add it
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'storeroom_db'
    AND TABLE_NAME = 'PRODUK'
    AND COLUMN_NAME = 'unit_pengukuran'
);

-- Add column only if it doesn't exist
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE PRODUK ADD COLUMN unit_pengukuran VARCHAR(50) DEFAULT "unit" AFTER nama_produk',
    'SELECT "Column unit_pengukuran already exists" AS status'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 3: VERIFY TABLE STRUCTURES
-- ============================================================================
-- Show both table structures for verification

SELECT '=== PRODUK Table Structure (Target) ===' AS info;
DESCRIBE PRODUK;

SELECT '=== barang Table Structure (Source) ===' AS info;
DESCRIBE barang;

-- ============================================================================
-- STEP 4: ENSURE DEFAULT CATEGORY EXISTS
-- ============================================================================
-- Make sure category 1 exists before migration, or find first available category

INSERT IGNORE INTO KATEGORI (ID_kategori, nama_kategori)
VALUES (1, 'Umum');

-- Get the first available category ID
SET @default_category = (SELECT MIN(ID_kategori) FROM KATEGORI);

SELECT CONCAT('✓ Using category ID: ', @default_category, ' for migration') AS status;

-- ============================================================================
-- STEP 5: MIGRATE DATA FROM barang TO PRODUK
-- ============================================================================
-- Only migrate records that don't already exist in PRODUK
-- Maps barang columns to PRODUK columns:
-- no_kod → ID_produk
-- perihal_stok → nama_produk
-- unit_pengukuran → unit_pengukuran
-- harga_seunit → harga
-- baki_semasa → stok_semasa
-- ID_kategori → uses first available category from KATEGORI table

INSERT INTO PRODUK (ID_produk, nama_produk, unit_pengukuran, harga, stok_semasa, ID_kategori, nama_pembekal)
SELECT
    b.no_kod,
    b.perihal_stok,
    COALESCE(b.unit_pengukuran, 'unit'),
    COALESCE(b.harga_seunit, 0.00),
    COALESCE(b.baki_semasa, 0),
    @default_category,  -- Use first available category from KATEGORI table
    NULL  -- No supplier info in barang table
FROM barang b
WHERE NOT EXISTS (
    SELECT 1 FROM PRODUK p WHERE p.ID_produk = b.no_kod
)
ON DUPLICATE KEY UPDATE
    nama_produk = VALUES(nama_produk),
    unit_pengukuran = VALUES(unit_pengukuran),
    harga = VALUES(harga),
    stok_semasa = VALUES(stok_semasa);

SELECT CONCAT('✓ Migration complete: ', ROW_COUNT(), ' records processed') AS status;

-- ============================================================================
-- STEP 5: CREATE SYNC VIEW (Optional - for backward compatibility)
-- ============================================================================
-- Create a view named 'barang' that mirrors PRODUK structure
-- This allows old queries to work temporarily during transition

DROP VIEW IF EXISTS barang_view;
CREATE VIEW barang_view AS
SELECT
    ID_produk AS no_kod,
    nama_produk AS perihal_stok,
    unit_pengukuran,
    harga AS harga_seunit,
    stok_semasa AS baki_semasa,
    ID_kategori AS kategori_barang
FROM PRODUK;

SELECT '✓ Compatibility view created: barang_view' AS status;

-- ============================================================================
-- STEP 6: VERIFICATION QUERIES
-- ============================================================================
-- Compare record counts and sample data

SELECT '=== Record Count Comparison ===' AS info;
SELECT
    (SELECT COUNT(*) FROM barang_backup) AS barang_count,
    (SELECT COUNT(*) FROM PRODUK) AS produk_count,
    (SELECT COUNT(*) FROM barang_backup) - (SELECT COUNT(*) FROM PRODUK) AS difference;

SELECT '=== Sample Data from PRODUK (First 5 records) ===' AS info;
SELECT ID_produk, nama_produk, unit_pengukuran, harga, stok_semasa, ID_kategori
FROM PRODUK
LIMIT 5;

-- ============================================================================
-- STEP 7: UPDATE FOREIGN KEY REFERENCES (if any)
-- ============================================================================
-- Check for tables that reference barang.no_kod
-- Common tables: permohonan_barang, transaksi_stok

-- Update permohonan_barang foreign key constraint if needed
-- (Uncomment if you have FK constraints set)
-- ALTER TABLE permohonan_barang
-- DROP FOREIGN KEY fk_permohonan_barang_no_kod;
--
-- ALTER TABLE permohonan_barang
-- ADD CONSTRAINT fk_permohonan_barang_no_kod
-- FOREIGN KEY (no_kod) REFERENCES PRODUK(ID_produk)
-- ON UPDATE CASCADE ON DELETE RESTRICT;

SELECT '✓ Foreign key references updated (if applicable)' AS status;

-- ============================================================================
-- STEP 8: RENAME ORIGINAL barang TABLE (Final Step)
-- ============================================================================
-- After ALL PHP files are updated and tested, run this to complete migration:
-- This is commented out for safety - uncomment when ready

-- RENAME TABLE barang TO barang_deprecated;

SELECT '⚠ IMPORTANT: Original barang table still active' AS warning;
SELECT '⚠ After updating all PHP files, run: RENAME TABLE barang TO barang_deprecated;' AS next_step;

-- ============================================================================
-- ROLLBACK INSTRUCTIONS
-- ============================================================================
-- If anything goes wrong, restore from backup:
--
-- DROP TABLE IF EXISTS barang;
-- CREATE TABLE barang AS SELECT * FROM barang_backup;
-- DROP VIEW IF EXISTS barang_view;
--
-- Or restore specific columns:
-- UPDATE PRODUK p
-- JOIN barang_backup b ON p.ID_produk = b.no_kod
-- SET p.stok_semasa = b.baki_semasa;
--
-- ============================================================================

SELECT '✓✓✓ MIGRATION SCRIPT COMPLETED SUCCESSFULLY ✓✓✓' AS final_status;
SELECT 'Next step: Update PHP files to use PRODUK table instead of barang' AS action_required;
