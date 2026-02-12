# Sistem Pengurusan Bilik Stor dan Inventori
**Majlis Perbandaran Kangar, Perlis**

---

## 📋 Overview

Web-based inventory management system designed for government storeroom operations. This system streamlines the process of requesting, approving, and tracking inventory items with complete audit trails and real-time notifications.

---

## ✨ Key Features

### For Staff (Staf)
- 📝 Create inventory requests (KEW.PS-8 form)
  - 🛒 **Product catalog browse** with dual-row filter (category + subcategory/brand)
  - 🤖 **Smart jawatan autocomplete** - Auto-suggests position based on profile and history
  - 💾 **Auto-save to session** - Form remembers your input if you navigate away
- 👀 View and track own requests
- ✏️ Edit pending requests before approval
- 💬 **Bidirectional remarks system** - View admin feedback AND your own notes
  - 📝 Staff remarks labeled as "Catatan Pemohon (Anda)"
  - 💼 Admin remarks labeled as "Catatan Pelulus"
- 🔔 Smart Telegram notifications (only shows filled fields)
- 👤 Manage personal profile and change password

### For Administrators
- ✅ Review and approve/reject staff requests with remarks/notes
  - 👁️ **View staff's original remarks** when reviewing
  - 💬 **Add admin remarks** for transparency
- 📊 Comprehensive dashboard with real-time statistics
  - 📈 **Interactive charts** (Chart.js) - stock distribution, request trends
  - 🎯 **Quick action modals** for pending requests and stock warnings
  - ⚡ **Smart glow indicators** with 5-minute timers for new requests
  - 📱 **Mobile-responsive stat cards** with hover effects
- 📦 Complete inventory management (CRUD operations)
  - 📷 **Product photos** with shared photo support
  - 📂 **Subcategory system** for hierarchical product organization
- 🔄 Manual stock adjustments (restock, corrections)
- 👥 User and department management
- 📈 Advanced reporting and analytics
  - Department-focused analytics with charts
  - Inventory reports with stock movements and MPK letterhead
  - KEW.PS-3 stock card reports
- 🔔 **Smart Telegram notifications** - Auto-hides empty optional fields
- 📋 Complete audit trail via transaction logs
- 🔃 **Sortable tables** - Click column headers to sort

---

## 🛠️ Technology Stack

### Backend
- **PHP** 8.x
- **MySQL/MariaDB** - Database
- **Session-based Authentication** with password hashing

### Frontend
- **Bootstrap 5.3.2** - Responsive UI framework
- **Chart.js** - Data visualization
- **Bootstrap Icons 1.11.3** - Icon library
- **JavaScript (Vanilla)** - Client-side interactions
- **AJAX** - Real-time form validation and cart management

### Integration
- **Telegram Bot API** - Real-time notifications
- **SweetAlert2** - Modern alert dialogs

---

## 📁 Project Structure

```
storeroom/
├── admin_*.php              # Admin-side pages and processes
├── staff_*.php              # Staff-side pages (limited access)
├── kewps8_*.php            # KEW.PS-8 form related files
├── kewps3_*.php            # KEW.PS-3 report related files
├── report_*.php            # Reporting and analytics pages
├── request_*.php           # Request management (review, edit, delete)
├── login.php               # Authentication entry point
├── db.php                  # Database connection
├── telegram_helper.php     # Telegram notification functions
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   └── img/               # Images, logos, and favicons
│       ├── favicon-32.png  # Browser tab favicon
│       └── favicon-180.png # Apple touch icon
├── uploads/               # User-uploaded files
│   ├── profile_pictures/  # Staff profile pictures
│   └── product_images/    # Product photos
├── md files/              # Documentation (26 markdown files)
└── 404.php, 500.php       # Error pages
```

---

## 🗄️ Database Schema

The system uses **7 core tables**:

1. **staf** - User accounts (Admin and Staff roles)
2. **jabatan** - Organizational departments
3. **KATEGORI** - Product categories
4. **barang** - Inventory/product master data
5. **permohonan** - Request headers
6. **permohonan_barang** - Request detail lines (items)
7. **transaksi_stok** - Stock transaction audit log

**Full documentation:** See [DATABASE_SCHEMA_ANALYSIS.md](DATABASE_SCHEMA_ANALYSIS.md)

---

## 🎉 What's New (Version 2.3 - February 2026)

### ✨ Latest Features

1. **🔒 Comprehensive Security Hardening**
   - CSRF token protection on all forms
   - Content Security Policy (CSP) headers
   - XSS output sanitization with `htmlspecialchars()`
   - Secure session configuration (httpOnly, sameSite)
   - Input validation and sanitization on all endpoints

2. **📊 Data Visualization & Dashboard Charts**
   - Interactive Chart.js charts on admin dashboard
   - Product statistics with stock level distribution
   - Request trend analysis and department breakdown
   - Visual stock status indicators

3. **📂 Subcategory System**
   - Hierarchical category → subcategory product organization
   - Smart photo delete (shared photo safety)
   - **Independent two-row filter** on browse page: main categories (Row 1) + subcategories/brands (Row 2)
   - Filter by brand (e.g., "HP") across all categories without selecting a main category first
   - Product cards display "Main > Sub" badge format (e.g., "Toner > HP")
   - **Category badges on request pages** - review, approval, receipt, edit, and quick-view modals show "Main > Sub" format

4. **📷 Product Photo Management**
   - Upload, preview, and delete product photos
   - "Apply photo to other products" shared photo feature
   - Product photos visible on browse/request pages
   - Smart shared photo deletion (only removes file when no other product references it)

5. **🎨 UI/UX Improvements**
   - Dynamic admin dashboard with animated stat cards
   - Toast notifications (SweetAlert2) for all actions
   - Sortable tables with column header click sorting
   - Mobile-responsive stat cards with hover effects
   - Quick action modals for pending requests and stock warnings

6. **🖼️ MPK Favicon & Branding**
   - Custom MPK logo favicon across all pages
   - Auto-cropped from source logo for optimal browser tab display
   - Apple touch icon (180x180) support
   - Formal MPK letterhead on inventory reports

7. **🔧 Previous Improvements (v2.1)**
   - Bidirectional remarks system (staff ↔ admin)
   - Smart jawatan autocomplete with history
   - Smarter Telegram notifications (auto-hide empty fields)
   - Duplicate entry handling with field validation
   - Admin request edit capability

📄 **Detailed documentation:** See [RECENT_IMPROVEMENTS.md](RECENT_IMPROVEMENTS.md)

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Telegram Bot Token (for notifications)

### Installation Steps

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd storeroom
   ```

2. **Import Database**
   - Create a new database: `storeroom_db`
   - Import the SQL dump file (if provided)
   - Or run the schema creation script

3. **Configure Database Connection**
   - Edit `db.php`:
   ```php
   $servername = "localhost";
   $username = "root";          // Your MySQL username
   $password = "";              // Your MySQL password
   $dbname = "storeroom_db";
   ```

4. **Configure Telegram Bot** (Optional but recommended)
   - Create a bot via [@BotFather](https://t.me/botfather) on Telegram
   - Get your Bot Token
   - Edit `telegram_helper.php` and add your token
   - Add your Telegram Chat ID for admin notifications

5. **Set Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/profile_pictures/
   ```

6. **Access the System**
   - Navigate to `http://localhost/storeroom/` (or your configured URL)
   - Use default credentials to login

---

## 🎨 UI/UX Features

### Unified Design System
- **Status Pills** - Consistent rounded pill badges across all pages
  - Soft pastel colors for better readability
  - Glowing animation for "Baru" (new) status with 5-minute timer
  - Applied to: Request status, stock status, user roles

### Admin Dashboard Enhancements
- **Interactive Modal Popups**
  - Pending Requests modal with filterable list
  - Stock Warning modal showing low/out-of-stock items
  - Click-to-view design for quick insights

- **Smart Visual Indicators**
  - Glow effects on stat cards for new/critical items
  - 15-minute session-based glow tracking
  - Color-coded top borders on stat cards

### Staff Features
- **Admin Remarks Visibility**
  - View admin's comments/notes on approved or rejected requests
  - Displayed directly in request list for transparency
  - Helps staff understand approval decisions

### Responsive Design
- Mobile-friendly stat cards with hover animations
- Compact layouts with optimal spacing
- Bootstrap 5 grid system for all screen sizes

---

## 👤 Default User Accounts

### Admin Account
- **ID:** A001
- **Password:** User123
- **Role:** Admin (full access)

### Staff Account
- **ID:** S001
- **Password:** User123
- **Role:** Staf (limited access)

**⚠️ IMPORTANT:** Change these passwords immediately after first login!

---

## 📊 Key Workflows

### 1. Request Creation Flow (Staff)
```
Staff Login → Create Request → Add Items to Cart → Submit →
Telegram Notification Sent → Wait for Admin Approval
```

### 2. Approval Flow (Admin)
```
Admin Login → View Pending Requests → Review Details →
Set Approved Quantities → Approve →
Stock Automatically Deducted → Transaction Logged →
Status Updated to 'Diluluskan'
```

### 3. Stock Management Flow (Admin)
```
Admin Login → Stock Management → Select Product →
Enter Adjustment Details → Submit →
Stock Updated → Transaction Logged
```

---

## 🔒 Security Features

- ✅ Password hashing using PHP `password_hash()` (bcrypt)
- ✅ Session-based authentication with secure configuration (httpOnly, sameSite)
- ✅ Role-based access control (Admin vs Staff)
- ✅ SQL injection prevention (prepared statements on all queries)
- ✅ CSRF token protection on all forms
- ✅ Content Security Policy (CSP) headers
- ✅ XSS prevention via `htmlspecialchars()` output encoding
- ✅ Input validation and sanitization on all endpoints
- ✅ Self-approval prevention (admin cannot approve own requests)
- ✅ Row-level locking for stock updates (prevents race conditions)
- ✅ Secure file upload validation (image type, size limits)

---

## 📱 Mobile Responsiveness

The system is fully responsive and works on:
- 📱 Mobile phones (Portrait & Landscape)
- 📱 Tablets
- 💻 Desktop browsers
- 🖥️ Large screens

Tested on Chrome, Firefox, Safari, and Edge.

---

## 📈 Reporting Capabilities

### 1. Department Analytics
- Top 10 departments by request volume
- Department status breakdown (approved/rejected/pending)
- Monthly trend analysis (top 5 departments)
- Approval rate tracking with color-coded indicators

### 2. Inventory Reports
- Current stock levels by category
- Stock movements (IN/OUT) within date range
- Previous month balance calculation
- Total inventory value

### 3. KEW.PS-3 Stock Card
- Individual product transaction history
- Balance after each transaction
- Date range filtering
- Print-friendly format

---

## 🔔 Telegram Integration

The system sends real-time notifications for:
- ✅ New request submissions
- 📅 Monthly stock reminder (low stock alerts)

**Setup:**
1. Create a Telegram bot via @BotFather
2. Get your Bot Token
3. Add token to `telegram_helper.php`
4. Get your Chat ID and add to admin notification settings

---

## 🎨 UI/UX Features

- Modern, clean Bootstrap 5 design
- Card-based layouts
- Responsive tables with search and filters
- Real-time form validation
- Loading states and animations
- Toast notifications (SweetAlert2)
- Icon-based actions (view, edit, delete)
- Color-coded status indicators
- Interactive charts (Chart.js)

---

## 📝 System Languages

- **Primary Interface:** Bahasa Malaysia (Malay)
- **Code Comments:** English
- **Database:** English column names

---

## 🐛 Troubleshooting

### Login Issues
- Verify database connection in `db.php`
- Check if default users exist in `staf` table
- Ensure session handling is enabled in PHP

### Stock Not Updating
- Check database transaction logs in `transaksi_stok` table
- Verify `barang.baki_semasa` field exists and is numeric
- Check browser console for JavaScript errors

### Telegram Not Working
- Verify Bot Token is correct
- Check Chat ID is set properly
- Test bot manually by sending `/start` command
- Check PHP `curl` extension is enabled

---

## 📞 Support & Contact

For issues, questions, or contributions:
- Check [DATABASE_SCHEMA_ANALYSIS.md](DATABASE_SCHEMA_ANALYSIS.md) for technical details
- Review code comments in key files:
  - `kewps8_form_process.php` - Request creation
  - `request_review_process.php` - Approval workflow
  - `admin_stock_manual_process.php` - Manual adjustments

---

## 📄 License & Credits

**Developed for:**
Majlis Perbandaran Kangar, Perlis

**Purpose:**
Final Year Project / Internship System

**Academic Year:** 2024/2025 (Semester 2)

---

## 🔄 Version History

### Current Version (2.3 - February 2026)
- ✅ Complete request workflow (create → approve → track)
- ✅ Real-time stock management
- ✅ Department-focused analytics with interactive charts
- ✅ Telegram integration
- ✅ Complete audit trail
- ✅ Mobile responsive design
- ✅ Comprehensive security hardening (CSRF, CSP, XSS)
- ✅ Product photo management with shared photos
- ✅ Subcategory system
- ✅ MPK favicon and branded reports
- ✅ Toast notifications and sortable tables

---

## 🚀 Future Enhancements (Optional)

Potential improvements if requested:
- Email notifications (in addition to Telegram)
- Bulk approval operations
- Dark mode theme
- Advanced search and filtering
- QR code for products
- Barcode scanning integration
- API layer for mobile app integration

---

**Last Updated:** February 2026

**System Version:** 2.3

**System Status:** ✅ Production Ready
