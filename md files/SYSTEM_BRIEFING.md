# COMPREHENSIVE CODEBASE BRIEFING DOCUMENT
## Sistem Pengurusan Bilik Stor dan Inventori (Storeroom Management System)

**Organization:** Majlis Perbandaran Kangar, Perlis
**System Status:** Production Ready - Cleaned & Optimized
**Last Updated:** 7 January 2026
**Total PHP Lines:** ~14,460

---

## EXECUTIVE SUMMARY

This is a **government inventory management system** designed for the municipal council's storeroom operations. It enables staff to request inventory items and administrators to approve requests, manage stock, and generate comprehensive reports. The system includes real-time Telegram notifications, transaction logging, and department-level analytics.

**Key Metrics:**
- ~90 PHP files with dedicated logic layers
- 7 core database tables with 8 FK constraints
- 2 user roles (Admin, Staff) with self-approval prevention
- 100% Malay localization in UI
- Fully responsive Bootstrap 5 design
- Complete audit trail via transaksi_stok table

---

## 1. PROJECT STRUCTURE & FILE ORGANIZATION

### Directory Hierarchy
```
storeroom/
├── Core Files (entry points)
│   ├── index.php                 (Landing page with login/register buttons)
│   ├── login.php                 (Login form with password reset)
│   ├── login_process.php         (Authentication handler)
│   ├── logout.php                (Session destroyer)
│   └── db.php                    (Database connection with Malay date formatter)
│
├── Authentication & Authorization
│   ├── auth_check.php            (Basic session validation)
│   ├── admin_auth_check.php      (Admin-only access gate)
│   ├── staff_auth_check.php      (Staff-only access gate)
│   └── check_session.php         (Session status verification)
│
├── Admin Dashboard & Management
│   ├── admin_dashboard.php       (Main admin dashboard with stats)
│   ├── admin_header.php          (Admin page template with sidebar)
│   ├── admin_footer.php
│   ├── admin_sidebar.php         (Sidebar navigation)
│   ├── admin_top_navbar.php      (Top navigation bar)
│   └── admin_profile.php         (Admin profile management)
│
├── Staff Management (Admin Side)
│   ├── admin_users.php           (User list with filtering)
│   ├── user_add.php              (Create new user)
│   ├── user_add_process.php      (User creation handler)
│   ├── user_edit.php             (User edit form)
│   ├── user_edit_process.php     (User update handler)
│   ├── user_view.php             (User detail view)
│   ├── user_delete.php           (User deletion handler)
│   └── admin_department.php      (Department management)
│
├── Product & Category Management
│   ├── admin_products.php        (Product listing with filters)
│   ├── admin_add_product.php     (Add new product form)
│   ├── admin_add_product_process.php (Product creation)
│   ├── admin_edit_product.php    (Edit product form)
│   ├── admin_edit_product_process.php (Update product)
│   ├── admin_delete_product.php  (Delete product)
│   └── admin_category.php        (Category management)
│
├── Stock Management
│   ├── admin_stock_manual.php    (Manual stock adjustment form)
│   ├── admin_stock_manual_process.php (Process stock adjustments)
│   └── [Restock/IN transactions]
│
├── Request Management (Staff Side)
│   ├── kewps8_form.php           (Request form - create new request)
│   ├── kewps8_form_process.php   (Request submission handler)
│   ├── kewps8_receipt.php        (Request receipt/confirmation)
│   ├── request_list.php          (View own requests)
│   ├── request_edit.php          (Edit pending request)
│   ├── request_edit_process.php  (Update request handler)
│   ├── request_delete.php        (Delete pending request)
│   ├── request_review.php        (Admin approval form)
│   ├── request_review_process.php (Approval/rejection handler)
│   └── request_details_ajax.php  (AJAX request details)
│
├── Request Management (Admin Side)
│   ├── manage_requests.php       (Admin's pending requests list)
│   ├── kewps8_approval.php       (Approval form)
│   ├── kewps8_approval_process.php (Approval handler)
│   ├── admin_request_details_ajax.php
│   └── print_request.php         (KEW.PS-8 printable receipt)
│
├── Reporting & Analytics
│   ├── admin_reports.php         (Main reports hub)
│   ├── report_requests.php       (Department analytics)
│   ├── report_requests_view.php  (Department report display)
│   ├── report_inventory.php      (Inventory status report)
│   ├── report_inventory_view.php (Inventory display)
│   ├── report_inventory_excel.php (Excel export)
│   ├── report_suppliers.php      (Supplier report)
│   ├── report_suppliers_view.php (Supplier display)
│   ├── kewps3_report.php         (Stock card report)
│   ├── kewps3_print.php          (Stock card print)
│   └── kewps8_print.php          (Request print)
│
├── User Profile Management
│   ├── staff_profile.php         (Staff profile form)
│   ├── admin_profile.php         (Admin profile form)
│   ├── profile_change_password.php (Change password - staff)
│   ├── profile_change_password_process.php
│   ├── change_password.php       (Password change - general)
│   ├── change_password_process.php
│   ├── forgot_password.php       (Password recovery)
│   ├── forgot_password_process.php
│   ├── reset_password.php        (Password reset)
│   ├── reset_password_process.php
│   ├── check_old_password.php    (Password validation AJAX)
│   ├── check_current_password.php (Current password check)
│   ├── upload_profile_picture.php (Profile picture upload)
│   ├── delete_profile_picture.php (Picture deletion)
│   └── staff_register.php        (New staff registration)
│
├── Staff Dashboard
│   ├── staff_dashboard.php       (Staff main dashboard)
│   ├── staff_header.php          (Staff page template)
│   ├── staff_footer.php
│   └── navbar.php                (Shared navbar)
│
├── Notifications & Integration
│   ├── telegram_helper.php       (Telegram notification functions)
│   ├── telegram_config.php       (Configuration with bot token)
│   ├── cron_monthly_reminder.php (Monthly stock reminder)
│   └── [External: Telegram Bot API]
│
├── Utilities & Helpers
│   ├── hash_generator.php        (Password hash generator)
│   ├── check_barang_columns.php  (DB structure verification)
│   ├── check_table_structure.php (Table validation)
│   ├── seed_users.php            (User seed script)
│   └── debug_reports.php         (Debug utility)
│
├── Staff-specific features
│   ├── staff/edit_request.php    (Staff side request editor)
│   └── [staff/ folder - isolated staff functionality]
│
├── Error Handling
│   ├── 404.php                   (404 error page)
│   └── [Global error handling in auth_check.php]
│
├── Assets
│   ├── assets/
│   │   ├── img/                  (Logo, backgrounds, icons)
│   │   │   ├── logo.png
│   │   │   ├── admin-logo.png
│   │   │   ├── background*.jpg   (3 slideshow backgrounds)
│   │   │   └── login-bg.jpg
│   │   └── css/                  (Cropper library)
│   │       └── cropper.min.css
│   ├── js/
│   │   └── cropper.min.js        (Image cropping library)
│   └── css/                      (Additional stylesheets)
│
├── Uploads
│   ├── uploads/
│   │   └── profile_pictures/     (User profile images)
│   └── [Dynamic folders created on demand]
│
├── Documentation
│   ├── README.md                 (Main documentation)
│   ├── DATABASE_SCHEMA_ANALYSIS.md (Complete DB schema)
│   ├── SYSTEM_ERD.md             (Entity relationships)
│   ├── SYSTEM_DFD.md             (Data flow diagrams)
│   ├── TESTING_CHECKLIST.md      (QA checklist)
│   └── COMMIT_INSTRUCTIONS.md    (Git workflow)
│
├── Configuration
│   ├── .htaccess                 (Apache rewrite rules)
│   ├── .gitignore                (Git exclusions)
│   ├── .claude/settings.local.json (Claude Code config)
│   ├── .vscode/settings.json     (VS Code config)
│   └── .git/                     (Git repository)
```

---

## 2. CORE FUNCTIONALITY & FEATURES

### A. FOR STAFF (Regular Users)

#### Request Creation Workflow
1. **Access KEW.PS-8 Form** (`kewps8_form.php`)
   - Browse available products by category
   - Add items to cart (session-based)
   - Enter optional notes
   - Real-time cart display with quantity editing

2. **Submit Request** (`kewps8_form_process.php`)
   - Transaction-based insertion
   - Creates `permohonan` header record (status = 'Baru')
   - Creates `permohonan_barang` detail records
   - Sends Telegram notification to admins
   - Session cart cleared

3. **View Own Requests** (`request_list.php`)
   - Filter by status (Baru, Diluluskan, Ditolak, Diterima)
   - Sort by date
   - View request history
   - Track approval status
   - Edit/delete pending requests only

4. **Profile Management** (`staff_profile.php`)
   - Update personal information
   - Upload/change profile picture with cropping
   - Change password
   - View department assignment

5. **Dashboard** (`staff_dashboard.php`)
   - Summary of own requests (pending/approved)
   - Quick statistics
   - Profile access
   - Navigation menu

### B. FOR ADMINISTRATORS

#### Dashboard (`admin_dashboard.php`)
- **Real-time Statistics:**
  - Pending requests count (with glowing animation if > 0)
  - Low stock items alert (with red pulsing glow)
  - Total requests this month
  - Total approved/rejected requests

- **Recent Requests Widget**
  - Display last 10 pending requests
  - Click to review each request
  - Smart time display ("X minit yang lalu" for today, formatted date for older)

- **Quick Actions:**
  - View pending requests
  - Stock management
  - User management
  - Generate reports

#### Request Approval System
1. **View Pending Requests** (`manage_requests.php`)
   - Filter by date range
   - Filter by department
   - Search by requester name/ID
   - Sort by request date
   - Quick actions: View/Approve/Reject/Delete

2. **Review & Approve** (`request_review.php` → `request_review_process.php`)
   - View request details with item breakdown
   - Current stock levels for each item
   - Set approved quantities (can be different from requested)
   - Prevent self-approval (admin ≠ requester)
   - Row-level locking to prevent race conditions
   - **On Approval:**
     - Stock automatically deducted (`barang.baki_semasa`)
     - Transaction logged (`transaksi_stok.jenis='Keluar'`)
     - Request marked 'Diluluskan'
     - Approver ID and timestamp recorded

3. **Reject Request** (`request_review_process.php`)
   - No stock changes
   - Status set to 'Ditolak'
   - Recorded with approver details

#### Product & Inventory Management
1. **Product Management** (`admin_products.php`)
   - View all products with stock levels
   - Real-time search (code, name, description)
   - Filter by category, supplier, stock status
   - Color-coded stock levels:
     - Green: Stock Sufficient (> 10 units)
     - Yellow: Low Stock (1-10 units)
     - Red: Out of Stock (0 units)
   - Actions: View, Edit, Delete

2. **Add Product** (`admin_add_product.php`)
   - Product code (no_kod)
   - Description (perihal_stok)
   - Category
   - Unit of measurement (box, unit, rim, etc.)
   - Unit price
   - Supplier name
   - Initial stock

3. **Edit Product** (`admin_edit_product.php`)
   - Update all product details
   - Current stock display (read-only)
   - Stock adjusted via stock adjustment form

4. **Manual Stock Adjustment** (`admin_stock_manual.php`)
   - Purpose: Restock, corrections, inventory adjustments
   - Select product
   - Enter quantity (IN/OUT)
   - Enter reference number (supplier doc, memo, etc.)
   - **Automatic Logging:**
     - Updates `barang.baki_semasa`
     - Creates transaction record
     - Links to reference document

5. **Category Management** (`admin_category.php`)
   - Add new product categories
   - Edit category names
   - Delete categories (if no products assigned)

#### User & Department Management
1. **User Management** (`admin_users.php`)
   - List all users (staff + admins)
   - Real-time search
   - Filter by role
   - Display: ID, Name, Email, Department, Role
   - Actions: View, Edit, Delete

2. **Add User** (`user_add.php`)
   - Staff ID (unique)
   - Full name
   - Email (unique)
   - Password (with bcrypt hashing)
   - Phone number
   - Position/Job title
   - Department assignment
   - Role (Staff/Admin) with toggle

3. **Edit User** (`user_edit.php`)
   - Update all details except password
   - Change department
   - Change role
   - Enforce unique ID/email

4. **User Profile** (`admin_profile.php`)
   - Own profile management
   - Password change
   - Profile picture upload

5. **Department Management** (`admin_department.php`)
   - Add new departments
   - Edit department names
   - View staff per department
   - Delete (with referential checks)v

#### Advanced Reporting
1. **Department Analytics** (`report_requests.php`)
   - **Top 10 Departments by Request Volume:**
     - Total requests per department
     - Breakdown: Approved/Rejected/Pending
     - Approval rate percentage
     - Color-coded status indicators

   - **Monthly Trend Chart (Top 5 Departments):**
     - Line chart showing request patterns
     - Identify busy periods

   - **Summary Statistics:**
     - Total requests in period
     - Number of active departments
     - Average requests per department

   - **Filters:**
     - Date range (from-to)
     - Product category

   - **Charts:**
     - Horizontal bar chart (top departments)
     - Stacked bar (status breakdown)
     - Line chart (monthly trends)
     - HTML summary table

2. **Inventory Report** (`report_inventory.php`)
   - Stock levels by category
   - Stock movement analysis (IN/OUT)
   - Previous month balance calculation
   - Total inventory value
   - Date range filtering
   - Category filtering
   - Excel export capability

3. **KEW.PS-3 Stock Card** (`kewps3_report.php`)
   - Individual product transaction history
   - Transaction type (IN/OUT)
   - Quantity moved
   - Balance after transaction
   - Transaction date/time
   - Reference (request ID or document)
   - Print-friendly format

4. **Supplier Report** (`report_suppliers.php`)
   - List all suppliers
   - Products per supplier
   - Total stock value by supplier
   - Payment/ordering information

#### Dashboard Features
- Real-time statistics with animated indicators
- Glowing animations for alerts (pending requests, low stock)
- Color-coded status badges
- Responsive mobile-friendly design
- Quick navigation to common tasks

---

## 3. DATABASE SCHEMA

### Tables Overview

#### 1. **staf** (Staff/Users)
- **PK:** ID_staf (VARCHAR) - Employee number
- **Columns:**
  - nama - Full name
  - emel - Email (UNIQUE)
  - kata_laluan - Hashed password (bcrypt)
  - jawatan - Job position
  - no_telefon - Phone number
  - ID_jabatan - FK to jabatan (department) - ON DELETE SET NULL
  - gambar_profil - Profile picture path
  - is_admin - Role flag (0=Staff, 1=Admin)
  - is_first_login - Force password change flag
  - created_at - Account creation timestamp

#### 2. **jabatan** (Departments)
- **PK:** ID_jabatan (INT, AUTO_INCREMENT)
- **Columns:**
  - nama_jabatan - Department name (UNIQUE)
  - created_at - Creation timestamp

#### 3. **KATEGORI** (Product Categories)
- **PK:** ID_kategori (INT, AUTO_INCREMENT)
- **Columns:**
  - nama_kategori - Category name (UNIQUE)

#### 4. **barang** (Products/Inventory)
- **PK:** no_kod (VARCHAR) - Product code
- **Columns:**
  - perihal_stok - Product description
  - ID_kategori - FK to KATEGORI - ON DELETE RESTRICT
  - kategori - Denormalized category name (for historical preservation)
  - unit_pengukuran - Unit (kotak, unit, rim, etc.)
  - harga_seunit - Unit price (DECIMAL)
  - nama_pembekal - Supplier name
  - baki_semasa - Current stock balance (INT, default 0)
  - created_at - Timestamp

**Critical:** `baki_semasa` is the single source of truth for stock levels. Updated via:
- Request approval (stock OUT) - automatically logs to transaksi_stok
- Manual adjustments (stock IN/OUT) - automatically logs to transaksi_stok

#### 5. **permohonan** (Request Headers)
- **PK:** ID_permohonan (INT, AUTO_INCREMENT)
- **Columns:**
  - tarikh_mohon - Request date
  - status - VARCHAR: 'Baru' (New) | 'Diluluskan' (Approved) | 'Ditolak' (Rejected) | 'Diterima' (Received)
  - ID_pemohon - FK to staf (requester) - ON DELETE RESTRICT
  - nama_pemohon - Denormalized requester name (for audit trail)
  - jawatan_pemohon - Denormalized requester position
  - ID_jabatan - FK to jabatan (department) - ON DELETE SET NULL
  - catatan - Request notes/remarks
  - ID_pelulus - FK to staf (approver, nullable) - ON DELETE RESTRICT
  - tarikh_lulus - Approval/rejection datetime
  - created_at - Timestamp

**Status Flow:** Baru → (Diluluskan | Ditolak) → [Optional] Diterima

#### 6. **permohonan_barang** (Request Detail Items)
- **PK:** ID (INT, AUTO_INCREMENT)
- **Columns:**
  - ID_permohonan - FK to permohonan - ON DELETE CASCADE
  - no_kod - FK to barang (product code) - ON DELETE RESTRICT
  - kuantiti_mohon - Quantity requested
  - kuantiti_lulus - Quantity approved (set during approval, default 0)

**Type:** Junction table for M:N relationship between requests and products

#### 7. **transaksi_stok** (Stock Transaction Audit Log)
- **PK:** ID_transaksi (INT, AUTO_INCREMENT)
- **Columns:**
  - no_kod - FK to barang (product) - ON DELETE RESTRICT
  - jenis_transaksi - VARCHAR: 'Masuk' (IN) | 'Keluar' (OUT)
  - kuantiti - Quantity moved
  - baki_selepas_transaksi - Balance after transaction
  - ID_rujukan_permohonan - FK to permohonan (nullable, for request-based transactions)
  - ID_pegawai - FK to staf (officer who processed) - semantic clarity
  - terima_dari_keluar_kepada - Department/unit reference
  - tarikh_transaksi - Transaction datetime
  - catatan - Transaction notes

**Purpose:** Complete audit trail of all stock movements with before/after balances
**Note:** ID_pegawai refers to the officer who processed the transaction (different from pemohon/pelulus)

### Relationships Summary
```
jabatan (1) ──────< (N) staf
jabatan (1) ──────< (N) permohonan

staf (1) ──────< (N) permohonan (as pemohon/requester)
staf (1) ──────< (N) permohonan (as pelulus/approver)
staf (1) ──────< (N) transaksi_stok (as pegawai/officer)

KATEGORI (1) ──────< (N) barang

permohonan (1) ──────< (N) permohonan_barang
barang (1) ──────< (N) permohonan_barang

permohonan (1) ──────< (N) transaksi_stok (optional, nullable)
barang (1) ──────< (N) transaksi_stok
```

### Foreign Key Constraints (Database-Level)
**Implemented:** 30 December 2025

1. `fk_barang_kategori`: barang → KATEGORI (ON DELETE RESTRICT)
2. `fk_staf_jabatan`: staf → jabatan (ON DELETE SET NULL)
3. `fk_permohonan_jabatan`: permohonan → jabatan (ON DELETE SET NULL)
4. `fk_permohonan_pemohon`: permohonan → staf (ON DELETE RESTRICT)
5. `fk_permohonan_pelulus`: permohonan → staf (ON DELETE RESTRICT)
6. `fk_pb_barang`: permohonan_barang → barang (ON DELETE RESTRICT)
7. `fk_pb_permohonan`: permohonan_barang → permohonan (ON DELETE CASCADE)
8. `fk_transaksi_stok_barang`: transaksi_stok → barang (ON DELETE RESTRICT)

---

## 4. KEY TECHNOLOGIES & FRAMEWORKS

### Backend
- **PHP 8.x** - Server-side logic
- **MySQLi** - Database interaction (prepared statements for SQL injection prevention)
- **Session Management** - PHP native sessions
- **Password Hashing** - `password_hash()` with bcrypt (PHP built-in)

### Frontend
- **Bootstrap 5.3.2** - Responsive CSS framework
- **Bootstrap Icons 1.11.3** - Icon library
- **Chart.js** - Data visualization (bar charts, line charts)
- **SweetAlert2** - Modern alert dialogs and toasts
- **Cropper.js** - Image cropping for profile pictures
- **Vanilla JavaScript** - Client-side interactions
- **AJAX** - Real-time form validation and asynchronous operations

### External Integration
- **Telegram Bot API** - Real-time notifications
- **cURL** - HTTP requests to Telegram API

### Development
- **Git** - Version control
- **VSCode** - IDE configuration included
- **Apache Rewrite Rules** - `.htaccess` for URL routing

---

## 5. AUTHENTICATION & AUTHORIZATION SYSTEM

### Login Flow
1. **Entry Point:** `login.php`
   - Form submission to `login_process.php`
   - POST-only validation

2. **Credentials Processing** (`login_process.php`)
   - Trim and validate input
   - Query `staf` table by ID_staf
   - `password_verify()` against hashed password
   - Session regeneration (security)
   - First-login detection → force password change

3. **Session Variables Set:**
   - `$_SESSION['ID_staf']` - User ID
   - `$_SESSION['nama']` - Full name
   - `$_SESSION['is_admin']` - Role flag (0 or 1)
   - `$_SESSION['is_first_login']` - First login flag

4. **Role-Based Routing:**
   - Admin → `admin_dashboard.php`
   - Staff → `staff_dashboard.php`

### Authorization Checks
- **`auth_check.php`** - Core session validation
  - Checks `$_SESSION['ID_staf']` exists
  - Loads user role into `$isAdmin`
  - Redirects to login if unauthenticated

- **`admin_auth_check.php`** - Admin-only gate
  - Requires `auth_check.php`
  - Checks `$isAdmin == 1`
  - Denies non-admins

- **`staff_auth_check.php`** - Staff-only gate
  - Requires `auth_check.php`
  - Checks `$isAdmin == 0` (implicit staff)

### Self-Approval Prevention
- In `request_review_process.php`:
  ```
  if ($id_pemohon === $id_pelulus) {
      Reject with custom error message
  }
  ```
- Prevents admins from approving their own requests
- Maintains audit trail

### Password Security
- **Hashing:** `password_hash($password, PASSWORD_DEFAULT)` (bcrypt)
- **Verification:** `password_verify($input, $hash)`
- **Force Change on First Login:**
  - `is_first_login` flag in database
  - First login redirects to `change_password.php`
- **Password Reset:**
  - Email verification (if configured)
  - Self-service password recovery
  - Real-time validation

---

## 6. REQUEST & APPROVAL WORKFLOW (Core Business Process)

### Phase 1: Request Creation
**Files:** `kewps8_form.php` → `kewps8_form_process.php`

1. Staff logs in and accesses form
2. **Build Request:**
   - Browse products by category
   - Add items to session cart (client-side cart display)
   - Edit quantities before submission
   - Enter optional notes

3. **Submit Request:**
   - POST to `kewps8_form_process.php`
   - Validate session and cart
   - **Database Transaction:**
     ```
     INSERT INTO permohonan (
       tarikh_mohon, status='Baru', ID_pemohon, nama_pemohon,
       jawatan_pemohon, ID_jabatan, catatan
     )
     // Get new ID_permohonan

     FOR EACH cart item:
       INSERT INTO permohonan_barang (
         ID_permohonan, no_kod, kuantiti_mohon, kuantiti_lulus=0
       )

     COMMIT
     ```

4. **Post-Submission:**
   - Send Telegram notification to admins
   - Clear session cart
   - Redirect to confirmation page
   - Staff can track status in `request_list.php`

### Phase 2: Admin Review
**Files:** `manage_requests.php` → `request_review.php` → `request_review_process.php`

1. **View Pending Requests:**
   ```sql
   SELECT * FROM permohonan WHERE status = 'Baru'
   ORDER BY tarikh_mohon DESC
   ```
   - Filter by date range, department
   - Search by requester
   - Click request to review

2. **Review Details:**
   - Request header info (requester, department, notes)
   - Item breakdown from `permohonan_barang`
   - Current stock levels for each item
   - Admin sets `kuantiti_lulus` for each item

3. **Approval Decision:**

   **If APPROVE:**
   - Begin transaction
   - For each item (if kuantiti_lulus > 0):
     - Lock row: `SELECT...FOR UPDATE` (prevents race conditions)
     - Check: `barang.baki_semasa >= kuantiti_lulus`
     - If insufficient: Throw exception
     - Update stock: `UPDATE barang SET baki_semasa -= kuantiti_lulus`
     - Update request: `UPDATE permohonan_barang SET kuantiti_lulus = ?`
     - Log transaction:
       ```sql
       INSERT INTO transaksi_stok (
         no_kod, jenis_transaksi='Keluar', kuantiti=kuantiti_lulus,
         baki_selepas_transaksi=(new balance),
         ID_rujukan_permohonan=ID_permohonan,
         tarikh_transaksi=NOW()
       )
       ```
   - Update request status: `SET status='Diluluskan', ID_pelulus=?, tarikh_lulus=NOW()`
   - Commit transaction
   - Redirect to confirmation

   **If REJECT:**
   - Update request: `SET status='Ditolak', ID_pelulus=?, tarikh_lulus=NOW()`
   - No stock changes
   - No transaction logs
   - Redirect to confirmation

### Phase 3: Request Receipt (Optional)
**File:** `kewps8_approval_process.php`

- Staff can mark approved request as 'Diterima' (Received)
- Used to track delivery/collection

### Data Integrity Measures
1. **Transaction Boundaries:**
   - All stock updates use transactions
   - Rollback on any error
   - Consistent state guaranteed

2. **Row-Level Locking:**
   - `SELECT...FOR UPDATE` prevents concurrent modifications
   - Ensures accurate stock checking

3. **Referential Integrity:**
   - Foreign keys maintained in application logic
   - Can add DB-level constraints for extra safety

4. **Audit Trail:**
   - Every stock change logged to `transaksi_stok`
   - Before/after balances stored
   - Request references maintained

---

## 7. STOCK MANAGEMENT SYSTEM

### Stock Movements

#### Type 1: Request Approval (Automatic)
- **Source:** Request approval process
- **Direction:** OUT ('Keluar')
- **Trigger:** Admin sets `kuantiti_lulus > 0`
- **Reference:** Links to `permohonan.ID_permohonan`
- **Logging:** Automatic transaction log

#### Type 2: Manual Adjustment
- **Source:** Admin stock management form
- **Direction:** IN ('Masuk') or OUT ('Keluar')
- **Trigger:** Manual form submission
- **Reference:** Supplier document number or memo
- **Logging:** Transaction log with custom notes
- **Use Cases:**
  - Restock from suppliers
  - Inventory corrections
  - Damage/loss adjustments
  - Physical count adjustments

### Stock Balance Calculation
```
Current Stock = Initial Stock
  + SUM(IN transactions)
  - SUM(OUT transactions)

Tracked in: barang.baki_semasa
```

### Stock Status Indicators
- **Green (✓):** > 10 units (sufficient)
- **Yellow (⚠):** 1-10 units (low stock warning)
- **Red (✗):** 0 units (out of stock)

### Reports on Stock
1. **Inventory Report:** Current levels by category
2. **Stock Movements:** IN/OUT history
3. **KEW.PS-3 Stock Card:** Individual product transaction history
4. **Dashboard Alert:** Low stock items highlighted

---

## 8. TELEGRAM INTEGRATION

### Configuration
**File:** `telegram_config.php`

```php
TELEGRAM_BOT_TOKEN      = Bot token from @BotFather
TELEGRAM_ADMIN_CHAT_IDS = Array of admin chat IDs
TELEGRAM_ENABLED        = true/false
MONTHLY_REMINDER_ENABLED = true/false
SYSTEM_BASE_URL         = http://localhost/storeroom (or production URL)
```

### Notification Functions (`telegram_helper.php`)

1. **`send_telegram_notification($message, $keyboard = null)`**
   - Sends message to all configured admin chat IDs
   - Supports inline keyboard buttons
   - Returns true if sent to at least one admin
   - Non-blocking (doesn't delay user experience)

2. **`send_new_request_notification($id_permohonan, ...)`**
   - Triggered when staff submits new request
   - Includes request ID, requester, item count
   - Adds "Log Masuk ke Sistem" button (if not localhost)
   - Formatted in Malay

3. **`send_monthly_restock_reminder()`**
   - Sends on first Tuesday of month at configured time
   - Triggered by `cron_monthly_reminder.php`
   - Reminds admins to review stock levels

### Notification Messages
- **New Request:** "🔔 PERMOHONAN BARU" with details
- **Monthly Reminder:** "📅 PERINGATAN STOK BULANAN"
- **Format:** HTML-formatted with emojis and section breaks

### Integration Points
1. Request submission (`kewps8_form_process.php`):
   ```php
   send_new_request_notification($id_permohonan, $nama_pemohon, ...);
   ```

2. Monthly cron job (`cron_monthly_reminder.php`):
   - Can be triggered by system cron scheduler
   - Checks first Tuesday condition
   - Sends monthly reminder

### Timezone Handling
- Set to 'Asia/Kuala_Lumpur' in notification functions
- Ensures timestamp consistency across notifications

### Error Handling
- Validates bot token and chat IDs before sending
- Logs failures to PHP error log
- Doesn't block application if Telegram is unavailable

---

## 9. FILE UPLOAD & PROFILE PICTURE MANAGEMENT

### Profile Picture Upload (`upload_profile_picture.php`)

**Process:**
1. User selects image via file input
2. AJAX POST with `FormData`
3. Server-side validation:
   - Session check
   - File exists check
   - Image validation via `getimagesize()`
   - Accepted formats: PNG, JPEG

4. **Image Processing:**
   - Resize and optimize using PHP GD library
   - PNG: 9-level compression, alpha channel preservation
   - JPEG: 85% quality

5. **Storage:**
   - Directory: `uploads/profile_pictures/`
   - Filename: `{ID_staf}.{ext}` (replaces old picture)
   - Auto-create directory if missing

6. **Database Update:**
   - Store path in `staf.gambar_profil`
   - Previous image deleted

7. **Response:**
   - JSON with success status and image path
   - Client displays new image immediately

**Security Measures:**
- Session validation required
- Image type validation
- File size limits (via PHP settings)
- Unique filenames per user

### Profile Picture Display
- Stored path displayed in profile pages
- Fallback to initials avatar if no picture
- Used in admin dashboard, headers, etc.

### Deletion (`delete_profile_picture.php`)
- Removes file from disk
- Clears database path
- Next display shows initials avatar

---

## 10. REPORTING & ANALYTICS

### Available Reports

#### 1. Department Analytics (`report_requests.php`)
- **Top 10 Departments by Request Volume**
  - Total requests per department
  - Approved/Rejected/Pending breakdown
  - Approval rate percentage
  - Filtering by date range and category
  - Interactive charts and tables

- **Monthly Trend Chart (Top 5 Departments)**
  - Track request patterns over time
  - Identify busy/slow periods

#### 2. Inventory Report (`report_inventory.php`)
- Current stock levels by category
- Stock movement analysis
- Previous month balance
- Total inventory value (calculated from price × quantity)
- Filtering and search capabilities
- Excel export option

#### 3. KEW.PS-3 Stock Card (`kewps3_report.php`)
- Individual product transaction history
- Complete audit trail
- Transaction details: type, quantity, balance, date
- Reference to related requests
- Print-friendly format

#### 4. Supplier Report (`report_suppliers.php`)
- Supplier listing
- Products per supplier
- Stock value by supplier
- Contact information (if available)

### Chart Types
- **Horizontal Bar Charts** - Department comparison
- **Stacked Bar Charts** - Status breakdown (approved/rejected/pending)
- **Line Charts** - Monthly trends
- **HTML Tables** - Detailed data with sorting

### Export Features
- Excel export for inventory reports
- Print-optimized formats for stock cards
- PDF export capability (via browser print)

---

## 11. USER INTERFACE & DESIGN PATTERNS

### Layout Components

#### Admin Interface
- **Sidebar Navigation** (`admin_sidebar.php`)
  - Fixed left sidebar (collapsible on mobile)
  - Grouped menu items (Dashboard, Requests, Inventory, Reports, Users, Settings)
  - Active page highlighting
  - Icons for visual clarity

- **Top Navbar** (`admin_top_navbar.php`)
  - Logo and title
  - Search/filter controls
  - User profile dropdown
  - Mobile menu toggle

- **Main Content Area**
  - Responsive grid layout
  - Card-based sections
  - Padding and spacing consistency
  - Shadow effects for depth

#### Staff Interface
- **Simplified Layout** (`staff_header.php`)
  - Cleaner navigation
  - Focus on request creation and tracking
  - Profile management

#### Landing Page (`index.php`)
- **Animated Background Slideshow**
  - 3 rotating background images
  - 15-second interval
  - Semi-transparent overlay
  - Logo and call-to-action buttons

### UI Patterns

#### Status Badges
- Color-coded badges for request status
  - Blue: Baru (New)
  - Green: Diluluskan (Approved)
  - Red: Ditolak (Rejected)
  - Gray: Diterima (Received)

#### Alert Indicators
- **Glowing Animations** for important alerts
  - Yellow glow for pending requests
  - Red glow for low stock items
  - Repeating pulse effect

#### Form Design
- Consistent Bootstrap styling
- Clear labeling
- Input validation feedback
- Success/error toast notifications
- Loading states on submit

#### Tables
- Responsive design (scrollable on mobile)
- Hover effects on rows
- Icon-based actions (View, Edit, Delete)
- Pagination or scrollable (depending on page)
- Filter/search integration

#### Filters & Search
- Real-time text search
- Dropdown filters
- Date range pickers
- "Clear Filters" button (appears when filters active)
- Filter count badge

### Responsive Design
- Mobile-first approach
- Breakpoints: 576px, 768px, 992px, 1200px
- Collapsible sidebar on mobile
- Stack layout on small screens
- Touch-friendly buttons and inputs
- Readable font sizes across devices

### Localization
- **Primary Language:** Bahasa Malaysia (Malay)
- **Interface:** All UI text in Malay
- **Database:** Column names and enums in English (best practice)
- **Dates:** Formatted with Malay month abbreviations

### Accessibility
- Semantic HTML structure
- ARIA labels where needed
- Keyboard navigation support
- Color-blind friendly indicators (icons + colors)
- Alt text on images

---

## 12. IMPLEMENTATION PATTERNS & BEST PRACTICES

### Transaction Safety
```php
$conn->begin_transaction();
try {
    // Multiple operations
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    // Error handling
}
```

### Prepared Statements (SQL Injection Prevention)
```php
$stmt = $conn->prepare("SELECT * FROM staf WHERE ID_staf = ?");
$stmt->bind_param("s", $id_staf);
$stmt->execute();
```

### Session Management
```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Session variables checked before use
```

### Error Handling
- Try-catch blocks for database operations
- Graceful error messages in Malay
- Logging to error log (not displayed to users)
- Redirect on critical errors

### Code Organization
- Separation of concerns (business logic vs. presentation)
- Reusable functions (e.g., date formatting, name shortening)
- Configuration files for sensitive data
- Helper files for specific features

### Security Measures
1. **SQL Injection:** Prepared statements exclusively
2. **XSS Prevention:** `htmlspecialchars()` on output, HTML purification
3. **CSRF Prevention:** POST-only forms, session validation
4. **Authentication:** Session-based with bcrypt passwords
5. **Authorization:** Role-based access checks
6. **Rate Limiting:** Can be added at web server level
7. **Input Validation:** Server-side validation on critical operations
8. **File Upload:** Image validation, safe directory permissions

### Performance Considerations
- Database: Indexes on foreign keys (recommend)
- Queries: Simple SELECT statements with JOINs where needed
- Pagination: Not heavily used (small datasets typical for government)
- Caching: Can be added via Redis/Memcached
- Assets: CDN-hosted Bootstrap and icons

---

## 13. UNIQUE IMPLEMENTATION PATTERNS

### 1. Cart-Based Request Creation
- Session-based shopping cart for requests
- Client-side cart display (no DB until submission)
- Flexibility to add/remove items before committing
- Clear cart on successful submission

### 2. Denormalized Data Strategy
- `permohonan` stores `nama_pemohon`, `jawatan_pemohon` (duplicated from `staf`)
- Preserves historical data (requester's name at time of request)
- Audit trail remains intact even if staff record changes
- Acceptable trade-off for government system

### 3. Approval Quantity Flexibility
- Admin can approve less quantity than requested
- `kuantiti_mohon` vs `kuantiti_lulus` separation
- Handles partial approvals
- Requester informed of actual approved quantity

### 4. Smart Time Display Function
```php
// For today's requests: "X minit yang lalu"
// For past requests: "08 Dis 2025" (Malay month)
smart_time_display($masa_mohon, $tarikh_mohon)
```

### 5. Multi-Admin Notification
- Single Telegram message sent to multiple admin chat IDs
- Configuration allows adding/removing admins without code change
- Non-blocking (async-like behavior in PHP)

### 6. Self-Approval Prevention Architecture
- Database-level check in approval process
- Requester ID vs Approver ID validation
- Clear error messages for users
- Maintains integrity of approval workflow

### 7. Row-Level Locking for Concurrency
```php
SELECT...FOR UPDATE // Locks row during approval
```
- Prevents race conditions on stock updates
- Ensures accurate stock deduction in high-concurrency scenarios

---

## 14. KEY FILES BY RESPONSIBILITY

### Authentication Core
- `db.php` - Database connection
- `auth_check.php` - Core session validation
- `admin_auth_check.php` - Admin gate
- `login_process.php` - Login handler
- `logout.php` - Logout

### Request Management Core
- `kewps8_form_process.php` - Request submission (transaction, notification)
- `request_review_process.php` - Approval/rejection (transaction, stock update)
- `admin_stock_manual_process.php` - Manual stock adjustment

### Data Access
- Most files use prepared statements
- `admin_products.php`, `admin_users.php` - Data retrieval
- `report_*.php` - Complex aggregation queries

### Notification
- `telegram_helper.php` - All Telegram functions
- `telegram_config.php` - Configuration
- `cron_monthly_reminder.php` - Scheduled task

### UI/Presentation
- `*_header.php` - Layout templates
- `*_form.php` - Form pages
- `admin_dashboard.php` - Dashboard with charts
- `report_*.php` - Report displays

---

## 15. DEPLOYMENT & CONFIGURATION

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with .htaccess support
- cURL extension (for Telegram)
- GD library (for image processing)

### Environment Setup
1. **Database Connection** (`db.php`)
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "storeroom_db";
   ```

2. **Telegram Configuration** (`telegram_config.php`)
   ```php
   define('TELEGRAM_BOT_TOKEN', 'YOUR_TOKEN_HERE');
   define('TELEGRAM_ADMIN_CHAT_IDS', [...]);
   define('SYSTEM_BASE_URL', 'http://yourdomain.com/storeroom');
   ```

3. **Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/profile_pictures/
   ```

4. **Web Server Configuration**
   - `.htaccess` handles URL routing
   - Ensure `mod_rewrite` is enabled

### Database Initialization
- Import SQL dump (if provided)
- Create 7 core tables with structure as documented
- Seed default admin/staff accounts
- Insert sample departments, categories, products

### Cron Job Setup
- Optional: Schedule `cron_monthly_reminder.php`
- Runs monthly on first Tuesday at specified time
- Sends Telegram reminder to admins

---

## 16. TESTING CHECKLIST

Key areas to verify:
1. **Authentication:** Login, logout, first-login password change, password reset
2. **Request Workflow:** Create, submit, view, approve, reject
3. **Stock Management:** Deduction on approval, manual adjustments, balances
4. **Reporting:** Department analytics, inventory reports, stock cards
5. **User Management:** Add, edit, delete users; role assignment
6. **Telegram:** Notification delivery, monthly reminders
7. **File Upload:** Profile picture upload, cropping, deletion
8. **Responsive Design:** Mobile, tablet, desktop views
9. **Data Integrity:** Transaction consistency, no duplicate records
10. **Security:** SQL injection, XSS, CSRF prevention

---

## 17. RECENT IMPROVEMENTS

### Database Optimization (30 December 2025)
**Major database cleanup and standardization:**
- ✅ Removed unused `produk` table (duplicate of `barang`)
- ✅ Removed unused columns:
  - `barang.lokasi_simpanan` (0 PHP uses)
  - `barang.gambar_produk` (0 PHP uses)
  - `staf.is_superadmin` (0 PHP uses)
  - `staf.peranan` (replaced by `is_admin`)
- ✅ Standardized role management on `is_admin` column only
- ✅ Implemented 8 database-level FK constraints
- ✅ Cleaned up duplicate FK constraints
- ✅ Verified data integrity (0 orphaned records)
- ✅ Updated all documentation (ERD, DFD, Schema Analysis)

**Impact:** Professional database structure optimized for clarity and maintainability

### Previous Features Implemented
- Comprehensive code comments and documentation
- Department analytics redesign in reports
- Search bar styling improvements
- Telegram timezone sync
- Clear Filters UI enhancement
- Real-time search with pagination
- Password validation UX improvements
- Keyboard accessibility enhancements
- Mobile responsiveness optimization
- Malay localization completion

---

## 18. FUTURE ENHANCEMENT OPPORTUNITIES

Potential improvements:
1. **Email Notifications** - Alternative to Telegram
2. **Bulk Approval** - Approve multiple requests at once
3. **Excel/PDF Export** - Enhanced report exports
4. **Dark Mode Theme** - UI toggle
5. **Advanced Search** - Full-text search capability
6. **QR Code Integration** - Product tracking
7. **Barcode Scanning** - Inventory management enhancement
8. **API Layer** - RESTful API for mobile app
9. **Soft Deletes** - Archive instead of delete (add `deleted_at` columns)
10. **Database Triggers** - Automatic category name sync in denormalized fields

---

## 19. CRITICAL SUCCESS FACTORS

### For Developers Taking Over:

1. **Database First:**
   - Understand 7-table schema thoroughly
   - Transaction pattern in approval workflow
   - Row-level locking mechanism

2. **Request Lifecycle:**
   - Session-based cart → DB submission
   - Approval triggers stock deduction
   - Denormalized data preserves history
   - Transaction logs everything

3. **Telegram Integration:**
   - Non-blocking (doesn't affect user experience)
   - Configuration-driven (no hardcoded IDs)
   - Error logged if unavailable

4. **Security Principles:**
   - Always use prepared statements
   - Session validation on every page
   - Role checks for sensitive operations
   - Self-approval prevention

5. **User Experience:**
   - All text in Malay
   - Responsive design required
   - Clear error messages
   - Animations for alerts (but not excessive)

---

## 20. KNOWLEDGE BASE SUMMARY

**Total System Size:** ~14,460 PHP lines
**Core Files:** 90+ PHP files
**Database Tables:** 7 (cleaned and optimized)
**Database Constraints:** 8 FK constraints
**User Roles:** 2 (Admin, Staff)
**Primary Language:** Bahasa Malaysia
**Framework:** Bootstrap 5.3.2
**Database:** MySQLi with prepared statements
**External Integration:** Telegram Bot API
**Security:** Bcrypt passwords, prepared statements, session-based auth, database-level integrity
**Key Feature:** Request approval workflow with automatic stock management and complete audit trail
**Status:** Production-Ready, Cleaned & Optimized (30 Dec 2025)

---

## CONCLUSION

This is a well-structured, production-ready government inventory management system. The architecture is sound with clear separation of concerns, proper transaction handling, and comprehensive audit trails. The codebase follows best practices for security, includes good error handling, and provides an intuitive user interface for both staff and administrators.

The system has been thoroughly documented with database schema analysis, data flow diagrams, and testing checklists already in place. Future developers should focus on understanding the request approval workflow, transaction patterns, and the denormalization strategy used for audit trails.

**Ready for:** Production deployment, feature enhancements, maintenance, and handoff to other development teams.
