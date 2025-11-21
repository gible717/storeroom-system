-- Migration: Add nama_pembekal column to PRODUK table
-- Date: 2025-01-21
-- Purpose: Add supplier name field for record keeping purposes

USE storeroom_db;

-- Add nama_pembekal column to PRODUK table
ALTER TABLE PRODUK
ADD COLUMN nama_pembekal VARCHAR(255) NULL AFTER harga;

-- Verify the change
DESCRIBE PRODUK;
