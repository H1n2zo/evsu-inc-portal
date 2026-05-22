<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ── Secret access key ──────────────────────────────────────────────────────────
// Change this to something only you know. Access: register.php?key=YOUR_KEY
define('REGISTER_SECRET', 'evsu-oc-secret-2025');

startSession();

$key = $_GET['key'] ?? $_POST['key'] ?? '';
if ($key !== REGISTER_SECRET) {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>');
}

$db = getDB();
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $role       = $_POST['role'] ?? '';
    $username   = trim($_POST['username'] ?? '') ?: null;
    $studentNum = trim($_POST['student_number'] ?? '') ?: null;
    $deptId     = !empty($_POST['dept_id']) ? (int)$_POST['dept_id'] : null;
    $pass       = $_POST['password'] ?? '';
    $passConf   = $_POST['password_confirm'] ?? '';

    $validRoles = ['admin','registrar','department_head','instructor','student'];

    if (!$name || !$email || !$role || !$pass) {
        $error = 'All required fields must be filled in.';
    } elseif (!in_array($role, $validRoles)) {
        $error = 'Invalid role selected.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($pass) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($pass !== $passConf) {
        $error = 'Passwords do not match.';
    } elseif ($role === 'student' && !$studentNum) {
        $error = 'Student number is required for student accounts.';
    } elseif (in_array($role, ['admin','registrar','department_head','instructor']) && !$username) {
        $error = 'Username is required for employee accounts.';
    } else {
        // Check duplicates
        $chkEmail = $db->prepare('SELECT user_id FROM users WHERE email = ?');
        $chkEmail->execute([$email]);
        if ($chkEmail->fetch()) {
            $error = 'An account with this email already exists.';
        } elseif ($username) {
            $chkUser = $db->prepare('SELECT user_id FROM users WHERE username = ?');
            $chkUser->execute([$username]);
            if ($chkUser->fetch()) $error = 'That username is already taken.';
        } elseif ($studentNum) {
            $chkSN = $db->prepare('SELECT user_id FROM users WHERE student_number = ?');
            $chkSN->execute([$studentNum]);
            if ($chkSN->fetch()) $error = 'That student number is already registered.';
        }

        if (!$error) {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $ins  = $db->prepare('INSERT INTO users (name, email, username, student_number, password_hash, role, dept_id) VALUES (?,?,?,?,?,?,?)');
            $ins->execute([$name, $email, $username, $studentNum, $hash, $role, $deptId]);
            $newId = $db->lastInsertId();

            // Log even if not logged in (use null user)
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $log = $db->prepare('INSERT INTO audit_logs (user_id, action, target_table, target_id, ip_address) VALUES (?,?,?,?,?)');
            $log->execute([null, 'Secret register: created user', 'users', $newId, $ip]);

            $success = "Account created successfully! Name: <strong>" . htmlspecialchars($name) . "</strong>, Role: <strong>" . ucfirst(str_replace('_', ' ', $role)) . "</strong>. They can now log in.";
        }
    }
}

$departments = $db->query('SELECT * FROM departments ORDER BY dept_name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – EVSU-OC</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ── Extra styles for this secret page ── */
        .reg-wrapper {
            min-height: 100vh;
            background: linear-gradient(145deg, #1a0a0f 0%, #3b0d1a 50%, #5a1320 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .reg-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.35);
            width: 100%;
            max-width: 560px;
            padding: 44px 40px;
        }
        .reg-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .reg-lock {
            width: 56px;
            height: 56px;
            background: var(--gold-pale);
            border: 3px solid var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
        }
        .reg-lock svg { width: 26px; height: 26px; }
        .reg-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--maroon);
            letter-spacing: -0.3px;
        }
        .reg-sub {
            font-size: 0.82rem;
            color: var(--gray-text);
            margin-top: 4px;
        }
        .badge-secret {
            display: inline-block;
            background: #fde8e8;
            color: #b91c1c;
            border: 1px solid #f5c6c6;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 3px 10px;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .full-col { grid-column: 1 / -1; }
        .role-note {
            font-size: 0.78rem;
            color: var(--gray-text);
            margin-top: 6px;
            padding: 8px 12px;
            background: var(--gold-pale);
            border-radius: 6px;
            border-left: 3px solid var(--gold);
            display: none;
        }
        .divider {
            border: none;
            border-top: 1.5px solid var(--gray);
            margin: 24px 0;
        }
        .login-link {
            text-align: center;
            font-size: 0.85rem;
            color: var(--gray-text);
            margin-top: 20px;
        }
        @media (max-width: 560px) {
            .reg-card { padding: 28px 18px; }
            .two-col { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="reg-wrapper">
    <div class="reg-card">
        <div class="reg-header">
            <div class="reg-lock">
                <svg viewBox="0 0 24 24" fill="none" stroke="#7B1C2B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
            </div>
            <div class="reg-title">Create Account</div>
            <div class="reg-sub">EVSU-OC INC Form System</div>
            <div class="badge-secret">&#x1F512; Secret Registration</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">

            <div class="two-col">
                <div class="form-group full-col">
                    <label>Full Name <span style="color:#b91c1c;">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="e.g. Maria Santos" required>
                </div>

                <div class="form-group full-col">
                    <label>Email Address <span style="color:#b91c1c;">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="user@evsu.edu.ph" required>
                </div>

                <div class="form-group full-col">
                    <label>Role <span style="color:#b91c1c;">*</span></label>
                    <select name="role" class="form-control" id="roleSelect" onchange="handleRoleChange(this.value)" required>
                        <option value="">— Select role —</option>
                        <option value="admin"           <?= ($_POST['role']??'')==='admin'?'selected':'' ?>>Admin</option>
                        <option value="registrar"       <?= ($_POST['role']??'')==='registrar'?'selected':'' ?>>Registrar</option>
                        <option value="department_head" <?= ($_POST['role']??'')==='department_head'?'selected':'' ?>>Department Head</option>
                        <option value="instructor"      <?= ($_POST['role']??'')==='instructor'?'selected':'' ?>>Instructor</option>
                        <option value="student"         <?= ($_POST['role']??'')==='student'?'selected':'' ?>>Student</option>
                    </select>
                    <div class="role-note" id="roleNote"></div>
                </div>

                <!-- Employee fields -->
                <div class="form-group full-col" id="usernameGroup" style="display:none;">
                    <label>Username <span style="color:#b91c1c;">*</span></label>
                    <input type="text" name="username" id="usernameField" class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Login username (employees)">
                </div>

                <!-- Student fields -->
                <div class="form-group full-col" id="studentNumGroup" style="display:none;">
                    <label>Student Number <span style="color:#b91c1c;">*</span></label>
                    <input type="text" name="student_number" id="studentNumField" class="form-control"
                           value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>"
                           placeholder="e.g. 2024-0001">
                </div>

                <!-- Department -->
                <div class="form-group full-col" id="deptGroup" style="display:none;">
                    <label>Department</label>
                    <select name="dept_id" class="form-control">
                        <option value="">— None —</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['dept_id'] ?>"
                                <?= (($_POST['dept_id'] ?? '') == $d['dept_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['dept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr class="divider">

            <div class="two-col">
                <div class="form-group">
                    <label>Password <span style="color:#b91c1c;">*</span></label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Min 8 characters" required minlength="8"
                           oninput="checkStrength(this.value)">
                    <div id="strengthBar" style="height:4px;border-radius:4px;margin-top:6px;background:var(--gray);transition:all 0.3s;"></div>
                    <div id="strengthLabel" style="font-size:0.75rem;color:var(--gray-text);margin-top:3px;"></div>
                </div>
                <div class="form-group">
                    <label>Confirm Password <span style="color:#b91c1c;">*</span></label>
                    <input type="password" name="password_confirm" class="form-control"
                           placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;padding:13px;">
                Create Account
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="index.php">Log in here</a>
        </div>
    </div>
</div>

<script>
const roleMessages = {
    admin:           { note: 'Admins have full system access including user management and audit logs.', showUser: true,  showSN: false, showDept: false },
    registrar:       { note: 'Registrars verify payments and post final grades.',                        showUser: true,  showSN: false, showDept: false },
    department_head: { note: 'Department Heads approve applications from instructors.',                  showUser: true,  showSN: false, showDept: true  },
    instructor:      { note: 'Instructors evaluate and submit resolved grades.',                         showUser: true,  showSN: false, showDept: true  },
    student:         { note: 'Students file INC applications and track their status.',                   showUser: false, showSN: true,  showDept: false },
};

function show(id, visible) {
    document.getElementById(id).style.display = visible ? '' : 'none';
}

function handleRoleChange(role) {
    const cfg = roleMessages[role];
    const noteEl = document.getElementById('roleNote');

    if (cfg) {
        noteEl.textContent = cfg.note;
        noteEl.style.display = '';
        show('usernameGroup', cfg.showUser);
        show('studentNumGroup', cfg.showSN);
        show('deptGroup', cfg.showDept);

        // Toggle required
        document.getElementById('usernameField').required  = cfg.showUser;
        document.getElementById('studentNumField').required = cfg.showSN;
    } else {
        noteEl.style.display = 'none';
        show('usernameGroup', false);
        show('studentNumGroup', false);
        show('deptGroup', false);
    }
}

function checkStrength(val) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { color: '#ef4444', text: 'Very weak',  w: '20%' },
        { color: '#f97316', text: 'Weak',       w: '40%' },
        { color: '#eab308', text: 'Fair',       w: '60%' },
        { color: '#22c55e', text: 'Strong',     w: '80%' },
        { color: '#16a34a', text: 'Very strong',w: '100%'},
    ];
    const lvl = levels[Math.max(0, score - 1)] || levels[0];
    bar.style.width     = val ? lvl.w : '0';
    bar.style.background = lvl.color;
    label.textContent   = val ? lvl.text : '';
    label.style.color   = lvl.color;
}

// Restore UI state on page reload (e.g. after validation error)
const savedRole = document.getElementById('roleSelect').value;
if (savedRole) handleRoleChange(savedRole);
</script>
</body>
</html>