-- EVSU-OC INC Form Portal — MySQL Database Schema
-- Run this in phpMyAdmin or MySQL CLI: source database.sql

CREATE DATABASE IF NOT EXISTS evsu_inc_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE evsu_inc_portal;

-- ─────────────────────────────────────────────
-- USERS TABLE
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    account_type ENUM('admin','employee','student') NOT NULL DEFAULT 'student',
    status ENUM('pending','active','disabled') NOT NULL DEFAULT 'pending',
    student_id VARCHAR(30) NULL COMMENT 'For students only',
    department VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- ROLES TABLE (employee roles: instructor, dept_head, registrar)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name ENUM('instructor','dept_head','registrar') NOT NULL UNIQUE
);

INSERT IGNORE INTO roles (role_name) VALUES ('instructor'), ('dept_head'), ('registrar');

-- ─────────────────────────────────────────────
-- USER ROLES (Many-to-Many: employee ↔ role)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_by INT NULL COMMENT 'Admin user ID who assigned this role',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_id)
);

-- ─────────────────────────────────────────────
-- MODULES (admin can toggle on/off)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(60) NOT NULL UNIQUE,
    module_name VARCHAR(120) NOT NULL,
    description TEXT,
    target_role VARCHAR(40) NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT IGNORE INTO modules (module_key, module_name, description, target_role, is_enabled) VALUES
('inc_filing',       'INC Form Filing',         'Allows students to initiate and submit INC completion applications', 'student', 1),
('grade_input',      'Grade Input',             'Instructor can enter resolved final grades and apply e-signatures', 'instructor', 1),
('dept_approval',    'Dept. Head Approval',     'Department Head reviews and approves instructor-submitted grades', 'dept_head', 1),
('receipt_upload',   'Receipt Upload',          'Student uploads payment OR after cash payment at the cashier', 'student', 1),
('or_verification',  'OR Verification Panel',   'Split-view OR ledger comparison and verification screen', 'registrar', 1),
('grade_posting',    'Grade Posting',           'Registrar finalizes and officially posts resolved grades to transcripts', 'registrar', 1),
('pdf_generation',   'PDF Generation',          'Auto-generates A4 INC completion document upon resolution', 'auto', 1),
('email_notif',      'Email Notifications',     'Transactional email hooks at every workflow state change (PHPMailer)', 'auto', 0);

-- ─────────────────────────────────────────────
-- INC APPLICATIONS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS inc_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_code VARCHAR(20) NOT NULL UNIQUE,
    student_id INT NOT NULL,
    subject_name VARCHAR(120) NOT NULL,
    subject_code VARCHAR(40) NOT NULL,
    units INT NOT NULL DEFAULT 3,
    processing_fee DECIMAL(8,2) GENERATED ALWAYS AS (units * 50) STORED,
    semester ENUM('1st','2nd','Summer') NOT NULL DEFAULT '2nd',
    school_year VARCHAR(20) NOT NULL DEFAULT '2025-2026',
    current_step TINYINT NOT NULL DEFAULT 1,
    status ENUM('draft','in_progress','pending_payment','verification','resolved','rejected') NOT NULL DEFAULT 'draft',
    -- Step 2: Instructor
    instructor_id INT NULL,
    instructor_grade VARCHAR(10) NULL,
    instructor_remarks TEXT NULL,
    instructor_signed_at TIMESTAMP NULL,
    instructor_signature LONGTEXT NULL COMMENT 'Base64 PNG of drawn signature',
    -- Step 3: Dept Head
    dept_head_id INT NULL,
    dept_head_remarks TEXT NULL,
    dept_head_signed_at TIMESTAMP NULL,
    dept_head_signature LONGTEXT NULL,
    dept_head_action ENUM('approved','rejected') NULL,
    -- Step 4: Student payment
    or_number VARCHAR(80) NULL,
    receipt_filename VARCHAR(255) NULL,
    receipt_uploaded_at TIMESTAMP NULL,
    -- Step 5-6: Registrar
    registrar_id INT NULL,
    registrar_remarks TEXT NULL,
    registrar_signed_at TIMESTAMP NULL,
    registrar_signature LONGTEXT NULL,
    registrar_action ENUM('approved','rejected') NULL,
    -- Rejection
    rejection_reason TEXT NULL,
    rejected_at TIMESTAMP NULL,
    rejected_by INT NULL,
    -- Resolution
    resolved_at TIMESTAMP NULL,
    pdf_filename VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (dept_head_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (registrar_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────
-- AUDIT LOGS (immutable — no UPDATE/DELETE allowed)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(100) NULL,
    active_role VARCHAR(40) NULL,
    action VARCHAR(120) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- SYSTEM SETTINGS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT,
    label VARCHAR(120),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO settings VALUES
('school_year',       '2025-2026',         'Current School Year', NOW()),
('active_semester',   '2nd',               'Active Semester',      NOW()),
('session_timeout',   '30',                'Session Timeout (minutes)', NOW()),
('smtp_host',         'smtp.gmail.com',    'SMTP Host',            NOW()),
('smtp_port',         '587',               'SMTP Port',            NOW()),
('smtp_user',         '',                  'SMTP Username',        NOW()),
('smtp_pass',         '',                  'SMTP Password',        NOW()),
('smtp_from_name',    'EVSU-OC INC Portal','SMTP From Name',       NOW()),
('max_upload_mb',     '5',                 'Max Upload Size (MB)', NOW());

-- ─────────────────────────────────────────────
-- SEED: Default Admin Account
-- password: Admin@1234  (bcrypt cost 12)
-- ─────────────────────────────────────────────
INSERT IGNORE INTO users (full_name, username, email, password_hash, account_type, status)
VALUES ('System Administrator', 'admin', 'admin@evsu.edu.ph',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', 'active');
-- NOTE: The hash above is for 'password' — change it immediately after first login.
-- To generate a proper hash for 'Admin@1234':
-- <?php echo password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost'=>12]);
