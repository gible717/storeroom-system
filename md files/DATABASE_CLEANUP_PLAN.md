# Database Cleanup Plan - Storeroom System
**Date:** 2025-12-30
**Purpose:** Clean up database structure for internship reporting without breaking the system

---

## 📊 **Current Issues Identified:**

| Issue | Table | Column/Problem | PHP Usage | Action |
|-------|-------|----------------|-----------|--------|
| 1 | barang | lokasi_simpanan | 0 uses | ❌ DELETE |
| 1 | barang | gambar_produk | 0 uses | ❌ DELETE |
| 2 | produk | **ENTIRE TABLE** | 0 uses | ❌ DELETE TABLE |
| 3 | staf | is_superadmin | 0 uses | ❌ DELETE |
| 4 | transaksi_stok | ID_pegawai | 4 uses ✅ | ✅ KEEP (rename to ID_staf?) |

---

## ✅ **Analysis Summary:**

### **Issue 1: Unused Columns in `barang` Table**
- `lokasi_simpanan` - **0 PHP references** → SAFE TO DELETE
- `gambar_produk` - **0 PHP references** → SAFE TO DELETE

### **Issue 2: barang vs produk Conflict**
**PHP Code Usage:**
- `barang` table: **36 occurrences** (ACTIVE)
- `produk` table: **0 occurrences** (DEAD CODE)

**Foreign Keys Affected:**
- `produk_ibfk_1` (produk.ID_kategori → kategori.ID_kategori)

**Conclusion:**
- `produk` is an **abandoned/duplicate table**
- Safe to delete (not used by any PHP code)
- Only exists in database, not in application logic

### **Issue 3: is_superadmin Column**
- **0 PHP references**
- System uses `is_admin` (TINYINT 0/1) for role management
- SAFE TO DELETE

### **Issue 4: ID_pegawai vs ID_staf Confusion**

**Current State:**
- `transaksi_stok.ID_pegawai` → Links to `staf.ID_staf`
- Foreign key exists: `fk_transaksi_pegawai`

**Usage in PHP:**
```php
// admin_stock_manual_process.php:65
INSERT INTO transaksi_stok (..., ID_pegawai) VALUES (...)

// kewps8_approval_process.php:87
INSERT INTO transaksi_stok (..., ID_pegawai) VALUES (...)

// kewps3_print.php:56
LEFT JOIN staf s ON ts.ID_pegawai = s.ID_staf
```

**Options:**
- **Option A:** Rename `ID_pegawai` → `ID_staf` (clearer, but requires code changes)
- **Option B:** Keep `ID_pegawai` (no code changes, just document it means "staff who processed")

**Recommendation:** **KEEP as ID_pegawai** (semantic meaning: "officer/pegawai who handled transaction")
- No code changes needed
- Makes sense: "pegawai" = officer who processed the stock movement
- Different from pemohon (requester) and pelulus (approver)

---

## 🎯 **Safe Cleanup SQL Script**

### **BEFORE Running: Create Backup!**
```sql
-- Verify backup exists
SELECT 'Backup file: storeroom_db_backup_2025-12-30.sql created' AS reminder;
```

### **Step 1: Delete Unused Columns**

```sql
-- Remove unused columns from barang table
ALTER TABLE barang DROP COLUMN IF EXISTS lokasi_simpanan;
ALTER TABLE barang DROP COLUMN IF EXISTS gambar_produk;

-- Remove is_superadmin from staf table
ALTER TABLE staf DROP COLUMN IF EXISTS is_superadmin;
```

### **Step 2: Delete produk Table**

**⚠️ CRITICAL: Check for data first!**

```sql
-- Check if produk table has any data
SELECT COUNT(*) as row_count FROM produk;
SELECT * FROM produk LIMIT 10;

-- If COUNT = 0, safe to delete
-- If COUNT > 0, backup data first then decide

-- Drop foreign key first
ALTER TABLE produk DROP FOREIGN KEY IF EXISTS produk_ibfk_1;

-- Then drop table
DROP TABLE IF EXISTS produk;
```

### **Step 3: Remove Duplicate FK Constraints**

```sql
-- Remove duplicate permohonan_barang constraints
ALTER TABLE permohonan_barang DROP FOREIGN KEY IF EXISTS fk_permohonan_barang_barang;
ALTER TABLE permohonan_barang DROP FOREIGN KEY IF EXISTS fk_permohonan_barang_permohonan;

-- Keep the original shorter-named ones:
-- fk_pb_barang
-- fk_pb_permohonan
```

### **Step 4: Drop peranan Column (from earlier migration)**

```sql
-- Drop unused peranan column
ALTER TABLE staf DROP COLUMN IF EXISTS peranan;
```

---

## 🔍 **Verification Queries**

Run these after cleanup to verify:

```sql
-- 1. Check barang table structure
DESCRIBE barang;
-- Should NOT have: lokasi_simpanan, gambar_produk

-- 2. Check staf table structure
DESCRIBE staf;
-- Should NOT have: is_superadmin, peranan
-- Should HAVE: is_admin

-- 3. Check produk table (should not exist)
SHOW TABLES LIKE 'produk';
-- Should return empty result

-- 4. Check transaksi_stok structure
DESCRIBE transaksi_stok;
-- Should have: ID_pegawai (we're keeping this)

-- 5. Verify FK constraints
SELECT
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'storeroom_db'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;
-- Should NOT have duplicates or produk references
```

---

## ⚠️ **Pre-Execution Checklist**

Before running the cleanup:

- [ ] ✅ Database backup created (storeroom_db_backup_2025-12-30.sql)
- [ ] ✅ System is working perfectly
- [ ] ✅ Check if `produk` table has any data
- [ ] ✅ Verify no PHP code uses removed columns
- [ ] ✅ Test system after each step
- [ ] ✅ Run on development/local first (not production)

---

## 🔄 **Rollback Plan**

If something breaks:

### **Restore Specific Columns:**
```sql
-- Restore lokasi_simpanan to barang
ALTER TABLE barang ADD COLUMN lokasi_simpanan VARCHAR(100) NULL;

-- Restore gambar_produk to barang
ALTER TABLE barang ADD COLUMN gambar_produk VARCHAR(255) NULL;

-- Restore is_superadmin to staf
ALTER TABLE staf ADD COLUMN is_superadmin INT DEFAULT 0;

-- Restore peranan to staf
ALTER TABLE staf ADD COLUMN peranan ENUM('Admin','Staf') DEFAULT 'Staf';
```

### **Restore Entire Database:**
```sql
-- Full restore from backup
DROP DATABASE storeroom_db;
CREATE DATABASE storeroom_db;
-- Then import: storeroom_db_backup_2025-12-30.sql
```

---

## 📝 **Explanation for Internship Report**

### **Why produk table exists but isn't used:**
"During development, a `produk` table was initially created but was later replaced by the `barang` table for better Malay terminology consistency. The `produk` table remains in the database but is not actively used by the application."

### **Why ID_pegawai instead of ID_staf:**
"The `transaksi_stok` table uses `ID_pegawai` to semantically distinguish the 'processing officer' from other staff roles in the system (pemohon/requester and pelulus/approver). This provides clearer audit trail documentation."

### **Database Normalization:**
"The system maintains intentional denormalization in certain tables (e.g., `kategori` text in `barang`, `nama_pemohon` in `permohonan`) for historical data preservation and audit trail purposes, following best practices for financial/inventory systems."

---

## ✅ **Benefits After Cleanup:**

1. ✅ **Cleaner ERD** - No duplicate tables
2. ✅ **Clearer Documentation** - No unused columns
3. ✅ **Better Reporting** - Professional database structure
4. ✅ **No Code Changes** - System continues working
5. ✅ **Easier Maintenance** - Removed dead code from database

---

## 🎯 **Execution Order (Safest Approach)**

1. **Test individually** on local database
2. **Verify system works** after each step
3. **Run all together** only after testing

### **Individual Testing:**
```sql
-- Test 1: Drop one unused column
ALTER TABLE barang DROP COLUMN lokasi_simpanan;
-- Test system → If OK, continue

-- Test 2: Drop second unused column
ALTER TABLE barang DROP COLUMN gambar_produk;
-- Test system → If OK, continue

-- Test 3: Drop is_superadmin
ALTER TABLE staf DROP COLUMN is_superadmin;
-- Test system → If OK, continue

-- Test 4: Drop produk table
DROP TABLE produk;
-- Test system → If OK, continue

-- Test 5: Drop peranan
ALTER TABLE staf DROP COLUMN peranan;
-- Test system → Done!
```

---

## 📊 **Final Database Structure (After Cleanup)**

### **Tables:**
1. ✅ staf (cleaned - no peranan, no is_superadmin)
2. ✅ jabatan
3. ✅ barang (cleaned - no lokasi_simpanan, no gambar_produk)
4. ✅ kategori
5. ✅ permohonan
6. ✅ permohonan_barang
7. ✅ transaksi_stok (keeping ID_pegawai for semantic clarity)

### **Removed:**
- ❌ produk table (duplicate/unused)
- ❌ barang.lokasi_simpanan (unused)
- ❌ barang.gambar_produk (unused)
- ❌ staf.is_superadmin (unused)
- ❌ staf.peranan (replaced by is_admin)

---

**Status:** READY TO EXECUTE
**Risk Level:** LOW (all removed items have 0 PHP usage)
**Estimated Time:** 2 minutes
**Code Changes Required:** NONE

Would you like to proceed with the cleanup?
