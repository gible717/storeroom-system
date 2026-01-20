# Logical Architecture Diagram - Storeroom Management System

## Overview

This document provides the logical architecture diagram for the Storeroom Management System (Sistem Pengurusan Stor) developed for BTMPKL.

---

## Logical Architecture Diagram

**Use this as reference to recreate in Draw.io/PowerPoint:**

```
┌─────────────────────┐                                              ┌─────────────────────┐
│                     │                                              │                     │
│   External Service  │                                              │       Users         │
│                     │                                              │                     │
│  • Telegram Bot API │                                              │  • Staff            │
│  • SMTP Email       │                                              │  • Admin            │
│                     │                                              │  • Ketua Bahagian   │
│                     │                                              │                     │
└─────────┬───────────┘                                              └──────────┬──────────┘
          │                                                                     │
          │  API                                                                │
          │  Call                                                               │
          ▼                                                                     ▼
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                          │
│                            STOREROOM MANAGEMENT SYSTEM                                   │
│                                                                                          │
│  ┌────────────────────────────────────────────────────────────────────────────────────┐ │
│  │                              WEB (Presentation Layer)                               │ │
│  │                                                                                     │ │
│  │    • HTML5, CSS3, JavaScript          • Bootstrap 5 (UI Framework)                 │ │
│  │    • Chart.js (Dashboard)             • Responsive Design                          │ │
│  └────────────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                          │
│  ┌────────────────────────────────────────────────────────────────────────────────────┐ │
│  │                              APP (Application Layer)                                │ │
│  │                                                                                     │ │
│  │                                     Modules:                                        │ │
│  │    • Authentication & Authorization      • Request Management                       │ │
│  │    • Inventory Management                • User Management                          │ │
│  │    • Reporting (PDF/Excel)               • Notification System                      │ │
│  └────────────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                          │
│  ┌────────────────────────────────────────────────────────────────────────────────────┐ │
│  │                                DB (Data Layer)                                      │ │
│  │                                                                                     │ │
│  │    Schema:                                                                          │ │
│  │    • users              • items              • categories                           │ │
│  │    • requests           • request_items      • activity_log                         │ │
│  └────────────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                          │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Simplified Flow Diagram

```
┌─────────────────────┐         ┌─────────────────────────────────┐         ┌─────────────────────┐
│                     │         │                                 │         │                     │
│    User Actions     │         │    Storeroom Management System  │         │       Output        │
│                     │         │                                 │         │                     │
│  • Login            │         │  ┌───────────────────────────┐  │         │  • View Dashboard   │
│  • Submit Request   │────────▶│  │   Request Processing      │  │────────▶│  • Approval Status  │
│  • View Inventory   │         │  │   Inventory Management    │  │         │  • PDF/Excel Report │
│  • Generate Report  │         │  │   User Authentication     │  │         │  • Telegram Alert   │
│                     │         │  └───────────────────────────┘  │         │  • Email Notif.     │
│                     │         │                                 │         │                     │
└─────────────────────┘         └─────────────────────────────────┘         └─────────────────────┘
```

---

## Component Description

### 1. External Services
| Service | Purpose |
|---------|---------|
| Telegram Bot API | Send instant notifications to users |
| SMTP Email Server | Password reset and email alerts |

### 2. System Layers

| Layer | Technology | Function |
|-------|------------|----------|
| **WEB** | HTML5, CSS3, JS, Bootstrap 5 | User interface and interaction |
| **APP** | PHP 8.x | Business logic and processing |
| **DB** | MySQL 8.x | Data storage and retrieval |

### 3. Application Modules

| Module | Description |
|--------|-------------|
| Authentication | Login, logout, password reset, session management |
| Authorization | Role-based access control (Staff, Admin, Ketua) |
| Request Management | Create, view, approve/reject inventory requests |
| Inventory Management | CRUD operations for stock items |
| User Management | Manage user accounts and roles |
| Reporting | Generate PDF and Excel reports |
| Notification | Telegram and email alerts |

### 4. Database Schema

| Table | Purpose |
|-------|---------|
| `users` | User accounts and credentials |
| `items` | Inventory items and stock levels |
| `categories` | Item categories |
| `requests` | Inventory request records |
| `request_items` | Items in each request |
| `activity_log` | System audit trail |

---

## User Access

| User Role | Access Level |
|-----------|--------------|
| Staff | Submit requests, view own history |
| Admin | Manage inventory, approve requests, reports |
| Ketua Bahagian | Full system access, user management |

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5, Chart.js |
| Backend | PHP 8.x |
| Database | MySQL 8.x |
| Web Server | Apache |
| Notifications | Telegram Bot API, PHPMailer |
| Reports | FPDF, PhpSpreadsheet |

---

*Reference document for creating visual diagram in Draw.io/PowerPoint*
