# UML Diagrams
## Sistem Pengurusan Bilik Stor dan Inventori MPK

**Organization:** Majlis Perbandaran Kangar, Perlis
**Generated:** 30 December 2025
**System Status:** Production-Ready, Cleaned & Optimized

---

## Table of Contents

1. [Class Diagram](#1-class-diagram)
2. [Use Case Diagram](#2-use-case-diagram)
3. [Sequence Diagrams](#3-sequence-diagrams)
4. [Activity Diagrams](#4-activity-diagrams)
5. [State Diagram](#5-state-diagram)
6. [Component Diagram](#6-component-diagram)

---

## 1. Class Diagram

### UML Class Diagram (Database Model)

```mermaid
classDiagram
    class Jabatan {
        +INT ID_jabatan PK
        +VARCHAR nama_jabatan
        +DATETIME created_at
        +getStaffList()
        +getRequestsByDepartment()
        +deleteCheck()
    }

    class Staf {
        +VARCHAR ID_staf PK
        +VARCHAR nama
        +VARCHAR emel UNIQUE
        +VARCHAR kata_laluan
        +VARCHAR jawatan
        +VARCHAR no_telefon
        +INT ID_jabatan FK
        +VARCHAR gambar_profil
        +TINYINT is_admin
        +TINYINT is_first_login
        +DATETIME created_at
        +authenticate()
        +changePassword()
        +updateProfile()
        +uploadProfilePicture()
        +getRequests()
        +getApprovedRequests()
    }

    class KATEGORI {
        +INT ID_kategori PK
        +VARCHAR nama_kategori UNIQUE
        +getProducts()
        +deleteCheck()
    }

    class Barang {
        +VARCHAR no_kod PK
        +VARCHAR perihal_stok
        +INT ID_kategori FK
        +VARCHAR kategori
        +VARCHAR unit_pengukuran
        +DECIMAL harga_seunit
        +VARCHAR nama_pembekal
        +INT baki_semasa
        +DATETIME created_at
        +updateStock()
        +checkLowStock()
        +getStockHistory()
        +isAvailable(quantity)
    }

    class Permohonan {
        +INT ID_permohonan PK
        +DATE tarikh_mohon
        +VARCHAR status
        +VARCHAR ID_pemohon FK
        +VARCHAR nama_pemohon
        +VARCHAR jawatan_pemohon
        +INT ID_jabatan FK
        +TEXT catatan
        +VARCHAR ID_pelulus FK
        +DATETIME tarikh_lulus
        +TIMESTAMP created_at
        +submit()
        +approve()
        +reject()
        +getItems()
        +generateReceipt()
        +canBeDeleted()
    }

    class PermohonanBarang {
        +INT ID PK
        +INT ID_permohonan FK
        +VARCHAR no_kod FK
        +INT kuantiti_mohon
        +INT kuantiti_lulus
        +setApprovedQuantity()
        +getProductDetails()
    }

    class TransaksiStok {
        +INT ID_transaksi PK
        +VARCHAR no_kod FK
        +VARCHAR jenis_transaksi
        +INT kuantiti
        +INT baki_selepas_transaksi
        +INT ID_rujukan_permohonan FK
        +VARCHAR ID_pegawai FK
        +VARCHAR terima_dari_keluar_kepada
        +DATETIME tarikh_transaksi
        +TEXT catatan
        +logTransaction()
        +getAuditTrail()
    }

    %% Relationships
    Jabatan "1" -- "0..*" Staf : employs
    Jabatan "1" -- "0..*" Permohonan : generates

    Staf "1" -- "0..*" Permohonan : creates (pemohon)
    Staf "1" -- "0..*" Permohonan : approves (pelulus)
    Staf "1" -- "0..*" TransaksiStok : processes (pegawai)

    KATEGORI "1" -- "0..*" Barang : categorizes

    Permohonan "1" -- "1..*" PermohonanBarang : contains
    Permohonan "1" -- "0..*" TransaksiStok : generates

    Barang "1" -- "0..*" PermohonanBarang : requested in
    Barang "1" -- "1..*" TransaksiStok : has movements
```

---

## 2. Use Case Diagram

### System Use Cases (Actor-Based UML)

```mermaid
flowchart TB
    %% Actors (Outside System Boundary)
    Staff((Staff<br/>Pemohon))
    Admin((Admin<br/>Pelulus))
    TelegramBot((Telegram Bot<br/>System))

    %% System Boundary
    subgraph SystemBoundary["Sistem Pengurusan Bilik Stor dan Inventori MPK"]
        direction TB

        %% Common Use Cases
        UC1((Login to<br/>System))
        UC2((Change<br/>Password))
        UC3((Update<br/>Profile))
        UC4((Browse<br/>Products))

        %% Staff-Specific Use Cases
        UC5((Submit Stock<br/>Request<br/>KEW.PS-8))
        UC6((View Request<br/>History))
        UC7((Delete Pending<br/>Request))
        UC15((Print Receipt<br/>KEW.PS-8))

        %% Admin-Specific Use Cases
        UC8((Approve/Reject<br/>Request))
        UC9((Manage<br/>Products))
        UC10((Manage Stock<br/>Manual IN/OUT))
        UC11((Manage<br/>Departments))
        UC12((Manage<br/>Users))
        UC13((Generate<br/>Reports))
        UC14((View<br/>Dashboard))
        UC16((Print<br/>KEW.PS-3))
    end

    %% Staff Associations
    Staff ---|participates| UC1
    Staff ---|participates| UC2
    Staff ---|participates| UC3
    Staff ---|participates| UC4
    Staff ---|initiates| UC5
    Staff ---|views| UC6
    Staff ---|manages| UC7
    Staff ---|prints| UC15

    %% Admin Associations
    Admin ---|participates| UC1
    Admin ---|participates| UC2
    Admin ---|participates| UC3
    Admin ---|processes| UC8
    Admin ---|manages| UC9
    Admin ---|manages| UC10
    Admin ---|manages| UC11
    Admin ---|manages| UC12
    Admin ---|generates| UC13
    Admin ---|views| UC14
    Admin ---|prints| UC15
    Admin ---|prints| UC16

    %% System Interactions (External Actor)
    UC5 -.->|notifies| TelegramBot
    UC8 -.->|notifies| TelegramBot

    %% Styling
    style Staff fill:#10b981,stroke:#059669,color:#fff,stroke-width:3px
    style Admin fill:#f59e0b,stroke:#d97706,color:#fff,stroke-width:3px
    style TelegramBot fill:#3b82f6,stroke:#2563eb,color:#fff,stroke-width:3px
    style SystemBoundary fill:#f9fafb,stroke:#6b7280,stroke-width:4px

    %% Use Case Styling
    style UC1 fill:#e0e7ff,stroke:#4f46e5,stroke-width:2px
    style UC2 fill:#e0e7ff,stroke:#4f46e5,stroke-width:2px
    style UC3 fill:#e0e7ff,stroke:#4f46e5,stroke-width:2px
    style UC4 fill:#dbeafe,stroke:#3b82f6,stroke-width:2px
    style UC5 fill:#dcfce7,stroke:#10b981,stroke-width:2px
    style UC6 fill:#dbeafe,stroke:#3b82f6,stroke-width:2px
    style UC7 fill:#dbeafe,stroke:#3b82f6,stroke-width:2px
    style UC8 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC9 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC10 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC11 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC12 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC13 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC14 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
    style UC15 fill:#e0e7ff,stroke:#4f46e5,stroke-width:2px
    style UC16 fill:#fef3c7,stroke:#f59e0b,stroke-width:2px
```

### Use Case Descriptions

| Use Case | Actor | Description | Precondition |
|----------|-------|-------------|--------------|
| **UC1: Login to System** | Staff, Admin | User authenticates with ID_staf and password | Valid account exists |
| **UC2: Change Password** | Staff, Admin | User updates their password | User is logged in |
| **UC3: Update Profile** | Staff, Admin | User updates profile info and picture | User is logged in |
| **UC4: Browse Products** | Staff | View available products and stock levels | User is logged in |
| **UC5: Submit Stock Request** | Staff | Create KEW.PS-8 request with items | User is logged in, products available |
| **UC6: View Request History** | Staff | View own request status and history | User is logged in |
| **UC7: Delete Pending Request** | Staff | Delete own request if status='Baru' | Request status must be 'Baru' |
| **UC8: Approve/Reject Request** | Admin | Process pending staff requests | Admin is logged in, request is pending |
| **UC9: Manage Products** | Admin | CRUD operations on products | Admin is logged in |
| **UC10: Manage Stock** | Admin | Manual stock adjustments (IN/OUT) | Admin is logged in |
| **UC11: Manage Departments** | Admin | CRUD operations on departments | Admin is logged in |
| **UC12: Manage Users** | Admin | CRUD operations on staff accounts | Admin is logged in |
| **UC13: Generate Reports** | Admin | View inventory/request reports | Admin is logged in |
| **UC14: View Dashboard** | Admin | View system statistics | Admin is logged in |
| **UC15: Print Receipt (KEW.PS-8)** | Staff, Admin | Print request receipt | Request exists |
| **UC16: Print KEW.PS-3** | Admin | Print inventory report | Admin is logged in |

---

## 3. Sequence Diagrams

### 3.1 Submit Stock Request (Staff)

```mermaid
sequenceDiagram
    actor Staff
    participant Browser
    participant PHP
    participant Session
    participant Database
    participant Telegram

    Staff->>Browser: Browse products
    Browser->>PHP: staff_request_view.php
    PHP->>Database: SELECT * FROM barang
    Database-->>PHP: Product list
    PHP-->>Browser: Display products

    Staff->>Browser: Add items to cart
    Browser->>Session: Store cart items
    Session-->>Browser: Cart updated

    Staff->>Browser: Submit request
    Browser->>PHP: staff_request_process.php (POST)

    PHP->>Database: BEGIN TRANSACTION
    PHP->>Database: INSERT INTO permohonan
    Database-->>PHP: ID_permohonan

    loop For each cart item
        PHP->>Database: INSERT INTO permohonan_barang
    end

    PHP->>Database: COMMIT
    Database-->>PHP: Success

    PHP->>Telegram: Send notification to admin
    Telegram-->>PHP: Notification sent

    PHP->>Session: Clear cart
    PHP-->>Browser: Redirect to success page
    Browser-->>Staff: Request submitted confirmation
```

---

### 3.2 Approve Request (Admin)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant PHP
    participant Database
    participant Telegram

    Admin->>Browser: View pending requests
    Browser->>PHP: admin_request_view.php
    PHP->>Database: SELECT * FROM permohonan WHERE status='Baru'
    Database-->>PHP: Pending requests
    PHP-->>Browser: Display requests

    Admin->>Browser: Click Approve
    Browser->>PHP: admin_approval_process.php (POST)

    PHP->>Database: BEGIN TRANSACTION
    PHP->>Database: SELECT...FOR UPDATE (lock request)

    loop For each item
        PHP->>Database: SELECT baki_semasa FROM barang FOR UPDATE
        Database-->>PHP: Current stock

        alt Stock sufficient
            PHP->>Database: UPDATE barang SET baki_semasa = baki_semasa - qty
            PHP->>Database: INSERT INTO transaksi_stok (type='Keluar')
        else Stock insufficient
            PHP->>Database: ROLLBACK
            PHP-->>Browser: Error: Insufficient stock
        end
    end

    PHP->>Database: UPDATE permohonan SET status='Diluluskan'
    PHP->>Database: COMMIT
    Database-->>PHP: Success

    PHP->>Telegram: Send approval notification
    Telegram-->>PHP: Notification sent

    PHP-->>Browser: Redirect to success page
    Browser-->>Admin: Request approved confirmation
```

---

### 3.3 User Authentication

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant PHP
    participant Database
    participant Session

    User->>Browser: Enter ID_staf and password
    Browser->>PHP: login_process.php (POST)

    PHP->>Database: SELECT * FROM staf WHERE ID_staf=?
    Database-->>PHP: User record

    alt User exists
        PHP->>PHP: password_verify(input, hashed)

        alt Password correct
            PHP->>Session: Create session
            Session->>Session: Store ID_staf, nama, is_admin

            alt is_first_login = 1
                PHP-->>Browser: Redirect to change password
            else
                alt is_admin = 1
                    PHP-->>Browser: Redirect to admin dashboard
                else
                    PHP-->>Browser: Redirect to staff dashboard
                end
            end
        else Password incorrect
            PHP-->>Browser: Error: Invalid credentials
        end
    else User not found
        PHP-->>Browser: Error: Invalid credentials
    end
```

---

### 3.4 Manual Stock Adjustment (Admin)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant PHP
    participant Database

    Admin->>Browser: Open product edit
    Browser->>PHP: admin_edit_item.php?no_kod=X
    PHP->>Database: SELECT * FROM barang WHERE no_kod=?
    Database-->>PHP: Product details
    PHP-->>Browser: Display edit form

    Admin->>Browser: Adjust stock (IN/OUT)
    Browser->>PHP: admin_update_stock_process.php (POST)

    PHP->>Database: BEGIN TRANSACTION
    PHP->>Database: SELECT baki_semasa FROM barang FOR UPDATE
    Database-->>PHP: Current stock

    PHP->>PHP: Calculate new balance

    alt Stock OUT and insufficient
        PHP->>Database: ROLLBACK
        PHP-->>Browser: Error: Insufficient stock
    else Valid adjustment
        PHP->>Database: UPDATE barang SET baki_semasa=?
        PHP->>Database: INSERT INTO transaksi_stok
        Note over PHP,Database: Log: jenis_transaksi, kuantiti, baki_selepas_transaksi, ID_pegawai
        PHP->>Database: COMMIT
        Database-->>PHP: Success
        PHP-->>Browser: Stock updated successfully
    end
```

---

## 4. Activity Diagrams

### 4.1 Request Approval Workflow

```mermaid
flowchart TD
    Start([Staff Creates Request]) --> ValidateCart{Cart has items?}

    ValidateCart -->|No| ErrorEmpty[Show error: Cart empty]
    ErrorEmpty --> End1([End])

    ValidateCart -->|Yes| CreateRequest[Create permohonan record<br/>Status = 'Baru']
    CreateRequest --> AddItems[Add items to<br/>permohonan_barang]
    AddItems --> SendNotif[Send Telegram notification<br/>to admin]
    SendNotif --> WaitApproval{Admin reviews request}

    WaitApproval -->|Approve| CheckStock{Stock sufficient<br/>for all items?}
    WaitApproval -->|Reject| RejectRequest[Update status = 'Ditolak'<br/>Record reason]

    CheckStock -->|No| PartialApproval{Allow partial<br/>approval?}
    CheckStock -->|Yes| DeductStock[Deduct stock from<br/>barang.baki_semasa]

    PartialApproval -->|Yes| AdjustQty[Adjust kuantiti_lulus<br/>to available stock]
    PartialApproval -->|No| RejectRequest

    AdjustQty --> DeductStock
    DeductStock --> LogTransaction[Create transaksi_stok records<br/>type = 'Keluar']
    LogTransaction --> UpdateStatus[Update status = 'Diluluskan'<br/>Set ID_pelulus, tarikh_lulus]
    UpdateStatus --> GenerateReceipt[Generate KEW.PS-8 receipt]

    RejectRequest --> NotifyRejection[Notify requester]
    GenerateReceipt --> NotifyApproval[Notify requester]

    NotifyRejection --> End2([End])
    NotifyApproval --> End3([End])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#ef4444,color:#fff
    style End3 fill:#10b981,color:#fff
    style DeductStock fill:#3b82f6,color:#fff
    style LogTransaction fill:#3b82f6,color:#fff
```

---

### 4.2 User Registration Workflow

```mermaid
flowchart TD
    Start([Admin creates new user]) --> EnterDetails[Enter: ID_staf, nama, emel,<br/>jawatan, ID_jabatan]

    EnterDetails --> ValidateInput{Input valid?}
    ValidateInput -->|No| ShowError[Show validation errors]
    ShowError --> EnterDetails

    ValidateInput -->|Yes| CheckDuplicate{ID_staf or<br/>emel exists?}

    CheckDuplicate -->|Yes| ShowDupError[Show error:<br/>ID or email already exists]
    ShowDupError --> EnterDetails

    CheckDuplicate -->|No| HashPassword[Generate default password<br/>Hash with bcrypt]
    HashPassword --> SetFlags[Set is_admin = 0<br/>Set is_first_login = 1]
    SetFlags --> InsertDB[INSERT INTO staf]
    InsertDB --> SendCredentials[Send credentials to user<br/>via email/manual]
    SendCredentials --> Success([User created successfully])

    style Start fill:#10b981,color:#fff
    style Success fill:#10b981,color:#fff
    style ShowError fill:#ef4444,color:#fff
    style ShowDupError fill:#ef4444,color:#fff
```

---

## 5. State Diagram

### Request Status State Machine

```mermaid
stateDiagram-v2
    [*] --> Baru : Staff submits request

    Baru --> Diluluskan : Admin approves<br/>(stock deducted, transaction logged)
    Baru --> Ditolak : Admin rejects<br/>(with reason)
    Baru --> [*] : Staff deletes request<br/>(before approval)

    Diluluskan --> Diterima : Staff acknowledges receipt<br/>(optional)

    Ditolak --> [*] : Final state
    Diterima --> [*] : Final state

    note right of Baru
        Initial state
        Telegram notification sent
        Can be deleted by requester
    end note

    note right of Diluluskan
        Stock levels updated
        Audit trail created
        Receipt generated (KEW.PS-8)
        Cannot be modified
    end note

    note right of Ditolak
        Reason recorded
        No stock changes
        Cannot be modified
    end note
```

---

## 6. Component Diagram

### System Architecture Components

```mermaid
graph TB
    subgraph "Presentation Layer"
        UI1[Staff Dashboard<br/>staff_dashboard.php]
        UI2[Admin Dashboard<br/>admin_dashboard.php]
        UI3[Request Forms<br/>staff_request_view.php]
        UI4[Product Management<br/>admin_item.php]
        UI5[Reports<br/>report_*.php]
    end

    subgraph "Business Logic Layer"
        BL1[Authentication<br/>login_process.php]
        BL2[Request Processing<br/>*_request_process.php]
        BL3[Approval Processing<br/>admin_approval_process.php]
        BL4[Stock Management<br/>admin_update_stock.php]
        BL5[Report Generation<br/>report_*_process.php]
    end

    subgraph "Data Access Layer"
        DAL[Database Connection<br/>db_connect.php]
        Session[Session Management<br/>session_start.php]
    end

    subgraph "External Services"
        Telegram[Telegram Bot API<br/>telegram_notify.php]
    end

    subgraph "Database"
        DB[(MySQL/MariaDB<br/>storeroom_db)]
    end

    subgraph "Assets"
        CSS[Bootstrap 5.3.2<br/>Custom CSS]
        JS[JavaScript<br/>Chart.js, jQuery]
        Images[Profile Pictures<br/>Product Images]
    end

    %% Connections
    UI1 --> BL1
    UI2 --> BL1
    UI3 --> BL2
    UI4 --> BL4
    UI5 --> BL5

    BL1 --> DAL
    BL2 --> DAL
    BL3 --> DAL
    BL4 --> DAL
    BL5 --> DAL

    BL2 --> Telegram
    BL3 --> Telegram

    DAL --> DB
    BL1 --> Session
    BL2 --> Session
    BL3 --> Session

    UI1 -.-> CSS
    UI1 -.-> JS
    UI2 -.-> CSS
    UI2 -.-> JS
    UI5 -.-> JS

    style DB fill:#4f46e5,color:#fff
    style Telegram fill:#3b82f6,color:#fff
```

---

## 7. Deployment Diagram

### System Deployment Architecture

```mermaid
graph TB
    subgraph "Client Devices"
        Browser1[Desktop Browser<br/>Chrome/Edge/Firefox]
        Browser2[Mobile Browser<br/>Responsive Design]
    end

    subgraph "Web Server (Laragon/XAMPP)"
        Apache[Apache HTTP Server<br/>Port 80/443]
        PHP[PHP 8.x Runtime<br/>MySQLi Extension]
    end

    subgraph "Database Server"
        MySQL[(MySQL/MariaDB<br/>storeroom_db<br/>Port 3306)]
    end

    subgraph "External Services"
        TelegramAPI[Telegram Bot API<br/>api.telegram.org<br/>HTTPS]
    end

    subgraph "File System"
        Uploads[Uploads Directory<br/>Profile Pictures]
        Logs[Log Files<br/>error_log.txt]
    end

    Browser1 -->|HTTP/HTTPS| Apache
    Browser2 -->|HTTP/HTTPS| Apache

    Apache --> PHP
    PHP --> MySQL
    PHP --> Uploads
    PHP --> Logs
    PHP -->|HTTPS POST| TelegramAPI

    style MySQL fill:#4f46e5,color:#fff
    style TelegramAPI fill:#3b82f6,color:#fff
    style Apache fill:#ef4444,color:#fff
```

---

## 8. Package Diagram

### Code Organization

```mermaid
graph TB
    subgraph "Root Package"
        subgraph "Authentication Package"
            AUTH1[login.php]
            AUTH2[login_process.php]
            AUTH3[logout.php]
            AUTH4[session_start.php]
        end

        subgraph "Staff Package"
            STAFF1[staff_dashboard.php]
            STAFF2[staff_request_view.php]
            STAFF3[staff_request_process.php]
            STAFF4[staff_profile.php]
        end

        subgraph "Admin Package"
            ADMIN1[admin_dashboard.php]
            ADMIN2[admin_item.php]
            ADMIN3[admin_approval.php]
            ADMIN4[admin_department.php]
            ADMIN5[admin_user.php]
        end

        subgraph "Reports Package"
            REPORT1[report_requests.php]
            REPORT2[report_inventory.php]
            REPORT3[kewps3.php]
            REPORT4[kewps8_print.php]
        end

        subgraph "Utilities Package"
            UTIL1[db_connect.php]
            UTIL2[telegram_notify.php]
            UTIL3[functions.php]
        end

        subgraph "Assets Package"
            ASSET1[/css]
            ASSET2[/js]
            ASSET3[/uploads]
        end
    end

    STAFF1 --> AUTH4
    STAFF2 --> UTIL1
    STAFF3 --> UTIL1
    STAFF3 --> UTIL2

    ADMIN1 --> AUTH4
    ADMIN2 --> UTIL1
    ADMIN3 --> UTIL1
    ADMIN3 --> UTIL2

    REPORT1 --> UTIL1
    REPORT2 --> UTIL1

    AUTH2 --> UTIL1
```

---

## Summary

This UML documentation provides comprehensive visual representations of the Storeroom Management System:

1. **Class Diagram** - Database entities as classes with attributes and methods
2. **Use Case Diagram** - User interactions and system functionality
3. **Sequence Diagrams** - Step-by-step process flows for key operations
4. **Activity Diagrams** - Workflow logic and decision points
5. **State Diagram** - Request status lifecycle
6. **Component Diagram** - System architecture and component relationships
7. **Deployment Diagram** - Physical deployment architecture
8. **Package Diagram** - Code organization structure

### Key Design Patterns Identified:

- **MVC Pattern**: Separation of presentation, business logic, and data layers
- **Transaction Script**: Business logic organized around database transactions
- **Repository Pattern**: Database access abstraction through db_connect.php
- **Session State**: User state management via PHP sessions
- **Observer Pattern**: Telegram notifications triggered by system events

### Architecture Characteristics:

- **Layered Architecture**: Clear separation of concerns
- **Monolithic**: Single deployment unit
- **Database-Centric**: Strong reliance on relational database
- **Synchronous Processing**: No background jobs or queues
- **Stateful**: Session-based user state management

---

**Document Version:** 1.0
**Generated:** 30 December 2025
**Database:** storeroom_db (7 tables, 8 FK constraints)
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK
**Status:** Production-Ready, Cleaned & Optimized
