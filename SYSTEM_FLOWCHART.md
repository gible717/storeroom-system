# System Flowcharts
## Sistem Pengurusan Bilik Stor dan Inventori MPK

**Organization:** Majlis Perbandaran Kangar, Perlis
**Generated:** 30 December 2025
**System Status:** Production-Ready, Cleaned & Optimized

---

## Table of Contents

1. [System Overview Flowchart](#1-system-overview-flowchart)
2. [User Authentication Flow](#2-user-authentication-flow)
3. [Staff Request Submission Flow](#3-staff-request-submission-flow)
4. [Admin Approval Process Flow](#4-admin-approval-process-flow)
5. [Product Management Flow](#5-product-management-flow)
6. [Stock Adjustment Flow](#6-stock-adjustment-flow)
7. [Report Generation Flow](#7-report-generation-flow)
8. [Password Management Flow](#8-password-management-flow)
9. [Department Management Flow](#9-department-management-flow)
10. [Telegram Notification Flow](#10-telegram-notification-flow)

---

## 1. System Overview Flowchart

### Main System Flow

```mermaid
flowchart TD
    Start([User Access System]) --> CheckSession{Session<br/>exists?}

    CheckSession -->|No| LoginPage[Display Login Page]
    CheckSession -->|Yes| ValidateSession{Session<br/>valid?}

    LoginPage --> LoginProcess[User enters credentials]
    LoginProcess --> AuthCheck{Authenticate}

    AuthCheck -->|Invalid| LoginError[Show error message]
    LoginError --> LoginPage

    AuthCheck -->|Valid| FirstLogin{is_first_login<br/>= 1?}

    FirstLogin -->|Yes| ForcePassword[Force Password Change]
    FirstLogin -->|No| CheckRole{is_admin<br/>= 1?}

    ForcePassword --> PasswordChanged{Password<br/>updated?}
    PasswordChanged -->|Yes| CheckRole
    PasswordChanged -->|No| ForcePassword

    CheckRole -->|Admin| AdminDashboard[Admin Dashboard]
    CheckRole -->|Staff| StaffDashboard[Staff Dashboard]

    ValidateSession -->|Valid| CheckRole
    ValidateSession -->|Invalid| Logout[Clear session]
    Logout --> LoginPage

    AdminDashboard --> AdminMenu{Select Function}
    StaffDashboard --> StaffMenu{Select Function}

    AdminMenu -->|Manage Products| ProductMgmt[Product Management]
    AdminMenu -->|Approve Requests| ApprovalProcess[Approval Process]
    AdminMenu -->|Manage Users| UserMgmt[User Management]
    AdminMenu -->|Manage Departments| DeptMgmt[Department Management]
    AdminMenu -->|View Reports| Reports[Reports & Analytics]
    AdminMenu -->|Profile| ProfileMgmt[Profile Management]
    AdminMenu -->|Logout| LogoutProcess[Logout]

    StaffMenu -->|Submit Request| RequestSubmit[Submit Stock Request]
    StaffMenu -->|View History| ViewHistory[View Request History]
    StaffMenu -->|Profile| ProfileMgmt
    StaffMenu -->|Logout| LogoutProcess

    LogoutProcess --> End([Session Ended])

    style Start fill:#10b981,color:#fff
    style End fill:#ef4444,color:#fff
    style AdminDashboard fill:#f59e0b,color:#fff
    style StaffDashboard fill:#3b82f6,color:#fff
    style LoginError fill:#ef4444,color:#fff
```

---

## 2. User Authentication Flow

### Login Process Flowchart

```mermaid
flowchart TD
    Start([User visits login.php]) --> DisplayForm[Display Login Form]

    DisplayForm --> UserInput[User enters:<br/>- ID_staf<br/>- kata_laluan]

    UserInput --> SubmitForm[Submit Form<br/>POST to login_process.php]

    SubmitForm --> ValidateInput{Input fields<br/>filled?}

    ValidateInput -->|No| ErrorEmpty[Error: Fields required]
    ErrorEmpty --> DisplayForm

    ValidateInput -->|Yes| QueryDB[Query Database:<br/>SELECT * FROM staf<br/>WHERE ID_staf = ?]

    QueryDB --> UserExists{User<br/>found?}

    UserExists -->|No| ErrorUser[Error: Invalid credentials]
    ErrorUser --> DisplayForm

    UserExists -->|Yes| VerifyPassword[password_verify<br/>input vs hashed password]

    VerifyPassword --> PasswordMatch{Password<br/>correct?}

    PasswordMatch -->|No| ErrorPassword[Error: Invalid credentials]
    ErrorPassword --> DisplayForm

    PasswordMatch -->|Yes| CreateSession[Create PHP Session:<br/>- $_SESSION ID_staf<br/>- $_SESSION nama<br/>- $_SESSION is_admin]

    CreateSession --> CheckFirstLogin{is_first_login<br/>= 1?}

    CheckFirstLogin -->|Yes| RedirectChangePassword[Redirect to:<br/>change_password.php<br/>with force flag]

    CheckFirstLogin -->|No| CheckAdmin{is_admin<br/>= 1?}

    CheckAdmin -->|Yes Admin| RedirectAdmin[Redirect to:<br/>admin_dashboard.php]
    CheckAdmin -->|No Staff| RedirectStaff[Redirect to:<br/>staff_dashboard.php]

    RedirectChangePassword --> End1([Must change password])
    RedirectAdmin --> End2([Admin session started])
    RedirectStaff --> End3([Staff session started])

    style Start fill:#10b981,color:#fff
    style End1 fill:#f59e0b,color:#fff
    style End2 fill:#f59e0b,color:#fff
    style End3 fill:#3b82f6,color:#fff
    style ErrorEmpty fill:#ef4444,color:#fff
    style ErrorUser fill:#ef4444,color:#fff
    style ErrorPassword fill:#ef4444,color:#fff
```

---

## 3. Staff Request Submission Flow

### Stock Request Process Flowchart

```mermaid
flowchart TD
    Start([Staff clicks<br/>Submit Request]) --> CheckAuth{Session<br/>valid?}

    CheckAuth -->|No| RedirectLogin[Redirect to login.php]
    RedirectLogin --> End1([Unauthorized])

    CheckAuth -->|Yes| LoadProducts[Load Products:<br/>SELECT * FROM barang<br/>WHERE baki_semasa > 0]

    LoadProducts --> DisplayProducts[Display product list<br/>with stock levels]

    DisplayProducts --> BrowseProducts{Staff browses<br/>products}

    BrowseProducts --> AddToCart[Staff adds items<br/>to cart session]

    AddToCart --> CartCheck{Continue<br/>shopping?}

    CartCheck -->|Yes| BrowseProducts
    CartCheck -->|No| ReviewCart[Review cart items]

    ReviewCart --> CartEmpty{Cart has<br/>items?}

    CartEmpty -->|No| ErrorNoItems[Error: Cart is empty]
    ErrorNoItems --> DisplayProducts

    CartEmpty -->|Yes| EnterNotes[Optional: Enter catatan<br/>request notes]

    EnterNotes --> SubmitRequest[Submit Request<br/>POST to staff_request_process.php]

    SubmitRequest --> BeginTransaction[BEGIN TRANSACTION]

    BeginTransaction --> GetStaffInfo[Get staff info:<br/>- nama_pemohon<br/>- jawatan_pemohon<br/>- ID_jabatan]

    GetStaffInfo --> InsertHeader[INSERT INTO permohonan:<br/>- tarikh_mohon = NOW<br/>- status = 'Baru'<br/>- ID_pemohon<br/>- catatan]

    InsertHeader --> GetRequestID[Get new<br/>ID_permohonan]

    GetRequestID --> LoopItems[Loop through<br/>cart items]

    LoopItems --> InsertItem[INSERT INTO<br/>permohonan_barang:<br/>- ID_permohonan<br/>- no_kod<br/>- kuantiti_mohon]

    InsertItem --> MoreItems{More items<br/>in cart?}

    MoreItems -->|Yes| LoopItems
    MoreItems -->|No| CommitTransaction[COMMIT TRANSACTION]

    CommitTransaction --> ClearCart[Clear session cart]

    ClearCart --> SendTelegram[Send Telegram notification<br/>to admin group]

    SendTelegram --> TelegramSent{Notification<br/>sent?}

    TelegramSent -->|Yes| SuccessWithNotif[Success message:<br/>Request submitted<br/>Admin notified]
    TelegramSent -->|No| SuccessNoNotif[Success message:<br/>Request submitted<br/>Manual notification needed]

    SuccessWithNotif --> ShowReceipt[Display request receipt<br/>with ID_permohonan]
    SuccessNoNotif --> ShowReceipt

    ShowReceipt --> End2([Request submitted])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#10b981,color:#fff
    style ErrorNoItems fill:#ef4444,color:#fff
    style BeginTransaction fill:#8b5cf6,color:#fff
    style CommitTransaction fill:#8b5cf6,color:#fff
```

---

## 4. Admin Approval Process Flow

### Request Approval/Rejection Flowchart

```mermaid
flowchart TD
    Start([Admin views<br/>pending requests]) --> CheckAuth{is_admin<br/>= 1?}

    CheckAuth -->|No| AccessDenied[Error: Access denied]
    AccessDenied --> End1([Unauthorized])

    CheckAuth -->|Yes| LoadRequests[Query Database:<br/>SELECT * FROM permohonan<br/>WHERE status = 'Baru'<br/>ORDER BY tarikh_mohon]

    LoadRequests --> DisplayList[Display pending<br/>requests list]

    DisplayList --> SelectRequest[Admin selects<br/>a request]

    SelectRequest --> LoadDetails[Load request details:<br/>JOIN permohonan_barang<br/>JOIN barang]

    LoadDetails --> DisplayDetails[Display:<br/>- Requester info<br/>- Items requested<br/>- Current stock levels]

    DisplayDetails --> AdminDecision{Admin<br/>decision?}

    AdminDecision -->|Approve| ConfirmApprove{Confirm<br/>approval?}
    AdminDecision -->|Reject| EnterReason[Enter rejection reason]

    ConfirmApprove -->|No| DisplayDetails
    ConfirmApprove -->|Yes| BeginApproval[BEGIN TRANSACTION]

    BeginApproval --> LockRequest[SELECT...FOR UPDATE<br/>permohonan]

    LockRequest --> LoopItems[Loop through<br/>requested items]

    LoopItems --> CheckStock[SELECT baki_semasa<br/>FROM barang<br/>FOR UPDATE]

    CheckStock --> StockSufficient{Stock ≥<br/>quantity?}

    StockSufficient -->|No| StockInsufficient[ROLLBACK<br/>Error: Insufficient stock<br/>for item]
    StockInsufficient --> End2([Approval failed])

    StockSufficient -->|Yes| DeductStock[UPDATE barang<br/>SET baki_semasa =<br/>baki_semasa - kuantiti_mohon]

    DeductStock --> UpdateApproved[UPDATE permohonan_barang<br/>SET kuantiti_lulus =<br/>kuantiti_mohon]

    UpdateApproved --> LogTransaction[INSERT INTO transaksi_stok:<br/>- jenis_transaksi = 'Keluar'<br/>- kuantiti<br/>- baki_selepas_transaksi<br/>- ID_rujukan_permohonan<br/>- ID_pegawai = admin ID]

    LogTransaction --> MoreItems{More items<br/>to process?}

    MoreItems -->|Yes| LoopItems
    MoreItems -->|No| UpdateStatus[UPDATE permohonan:<br/>- status = 'Diluluskan'<br/>- ID_pelulus<br/>- tarikh_lulus = NOW]

    UpdateStatus --> CommitApproval[COMMIT TRANSACTION]

    CommitApproval --> SendApprovalNotif[Send Telegram:<br/>Request approved]

    SendApprovalNotif --> ShowSuccess[Success: Request approved<br/>Stock updated<br/>Receipt generated]

    ShowSuccess --> End3([Approval complete])

    EnterReason --> SubmitRejection[Submit rejection]

    SubmitRejection --> UpdateRejected[UPDATE permohonan:<br/>- status = 'Ditolak'<br/>- ID_pelulus<br/>- tarikh_lulus = NOW<br/>- catatan rejection reason]

    UpdateRejected --> SendRejectionNotif[Send Telegram:<br/>Request rejected]

    SendRejectionNotif --> ShowRejected[Show: Request rejected]

    ShowRejected --> End4([Rejection complete])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#ef4444,color:#fff
    style End3 fill:#10b981,color:#fff
    style End4 fill:#f59e0b,color:#fff
    style BeginApproval fill:#8b5cf6,color:#fff
    style CommitApproval fill:#8b5cf6,color:#fff
    style StockInsufficient fill:#ef4444,color:#fff
```

---

## 5. Product Management Flow

### Add/Edit/Delete Product Flowchart

```mermaid
flowchart TD
    Start([Admin selects<br/>Product Management]) --> CheckAuth{is_admin<br/>= 1?}

    CheckAuth -->|No| AccessDenied[Error: Access denied]
    AccessDenied --> End1([Unauthorized])

    CheckAuth -->|Yes| LoadProducts[Load all products:<br/>SELECT * FROM barang<br/>JOIN KATEGORI]

    LoadProducts --> DisplayProducts[Display product list<br/>with search/filter]

    DisplayProducts --> Action{Admin<br/>action?}

    Action -->|Add New| ShowAddForm[Display Add Product Form]
    Action -->|Edit| SelectProduct[Select product]
    Action -->|Delete| SelectForDelete[Select product to delete]
    Action -->|View| DisplayProducts

    ShowAddForm --> EnterDetails[Enter product details:<br/>- no_kod PK<br/>- perihal_stok<br/>- ID_kategori<br/>- unit_pengukuran<br/>- harga_seunit<br/>- nama_pembekal<br/>- baki_semasa]

    EnterDetails --> ValidateAdd{Validate<br/>input?}

    ValidateAdd -->|Invalid| ShowAddError[Show validation errors]
    ShowAddError --> ShowAddForm

    ValidateAdd -->|Valid| CheckDuplicate{no_kod<br/>exists?}

    CheckDuplicate -->|Yes| ErrorDuplicate[Error: Product code exists]
    ErrorDuplicate --> ShowAddForm

    CheckDuplicate -->|No| GetCategoryName[Get nama_kategori<br/>from KATEGORI table]

    GetCategoryName --> InsertProduct[INSERT INTO barang<br/>with all details<br/>kategori denormalized]

    InsertProduct --> LogInitialStock{Initial stock<br/>> 0?}

    LogInitialStock -->|Yes| InsertInitialTrans[INSERT INTO transaksi_stok<br/>jenis = 'Masuk'<br/>kuantiti = baki_semasa]
    LogInitialStock -->|No| SuccessAdd[Success: Product added]

    InsertInitialTrans --> SuccessAdd
    SuccessAdd --> End2([Product created])

    SelectProduct --> ShowEditForm[Display Edit Form<br/>with current values]

    ShowEditForm --> ModifyDetails[Modify product details]

    ModifyDetails --> ValidateEdit{Validate<br/>changes?}

    ValidateEdit -->|Invalid| ShowEditError[Show validation errors]
    ShowEditError --> ShowEditForm

    ValidateEdit -->|Valid| UpdateProduct[UPDATE barang<br/>SET new values]

    UpdateProduct --> SuccessEdit[Success: Product updated]
    SuccessEdit --> End3([Product updated])

    SelectForDelete --> ConfirmDelete{Confirm<br/>delete?}

    ConfirmDelete -->|No| DisplayProducts
    ConfirmDelete -->|Yes| CheckUsage[Check if product used:<br/>- permohonan_barang<br/>- transaksi_stok]

    CheckUsage --> InUse{Product<br/>in use?}

    InUse -->|Yes| ErrorInUse[Error: Cannot delete<br/>Product has history<br/>FK constraint]
    ErrorInUse --> DisplayProducts

    InUse -->|No| DeleteProduct[DELETE FROM barang<br/>WHERE no_kod = ?]

    DeleteProduct --> SuccessDelete[Success: Product deleted]
    SuccessDelete --> End4([Product deleted])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#10b981,color:#fff
    style End3 fill:#10b981,color:#fff
    style End4 fill:#f59e0b,color:#fff
    style ErrorDuplicate fill:#ef4444,color:#fff
    style ErrorInUse fill:#ef4444,color:#fff
```

---

## 6. Stock Adjustment Flow

### Manual Stock Update Flowchart

```mermaid
flowchart TD
    Start([Admin manual<br/>stock adjustment]) --> CheckAuth{is_admin<br/>= 1?}

    CheckAuth -->|No| AccessDenied[Error: Access denied]
    AccessDenied --> End1([Unauthorized])

    CheckAuth -->|Yes| SelectProduct[Select product<br/>to adjust]

    SelectProduct --> LoadCurrent[Query current:<br/>SELECT baki_semasa<br/>FROM barang<br/>WHERE no_kod = ?]

    LoadCurrent --> DisplayCurrent[Display:<br/>- Product details<br/>- Current stock: XX units]

    DisplayCurrent --> SelectType{Adjustment<br/>type?}

    SelectType -->|Stock IN| EnterStockIn[Enter quantity IN<br/>Source: Purchase/Return]
    SelectType -->|Stock OUT| EnterStockOut[Enter quantity OUT<br/>Reason: Damage/Loss/Other]

    EnterStockIn --> ValidateIn{Quantity<br/>valid?}
    EnterStockOut --> ValidateOut{Quantity<br/>valid?}

    ValidateIn -->|No| ErrorInvalid[Error: Invalid quantity]
    ValidateOut -->|No| ErrorInvalid
    ErrorInvalid --> DisplayCurrent

    ValidateIn -->|Yes| CalculateNewIn[Calculate new stock:<br/>new = current + quantity]
    ValidateOut -->|Yes| CheckSufficient{Current stock ≥<br/>quantity OUT?}

    CheckSufficient -->|No| ErrorInsufficient[Error: Insufficient stock<br/>Current: XX<br/>Requested: YY]
    ErrorInsufficient --> DisplayCurrent

    CheckSufficient -->|Yes| CalculateNewOut[Calculate new stock:<br/>new = current - quantity]

    CalculateNewIn --> EnterNotes[Enter notes/catatan]
    CalculateNewOut --> EnterNotes

    EnterNotes --> ConfirmAdjust{Confirm<br/>adjustment?}

    ConfirmAdjust -->|No| DisplayCurrent
    ConfirmAdjust -->|Yes| BeginTransaction[BEGIN TRANSACTION]

    BeginTransaction --> LockProduct[SELECT...FOR UPDATE<br/>barang]

    LockProduct --> DetermineType{Transaction<br/>type?}

    DetermineType -->|IN| UpdateStockIn[UPDATE barang<br/>SET baki_semasa =<br/>baki_semasa + quantity]
    DetermineType -->|OUT| UpdateStockOut[UPDATE barang<br/>SET baki_semasa =<br/>baki_semasa - quantity]

    UpdateStockIn --> GetNewBalance[Get new baki_semasa]
    UpdateStockOut --> GetNewBalance

    GetNewBalance --> LogTransactionIn{Was it<br/>Stock IN?}

    LogTransactionIn -->|Yes| InsertTransIn[INSERT INTO transaksi_stok:<br/>- jenis = 'Masuk'<br/>- kuantiti<br/>- baki_selepas_transaksi<br/>- ID_pegawai admin<br/>- catatan]

    LogTransactionIn -->|No| InsertTransOut[INSERT INTO transaksi_stok:<br/>- jenis = 'Keluar'<br/>- kuantiti<br/>- baki_selepas_transaksi<br/>- ID_pegawai admin<br/>- catatan]

    InsertTransIn --> CommitTransaction[COMMIT TRANSACTION]
    InsertTransOut --> CommitTransaction

    CommitTransaction --> CheckLowStock{New stock<br/>≤ 10?}

    CheckLowStock -->|Yes| SendLowStockAlert[Send Telegram:<br/>Low stock warning<br/>Product: XXX<br/>Stock: YY units]
    CheckLowStock -->|No| SuccessAdjust[Success:<br/>Stock updated<br/>Transaction logged]

    SendLowStockAlert --> SuccessAdjust

    SuccessAdjust --> End2([Stock adjusted])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#10b981,color:#fff
    style ErrorInsufficient fill:#ef4444,color:#fff
    style ErrorInvalid fill:#ef4444,color:#fff
    style BeginTransaction fill:#8b5cf6,color:#fff
    style CommitTransaction fill:#8b5cf6,color:#fff
```

---

## 7. Report Generation Flow

### Report Creation Flowchart

```mermaid
flowchart TD
    Start([Admin selects<br/>Reports]) --> CheckAuth{is_admin<br/>= 1?}

    CheckAuth -->|No| AccessDenied[Error: Access denied]
    AccessDenied --> End1([Unauthorized])

    CheckAuth -->|Yes| SelectReport{Select<br/>Report Type?}

    SelectReport -->|Inventory| InventoryReport[Inventory Report<br/>report_inventory.php]
    SelectReport -->|Requests| RequestsReport[Requests Report<br/>report_requests.php]
    SelectReport -->|KEW.PS-3| KEWPS3Report[KEW.PS-3 Report<br/>kewps3.php]
    SelectReport -->|Suppliers| SuppliersReport[Suppliers Report<br/>report_suppliers.php]

    InventoryReport --> SetInvFilters[Set filters:<br/>- Category<br/>- Stock level<br/>- Supplier]

    SetInvFilters --> QueryInventory[Query:<br/>SELECT * FROM barang<br/>JOIN KATEGORI<br/>WHERE conditions]

    QueryInventory --> GenerateInvData[Generate data:<br/>- Total items<br/>- Total value<br/>- Low stock items<br/>- By category stats]

    GenerateInvData --> DisplayInvReport[Display report with:<br/>- Tables<br/>- Charts Chart.js<br/>- Summary cards]

    DisplayInvReport --> InvExport{Export<br/>option?}

    InvExport -->|Excel| ExportExcel[Generate Excel file]
    InvExport -->|PDF| ExportPDF[Generate PDF]
    InvExport -->|Print| PrintReport[Browser print]
    InvExport -->|None| End2([Report viewed])

    ExportExcel --> End2
    ExportPDF --> End2
    PrintReport --> End2

    RequestsReport --> SetReqFilters[Set filters:<br/>- Date range<br/>- Department<br/>- Status<br/>- Requester]

    SetReqFilters --> QueryRequests[Query:<br/>SELECT * FROM permohonan<br/>JOIN staf<br/>JOIN jabatan<br/>WHERE conditions]

    QueryRequests --> GenerateReqData[Generate data:<br/>- Total requests<br/>- By status<br/>- By department<br/>- Top requesters<br/>- Approval rates]

    GenerateReqData --> DisplayReqReport[Display report with:<br/>- Department analytics<br/>- Status breakdown<br/>- Charts<br/>- Top 10 departments]

    DisplayReqReport --> ReqExport{Export<br/>option?}

    ReqExport -->|Excel| ExportExcel
    ReqExport -->|PDF| ExportPDF
    ReqExport -->|Print| PrintReport
    ReqExport -->|None| End3([Report viewed])

    KEWPS3Report --> SetKEWFilters[Set date range<br/>for KEW.PS-3]

    SetKEWFilters --> QueryKEW[Query approved requests<br/>in date range<br/>with stock movements]

    QueryKEW --> GenerateKEWData[Generate KEW.PS-3:<br/>- Opening balance<br/>- Stock IN<br/>- Stock OUT<br/>- Closing balance<br/>By product]

    GenerateKEWData --> DisplayKEW[Display KEW.PS-3 form<br/>Official format]

    DisplayKEW --> KEWPrint[Print KEW.PS-3]
    KEWPrint --> End4([KEW.PS-3 printed])

    SuppliersReport --> QuerySuppliers[Query:<br/>SELECT DISTINCT<br/>nama_pembekal<br/>FROM barang<br/>GROUP BY]

    QuerySuppliers --> GenerateSupplierData[Generate:<br/>- Suppliers list<br/>- Products per supplier<br/>- Total value<br/>- Stock levels]

    GenerateSupplierData --> DisplaySuppliers[Display supplier<br/>analysis report]

    DisplaySuppliers --> End5([Report viewed])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#10b981,color:#fff
    style End3 fill:#10b981,color:#fff
    style End4 fill:#10b981,color:#fff
    style End5 fill:#10b981,color:#fff
```

---

## 8. Password Management Flow

### Change Password Flowchart

```mermaid
flowchart TD
    Start([User changes<br/>password]) --> CheckAuth{Session<br/>valid?}

    CheckAuth -->|No| RedirectLogin[Redirect to login.php]
    RedirectLogin --> End1([Unauthorized])

    CheckAuth -->|Yes| CheckForced{is_first_login<br/>= 1?}

    CheckForced -->|Yes| DisplayForced[Display form:<br/>MUST change password<br/>Cannot skip]
    CheckForced -->|No| DisplayOptional[Display form:<br/>Optional change<br/>Can cancel]

    DisplayForced --> EnterPasswords[Enter:<br/>- Current password<br/>- New password<br/>- Confirm new password]
    DisplayOptional --> EnterPasswords

    EnterPasswords --> ValidateInput{All fields<br/>filled?}

    ValidateInput -->|No| ErrorEmpty[Error: All fields required]
    ErrorEmpty --> CheckForced

    ValidateInput -->|Yes| QueryCurrent[Query current password:<br/>SELECT kata_laluan<br/>FROM staf<br/>WHERE ID_staf = ?]

    QueryCurrent --> VerifyOld[password_verify<br/>current input vs DB]

    VerifyOld --> OldMatch{Current password<br/>correct?}

    OldMatch -->|No| ErrorOldWrong[Error: Current password<br/>incorrect]
    ErrorOldWrong --> CheckForced

    OldMatch -->|Yes| CheckNewLength{New password<br/>≥ 8 chars?}

    CheckNewLength -->|No| ErrorLength[Error: Password must be<br/>at least 8 characters]
    ErrorLength --> CheckForced

    CheckNewLength -->|Yes| CheckMatch{New password =<br/>Confirm?}

    CheckMatch -->|No| ErrorMismatch[Error: Passwords<br/>do not match]
    ErrorMismatch --> CheckForced

    CheckMatch -->|Yes| CheckSameAsOld{New = Old<br/>password?}

    CheckSameAsOld -->|Yes| ErrorSame[Error: New password must<br/>be different from current]
    ErrorSame --> CheckForced

    CheckSameAsOld -->|No| HashNewPassword[Hash new password:<br/>password_hash<br/>PASSWORD_BCRYPT]

    HashNewPassword --> UpdatePassword[UPDATE staf SET:<br/>- kata_laluan = hashed<br/>- is_first_login = 0<br/>WHERE ID_staf = ?]

    UpdatePassword --> ClearSession[Clear current session]

    ClearSession --> SuccessMessage[Success:<br/>Password changed<br/>Please login again]

    SuccessMessage --> RedirectToLogin[Redirect to login.php]

    RedirectToLogin --> End2([Must re-login])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#10b981,color:#fff
    style ErrorEmpty fill:#ef4444,color:#fff
    style ErrorOldWrong fill:#ef4444,color:#fff
    style ErrorLength fill:#ef4444,color:#fff
    style ErrorMismatch fill:#ef4444,color:#fff
    style ErrorSame fill:#ef4444,color:#fff
```

---

## 9. Department Management Flow

### Add/Edit/Delete Department Flowchart

```mermaid
flowchart TD
    Start([Admin manages<br/>departments]) --> CheckAuth{is_admin<br/>= 1?}

    CheckAuth -->|No| AccessDenied[Error: Access denied]
    AccessDenied --> End1([Unauthorized])

    CheckAuth -->|Yes| LoadDepts[Query:<br/>SELECT * FROM jabatan<br/>ORDER BY nama_jabatan]

    LoadDepts --> DisplayDepts[Display department list<br/>with staff count]

    DisplayDepts --> Action{Admin<br/>action?}

    Action -->|Add| ShowAddForm[Display Add Form]
    Action -->|Edit| SelectDept[Select department]
    Action -->|Delete| SelectForDelete[Select to delete]

    ShowAddForm --> EnterName[Enter nama_jabatan]

    EnterName --> ValidateAdd{Name<br/>filled?}

    ValidateAdd -->|No| ErrorEmptyAdd[Error: Name required]
    ErrorEmptyAdd --> ShowAddForm

    ValidateAdd -->|Yes| CheckDuplicate{Name<br/>exists?}

    CheckDuplicate -->|Yes| ErrorDuplicate[Error: Department<br/>already exists]
    ErrorDuplicate --> ShowAddForm

    CheckDuplicate -->|No| InsertDept[INSERT INTO jabatan<br/>nama_jabatan, created_at]

    InsertDept --> SuccessAdd[Success:<br/>Department created]
    SuccessAdd --> End2([Department added])

    SelectDept --> ShowEditForm[Display edit form<br/>with current name]

    ShowEditForm --> ModifyName[Modify nama_jabatan]

    ModifyName --> ValidateEdit{Name<br/>filled?}

    ValidateEdit -->|No| ErrorEmptyEdit[Error: Name required]
    ErrorEmptyEdit --> ShowEditForm

    ValidateEdit -->|Yes| UpdateDept[UPDATE jabatan<br/>SET nama_jabatan = ?<br/>WHERE ID_jabatan = ?]

    UpdateDept --> SuccessEdit[Success:<br/>Department updated]
    SuccessEdit --> End3([Department updated])

    SelectForDelete --> CheckStaff[Check staff count:<br/>SELECT COUNT(*)<br/>FROM staf<br/>WHERE ID_jabatan = ?]

    CheckStaff --> HasStaff{Staff count<br/>> 0?}

    HasStaff -->|Yes| WarnStaff[Warning:<br/>XX staff will become<br/>unassigned ID_jabatan=NULL]
    HasStaff -->|No| ConfirmDelete{Confirm<br/>delete?}

    WarnStaff --> ConfirmWithStaff{Confirm delete<br/>anyway?}

    ConfirmWithStaff -->|No| DisplayDepts
    ConfirmDelete -->|No| DisplayDepts

    ConfirmWithStaff -->|Yes| DeleteDept[DELETE FROM jabatan<br/>WHERE ID_jabatan = ?<br/>FK ON DELETE SET NULL]
    ConfirmDelete -->|Yes| DeleteDept

    DeleteDept --> SuccessDelete[Success:<br/>Department deleted<br/>Staff unassigned if any]

    SuccessDelete --> End4([Department deleted])

    style Start fill:#10b981,color:#fff
    style End1 fill:#ef4444,color:#fff
    style End2 fill:#10b981,color:#fff
    style End3 fill:#10b981,color:#fff
    style End4 fill:#f59e0b,color:#fff
    style ErrorDuplicate fill:#ef4444,color:#fff
```

---

## 10. Telegram Notification Flow

### Notification System Flowchart

```mermaid
flowchart TD
    Start([System Event<br/>Triggered]) --> EventType{Event<br/>Type?}

    EventType -->|New Request| NewRequestEvent[Staff submitted<br/>new request]
    EventType -->|Request Approved| ApprovedEvent[Admin approved<br/>request]
    EventType -->|Request Rejected| RejectedEvent[Admin rejected<br/>request]
    EventType -->|Low Stock| LowStockEvent[Stock level ≤ 10]
    EventType -->|Monthly Reminder| MonthlyEvent[First Tuesday<br/>of month, 9 AM]

    NewRequestEvent --> FormatNewMsg[Format message:<br/>🔔 PERMOHONAN BARU<br/>ID: XXX<br/>Pemohon: NAME<br/>Jabatan: DEPT<br/>Tarikh: DATE<br/>Items: N items]

    ApprovedEvent --> FormatApprovedMsg[Format message:<br/>✅ DILULUSKAN<br/>ID: XXX<br/>Pemohon: NAME<br/>Pelulus: ADMIN<br/>Tarikh: DATE]

    RejectedEvent --> FormatRejectedMsg[Format message:<br/>❌ DITOLAK<br/>ID: XXX<br/>Pemohon: NAME<br/>Pelulus: ADMIN<br/>Sebab: REASON]

    LowStockEvent --> FormatLowStockMsg[Format message:<br/>⚠️ STOK RENDAH<br/>Produk: XXX<br/>Baki: YY units<br/>Sila tambah stok]

    MonthlyEvent --> FormatMonthlyMsg[Format message:<br/>📅 PERINGATAN BULANAN<br/>Sila semak stok<br/>dan buat pembelian<br/>untuk bulan ini]

    FormatNewMsg --> PrepareAPI[Prepare Telegram API call]
    FormatApprovedMsg --> PrepareAPI
    FormatRejectedMsg --> PrepareAPI
    FormatLowStockMsg --> PrepareAPI
    FormatMonthlyMsg --> PrepareAPI

    PrepareAPI --> GetConfig[Get config:<br/>- BOT_TOKEN<br/>- CHAT_ID group]

    GetConfig --> BuildURL[Build URL:<br/>https://api.telegram.org/bot<br/>BOT_TOKEN/sendMessage]

    BuildURL --> SetPayload[Set payload:<br/>- chat_id<br/>- text message<br/>- parse_mode HTML]

    SetPayload --> SendRequest[Send POST request<br/>via cURL/file_get_contents]

    SendRequest --> CheckResponse{Response<br/>ok = true?}

    CheckResponse -->|Yes| LogSuccess[Optional: Log success<br/>to file/database]
    CheckResponse -->|No| LogError[Log error:<br/>- Error message<br/>- Timestamp<br/>- Event type]

    LogSuccess --> End1([Notification sent ✅])

    LogError --> RetryDecision{Retry<br/>attempt < 3?}

    RetryDecision -->|Yes| WaitRetry[Wait 5 seconds]
    RetryDecision -->|No| FinalError[Give up<br/>Notification failed<br/>Admin must check manually]

    WaitRetry --> SendRequest
    FinalError --> End2([Notification failed ❌])

    style Start fill:#10b981,color:#fff
    style End1 fill:#10b981,color:#fff
    style End2 fill:#ef4444,color:#fff
    style LogError fill:#f59e0b,color:#fff
```

---

## Flowchart Symbols Legend

### Standard Flowchart Symbols Used

```mermaid
flowchart TD
    A([Terminal/Start/End<br/>Oval shape])
    B[Process/Action<br/>Rectangle]
    C{Decision Point<br/>Diamond}
    D[(Database Operation<br/>Cylinder)]
    E[/Input/Output<br/>Parallelogram/]

    A --> B
    B --> C
    C -->|Yes| D
    C -->|No| E
    D --> E
```

### Symbol Meanings:

| Symbol | Shape | Meaning | Example |
|--------|-------|---------|---------|
| `([...])` | Oval | Start/End point | `([User Login])` |
| `[...]` | Rectangle | Process/Action | `[Validate Input]` |
| `{...}` | Diamond | Decision/Branch | `{Password correct?}` |
| `[(...)]` | Cylinder | Database operation | `[(Query database)]` |
| `[/..../]` | Parallelogram | Input/Output | `[/Display form/]` |

### Color Coding (in diagrams):

- **Green (`#10b981`)**: Start points, Success states
- **Red (`#ef4444`)**: Errors, End/Fail states
- **Blue (`#3b82f6`)**: Staff-related processes
- **Orange (`#f59e0b`)**: Admin-related processes, Warnings
- **Purple (`#8b5cf6`)**: Database transactions

---

## Usage Guide

### For Internship Documentation:

1. **System Overview**: Use Flowchart #1 to explain overall system architecture
2. **User Authentication**: Use Flowchart #2 for security explanation
3. **Core Workflows**: Use Flowcharts #3-4 for main business processes
4. **Data Management**: Use Flowcharts #5-6 for CRUD operations
5. **Reporting**: Use Flowchart #7 for analytics features
6. **Integration**: Use Flowchart #10 for external API integration

### Reading the Flowcharts:

- Follow arrows from top to bottom
- Diamond shapes are decision points (Yes/No branches)
- Rectangles are actions/processes
- Cylinders represent database operations
- Ovals mark start and end points

---

**Document Version:** 1.0
**Generated:** 30 December 2025
**Database:** storeroom_db (7 tables, 8 FK constraints)
**System:** Sistem Pengurusan Bilik Stor dan Inventori MPK
**Status:** Production-Ready, Cleaned & Optimized
