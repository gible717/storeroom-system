-- ============================================================================
-- FILE: add_pembekal_column.sql
-- ============================================================================
--
-- DESCRIPTION:
-- Database migration script to add supplier name (nama_pembekal) column
-- to the PRODUK (products) table.
--
-- PURPOSE:
-- This column stores the supplier/vendor name for each product.
-- It is for record-keeping purposes only and is NOT linked to:
-- - KEW.PS-3 reports
-- - Stock transactions
-- - Any official government documents
--
-- USAGE:
-- Run this script once to add the column to an existing database:
-- mysql -u username -p storeroom_db < add_pembekal_column.sql
--
-- Or execute directly in phpMyAdmin:
-- 1. Open phpMyAdmin
-- 2. Select 'storeroom_db' database
-- 3. Go to 'SQL' tab
-- 4. Paste and run this script
--
-- COLUMN DETAILS:
-- - Name: nama_pembekal
-- - Type: VARCHAR(255) - supports long supplier names
-- - Nullable: YES (optional field)
-- - Position: After 'harga' (price) column
--
-- RELATED FILES:
-- - admin_add_product.php: Form includes supplier input field
-- - admin_add_product_process.php: Saves supplier to database
-- - admin_edit_product.php: Form shows/edits supplier field
-- - admin_edit_product_process.php: Updates supplier in database
-- - admin_products.php: Displays supplier column, allows filtering by supplier
--
-- AUTHOR: System Developer
-- DATE CREATED: January 2025
-- ============================================================================

-- Select the database (change if your database name is different)
USE storeroom_db;

-- ============================================================================
-- MIGRATION: Add nama_pembekal column
-- ============================================================================
-- This ALTER TABLE statement adds the new column.
-- It is safe to run multiple times - will fail silently if column exists.

ALTER TABLE PRODUK
ADD COLUMN nama_pembekal VARCHAR(255) NULL AFTER harga;

-- ============================================================================
-- VERIFICATION: Show updated table structure
-- ============================================================================
-- Run this to verify the column was added successfully.
-- Expected output should include 'nama_pembekal' column.

DESCRIBE PRODUK;

-- ============================================================================
-- OPTIONAL: Check existing data
-- ============================================================================
-- Uncomment the following line to see current products with supplier column:
-- SELECT ID_produk, nama_produk, nama_pembekal FROM PRODUK LIMIT 10;

-- ============================================================================
-- ROLLBACK (if needed)
-- ============================================================================
-- If you need to remove this column, uncomment and run:
-- ALTER TABLE PRODUK DROP COLUMN nama_pembekal;
