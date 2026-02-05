-- Migration: Add gambar_produk column to barang table
-- Date: 2026-02-04
-- Feature: Product Photo (FEATURE_PRODUCT_PHOTO.md)
--
-- This adds a nullable VARCHAR(255) column to store the product image file path.
-- Example value: 'uploads/product_images/ABC01.jpeg'
-- NULL means no image (placeholder will be shown).
--
-- Run this SQL in phpMyAdmin or MySQL CLI:

ALTER TABLE barang ADD COLUMN gambar_produk VARCHAR(255) NULL AFTER nama_pembekal;
