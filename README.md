# EVSU-OC INC Form Portal
## PHP + MySQL System | XAMPP Localhost Setup Guide

---

## 📋 System Overview

A complete web-based Incomplete Grade (INC) completion portal for Eastern Visayas State University – Ormoc Campus (EVSU-OC), built with:
- **PHP 8+** (native, no framework)
- **MySQL** via PDO
- **XAMPP** as localhost server
- Maroon & gold EVSU design system (matching the provided HTML prototype)

---

## 🚀 Quick Setup (XAMPP)

### Step 1 — Copy Files
Place the `evsu_inc_portal/` folder inside your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\evsu_inc_portal\
```

### Step 2 — Import Database
1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open `http://localhost/phpmyadmin`
3. Create a new database named **`evsu_inc_portal`**
4. Click **Import** → Choose `config/database.sql` → Click **Go**

### Step 3 — Configure Database (if needed)
Edit `config/db.php` if your MySQL credentials differ:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'evsu_inc_portal');
define('DB_USER', 'root');
define('DB_PASS', '');   // Default XAMPP = empty
```

### Step 4 — Open the Portal
Visit: `http://localhost/evsu_inc_portal/`

---

## 🔑 Default Login

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `password` |

> ⚠️ **Change the admin password immediately after first login!**
> Go to phpMyAdmin → `users` table → update `password_hash` with a bcrypt hash.
>
> Generate a new hash in PHP:
> ```php
> echo password_hash('YourNewPassword', PASSWORD_BCRYPT, ['cost' => 12]);
> ```

---

## 👤 User Roles & Access

### Admin
- Full system access — ONLY admin sees admin panel
- RBAC configuration (assign employee roles)
- Module control (enable/disable features)
- System settings (academic year, session, SMTP)
- Immutable audit logs
- Approve/reject/disable employee accounts

### Employee (Instructor / Dept. Head / Registrar)
- A single employee account can hold **multiple roles** (e.g., Instructor + Dept. Head)
- After login, employees with multiple roles see a **role switcher** in the sidebar
- Active role determines which dashboard views and actions are available
- Role switching is logged in the audit trail

### Student
- Register and login with Student ID
- File new INC applications (auto fee: units × ₱50)
- Upload payment receipt (JPG/PNG/PDF, max 5MB)
- Track application progress through all 7 steps

---

## 🔄 7-Step Workflow

```
Step 1: Student files INC application
Step 2: Instructor enters resolved grade + e-signs
Step 3: Dept. Head reviews + approves/rejects + e-signs
Step 4: Student pays at cashier, uploads receipt
Step 5: Registrar verifies O.R. (split-view panel)
Step 6: Registrar posts final grade + e-signs
Step 7: Resolved — PDF archive generated
```

---

## 📁 Directory Structure

```
evsu_inc_portal/
├── config/
│   ├── db.php              Database connection (PDO)
│   └── database.sql        MySQL schema + seed data
├── includes/
│   ├── auth.php            Session, login, CSRF, audit log
│   ├── head.php            Shared HTML <head> + global CSS
│   ├── admin_sidebar.php   Admin navigation sidebar
│   ├── employee_sidebar.php Employee sidebar with role switcher
│   └── student_sidebar.php Student navigation sidebar
├── admin/
│   ├── dashboard.php       Admin overview + stats
│   ├── users.php           User management + role assignment
│   ├── rbac.php            RBAC configuration overview
│   ├── modules.php         Module control (toggle features)
│   ├── settings.php        System settings (SMTP, academic period)
│   ├── logs.php            Audit logs (read-only)
│   ├── applications.php    All INC applications
│   ├── application_view.php Full application detail
│   └── user_view.php       Individual user detail
├── employee/
│   ├── dashboard.php       Role-aware employee dashboard
│   ├── applications.php    Applications assigned to employee
│   ├── application_view.php View + act on application (sign/approve)
│   └── switch_role.php     POST handler for role switching
├── student/
│   ├── dashboard.php       Student overview
│   ├── apply.php           New INC application form
│   ├── applications.php    Student's application list
│   └── application_view.php View application + upload receipt
├── assets/
│   └── uploads/            Receipt file uploads (auto-created)
├── index.php               Landing page (role chooser)
├── login.php               Login (student / employee)
├── register.php            Account registration
└── logout.php              Session destroy + redirect
```

---

## 🔒 Security Features

- **bcrypt password hashing** (cost factor 12)
- **CSRF tokens** on all forms
- **PDO prepared statements** (SQL injection prevention)
- **XSS protection** via `htmlspecialchars()` on all output
- **Session timeout** (configurable, default 30 minutes)
- **Role-based access guards** (`requireAdmin()`, `requireEmployee()`, `requireLogin()`)
- **403 errors** for unauthorized role access
- **Immutable audit logs** (no edit/delete on log table)

---

## ⚙️ Admin Configuration

### RBAC (Role Assignment)
1. Go to **Admin → Users & Roles**
2. Click **Edit Roles** on any employee account
3. Check/uncheck roles: Instructor, Dept. Head, Registrar
4. Employees with multiple roles can switch in their sidebar

### Module Control
Go to **Admin → Module Control** to enable/disable:
- INC Form Filing (Student)
- Grade Input (Instructor)
- Dept. Head Approval
- Receipt Upload (Student)
- O.R. Verification Panel (Registrar)
- Grade Posting (Registrar)
- PDF Generation (Auto)
- Email Notifications (PHPMailer - requires setup)

### Email Notifications (Optional)
1. Go to **Admin → Settings**
2. Fill in SMTP credentials (Google Workspace App Password recommended)
3. Enable **Email Notifications** in Module Control
4. Install PHPMailer: `composer require phpmailer/phpmailer`

---

## 📝 Notes

- **File uploads** go to `assets/uploads/` — ensure it's writable (`chmod 755`)
- **Uploads folder** is auto-created on first receipt upload
- The **uploads folder** should be protected in production (add `.htaccess` to prevent direct PHP execution)
- For production deployment, use HTTPS and set `secure` cookie flag in `auth.php`

---

*Built for EVSU-OC INC Form System — Academic Year 2025-2026*
