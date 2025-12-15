# Entity Relationship Diagram (ERD)
## Sistem Pengurusan Bilik Stor dan Inventori MPK

---

## Database Tables Overview

### 1. **staf** (Staff/User)
Primary table for all system users (both staff and admin)

**Columns:**
- `ID_staf` (VARCHAR) - PRIMARY KEY - Staff ID / Employee Number
- `nama` (VARCHAR) - Full name
- `emel` (VARCHAR) - Email address
- `kata_laluan` (VARCHAR) - Hashed password
- `jawatan` (VARCHAR) - Position/Job title
- `ID_jabatan` (INT) - FOREIGN KEY → jabatan.ID_jabatan
- `gambar_profil` (VARCHAR) - Profile picture path
- `is_admin` (TINYINT) - Admin flag (0=Staff, 1=Admin)
- `created_at` (DATETIME) - Account creation timestamp

**Relationships:**
- One staff belongs to ONE department (jabatan)
- One staff can make MANY requests (permohonan)

---

### 2. **jabatan** (Department/Unit)
Organizational departments within MPK

**Columns:**
- `ID_jabatan` (INT) - PRIMARY KEY - Department ID
- `nama_jabatan` (VARCHAR) - Department name
- `created_at` (DATETIME) - Record creation timestamp

**Relationships:**
- One department has MANY staff members
- One department can have MANY requests

---

### 3. **barang** (Products/Items/Stock)
Inventory items available in the storeroom

**Columns:**
- `no_kod` (VARCHAR) - PRIMARY KEY - Product code/SKU
- `perihal_stok` (VARCHAR) - Product description/name
- `unit_pengukuran` (VARCHAR) - Unit of measurement (e.g., "unit", "kotak", "rim")
- `harga_seunit` (DECIMAL) - Price per unit
- `nama_pembekal` (VARCHAR) - Supplier name
- `baki_semasa` (INT) - Current stock balance
- `kategori` (VARCHAR) - Product category
- `ID_kategori` (INT) - FOREIGN KEY → KATEGORI.ID_kategori (if using category table)
- `created_at` (DATETIME) - Record creation timestamp

**Relationships:**
- One product can appear in MANY request items (permohonan_barang)
- One product can have MANY stock movement records

---

### 4. **KATEGORI** (Category)
Product categories for organization

**Columns:**
- `ID_kategori` (INT) - PRIMARY KEY - Category ID
- `nama_kategori` (VARCHAR) - Category name

**Relationships:**
- One category has MANY products (barang)

---

### 5. **permohonan** (Stock Requests)
Header table for stock requests (KEW.PS-8 form)

**Columns:**
- `ID_permohonan` (INT) - PRIMARY KEY AUTO_INCREMENT - Request ID
- `tarikh_mohon` (DATE) - Request date
- `masa_mohon` (DATETIME) - Request timestamp
- `status` (ENUM: 'Baru', 'Diluluskan', 'Ditolak') - Request status
- `ID_pemohon` (VARCHAR) - FOREIGN KEY → staf.ID_staf - Requester ID
- `nama_pemohon` (VARCHAR) - Requester name (denormalized)
- `jawatan_pemohon` (VARCHAR) - Requester position (denormalized)
- `ID_jabatan` (INT) - FOREIGN KEY → jabatan.ID_jabatan - Department ID
- `catatan` (TEXT) - Notes/remarks
- `ID_pelulus` (VARCHAR) - FOREIGN KEY → staf.ID_staf - Approver ID
- `nama_pelulus` (VARCHAR) - Approver name
- `jawatan_pelulus` (VARCHAR) - Approver position
- `tarikh_lulus` (DATE) - Approval date
- `catatan_pelulus` (TEXT) - Approver notes

**Relationships:**
- One request belongs to ONE staff (pemohon/requester)
- One request belongs to ONE department
- One request has MANY request items (permohonan_barang)
- One request can be approved by ONE admin (pelulus/approver)

---

### 6. **permohonan_barang** (Request Items/Details)
Detail table for requested items (many-to-many junction table)

**Columns:**
- `ID_permohonan_barang` (INT) - PRIMARY KEY AUTO_INCREMENT - Request item ID
- `ID_permohonan` (INT) - FOREIGN KEY → permohonan.ID_permohonan - Request ID
- `no_kod` (VARCHAR) - FOREIGN KEY → barang.no_kod - Product code
- `kuantiti_mohon` (INT) - Quantity requested
- `kuantiti_lulus` (INT) - Quantity approved (nullable)

**Relationships:**
- Many request items belong to ONE request (permohonan)
- Many request items reference ONE product (barang)
- **JUNCTION TABLE** linking permohonan ↔ barang (Many-to-Many)

---

## ERD Diagram (Mermaid Syntax)

```mermaid
erDiagram
    jabatan ||--o{ staf : "employs"
    jabatan ||--o{ permohonan : "makes requests from"

    staf ||--o{ permohonan : "creates (as pemohon)"
    staf ||--o{ permohonan : "approves (as pelulus)"

    permohonan ||--o{ permohonan_barang : "contains"

    barang ||--o{ permohonan_barang : "requested in"

    KATEGORI ||--o{ barang : "categorizes"

    jabatan {
        INT ID_jabatan PK
        VARCHAR nama_jabatan
        DATETIME created_at
    }

    staf {
        VARCHAR ID_staf PK "Employee Number"
        VARCHAR nama
        VARCHAR emel
        VARCHAR kata_laluan "Hashed"
        VARCHAR jawatan
        INT ID_jabatan FK
        VARCHAR gambar_profil
        TINYINT is_admin "0=Staff 1=Admin"
        DATETIME created_at
    }

    barang {
        VARCHAR no_kod PK "Product Code"
        VARCHAR perihal_stok "Description"
        VARCHAR unit_pengukuran "Unit"
        DECIMAL harga_seunit "Price"
        VARCHAR nama_pembekal "Supplier"
        INT baki_semasa "Current Stock"
        VARCHAR kategori
        INT ID_kategori FK
        DATETIME created_at
    }

    KATEGORI {
        INT ID_kategori PK
        VARCHAR nama_kategori
    }

    permohonan {
        INT ID_permohonan PK "Auto Increment"
        DATE tarikh_mohon
        DATETIME masa_mohon
        ENUM status "Baru Diluluskan Ditolak"
        VARCHAR ID_pemohon FK "Requester"
        VARCHAR nama_pemohon
        VARCHAR jawatan_pemohon
        INT ID_jabatan FK
        TEXT catatan "Notes"
        VARCHAR ID_pelulus FK "Approver"
        VARCHAR nama_pelulus
        VARCHAR jawatan_pelulus
        DATE tarikh_lulus
        TEXT catatan_pelulus
    }

    permohonan_barang {
        INT ID_permohonan_barang PK "Auto Increment"
        INT ID_permohonan FK
        VARCHAR no_kod FK
        INT kuantiti_mohon "Qty Requested"
        INT kuantiti_lulus "Qty Approved"
    }
```

---

## Relationship Summary

### **1:N (One-to-Many) Relationships:**

1. **jabatan → staf** (1:N)
   - One department employs many staff members
   - FK: `staf.ID_jabatan` → `jabatan.ID_jabatan`

2. **jabatan → permohonan** (1:N)
   - One department makes many requests
   - FK: `permohonan.ID_jabatan` → `jabatan.ID_jabatan`

3. **staf → permohonan (as pemohon)** (1:N)
   - One staff member creates many requests
   - FK: `permohonan.ID_pemohon` → `staf.ID_staf`

4. **staf → permohonan (as pelulus)** (1:N)
   - One admin approves many requests
   - FK: `permohonan.ID_pelulus` → `staf.ID_staf`

5. **permohonan → permohonan_barang** (1:N)
   - One request contains many items
   - FK: `permohonan_barang.ID_permohonan` → `permohonan.ID_permohonan`

6. **barang → permohonan_barang** (1:N)
   - One product appears in many request items
   - FK: `permohonan_barang.no_kod` → `barang.no_kod`

7. **KATEGORI → barang** (1:N)
   - One category contains many products
   - FK: `barang.ID_kategori` → `KATEGORI.ID_kategori`

### **M:N (Many-to-Many) Relationships:**

1. **permohonan ↔ barang** (M:N)
   - Junction table: `permohonan_barang`
   - One request can have many products
   - One product can be in many requests
   - FKs:
     - `permohonan_barang.ID_permohonan` → `permohonan.ID_permohonan`
     - `permohonan_barang.no_kod` → `barang.no_kod`

---

## Database Normalization

**Current Form: 2NF (Second Normal Form)**

### Why 2NF?
- ✅ All non-key attributes depend on the primary key
- ✅ No partial dependencies exist
- ⚠️ Some denormalization exists (nama_pemohon, jawatan_pemohon stored in permohonan)

### Denormalization Reasons:
- **Historical Data Preservation**: Stores requester's name/position at time of request
- **Performance**: Avoids joins when displaying request history
- **Data Integrity**: Preserves original request details even if staff record changes

### Could be 3NF if:
- Remove `nama_pemohon`, `jawatan_pemohon` from `permohonan` (always JOIN with staf)
- Remove `nama_pelulus`, `jawatan_pelulus` from `permohonan` (always JOIN with staf)
- **Trade-off**: Performance vs strict normalization

---

## Cardinality Notation

```
jabatan (1) ──────< (N) staf
jabatan (1) ──────< (N) permohonan
staf (1) ──────< (N) permohonan (as pemohon)
staf (1) ──────< (N) permohonan (as pelulus)
permohonan (1) ──────< (N) permohonan_barang
barang (1) ──────< (N) permohonan_barang
KATEGORI (1) ──────< (N) barang
```

---

## Business Rules

1. **Every staff member must belong to one department** (mandatory relationship)
2. **Only admins (is_admin=1) can approve requests** (pelulus)
3. **Requests start with status 'Baru'** (new)
4. **Approved requests update stock levels** (baki_semasa)
5. **One request can contain multiple items** (master-detail pattern)
6. **Staff can be both requesters and approvers** (self-referencing on staf table)
7. **Departments can be deleted only if no staff assigned** (referential integrity)

---

**Generated:** 2025-12-16
**Database:** storeroom_db
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK
