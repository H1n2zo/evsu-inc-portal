# EVSU-OC INC Form System
## Setup Instructions (XAMPP)

### 1. Place files
Copy the `hajemi` folder to: `C:/xampp/htdocs/hajemi`

### 2. Import database
- Open phpMyAdmin: http://localhost/phpmyadmin
- Click **Import** tab
- Select `database.sql` and click **Go**

### 3. Test accounts (all passwords: `password`)
| Role | Login Field | Value |
|------|-------------|-------|
| Admin | Username | admin |
| Registrar | Username | registrar |
| Department Head | Username | depthead |
| Instructor | Username | instructor |
| Student | Student ID | 2024-0001 |

### 4. Open the app
Go to: http://localhost/hajemi/

### 5. Folder permissions
Make sure `uploads/receipts/` is writable (it already exists in the package).

### Notes
- PHP 7.4+ required
- MySQL 5.7+ / MariaDB 10.x
- Default DB credentials: root / (no password) on localhost
- To change DB credentials, edit `includes/db.php`
