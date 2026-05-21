<?php
require_once '../includes/auth.php';
requireRole('admin');
$user = getCurrentUser();
$db   = getDB();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '') ?: null;
        $studNum  = trim($_POST['student_number'] ?? '') ?: null;
        $role     = $_POST['role'] ?? '';
        $deptId   = $_POST['dept_id'] ? (int)$_POST['dept_id'] : null;
        $pass     = $_POST['password'] ?? '';
        $roles    = ['admin','registrar','department_head','instructor','student'];

        if (!$name || !$email || !$role || !$pass || !in_array($role, $roles)) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $chk = $db->prepare('SELECT user_id FROM users WHERE email=?');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $error = 'Email address already exists.';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $ins  = $db->prepare('INSERT INTO users (name,email,username,student_number,password_hash,role,dept_id) VALUES (?,?,?,?,?,?,?)');
                $ins->execute([$name,$email,$username,$studNum,$hash,$role,$deptId]);
                auditLog('Admin created user', 'users', $db->lastInsertId());
                $success = 'User created successfully.';
            }
        }
    } elseif ($action === 'deactivate') {
        $uid = (int)$_POST['user_id'];
        $db->prepare('UPDATE users SET is_active=0 WHERE user_id=?')->execute([$uid]);
        auditLog('Admin deactivated user', 'users', $uid);
        $success = 'User deactivated.';
    } elseif ($action === 'activate') {
        $uid = (int)$_POST['user_id'];
        $db->prepare('UPDATE users SET is_active=1 WHERE user_id=?')->execute([$uid]);
        auditLog('Admin activated user', 'users', $uid);
        $success = 'User activated.';
    }
}

$users = $db->query("
    SELECT u.*, d.dept_name FROM users u
    LEFT JOIN departments d ON u.dept_id = d.dept_id
    ORDER BY u.role, u.name
")->fetchAll();
$departments = $db->query('SELECT * FROM departments ORDER BY dept_name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management – EVSU-OC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; Administrator
        <a href="../logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
<aside class="sidebar">
    <a class="sidebar-item" href="admin.php">Dashboard</a>
    <a class="sidebar-item active" href="admin_users.php">User Management</a>
    <a class="sidebar-item" href="admin_applications.php">All Applications</a>
    <a class="sidebar-item" href="admin_audit.php">Audit Logs</a>
</aside>
<div class="main-content">
    <div class="page-title">User Management</div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card" style="margin-bottom:24px;">
        <strong style="color:var(--maroon);display:block;margin-bottom:16px;">Add New User</strong>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
                <div class="form-group" style="margin:0;">
                    <label>Full Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Username (employees)</label>
                    <input type="text" name="username" class="form-control">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Student Number (students)</label>
                    <input type="text" name="student_number" class="form-control">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Role *</label>
                    <select name="role" class="form-control" required>
                        <option value="">— Select —</option>
                        <option value="admin">Admin</option>
                        <option value="registrar">Registrar</option>
                        <option value="department_head">Department Head</option>
                        <option value="instructor">Instructor</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Department</label>
                    <select name="dept_id" class="form-control">
                        <option value="">— None —</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['dept_id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Password * (min 8 chars)</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:16px;">Create User</button>
        </form>
    </div>

    <div class="card">
        <strong style="color:var(--maroon);display:block;margin-bottom:14px;">All Users</strong>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Student No.</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= ucfirst(str_replace('_',' ',$u['role'])) ?></td>
                        <td><?= htmlspecialchars($u['dept_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($u['student_number'] ?? '—') ?></td>
                        <td><span class="badge <?= $u['is_active'] ? 'badge-approved' : 'badge-rejected' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <?php if ($u['user_id'] !== $user['user_id']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="<?= $u['is_active'] ? 'deactivate' : 'activate' ?>">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <button class="btn btn-sm <?= $u['is_active'] ? 'btn-outline' : 'btn-primary' ?>"><?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</body>
</html>
