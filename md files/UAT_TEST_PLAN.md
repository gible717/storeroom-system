# UAT Test Plan
**Sistem Pengurusan Bilik Stor dan Inventori - MPK**

**Document Version:** 1.0
**Date Created:** 7 January 2026
**UAT Phase:** User Acceptance Testing
**Status:** Ready for Execution

---

## 📋 Table of Contents

1. [Introduction](#1-introduction)
2. [Test Objectives](#2-test-objectives)
3. [Test Scope](#3-test-scope)
4. [Test Participants](#4-test-participants)
5. [Test Environment](#5-test-environment)
6. [Test Scenarios - Staff Workflows](#6-test-scenarios---staff-workflows)
7. [Test Scenarios - Admin Workflows](#7-test-scenarios---admin-workflows)
8. [Test Scenarios - Security & Access Control](#8-test-scenarios---security--access-control)
9. [Test Scenarios - Reporting](#9-test-scenarios---reporting)
10. [Test Scenarios - Integration](#10-test-scenarios---integration)
11. [Defect Severity Definitions](#11-defect-severity-definitions)
12. [UAT Acceptance Criteria](#12-uat-acceptance-criteria)

---

## 1. Introduction

### 1.1 Purpose
This document outlines the User Acceptance Testing (UAT) plan for the Sistem Pengurusan Bilik Stor dan Inventori. UAT is conducted to ensure the system meets business requirements and is ready for production deployment.

### 1.2 System Overview
- **System Name:** Sistem Pengurusan Bilik Stor dan Inventori
- **Organization:** Majlis Perbandaran Kangar, Perlis
- **Primary Users:** Staff (requesters) and Administrators (approvers)
- **Core Function:** Inventory request management with KEW.PS-8 form compliance

---

## 2. Test Objectives

1. ✅ Validate that all user roles function correctly (Staff & Admin)
2. ✅ Verify end-to-end business workflows (request → approval → stock update)
3. ✅ Ensure data accuracy and integrity
4. ✅ Confirm security controls work properly
5. ✅ Validate usability and user experience
6. ✅ Test system integrations (Telegram notifications)
7. ✅ Verify audit trail completeness

---

## 3. Test Scope

### 3.1 In Scope
- ✅ Staff request submission workflow
- ✅ Admin approval/rejection workflow
- ✅ Inventory management (CRUD operations)
- ✅ User management
- ✅ Department management
- ✅ Reporting and analytics
- ✅ Stock transaction logging
- ✅ Telegram notifications
- ✅ Access control and security
- ✅ KEW.PS-8 form generation

### 3.2 Out of Scope
- ❌ Server infrastructure testing
- ❌ Performance/load testing (covered separately)
- ❌ Database backup/recovery procedures
- ❌ Network security testing

---

## 4. Test Participants

### 4.1 Required Participants

| Role | Number | Responsibilities |
|------|--------|------------------|
| **Staff Testers** | 2-3 | Test staff workflows, request submission |
| **Admin Testers** | 2 | Test admin workflows, approval process |
| **UAT Coordinator** | 1 | Facilitate testing, log issues, track progress |
| **IT Support** | 1 | Resolve technical issues, provide assistance |

### 4.2 Participant Requirements
- Must be actual end users (not developers)
- Represent different departments
- Available for 3-5 days of testing
- Basic computer literacy

---

## 5. Test Environment

### 5.1 Test Server Details
- **URL:** `http://localhost/storeroom` (or production URL)
- **Database:** `storeroom_db` (test copy)
- **Browser:** Chrome, Edge, Firefox (latest versions)
- **Test Accounts:** See UAT_TEST_DATA.sql

### 5.2 Test Data
- Test user accounts (staff & admin)
- Sample departments
- Sample products with varying stock levels
- Pre-existing requests for testing approval workflow

---

## 6. Test Scenarios - Staff Workflows

### TC-S-001: User Login (Staff)
**Priority:** Critical
**Module:** Authentication

**Preconditions:**
- Test staff account exists (see UAT_TEST_DATA.sql)
- User not logged in

**Test Steps:**
1. Navigate to login page
2. Enter staff credentials:
   - ID_staf: `TEST001`
   - Password: `Test@123`
3. Click "Log Masuk"

**Expected Results:**
- ✅ Redirected to staff dashboard
- ✅ Welcome message shows staff name
- ✅ Sidebar shows staff-appropriate menu items
- ✅ Session created successfully

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-002: Submit Basic Stock Request
**Priority:** Critical
**Module:** Request Management

**Preconditions:**
- Logged in as staff (TEST001)
- Products exist in catalog with available stock

**Test Steps:**
1. Navigate to "Hantar Permohonan" or kewps8_form.php
2. Browse available products
3. Click "Tambah ke Permohonan" for 2 different products
4. Enter quantities (e.g., 5 and 3)
5. Optional: Enter catatan (notes): "Untuk projek ujian"
6. Optional: Verify jawatan auto-filled from profile
7. Click "Hantar Permohonan"
8. Verify confirmation message and request ID displayed

**Expected Results:**
- ✅ Products added to cart successfully
- ✅ Cart displays correct items and quantities
- ✅ Jawatan field auto-filled (if profile has jawatan)
- ✅ Request created with status "Baru"
- ✅ Request ID displayed (e.g., #42)
- ✅ Confirmation message shown
- ✅ Cart cleared after submission
- ✅ Telegram notification sent to admin (check Telegram group)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-003: Smart Jawatan Autocomplete
**Priority:** High
**Module:** Request Management

**Preconditions:**
- Logged in as staff with jawatan in profile
- Previously submitted request(s) exist

**Test Steps:**
1. Navigate to request form (kewps8_form.php)
2. Observe jawatan field on page load
3. Click on jawatan field
4. Check for autocomplete suggestions dropdown

**Expected Results:**
- ✅ Jawatan field auto-filled with profile jawatan (gray text)
- ✅ Clicking field shows suggestions:
   - Profile jawatan labeled "(Profil Anda)"
   - Recent jawatan from previous requests labeled "(Permohonan Lepas)"
- ✅ User can select from suggestions or type custom value
- ✅ Text color changes from gray to black when user types

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-004: View Own Request History
**Priority:** High
**Module:** Request Management

**Preconditions:**
- Logged in as staff
- Staff has submitted at least 2 requests with different statuses

**Test Steps:**
1. Navigate to "Sejarah Permohonan" or request_list.php
2. Verify list of own requests displayed
3. Click "Lihat" on a request to view details modal

**Expected Results:**
- ✅ Only own requests displayed (ID_pemohon matches logged-in user)
- ✅ Requests sorted by date (newest first)
- ✅ Status badges display correctly:
   - Yellow "Baru" for pending
   - Green "Diluluskan" for approved
   - Red "Ditolak" for rejected
- ✅ Modal shows:
   - Request details (items, quantities)
   - Staff's own catatan (labeled "Catatan Pemohon (Anda)")
   - Admin's catatan_admin (labeled "Catatan Pelulus") if exists
- ✅ Bidirectional remarks visible

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-005: Edit Pending Request
**Priority:** High
**Module:** Request Management

**Preconditions:**
- Logged in as staff
- Staff has request with status "Baru"

**Test Steps:**
1. Go to "Sejarah Permohonan"
2. Find request with "Baru" status
3. Click "Edit" button
4. Modify cart:
   - Remove 1 item
   - Change quantity of another item
   - Add a new item
5. Update catatan
6. Save changes

**Expected Results:**
- ✅ Edit button only visible for "Baru" status requests
- ✅ Edit page loads with existing cart items
- ✅ Can add/remove/modify items
- ✅ Changes saved successfully
- ✅ Updated request reflects new items and quantities
- ✅ Cannot edit "Diluluskan" or "Ditolak" requests

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-006: Delete Pending Request
**Priority:** Medium
**Module:** Request Management

**Preconditions:**
- Logged in as staff
- Staff has request with status "Baru"

**Test Steps:**
1. Go to "Sejarah Permohonan"
2. Find request with "Baru" status
3. Click "Padam" button
4. Confirm deletion in confirmation dialog

**Expected Results:**
- ✅ Delete button only visible for "Baru" status
- ✅ Confirmation dialog appears
- ✅ Request deleted successfully
- ✅ Request removed from list
- ✅ Cannot delete approved/rejected requests

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-007: View Staff Dashboard
**Priority:** Medium
**Module:** Dashboard

**Preconditions:**
- Logged in as staff

**Test Steps:**
1. Navigate to staff dashboard
2. Observe displayed statistics and widgets

**Expected Results:**
- ✅ Dashboard shows summary cards:
   - Total requests submitted
   - Pending requests count
   - Approved requests count
- ✅ Recent requests list displayed (if any)
- ✅ Quick action buttons visible
- ✅ Responsive design works on different screen sizes

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-008: Update Profile Information
**Priority:** Medium
**Module:** User Management

**Preconditions:**
- Logged in as staff

**Test Steps:**
1. Navigate to "Profil" page
2. Update fields:
   - Nama: Change to "Test User Updated"
   - Jawatan: Change to "Pegawai IT"
   - Email: Update if editable
3. Upload profile picture (optional)
4. Save changes

**Expected Results:**
- ✅ Profile page loads with current data
- ✅ Fields are editable
- ✅ Changes saved successfully
- ✅ Success message displayed
- ✅ Updated name shown in navbar/header
- ✅ Profile picture updated (if uploaded)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-009: Change Password
**Priority:** High
**Module:** Security

**Preconditions:**
- Logged in as staff

**Test Steps:**
1. Navigate to "Tukar Kata Laluan" page
2. Enter current password
3. Enter new password (min 8 characters)
4. Confirm new password
5. Submit

**Expected Results:**
- ✅ Form validates:
   - Current password correct
   - New password meets requirements (min 8 chars)
   - Confirmation matches new password
   - New password different from current
- ✅ Password updated successfully
- ✅ Session cleared, redirected to login
- ✅ Can login with new password
- ✅ Cannot login with old password

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-S-010: Logout
**Priority:** High
**Module:** Authentication

**Preconditions:**
- Logged in as staff

**Test Steps:**
1. Click "Log Keluar" button
2. Verify redirect to login page
3. Try accessing protected page (e.g., dashboard) directly

**Expected Results:**
- ✅ Session destroyed
- ✅ Redirected to login page
- ✅ Success message: "Anda telah berjaya log keluar"
- ✅ Cannot access protected pages without login
- ✅ Redirect to login if attempting to access protected pages

**Pass/Fail:** ___________
**Notes:** ___________

---

## 7. Test Scenarios - Admin Workflows

### TC-A-001: Admin Login
**Priority:** Critical
**Module:** Authentication

**Preconditions:**
- Test admin account exists
- User not logged in

**Test Steps:**
1. Navigate to login page
2. Enter admin credentials:
   - ID_staf: `ADMIN001`
   - Password: `Admin@123`
3. Click "Log Masuk"

**Expected Results:**
- ✅ Redirected to admin dashboard
- ✅ Admin-specific sidebar menu visible
- ✅ Has access to admin-only features
- ✅ Session created with is_admin = 1

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-002: View Admin Dashboard
**Priority:** High
**Module:** Dashboard

**Preconditions:**
- Logged in as admin

**Test Steps:**
1. View admin dashboard
2. Verify statistics and action cards

**Expected Results:**
- ✅ Dashboard displays:
   - Total products (Jumlah Produk) - clickable to products page
   - Pending requests (Permohonan Tertunda) - clickable modal
   - Low stock warnings (Pantau Stok) - clickable modal
- ✅ Stat card numbers in white color (visible on gradients)
- ✅ Glow animation on pending/stock warnings (if applicable)
- ✅ Recent requests list with "Baru" badges glowing (first 5 minutes)
- ✅ Mini stat cards: Total users, Monthly requests, Approval rate, Departments
- ✅ All click actions work (modals open, pages navigate)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-003: Review Pending Request
**Priority:** Critical
**Module:** Request Management

**Preconditions:**
- Logged in as admin
- At least 1 request with status "Baru" exists

**Test Steps:**
1. Navigate to "Urus Permohonan" (manage_requests.php)
2. Verify pending requests listed
3. Click "Semak" (Review) on a request
4. Observe review page details

**Expected Results:**
- ✅ Pending requests displayed with "Baru" status
- ✅ Review page shows:
   - Requester information (nama, jawatan with COALESCE logic, jabatan)
   - Request date and time (smart time display)
   - Items requested with quantities
   - Current stock levels for each item
   - Staff's catatan (remarks)
- ✅ Form allows setting kuantiti_lulus for each item
- ✅ Admin remarks field (catatan_pelulus) available
- ✅ "Lulus" and "Tolak" buttons visible

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-004: Approve Request with Full Quantity
**Priority:** Critical
**Module:** Request Management

**Preconditions:**
- Logged in as admin
- Request exists with status "Baru"
- Sufficient stock available for all items

**Test Steps:**
1. Navigate to review page for a request
2. Verify stock availability for each item
3. Set kuantiti_lulus = kuantiti_mohon for all items
4. Enter admin remarks: "Diluluskan. Stok mencukupi."
5. Click "Lulus" (Approve)
6. Verify changes in system

**Expected Results:**
- ✅ Approval succeeds
- ✅ Request status updated to "Diluluskan"
- ✅ Stock levels deducted correctly:
   - barang.baki_semasa reduced by kuantiti_lulus
- ✅ permohonan_barang.kuantiti_lulus updated
- ✅ Transaction log created in transaksi_stok:
   - jenis_transaksi = "Keluar"
   - kuantiti = kuantiti_lulus
   - baki_selepas_transaksi recorded
   - ID_rujukan_permohonan = request ID
- ✅ permohonan table updated:
   - ID_pelulus = admin ID
   - nama_pelulus = admin name (denormalized)
   - jawatan_pelulus = admin position (denormalized)
   - tarikh_lulus = current datetime
   - catatan_admin = admin remarks saved
- ✅ Success message displayed
- ✅ Staff can view admin remarks in request history

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-005: Approve Request with Partial Quantity
**Priority:** High
**Module:** Request Management

**Preconditions:**
- Logged in as admin
- Request exists with multiple items
- One item has limited stock

**Test Steps:**
1. Review a request
2. Set kuantiti_lulus < kuantiti_mohon for one item (e.g., requested 10, approve 5)
3. Set kuantiti_lulus = 0 for another item (reject item)
4. Enter admin remarks: "Sebahagian item stok terhad"
5. Approve

**Expected Results:**
- ✅ Partial approval succeeds
- ✅ Stock deducted only for approved quantities
- ✅ Items with kuantiti_lulus = 0 not deducted
- ✅ Status = "Diluluskan" (even with partial approval)
- ✅ Transaction log created only for approved items
- ✅ Admin remarks saved and visible to staff

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-006: Reject Insufficient Stock
**Priority:** Critical
**Module:** Request Management

**Preconditions:**
- Request exists where requested quantity > available stock

**Test Steps:**
1. Review request
2. Attempt to approve with kuantiti_lulus > baki_semasa
3. Observe error handling

**Expected Results:**
- ✅ System prevents approval
- ✅ Error message: "Stok tidak mencukupi untuk [product name]"
- ✅ Transaction rolled back (no partial changes)
- ✅ Request status remains "Baru"
- ✅ No stock deduction occurs

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-007: Reject Request
**Priority:** High
**Module:** Request Management

**Preconditions:**
- Logged in as admin
- Request with status "Baru" exists

**Test Steps:**
1. Review request
2. Enter admin remarks: "Ditolak. Item tidak diperlukan"
3. Click "Tolak" (Reject)

**Expected Results:**
- ✅ Request status updated to "Ditolak"
- ✅ No stock deduction
- ✅ No transaction log created
- ✅ permohonan table updated:
   - ID_pelulus = admin ID
   - nama_pelulus, jawatan_pelulus saved
   - tarikh_lulus = current datetime
   - catatan_admin = rejection reason saved
- ✅ Success message displayed
- ✅ Staff can view rejection reason in request history

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-008: Prevent Self-Approval (Critical Security Test)
**Priority:** Critical
**Module:** Security

**Preconditions:**
- Admin creates own request as staff

**Test Steps:**
1. Login as admin (ADMIN001)
2. Submit a request (admin as requester)
3. Logout and login again as same admin
4. Try to approve own request

**Expected Results:**
- ✅ System blocks approval
- ✅ Error message: "Anda tidak boleh meluluskan permohonan anda sendiri. Kelulusan mesti dibuat oleh admin lain."
- ✅ Request status remains "Baru"
- ✅ No changes to stock or database
- ✅ Same validation for rejection

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-009: Add New Product
**Priority:** High
**Module:** Inventory Management

**Preconditions:**
- Logged in as admin

**Test Steps:**
1. Navigate to "Urus Produk" (admin_products.php)
2. Click "Tambah Produk Baru"
3. Fill form:
   - no_kod: `TEST001`
   - perihal_stok: `Produk Ujian UAT`
   - ID_kategori: Select a category
   - unit_pengukuran: `unit`
   - harga_seunit: `15.50`
   - nama_pembekal: `Pembekal Test`
   - baki_semasa: `100`
4. Submit

**Expected Results:**
- ✅ Product created successfully
- ✅ Product appears in product list
- ✅ If baki_semasa > 0, transaction log created:
   - jenis_transaksi = "Masuk"
   - kuantiti = 100
   - baki_selepas_transaksi = 100
   - ID_rujukan_permohonan = NULL (manual entry)
- ✅ Category name denormalized in barang.kategori
- ✅ Success message displayed

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-010: Edit Product
**Priority:** High
**Module:** Inventory Management

**Preconditions:**
- Admin logged in
- Product exists

**Test Steps:**
1. Navigate to product list
2. Click "Edit" on a product
3. Modify fields:
   - perihal_stok: Update description
   - harga_seunit: Change price
4. Save changes

**Expected Results:**
- ✅ Changes saved successfully
- ✅ Product list reflects updates
- ✅ No impact on existing requests using this product
- ✅ Stock balance unchanged (use manual adjustment for stock)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-011: Delete Unused Product
**Priority:** Medium
**Module:** Inventory Management

**Preconditions:**
- Product exists
- Product has NO requests or transactions

**Test Steps:**
1. Navigate to product list
2. Click "Padam" on unused product
3. Confirm deletion

**Expected Results:**
- ✅ Product deleted successfully
- ✅ Removed from product list
- ✅ Success message displayed

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-012: Prevent Deletion of Used Product
**Priority:** High
**Module:** Data Integrity

**Preconditions:**
- Product has been used in requests or transactions

**Test Steps:**
1. Try to delete product that has history
2. Observe error handling

**Expected Results:**
- ✅ Deletion blocked by FK constraint
- ✅ Error message: "Tidak boleh padam. Produk ini mempunyai sejarah transaksi"
- ✅ Product remains in database
- ✅ Historical data preserved

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-013: Manual Stock Adjustment - Stock IN
**Priority:** High
**Module:** Inventory Management

**Preconditions:**
- Logged in as admin
- Product exists

**Test Steps:**
1. Navigate to "Kemaskini Stok Manual" (admin_stock_manual.php)
2. Select product
3. Select "Stok Masuk"
4. Enter quantity: `50`
5. Enter catatan: "Tambah stok dari pembekal"
6. Submit

**Expected Results:**
- ✅ Stock increased: baki_semasa += 50
- ✅ Transaction log created:
   - jenis_transaksi = "Masuk"
   - kuantiti = 50
   - baki_selepas_transaksi = new balance
   - ID_rujukan_permohonan = NULL
   - catatan saved
- ✅ Success message displayed

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-014: Manual Stock Adjustment - Stock OUT
**Priority:** High
**Module:** Inventory Management

**Preconditions:**
- Product has sufficient stock

**Test Steps:**
1. Navigate to manual stock adjustment
2. Select product
3. Select "Stok Keluar"
4. Enter quantity: `20`
5. Enter catatan: "Rosak/hilang"
6. Submit

**Expected Results:**
- ✅ Stock decreased: baki_semasa -= 20
- ✅ Transaction log created:
   - jenis_transaksi = "Keluar"
   - kuantiti = 20
   - baki_selepas_transaksi = new balance
   - catatan saved
- ✅ Cannot reduce stock below 0

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-015: Low Stock Alert
**Priority:** Medium
**Module:** Dashboard

**Preconditions:**
- At least 1 product with baki_semasa ≤ 10

**Test Steps:**
1. View admin dashboard
2. Check "Pantau Stok" card
3. Click to view low stock modal

**Expected Results:**
- ✅ Pantau Stok card shows count of low stock items
- ✅ Red glow animation if low stock detected (first 15 minutes)
- ✅ Modal displays:
   - List of all products with stock ≤ 10
   - Stock level for each (0 = red "Stok Habis", 1-10 = yellow "Stok Rendah")
   - Quick link to update stock
- ✅ If stock = 0, shows "Stok Habis" badge
- ✅ If stock 1-10, shows "Stok Rendah" badge

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-016: Add New User
**Priority:** High
**Module:** User Management

**Preconditions:**
- Logged in as admin

**Test Steps:**
1. Navigate to "Urus Pengguna" (admin_users.php)
2. Click "Tambah Pengguna"
3. Fill form:
   - ID_staf: `TEST002`
   - nama: `Pengguna Test`
   - emel: `test@mpk.gov.my`
   - kata_laluan: Auto-generated or set
   - jawatan: `Pegawai Tadbir`
   - ID_jabatan: Select department
   - is_admin: 0 (Staff)
4. Submit

**Expected Results:**
- ✅ User created successfully
- ✅ Password hashed with bcrypt
- ✅ is_first_login = 1 (forces password change)
- ✅ User can login with provided credentials
- ✅ User must change password on first login

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-017: Add New Department
**Priority:** Medium
**Module:** Department Management

**Preconditions:**
- Logged in as admin

**Test Steps:**
1. Navigate to "Urus Jabatan" (admin_departments.php)
2. Click "Tambah Jabatan"
3. Enter nama_jabatan: `Jabatan Test UAT`
4. Submit

**Expected Results:**
- ✅ Department created
- ✅ Appears in department dropdown in user management
- ✅ Appears in department dropdown in request forms
- ✅ Unique constraint enforced (cannot add duplicate)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-A-018: Delete Department with Staff
**Priority:** Medium
**Module:** Department Management

**Preconditions:**
- Department exists with assigned staff

**Test Steps:**
1. Try to delete department that has staff
2. Observe warning and proceed

**Expected Results:**
- ✅ Warning message: "XX staff akan menjadi tidak berjabatan"
- ✅ Deletion succeeds with confirmation
- ✅ FK constraint: ON DELETE SET NULL
- ✅ Staff members' ID_jabatan set to NULL
- ✅ Historical requests preserve original jabatan

**Pass/Fail:** ___________
**Notes:** ___________

---

## 8. Test Scenarios - Security & Access Control

### TC-SEC-001: Staff Cannot Access Admin Pages
**Priority:** Critical
**Module:** Security

**Preconditions:**
- Logged in as staff

**Test Steps:**
1. Try to access admin pages directly:
   - `admin_dashboard.php`
   - `admin_products.php`
   - `manage_requests.php`
2. Observe access control

**Expected Results:**
- ✅ Access denied
- ✅ Error message or redirect to staff dashboard
- ✅ admin_auth_check.php blocks access
- ✅ Session check: is_admin must = 1

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-002: Unauthenticated User Cannot Access System
**Priority:** Critical
**Module:** Security

**Preconditions:**
- User not logged in

**Test Steps:**
1. Try to access protected pages directly:
   - `staff_dashboard.php`
   - `admin_dashboard.php`
   - `kewps8_form.php`
2. Observe redirect

**Expected Results:**
- ✅ Redirected to login page
- ✅ auth_check.php blocks access
- ✅ Cannot access any protected resource
- ✅ Session must exist and be valid

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-003: SQL Injection Prevention
**Priority:** Critical
**Module:** Security

**Preconditions:**
- Any login or input form

**Test Steps:**
1. Try SQL injection in login:
   - Username: `admin' OR '1'='1`
   - Password: `anything`
2. Try SQL injection in search/filter fields
3. Observe system response

**Expected Results:**
- ✅ SQL injection blocked
- ✅ Prepared statements used throughout
- ✅ No SQL errors displayed
- ✅ Login fails with invalid credentials message

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-004: XSS Prevention
**Priority:** High
**Module:** Security

**Test Steps:**
1. Enter XSS payload in catatan field:
   - `<script>alert('XSS')</script>`
2. Submit request
3. View request in history

**Expected Results:**
- ✅ Script tag escaped/sanitized
- ✅ No JavaScript execution
- ✅ htmlspecialchars() applied
- ✅ Text displayed as plain text, not executed

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-005: Password Hashing Verification
**Priority:** Critical
**Module:** Security

**Test Steps:**
1. Create new user or check existing user in database
2. Query staf table directly
3. Observe kata_laluan column

**Expected Results:**
- ✅ Password stored as bcrypt hash
- ✅ Hash format: `$2y$10$...` (60 chars)
- ✅ Plaintext password never stored
- ✅ password_hash() and password_verify() used

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-006: Session Timeout
**Priority:** Medium
**Module:** Security

**Test Steps:**
1. Login to system
2. Leave idle for extended period (e.g., 30+ minutes)
3. Try to perform action

**Expected Results:**
- ✅ Session expires after inactivity
- ✅ Redirected to login page
- ✅ Must login again to continue

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-007: Staff Cannot View Other Staff's Requests
**Priority:** High
**Module:** Access Control

**Preconditions:**
- Multiple staff users with requests

**Test Steps:**
1. Login as Staff A
2. View request history
3. Note only own requests visible
4. Try to access another staff's request by ID manipulation

**Expected Results:**
- ✅ Only own requests displayed
- ✅ WHERE ID_pemohon = session ID_staf enforced
- ✅ Cannot access other staff's requests via URL manipulation
- ✅ Access denied if trying to view/edit others' requests

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-SEC-008: Concurrent Stock Update (Race Condition)
**Priority:** High
**Module:** Data Integrity

**Test Steps:**
1. Have 2 admins attempt to approve same request simultaneously
2. Both admins reviewing same request with limited stock
3. Both click "Approve" at same time

**Expected Results:**
- ✅ Row-level locking (SELECT...FOR UPDATE) prevents race condition
- ✅ First approval succeeds
- ✅ Second approval fails with stock insufficient error
- ✅ No negative stock balance
- ✅ Data integrity maintained

**Pass/Fail:** ___________
**Notes:** ___________

---

## 9. Test Scenarios - Reporting

### TC-RPT-001: Inventory Report
**Priority:** High
**Module:** Reporting

**Preconditions:**
- Admin logged in
- Products exist in database

**Test Steps:**
1. Navigate to "Laporan Inventori" (report_inventory.php)
2. Select filters (optional):
   - Category
   - Stock level (all/low/sufficient)
3. Generate report

**Expected Results:**
- ✅ Report displays:
   - Total products
   - Total inventory value
   - Low stock items count
   - Breakdown by category
- ✅ Charts display correctly (Chart.js)
- ✅ Data accurate from barang table
- ✅ Export to Excel works (if implemented)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-RPT-002: Request Report
**Priority:** High
**Module:** Reporting

**Preconditions:**
- Requests exist in database

**Test Steps:**
1. Navigate to "Laporan Permohonan" (report_requests.php)
2. Select date range filter
3. Select status filter (optional)
4. Generate report

**Expected Results:**
- ✅ Report displays:
   - Total requests in period
   - Breakdown by status (Baru/Diluluskan/Ditolak)
   - Top requesters
   - Department analytics
- ✅ Charts accurate
- ✅ Filtering works correctly

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-RPT-003: KEW.PS-3 Stock Card Report
**Priority:** High
**Module:** Reporting

**Preconditions:**
- Stock transactions exist

**Test Steps:**
1. Navigate to KEW.PS-3 report (kewps3.php)
2. Select date range
3. Select product (optional)
4. Generate report

**Expected Results:**
- ✅ Report shows for each product:
   - Opening balance
   - Stock IN (Masuk) transactions
   - Stock OUT (Keluar) transactions
   - Closing balance
- ✅ Formula: Closing = Opening + IN - OUT
- ✅ Data from transaksi_stok table
- ✅ Print format matches government KEW.PS-3 form

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-RPT-004: Department Analytics
**Priority:** Medium
**Module:** Reporting

**Test Steps:**
1. Navigate to department analytics report
2. Select date range
3. Generate report

**Expected Results:**
- ✅ Top 10 departments by request count
- ✅ Status breakdown per department
- ✅ Monthly trend chart (Chart.js line chart)
- ✅ Accurate aggregation from permohonan table

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-RPT-005: KEW.PS-8 Form Print
**Priority:** High
**Module:** Reporting

**Preconditions:**
- Request exists (any status)

**Test Steps:**
1. View request details
2. Click "Print" or "Cetak KEW.PS-8"
3. Observe print preview

**Expected Results:**
- ✅ KEW.PS-8 form displays correctly:
   - Requester info (nama, jawatan with COALESCE, jabatan)
   - Request date
   - Items table with quantities
   - If approved: kuantiti_lulus shown
   - Signature sections
- ✅ Print layout matches official form
- ✅ No navigation elements in print view
- ✅ Browser print dialog works

**Pass/Fail:** ___________
**Notes:** ___________

---

## 10. Test Scenarios - Integration

### TC-INT-001: Telegram Notification - New Request
**Priority:** High
**Module:** Integration

**Preconditions:**
- Telegram bot configured
- Admin group chat ID set
- Network connectivity

**Test Steps:**
1. Staff submits new request
2. Check configured Telegram group

**Expected Results:**
- ✅ Notification sent within seconds
- ✅ Message format:
   ```
   🔔 PERMOHONAN BARU

   📋 ID Permohonan: #42
   👤 Pemohon: Muhammad Hazeeq
   💼 Jawatan: Pegawai IT (only if not empty)
   📦 Jumlah Item: 3
   📅 Tarikh: 06/01/2026 15:30
   📝 Catatan: [text] (only if not empty)

   ⚠️ Sila log masuk ke sistem untuk semakan
   ```
- ✅ Empty fields (jawatan, catatan) auto-hidden (smart notifications)
- ✅ If notification fails, request still succeeds (non-blocking)

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-INT-002: Smart Notification Field Hiding
**Priority:** Medium
**Module:** Integration

**Test Steps:**
1. Submit request WITHOUT entering jawatan or catatan
2. Check Telegram notification

**Expected Results:**
- ✅ Notification sent
- ✅ Jawatan line NOT shown (if empty)
- ✅ Catatan line NOT shown (if empty)
- ✅ Other fields still present
- ✅ Clean, concise notification

**Pass/Fail:** ___________
**Notes:** ___________

---

### TC-INT-003: Notification Failure Handling
**Priority:** Medium
**Module:** Integration

**Test Steps:**
1. Temporarily disable network/Telegram API
2. Submit request
3. Observe system behavior

**Expected Results:**
- ✅ Request submission succeeds
- ✅ Notification failure does not block request
- ✅ Error logged (if logging implemented)
- ✅ User still receives success message
- ✅ Request saved to database

**Pass/Fail:** ___________
**Notes:** ___________

---

## 11. Defect Severity Definitions

### Critical
- System crash or data loss
- Security vulnerabilities
- Core functionality completely broken
- **Action:** Fix immediately

### High
- Major feature not working as expected
- Significant impact on user workflow
- Workaround difficult or impossible
- **Action:** Fix before deployment

### Medium
- Feature partially working
- Minor impact on workflow
- Workaround available
- **Action:** Fix in next update

### Low
- Cosmetic issues (UI/UX)
- Minor inconveniences
- Easy workaround
- **Action:** Fix when possible

---

## 12. UAT Acceptance Criteria

### The system is ACCEPTED if:

1. ✅ **100% of Critical test cases PASS**
2. ✅ **≥95% of High priority test cases PASS**
3. ✅ **≥90% of Medium priority test cases PASS**
4. ✅ **No Critical or High severity defects remain open**
5. ✅ **All security test cases PASS**
6. ✅ **Core business workflows function correctly:**
   - Request submission
   - Approval process
   - Stock update accuracy
   - Audit trail completeness

### The system is REJECTED if:

1. ❌ Any Critical test case fails
2. ❌ Core business workflow broken
3. ❌ Data integrity compromised
4. ❌ Security vulnerability exists

---

## 📊 UAT Progress Tracking

| Module | Total Tests | Passed | Failed | Blocked | % Complete |
|--------|-------------|--------|--------|---------|------------|
| Authentication | 4 | | | | 0% |
| Staff Workflows | 10 | | | | 0% |
| Admin Workflows | 18 | | | | 0% |
| Security | 8 | | | | 0% |
| Reporting | 5 | | | | 0% |
| Integration | 3 | | | | 0% |
| **TOTAL** | **48** | **0** | **0** | **0** | **0%** |

---

## 📝 Sign-Off Section

### UAT Participants

| Name | Role | Signature | Date |
|------|------|-----------|------|
| | Staff Tester 1 | | |
| | Staff Tester 2 | | |
| | Admin Tester 1 | | |
| | Admin Tester 2 | | |
| | UAT Coordinator | | |

### UAT Decision

**Final Verdict:** ☐ ACCEPTED  ☐ REJECTED  ☐ ACCEPTED WITH CONDITIONS

**Conditions (if applicable):**
___________________________________________________________________________
___________________________________________________________________________

**Approved By:**

| Name | Position | Signature | Date |
|------|----------|-----------|------|
| | IT Manager | | |
| | System Administrator | | |

---

**Document End**
**Generated:** 7 January 2026
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK
**UAT Phase:** Ready for Execution
