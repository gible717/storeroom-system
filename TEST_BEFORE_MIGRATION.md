# Pre-Migration Testing Checklist

**IMPORTANT:** Test these BEFORE running the database migration!

---

## ✅ Phase 1: Code Syntax Check

### Check for PHP syntax errors:
```bash
# Navigate to project directory
cd c:\xampp\htdocs\storeroom

# Test each modified file
php -l auth_check.php
php -l admin_auth_check.php
php -l login_process.php
php -l user_add.php
php -l user_add_process.php
php -l admin_users.php
php -l user_edit.php
php -l user_view.php
php -l kewps3_print.php
```

**Expected:** All files should return "No syntax errors detected"

---

## ✅ Phase 2: Login & Access Testing

### Test 1: Admin Login
1. **Start XAMPP** (Apache + MySQL)
2. **Open browser:** http://localhost/storeroom/login.php
3. **Login with Admin account**
4. **Expected:** Successful login, redirected to admin dashboard
5. **Check for errors:** No PHP warnings/errors on page

### Test 2: Admin Access to User Management
1. **Navigate to:** User Management (admin_users.php)
2. **Expected:** Page loads successfully
3. **Verify:** User list displays with role badges:
   - Admin users show: Blue badge "Admin"
   - Staff users show: Gray badge "Staf"
   - **NO "Super Admin" badges should appear**

---

## ✅ Phase 3: User Creation Testing

### Test 3: Create New Admin User
1. **Click:** "Tambah Pengguna" button
2. **Expected:** Add user form loads (user_add.php)
3. **Verify:** Role selection shows BOTH options:
   - ☐ Staf
   - ☐ Admin
4. **Fill in test data:**
   - ID Staf: TEST001
   - Nama: Test Admin User
   - Emel: testadmin@test.com
   - Jabatan: (any)
   - Peranan: **Admin**
5. **Submit form**
6. **Expected:** Success message, user created
7. **Verify:** New user appears in list with "Admin" badge

### Test 4: Create New Staff User
1. **Click:** "Tambah Pengguna" button
2. **Fill in test data:**
   - ID Staf: TEST002
   - Nama: Test Staff User
   - Emel: teststaff@test.com
   - Jabatan: (any)
   - Peranan: **Staf**
3. **Submit form**
4. **Expected:** Success message, user created
5. **Verify:** New user appears in list with "Staf" badge

---

## ✅ Phase 4: User Viewing & Editing

### Test 5: View User Details
1. **Click "View" icon** on any user
2. **Expected:** User details page loads (user_view.php)
3. **Verify:** Role shows correct badge (Admin or Staf)
4. **No errors displayed**

### Test 6: Edit User
1. **Click "Edit" icon** on TEST001 (Admin user)
2. **Expected:** Edit form loads (user_edit.php)
3. **Verify:** Role dropdown shows:
   - ☐ Staf
   - ☐ Admin (selected)
4. **Change role to Staf**
5. **Submit form**
6. **Expected:** Success message, role updated
7. **Verify:** User list shows updated badge

### Test 7: Delete Test Users
1. **Delete TEST001** - Expected: Success
2. **Delete TEST002** - Expected: Success

---

## ✅ Phase 5: Reports Access Testing

### Test 8: KEW.PS-3 Report Access
1. **Navigate to:** Reports > KEW.PS-3 Bahagian B
2. **Select any item** with transaction history
3. **Set date range**
4. **Click "Papar/Cetak"**
5. **Expected:** Report opens in new tab (kewps3_print.php)
6. **No access denied errors**

---

## ✅ Phase 6: Staff User Testing

### Test 9: Staff Login (if you have a staff account)
1. **Logout from Admin**
2. **Login with Staff account**
3. **Expected:** Redirected to staff dashboard
4. **Verify:** No access to User Management
5. **Try accessing:** http://localhost/storeroom/admin_users.php
6. **Expected:** "Akses ditolak" (Access denied)

---

## ✅ Phase 7: Session & Authentication

### Test 10: Session Variables
1. **While logged in as Admin**
2. **Create temporary test file:** `session_test.php`
```php
<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
```
3. **Access:** http://localhost/storeroom/session_test.php
4. **Expected output should include:**
   - `[ID_staf]` ✓
   - `[nama]` ✓
   - `[is_admin]` => 1 ✓
5. **Should NOT include:**
   - `[is_superadmin]` ❌ (Should not exist)
6. **Delete test file after checking**

---

## 📋 Testing Results Summary

| Test | Status | Notes |
|------|--------|-------|
| 1. Admin Login | ☐ Pass / ☐ Fail | |
| 2. User Management Access | ☐ Pass / ☐ Fail | |
| 3. Create Admin User | ☐ Pass / ☐ Fail | |
| 4. Create Staff User | ☐ Pass / ☐ Fail | |
| 5. View User Details | ☐ Pass / ☐ Fail | |
| 6. Edit User | ☐ Pass / ☐ Fail | |
| 7. Delete Users | ☐ Pass / ☐ Fail | |
| 8. KEW.PS-3 Report | ☐ Pass / ☐ Fail | |
| 9. Staff Access Control | ☐ Pass / ☐ Fail | |
| 10. Session Variables | ☐ Pass / ☐ Fail | |

---

## 🔴 If ALL Tests Pass:

### Step 1: Backup Database
```bash
# Open Command Prompt as Administrator
cd C:\xampp\mysql\bin

# Create backup
mysqldump -u root storeroom > C:\xampp\htdocs\storeroom\backup_before_superadmin_removal.sql
```

### Step 2: Run Migration
1. **Open:** http://localhost/phpmyadmin
2. **Select:** `storeroom` database
3. **Click:** SQL tab
4. **Copy & paste** contents from: `remove_superadmin_column.sql`
5. **Click:** "Go"
6. **Expected:**
   - UPDATE query: Shows how many SuperAdmin users converted
   - ALTER TABLE: "Table altered successfully"
   - SELECT queries: Show updated table structure

### Step 3: Restart XAMPP
1. **Stop Apache & MySQL**
2. **Start Apache & MySQL**
3. **This clears all sessions**

### Step 4: Final Verification
1. **Login again as Admin**
2. **Check User Management** - All features work
3. **Create test user** - Works correctly
4. **No PHP errors anywhere**

---

## 🔴 If ANY Test Fails:

**STOP - Do NOT run migration!**

1. **Note which test failed**
2. **Check browser console for errors** (F12)
3. **Check XAMPP error logs:**
   - Apache: `C:\xampp\apache\logs\error.log`
   - PHP: `C:\xampp\php\logs\php_error_log`
4. **Report the error** - We'll fix before migration

---

## 📝 Notes

- **Session clearing:** If you see old SuperAdmin data in session test, restart XAMPP
- **Browser cache:** Clear cache if pages look unchanged (Ctrl+Shift+R)
- **Test thoroughly:** Database migration cannot be undone easily
- **Keep backup:** Keep the SQL backup file safe

---

**Date Created:** 2025-11-28
**Purpose:** Pre-migration testing for SuperAdmin removal
**Related Files:** See SUPERADMIN_REMOVAL_SUMMARY.md
