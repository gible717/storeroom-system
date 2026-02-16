-- ============================================
-- SEED DATA - Default Accounts & Departments
-- Sistem Pengurusan Bilik Stor dan Inventori
-- ============================================
-- Run AFTER schema.sql
-- Creates default admin and staff accounts
-- ============================================

USE storeroom_db;

-- ============================================
-- 1. DEFAULT DEPARTMENTS
-- ============================================
INSERT INTO jabatan (nama_jabatan) VALUES
('Unit Teknologi Maklumat'),
('Jabatan Kewangan'),
('Jabatan Pentadbiran'),
('Jabatan Kejuruteraan'),
('Jabatan Perancangan & Pembangunan');

-- ============================================
-- 2. DEFAULT ADMIN ACCOUNT
-- ============================================
-- Password: User123
-- Hashed with: password_hash('User123', PASSWORD_BCRYPT)

SET @dept_it = (SELECT ID_jabatan FROM jabatan WHERE nama_jabatan = 'Unit Teknologi Maklumat' LIMIT 1);

INSERT INTO staf (ID_staf, nama, kata_laluan, is_admin, emel, jawatan, ID_jabatan, is_first_login) VALUES
('A001', 'Admin Sistem', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'admin@example.com', 'Pentadbir Sistem', @dept_it, 1);

-- ============================================
-- 3. DEFAULT STAFF ACCOUNT
-- ============================================
-- Password: User123

INSERT INTO staf (ID_staf, nama, kata_laluan, is_admin, emel, jawatan, ID_jabatan, is_first_login) VALUES
('S0001', 'Staf Contoh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'staff@example.com', 'Pembantu Tadbir', @dept_it, 1);

-- ============================================
-- 4. DEFAULT CATEGORIES
-- ============================================
INSERT INTO KATEGORI (nama_kategori, parent_id) VALUES
('Alat Tulis', NULL),
('Peralatan IT', NULL),
('Peralatan Pembersihan', NULL),
('Peralatan Pejabat', NULL);

-- ============================================
-- SEED DATA COMPLETE
-- ============================================
-- Default login:
--   Admin: A001 / User123
--   Staff: S0001 / User123
--
-- IMPORTANT: Change passwords after first login!
-- ============================================
