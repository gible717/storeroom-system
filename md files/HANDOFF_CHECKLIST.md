# System Handoff Checklist
**Sistem Pengurusan Bilik Stor dan Inventori - MPK**

**Date:** 10 February 2026
**Status:** Production-Ready, Version 2.3
**Version:** 2.3

---

## 📋 Purpose

This checklist ensures smooth handoff of the storeroom management system to:
- IT Department staff
- Third-party developers
- Maintenance teams
- New developers joining the project

---

## ✅ Pre-Handoff Verification

### System Status Checks

- [x] **Database is clean and optimized**
  - 7 tables (produk removed)
  - 8 FK constraints implemented
  - 0 orphaned records
  - Role management standardized on `is_admin`

- [x] **All documentation is up-to-date (26 .md files)**
  - DATABASE_SCHEMA_ANALYSIS.md (100% accurate)
  - SYSTEM_ERD.md (complete with 7 tables)
  - SYSTEM_DFD.md (data flows documented)
  - SYSTEM_BRIEFING.md (comprehensive guide)
  - BUSINESS_RULES.md (58 rules documented)
  - QUICK_REFERENCE.md (fast lookup)
  - RECENT_IMPROVEMENTS.md (v2.1-2.3 changes)
  - USER_MANUAL.md (complete user guide)
  - TESTING_CHECKLIST.md (24 test cases)

- [x] **Code is production-ready**
  - All PHP files commented
  - Standard patterns used
  - Security measures in place
  - No development/debug code in production

- [x] **Testing completed**
  - Database integrity verified
  - All features working
  - No broken functionality

---

## 📚 Documentation Review

### For New Developers: Read These First

**Priority 1 - Start Here:**
1. **README.md** (5 min read)
   - Quick overview of the system
   - Installation instructions
   - Basic usage guide

2. **QUICK_REFERENCE.md** (10 min read)
   - Fast lookup for common tasks
   - File locations
   - Common operations

3. **SYSTEM_BRIEFING.md** (30-45 min read)
   - Comprehensive system overview
   - All features explained
   - Code patterns documented
   - Best for understanding the entire system

**Priority 2 - Database Understanding:**
4. **DATABASE_SCHEMA_ANALYSIS.md** (20 min read)
   - Complete database structure
   - Table relationships
   - FK constraints
   - ERD diagrams

5. **SYSTEM_ERD.md** (15 min read)
   - Entity relationships
   - Mermaid diagrams
   - Cardinality notation

6. **SYSTEM_DFD.md** (15 min read)
   - Data flow diagrams
   - Process descriptions
   - System boundaries

**Priority 3 - Business Logic:**
7. **BUSINESS_RULES.md** (25 min read)
   - 58 business rules explained
   - Priority levels (Critical/High/Medium/Low)
   - Enforcement methods

**Priority 4 - Optimization History:**
8. **DATABASE_OPTIMIZATION_SUMMARY.md** (10 min read)
   - Recent changes made
   - Cleanup rationale
   - Verification results

9. **MIGRATION_SUMMARY.md** (5 min read)
   - Role standardization changes
   - Files modified

**Total Reading Time: ~2.5 hours for complete understanding**

---

## 🛠️ Development Environment Setup

### Step 1: Prerequisites Installation

```bash
# Required Software
✓ PHP 8.0 or higher
✓ MySQL 5.7+ or MariaDB 10.3+
✓ Apache with mod_rewrite enabled
✓ Composer (optional, for dependencies)
✓ Git (for version control)
```

**Verification Commands:**
```bash
php -v                    # Should show PHP 8.x
mysql --version           # Should show MySQL/MariaDB version
apache2 -v               # Should show Apache version
```

### Step 2: Project Setup

1. **Clone Repository:**
   ```bash
   git clone [repository-url] storeroom
   cd storeroom
   ```

2. **Database Setup:**
   ```bash
   # Create database
   mysql -u root -p
   CREATE DATABASE storeroom_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;

   # Import database structure (if provided)
   mysql -u root -p storeroom_db < database_backup.sql
   ```

3. **Configure Database Connection:**
   ```bash
   # Edit db.php
   nano db.php

   # Update these lines:
   $servername = "localhost";
   $username = "root";           # Your MySQL username
   $password = "";               # Your MySQL password
   $dbname = "storeroom_db";
   ```

4. **Configure Telegram (Optional):**
   ```bash
   # Copy template
   cp telegram_config.example.php telegram_config.php

   # Edit telegram_config.php
   nano telegram_config.php

   # Update:
   define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN');
   define('TELEGRAM_ADMIN_CHAT_IDS', [123456789]);
   ```

5. **Set Permissions:**
   ```bash
   # Linux/Mac
   chmod 755 uploads/
   chmod 755 uploads/profile_pictures/

   # Windows - ensure write permissions for:
   # - uploads folder
   # - uploads/profile_pictures folder
   ```

6. **Test Installation:**
   ```bash
   # Access in browser
   http://localhost/storeroom/

   # Default login (if seeded):
   ID: A001
   Password: password123
   ```

### Step 3: Verify Installation

- [ ] Database connection successful
- [ ] Login page loads correctly
- [ ] Can login with test account
- [ ] Dashboard displays without errors
- [ ] Can create a test request
- [ ] Can view products list
- [ ] Profile pictures upload works
- [ ] Reports generate correctly

---

## 🗂️ Key File Locations

### Core Configuration
```
db.php                          # Database connection
telegram_config.php             # Telegram settings (create from .example)
.htaccess                       # Apache rewrite rules
```

### Authentication & Authorization
```
login.php, login_process.php    # Login system
auth_check.php                  # Session validation
admin_auth_check.php            # Admin-only gate
staff_auth_check.php            # Staff-only gate
```

### Main Features
```
# Staff Request Flow
kewps8_form.php                 # Request creation form
kewps8_form_process.php         # Request submission
request_list.php                # View own requests

# Admin Approval Flow
manage_requests.php             # Pending requests list
request_review.php              # Review/approve form
request_review_process.php      # Approval handler

# Inventory Management
admin_products.php              # Product list
admin_stock_manual.php          # Manual stock adjustment

# Reports
admin_reports.php               # Reports hub
report_requests.php             # Department analytics
report_inventory.php            # Inventory report
kewps3_report.php               # Stock card (KEW.PS-3)
```

---

## 🔑 Important Credentials & Settings

### Default Admin Account (if seeded)
```
Staff ID: A001
Password: password123
Role: Admin

⚠️ IMPORTANT: Change password on first login!
```

### Telegram Bot Setup (Optional)
```
1. Message @BotFather on Telegram
2. Create new bot: /newbot
3. Copy bot token
4. Get your chat ID: message @userinfobot
5. Update telegram_config.php
```

### Database Access
```
Host: localhost
Database: storeroom_db
Default User: root
Default Password: (empty or your MySQL password)
```

---

## 📊 Database Structure Overview

### Tables (7 Total)
```
1. staf                  # Users (admin & staff)
2. jabatan               # Departments
3. barang                # Products/inventory
4. KATEGORI              # Product categories
5. permohonan            # Request headers
6. permohonan_barang     # Request items (junction table)
7. transaksi_stok        # Stock transaction log (audit trail)
```

### Key Relationships
```
Staff creates Requests (as pemohon)
Admin approves Requests (as pelulus)
Officer processes Transactions (as pegawai)
Requests contain Products (via permohonan_barang)
Approved requests generate Transactions
```

### Foreign Key Constraints (8 Total)
All FK constraints are enforced at database level with appropriate ON DELETE rules.

---

## 🔒 Security Checklist

### Before Going Live

- [ ] **Change default admin password**
- [ ] **Update database credentials in db.php**
- [ ] **Configure Telegram bot (or disable)**
- [ ] **Review .htaccess security rules**
- [ ] **Ensure HTTPS is enabled (production)**
- [ ] **Set proper file permissions (uploads folder)**
- [ ] **Disable error display in production**
  ```php
  // In db.php or index.php
  error_reporting(0);
  ini_set('display_errors', 0);
  ```
- [ ] **Review admin user list (remove test accounts)**
- [ ] **Backup database before going live**

---

## 🧪 Testing Checklist

### Functional Testing

**Authentication:**
- [ ] Staff can login
- [ ] Admin can login
- [ ] Logout works correctly
- [ ] First-time password change enforced
- [ ] Password reset works

**Staff Features:**
- [ ] Create new request (add items to cart)
- [ ] Submit request successfully
- [ ] View own requests
- [ ] Edit pending request
- [ ] Delete pending request
- [ ] View request history
- [ ] Update profile
- [ ] Upload profile picture

**Admin Features:**
- [ ] View all pending requests
- [ ] Approve request (stock deducted correctly)
- [ ] Reject request (no stock change)
- [ ] Add new product
- [ ] Edit existing product
- [ ] Manual stock adjustment (IN/OUT)
- [ ] View inventory reports
- [ ] View department analytics
- [ ] Generate KEW.PS-3 stock card
- [ ] Manage users (add/edit/delete)
- [ ] Manage departments
- [ ] Manage categories

**Business Rules:**
- [ ] Admin cannot approve own request (self-approval blocked)
- [ ] Stock cannot go negative (validation works)
- [ ] Partial approval allowed (approve less than requested)
- [ ] Transactions logged for all stock movements
- [ ] FK constraints prevent orphaned records

**Telegram Integration:**
- [ ] New request notification sent to admins
- [ ] Notification includes correct details
- [ ] System works even if Telegram fails

---

## 🚨 Common Issues & Solutions

### Issue 1: Database Connection Error
**Error:** "Connection failed: Access denied"

**Solution:**
1. Check db.php credentials
2. Verify MySQL service is running
3. Create database: `CREATE DATABASE storeroom_db;`
4. Grant permissions: `GRANT ALL ON storeroom_db.* TO 'root'@'localhost';`

---

### Issue 2: Login Redirects to Blank Page
**Error:** White screen after login

**Solution:**
1. Check session.save_path is writable
2. Verify auth_check.php exists
3. Enable error display temporarily to see error
4. Check file permissions

---

### Issue 3: Profile Picture Upload Fails
**Error:** "Failed to upload image"

**Solution:**
1. Check uploads/profile_pictures/ exists
2. Set folder permissions to 755 (Linux) or writable (Windows)
3. Verify PHP GD library installed: `php -m | grep gd`
4. Check PHP upload_max_filesize setting

---

### Issue 4: Telegram Notifications Not Sending
**Error:** No notifications received

**Solution:**
1. Verify bot token in telegram_config.php
2. Check chat ID is correct (message @userinfobot)
3. Ensure TELEGRAM_ENABLED = true
4. Test bot with: `curl -X POST "https://api.telegram.org/bot[TOKEN]/sendMessage" -d "chat_id=[CHAT_ID]&text=Test"`
5. System works even without Telegram - notifications are non-blocking

---

### Issue 5: FK Constraint Error When Deleting
**Error:** "Cannot delete or update a parent row: a foreign key constraint fails"

**Solution:**
- This is CORRECT behavior (data integrity protection)
- Example: Cannot delete staff with existing requests
- Example: Cannot delete products used in requests
- Solution: This is by design for audit trail preservation
- If deletion needed: Remove dependent records first

---

## 📞 Support Resources

### Documentation Files
```
README.md                       # Quick start guide
SYSTEM_BRIEFING.md             # Comprehensive system guide
QUICK_REFERENCE.md             # Fast lookup
BUSINESS_RULES.md              # Business logic
DATABASE_SCHEMA_ANALYSIS.md    # Database structure
TESTING_CHECKLIST.md           # QA procedures
```

### Online Resources
```
PHP Documentation:        https://www.php.net/docs.php
MySQL Documentation:      https://dev.mysql.com/doc/
Bootstrap 5:              https://getbootstrap.com/docs/5.3/
Telegram Bot API:         https://core.telegram.org/bots/api
Chart.js:                 https://www.chartjs.org/docs/
```

### Technology Stack
```
Backend:  PHP 8.x + MySQLi
Frontend: Bootstrap 5.3.2 + Vanilla JavaScript
Database: MySQL/MariaDB with FK constraints
External: Telegram Bot API (optional)
```

---

## 🎯 Quick Start for New Developer

### Day 1: Understanding
1. Read README.md (5 min)
2. Read QUICK_REFERENCE.md (10 min)
3. Skim SYSTEM_BRIEFING.md (20 min)
4. Set up development environment (30 min)
5. Test login and basic features (15 min)

**Total: ~1.5 hours to be productive**

### Day 2: Deep Dive
1. Read BUSINESS_RULES.md (25 min)
2. Read DATABASE_SCHEMA_ANALYSIS.md (20 min)
3. Review key PHP files with comments (1 hour)
4. Try creating and approving a test request (15 min)

**Total: 2 hours to understand core workflows**

### Week 1: Mastery
1. Read complete SYSTEM_BRIEFING.md
2. Review all major features
3. Understand request approval workflow
4. Study database relationships
5. Practice adding small features

**Total: Ready to maintain and extend the system**

---

## 🔄 Maintenance Tasks

### Daily (Automated)
- Database backups (set up cron/scheduled task)
- Log file rotation

### Weekly
- Review error logs
- Check low stock items
- Verify backup integrity

### Monthly
- Database optimization: `OPTIMIZE TABLE tablename;`
- Review user accounts (remove inactive)
- Update documentation if features added
- Security updates for PHP/MySQL

### Quarterly
- Full system backup (database + files)
- Performance review
- User feedback collection
- Documentation review

---

## 🚀 Extension Guidelines

### Adding a New Feature

1. **Plan First:**
   - Document the feature in BUSINESS_RULES.md
   - Update ERD/DFD if database/flow changes
   - Review similar existing features

2. **Follow Existing Patterns:**
   - Use same file naming convention
   - Follow existing code structure
   - Use prepared statements for queries
   - Include auth checks (auth_check.php)

3. **Database Changes:**
   - Always create migration script
   - Document in DATABASE_SCHEMA_ANALYSIS.md
   - Use FK constraints for integrity
   - Test with sample data first

4. **Testing:**
   - Test as both admin and staff
   - Verify security (authorization checks)
   - Test edge cases
   - Update TESTING_CHECKLIST.md

5. **Documentation:**
   - Update SYSTEM_BRIEFING.md
   - Add to QUICK_REFERENCE.md
   - Document business rules
   - Comment code thoroughly

---

## ✅ Handoff Completion Checklist

### For Outgoing Developer

- [ ] All documentation reviewed and updated
- [ ] Code comments are clear and complete
- [ ] No TODO or FIXME comments unresolved
- [ ] Database is clean (no test data in production)
- [ ] All credentials documented (or available to team)
- [ ] Backup created and verified
- [ ] Known issues documented
- [ ] Handoff meeting scheduled
- [ ] Walkthrough of key features completed
- [ ] Questions answered

### For Incoming Developer

- [ ] Documentation read (at minimum: README, QUICK_REFERENCE, SYSTEM_BRIEFING)
- [ ] Development environment set up successfully
- [ ] Can login to system
- [ ] Can create test request
- [ ] Can approve test request (as admin)
- [ ] Understand database structure
- [ ] Know where to find things (file locations)
- [ ] Have access to:
  - [ ] Database credentials
  - [ ] Server access (if applicable)
  - [ ] Telegram bot settings
  - [ ] Git repository
- [ ] Questions list prepared for outgoing developer

---

## 📝 Notes Section

### System Strengths
✅ Clean, professional database structure (7 tables, 8 FK constraints)
✅ Complete audit trail (transaksi_stok table)
✅ Comprehensive documentation (26 .md files)
✅ Comprehensive security (CSRF, CSP, XSS, bcrypt, prepared statements)
✅ Interactive data visualization (Chart.js dashboard charts)
✅ Product photo management with shared photo support
✅ Subcategory system for product organization
✅ User-friendly interface (Bootstrap 5, Malay localization)
✅ Real-time notifications (Telegram integration)
✅ Toast notifications and sortable tables
✅ MPK branding (favicon, letterhead)
✅ Standard technology stack (easy to find developers)

### Known Limitations
⚠️ No email notifications (Telegram only)
⚠️ Single language (Malay only - by design for government use)
⚠️ No mobile app (web-based only)
⚠️ Manual backup process (no automated backup GUI)

### Future Enhancement Opportunities
💡 Bulk request approval
💡 QR code product tracking
💡 Email notification alternative
💡 API layer for mobile app
💡 Dark mode theme

---

## 🎓 Learning Path

### For Junior Developers
**Week 1-2:** Understand the system
- Read all documentation
- Set up environment
- Test all features manually
- Trace request workflow in code

**Week 3-4:** Make small changes
- Fix minor UI issues
- Add field validation
- Improve error messages
- Add small reports

**Month 2:** Add features
- Add new report
- Create new form field
- Implement simple workflow

**Month 3+:** Full capability
- Database schema changes
- Complex feature implementation
- Performance optimization

### For Senior Developers
**Day 1:** Quick setup and review
- Environment setup
- Skim documentation
- Review database structure
- Understand business rules

**Week 1:** Ready to extend
- Implement new features
- Optimize performance
- Review security
- Plan enhancements

---

## 📞 Emergency Contacts

### System Issues
```
Database Issues:        Check db.php, verify MySQL running
Code Errors:            Check Apache error logs
User Cannot Login:      Verify database connection, check user exists
Stock Discrepancy:      Review transaksi_stok audit trail
```

### Escalation Path
```
Level 1: Check documentation (this file, SYSTEM_BRIEFING.md)
Level 2: Review code comments in affected file
Level 3: Check BUSINESS_RULES.md for business logic
Level 4: Contact previous developer / IT department
```

---

## ✅ Final Verification

Before considering handoff complete:

- [ ] System is running smoothly in production
- [ ] All documentation is accessible
- [ ] New developer can login and navigate system
- [ ] New developer knows how to find information
- [ ] Backup strategy is in place
- [ ] Security checklist completed
- [ ] Known issues are documented
- [ ] Emergency procedures are clear

---

**Document Version:** 2.3
**Last Updated:** 10 February 2026
**System:** Sistem Pengurusan Bilik Stor dan Inventori - Majlis Perbandaran Kangar
**Status:** Production-Ready, Version 2.3

---

**Prepared by:** Development Team
**For:** System handoff and onboarding new developers
**Estimated Onboarding Time:** 1-2 weeks for full productivity
