# Recent System Improvements

**Date**: 10 February 2026
**Version**: 2.3
**Status**: ✅ Completed

---

## 📋 Table of Contents

### Version 2.3 (February 2026)
5. [Comprehensive Security Hardening](#5-comprehensive-security-hardening)
6. [Data Visualization & Dashboard Charts](#6-data-visualization--dashboard-charts)
7. [Subcategory System](#7-subcategory-system)
8. [Product Photo Management](#8-product-photo-management)
9. [UI/UX Improvements](#9-uiux-improvements)
10. [MPK Favicon & Branding](#10-mpk-favicon--branding)

### Version 2.1 (January 2026)
1. [Bidirectional Remarks System](#1-bidirectional-remarks-system)
2. [Smart Jawatan Autocomplete](#2-smart-jawatan-autocomplete)
3. [Smart Telegram Notifications](#3-smart-telegram-notifications)
4. [Bug Fixes](#4-bug-fixes)

---

# Version 2.3 Improvements (February 2026)

---

## 5. Comprehensive Security Hardening

### Overview
Major security upgrade across the entire application, implementing industry best practices for web application security.

### Changes Implemented

#### CSRF Token Protection
- All forms now include CSRF tokens to prevent cross-site request forgery
- Token generation and validation on every POST request
- Session-based token storage with automatic regeneration

#### Content Security Policy (CSP) Headers
- CSP headers added to prevent unauthorized script execution
- Restricts resource loading to trusted sources only
- Mitigates XSS and data injection attacks

#### XSS Prevention
- All user-facing output encoded with `htmlspecialchars()`
- Input sanitization on all form submissions
- Safe rendering of user-generated content

#### Secure Session Configuration
- `httpOnly` flag on session cookies (prevents JavaScript access)
- `sameSite` attribute set to prevent CSRF via cookies
- Session regeneration on login to prevent fixation attacks

#### Input Validation
- Server-side validation on all endpoints
- Type checking and length validation
- Prepared statements on all database queries (already existed, verified)

### Impact
- **Files Modified**: 40+ PHP files across entire application
- **Commit**: `81927cd`

---

## 6. Data Visualization & Dashboard Charts

### Overview
Added interactive Chart.js charts to the admin dashboard and product statistics pages for better data insights.

### Features Added
- **Stock Level Distribution Chart** - Pie/doughnut chart showing product stock status breakdown (sufficient/low/out of stock)
- **Request Trend Chart** - Line/bar chart showing request volume over time
- **Department Breakdown** - Visual breakdown of requests by department
- **Product Statistics** - Enhanced product listing with visual stock indicators

### Technical Details
- Uses Chart.js library (already included in project)
- Real-time data from database queries
- Responsive charts that adapt to screen size
- Color-coded indicators matching existing UI patterns

### Impact
- **Commit**: `427a4e2`

---

## 7. Subcategory System

### Overview
Added hierarchical product organization with subcategories under existing categories, and smart photo deletion logic.

### Features Added
- **Category → Subcategory hierarchy** for better product organization
- **Subcategory filters** on product browsing pages
- **Smart photo delete** - When deleting a product, only removes the photo file if no other product references the same image

### Impact
- **Commit**: `2e3291f`

---

## 8. Product Photo Management

### Overview
Full product photo system allowing admins to upload, preview, and manage product images.

### Features Added
- **Photo upload** on add/edit product pages with preview
- **Photo delete** with confirmation
- **"Apply photo to other products"** dialog - share one photo across multiple products
- **Select all** support in photo sharing dialog
- **Green name indicator** for items that already have photos
- **Product photos on browse page** (`kewps8_browse.php`) - staff can see product images when making requests
- **Functional view button popup** in admin product listing showing photo, details, and stock status
- **Shared photo safety** - file only deleted from disk when no other product references it

### Impact
- **Commit**: `2e68e9f`
- **New directory**: `uploads/product_images/`

---

## 9. UI/UX Improvements

### Overview
Significant user interface and experience enhancements across the application.

### Features Added
- **Dynamic admin dashboard** with animated stat cards and gradient backgrounds
- **Toast notifications** (SweetAlert2) replacing standard alerts for all actions
- **Sortable tables** - click column headers to sort ascending/descending
- **Mobile-responsive stat cards** with hover lift effects
- **Quick action modals** for pending requests and stock warnings on dashboard
- **Smart glow indicators** with 5-minute timers for new requests
- **Improved profile picture UI** - fixed crop modal backdrop issues, cleaner upload interface

### Impact
- **Commit**: `28c760e`

---

## 10. MPK Favicon & Branding

### Overview
Added custom MPK (Majlis Perbandaran Kangar) branding across the application.

### Features Added
- **Custom favicon** (32x32) auto-cropped from MPK logo, displayed in all browser tabs
- **Apple touch icon** (180x180) for iOS bookmark support
- **Favicon added to all 13 standalone pages** (login, register, error pages, print pages, etc.)
- **Formal MPK letterhead** on inventory report printouts

### Files Modified
- `admin_header.php`, `staff_header.php` (covers all admin/staff pages)
- `index.php`, `login.php`, `forgot_password.php`, `reset_password.php`, `staff_register.php`
- `404.php`, `500.php`
- `kewps3_print.php`, `kewps8_print.php`, `print_request.php`, `report_inventory_view.php`

### New Assets
- `assets/img/favicon-32.png` - Browser tab favicon
- `assets/img/favicon-180.png` - Apple touch icon

### Impact
- **Commit**: `c8a2840` (favicon), `c0764e6` (letterhead)

---

# Version 2.1 Improvements (January 2026)

---

## 1. Bidirectional Remarks System

### Overview
Enhanced the remarks system to allow both staff and admin to view each other's notes, creating transparent two-way communication.

### Implementation Details

#### Fields Involved
- `permohonan.catatan` - Staff's remarks/notes (existing)
- `permohonan.catatan_admin` - Admin's approval/rejection remarks (existing)

#### Files Modified

**Staff Side:**
1. **request_list.php** (lines 441-463)
   - Modal now shows both staff's own catatan and admin's catatan_admin
   - Staff catatan labeled as "Catatan Pemohon (Anda)" in blue alert
   - Admin catatan labeled as "Catatan Pelulus" in yellow alert

2. **staff_dashboard.php** (lines 673-695)
   - Quick view modal updated to show both remarks
   - Same visual styling as request_list.php

**Admin Side:**
3. **manage_requests.php** (lines 404-426)
   - Modal shows staff's catatan and admin's own catatan_admin
   - Staff catatan labeled as "Catatan Pemohon"
   - Admin catatan labeled as "Catatan Pelulus"

### Visual Design
```
Staff View:
┌─────────────────────────────────────┐
│ Catatan Pemohon (Anda)              │
│ ℹ️ [Blue Alert]                     │
│ Staff's own remarks here...         │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ Catatan Pelulus                     │
│ ⚠️ [Yellow Alert]                   │
│ Admin's remarks here...             │
└─────────────────────────────────────┘

Admin View:
┌─────────────────────────────────────┐
│ Catatan Pemohon                     │
│ ℹ️ [Blue Alert]                     │
│ Staff's remarks here...             │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ Catatan Pelulus                     │
│ ⚠️ [Yellow Alert]                   │
│ Admin's remarks here...             │
└─────────────────────────────────────┘
```

### Important Notes
- ✅ Remarks are **system-only** - not printed on physical KEW.PS-8 documents
- ✅ Only displays when remarks exist (progressive disclosure)
- ✅ Multi-line support with `<br>` tag conversion
- ✅ XSS protection via proper escaping

---

## 2. Smart Jawatan Autocomplete

### Overview
Intelligent autocomplete system for the jawatan (position) field that learns from user behavior and profile data.

### Database Strategy - COALESCE Logic

The system uses SQL COALESCE to intelligently select jawatan from multiple sources:

```sql
COALESCE(NULLIF(p.jawatan_pemohon, ''), pemohon.jawatan) AS jawatan_pemohon
```

**Priority:**
1. First: `permohonan.jawatan_pemohon` (what user filled in form)
2. Fallback: `staf.jawatan` (user's profile)
3. If both NULL: Show NULL/empty

### Files Modified

#### 1. **kewps8_print.php** (lines 12-15)
```php
COALESCE(NULLIF(p.jawatan_pemohon, ''), pemohon.jawatan) AS jawatan_pemohon
```
- Print page now uses smart COALESCE logic
- Prefers form input, falls back to profile

#### 2. **request_review.php** (lines 19-22)
```php
COALESCE(NULLIF(p.jawatan_pemohon, ''), s.jawatan) AS jawatan_pemohon
```
- Admin approval page shows correct jawatan
- Uses same COALESCE logic

#### 3. **get_jawatan_suggestions.php** (NEW FILE)
AJAX endpoint that provides smart suggestions:
- Fetches from user's profile (`staf.jawatan`)
- Fetches from previous 5 requests (`permohonan.jawatan_pemohon`)
- De-duplicates suggestions
- Labels sources: "(Profil Anda)" vs "(Permohonan Lepas)"

#### 4. **kewps8_form.php**

**HTML Changes (lines 106-116):**
- Added HTML5 `<datalist>` for autocomplete
- Added helpful hint text with lightbulb icon
- Added `list="jawatan_suggestions"` attribute

**JavaScript Changes (lines 238-283):**
- Loads suggestions on page load
- Restores jawatan from session (for page refresh)
- Auto-fills profile jawatan if available
- Shows suggestions in dropdown when field is focused
- Removes muted styling when user types

#### 5. **kewps8_cart_ajax.php** (line 61)
```php
$response['jawatan'] = $_SESSION['request_jawatan'] ?? '';
```
- `get` action now returns jawatan from session
- Allows restoration when user navigates back

### User Experience Flow

```
Page Load
    ↓
Fetch jawatan from session (if exists)
    ↓
Fetch suggestions from server
    ↓
Auto-fill profile jawatan (if field empty)
    ↓
User clicks field → See dropdown suggestions:
    • "Pegawai Teknologi Maklumat (Profil Anda)"
    • "Penolong Pegawai Tadbir (Permohonan Lepas)"
    • etc.
    ↓
User selects or types custom value
    ↓
Saved to session → Database → Print page
```

### Features
✅ **Profile-first**: Profile jawatan auto-fills
✅ **History-aware**: Shows recent 5 jawatan values
✅ **User-specific**: Only shows suggestions for that user's ID
✅ **No duplicates**: Removes duplicate values
✅ **Visual feedback**: Auto-filled text in muted gray
✅ **Flexible**: User can ignore and type anything
✅ **Session persistence**: Remembers value during form editing

---

## 3. Smart Telegram Notifications

### Overview
Telegram notifications now intelligently hide empty optional fields for cleaner messages.

### File Modified
**telegram_helper.php** (lines 106-117)

### Changes

**Before:**
```
🔔 PERMOHONAN BARU

📋 ID Permohonan: #42
👤 Pemohon: Muhammad Hazeeq
💼 Jawatan:
📦 Jumlah Item: 3
📅 Tarikh: 06/01/2026 15:30
📝 Catatan:

⚠️ Sila log masuk ke sistem...
```

**After (with empty fields):**
```
🔔 PERMOHONAN BARU

📋 ID Permohonan: #42
👤 Pemohon: Muhammad Hazeeq
📦 Jumlah Item: 3
📅 Tarikh: 06/01/2026 15:30

⚠️ Sila log masuk ke sistem...
```

**After (with filled fields):**
```
🔔 PERMOHONAN BARU

📋 ID Permohonan: #42
👤 Pemohon: Muhammad Hazeeq
💼 Jawatan: Pegawai IT
📦 Jumlah Item: 3
📅 Tarikh: 06/01/2026 15:30
📝 Catatan: Untuk projek sistem baru

⚠️ Sila log masuk ke sistem...
```

### Logic

```php
// Only show jawatan if not empty
if (!empty($jawatan_pemohon)) {
    $message .= "💼 Jawatan: {$jawatan_pemohon}\n";
}

// Only show catatan if not empty
if (!empty($catatan)) {
    $message .= "📝 Catatan: " . htmlspecialchars($catatan) . "\n";
}
```

### Fields Behavior
| Field | Display Rule |
|-------|-------------|
| ID Permohonan | Always show |
| Pemohon | Always show |
| **Jawatan** | **Only if not empty** |
| Jumlah Item | Always show |
| Tarikh | Always show |
| **Catatan** | **Only if not empty** |

---

## 4. Bug Fixes

### 4.1 Missing "Diluluskan" Status Badge (admin_dashboard.php)

**Issue:**
The green "Diluluskan" status badge was invisible on admin dashboard.

**Root Cause:**
Code was setting the CSS class but never echoing the HTML `<span>` element.

**Fix (lines 388-390):**
```php
if ($status === 'Diluluskan') {
    $badge_class .= ' status-diluluskan';
    echo '<span class="' . $badge_class . '">' . $status . '</span>';
}
```

**Before:** Only set `$badge_class`, no output
**After:** Sets class AND outputs the span element

---

## 📊 Summary Statistics

### Version 2.3 Changes
- **Security Hardening**: 40+ files modified with CSRF, CSP, XSS protection
- **Data Visualization**: Dashboard charts and product statistics added
- **Subcategory System**: Hierarchical product categorization
- **Product Photos**: Full photo management system with shared photo support
- **UI/UX**: Toast notifications, sortable tables, dynamic dashboard
- **Favicon**: 13 pages updated with MPK branding
- **Letterhead**: Formal MPK header on inventory reports

### Version 2.1 Changes
- **Files Created**: 2
- **Files Modified**: 9
- **Lines Added**: ~200
- **Lines Modified**: ~50

---

## 🎯 Benefits

### For Users (Staff)
✅ See admin feedback on their requests
✅ Faster form filling with smart autocomplete
✅ Consistent jawatan across requests
✅ Better transparency in approval process

### For Admins
✅ See staff's original remarks
✅ Cleaner Telegram notifications
✅ More context when reviewing requests
✅ Better communication with staff

### For System
✅ Improved data quality (COALESCE fallback)
✅ Better UX with autocomplete
✅ Reduced notification clutter
✅ More maintainable code

---

## 🔒 Security & Best Practices

### XSS Prevention
- All user input escaped with `htmlspecialchars()`
- Telegram messages use HTML mode with proper escaping
- JavaScript uses proper string replacement for newlines

### SQL Injection Prevention
- All queries use prepared statements
- COALESCE with NULLIF for safe string handling
- Proper parameter binding

### Progressive Disclosure
- Only show fields when they have data
- Reduces cognitive load
- Cleaner UI/UX

---

## 📝 Future Enhancements (Optional)

### Potential Improvements
- [ ] Email notifications (alternative to Telegram)
- [ ] Bulk approval for multiple requests
- [ ] Dark mode theme
- [ ] QR code product tracking
- [ ] Barcode scanning integration
- [ ] API layer for mobile app
- [ ] Quick approve/reject buttons in Telegram

---

**Last Updated:** 10 February 2026
**Version:** 2.3
**End of Document**
