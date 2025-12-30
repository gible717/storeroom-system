# Testing Checklist - Sistem Pengurusan Bilik Stor

**Date:** 5 December 2025
**Status:** Pre-Presentation Testing
**Tester:** Noufah

---

## ✅ Pre-Test Setup

- [ ] Laragon is running (green icon in tray)
- [ ] MySQL service is active
- [ ] Browser cache cleared
- [ ] Open in fresh browser window/tab
- [ ] Console (F12) closed during demo

---

## 🌐 Landing Page (index.php)

**URL:** `http://localhost/storeroom/`

- [ ] Page loads without errors
- [ ] Logo displays correctly
- [ ] "Log Masuk Ke Sistem" button visible
- [ ] Background image loads
- [ ] No console errors (check F12)
- [ ] Footer displays correctly

---

## 🔐 Login Functionality

**URL:** `http://localhost/storeroom/login.php`

### Admin Login
**Credentials:** admin / admin123 (or your password)

- [ ] Login page displays correctly
- [ ] Back arrow button visible (top left)
- [ ] Back arrow works (returns to index.php)
- [ ] Can login as admin successfully
- [ ] Redirects to admin_dashboard.php
- [ ] Wrong password shows error message
- [ ] Empty fields show validation

### Staff Login
**Credentials:** staff / staff123 (or your test account)

- [ ] Can login as staff successfully
- [ ] Redirects to staff_dashboard.php
- [ ] Session persists across pages

---

## 👨‍💼 Admin Dashboard

**URL:** `http://localhost/storeroom/admin_dashboard.php`

### Visual Check
- [ ] Sidebar displays on left (dark background)
- [ ] Logo and "Sistem Pengurusan" title visible
- [ ] All menu items visible
- [ ] User profile shows in top right
- [ ] Statistics cards display (4 cards)
- [ ] Charts load correctly
- [ ] Recent requests table visible

### Statistics Cards
- [ ] "Jumlah Produk" shows correct count
- [ ] "Permohonan Baru" shows correct count (yellow badge)
- [ ] "Diluluskan Bulan Ini" shows count
- [ ] "Ditolak Bulan Ini" shows count
- [ ] Icons display correctly

### Charts
- [ ] "Trend Permohonan (6 Bulan)" line chart loads
- [ ] "Pecahan Status Permohonan" pie chart loads
- [ ] Colors: Green (Diluluskan), Yellow (Baru), Red (Ditolak)
- [ ] No JavaScript errors

### Navigation
- [ ] Clicking "Admin Dashboard" stays on dashboard
- [ ] Sidebar links highlight on hover
- [ ] Active page is highlighted

---

## 📦 Produk (Product Management)

**URL:** `http://localhost/storeroom/admin_products.php`

### Display
- [ ] Products table displays
- [ ] Search box works
- [ ] Can filter by category
- [ ] Pagination shows correctly
- [ ] "Showing X to Y of Z entries" displays

### CRUD Operations
- [ ] Can add new product
- [ ] Can edit existing product
- [ ] Can view product details
- [ ] Form validation works
- [ ] Success messages display
- [ ] Error messages display (if any)

---

## 📋 Kemaskini Stok (Stock Update)

**URL:** `http://localhost/storeroom/admin_stock_update.php`

- [ ] Stock update form displays
- [ ] Can search for products
- [ ] Can update stock quantity
- [ ] Low stock alerts show (if any)
- [ ] Changes save successfully
- [ ] Success confirmation displays

---

## 📝 Pengurusan Permohonan (Request Management)

**URL:** `http://localhost/storeroom/manage_requests.php`

### Table Display
- [ ] All requests display in table
- [ ] 7 columns visible: ID, Nama Staf, Senarai Item, Bil. Item, Tarikh, Status, Tindakan
- [ ] Pagination info: "Showing 1 to X of Y entries"
- [ ] Status badges colored correctly:
  - [ ] Baru = Yellow
  - [ ] Diluluskan = Green
  - [ ] Ditolak = Red
  - [ ] Selesai = Blue

### Search & Filter
- [ ] Status filter dropdown works
- [ ] Search box appears
- [ ] Can search by ID Permohonan (e.g., "17")
- [ ] Can search by staff name
- [ ] Can search by item name
- [ ] Yellow highlight on search matches
- [ ] Clearing search shows all results again ✅ **NEW FIX**
- [ ] Pagination updates dynamically
- [ ] No console errors

### Quick View Modal ✅ **NEW FEATURE**
- [ ] Can click blue ID link (e.g., #17)
- [ ] Modal pops up
- [ ] Shows "Maklumat Permohonan #X" title
- [ ] Shows Nama Pemohon
- [ ] Shows Jawatan (if filled)
- [ ] Shows Catatan (if filled)
- [ ] Shows item list table
- [ ] Can close modal with X button
- [ ] Can close modal by clicking outside

### Actions
- [ ] "Semak" button visible for "Baru" status
- [ ] Can click "Semak" to review request
- [ ] Eye icon visible for approved requests
- [ ] Print icon visible for approved requests

---

## ✅ Request Review (Semakan Permohonan)

**URL:** `http://localhost/storeroom/request_review.php?id=X`

### Display
- [ ] Request details display
- [ ] Pemohon info shows (Nama, Jawatan)
- [ ] Jawatan displays user-entered value (not role) ✅ **FIX**
- [ ] Item list displays with quantities
- [ ] Approval form visible

### Actions
- [ ] Can approve request
- [ ] Can reject request
- [ ] Must enter rejection reason if rejecting
- [ ] Success message after approval
- [ ] Redirects after action
- [ ] Database updates correctly

---

## 📊 Laporan (Reports)

**URL:** `http://localhost/storeroom/admin_reports.php`

### Charts
- [ ] All charts load
- [ ] "Trend Permohonan" chart displays
- [ ] "Pecahan Status" pie chart displays
- [ ] Colors match: Green=Approved, Yellow=New, Red=Rejected ✅ **FIX**
- [ ] "Produk Popular" chart displays

### Report Options
- [ ] Can filter by date range
- [ ] Can export reports (if implemented)
- [ ] Data is accurate

---

## 👥 Pengguna (User Management)

**URL:** `http://localhost/storeroom/admin_users.php`

### Display
- [ ] Users table displays
- [ ] Search box works
- [ ] Search by ID Staf works ✅ **FIX**
- [ ] Search by Nama works ✅ **FIX**

### CRUD
- [ ] Can add new user (user_add.php)
- [ ] Email field is optional ✅ **FIX**
- [ ] Can submit without email
- [ ] Can edit users
- [ ] Can delete users (with confirmation)
- [ ] Validation works

---

## 👤 Admin Profile

**URL:** `http://localhost/storeroom/admin_profile.php`

- [ ] Profile displays correctly
- [ ] Can edit own jawatan ✅ **FIX**
- [ ] Can update profile info
- [ ] Can change password
- [ ] Can upload profile picture
- [ ] Changes save successfully

---

## 🔓 Logout

- [ ] Logout button visible
- [ ] Logout clears session
- [ ] Redirects to login page
- [ ] Cannot access admin pages after logout

---

## 👨‍🏫 Staff Dashboard

**URL:** `http://localhost/storeroom/staff_dashboard.php`

### Display
- [ ] Top navbar displays
- [ ] Welcome message shows staff name
- [ ] Action cards display (3 cards)
- [ ] No sidebar (staff has simpler interface)

### Action Cards
- [ ] "Permohonan Baru" card visible
- [ ] "Permohonan Saya" card visible
- [ ] "Profil Saya" card visible
- [ ] Icons display
- [ ] Cards are clickable

---

## 📄 Create New Request (Staff)

**URL:** `http://localhost/storeroom/kewps8_form.php?action=new`

### Form Display
- [ ] KEW.PS-8 form displays
- [ ] Nama Pemohon auto-filled
- [ ] Jawatan field is empty (optional) ✅ **FIX**
- [ ] Jawatan placeholder shows: "Contoh: Pegawai Teknologi Maklumat"
- [ ] Catatan field visible
- [ ] Item selection dropdown visible

### Add Items
- [ ] Can search for items in dropdown
- [ ] Can add multiple items
- [ ] Quantity field works
- [ ] "Tambah Item" button works
- [ ] Added items show in table
- [ ] Can remove items from list
- [ ] Item table updates dynamically

### Submit
- [ ] "Hantar" button visible
- [ ] Form validation works
- [ ] Must have at least 1 item
- [ ] Success message after submit
- [ ] No console errors ✅ **CLEANED**
- [ ] Redirects to request list

---

## 📋 My Requests (Staff)

**URL:** `http://localhost/storeroom/request_list.php`

### Display
- [ ] All staff's requests display
- [ ] Table shows: No, ID Permohonan, Bil. Item, Tarikh, Status, Tindakan
- [ ] Status filter works
- [ ] Search box works
- [ ] Pagination info: "Showing 1 to X of Y entries" ✅ **FIX**
- [ ] Newest requests first (descending order) ✅ **FIX**

### Quick View Modal ✅ **SAME AS ADMIN**
- [ ] Can click blue ID link
- [ ] Modal shows request details
- [ ] Shows Jawatan (user-entered)
- [ ] Shows Catatan
- [ ] Shows item list
- [ ] No console errors ✅ **CLEANED**

### Actions
- [ ] Can edit "Baru" status requests
- [ ] Can delete "Baru" status requests
- [ ] Delete confirmation works
- [ ] Can view approved requests
- [ ] Can print approved requests
- [ ] Eye icon opens KEW.PS-8 preview
- [ ] Print icon opens print dialog

---

## 📑 KEW.PS-8 Print Form

**URL:** `http://localhost/storeroom/kewps8_print.php?id=X`

### Display
- [ ] Form displays in print layout
- [ ] MPK logo visible
- [ ] Form title correct
- [ ] Pemohon info displays
- [ ] Jawatan blank (not showing role) ✅ **FIX**
- [ ] Item table displays correctly
- [ ] Pelulus info displays
- [ ] Pegawai Pelulus jawatan blank ✅ **FIX**
- [ ] Dates formatted correctly

### Print
- [ ] Print preview works (Ctrl+P or print icon)
- [ ] Layout fits A4 paper
- [ ] All info visible when printed
- [ ] No page breaks in wrong places

---

## 👤 Staff Profile

**URL:** `http://localhost/storeroom/staff_profile.php`

- [ ] Profile displays correctly
- [ ] Can edit own jawatan ✅ **FIX**
- [ ] Can update info
- [ ] Can change password
- [ ] Can upload profile picture
- [ ] Changes save successfully

---

## 🔐 Security Tests

### Session Management
- [ ] Cannot access admin pages as staff
- [ ] Cannot access staff pages as admin
- [ ] Session expires after logout
- [ ] Cannot access pages without login
- [ ] Redirects to login if not authenticated

### Input Validation
- [ ] SQL injection prevention (test with: `' OR '1'='1`)
- [ ] XSS prevention (test with: `<script>alert('xss')</script>`)
- [ ] Empty field validation
- [ ] Numeric field validation
- [ ] Email format validation (if required)

---

## 🌐 Browser Compatibility (Quick Check)

**Test in:**
- [ ] Chrome/Edge (primary browser)
- [ ] Firefox (if available)
- [ ] Mobile Chrome (if available)

---

## 🐛 Common Issues to Check

### Console Errors
- [ ] Open browser console (F12)
- [ ] Navigate through all pages
- [ ] **No console.log messages** ✅ **CLEANED**
- [ ] **No console.error messages** ✅ **CLEANED**
- [ ] No 404 errors for assets
- [ ] No JavaScript errors

### Database
- [ ] Check phpMyAdmin for data integrity
- [ ] Verify foreign keys work
- [ ] Check if all tables have data

### File Upload
- [ ] Profile picture uploads work
- [ ] File size limits respected
- [ ] Correct file types only

---

## 📊 Performance Check

- [ ] Pages load in < 3 seconds
- [ ] No lag when scrolling
- [ ] Charts render smoothly
- [ ] AJAX calls respond quickly
- [ ] Search results instant

---

## ✅ Final Verification

Before Presentation:
- [ ] All tests above passed
- [ ] No console errors anywhere
- [ ] Database has good sample data
- [ ] Sample data looks realistic (names, dates, items)
- [ ] All features work smoothly
- [ ] Browser cache cleared
- [ ] Laragon auto-start enabled
- [ ] Laptop power settings: Never sleep

---

## 📝 Test Results Summary

**Date Tested:** _________________
**Tested By:** Noufah
**Browser:** _________________
**Laragon Version:** _________________

**Total Tests:** 200+
**Passed:** _______
**Failed:** _______
**Notes:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

---

## 🚀 Ready for Presentation?

- [ ] All critical tests passed
- [ ] Demo script prepared
- [ ] Backup plan ready (screenshots/video)
- [ ] Confident in system functionality
- [ ] Know how to explain each feature

**Signature:** __________________  **Date:** __________

---

## 💡 Quick Test Run (15 minutes)

If short on time, test these critical paths:

**Path 1: Staff Creates Request (5 mins)**
1. Login as staff
2. Create new request
3. Add 3 items
4. Submit
5. View in request list
6. Click ID to view modal

**Path 2: Admin Approves Request (5 mins)**
1. Login as admin
2. View new request
3. Click ID to view modal
4. Click "Semak"
5. Approve request
6. Print KEW.PS-8

**Path 3: Search & Filter (3 mins)**
1. Test search on manage_requests.php
2. Type ID, clear search
3. Filter by status
4. Verify pagination updates

**Path 4: Reports (2 mins)**
1. Open admin_reports.php
2. Verify all charts load
3. Check colors correct

---

**Good luck with your presentation! 🎉**