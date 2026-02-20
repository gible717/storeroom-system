# QUICK REFERENCE GUIDE
## Storeroom Management System - 5-Minute Briefing

---

## WHAT IS THIS SYSTEM?

A **government inventory management system** for Majlis Perbandaran Kangar (Perlis Municipal Council). Staff request items from storeroom, admins approve/reject, stock automatically deducted, everything logged for audit.

**Tech Stack:** PHP 8 + MySQLi + Bootstrap 5 + Chart.js + Telegram Bot API

---

## CORE WORKFLOW

```
STAFF                          ADMIN                         SYSTEM
  |                              |                              |
  ├─► Browse Products            |                              |
  ├─► Add to Cart (Session)      |                              |
  ├─► Submit Request ────────────┼─────► Telegram Notification  |
  |                              |                              |
  |                              ├─► Review Request             |
  |                              ├─► Set Approved Qty           |
  |                              ├─► APPROVE ───────────────────┼─► BEGIN TRANSACTION
  |                              |                              ├─► Lock Stock Row
  |                              |                              ├─► Check Balance
  |                              |                              ├─► Deduct Stock
  |                              |                              ├─► Log Transaction
  |                              |                              └─► COMMIT
  |                              |                              |
  ├─◄ View Status (Approved)     |                              |
```

---

## DATABASE ESSENTIALS (7 Tables)

### Core Tables
1. **staf** - Users (ID_staf, nama, emel, kata_laluan, is_admin, ID_jabatan)
2. **jabatan** - Departments (ID_jabatan, nama_jabatan)
3. **barang** - Products (no_kod, perihal_stok, **baki_semasa**, harga_seunit)
4. **KATEGORI** - Product categories (ID_kategori, nama_kategori)

### Request Tables
5. **permohonan** - Request headers (ID_permohonan, status, ID_pemohon, ID_pelulus, tarikh_lulus)
   - Status: 'Baru' → 'Diluluskan' | 'Ditolak' → 'Diterima'
   - Stores denormalized nama_pemohon, nama_pelulus (audit trail)

6. **permohonan_barang** - Request items (ID_permohonan, no_kod, kuantiti_mohon, kuantiti_lulus)

7. **transaksi_stok** - Audit log (ID_transaksi, no_kod, jenis_transaksi, kuantiti, baki_selepas_transaksi)

### Critical Field
- **`barang.baki_semasa`** = Single source of truth for stock levels

---

## KEY FILES TO KNOW

### Authentication
- `login_process.php` - Handles login, bcrypt password verification
- `auth_check.php` - Session validation (included in every protected page)
- `admin_auth_check.php` - Admin-only gate

### Request Flow
- `kewps8_form.php` - Staff request form (session cart)
- `kewps8_form_process.php` - Submits request + sends Telegram notification
- `manage_requests.php` - Admin views pending requests
- `request_review_process.php` - **CRITICAL: Approval logic with transactions**

### Stock Management
- `admin_stock_manual_process.php` - Manual stock IN/OUT adjustments
- `admin_products.php` - Product listing with stock levels

### Reports
- `report_requests.php` - Department analytics (Chart.js)
- `report_inventory.php` - Stock levels + movements
- `kewps3_report.php` - Individual product transaction history (KEW.PS-3 stock card)

### Telegram
- `telegram_config.php` - Bot token + admin chat IDs
- `telegram_helper.php` - Notification functions

---

## APPROVAL WORKFLOW (The Heart of the System)

**File:** `request_review_process.php`

```php
// Prevent self-approval
if ($id_pemohon === $id_pelulus) { reject }

$conn->begin_transaction();

foreach ($items as $item) {
    // LOCK ROW (prevent race conditions)
    SELECT baki_semasa FROM barang WHERE no_kod = ? FOR UPDATE;

    // Check sufficient stock
    if ($baki_semasa < $kuantiti_lulus) { throw exception }

    // Deduct stock
    UPDATE barang SET baki_semasa -= $kuantiti_lulus;

    // Log transaction
    INSERT INTO transaksi_stok (
        jenis_transaksi='Keluar',
        baki_selepas_transaksi = new_balance,
        ID_rujukan_permohonan = request_id
    );
}

// Mark request approved
UPDATE permohonan SET status='Diluluskan', ID_pelulus=?, tarikh_lulus=NOW();

$conn->commit();
```

**Key Features:**
- Row-level locking (`FOR UPDATE`)
- Transaction safety (rollback on error)
- Denormalized approver data (audit trail)
- Self-approval prevention

---

## SECURITY MEASURES (Hardened - February 2026)

1. **SQL Injection Prevention:** ALL queries use prepared statements
   ```php
   $stmt = $conn->prepare("SELECT * FROM staf WHERE ID_staf = ?");
   $stmt->bind_param("s", $id);
   ```

2. **CSRF Token Protection:** All forms include CSRF tokens
   ```php
   // Token generation in forms, validation on POST
   ```

3. **Content Security Policy (CSP):** Headers restrict unauthorized scripts

4. **XSS Prevention:** All output encoded with `htmlspecialchars()`

5. **Password Security:** Bcrypt hashing
   ```php
   password_hash($password, PASSWORD_DEFAULT)
   password_verify($input, $hash)
   ```

6. **Session Management:** Secure configuration
   - `$_SESSION['ID_staf']` - User ID
   - `$_SESSION['is_admin']` - 0=Staff, 1=Admin
   - httpOnly cookies, sameSite attribute

7. **File Upload:** Image validation, unique filenames per user

---

## TELEGRAM INTEGRATION

**Setup:** `telegram_config.php`
```php
TELEGRAM_BOT_TOKEN = 'your_bot_token'
TELEGRAM_ADMIN_CHAT_IDS = [123456, 789012]
```

**Notifications Sent:**
1. **New Request Submitted** → All admins notified immediately
2. **Monthly Reminder** → First Tuesday of month (via cron)

**Function:** `send_new_request_notification($id_permohonan, ...)`

**Error Handling:** Non-blocking (doesn't stop user if Telegram fails)

---

## UI/UX PATTERNS

### Admin Dashboard Features
- **Pending Requests Count** with glowing yellow animation (if > 0)
- **Low Stock Alert** with red pulsing glow (if items ≤ 10 units)
- **Interactive Charts** (Chart.js) - stock distribution, request trends
- Recent requests widget with quick action modals
- Toast notifications (SweetAlert2) for all actions
- Sortable tables with column header click sorting
- MPK favicon in browser tab

### Stock Status Colors
- 🟢 Green: > 10 units (sufficient)
- 🟡 Yellow: 1-10 units (low stock)
- 🔴 Red: 0 units (out of stock)

### Request Status Badges
- 🔵 Blue: Baru (New)
- 🟢 Green: Diluluskan (Approved)
- 🔴 Red: Ditolak (Rejected)
- ⚪ Gray: Diterima (Received)

### Language
- **100% Malay UI** (Bahasa Malaysia)
- Database columns in English (best practice)

---

## UNIQUE IMPLEMENTATION PATTERNS

1. **Session Cart** - Request items stored in `$_SESSION['cart']` until submission
2. **Denormalized Data** - `permohonan` stores requester/approver names (preserves history)
3. **Flexible Approval** - Admin can approve less than requested quantity
4. **Smart Time Display** - "5 minit yang lalu" vs "08 Dis 2025"
5. **Multi-Admin Broadcast** - Single Telegram message to multiple admins

---

## COMMON TASKS FOR NEW DEVELOPERS

### Adding a New Feature
1. Check if it affects stock → use transactions
2. Check if it needs admin approval → add to `admin_auth_check.php` pages
3. All text in Malay
4. Use Bootstrap 5 components
5. Add to appropriate section in sidebar

### Debugging Stock Issues
1. Check `transaksi_stok` table (complete audit trail)
2. Verify `baki_selepas_transaksi` matches expectations
3. Look for failed transactions (rollback logs)
4. Check `barang.baki_semasa` vs SUM of transactions

### Adding New Report
1. Create `report_[name].php` (form with filters)
2. Create `report_[name]_view.php` (display with charts)
3. Use Chart.js for visualizations
4. Add to Reports menu in `admin_sidebar.php`

---

## DEPLOYMENT CHECKLIST

1. **Database Setup**
   - Import schema (7 tables)
   - Seed admin account
   - Insert departments, categories

2. **Configuration**
   - Set DB credentials in `db.php`
   - Set Telegram token in `telegram_config.php`
   - Create `uploads/profile_pictures/` directory
   - Set permissions: `chmod 755 uploads/`

3. **Web Server**
   - Enable `mod_rewrite` for `.htaccess`
   - Set timezone: `Asia/Kuala_Lumpur`

4. **Optional**
   - Setup cron for `cron_monthly_reminder.php`

---

## TESTING QUICK HITS

### Must-Test Scenarios
1. **Login:** Admin vs Staff routing
2. **Request Flow:** Create → Submit → Approve → Check stock deducted
3. **Self-Approval:** Admin cannot approve own request (should error)
4. **Concurrent Approval:** Two admins approve same request (one should fail gracefully)
5. **Stock Depletion:** Approve request when insufficient stock (should reject)
6. **Telegram:** Submit request → Check admin receives notification
7. **Profile Picture:** Upload PNG/JPEG → Verify cropping → Check storage

---

## TROUBLESHOOTING

### Request Approval Fails
- Check stock balance: `SELECT baki_semasa FROM barang WHERE no_kod = ?`
- Check transaction logs for errors
- Verify no self-approval attempt

### Telegram Not Sending
- Check `telegram_config.php` has valid token
- Check admin chat IDs are correct
- Check error logs: `tail -f /path/to/error.log`

### Stock Mismatch
- Run audit query:
  ```sql
  SELECT b.no_kod, b.baki_semasa,
         SUM(CASE WHEN jenis='Masuk' THEN kuantiti ELSE 0 END) -
         SUM(CASE WHEN jenis='Keluar' THEN kuantiti ELSE 0 END) AS calculated
  FROM barang b
  LEFT JOIN transaksi_stok t ON b.no_kod = t.no_kod
  GROUP BY b.no_kod
  HAVING b.baki_semasa != calculated;
  ```

---

## CRITICAL REMINDERS

1. **Never skip transactions** on stock updates
2. **Always use prepared statements** (no raw SQL)
3. **All UI text in Malay** (except code comments)
4. **Test on mobile** (Bootstrap responsive design)
5. **Denormalized data is intentional** (audit trail preservation)

---

## FILE COUNT SUMMARY

- **90+ PHP files**
- **7 database tables**
- **~14,460+ lines of PHP code**
- **2 user roles** (Admin, Staff)
- **Bootstrap 5.3.2** frontend
- **Chart.js** for analytics & dashboard charts
- **SweetAlert2** for toast notifications
- **Telegram Bot API** integration
- **13 documentation files** (.md)
- **Security**: CSRF tokens, CSP headers, XSS prevention

---

## NEXT STEPS FOR OTHER CLAUDE

When starting a new conversation with this briefing:

1. **Understand the context** - This is production-ready government system
2. **Read the workflow** - Request approval is the core business logic
3. **Check the database** - 7 tables with clear relationships
4. **Review security** - All queries use prepared statements
5. **Respect patterns** - Denormalization, transactions, Telegram integration

**When suggesting improvements:**
- Maintain backward compatibility
- Keep Malay UI language
- Preserve audit trail (don't delete transaction logs)
- Test stock deduction logic thoroughly
- Consider multi-user concurrency

---


**Last Updated:** February 2026
**Version:** 2.3
**System Status:** Production Ready
**Primary Contact:** [Your organization IT department]
