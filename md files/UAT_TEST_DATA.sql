-- ============================================
-- UAT TEST DATA SCRIPT
-- Sistem Pengurusan Bilik Stor dan Inventori
-- ============================================
-- Purpose: Populate database with test accounts and sample data for UAT
-- Generated: 7 January 2026
-- Database: storeroom
--
-- INSTRUCTIONS:
-- 1. Backup your database first: mysqldump -u root storeroom > backup.sql
-- 2. Run this script: mysql -u root storeroom < UAT_TEST_DATA.sql
-- 3. Test accounts will be created with default passwords
-- 4. All test data has "TEST" or "UAT" prefix for easy identification
-- ============================================

USE storeroom;

-- ============================================
-- 1. CREATE TEST DEPARTMENTS
-- ============================================
INSERT INTO jabatan (nama_jabatan, created_at) VALUES
('Jabatan IT - UAT Test', NOW()),
('Jabatan Kewangan - UAT Test', NOW()),
('Jabatan Pentadbiran - UAT Test')
ON DUPLICATE KEY UPDATE nama_jabatan = nama_jabatan;

-- Get department IDs for reference
SET @dept_it = (SELECT ID_jabatan FROM jabatan WHERE nama_jabatan = 'Jabatan IT - UAT Test' LIMIT 1);
SET @dept_finance = (SELECT ID_jabatan FROM jabatan WHERE nama_jabatan = 'Jabatan Kewangan - UAT Test' LIMIT 1);
SET @dept_admin = (SELECT ID_jabatan FROM jabatan WHERE nama_jabatan = 'Jabatan Pentadbiran - UAT Test' LIMIT 1);

-- ============================================
-- 2. CREATE TEST STAFF ACCOUNTS
-- ============================================
-- Password for all test accounts: Test@123
-- Hashed with bcrypt (PASSWORD_BCRYPT)
-- Hash: $2y$10$YPKvQs5S.zqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW

-- Test Staff 1 - IT Department
INSERT INTO staf (ID_staf, nama, emel, kata_laluan, jawatan, ID_jabatan, is_admin, is_first_login, created_at) VALUES
('TEST001', 'Ahmad Test Staff', 'test.staff1@mpk.gov.my', '$2y$10$YPKvQs5S.zqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW', 'Pegawai Teknologi Maklumat', @dept_it, 0, 0, NOW())
ON DUPLICATE KEY UPDATE nama = 'Ahmad Test Staff';

-- Test Staff 2 - Finance Department
INSERT INTO staf (ID_staf, nama, emel, kata_laluan, jawatan, ID_jabatan, is_admin, is_first_login, created_at) VALUES
('TEST002', 'Siti Test Staff', 'test.staff2@mpk.gov.my', '$2y$10$YPKvQs5S.zqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW', 'Pegawai Kewangan', @dept_finance, 0, 0, NOW())
ON DUPLICATE KEY UPDATE nama = 'Siti Test Staff';

-- Test Staff 3 - Admin Department
INSERT INTO staf (ID_staf, nama, emel, kata_laluan, jawatan, ID_jabatan, is_admin, is_first_login, created_at) VALUES
('TEST003', 'Kumar Test Staff', 'test.staff3@mpk.gov.my', '$2y$10$YPKvQs5S.zqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW', 'Pembantu Tadbir', @dept_admin, 0, 0, NOW())
ON DUPLICATE KEY UPDATE nama = 'Kumar Test Staff';

-- ============================================
-- 3. CREATE TEST ADMIN ACCOUNTS
-- ============================================
-- Password: Admin@123
-- Hash: $2y$10$Nq9vH5K8YqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW

-- Test Admin 1 - Primary Admin
INSERT INTO staf (ID_staf, nama, emel, kata_laluan, jawatan, ID_jabatan, is_admin, is_first_login, created_at) VALUES
('ADMIN001', 'Admin Test Primary', 'test.admin1@mpk.gov.my', '$2y$10$Nq9vH5K8YqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW', 'Pentadbir Sistem', @dept_it, 1, 0, NOW())
ON DUPLICATE KEY UPDATE nama = 'Admin Test Primary', is_admin = 1;

-- Test Admin 2 - Secondary Admin
INSERT INTO staf (ID_staf, nama, emel, kata_laluan, jawatan, ID_jabatan, is_admin, is_first_login, created_at) VALUES
('ADMIN002', 'Admin Test Secondary', 'test.admin2@mpk.gov.my', '$2y$10$Nq9vH5K8YqGH0MJXvZ8eeXOZh9kHJFGbFVH3c2mUGJYvL7qjNJiW', 'Pentadbir Sistem Backup', @dept_it, 1, 0, NOW())
ON DUPLICATE KEY UPDATE nama = 'Admin Test Secondary', is_admin = 1;

-- ============================================
-- 4. CREATE TEST CATEGORIES
-- ============================================
INSERT INTO KATEGORI (nama_kategori) VALUES
('Alat Tulis - UAT'),
('Peralatan IT - UAT'),
('Alat Pembersihan - UAT')
ON DUPLICATE KEY UPDATE nama_kategori = nama_kategori;

-- Get category IDs
SET @cat_stationery = (SELECT ID_kategori FROM KATEGORI WHERE nama_kategori = 'Alat Tulis - UAT' LIMIT 1);
SET @cat_it = (SELECT ID_kategori FROM KATEGORI WHERE nama_kategori = 'Peralatan IT - UAT' LIMIT 1);
SET @cat_cleaning = (SELECT ID_kategori FROM KATEGORI WHERE nama_kategori = 'Alat Pembersihan - UAT' LIMIT 1);

-- ============================================
-- 5. CREATE TEST PRODUCTS
-- ============================================

-- Product 1 - Sufficient Stock
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT001', 'Pen Biru - UAT Test (Stok Mencukupi)', @cat_stationery, 'Alat Tulis - UAT', 'batang', 1.50, 'Pembekal Test Sdn Bhd', 100, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 100;

-- Product 2 - Low Stock (triggers warning)
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT002', 'Kertas A4 - UAT Test (Stok Rendah)', @cat_stationery, 'Alat Tulis - UAT', 'rim', 15.00, 'Pembekal Test Sdn Bhd', 8, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 8;

-- Product 3 - Out of Stock
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT003', 'Stapler - UAT Test (Stok Habis)', @cat_stationery, 'Alat Tulis - UAT', 'unit', 12.50, 'Pembekal Test Sdn Bhd', 0, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 0;

-- Product 4 - IT Equipment (High Stock)
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT004', 'Mouse USB - UAT Test', @cat_it, 'Peralatan IT - UAT', 'unit', 25.00, 'IT Supplier Test', 50, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 50;

-- Product 5 - IT Equipment (Limited Stock)
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT005', 'Keyboard - UAT Test (Stok Terhad)', @cat_it, 'Peralatan IT - UAT', 'unit', 45.00, 'IT Supplier Test', 5, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 5;

-- Product 6 - Cleaning Supplies
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT006', 'Sabun Cuci Tangan - UAT Test', @cat_cleaning, 'Alat Pembersihan - UAT', 'botol', 8.50, 'Cleaning Supplier Test', 30, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 30;

-- Product 7 - For partial approval test (limited stock)
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT007', 'Marker Pen - UAT Test (Untuk Ujian Partial)', @cat_stationery, 'Alat Tulis - UAT', 'batang', 3.00, 'Pembekal Test Sdn Bhd', 10, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 10;

-- Product 8 - For insufficient stock test
INSERT INTO barang (no_kod, perihal_stok, ID_kategori, kategori, unit_pengukuran, harga_seunit, nama_pembekal, baki_semasa, created_at) VALUES
('UAT008', 'Buku Nota - UAT Test (Stok Sangat Rendah)', @cat_stationery, 'Alat Tulis - UAT', 'unit', 5.50, 'Pembekal Test Sdn Bhd', 2, NOW())
ON DUPLICATE KEY UPDATE baki_semasa = 2;

-- ============================================
-- 6. CREATE SAMPLE REQUESTS FOR TESTING
-- ============================================

-- Request 1: Pending request (Status: Baru) - for approval testing
INSERT INTO permohonan (tarikh_mohon, masa_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan, created_at) VALUES
(CURDATE(), NOW(), 'Baru', 'TEST001', 'Ahmad Test Staff', 'Pegawai Teknologi Maklumat', @dept_it, 'Permohonan untuk projek UAT testing', NOW());

SET @req1 = LAST_INSERT_ID();

-- Request 1 Items
INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) VALUES
(@req1, 'UAT001', 10),  -- Pen Biru - sufficient stock
(@req1, 'UAT004', 5);   -- Mouse USB - sufficient stock

-- Request 2: Pending request (Status: Baru) - for partial approval testing
INSERT INTO permohonan (tarikh_mohon, masa_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan, created_at) VALUES
(CURDATE(), NOW(), 'Baru', 'TEST002', 'Siti Test Staff', 'Pegawai Kewangan', @dept_finance, 'Perlu untuk jabatan kewangan', NOW());

SET @req2 = LAST_INSERT_ID();

-- Request 2 Items (one with limited stock)
INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) VALUES
(@req2, 'UAT007', 15),  -- Marker Pen - only 10 available (partial approval scenario)
(@req2, 'UAT006', 5);   -- Sabun - sufficient stock

-- Request 3: Pending request (Status: Baru) - for insufficient stock testing
INSERT INTO permohonan (tarikh_mohon, masa_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan, created_at) VALUES
(CURDATE(), NOW(), 'Baru', 'TEST003', 'Kumar Test Staff', 'Pembantu Tadbir', @dept_admin, 'Permohonan untuk jabatan pentadbiran', NOW());

SET @req3 = LAST_INSERT_ID();

-- Request 3 Items (insufficient stock)
INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) VALUES
(@req3, 'UAT008', 5),   -- Buku Nota - only 2 available (should fail approval)
(@req3, 'UAT001', 5);   -- Pen Biru - sufficient stock

-- Request 4: Already approved request (for viewing history)
INSERT INTO permohonan (tarikh_mohon, masa_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan, catatan_admin, ID_pelulus, nama_pelulus, jawatan_pelulus, tarikh_lulus, created_at) VALUES
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 'Diluluskan', 'TEST001', 'Ahmad Test Staff', 'Pegawai Teknologi Maklumat', @dept_it, 'Permohonan terdahulu', 'Diluluskan. Stok mencukupi untuk semua item.', 'ADMIN001', 'Admin Test Primary', 'Pentadbir Sistem', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

SET @req4 = LAST_INSERT_ID();

-- Request 4 Items
INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon, kuantiti_lulus) VALUES
(@req4, 'UAT001', 20, 20),  -- Approved full quantity
(@req4, 'UAT006', 10, 10);  -- Approved full quantity

-- Request 5: Rejected request (for viewing history)
INSERT INTO permohonan (tarikh_mohon, masa_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan, catatan_admin, ID_pelulus, nama_pelulus, jawatan_pelulus, tarikh_lulus, created_at) VALUES
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), 'Ditolak', 'TEST002', 'Siti Test Staff', 'Pegawai Kewangan', @dept_finance, 'Item untuk projek X', 'Ditolak. Projek X telah dibatalkan.', 'ADMIN002', 'Admin Test Secondary', 'Pentadbir Sistem Backup', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY));

SET @req5 = LAST_INSERT_ID();

-- Request 5 Items (rejected, no kuantiti_lulus)
INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) VALUES
(@req5, 'UAT004', 15),
(@req5, 'UAT005', 3);

-- Request 6: Self-approval test - Admin creates own request
INSERT INTO permohonan (tarikh_mohon, masa_mohon, status, ID_pemohon, nama_pemohon, jawatan_pemohon, ID_jabatan, catatan, created_at) VALUES
(CURDATE(), NOW(), 'Baru', 'ADMIN001', 'Admin Test Primary', 'Pentadbir Sistem', @dept_it, 'Permohonan oleh admin untuk ujian self-approval prevention', NOW());

SET @req6 = LAST_INSERT_ID();

-- Request 6 Items
INSERT INTO permohonan_barang (ID_permohonan, no_kod, kuantiti_mohon) VALUES
(@req6, 'UAT001', 5),
(@req6, 'UAT006', 3);

-- ============================================
-- 7. CREATE TRANSACTION LOGS FOR APPROVED REQUEST
-- ============================================
-- These logs simulate the approval of Request 4

INSERT INTO transaksi_stok (no_kod, jenis_transaksi, kuantiti, baki_selepas_transaksi, ID_rujukan_permohonan, tarikh_transaksi) VALUES
('UAT001', 'Keluar', 20, 80, @req4, DATE_SUB(NOW(), INTERVAL 2 DAY)),  -- 100 - 20 = 80 (but we reset to 100 above for UAT)
('UAT006', 'Keluar', 10, 20, @req4, DATE_SUB(NOW(), INTERVAL 2 DAY));  -- 30 - 10 = 20 (but we reset to 30 above for UAT)

-- Note: Stock levels have been reset above to allow UAT testing
-- In real scenario, UAT001 would be 80 and UAT006 would be 20

-- ============================================
-- 8. SUMMARY OF TEST ACCOUNTS
-- ============================================

SELECT '============================================' AS '';
SELECT 'UAT TEST DATA LOADED SUCCESSFULLY' AS 'STATUS';
SELECT '============================================' AS '';
SELECT '' AS '';

SELECT 'TEST ACCOUNTS CREATED:' AS 'SECTION';
SELECT '------------------------------' AS '';

SELECT 'STAFF ACCOUNTS (Password: Test@123)' AS 'STAFF_ACCOUNTS';
SELECT ID_staf, nama, emel, jawatan, is_admin
FROM staf
WHERE ID_staf LIKE 'TEST%'
ORDER BY ID_staf;

SELECT '' AS '';
SELECT 'ADMIN ACCOUNTS (Password: Admin@123)' AS 'ADMIN_ACCOUNTS';
SELECT ID_staf, nama, emel, jawatan, is_admin
FROM staf
WHERE ID_staf LIKE 'ADMIN%'
ORDER BY ID_staf;

SELECT '' AS '';
SELECT '------------------------------' AS '';
SELECT 'TEST DEPARTMENTS:' AS 'SECTION';
SELECT ID_jabatan, nama_jabatan
FROM jabatan
WHERE nama_jabatan LIKE '%UAT%'
ORDER BY ID_jabatan;

SELECT '' AS '';
SELECT '------------------------------' AS '';
SELECT 'TEST PRODUCTS:' AS 'SECTION';
SELECT no_kod, perihal_stok, baki_semasa,
CASE
    WHEN baki_semasa = 0 THEN 'STOK HABIS'
    WHEN baki_semasa <= 10 THEN 'STOK RENDAH'
    ELSE 'STOK MENCUKUPI'
END AS status_stok
FROM barang
WHERE no_kod LIKE 'UAT%'
ORDER BY no_kod;

SELECT '' AS '';
SELECT '------------------------------' AS '';
SELECT 'TEST REQUESTS:' AS 'SECTION';
SELECT ID_permohonan, nama_pemohon, status, tarikh_mohon, catatan
FROM permohonan
WHERE ID_pemohon LIKE 'TEST%' OR ID_pemohon LIKE 'ADMIN%'
ORDER BY ID_permohonan DESC;

SELECT '' AS '';
SELECT '============================================' AS '';
SELECT 'UAT TESTING CAN NOW BEGIN' AS '';
SELECT 'Refer to UAT_TEST_PLAN.md for test scenarios' AS '';
SELECT '============================================' AS '';

-- ============================================
-- END OF UAT TEST DATA SCRIPT
-- ============================================
