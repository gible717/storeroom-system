# Testing Checklist - Sistem Pengurusan Bilik Stor

**Date:** 12 January 2026
**Tester:** Noufah
**Project:** Sistem Pengurusan Bilik Stor
**Environment:** Laragon + MySQL + PHP

---

## Test Case #1: Pre-Test Setup & Landing Page

| **Test Case #:** | 1 |
|---|---|
| **Test Case Name:** | Pre-Test Setup & Landing Page Loading |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Landing Page (index.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify system prerequisites and landing page loads correctly |

### Pre-conditions
- Laragon installed and configured
- MySQL database 'inventstor' exists with all tables
- Browser cache cleared
- Fresh browser window/tab opened

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Start Laragon | Laragon icon turns green in system tray | | |
| 2 | Verify MySQL is running | MySQL service shows as active | | |
| 3 | Navigate to http://localhost/storeroom/ | Landing page loads without errors | | |
| 4 | Check page elements | Logo, background image, "Log Masuk Ke Sistem" button all visible | | |
| 5 | Open browser console (F12) | No JavaScript errors in console | | |
| 6 | Click "Log Masuk Ke Sistem" | Redirects to login.php | | |

### Post-conditions
- Landing page successfully loaded
- No console errors
- System ready for login testing

---

## Test Case #2: Admin Login

| **Test Case #:** | 2 |
|---|---|
| **Test Case Name:** | Admin Login Functionality |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Authentication (login.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can login with valid credentials |

### Pre-conditions
- User is on login.php
- Admin account exists with username 'admin' and password 'admin123'

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Enter username: "admin" | Username field populated | | |
| 2 | Enter password: "admin123" | Password field shows dots | | |
| 3 | Click "Log Masuk" button | System validates credentials | | |
| 4 | Wait for redirect | Redirects to admin_dashboard.php | | |
| 5 | Check session | Session created with admin role | | |
| 6 | Verify dashboard loads | Dashboard displays with admin name in header | | |

### Post-conditions
- Admin successfully logged in
- Session active
- Admin dashboard accessible

---

## Test Case #3: Admin Login - Negative Testing

| **Test Case #:** | 3 |
|---|---|
| **Test Case Name:** | Admin Login with Invalid Credentials |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Authentication (login.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify system rejects invalid login attempts |

### Pre-conditions
- User is on login.php
- Not logged in

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Enter username: "admin" | Username field populated | | |
| 2 | Enter password: "wrongpassword" | Password field shows dots | | |
| 3 | Click "Log Masuk" button | Error message displays: "Invalid credentials" | | |
| 4 | Verify no redirect | Stays on login.php | | |
| 5 | Leave username empty | Validation message shows | | |
| 6 | Leave password empty | Validation message shows | | |

### Post-conditions
- No session created
- User remains on login page
- Appropriate error messages displayed

---

## Test Case #4: Admin Dashboard Display

| **Test Case #:** | 4 |
|---|---|
| **Test Case Name:** | Admin Dashboard Loading and Display |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Admin Dashboard (admin_dashboard.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin dashboard displays all components correctly |

### Pre-conditions
- Admin is logged in
- Database has sample data (products, requests, users)

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Navigate to admin_dashboard.php | Page loads completely | | |
| 2 | Check sidebar | Sidebar displays with all menu items | | |
| 3 | Check stat cards | 3 colored stat cards display (Jumlah Produk, Permohonan Tertunda, Pantau Stok) | | |
| 4 | Check stat card colors | Blue (Produk), Yellow (Tertunda), Red (Stok) gradients visible | | |
| 5 | Check "Permohonan Terkini" | Table shows 6 most recent requests | | |
| 6 | Check request sorting | "Baru" status appears first, then "Diluluskan", then "Ditolak", then "Selesai" | | |
| 7 | Check mini stat cards | 4 mini cards display with colored icons (Pengguna-Blue, Bulan Ini-Yellow, Kelulusan-Green, Jabatan-Cyan) | | |
| 8 | Hover over stat cards | Cards lift slightly on hover | | |
| 9 | Check console | No errors in console | | |

### Post-conditions
- Dashboard fully loaded
- All statistics accurate
- No visual glitches

---

## Test Case #5: Product Management - View Products

| **Test Case #:** | 5 |
|---|---|
| **Test Case Name:** | View Products List |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Product Management (admin_products.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify products list displays correctly with search, filters, and all features |

### Pre-conditions
- Admin is logged in
- Database contains product records
- Products have various stock levels (normal, low, out of stock)

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Produk" in sidebar | Redirects to admin_products.php, page title shows "Pengurusan Produk" | | |
| 2 | Check table columns | Table displays with columns: Bil, Kod Item, Nama Produk, Kategori, Nama Pembekal, Harga (RM), Stok, Status, Tindakan | | |
| 3 | Check filter dropdowns | Three filter dropdowns visible: "Semua Kategori", "Semua Pembekal", "Semua Status" | | |
| 4 | Select "Semua Kategori" dropdown | Shows list of all product categories (e.g., Toner, Alat Tulis, etc.) | | |
| 5 | Filter by specific category | Select a category - table filters to show only products in that category | | |
| 6 | Select "Semua Pembekal" dropdown | Shows list of all suppliers (e.g., Puncak Niaga, Econ Stationary, SME Office Equipment, etc.) | | |
| 7 | Filter by specific supplier | Select a supplier - table filters to show only products from that supplier | | |
| 8 | Select "Semua Status" dropdown | Shows stock status options (Stok Rendah, Kehabisan Stok, Normal) | | |
| 9 | Filter by "Kehabisan Stok" | Table shows only products with 0 unit stock | | |
| 10 | Filter by "Stok Rendah" | Table shows only products with low stock levels | | |
| 11 | Check search box | Search box visible with placeholder "Cari Kod, Nama Produk..." | | |
| 12 | Enter search term "HP 975" | Table filters to show matching products (HP 975X BLACK, HP 975X CYAN, etc.) | | |
| 13 | Clear search | All products display again | | |
| 14 | Check status badges | Products show correct status: "STOK RENDAH" (orange badge) for low stock, "KEHABISAN STOK" (red badge) for 0 stock | | |
| 15 | Check "Urus Kategori" button | Button visible at top right | | |
| 16 | Click "Urus Kategori" button | Navigates to admin_category.php, shows "Pengurusan Kategori" page with list of categories and "Tambah Kategori Baru" form | | |
| 17 | Check "+ Tambah Produk" button | Blue button visible at top right | | |
| 18 | Click "+ Tambah Produk" button | Navigates to admin_add_product.php, shows "Tambah Produk Baru" form | | |
| 19 | Check action icons | Each row has 3 action icons: View (eye), Edit (pencil), Delete (trash) | | |
| 20 | Click view icon (eye) | Opens product detail view/modal | | |
| 21 | Click edit icon (pencil) | Opens product edit form/modal | | |
| 22 | Verify Harga (RM) column | Prices display with proper formatting (e.g., 244.50, 420.17) | | |
| 23 | Verify Stok column | Stock displays with unit (e.g., "4 unit", "0 unit", "5 unit") | | |
| 24 | Combine multiple filters | Select category + status filter together - filters work in combination | | |
| 25 | Reset all filters | Change all dropdowns back to "Semua..." - all products display | | |

### Post-conditions
- Products displayed correctly with all columns
- All three filter dropdowns working correctly
- Search functionality working
- Status badges showing correct stock status
- All action buttons functional
- Data accurate and matches database

---

## Test Case #6: Category Management

| **Test Case #:** | 6 |
|---|---|
| **Test Case Name:** | Category Management (View, Add, Edit, Delete) |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Category Management (admin_category.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can manage product categories (view, add, edit, delete) |

### Pre-conditions
- Admin is logged in
- On admin_category.php page (navigated from "Urus Kategori" button)
- Page shows "Pengurusan Kategori"

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Check page title | Page displays "Pengurusan Kategori" with back arrow (←) | | |
| 2 | Check left section | "Senarai Kategori Sedia Ada" table visible | | |
| 3 | Check table columns | Table shows: Nama Kategori, Tindakan | | |
| 4 | Check existing categories | List of categories displayed (e.g., Toner) | | |
| 5 | Check action icons | Each row has 2 icons: Edit (pencil), Delete (trash) | | |
| 6 | Check right section | "Tambah Kategori Baru" card visible with blue header | | |
| 7 | Check add form | "Nama Kategori" label with input field, placeholder "Cth: Toner" | | |
| 8 | Check add button | "+ Tambah" blue button visible | | |
| 9 | Leave Nama Kategori empty and click "+ Tambah" | Validation message appears (field required) | | |
| 10 | Enter new category: "Alat Tulis" | Field populated | | |
| 11 | Click "+ Tambah" button | Success message displays | | |
| 12 | Check category list | New category "Alat Tulis" appears in table | | |
| 13 | Click edit icon (pencil) on a category | Edit modal/form appears with current name pre-filled | | |
| 14 | Change category name | Field updated with new value | | |
| 15 | Save edit | Success message displays, name updated in table | | |
| 16 | Click delete icon (trash) on a category | Confirmation prompt appears | | |
| 17 | Cancel delete | Category remains in list | | |
| 18 | Click delete icon again and confirm | Category removed from list (if not in use) | | |
| 19 | Try deleting category with products | Error message: Cannot delete category in use | | |
| 20 | Click back arrow (←) | Returns to admin_products.php | | |

### Post-conditions
- Categories can be viewed, added, edited, and deleted
- Cannot delete categories that are in use by products
- All changes saved to database
- Form validation working

---

## Test Case #7: Product Management - Add New Product

| **Test Case #:** | 7 |
|---|---|
| **Test Case Name:** | Add New Product |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Product Management (admin_add_product.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can add new product successfully with all form fields |

### Pre-conditions
- Admin is logged in
- On admin_add_product.php page (navigated from "+ Tambah Produk" button)
- Page shows "Tambah Produk Baru" form

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Check form title | Page displays "Tambah Produk Baru" with back arrow | | |
| 2 | Check "Nama Produk" field | Empty text field with asterisk (*) indicating required | | |
| 3 | Check "ID Produk / SKU" field | Empty text field with asterisk (*), placeholder shows "Contoh: A4-PAPER-001" | | |
| 4 | Check "Kategori" dropdown | Dropdown shows "-- Sila Pilih Kategori --" with asterisk (*) | | |
| 5 | Check "Nama Pembekal" field | Empty text field with placeholder "Contoh: Syarikat ABC Sdn Bhd", helper text shows "Nama pembekal untuk tujuan rekod sahaja (pilihan)" | | |
| 6 | Check "Harga Seunit (RM)" field | Field with "RM" prefix, default value "0.00" | | |
| 7 | Check "Kuantiti Stok Awal" field | Empty number field with asterisk (*), default value "0" | | |
| 8 | Check buttons | "Batal" (grey) and "Simpan Produk" (blue) buttons visible | | |
| 9 | Enter Nama Produk: "Test Pen Pilot" | Field populated | | |
| 10 | Enter ID Produk / SKU: "PEN-PILOT-001" | Field populated | | |
| 11 | Click Kategori dropdown | Shows list of available categories | | |
| 12 | Select a category (e.g., "Alat Tulis") | Category selected and displayed | | |
| 13 | Enter Nama Pembekal: "ABC Stationery Sdn Bhd" | Field populated (optional field) | | |
| 14 | Enter Harga Seunit: "5.50" | Field shows "RM 5.50" | | |
| 15 | Enter Kuantiti Stok Awal: "100" | Field populated with number | | |
| 16 | Click "Simpan Produk" button | Success message displays, redirects to admin_products.php | | |
| 17 | Check products table | New product "Test Pen Pilot" appears in product list | | |
| 18 | Verify product details | Product shows correct: Kod Item, Nama, Kategori, Pembekal, Harga, Stok | | |

### Post-conditions
- New product successfully added to database
- Product visible in admin_products.php table
- All entered data saved correctly
- Stock level set to initial quantity

---

## Test Case #8: Stock Management - Update Stock

| **Test Case #:** | 8 |
|---|---|
| **Test Case Name:** | Manual Stock Update |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Stock Management (admin_stock_manual.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can manually add stock (Tambah Stok) with all form fields |

### Pre-conditions
- Admin is logged in
- Products exist in database with categories assigned

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Kemaskini Stok" in sidebar | Navigates to admin_stock_manual.php, page title shows "Pengemaskinian Stok" | | |
| 2 | Check form card title | "Kemaskini Stok" card visible | | |
| 3 | Check "Kategori" dropdown | Dropdown shows "-- Semua Kategori --" as default | | |
| 4 | Click Kategori dropdown | Shows list of all categories (e.g., Toner, Alat Tulis) | | |
| 5 | Select a category (e.g., "Toner") | Category selected, Nama Item dropdown updates to show items in that category | | |
| 6 | Check "Nama Item" dropdown | Dropdown shows "-- Sila Pilih Item --" with asterisk (*) indicating required | | |
| 7 | Click Nama Item dropdown | Shows list of items filtered by selected category | | |
| 8 | Select an item (e.g., "HP 975X CYAN") | Item selected | | |
| 9 | Check "Stok Semasa" field | Displays current stock quantity with "Unit" label (read-only) | | |
| 10 | Check "Kuantiti Masuk" field | Number input field with asterisk (*), default value "1", with "Unit" label | | |
| 11 | Check "Catatan (Optional)" field | Text field with placeholder "Cth: Invois 12345 / Dari Pembekal A" | | |
| 12 | Check helper text | Shows "*Boleh diisi untuk rujukan Laporan Transaksi." below Catatan field | | |
| 13 | Check buttons | "Batal" (grey) and "Tambah Stok" (blue) buttons visible | | |
| 14 | Leave Nama Item unselected and click "Tambah Stok" | Validation message appears (item required) | | |
| 15 | Select item but leave Kuantiti Masuk as 0 | Validation message appears (quantity must be > 0) | | |
| 16 | Enter Kuantiti Masuk: "50" | Field populated with number | | |
| 17 | Enter Catatan: "Invois 12345 / Dari ABC Supplier" | Field populated (optional) | | |
| 18 | Click "Tambah Stok" button | Success message displays | | |
| 19 | Check Stok Semasa | Stock increased by 50 from previous value | | |
| 20 | Navigate to admin_products.php | Verify product stock updated in product list | | |
| 21 | Click "Batal" button | Form resets or navigates back | | |

### Post-conditions
- Stock level updated correctly (increased by Kuantiti Masuk)
- Transaction recorded in database
- Low stock alert clears if stock now above threshold
- Catatan saved for transaction reference

---

## Test Case #9: Request Management - View Requests

| **Test Case #:** | 9 |
|---|---|
| **Test Case Name:** | View All Requests with Filtering |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Request Management (manage_requests.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can view, filter, and manage all requests with all features |

### Pre-conditions
- Admin is logged in
- Database contains request records with various statuses (Baru, Diluluskan, Ditolak)

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Permohonan" in sidebar | Navigates to manage_requests.php, page title shows "Pengurusan Permohonan" | | |
| 2 | Check page subtitle | "Senarai Permohonan" displayed | | |
| 3 | Check "+ Buat Permohonan" button | Blue button visible at top right | | |
| 4 | Click "+ Buat Permohonan" button | Navigates to kewps8_form.php for creating new request | | |
| 5 | Navigate back to manage_requests.php | Request list displayed | | |
| 6 | Check filter dropdowns | Two dropdowns visible: "Semua Status", "Semua Kategori" | | |
| 7 | Check search box | Search box visible with placeholder "Cari ID, Staf, atau Item..." | | |
| 8 | Check table columns | Table shows: ID Permohonan, Nama Staf, Senarai Item, Bil. Item, Tarikh Mohon, Status, Tindakan | | |
| 9 | Check ID Permohonan column | IDs are clickable links (e.g., #48, #44, #47) | | |
| 10 | Click on an ID link | Opens request detail view/modal | | |
| 11 | Check status badges | BARU (yellow), DILULUSKAN (green), DITOLAK (red) badges displayed correctly | | |
| 12 | Check Tindakan column for "BARU" status | Shows "Semak" button (teal/cyan) | | |
| 13 | Check Tindakan column for "DILULUSKAN" status | Shows View (eye icon, cyan) and Print (printer icon, green) buttons | | |
| 14 | Check Tindakan column for "DITOLAK" status | Shows Catatan with rejection remarks (if available) | | |
| 15 | Click "Semua Status" dropdown | Shows options: Semua Status, Baru, Diluluskan, Ditolak | | |
| 16 | Select "Baru" from status filter | Table filters to show only BARU requests | | |
| 17 | Select "Diluluskan" from status filter | Table filters to show only DILULUSKAN requests | | |
| 18 | Select "Ditolak" from status filter | Table filters to show only DITOLAK requests | | |
| 19 | Reset to "Semua Status" | All requests display again | | |
| 20 | Click "Semua Kategori" dropdown | Shows list of product categories | | |
| 21 | Select a category | Table filters to show requests containing items from that category | | |
| 22 | Enter search term "NOUFAH" in search box | Table filters to show requests from staff named Noufah | | |
| 23 | Enter search term "#48" in search box | Table filters to show request with ID #48 | | |
| 24 | Enter search term "CB 435A" in search box | Table filters to show requests containing that item | | |
| 25 | Clear search | All requests display again | | |
| 26 | Check pagination | Shows "Showing X to Y of Z entries" at bottom | | |
| 27 | Click pagination numbers (if available) | Navigates to next/previous page of results | | |
| 28 | Click "Semak" button on a BARU request | Navigates to request_review.php for approval/rejection | | |
| 29 | Click view icon on a DILULUSKAN request | Opens request detail view | | |
| 30 | Click print icon on a DILULUSKAN request | Opens kewps8_print.php for printing | | |

### Post-conditions
- All requests visible with correct data
- Both filter dropdowns (Status, Kategori) working correctly
- Search functionality filters by ID, Staff name, and Item name
- Status badges display correct colors
- Action buttons appropriate for each status
- Pagination working (if applicable)
- Navigation to create/review/print requests working

---

## Test Case #10: Request Review and Approval

| **Test Case #:** | 10 |
|---|---|
| **Test Case Name:** | Admin Reviews and Approves Request |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Request Review (request_review.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can review and approve pending requests |

### Pre-conditions
- Admin is logged in
- At least one "Baru" status request exists

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Find "Baru" request in table | Status badge shows yellow "BARU" | | |
| 2 | Click "Semak" button | Redirects to request_review.php | | |
| 3 | Check request details | Shows: Nama Pemohon, Jawatan, Catatan, Item list | | |
| 4 | Verify item list | Table shows all requested items with quantities | | |
| 5 | Click "Luluskan" button | Confirmation prompt appears | | |
| 6 | Confirm approval | Success message displays | | |
| 7 | Check status | Request status changes to "Diluluskan" | | |
| 8 | Verify redirect | Returns to manage_requests.php | | |

### Post-conditions
- Request approved
- Status updated in database
- Stock levels adjusted (if implemented)

---

## Test Case #11: Request Rejection

| **Test Case #:** | 11 |
|---|---|
| **Test Case Name:** | Admin Rejects Request with Remarks |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Request Review (request_review.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can reject request with mandatory remarks |

### Pre-conditions
- Admin is logged in on request_review.php
- Viewing a "Baru" request

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Tolak" button | Remarks modal/field appears | | |
| 2 | Leave remarks empty | Validation message: "Remarks required" | | |
| 3 | Enter rejection reason | Text field populated | | |
| 4 | Click "Tolak" confirmation | Success message displays | | |
| 5 | Check status | Request status changes to "Ditolak" | | |
| 6 | Verify remarks saved | Remarks stored in database | | |
| 7 | Check staff view | Staff can see rejection remarks | | |

### Post-conditions
- Request rejected
- Rejection remarks visible to staff
- Database updated

---

## Test Case #12: User Management - Add New User

| **Test Case #:** | 12 |
|---|---|
| **Test Case Name:** | Add New User Account |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | User Management (admin_users.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can create new user accounts |

### Pre-conditions
- Admin is logged in
- On admin_users.php page

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Tambah Pengguna" button | Redirects to user_add.php | | |
| 2 | Enter ID Staf: "TEST001" | Field populated | | |
| 3 | Enter Nama: "Test User" | Field populated | | |
| 4 | Enter Username: "testuser" | Field populated | | |
| 5 | Enter Password: "test123" | Field shows dots | | |
| 6 | Select Role: "Staf" | Dropdown selected | | |
| 7 | Leave email empty (optional) | Field left blank | | |
| 8 | Click "Simpan" | Success message displays | | |
| 9 | Check user list | New user appears in table | | |

### Post-conditions
- New user created
- Can login with new credentials
- Email field optional (not required)

---

## Test Case #13: Staff Login

| **Test Case #:** | 13 |
|---|---|
| **Test Case Name:** | Staff Login Functionality |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Authentication (login.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify staff can login and access staff dashboard |

### Pre-conditions
- Logged out from any previous session
- Staff account exists (username: staff, password: staff123)

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Navigate to login.php | Login page loads | | |
| 2 | Enter username: "staff" | Username field populated | | |
| 3 | Enter password: "staff123" | Password field shows dots | | |
| 4 | Click "Log Masuk" | System validates credentials | | |
| 5 | Wait for redirect | Redirects to staff_dashboard.php | | |
| 6 | Check dashboard | Staff dashboard displays with welcome message | | |
| 7 | Verify session | Session created with staff role | | |

### Post-conditions
- Staff successfully logged in
- Staff dashboard accessible
- Cannot access admin pages

---

## Test Case #14: Staff Dashboard Display

| **Test Case #:** | 14 |
|---|---|
| **Test Case Name:** | Staff Dashboard Loading |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Staff Dashboard (staff_dashboard.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify staff dashboard displays correctly |

### Pre-conditions
- Staff is logged in

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Navigate to staff_dashboard.php | Page loads | | |
| 2 | Check top navbar | Navbar shows with staff name | | |
| 3 | Check action cards | 3 cards visible: Permohonan Baru, Permohonan Saya, Profil Saya | | |
| 4 | Verify no sidebar | Staff interface simpler than admin (no sidebar) | | |
| 5 | Check icons | Icons display on each card | | |
| 6 | Hover over cards | Cards respond to hover | | |

### Post-conditions
- Dashboard fully loaded
- All action cards clickable
- Navigation works

---

## Test Case #15: User Creates New Request (KEW.PS-8)

| **Test Case #:** | 15 |
|---|---|
| **Test Case Name:** | User Creates New Stock Request |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | KEW.PS-8 Form (kewps8_form.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify staff or admin can create new request with multiple items |

### Pre-conditions
- Staff or admin is logged in
- Products exist in database

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Permohonan Baru" card (or navigate to kewps8_form.php) | Opens kewps8_form.php | | |
| 2 | Check Nama Pemohon | Auto-filled with user's name | | |
| 3 | Check Jawatan field | Empty with placeholder "Contoh: Pegawai Teknologi Maklumat" | | |
| 4 | Enter Jawatan: "Pegawai IT" | Field populated | | |
| 5 | Check Catatan field | Optional field (can be left empty) | | |
| 6 | Leave Catatan empty | Field remains empty | | |
| 7 | Search item in dropdown | Type "HP" - matching items appear | | |
| 8 | Select item "HP 975X CYAN" | Item selected | | |
| 9 | Enter quantity: "2" | Quantity field populated | | |
| 10 | Click "Tambah Item" | Item added to table below | | |
| 11 | Add 2 more items | Repeat steps 7-10 for other items | | |
| 12 | Check item table | Shows 3 items with quantities | | |
| 13 | Click "Sahkan" button | Confirmation modal appears | | |
| 14 | Click "Hantar" in modal | Success message displays (Catatan field not required) | | |
| 15 | Check console | No console errors | | |

### Post-conditions
- Request created successfully (with or without Catatan)
- Status set to "Baru"
- Redirects to request list
- Request visible in user's request list
- If user is admin, they cannot approve their own request

---

## Test Case #16: Staff Views Request List

| **Test Case #:** | 16 |
|---|---|
| **Test Case Name:** | View My Requests |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Request List (request_list.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify staff can view their own requests only |

### Pre-conditions
- Staff is logged in
- Staff has created at least one request

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Click "Permohonan Saya" card | Opens request_list.php | | |
| 2 | Check table | Shows: No, ID Permohonan, Bil. Item, Tarikh, Status, Tindakan | | |
| 3 | Verify sorting | Newest requests first (descending order) | | |
| 4 | Check status badges | Colored correctly (Yellow/Green/Red/Blue) | | |
| 5 | Check pagination info | Shows "Showing 1 to X of Y entries" | | |
| 6 | Click request ID link | Quick view modal opens | | |
| 7 | Check modal content | Shows Nama Pemohon, Jawatan, Catatan, Item list | | |
| 8 | Close modal | Click X or outside modal | | |

### Post-conditions
- Only staff's own requests visible
- Data displayed accurately
- No console errors

---

## Test Case #17: User Edits Pending Request

| **Test Case #:** | 17 |
|---|---|
| **Test Case Name:** | Edit Pending Request |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Request Edit (kewps8_form.php?action=edit) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify staff or admin can edit requests with "Baru" status only |

### Pre-conditions
- Staff or admin is logged in
- Has at least one "Baru" status request

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Find "Baru" request in list | Status shows yellow "BARU" | | |
| 2 | Click "Edit" button | Opens kewps8_form.php with existing data | | |
| 3 | Check form fields | Jawatan pre-filled, Catatan pre-filled (if previously entered) | | |
| 4 | Check item list | All items from original request shown | | |
| 5 | Modify Catatan (optional) | Edit text or leave empty | | |
| 6 | Clear Catatan field | Field can be emptied (optional field) | | |
| 7 | Add new item | Item added to list | | |
| 8 | Remove an item | Click remove, item deleted | | |
| 9 | Click "Kemaskini" | Success message displays (Catatan not mandatory) | | |
| 10 | Return to request list | Changes visible | | |

### Post-conditions
- Request updated successfully (with or without Catatan)
- Status remains "Baru"
- Cannot edit approved/rejected requests

---

## Test Case #18: KEW.PS-8 Print Preview

| **Test Case #:** | 18 |
|---|---|
| **Test Case Name:** | Print KEW.PS-8 Form |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Print Form (kewps8_print.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify approved requests can be printed in KEW.PS-8 format |

### Pre-conditions
- Staff or admin is logged in
- At least one "Diluluskan" request exists

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Find "Diluluskan" request | Status shows green | | |
| 2 | Click print icon | Opens kewps8_print.php | | |
| 3 | Check MPK logo | Logo displays at top | | |
| 4 | Check form title | "KEW.PS-8 PERMOHONAN KERTAS & ALAT TULIS" | | |
| 5 | Check Pemohon section | Nama and Jawatan display (user-entered, not role) | | |
| 6 | Check item table | All items with quantities shown | | |
| 7 | Check Pelulus section | Admin name and date shown | | |
| 8 | Check Pegawai Pelulus Jawatan | Blank (not showing role) | | |
| 9 | Press Ctrl+P | Print dialog opens | | |
| 10 | Check print preview | Layout fits A4 paper | | |

### Post-conditions
- Form ready to print
- All data accurate
- Professional layout

---

## Test Case #19: Admin Request Approval by Another Admin

| **Test Case #:** | 19 |
|---|---|
| **Test Case Name:** | Admin Creates Request and Another Admin Approves |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | KEW.PS-8 Request & Approval (kewps8_form.php, request_review.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify admin can create request but cannot approve their own request - requires another admin |

### Pre-conditions
- Two admin accounts exist (Admin A and Admin B)
- Admin A is logged in
- Products exist in database

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Admin A creates new KEW.PS-8 request | Navigate to kewps8_form.php | | |
| 2 | Fill in Jawatan and add items | Form completed with items | | |
| 3 | Submit request | Request created with status "Baru" | | |
| 4 | Admin A navigates to manage_requests.php | Can see their own request in list | | |
| 5 | Admin A clicks "Semak" on their own request | Opens request_review.php | | |
| 6 | Check approval buttons | "Luluskan" and "Tolak" buttons are DISABLED or hidden for own request | | |
| 7 | Verify restriction message | System shows message: "Anda tidak boleh meluluskan permohonan sendiri" | | |
| 8 | Logout Admin A | Session cleared | | |
| 9 | Login as Admin B | Admin B logged in successfully | | |
| 10 | Admin B navigates to manage_requests.php | Can see Admin A's request | | |
| 11 | Admin B clicks "Semak" on Admin A's request | Opens request_review.php | | |
| 12 | Check approval buttons | "Luluskan" and "Tolak" buttons are ENABLED for Admin B | | |
| 13 | Admin B clicks "Luluskan" | Confirmation prompt appears | | |
| 14 | Confirm approval | Request status changes to "Diluluskan" | | |
| 15 | Check database | pelulus_id is Admin B's ID, not Admin A's | | |

### Post-conditions
- Admin cannot approve their own requests
- Another admin can approve the request
- System enforces separation of duties
- Request approval properly logged with correct approver ID

---

## Test Case #20: Session Security

| **Test Case #:** | 20 |
|---|---|
| **Test Case Name:** | Session Management and Access Control |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Authentication & Authorization |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify session security and role-based access control |

### Pre-conditions
- Browser cleared of all sessions/cookies
- Not logged in

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Try accessing admin_dashboard.php directly | Redirects to login.php | | |
| 2 | Try accessing staff_dashboard.php directly | Redirects to login.php | | |
| 3 | Login as staff | Successfully logged in | | |
| 4 | Try accessing admin_products.php | Access denied or redirect | | |
| 5 | Logout | Session cleared | | |
| 6 | Try accessing staff_dashboard.php | Redirects to login.php | | |
| 7 | Login as admin | Successfully logged in | | |
| 8 | Admin can create KEW.PS-8 request | Request created successfully | | |
| 9 | Admin tries to approve their own request | Approval buttons disabled or error message shown | | |
| 10 | Verify separation of duties | System prevents admin from approving own request | | |

### Post-conditions
- Unauthorized access prevented
- Sessions properly managed
- Role-based access working
- Admin self-approval restriction enforced

---

## Test Case #21: Input Validation & Security

| **Test Case #:** | 21 |
|---|---|
| **Test Case Name:** | SQL Injection and XSS Prevention |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | All Forms |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify system prevents SQL injection and XSS attacks |

### Pre-conditions
- System is accessible
- Test accounts available

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Login with username: `' OR '1'='1` | Login rejected, no SQL error | | |
| 2 | Enter product name: `<script>alert('xss')</script>` | Script not executed, stored as text | | |
| 3 | Enter catatan: `"; DROP TABLE permohonan; --` | Input sanitized, no damage | | |
| 4 | Test empty required fields | Validation messages appear | | |
| 5 | Test numeric fields with letters | Validation rejects input | | |
| 6 | Test email with invalid format | Email validation works | | |

### Post-conditions
- No SQL injection possible
- No XSS vulnerabilities
- Input properly validated and sanitized

---

## Test Case #22: Performance & Console Check

| **Test Case #:** | 22 |
|---|---|
| **Test Case Name:** | Performance and Console Error Check |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | All Pages |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify system performance and clean console output |

### Pre-conditions
- System fully loaded
- Sample data in database

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Open browser console (F12) | Console panel opens | | |
| 2 | Navigate to index.php | Page loads in < 3 seconds | | |
| 3 | Check console | No errors, no console.log messages | | |
| 4 | Navigate to admin_dashboard.php | Page loads in < 3 seconds | | |
| 5 | Check console | No errors or warnings | | |
| 6 | Navigate to manage_requests.php | Page loads quickly | | |
| 7 | Check console | No 404 errors for assets | | |
| 8 | Test search functionality | Results appear instantly | | |
| 9 | Navigate through all pages | All pages load smoothly | | |
| 10 | Check console on all pages | Clean console (no errors) | | |

### Post-conditions
- All pages load within 3 seconds
- No console errors anywhere
- Smooth user experience

---

## Test Case #23: KEW.PS-3 Report Generation

| **Test Case #:** | 23 |
|---|---|
| **Test Case Name:** | Generate and View KEW.PS-3 Report |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | KEW.PS-3 Report (kewps3_report.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify KEW.PS-3 stock report displays correctly with filtering options |

### Pre-conditions
- Admin is logged in
- Database contains stock transaction records
- Products exist in database

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Navigate to kewps3_report.php | Page loads with filter options | | |
| 2 | Check page title | "KEW.PS-3 REKOD PENERIMAAN DAN PENGELUARAN STOK" displayed | | |
| 3 | Check filter controls | Date range picker, product filter, category filter visible | | |
| 4 | Select date range | Start and end dates can be selected | | |
| 5 | Select product category | Category dropdown filters products | | |
| 6 | Click "Jana Laporan" | Report generates with selected filters | | |
| 7 | Check report table columns | Shows: Tarikh, No Kod Barang, Perihal, Terima, Keluar, Baki | | |
| 8 | Verify calculations | Opening balance + Terima - Keluar = Closing balance | | |
| 9 | Check report summary | Total Terima and Total Keluar calculated correctly | | |
| 10 | Test print functionality | Press Ctrl+P, print preview displays properly | | |
| 11 | Check report header | MPK logo and report title formatted for printing | | |
| 12 | Test export options | CSV/Excel export works (if implemented) | | |
| 13 | Test empty results | Selecting date range with no data shows "Tiada rekod" | | |

### Post-conditions
- Report generates accurately
- Calculations are correct
- Print layout is professional
- All filters work properly

---

## Test Case #24: Inventory Report

| **Test Case #:** | 24 |
|---|---|
| **Test Case Name:** | Generate and View Inventory Report |
| **System:** | Sistem Pengurusan Bilik Stor |
| **Subsystem:** | Inventory Report (report_inventory.php) |
| **Designed by:** | Noufah |
| **Design Date:** | 12/01/2026 |
| **Executed by:** | Noufah |
| **Execution Date:** | ___/___/_____ |
| **Short Description:** | Verify inventory report displays current stock levels with search and filter |

### Pre-conditions
- Admin is logged in
- Database contains products with stock data

### Test Steps

| **Step** | **Action** | **Expected System Response** | **Pass/Fail** | **Comment** |
|---|---|---|---|---|
| 1 | Navigate to report_inventory.php | Page loads with inventory list | | |
| 2 | Check page title | "Laporan Inventori Semasa" or similar title displayed | | |
| 3 | Check table columns | Shows: No Kod, Perihal, Kategori, Baki Semasa, Unit, Status | | |
| 4 | Verify stock status indicators | Low stock items highlighted (red/yellow warning) | | |
| 5 | Test search functionality | Search by product code or description filters results | | |
| 6 | Test category filter | Filter by category shows only matching items | | |
| 7 | Check sorting | Click column headers to sort (ascending/descending) | | |
| 8 | Verify stock levels | Baki Semasa matches database records | | |
| 9 | Check low stock alert | Products below minimum level show warning | | |
| 10 | Test print functionality | Press Ctrl+P, print layout displays properly | | |
| 11 | Check pagination | If >10 items, pagination controls work correctly | | |
| 12 | Test export options | CSV/Excel export works (if implemented) | | |
| 13 | Verify report date/time | Current date and time displayed on report | | |

### Post-conditions
- Inventory report displays accurate current stock levels
- Low stock warnings visible
- Search and filter working correctly
- Print-friendly layout

---

## Final Test Summary

| **Category** | **Total Tests** | **Passed** | **Failed** | **Notes** |
|---|---|---|---|---|
| Authentication | | | | |
| Admin Dashboard | | | | |
| Product Management | | | | |
| Stock Management | | | | |
| Request Management | | | | |
| User Management | | | | |
| Staff Functions | | | | |
| KEW.PS-8 Forms | | | | |
| Security | | | | |
| Performance | | | | |

---

## Sign-off

**Date Tested:** _______________
**Tested By:** Noufah
**Browser:** _______________
**Environment:** Laragon + MySQL + PHP

**Overall Status:** ☐ Pass  ☐ Fail

**Tester Signature:** __________________  **Date:** __________

---

**Ready for Presentation:** ☐ Yes  ☐ No

**Comments:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
