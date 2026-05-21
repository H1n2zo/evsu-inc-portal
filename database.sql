CREATE DATABASE IF NOT EXISTS hajemi_inc;
USE hajemi_inc;

CREATE TABLE IF NOT EXISTS departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE,
    student_number VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','registrar','department_head','instructor','student') NOT NULL,
    dept_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL,
    subject_name VARCHAR(150) NOT NULL,
    units INT NOT NULL,
    dept_id INT,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS inc_applications (
    app_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    semester VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    status VARCHAR(60) DEFAULT 'Pending Instructor Evaluation',
    fee_computed DECIMAL(10,2),
    or_number VARCHAR(100),
    or_receipt_path VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
);

CREATE TABLE IF NOT EXISTS workflow_steps (
    step_id INT AUTO_INCREMENT PRIMARY KEY,
    app_id INT NOT NULL,
    step_number INT NOT NULL,
    acting_user_id INT,
    action VARCHAR(100),
    remarks TEXT,
    acted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (app_id) REFERENCES inc_applications(app_id) ON DELETE CASCADE,
    FOREIGN KEY (acting_user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    app_id INT NOT NULL,
    instructor_id INT,
    resolved_grade VARCHAR(10),
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    posted_by_admin_id INT DEFAULT NULL,
    FOREIGN KEY (app_id) REFERENCES inc_applications(app_id),
    FOREIGN KEY (instructor_id) REFERENCES users(user_id),
    FOREIGN KEY (posted_by_admin_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    target_table VARCHAR(100),
    target_id INT,
    ip_address VARCHAR(50),
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

INSERT INTO departments (dept_name) VALUES
('College of Engineering'),
('College of Information Technology'),
('College of Education'),
('College of Business'),
('College of Arts and Sciences');

INSERT INTO subjects (subject_code, subject_name, units, dept_id) VALUES
('CS101','Introduction to Programming',3,2),
('CS201','Data Structures',3,2),
('IT101','Web Development',3,2),
('ENG101','Engineering Mathematics',3,1),
('ED101','Principles of Teaching',3,3);

INSERT INTO users (name, email, username, student_number, password_hash, role, dept_id) VALUES
('Admin User','admin@evsu.edu.ph','admin',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',NULL),
('Registrar Office','registrar@evsu.edu.ph','registrar',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','registrar',NULL),
('Dr. Maria Santos','depthead@evsu.edu.ph','depthead',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','department_head',2),
('Prof. Juan Dela Cruz','instructor@evsu.edu.ph','instructor',NULL,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','instructor',2),
('Anna Reyes','student@evsu.edu.ph',NULL,'2024-0001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',NULL);
