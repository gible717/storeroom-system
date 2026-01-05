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
- `is_admin` (TINYINT) - Role indicator: 0=Staff, 1=Admin
- `is_first_login` (TINYINT) - First login flag: 0=No, 1=Yes
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
- `status` (VARCHAR: 'Baru', 'Diluluskan', 'Ditolak', 'Diterima') - Request status
- `ID_pemohon` (VARCHAR) - FOREIGN KEY → staf.ID_staf - Requester ID
- `nama_pemohon` (VARCHAR) - Requester name (denormalized for history)
- `jawatan_pemohon` (VARCHAR) - Requester position (denormalized for history)
- `ID_jabatan` (INT) - FOREIGN KEY → jabatan.ID_jabatan - Department ID
- `catatan` (TEXT) - Staff's notes/remarks on the request
- `catatan_admin` (TEXT) - **NEW** Admin's remarks/notes for approval or rejection
- `ID_pelulus` (VARCHAR) - FOREIGN KEY → staf.ID_staf - Approver ID
- `nama_pelulus` (VARCHAR) - **NEW** Approver name (denormalized for audit trail)
- `jawatan_pelulus` (VARCHAR) - **NEW** Approver position (denormalized for audit trail)
- `tarikh_lulus` (DATETIME) - Approval/rejection datetime
- `created_at` (TIMESTAMP) - Record creation timestamp

**Relationships:**
- One request belongs to ONE staff (pemohon/requester)
- One request belongs to ONE department
- One request has MANY request items (permohonan_barang)
- One request can be approved by ONE admin (pelulus/approver)

**Notes:**
- `nama_pelulus` and `jawatan_pelulus` are denormalized copies from the `staf` table at the time of approval to maintain historical accuracy
- `catatan_admin` stores admin feedback/reasons for approval or rejection decisions

---

### 6. **permohonan_barang** (Request Items/Details)
Detail table for requested items (many-to-many junction table)

**Columns:**
- `ID` (INT) - PRIMARY KEY AUTO_INCREMENT - Request item ID
- `ID_permohonan` (INT) - FOREIGN KEY → permohonan.ID_permohonan - Request ID
- `no_kod` (VARCHAR) - FOREIGN KEY → barang.no_kod - Product code
- `kuantiti_mohon` (INT) - Quantity requested
- `kuantiti_lulus` (INT) - Quantity approved (default 0)

**Relationships:**
- Many request items belong to ONE request (permohonan)
- Many request items reference ONE product (barang)
- **JUNCTION TABLE** linking permohonan ↔ barang (Many-to-Many)

---

### 7. **transaksi_stok** (Stock Transaction Log)
Audit trail for all stock movements (in/out)

**Columns:**
- `ID_transaksi` (INT) - PRIMARY KEY AUTO_INCREMENT - Transaction ID
- `no_kod` (VARCHAR) - FOREIGN KEY → barang.no_kod - Product code
- `jenis_transaksi` (VARCHAR: 'Masuk', 'Keluar') - Transaction type (In/Out)
- `kuantiti` (INT) - Quantity moved
- `baki_selepas_transaksi` (INT) - Balance after transaction
- `ID_rujukan_permohonan` (INT) - FOREIGN KEY → permohonan.ID_permohonan (nullable) - Related request
- `ID_pegawai` (VARCHAR) - FOREIGN KEY → staf.ID_staf - Officer who processed
- `terima_dari_keluar_kepada` (VARCHAR) - Department/unit reference
- `tarikh_transaksi` (DATETIME) - Transaction datetime
- `catatan` (TEXT) - Transaction notes

**Relationships:**
- One transaction references ONE product (barang)
- One transaction may reference ONE request (optional for manual adjustments)
- One transaction is processed by ONE officer (staf)

---

## ERD Diagram - Crow's Foot Notation

### Crow's Foot Notation Legend:
```
||   One (and only one)
|o   Zero or one
}o   Zero or many (optional)
}{   One or many (mandatory)
```

### ERD Diagram (Crow's Foot)

```mermaid
erDiagram
    %% One-to-Many Relationships
    jabatan ||--o{ staf : "employs"
    jabatan ||--o{ permohonan : "makes requests from"

    staf ||--o{ permohonan : "creates (as pemohon)"
    staf ||--o{ permohonan : "approves (as pelulus)"
    staf ||--o{ transaksi_stok : "processes (as pegawai)"

    KATEGORI ||--o{ barang : "categorizes"

    permohonan ||--}{ permohonan_barang : "contains"
    permohonan ||--o{ transaksi_stok : "generates"

    barang ||--o{ permohonan_barang : "requested in"
    barang ||--}{ transaksi_stok : "has movements"

    %% Entity Definitions
    jabatan {
        INT ID_jabatan PK
        VARCHAR nama_jabatan
        DATETIME created_at
    }

    staf {
        VARCHAR ID_staf PK "Employee Number"
        VARCHAR nama
        VARCHAR emel UNIQUE
        VARCHAR kata_laluan "Hashed"
        VARCHAR jawatan
        INT ID_jabatan FK
        VARCHAR gambar_profil
        TINYINT is_admin "0=Staff, 1=Admin"
        TINYINT is_first_login "0=No, 1=Yes"
        DATETIME created_at
    }

    KATEGORI {
        INT ID_kategori PK
        VARCHAR nama_kategori UNIQUE
    }

    barang {
        VARCHAR no_kod PK "Product Code"
        VARCHAR perihal_stok "Description"
        INT ID_kategori FK
        VARCHAR kategori "Denormalized"
        VARCHAR unit_pengukuran "Unit"
        DECIMAL harga_seunit "Price"
        VARCHAR nama_pembekal "Supplier"
        INT baki_semasa "Current Stock"
        DATETIME created_at
    }

    permohonan {
        INT ID_permohonan PK "Auto Increment"
        DATE tarikh_mohon
        VARCHAR status "Baru|Diluluskan|Ditolak|Diterima"
        VARCHAR ID_pemohon FK "Requester"
        VARCHAR nama_pemohon "Denormalized"
        VARCHAR jawatan_pemohon "Denormalized"
        INT ID_jabatan FK
        TEXT catatan "Notes"
        VARCHAR ID_pelulus FK "Approver (nullable)"
        DATETIME tarikh_lulus "Nullable"
        TIMESTAMP created_at
    }

    permohonan_barang {
        INT ID PK "Auto Increment"
        INT ID_permohonan FK
        VARCHAR no_kod FK
        INT kuantiti_mohon "Qty Requested"
        INT kuantiti_lulus "Qty Approved"
    }

    transaksi_stok {
        INT ID_transaksi PK "Auto Increment"
        VARCHAR no_kod FK
        VARCHAR jenis_transaksi "Masuk|Keluar"
        INT kuantiti
        INT baki_selepas_transaksi
        INT ID_rujukan_permohonan FK "Nullable"
        VARCHAR ID_pegawai FK "Officer"
        VARCHAR terima_dari_keluar_kepada
        DATETIME tarikh_transaksi
        TEXT catatan
    }
```

---

## Visual ERD - Crow's Foot Notation (Text-Based)

```
┌─────────────────────────┐
│       KATEGORI          │
│─────────────────────────│
│ PK  ID_kategori (INT)   │
│     nama_kategori       │
└───────────┬─────────────┘
            │
            │ 1
            │
            ○ categorizes
            │
            │ N
            │
┌───────────┴─────────────┐           ┌─────────────────────────┐
│        barang           │           │       jabatan           │
│─────────────────────────│           │─────────────────────────│
│ PK  no_kod (VARCHAR)    │           │ PK  ID_jabatan (INT)    │
│     perihal_stok        │           │     nama_jabatan        │
│ FK  ID_kategori         │           │     created_at          │
│     kategori (denorm)   │           └───────┬────────┬────────┘
│     unit_pengukuran     │                   │        │
│     harga_seunit        │                   │ 1      │ 1
│     nama_pembekal       │                   │        │
│     baki_semasa         │                   ○        ○ employs / requests from
│     created_at          │                   │        │
└──────┬──────────┬───────┘                   │ N      │ N
       │          │                           │        │
       │ 1        │ 1                  ┌──────┴────────┴──────────────────────┐
       │          │                    │              staf                     │
       ○          ○ has movements      │───────────────────────────────────────│
       │          │                    │ PK  ID_staf (VARCHAR)                 │
       │ N        │ N                  │     nama                              │
       │          │                    │     emel (UNIQUE)                     │
┌──────┴──────┐   │                    │     kata_laluan (hashed)              │
│ permohonan_ │   │                    │     jawatan                           │
│   barang    │   │                    │ FK  ID_jabatan                        │
│─────────────│   │                    │     gambar_profil                     │
│ PK  ID      │   │                    │     is_admin (0/1)                    │
│ FK  ID_perm │   │                    │     is_first_login (0/1)              │
│ FK  no_kod  │   │                    │     created_at                        │
│ kuantiti_m  │   │                    └──────┬────────┬───────────────────┬───┘
│ kuantiti_l  │   │                           │        │                   │
└──────┬──────┘   │                           │        │                   │
       │          │                           │ 1      │ 1                 │ 1
       │ N        │                           │        │                   │
       │          │                           ○ creates○ approves          ○ processes
       │          │                           │        │                   │
       │ 1        │                           │ N      │ N                 │ N
       │          │                           │        │                   │
┌──────┴──────────┴───────────────────────────┴────────┴───────┐           │
│                      permohonan                               │           │
│───────────────────────────────────────────────────────────────│           │
│ PK  ID_permohonan (INT AUTO_INCREMENT)                        │           │
│     tarikh_mohon                                              │           │
│     status (Baru|Diluluskan|Ditolak|Diterima)                │           │
│ FK  ID_pemohon → staf.ID_staf (requester)                    │           │
│     nama_pemohon (denormalized)                               │           │
│     jawatan_pemohon (denormalized)                            │           │
│ FK  ID_jabatan → jabatan.ID_jabatan                           │           │
│     catatan                                                   │           │
│ FK  ID_pelulus → staf.ID_staf (approver, nullable)           │           │
│     tarikh_lulus                                              │           │
│     created_at                                                │           │
└───────────────────────────────┬───────────────────────────────┘           │
                                │                                           │
                                │ 1                                         │
                                │                                           │
                                ○ generates                                 │
                                │                                           │
                                │ N                                         │
                                │                                           │
                    ┌───────────┴───────────────────────────────────────────┘
                    │
┌───────────────────┴─────────────────────┐
│          transaksi_stok                 │
│─────────────────────────────────────────│
│ PK  ID_transaksi (INT AUTO_INCREMENT)   │
│ FK  no_kod → barang.no_kod              │
│     jenis_transaksi (Masuk|Keluar)      │
│     kuantiti                            │
│     baki_selepas_transaksi              │
│ FK  ID_rujukan_permohonan (nullable)    │
│ FK  ID_pegawai → staf.ID_staf           │
│     terima_dari_keluar_kepada           │
│     tarikh_transaksi                    │
│     catatan                             │
└─────────────────────────────────────────┘

Legend:
───── Relationship line
  │   Connection
  ○   Zero or more (optional, can be NULL)
  ●   One or more (mandatory)
  1   One (exactly one)
  N   Many (zero or more)
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

8. **barang → transaksi_stok** (1:N)
   - One product has many stock movement records
   - FK: `transaksi_stok.no_kod` → `barang.no_kod`

9. **permohonan → transaksi_stok** (1:N)
   - One request generates many stock transactions (optional relationship)
   - FK: `transaksi_stok.ID_rujukan_permohonan` → `permohonan.ID_permohonan` (nullable)

10. **staf → transaksi_stok (as pegawai)** (1:N)
   - One officer processes many stock transactions
   - FK: `transaksi_stok.ID_pegawai` → `staf.ID_staf`

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

**Current Form: 2NF (Second Normal Form) - Intentional Design Decision**

### Why 2NF (Not 3NF)?
- ✅ All tables are in 1NF (atomic values, no repeating groups)
- ✅ All non-key attributes depend on the primary key (2NF satisfied)
- ⚠️ **Intentional denormalization** exists in `permohonan` table:
  - `nama_pemohon` (duplicates `staf.nama`)
  - `jawatan_pemohon` (duplicates `staf.jawatan`)
  - These violate 3NF because they depend on `ID_pemohon`, not directly on `ID_permohonan`

### Why Denormalization is Required:

#### 1. **Historical Data Preservation (Regulatory Compliance)**
KEW.PS-8 is a government form that must show **exact details at the time of request**:
- If staff name changes (marriage, corrections): old requests show original name
- If staff gets promoted: old requests show original position
- If staff leaves organization: their historical requests remain intact

**Example:**
```
15 Jan 2025: Request #123 by "Ahmad Faiz, Pembantu Tadbir"
1 Mar 2025: Ahmad promoted to "Penolong Pegawai Tadbir"
Printing request #123: Still shows "Pembantu Tadbir" ✅ (correct)
```

#### 2. **Audit Trail Requirements**
- Government audits require proof of who made the request and their position **at that specific time**
- Denormalized data provides immutable historical records
- Protects against data loss if staff records are modified or deleted

#### 3. **Performance Optimization**
- Avoids JOIN operations when displaying request history, reports, and printing forms
- Faster queries for frequently accessed request data

### Could be 3NF if:
- Remove `nama_pemohon`, `jawatan_pemohon` from `permohonan` (always JOIN with staf)
- **Trade-off**: Strict normalization vs Business requirements
- **Decision**: We choose 2NF because **regulatory compliance and historical accuracy** are more important than strict normalization

### Why This is Good Design:
This is an example of **pragmatic denormalization** - intentionally violating 3NF for valid business reasons. Industry best practice supports denormalization when:
1. Historical snapshots are required for legal/audit purposes
2. Performance gains are significant
3. Data consistency is maintained through application logic

---

## Cardinality Notation

```
jabatan (1) ──────< (N) staf
jabatan (1) ──────< (N) permohonan
staf (1) ──────< (N) permohonan (as pemohon)
staf (1) ──────< (N) permohonan (as pelulus)
staf (1) ──────< (N) transaksi_stok (as pegawai)
permohonan (1) ──────< (N) permohonan_barang
permohonan (1) ──────< (N) transaksi_stok (optional)
barang (1) ──────< (N) permohonan_barang
barang (1) ──────< (N) transaksi_stok
KATEGORI (1) ──────< (N) barang
```

---

## Business Rules

1. **Every staff member must belong to one department** (mandatory relationship)
2. **Only admins (is_admin=1) can approve requests** (pelulus)
3. **Requests start with status 'Baru'** (new)
4. **Approved requests update stock levels** (baki_semasa) and create stock transactions
5. **One request can contain multiple items** (master-detail pattern)
6. **Staff can be both requesters and approvers** (self-referencing on staf table)
7. **All stock movements are logged** in transaksi_stok table (audit trail)
8. **ID_pegawai tracks the officer who processed the transaction** (different from pemohon/pelulus)
9. **Stock transactions can be manual or request-based** (ID_rujukan_permohonan nullable)

---

## Foreign Key Constraints

**Database-level FK constraints implemented (30 December 2025):**

1. `fk_barang_kategori`: barang.ID_kategori → KATEGORI.ID_kategori (ON DELETE RESTRICT)
2. `fk_staf_jabatan`: staf.ID_jabatan → jabatan.ID_jabatan (ON DELETE SET NULL)
3. `fk_permohonan_jabatan`: permohonan.ID_jabatan → jabatan.ID_jabatan (ON DELETE SET NULL)
4. `fk_permohonan_pemohon`: permohonan.ID_pemohon → staf.ID_staf (ON DELETE RESTRICT)
5. `fk_permohonan_pelulus`: permohonan.ID_pelulus → staf.ID_staf (ON DELETE RESTRICT)
6. `fk_pb_barang`: permohonan_barang.no_kod → barang.no_kod (ON DELETE RESTRICT)
7. `fk_pb_permohonan`: permohonan_barang.ID_permohonan → permohonan.ID_permohonan (ON DELETE CASCADE)
8. `fk_transaksi_stok_barang`: transaksi_stok.no_kod → barang.no_kod (ON DELETE RESTRICT)

---

**Generated:** 30 December 2025
**Database:** storeroom_db (7 tables, 8 FK constraints)
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK
**Status:** Production-Ready, Cleaned & Optimized
