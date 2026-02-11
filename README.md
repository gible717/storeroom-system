# InventStor - Storeroom & Inventory Management System

**Sistem Pengurusan Bilik Stor dan Inventori**
*Majlis Perbandaran Kangar, Perlis*

A full-stack web-based inventory management system designed for government storeroom operations. Streamlines the complete lifecycle of inventory requests, approvals, stock tracking, and reporting with real-time notifications and audit trails.

---

## Features

### Staff Portal
- **KEW.PS-8 Request System** - Browse product catalog with dual-row filters (category + brand), add items to cart, submit formal requests
- **Request Tracking** - View status, edit pending requests, receive approval/rejection updates
- **Bidirectional Remarks** - Two-way communication between staff and admin on each request
- **Smart Autocomplete** - Position field auto-suggests based on profile and history
- **Session Auto-save** - Form state persists across page navigations

### Admin Portal
- **Interactive Dashboard** - Real-time stat cards, Chart.js visualizations, quick action modals
- **Request Management** - Review, approve/reject with remarks, set approved quantities
- **Inventory CRUD** - Full product management with photo uploads and shared photo support
- **Hierarchical Categories** - Parent-child subcategory system with independent brand filtering on browse page
- **Manual Stock Adjustments** - Restock and correction entries with full audit logging
- **User & Department Management** - Staff accounts, roles, and organizational units
- **Advanced Reporting** - Department analytics, inventory reports with MPK letterhead, KEW.PS-3 stock cards
- **Sortable Tables** - Click any column header to sort data

### Security
- Password hashing (bcrypt via `password_hash()`)
- CSRF token protection on all forms
- Content Security Policy (CSP) headers
- XSS prevention via output encoding
- SQL injection prevention (prepared statements)
- Session timeout with idle detection (30-minute server + client-side warning)
- Rate-limited login (5 attempts per 15 minutes)
- Role-based access control (Admin vs Staff)
- Secure session cookies (httpOnly, sameSite, secure)
- Self-approval prevention
- Row-level locking for concurrent stock updates

### Integrations
- **Telegram Bot** - Real-time notifications for new requests and low stock alerts
- **SweetAlert2** - Modern confirmation dialogs and toast notifications
- **Chart.js** - Dashboard charts for stock distribution and request trends

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.x |
| **Database** | MySQL / MariaDB |
| **Frontend** | Bootstrap 5.3, vanilla JavaScript, AJAX |
| **Charts** | Chart.js |
| **Icons** | Bootstrap Icons 1.11 |
| **Notifications** | Telegram Bot API, SweetAlert2 |
| **Authentication** | Session-based with bcrypt password hashing |

---

## Project Structure

```
storeroom/
├── admin_*.php              # Admin pages and process handlers
├── staff_*.php              # Staff portal pages
├── kewps8_*.php             # KEW.PS-8 request workflow (browse, form, process)
├── kewps3_*.php             # KEW.PS-3 stock card reports
├── report_*.php             # Reporting and analytics
├── request_*.php            # Request review, edit, delete
├── login.php                # Authentication entry point
├── auth_check.php           # Session validation + timeout guard
├── admin_auth_check.php     # Admin role gate
├── db.php                   # Database connection
├── csrf.php                 # CSRF token utilities
├── telegram_helper.php      # Telegram notification functions
├── assets/
│   ├── css/                 # Stylesheets
│   └── img/                 # Logos, favicons
├── uploads/
│   ├── profile_pictures/    # Staff profile photos
│   └── product_images/      # Product photos
└── md files/                # Internal documentation (26 files)
```

---

## Database Schema

7 core tables:

| Table | Purpose |
|-------|---------|
| `staf` | User accounts with roles (Admin/Staff) |
| `jabatan` | Organizational departments |
| `KATEGORI` | Product categories with parent-child hierarchy |
| `barang` | Product/inventory master data |
| `permohonan` | Request headers |
| `permohonan_barang` | Request line items |
| `transaksi_stok` | Stock transaction audit log |

---

## Key Workflows

**Request Flow (Staff)**
```
Browse Catalog  ->  Add to Cart  ->  Submit KEW.PS-8 Form  ->  Telegram Notification  ->  Await Approval
```

**Approval Flow (Admin)**
```
View Pending  ->  Review Details  ->  Set Quantities  ->  Approve/Reject  ->  Stock Auto-Deducted  ->  Transaction Logged
```

**Stock Management (Admin)**
```
Select Product  ->  Enter Adjustment  ->  Submit  ->  Stock Updated  ->  Transaction Logged
```

---

## Reporting

- **Department Analytics** - Top departments by volume, approval rates, monthly trends
- **Inventory Reports** - Stock levels by category, movements (IN/OUT), previous balance, total value, printable with MPK letterhead
- **KEW.PS-3 Stock Card** - Individual product transaction history with running balance, date filtering, print-ready format

---

## Installation

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx
- Telegram Bot Token (optional, for notifications)

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/storeroom.git
   cd storeroom
   ```

2. **Create the database**
   ```sql
   CREATE DATABASE storeroom_db;
   ```
   Import the provided SQL schema/dump file.

3. **Configure database connection** in `db.php`:
   ```php
   $servername = "localhost";
   $username   = "root";
   $password   = "";
   $dbname     = "storeroom_db";
   ```

4. **Configure Telegram** (optional) in `telegram_helper.php`:
   - Create a bot via [@BotFather](https://t.me/botfather)
   - Add your Bot Token and Chat ID

5. **Set upload permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/profile_pictures/
   chmod 755 uploads/product_images/
   ```

6. **Access the system** at `http://localhost/storeroom/`

### Default Accounts

| Role | ID | Password |
|------|-----|----------|
| Admin | A001 | User123 |
| Staff | S001 | User123 |

> Change these passwords immediately after first login.

---

## Screenshots

| Dashboard | Product Catalog | Request Form |
|-----------|----------------|--------------|
| Interactive stat cards with charts | Grid browse with category filters | KEW.PS-8 with cart review |

---

## Language

- **Interface:** Bahasa Malaysia (Malay)
- **Code & Comments:** English
- **Database columns:** English

---

## License

Developed as a Final Year Project / Internship System for Majlis Perbandaran Kangar, Perlis.

---

*Built with PHP, MySQL, Bootstrap 5, Chart.js, and Telegram Bot API.*
