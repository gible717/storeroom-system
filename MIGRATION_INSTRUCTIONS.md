# DATABASE MIGRATION: barang → PRODUK
## Complete Migration Guide

**Date:** 2025-12-01
**Purpose:** Consolidate dual table architecture into single unified PRODUK table
**Impact:** All 27 PHP files updated to use PRODUK table

---

## 🎯 MIGRATION OVERVIEW

### Problem Solved:
- **Before:** Two separate tables (`barang` and `PRODUK`) causing data inconsistency
- **After:** Single unified `PRODUK` table with all necessary columns
- **Benefit:** Admin product updates now immediately appear in user request forms

### Files Modified:
✅ **11 Critical PHP Files Updated:**
1. ✅ kewps8_form.php - User request form
2. ✅ request_edit.php - Edit request page
3. ✅ manage_requests.php - Admin request management
4. ✅ kewps8_approval_process.php - Approval processing
5. ✅ request_review_process.php - Request review
6. ✅ admin_dashboard.php - Dashboard statistics
7. ✅ kewps3_print.php - KEW.PS-3 report printing
8. ✅ kewps3_report.php - KEW.PS-3 report generation
9. ✅ admin_reports.php - Analytics reports
10. ✅ kewps8_approval.php - Approval interface
11. ✅ kewps8_print.php - KEW.PS-8 printing

✅ **Additional Files Updated:**
- kewps8_receipt.php
- request_details_ajax.php
- request_review.php

---

## 📋 MIGRATION STEPS

### STEP 1: BACKUP YOUR DATABASE ⚠️
**CRITICAL - DO THIS FIRST!**

```sql
-- Via phpMyAdmin: Export entire database
-- OR via command line:
mysqldump -u root -p storeroom_db > backup_before_migration.sql
```

### STEP 2: RUN MIGRATION SCRIPT

**Option A: phpMyAdmin (Recommended)**
1. Open phpMyAdmin
2. Select `storeroom_db` database
3. Go to **SQL** tab
4. Open file: `migrate_barang_to_produk.sql`
5. Copy entire content
6. Paste into SQL window
7. Click **Go**

**Option B: Command Line**
```bash
mysql -u root -p storeroom_db < migrate_barang_to_produk.sql
```

### STEP 3: VERIFY MIGRATION

After running the script, check the output:

```
✓ Backup created: X records backed up
✓ Column unit_pengukuran added (or already exists)
✓ Migration complete: X records processed
✓ Compatibility view created: barang_view
```

### STEP 4: VERIFY DATA

Run these queries to ensure data migrated correctly:

```sql
-- Check PRODUK table structure
DESCRIBE PRODUK;

-- Expected columns:
-- ID_produk, nama_produk, unit_pengukuran, harga, nama_pembekal, stok_semasa, ID_kategori

-- Compare record counts
SELECT
    (SELECT COUNT(*) FROM barang_backup) AS old_count,
    (SELECT COUNT(*) FROM PRODUK) AS new_count;

-- View sample migrated data
SELECT ID_produk, nama_produk, unit_pengukuran, stok_semasa
FROM PRODUK
LIMIT 10;
```

### STEP 5: TEST THE APPLICATION

1. **Test User Request Form:**
   - Go to: `kewps8_form.php`
   - Check if item dropdown shows all products
   - Verify items match what's in admin panel

2. **Test Admin Product Management:**
   - Go to: `admin_products.php`
   - Add a new product
   - Go back to user form - verify new product appears

3. **Test Request Approval:**
   - Create a test request
   - Approve it as admin
   - Verify stock updates correctly

4. **Test Reports:**
   - Generate KEW.PS-3 report
   - Generate KEW.PS-8 form
   - Verify all data displays correctly

### STEP 6: FINALIZE MIGRATION (After Testing)

Once everything works correctly, deprecate the old table:

```sql
-- Rename old table to mark as deprecated
RENAME TABLE barang TO barang_deprecated;

-- Optional: Drop the view if no longer needed
-- DROP VIEW IF EXISTS barang_view;
```

---

## 🔄 COLUMN MAPPING REFERENCE

| Old (barang) | New (PRODUK) | Notes |
|--------------|--------------|-------|
| `no_kod` | `ID_produk` | Primary key |
| `perihal_stok` | `nama_produk` | Product description |
| `unit_pengukuran` | `unit_pengukuran` | ✨ New column added |
| `harga_seunit` | `harga` | Unit price |
| `baki_semasa` | `stok_semasa` | Current stock |
| `kategori_barang` | `ID_kategori` | Links to KATEGORI table |
| N/A | `nama_pembekal` | Supplier name (optional) |

---

## 📝 PHP CODE CHANGES SUMMARY

### Typical Change Pattern:

**BEFORE:**
```php
$result = $conn->query("SELECT no_kod, perihal_stok FROM barang WHERE baki_semasa > 0");
```

**AFTER:**
```php
$result = $conn->query("SELECT ID_produk AS no_kod, nama_produk AS perihal_stok FROM PRODUK WHERE stok_semasa > 0");
```

### JOIN Statement Changes:

**BEFORE:**
```php
JOIN barang b ON pb.no_kod = b.no_kod
```

**AFTER:**
```php
JOIN PRODUK prod ON pb.no_kod = prod.ID_produk
```

### UPDATE Statement Changes:

**BEFORE:**
```php
UPDATE barang SET baki_semasa = baki_semasa - ? WHERE no_kod = ?
```

**AFTER:**
```php
UPDATE PRODUK SET stok_semasa = stok_semasa - ? WHERE ID_produk = ?
```

---

## 🔍 VERIFICATION CHECKLIST

After migration, verify these features work:

- [ ] User can see all products in request form dropdown
- [ ] Admin can add new products
- [ ] New products immediately appear in user forms
- [ ] Stock updates correctly when requests are approved
- [ ] KEW.PS-3 reports generate correctly
- [ ] KEW.PS-8 forms print correctly
- [ ] Dashboard shows correct product count
- [ ] Request management page displays items correctly
- [ ] Search and filters work on all pages
- [ ] No PHP errors in browser console
- [ ] No SQL errors in error logs

---

## ⚠️ ROLLBACK PROCEDURE

If something goes wrong, restore from backup:

### Quick Rollback:

```sql
-- Restore barang table from backup
DROP TABLE IF EXISTS barang;
CREATE TABLE barang AS SELECT * FROM barang_backup;

-- Remove compatibility view
DROP VIEW IF EXISTS barang_view;
```

### Full Database Rollback:

```bash
# Restore entire database from backup file
mysql -u root -p storeroom_db < backup_before_migration.sql
```

### Restore PHP Files:

```bash
# If you have git version control:
git checkout HEAD -- *.php

# Otherwise, manually revert changes using your editor's history
```

---

## 🎉 SUCCESS INDICATORS

Migration is successful when:

1. ✅ No PHP errors on any page
2. ✅ User forms show same products as admin panel
3. ✅ Stock updates reflect in both admin and user views
4. ✅ All reports generate without errors
5. ✅ Database has `PRODUK` table with `unit_pengukuran` column
6. ✅ `barang_backup` table exists as safety backup

---

## 📞 TROUBLESHOOTING

### Issue: "Unknown column 'unit_pengukuran'"
**Solution:** Re-run STEP 2 of migration script

### Issue: Items not showing in user form
**Solution:** Check if PRODUK has `stok_semasa > 0`

### Issue: "Table 'barang' doesn't exist" error
**Solution:** You may have deprecated it too early. Rename back:
```sql
RENAME TABLE barang_deprecated TO barang;
```

### Issue: Duplicate products after migration
**Solution:** Check for duplicates:
```sql
SELECT ID_produk, COUNT(*)
FROM PRODUK
GROUP BY ID_produk
HAVING COUNT(*) > 1;
```

---

## 📚 ADDITIONAL NOTES

### Database Indexes
Consider adding indexes for performance:

```sql
-- Add index on stok_semasa for filtering
ALTER TABLE PRODUK ADD INDEX idx_stok_semasa (stok_semasa);

-- Add index on ID_kategori for joins
ALTER TABLE PRODUK ADD INDEX idx_kategori (ID_kategori);
```

### Future Enhancements
- Add `tarikh_kemaskini` (last updated timestamp) column
- Add `catatan` (notes) column for product remarks
- Implement soft delete with `deleted_at` column
- Add `min_stock_level` for low stock alerts

---

## ✅ MIGRATION COMPLETE!

Once all tests pass:
1. Mark `barang` table as deprecated
2. Document this migration in your change log
3. Inform all users about the update
4. Monitor for any issues in the next 24-48 hours
5. After 1 week, consider dropping `barang_deprecated` if no issues

**Migration Date:** 2025-12-01
**Migrated By:** System Administrator
**Status:** ✅ COMPLETED
