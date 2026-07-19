# Enterprise Distribution Management System (DMS) — V2

> A secure, role-based distribution management platform built on a custom MVC framework in PHP 8.x.

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Table of Contents

- [Overview](#overview)
- [Architecture Overview](#architecture-overview)
- [Core Modules & Features](#core-modules--features)
- [Database Schema Highlights](#database-schema-highlights)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Security](#security)

---

## Overview

The **Enterprise DMS V2** is a full-featured distribution management platform designed for multi-tenant business operations. It provides a centralized hub for managing warehouse inventory, customer relationships, sales orders, procurement, and user access — all behind a strict Role-Based Access Control (RBAC) layer.

The system is architected around a **custom PHP MVC framework** with a front-controller pattern, a PSR-4-compliant autoloader, and a lightweight but extensible router — making it free from third-party framework dependencies while remaining structured and maintainable at enterprise scale.

---

## Architecture Overview

### MVC Pattern

The application enforces a strict separation of concerns:

| Layer | Location | Responsibility |
|---|---|---|
| **Model** | `models/` | Business logic, data access via PDO |
| **View** | `views/` | Presentation templates (PHP + Bootstrap 5) |
| **Controller** | `controllers/` | Request handling, input validation, response orchestration |

### Front Controller & Router

All HTTP requests are funnelled through a single entry point, `index_v2.php`, which bootstraps a PSR-4 autoloader and delegates to `Core\Router`. The router supports both `GET` and `POST` verbs with dynamic URI parameter extraction (`{id}` placeholders) and a first-class middleware pipeline per route.

```
HTTP Request
     │
     ▼
index_v2.php  (Front Controller + PSR-4 Autoloader)
     │
     ▼
Core\Router::dispatch()
     │
     ├── Middleware Pipeline  (auth guards, RBAC checks)
     │
     └── Controller::action()  →  Model  →  View
```

### Role-Based Access Control (RBAC)

Every authenticated user is bound to a `role` that is scoped to a `company`. Controllers enforce authorization at the action level, ensuring that users can only access the modules and data relevant to their assigned role.

---

## Core Modules & Features

### 🔐 User Authentication & RBAC

- Session-based authentication with secure password hashing (`password_hash` / `password_verify`).
- Role definitions are **company-scoped** — the same platform serves multiple tenants without data leakage.
- Role-based dashboard isolation: each role (e.g., `Warehouse Manager`, `Sales Representative`) is presented a tailored dashboard surface with only permitted modules visible.
- Seeded default roles: `Warehouse Manager`, `Sales Representative`, `Customer Support`, `Product Manager`.

### 📦 Inventory Management (WMS — Warehouse Management System)

- Receive stock into the warehouse against a supplier and product via `InventoryController`.
- Context-aware dropdowns dynamically populate available products and suppliers, preventing orphaned foreign key references.
- Real-time stock level tracking; inventory is atomically deducted at order fulfilment time.
- Routes: `GET /inventory`, `GET /inventory/receive`, `POST /inventory/store`.

### 👥 Customer Relationship Management (CRM)

- Full customer onboarding workflow with structured data capture (contact details, address, retailer classification).
- Customer listing with sortable, searchable records.
- Designed to support retailer and end-customer segmentation for downstream sales analytics.
- Routes: `GET /customers`, `GET /customers/create`, `POST /customers/store`.

### 🛒 Sales Order Processing

- End-to-end order lifecycle: create → validate → persist → deduct inventory — all within a single **atomic database transaction**.
- Automated pricing applied at order creation based on the Product Information Management (PIM) catalogue.
- Order lines are linked to customers, products, and the authenticated user for full auditability.
- Inventory deduction is performed within the same transaction as order insertion — preventing overselling under concurrent load.
- Routes: `GET /orders`, `GET /orders/create`, `POST /orders/store`.

### 🏷️ Product Information Management (PIM)

- Centralised product catalogue with create, list, and delete operations.
- Products carry pricing metadata consumed by the Sales module for automated order valuation.
- Routes: `GET /products`, `GET /products/create`, `POST /products/store`, `POST /products/delete/{id}`.

### 🏢 User & Tenant Management

- Admin-level user creation with role assignment scoped to the active company.
- Multi-tenant company isolation enforced at the model and query level.
- Routes: `GET /users`, `GET /users/create`, `POST /users/store`.

---

## Database Schema Highlights

The schema (`database/migrations/schema.sql`) is designed for **multi-tenant readiness** from the ground up:

- **`companies` table** acts as the root tenant anchor. All domain entities (`roles`, `users`, `products`, `customers`, `orders`, `inventory`) carry a `company_id` foreign key, enforcing strict data isolation between tenants.
- **Foreign key constraints** are declared on every relational boundary (e.g., `orders → customers`, `order_items → products`, `inventory → products → suppliers`) to guarantee referential integrity at the database engine level.
- **Transactional integrity** is applied to all multi-statement write operations (e.g., inserting an order and its line items while simultaneously deducting stock quantities) using PDO transactions with `beginTransaction()`, `commit()`, and `rollBack()`.
- **UUID columns** are present on core tenant entities for globally unique identifiers suitable for distributed or API-facing scenarios.
- **`INSERT IGNORE`** is used strategically in seeders to make them safely idempotent and re-runnable.

---

## Tech Stack

| Component | Technology |
|---|---|
| **Language** | PHP 8.x (`strict_types=1`) |
| **Database Abstraction** | PDO (PHP Data Objects) with prepared statements |
| **Frontend Framework** | Bootstrap 5 |
| **Database** | MySQL 8.x |
| **Local Environment** | XAMPP (Apache + MySQL + PHP) |
| **Routing** | Custom `Core\Router` (no external dependencies) |
| **Autoloading** | Custom PSR-4 `spl_autoload_register` |

---

## Project Structure

```
dms/
├── index_v2.php            # Front Controller (single entry point)
├── routes.php              # Centralized route definitions
│
├── core/
│   ├── Router.php          # HTTP router with middleware pipeline support
│   └── Database.php        # PDO singleton wrapper
│
├── config/
│   └── Database.php        # Database connection configuration
│
├── controllers/
│   ├── Foundation/         # Auth, Dashboard, User controllers
│   ├── WMS/                # Inventory management
│   ├── CRM/                # Customer management
│   ├── Sales/              # Order processing
│   ├── PIM/                # Product catalogue
│   ├── Procurement/        # Supplier & purchasing
│   ├── Accounting/         # Financial operations
│   ├── Logistics/          # Delivery & fulfilment
│   ├── BI/                 # Business intelligence & reporting
│   ├── RBAC/               # Role & permission management
│   └── Automation/         # Workflow automation
│
├── models/                 # Data models (mirrors controllers structure)
├── views/                  # PHP view templates (mirrors controllers structure)
│
└── database/
    ├── migrations/
    │   └── schema.sql      # Full database schema
    └── seeders/
        ├── seed_roles.php  # Seeds default operational roles
        ├── seed_admin.php  # Seeds the initial super-admin user
        └── seed_catalog.php # Seeds sample product catalogue
```

---

## Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (v8.x recommended) with Apache and MySQL services running.
- PHP 8.0 or higher.

### 1. Clone & Place the Project

```bash
# Place the project in your XAMPP web root
git clone <your-repo-url> C:/xampp/htdocs/dms
```

### 2. Configure the Database Connection

Edit `config/Database.php` and set your local MySQL credentials:

```php
// config/Database.php
private string $host     = '127.0.0.1';
private string $dbName   = 'dms';
private string $username = 'root';
private string $password = '';  // Default XAMPP password is empty
```

### 3. Create the Database & Run Migrations

Open **phpMyAdmin** (`http://localhost/phpmyadmin`), create a new database named `dms`, then import the schema:

```sql
-- In phpMyAdmin, select the 'dms' database and import:
database/migrations/schema.sql
```

Alternatively, run via MySQL CLI:

```bash
mysql -u root -p dms < C:/xampp/htdocs/dms/database/migrations/schema.sql
```

### 4. Run the Seeders

Open a terminal in the project root and execute the seeders in order:

```bash
# Step 1: Seed default company and operational roles
php database/seeders/seed_roles.php

# Step 2: Seed the super-admin user account
php database/seeders/seed_admin.php

# Step 3 (Optional): Seed a sample product catalogue for testing
php database/seeders/seed_catalog.php
```

### 5. Access the Application

Navigate to the application in your browser:

```
http://localhost/dms/index_v2.php
```

Log in with the admin credentials created by `seed_admin.php` (check the seeder output or the script itself for the default credentials).

---

## Security

### SQL Injection Prevention

All database interactions in the system use **PDO prepared statements with named parameter binding**. Raw string interpolation into SQL queries is strictly avoided throughout the codebase.

```php
// ✅ Correct — parameterized query, safe from injection
$stmt = $db->prepare(
    "SELECT id FROM roles WHERE name = :name AND company_id = :company_id LIMIT 1"
);
$stmt->execute([':name' => $roleName, ':company_id' => $companyId]);

// ❌ Never done — vulnerable to SQL injection
$stmt = $db->query("SELECT * FROM users WHERE email = '{$_POST['email']}'");
```

### Controller-Level Authorization

Every controller action that operates on sensitive data performs an **authorization check at the top of the method** before any business logic executes. Unauthenticated or insufficiently privileged requests are intercepted and redirected — they never reach model or database layer code.

The `Core\Router` supports a **middleware pipeline** per route, allowing authentication and role guards to be declared declaratively alongside route definitions for a clean, auditable security surface.

### Password Security

User passwords are stored exclusively as **bcrypt hashes** using PHP's native `password_hash(PASSWORD_BCRYPT)`. Plaintext passwords are never persisted or logged.

### Session Management

PHP sessions are initiated via `session_start()` at the front controller level. Session data is used to track the authenticated user's ID, role, and company scope — and is validated on every protected request.

---

> **DMS V2** — Built as a portfolio demonstration of enterprise-grade PHP application architecture, security practices, and modular domain-driven design.

## Changelog
- **2026-07-19**: Fixed Sales Rep UI layout, implemented strict inventory validation to prevent negative stock, and added System Admin user deletion.
- **2026-07-19**: Created the Sales Representative dashboard based on the `dist.html` blueprint template.
- **2026-07-19**: Added separated wholesale and retail pricing logic to the Product Information Management (PIM) module.
