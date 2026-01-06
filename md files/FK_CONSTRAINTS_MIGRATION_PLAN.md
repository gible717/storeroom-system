# Foreign Key Constraints Migration Plan
**Date:** 2025-12-30
**System:** Storeroom Management System - MPK

---

## 📊 Analysis Results

### Your Answers Summary:
1. **Department Deletion:** B - Set staff's department to NULL
2. **Staff Deletion:** Keep records (preserve history)
3. **Product Deletion:** Keep records (preserve history)
4. **Request-Item Deletion:** Staff can delete their own requests before approval
5. **Category Usage:** System uses BOTH ID_kategori and kategori (denormalized)

### Code Analysis Findings:
- **ID_kategori usage:** 40 occurrences (used in forms, JOINs, inserts)
- **kategori text usage:** Synced automatically from KATEGORI table
- **Pattern:** Lines 28-38 in admin_add_product_process.php fetch category name and store both
- **Conclusion:** Denormalization is GOOD - keeps historical category names even if category is renamed

---

## 🎯 Recommended FK Constraints

Based on your business rules, here's the custom plan:

### **Priority 1: CRITICAL (Prevent Data Corruption)**

#### 1. permohonan_barang → permohonan
**Purpose:** Prevent orphaned request items

```sql
ALTER TABLE permohonan_barang
ADD CONSTRAINT fk_permohonan_barang_permohonan
FOREIGN KEY (ID_permohonan) REFERENCES permohonan(ID_permohonan)
ON DELETE CASCADE
ON UPDATE CASCADE;
```

**Why CASCADE:** When staff deletes their own request, all items should be deleted too.

---

#### 2. permohonan_barang → barang
**Purpose:** Ensure request items reference valid products

```sql
ALTER TABLE permohonan_barang
ADD CONSTRAINT fk_permohonan_barang_barang
FOREIGN KEY (no_kod) REFERENCES barang(no_kod)
ON DELETE RESTRICT
ON UPDATE CASCADE;
```

**Why RESTRICT:** Cannot delete products that are in active/past requests (preserves history).

---

### **Priority 2: IMPORTANT (Data Integrity)**

#### 3. staf → jabatan
**Purpose:** Ensure staff belong to valid departments

```sql
ALTER TABLE staf
ADD CONSTRAINT fk_staf_jabatan
FOREIGN KEY (ID_jabatan) REFERENCES jabatan(ID_jabatan)
ON DELETE SET NULL
ON UPDATE CASCADE;
```

**Why SET NULL:** Your answer #1 - When department deleted, staff becomes "unassigned" (NULL).

---

#### 4. permohonan → jabatan
**Purpose:** Ensure requests reference valid departments

```sql
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_jabatan
FOREIGN KEY (ID_jabatan) REFERENCES jabatan(ID_jabatan)
ON DELETE SET NULL
ON UPDATE CASCADE;
```

**Why SET NULL:** Historical requests can exist even if department deleted.

---

#### 5. permohonan → staf (pemohon)
**Purpose:** Ensure requests reference valid staff (requester)

```sql
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_pemohon
FOREIGN KEY (ID_pemohon) REFERENCES staf(ID_staf)
ON DELETE RESTRICT
ON UPDATE CASCADE;
```

**Why RESTRICT:** Your answer #2 - Cannot delete staff who have made requests (preserve history).

---

#### 6. permohonan → staf (pelulus)
**Purpose:** Ensure approved requests reference valid approver

```sql
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_pelulus
FOREIGN KEY (ID_pelulus) REFERENCES staf(ID_staf)
ON DELETE RESTRICT
ON UPDATE CASCADE;
```

**Why RESTRICT:** Cannot delete admin who has approved requests (audit trail).

---

### **Priority 3: RECOMMENDED (Clean Data)**

#### 7. barang → KATEGORI
**Purpose:** Ensure products reference valid categories

```sql
ALTER TABLE barang
ADD CONSTRAINT fk_barang_kategori
FOREIGN KEY (ID_kategori) REFERENCES KATEGORI(ID_kategori)
ON DELETE RESTRICT
ON UPDATE CASCADE;
```

**Why RESTRICT:** Cannot delete categories that have products.
**Note:** kategori text column stays for historical record (good denormalization).

---

#### 8. transaksi_stok → barang
**Purpose:** Ensure stock transactions reference valid products

```sql
ALTER TABLE transaksi_stok
ADD CONSTRAINT fk_transaksi_stok_barang
FOREIGN KEY (no_kod) REFERENCES barang(no_kod)
ON DELETE RESTRICT
ON UPDATE CASCADE;
```

**Why RESTRICT:** Cannot delete products that have transaction history.

---

## 📋 Complete Migration SQL Script

Run this in HeidiSQL Query tab:

```sql
-- ============================================
-- FOREIGN KEY CONSTRAINTS MIGRATION
-- Date: 2025-12-30
-- ============================================

-- Step 1: Add barang → KATEGORI
ALTER TABLE barang
ADD CONSTRAINT fk_barang_kategori
FOREIGN KEY (ID_kategori) REFERENCES KATEGORI(ID_kategori)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 2: Add staf → jabatan
ALTER TABLE staf
ADD CONSTRAINT fk_staf_jabatan
FOREIGN KEY (ID_jabatan) REFERENCES jabatan(ID_jabatan)
ON DELETE SET NULL ON UPDATE CASCADE;

-- Step 3: Add permohonan → jabatan
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_jabatan
FOREIGN KEY (ID_jabatan) REFERENCES jabatan(ID_jabatan)
ON DELETE SET NULL ON UPDATE CASCADE;

-- Step 4: Add permohonan → staf (pemohon)
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_pemohon
FOREIGN KEY (ID_pemohon) REFERENCES staf(ID_staf)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 5: Add permohonan → staf (pelulus)
ALTER TABLE permohonan
ADD CONSTRAINT fk_permohonan_pelulus
FOREIGN KEY (ID_pelulus) REFERENCES staf(ID_staf)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 6: Add permohonan_barang → barang
ALTER TABLE permohonan_barang
ADD CONSTRAINT fk_permohonan_barang_barang
FOREIGN KEY (no_kod) REFERENCES barang(no_kod)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Step 7: Add permohonan_barang → permohonan
ALTER TABLE permohonan_barang
ADD CONSTRAINT fk_permohonan_barang_permohonan
FOREIGN KEY (ID_permohonan) REFERENCES permohonan(ID_permohonan)
ON DELETE CASCADE ON UPDATE CASCADE;

-- Step 8: Add transaksi_stok → barang
ALTER TABLE transaksi_stok
ADD CONSTRAINT fk_transaksi_stok_barang
FOREIGN KEY (no_kod) REFERENCES barang(no_kod)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================
-- VERIFICATION
-- ============================================
-- Check all constraints were added:
SELECT
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'storeroom_db'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;
```

---

## 🔄 Rollback Script

If you need to remove all FK constraints:

```sql
-- Remove all FK constraints (run if needed)
ALTER TABLE transaksi_stok DROP FOREIGN KEY fk_transaksi_stok_barang;
ALTER TABLE permohonan_barang DROP FOREIGN KEY fk_permohonan_barang_permohonan;
ALTER TABLE permohonan_barang DROP FOREIGN KEY fk_permohonan_barang_barang;
ALTER TABLE permohonan DROP FOREIGN KEY fk_permohonan_pelulus;
ALTER TABLE permohonan DROP FOREIGN KEY fk_permohonan_pemohon;
ALTER TABLE permohonan DROP FOREIGN KEY fk_permohonan_jabatan;
ALTER TABLE staf DROP FOREIGN KEY fk_staf_jabatan;
ALTER TABLE barang DROP FOREIGN KEY fk_barang_kategori;
```

---

## ✅ Summary & Recommendations

### What We're Adding:
- ✅ 8 Foreign Key constraints
- ✅ Data integrity protection
- ✅ Automatic cascade updates
- ✅ Prevents orphaned records

### What We're Keeping:
- ✅ Denormalized `kategori` text column (good for history)
- ✅ Denormalized `nama_pemohon`, `jawatan_pemohon` (audit trail)
- ✅ All existing PHP code (no changes needed)

### What to Consider Later:
- 📅 Add soft deletes (`is_active`, `deleted_at` columns)
- 📅 Drop unused `peranan` column (after testing)

---

**Status:** READY TO IMPLEMENT
**Risk Level:** LOW (data already follows these rules)
**Estimated Time:** 5 minutes

Would you like to proceed with adding these constraints?
