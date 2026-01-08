# Business Rules Documentation
**Sistem Pengurusan Bilik Stor dan Inventori - MPK**

**Date:** 30 December 2025 | **Last Updated:** 7 January 2026
**Status:** Production-Ready, Cleaned & Optimized
**Version:** 1.1

---

## Table of Contents
1. [User Management Rules](#1-user-management-rules)
2. [Authentication & Authorization Rules](#2-authentication--authorization-rules)
3. [Stock Request Rules](#3-stock-request-rules)
4. [Approval Process Rules](#4-approval-process-rules)
5. [Inventory Management Rules](#5-inventory-management-rules)
6. [Stock Transaction Rules](#6-stock-transaction-rules)
7. [Department Management Rules](#7-department-management-rules)
8. [Category Management Rules](#8-category-management-rules)
9. [Data Integrity Rules](#9-data-integrity-rules)
10. [Notification Rules](#10-notification-rules)

---

## 1. User Management Rules

### BR-UM-001: Unique Staff ID
**Rule:** Each staff member must have a unique ID (ID_staf)
- **Type:** Constraint
- **Enforcement:** Database UNIQUE constraint on `staf.ID_staf`
- **Impact:** HIGH
- **Rationale:** Prevents duplicate user accounts and ensures accurate identification

### BR-UM-002: Unique Email Address
**Rule:** Each staff member must have a unique email address
- **Type:** Constraint
- **Enforcement:** Database UNIQUE constraint on `staf.emel`
- **Impact:** HIGH
- **Rationale:** Enables password recovery and prevents account conflicts

### BR-UM-003: Role Assignment
**Rule:** Every user must be assigned exactly ONE role (Admin or Staff)
- **Type:** Mandatory
- **Enforcement:** `staf.is_admin` NOT NULL, DEFAULT 0
- **Values:**
  - `0` = Staff (regular user)
  - `1` = Admin (administrator)
- **Impact:** HIGH
- **Rationale:** Determines access rights and system capabilities

### BR-UM-004: Department Assignment
**Rule:** Staff members SHOULD be assigned to a department, but it's optional
- **Type:** Optional
- **Enforcement:** `staf.ID_jabatan` nullable, FK with ON DELETE SET NULL
- **Impact:** MEDIUM
- **Rationale:** Admin users may not belong to specific departments

### BR-UM-005: First Login Password Change
**Rule:** Users marked with `is_first_login = 1` MUST change password on first login
- **Type:** Mandatory
- **Enforcement:** Application logic in login_process.php
- **Impact:** HIGH
- **Rationale:** Security - ensures users set their own passwords

### BR-UM-006: Password Hashing
**Rule:** All passwords MUST be hashed using bcrypt before storage
- **Type:** Security
- **Enforcement:** Application logic using `password_hash()`
- **Impact:** CRITICAL
- **Rationale:** Protects user credentials from exposure

### BR-UM-007: Staff Deletion Restriction
**Rule:** Cannot delete staff members who have created requests or approved requests
- **Type:** Constraint
- **Enforcement:** FK constraints with ON DELETE RESTRICT
  - `fk_permohonan_pemohon`: Prevents deletion if staff has requests as pemohon
  - `fk_permohonan_pelulus`: Prevents deletion if staff has approvals as pelulus
- **Impact:** HIGH
- **Rationale:** Maintains historical audit trail integrity

---

## 2. Authentication & Authorization Rules

### BR-AA-001: Session-Based Authentication
**Rule:** All authenticated pages require active session with valid `$_SESSION['ID_staf']`
- **Type:** Security
- **Enforcement:** `auth_check.php` included on all protected pages
- **Impact:** CRITICAL
- **Rationale:** Prevents unauthorized access

### BR-AA-002: Role-Based Access Control
**Rule:** Admin-only pages require `$_SESSION['is_admin'] == 1`
- **Type:** Authorization
- **Enforcement:** `admin_auth_check.php`
- **Protected Areas:**
  - Product management (add/edit/delete)
  - Request approval
  - User management
  - Department management
  - Reports access
  - Manual stock adjustments
- **Impact:** CRITICAL
- **Rationale:** Separates admin and staff capabilities

### BR-AA-003: Staff-Only Pages
**Rule:** Staff-specific pages reject admin access (e.g., request receipt confirmation)
- **Type:** Authorization
- **Enforcement:** `staff_auth_check.php`
- **Impact:** MEDIUM
- **Rationale:** Enforces workflow separation

### BR-AA-004: Session Timeout
**Rule:** Sessions expire after inactivity period (PHP default or configured timeout)
- **Type:** Security
- **Enforcement:** PHP session management
- **Impact:** MEDIUM
- **Rationale:** Prevents session hijacking and unauthorized access

---

## 3. Stock Request Rules

### BR-SR-001: Staff Can Create Requests
**Rule:** Any authenticated staff member (admin or non-admin) can create stock requests
- **Type:** Permission
- **Enforcement:** Application logic
- **Impact:** HIGH
- **Rationale:** All staff may need inventory items

### BR-SR-002: Request Must Contain Items
**Rule:** A stock request MUST contain at least one product item
- **Type:** Validation
- **Enforcement:** Application logic in `kewps8_form_process.php`
- **Impact:** MEDIUM
- **Rationale:** Empty requests serve no purpose

### BR-SR-003: Initial Request Status
**Rule:** All new requests MUST start with status 'Baru' (New)
- **Type:** Mandatory
- **Enforcement:** Application logic sets default status
- **Status Flow:** Baru → Diluluskan/Ditolak → [Optional] Diterima
- **Impact:** HIGH
- **Rationale:** Establishes clear workflow progression

### BR-SR-004: Requester Information Capture
**Rule:** Request MUST capture requester's name, position, and department at submission time
- **Type:** Mandatory
- **Enforcement:** Denormalized data stored in `permohonan` table
- **Captured Fields:**
  - `nama_pemohon` (name)
  - `jawatan_pemohon` (position)
  - `ID_jabatan` (department)
- **Impact:** HIGH
- **Rationale:** Historical preservation - maintains audit trail even if staff record changes

### BR-SR-005: Request Editing Restriction
**Rule:** Staff can ONLY edit their own requests with status 'Baru'
- **Type:** Authorization
- **Enforcement:** Application logic in `request_edit.php`
- **Conditions:**
  - Request status = 'Baru'
  - `ID_pemohon` matches current user's `ID_staf`
- **Impact:** HIGH
- **Rationale:** Prevents tampering with processed requests

### BR-SR-006: Request Deletion Restriction
**Rule:** Staff can ONLY delete their own requests with status 'Baru'
- **Type:** Authorization
- **Enforcement:** Application logic in `request_delete.php`
- **Impact:** HIGH
- **Rationale:** Prevents deletion of approved/rejected requests

### BR-SR-007: Automatic Notification
**Rule:** When a request is submitted, Telegram notification MUST be sent to all configured admins
- **Type:** Workflow
- **Enforcement:** Application logic in `kewps8_form_process.php`
- **Impact:** MEDIUM
- **Rationale:** Ensures timely admin awareness of pending requests

---

## 4. Approval Process Rules

### BR-AP-001: Self-Approval Prohibition
**Rule:** An admin CANNOT approve their own request
- **Type:** Business Logic
- **Enforcement:** Application logic in `request_review_process.php`
- **Validation:** `ID_pemohon !== ID_pelulus`
- **Impact:** CRITICAL
- **Rationale:** Prevents conflict of interest and maintains audit integrity

### BR-AP-002: Admin-Only Approval
**Rule:** ONLY users with `is_admin = 1` can approve or reject requests
- **Type:** Authorization
- **Enforcement:** `admin_auth_check.php`
- **Impact:** CRITICAL
- **Rationale:** Ensures proper authorization hierarchy

### BR-AP-003: Partial Approval Allowed
**Rule:** Admin can approve less quantity than requested (`kuantiti_lulus ≤ kuantiti_mohon`)
- **Type:** Business Logic
- **Enforcement:** Application logic allows admin to set `kuantiti_lulus`
- **Impact:** HIGH
- **Rationale:** Provides flexibility for limited stock situations

### BR-AP-004: Stock Availability Check
**Rule:** Before approval, system MUST verify `barang.baki_semasa >= kuantiti_lulus` for each item
- **Type:** Validation
- **Enforcement:** Row-level locking with `SELECT...FOR UPDATE`
- **Impact:** CRITICAL
- **Rationale:** Prevents overselling inventory

### BR-AP-005: Automatic Stock Deduction
**Rule:** Upon approval, stock MUST be automatically deducted from `barang.baki_semasa`
- **Type:** Workflow
- **Enforcement:** Database transaction in `request_review_process.php`
- **Formula:** `baki_semasa = baki_semasa - kuantiti_lulus`
- **Impact:** CRITICAL
- **Rationale:** Maintains accurate inventory levels

### BR-AP-006: Transaction Logging Mandatory
**Rule:** Every approval MUST create corresponding `transaksi_stok` records
- **Type:** Mandatory
- **Enforcement:** Database transaction
- **Logged Data:**
  - Product code (`no_kod`)
  - Transaction type = 'Keluar'
  - Quantity = `kuantiti_lulus`
  - Balance after transaction
  - Reference to request (`ID_rujukan_permohonan`)
  - Timestamp
- **Impact:** CRITICAL
- **Rationale:** Complete audit trail for compliance

### BR-AP-007: Approval Timestamp
**Rule:** Approval or rejection MUST record exact datetime in `tarikh_lulus`
- **Type:** Mandatory
- **Enforcement:** Application logic using NOW()
- **Impact:** HIGH
- **Rationale:** Audit trail and performance tracking

### BR-AP-008: Approver Identification
**Rule:** Approval MUST record approver's ID in `ID_pelulus`
- **Type:** Mandatory
- **Enforcement:** Application logic captures `$_SESSION['ID_staf']`
- **Impact:** CRITICAL
- **Rationale:** Accountability and audit trail

### BR-AP-009: Status Update Atomicity
**Rule:** Stock deduction, transaction logging, and status update MUST occur atomically
- **Type:** Technical
- **Enforcement:** Database transaction with rollback on error
- **Impact:** CRITICAL
- **Rationale:** Data consistency - all or nothing principle

### BR-AP-010: Rejection Does Not Affect Stock
**Rule:** Rejected requests do NOT trigger stock deduction or transaction logs
- **Type:** Business Logic
- **Enforcement:** Conditional logic in `request_review_process.php`
- **Impact:** HIGH
- **Rationale:** Stock remains unchanged for rejected requests

---

## 5. Inventory Management Rules

### BR-IM-001: Unique Product Code
**Rule:** Each product MUST have a unique product code (`no_kod`)
- **Type:** Constraint
- **Enforcement:** Database PRIMARY KEY on `barang.no_kod`
- **Impact:** CRITICAL
- **Rationale:** Unique identification for inventory tracking

### BR-IM-002: Product Category Assignment
**Rule:** Every product SHOULD be assigned to a category
- **Type:** Recommended
- **Enforcement:** FK constraint `fk_barang_kategori` with ON DELETE RESTRICT
- **Impact:** MEDIUM
- **Rationale:** Enables category-based reporting and organization

### BR-IM-003: Denormalized Category Name
**Rule:** Product stores BOTH category FK (`ID_kategori`) AND category name text (`kategori`)
- **Type:** Design Decision
- **Enforcement:** Application logic maintains both fields
- **Impact:** LOW
- **Rationale:** Historical preservation - keeps original category name even if category is renamed

### BR-IM-004: Stock Balance Accuracy
**Rule:** `baki_semasa` is the single source of truth for current stock levels
- **Type:** Data Integrity
- **Enforcement:** All stock changes go through controlled processes
- **Impact:** CRITICAL
- **Rationale:** Ensures accurate inventory reporting

### BR-IM-005: Negative Stock Prevention
**Rule:** Stock balance (`baki_semasa`) MUST NOT go negative
- **Type:** Validation
- **Enforcement:** Pre-approval stock check with row-level locking
- **Impact:** CRITICAL
- **Rationale:** Prevents overselling and maintains data integrity

### BR-IM-006: Product Deletion Restriction
**Rule:** Cannot delete products that have been requested or have transaction history
- **Type:** Constraint
- **Enforcement:** FK constraints with ON DELETE RESTRICT
  - `fk_pb_barang`: Prevents deletion if product in request items
  - `fk_transaksi_stok_barang`: Prevents deletion if product has transactions
- **Impact:** HIGH
- **Rationale:** Maintains historical data integrity

### BR-IM-007: Low Stock Alert Threshold
**Rule:** Products with `baki_semasa ≤ 10` are flagged as "low stock"
- **Type:** Business Logic
- **Enforcement:** Application logic in dashboard and reports
- **Indicators:**
  - Green: > 10 units (sufficient)
  - Yellow: 1-10 units (low stock warning)
  - Red: 0 units (out of stock)
- **Impact:** MEDIUM
- **Rationale:** Proactive inventory management

---

## 6. Stock Transaction Rules

### BR-ST-001: All Movements Logged
**Rule:** ALL stock movements (in/out) MUST be logged in `transaksi_stok` table
- **Type:** Mandatory
- **Enforcement:** Application logic in approval and manual adjustment processes
- **Impact:** CRITICAL
- **Rationale:** Complete audit trail for compliance and reconciliation

### BR-ST-002: Transaction Types
**Rule:** Every transaction MUST be classified as either 'Masuk' (IN) or 'Keluar' (OUT)
- **Type:** Constraint
- **Enforcement:** Application logic validates transaction type
- **Values:**
  - 'Masuk': Stock received (restock, returns, corrections)
  - 'Keluar': Stock issued (approved requests, losses, adjustments)
- **Impact:** HIGH
- **Rationale:** Clear categorization for reporting

### BR-ST-003: Balance After Transaction
**Rule:** Every transaction MUST record the balance after the transaction (`baki_selepas_transaksi`)
- **Type:** Mandatory
- **Enforcement:** Application logic calculates and stores balance
- **Formula:**
  - For 'Masuk': `baki_selepas_transaksi = baki_semasa + kuantiti`
  - For 'Keluar': `baki_selepas_transaksi = baki_semasa - kuantiti`
- **Impact:** HIGH
- **Rationale:** Enables audit and reconciliation

### BR-ST-004: Request Reference Linking
**Rule:** Transactions from approved requests MUST link to request via `ID_rujukan_permohonan`
- **Type:** Mandatory (for request-based transactions)
- **Enforcement:** Application logic in approval process
- **Impact:** HIGH
- **Rationale:** Traces stock movements back to source requests

### BR-ST-005: Manual Transaction Reference
**Rule:** Manual stock adjustments SHOULD include reference information in `catatan` or `terima_dari_keluar_kepada`
- **Type:** Recommended
- **Enforcement:** Application provides input fields
- **Examples:** Supplier invoice number, memo reference, correction reason
- **Impact:** MEDIUM
- **Rationale:** Documentation for manual adjustments

### BR-ST-006: Officer Identification
**Rule:** Every transaction MUST record the officer who processed it (`ID_pegawai`)
- **Type:** Mandatory
- **Enforcement:** Application logic captures `$_SESSION['ID_staf']`
- **Impact:** HIGH
- **Rationale:** Accountability - distinguishes processing officer from requester/approver

### BR-ST-007: Transaction Immutability
**Rule:** Once created, transaction records CANNOT be edited or deleted
- **Type:** Business Policy
- **Enforcement:** No edit/delete functionality in application
- **Impact:** CRITICAL
- **Rationale:** Audit trail integrity - corrections done via new transactions

---

## 7. Department Management Rules

### BR-DM-001: Unique Department Name
**Rule:** Each department MUST have a unique name (`nama_jabatan`)
- **Type:** Constraint
- **Enforcement:** Database UNIQUE constraint on `jabatan.nama_jabatan`
- **Impact:** HIGH
- **Rationale:** Prevents duplicate department entries

### BR-DM-002: Department Deletion Policy
**Rule:** Deleting a department sets `ID_jabatan` to NULL for associated staff and requests
- **Type:** Cascade Behavior
- **Enforcement:** FK constraints with ON DELETE SET NULL
  - `fk_staf_jabatan`: Staff becomes unassigned
  - `fk_permohonan_jabatan`: Request department becomes null (historical data preserved)
- **Impact:** MEDIUM
- **Rationale:** Preserves staff and request records while removing department reference

### BR-DM-003: Department in Reports
**Rule:** Reports MUST handle null departments gracefully (show as "Tiada Jabatan" or "N/A")
- **Type:** Display Logic
- **Enforcement:** Application logic in reporting modules
- **Impact:** LOW
- **Rationale:** User-friendly display of unassigned items

---

## 8. Category Management Rules

### BR-CM-001: Unique Category Name
**Rule:** Each category MUST have a unique name (`nama_kategori`)
- **Type:** Constraint
- **Enforcement:** Database UNIQUE constraint on `KATEGORI.nama_kategori`
- **Impact:** HIGH
- **Rationale:** Prevents duplicate category entries

### BR-CM-002: Category Deletion Restriction
**Rule:** Cannot delete categories that have products assigned
- **Type:** Constraint
- **Enforcement:** FK constraint `fk_barang_kategori` with ON DELETE RESTRICT
- **Impact:** MEDIUM
- **Rationale:** Prevents orphaning products

### BR-CM-003: Category Usage in Reporting
**Rule:** Reports SHOULD allow filtering by category
- **Type:** Functional Requirement
- **Enforcement:** Application logic provides category filters
- **Impact:** MEDIUM
- **Rationale:** Enables category-based analysis

---

## 9. Data Integrity Rules

### BR-DI-001: Foreign Key Enforcement
**Rule:** All foreign key relationships MUST be enforced at database level
- **Type:** Constraint
- **Enforcement:** 8 FK constraints implemented (see DATABASE_SCHEMA_ANALYSIS.md)
- **Impact:** CRITICAL
- **Rationale:** Prevents orphaned records and maintains referential integrity

### BR-DI-002: Transaction Atomicity
**Rule:** Multi-step operations (approval, stock adjustment) MUST use database transactions
- **Type:** Technical
- **Enforcement:** `BEGIN TRANSACTION`, `COMMIT`, `ROLLBACK` pattern
- **Impact:** CRITICAL
- **Rationale:** Ensures data consistency (all-or-nothing)

### BR-DI-003: Row-Level Locking
**Rule:** Stock updates MUST use `SELECT...FOR UPDATE` to lock rows during approval
- **Type:** Technical
- **Enforcement:** SQL query in `request_review_process.php`
- **Impact:** HIGH
- **Rationale:** Prevents race conditions in concurrent approvals

### BR-DI-004: Prepared Statements Only
**Rule:** ALL database queries MUST use prepared statements
- **Type:** Security
- **Enforcement:** Code standard - no direct SQL string concatenation
- **Impact:** CRITICAL
- **Rationale:** SQL injection prevention

### BR-DI-005: Denormalization Justification
**Rule:** Denormalized data (requester name, category name) is intentional for historical preservation
- **Type:** Design Decision
- **Enforcement:** Application maintains duplicate data
- **Examples:**
  - `permohonan.nama_pemohon` duplicates `staf.nama`
  - `barang.kategori` duplicates `KATEGORI.nama_kategori`
- **Impact:** LOW
- **Rationale:** Audit trail - preserves data as it was at time of transaction

---

## 10. Notification Rules

### BR-NF-001: New Request Notification
**Rule:** When staff submits request, Telegram notification MUST be sent to all configured admins
- **Type:** Workflow
- **Enforcement:** Application logic in `kewps8_form_process.php`
- **Configuration:** `TELEGRAM_ADMIN_CHAT_IDS` in `telegram_config.php`
- **Impact:** MEDIUM
- **Rationale:** Timely admin awareness

### BR-NF-002: Notification Non-Blocking
**Rule:** Notification failures MUST NOT block request submission
- **Type:** Technical
- **Enforcement:** Try-catch around Telegram API calls
- **Impact:** HIGH
- **Rationale:** System availability - external service failures don't affect core operations

### BR-NF-003: Monthly Reminder (Optional)
**Rule:** System CAN send monthly restock reminders on first Tuesday of each month
- **Type:** Optional Feature
- **Enforcement:** Cron job triggering `cron_monthly_reminder.php`
- **Configuration:** `MONTHLY_REMINDER_ENABLED` flag
- **Impact:** LOW
- **Rationale:** Proactive inventory management

### BR-NF-004: Notification Content
**Rule:** Notifications MUST include essential information:
- **Type:** Functional
- **New Request Content:**
  - Request ID
  - Requester name
  - Number of items
  - Timestamp
  - Link to system (if not localhost)
- **Impact:** MEDIUM
- **Rationale:** Provides actionable information to admins

---

## Business Rule Priority Matrix

| Priority | Count | Description |
|----------|-------|-------------|
| CRITICAL | 19 | Rules that MUST be enforced - system failure if violated |
| HIGH | 22 | Rules that strongly impact business operations |
| MEDIUM | 13 | Rules that support business operations |
| LOW | 4 | Rules for user experience and reporting |

---

## Rule Enforcement Summary

| Enforcement Method | Count | Examples |
|-------------------|-------|----------|
| Database Constraints | 15 | FK constraints, UNIQUE, NOT NULL, PRIMARY KEY |
| Application Logic | 28 | Validation, workflow control, authorization |
| Security Controls | 8 | Password hashing, prepared statements, session management |
| Transaction Management | 7 | Atomicity, row-level locking, rollback handling |

---

## Compliance & Audit Trail

### Audit Trail Coverage
✅ **Request Lifecycle:**
- Request creation timestamp (`permohonan.created_at`)
- Requester information (denormalized)
- Approval/rejection timestamp (`tarikh_lulus`)
- Approver identification (`ID_pelulus`)

✅ **Stock Movements:**
- Complete transaction log (`transaksi_stok`)
- Before/after balances
- Processing officer (`ID_pegawai`)
- Reference to source request
- Timestamp of every movement

✅ **User Actions:**
- Password changes logged
- Profile updates tracked
- First login detection

### Historical Data Preservation
✅ **Intentional Denormalization:**
- Requester name/position at time of request
- Category name at time of product creation
- Department information at time of request

**Rationale:** Ensures historical accuracy even if master data (staff, categories) changes

---

## Rule Validation Checklist

For developers implementing changes:

- [ ] Does this change affect any CRITICAL priority rules?
- [ ] Are all database constraints still enforced?
- [ ] Are transactions used for multi-step operations?
- [ ] Is authorization checked at both database and application level?
- [ ] Does the audit trail remain complete?
- [ ] Are foreign key cascades appropriate for the use case?
- [ ] Is denormalized data being maintained correctly?
- [ ] Are notifications non-blocking?
- [ ] Are prepared statements used for all queries?
- [ ] Is row-level locking used for concurrent stock updates?

---

## Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 30 Dec 2025 | 1.0 | Initial business rules documentation | Claude Sonnet 4.5 |

---

**Document Owner:** System Administrator
**Last Review:** 30 December 2025
**Next Review:** Quarterly or upon significant system changes
**System:** Sistem Pengurusan Bilik Stor dan Inventori - Majlis Perbandaran Kangar
