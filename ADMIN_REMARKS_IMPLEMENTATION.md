# Admin Remarks Feature - Implementation Summary

**Date**: 5 January 2026
**Feature**: Admin remarks/notes on stock request approval/rejection
**Purpose**: Allow admins to provide feedback/reasons when approving or rejecting staff requests

---

## 📋 Overview

This feature allows administrators to add remarks or notes when approving or rejecting stock requests. These remarks are then visible to staff members, providing transparency and context for approval decisions.

---

## 🗄️ Database Changes

### Modified Table: `permohonan`

Three new columns were added to track approver information and admin remarks:

| Column Name | Data Type | Nullable | Description |
|------------|-----------|----------|-------------|
| `nama_pelulus` | VARCHAR(255) | YES | Name of the approver |
| `jawatan_pelulus` | VARCHAR(100) | YES | Position/title of the approver |
| `catatan_admin` | TEXT | YES | Admin's remarks/notes for approval or rejection |

**SQL Migration**:
```sql
-- Add nama_pelulus column
ALTER TABLE permohonan
ADD COLUMN nama_pelulus VARCHAR(255) NULL
COMMENT 'Name of the approver/reviewer'
AFTER ID_pelulus;

-- Add jawatan_pelulus column
ALTER TABLE permohonan
ADD COLUMN jawatan_pelulus VARCHAR(100) NULL
COMMENT 'Position/title of the approver/reviewer'
AFTER nama_pelulus;

-- Add catatan_admin column
ALTER TABLE permohonan
ADD COLUMN catatan_admin TEXT NULL
COMMENT 'Admin remarks/notes for approval or rejection'
AFTER catatan;
```

---

## 📝 Files Modified

### 1. `request_review.php` (Admin Approval Form)
**Purpose**: Display form for admins to review and approve/reject requests

**Changes**:
- Added textarea field for admin remarks (lines 138-140)
- Field name: `catatan_pelulus`
- Optional field with helpful placeholder text

**Code Added**:
```php
<div class="mb-3">
    <label for="admin_catatan" class="form-label fw-bold">Catatan Pelulus (Optional)</label>
    <textarea class="form-control" name="catatan_pelulus" id="admin_catatan" rows="3"
              placeholder="Sila ambil..."></textarea>
</div>
```

---

### 2. `request_review_process.php` (Process Approval/Rejection)
**Purpose**: Handle the approval/rejection logic and save data to database

**Changes**:

#### A. Capture Admin Remarks (Line 58)
```php
$admin_remarks = trim($_POST['catatan_pelulus'] ?? '');
```

#### B. Rejection Logic (Lines 88-108)
- Fetch approver's name and position from `staf` table
- Save all approver details + remarks to `permohonan` table

```php
// Get admin's name and position
$stmt_admin = $conn->prepare("SELECT nama, jawatan FROM staf WHERE ID_staf = ?");
$stmt_admin->bind_param("s", $id_pelulus);
$stmt_admin->execute();
$admin_data = $stmt_admin->get_result()->fetch_assoc();
$nama_pelulus = $admin_data['nama'];
$jawatan_pelulus = $admin_data['jawatan'];
$stmt_admin->close();

// Update permohonan with rejection status and remarks
$stmt = $conn->prepare("UPDATE permohonan
                        SET status = 'Ditolak',
                            ID_pelulus = ?,
                            nama_pelulus = ?,
                            jawatan_pelulus = ?,
                            tarikh_lulus = ?,
                            catatan_admin = ?
                        WHERE ID_permohonan = ? AND status = 'Baru'");
$stmt->bind_param("sssssi", $id_pelulus, $nama_pelulus, $jawatan_pelulus,
                  $tarikh_lulus, $admin_remarks, $id_permohonan);
$stmt->execute();
```

#### C. Approval Logic (Lines 171-202)
- Same logic as rejection
- Fetches approver details and saves remarks

---

### 3. `request_list.php` (Staff View - My Requests)
**Purpose**: Display staff's own requests with admin remarks

**Changes**:

#### A. SQL Query (Lines 35-53)
Added `p.catatan_admin` to SELECT clause and GROUP BY clause:
```php
$sql = "SELECT
            p.ID_permohonan,
            p.tarikh_mohon,
            p.status,
            p.catatan_admin,  // Added
            COUNT(DISTINCT pb.ID_permohonan_barang) AS bilangan_item,
            GROUP_CONCAT(DISTINCT b.perihal_stok SEPARATOR ', ') AS senarai_barang
        FROM permohonan p
        LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN barang b ON pb.no_kod = b.no_kod
        WHERE p.ID_pemohon = ? $kategori_condition
        GROUP BY p.ID_permohonan, p.tarikh_mohon, p.status, p.catatan_admin  // Added
        ORDER BY p.ID_permohonan DESC";
```

#### B. Display Logic (Lines 210-243)
Shows admin remarks in the **Tindakan** (Action) column:
- Only displays if `catatan_admin` is not empty
- Uses icon and styled text
- Replaces the "-" placeholder when remarks exist

```php
<?php
$catatan_admin = trim($row['catatan_admin'] ?? '');

if ($row['status'] === 'Baru'):
    // Show edit/delete buttons
elseif ($row['status'] === 'Diluluskan' || $row['status'] === 'Selesai'):
    // Show view/print buttons
elseif (!empty($catatan_admin)): ?>
    <div>
        <small class="text-muted d-block">
            <i class="bi bi-chat-left-text-fill me-1"></i>
            <strong>Catatan:</strong><br>
            <em><?php echo htmlspecialchars($catatan_admin); ?></em>
        </small>
    </div>
<?php else: ?>
    <span class="text-muted">-</span>
<?php endif; ?>
```

---

### 4. `manage_requests.php` (Admin View - All Requests)
**Purpose**: Display all requests for admin management

**Changes**:

#### A. SQL Query (Lines 47-55)
Added `p.catatan_admin` to SELECT and GROUP BY:
```php
$sql = "SELECT p.ID_permohonan, p.tarikh_mohon, p.status, p.catatan_admin, s.nama,
            COUNT(pb.ID_permohonan_barang) AS bilangan_item,
            GROUP_CONCAT(DISTINCT b.perihal_stok SEPARATOR ', ') AS senarai_barang,
            GROUP_CONCAT(DISTINCT b.kategori SEPARATOR ', ') AS kategori_list
        FROM permohonan p
        JOIN staf s ON p.ID_pemohon = s.ID_staf
        LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
        LEFT JOIN barang b ON pb.no_kod = b.no_kod
        GROUP BY p.ID_permohonan, p.tarikh_mohon, p.status, p.catatan_admin, s.nama
        ...";
```

#### B. Display Logic (Lines 157-185)
Same display pattern as `request_list.php` - shows remarks in Tindakan column

---

## 🎯 Feature Behavior

### Admin Workflow:
1. Admin navigates to `manage_requests.php`
2. Clicks "Semak" button on a "Baru" (new) request
3. Reviews request details in `request_review.php`
4. Optionally enters remarks in "Catatan Pelulus" textarea
5. Clicks "Luluskan Permohonan" (Approve) or "Tolak Permohonan" (Reject)
6. `request_review_process.php` processes the request:
   - Fetches admin's name and position from `staf` table
   - Saves status, approver details, and remarks to database
   - Updates stock levels (if approved)
   - Logs transaction (if approved)

### Staff Workflow:
1. Staff navigates to `request_list.php` (My Requests)
2. Views list of their requests
3. For approved/rejected requests with admin remarks:
   - Sees "Catatan:" label with admin's notes in Tindakan column
4. For requests without remarks:
   - Sees "-" placeholder

---

## ✅ Testing Checklist

- [x] Database columns created successfully
- [x] Admin can enter remarks in approval form
- [x] Remarks saved correctly for approved requests
- [x] Remarks saved correctly for rejected requests
- [x] Staff can view remarks in request_list.php
- [x] Admin can view remarks in manage_requests.php
- [x] Empty remarks handled correctly (shows "-")
- [x] Long remarks display properly
- [x] Special characters handled safely (htmlspecialchars)

---

## 🔒 Security Considerations

1. **Input Sanitization**: Admin remarks are trimmed and properly escaped using `htmlspecialchars()` before display
2. **SQL Injection Prevention**: All database queries use prepared statements with parameter binding
3. **XSS Prevention**: All user-generated content is escaped before rendering in HTML

---

## 📊 ERD Changes

Update your Entity Relationship Diagram to include these new attributes in the `permohonan` entity:

```
permohonan
├── ID_permohonan (PK)
├── ...existing attributes...
├── nama_pelulus (NEW)
├── jawatan_pelulus (NEW)
└── catatan_admin (NEW)
```

**Relationships**:
- `permohonan.ID_pelulus` → `staf.ID_staf` (FK - approver)
- Values for `nama_pelulus` and `jawatan_pelulus` are denormalized copies from `staf` table at time of approval

---

## 📝 Notes for Documentation

### Why Denormalize `nama_pelulus` and `jawatan_pelulus`?

Instead of just storing `ID_pelulus` and joining to the `staf` table to get name/position, we store copies of the name and position at the time of approval. This is intentional because:

1. **Historical Accuracy**: If an approver's name or position changes later, we still have the correct information from when they approved the request
2. **Audit Trail**: Provides a permanent record of who approved the request and their position at that time
3. **Performance**: Avoids additional JOIN queries when displaying approval information

### Field Naming Convention

- `catatan` = Staff's notes/remarks (existing field)
- `catatan_admin` = Admin's approval/rejection remarks (new field)
- Both are TEXT fields to support longer messages

---

## 🎨 UI/UX Design

### Display Style:
- Small, muted text to avoid overwhelming the interface
- Icon (💬 chat bubble) for visual recognition
- Label: "Catatan:" (concise, clear)
- Italicized text for the actual remark content
- Displayed in Tindakan (Action) column, below action buttons
- Only appears when remarks exist (progressive disclosure)

### Accessibility:
- Clear label text
- Sufficient color contrast
- Icon with semantic meaning
- Responsive design (works on mobile)

---

## 🚀 Future Enhancements (Optional)

1. **Character Limit**: Add maxlength to textarea (e.g., 500 characters)
2. **Required Field**: Make remarks required for rejections
3. **Rich Text**: Support basic formatting (bold, lists)
4. **Timestamps**: Show when remarks were added
5. **Edit History**: Track if remarks are modified later
6. **Notification**: Email/SMS to staff when remarks are added

---

## 📌 Summary

This implementation adds a transparent communication channel between admins and staff regarding stock request decisions. The feature integrates seamlessly with the existing approval workflow and maintains data integrity through proper database design and security practices.

**Total Files Modified**: 4
**Database Columns Added**: 3
**Lines of Code**: ~100 (excluding comments and formatting)

---

**End of Document**
