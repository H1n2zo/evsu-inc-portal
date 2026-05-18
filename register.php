<?php
require_once 'config/database.php';
session_start();

$msg = '';
$err = '';

// Fetch active roles from the database to populate the employee roles checkboxes
try {
    $roles_stmt = $pdo->query("SELECT * FROM roles WHERE role_name != 'Student'");
    $available_roles = $roles_stmt->fetchAll();
} catch (PDOException $e) {
    $available_roles = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = $_POST['account_type'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 10]);

    if ($account_type === 'student') {
        $student_id = trim($_POST['student_id']);

        // Strictly validate student ID formatting pattern (YYYY-XXXXX)
        if (!preg_match('/^\d{4}-\d{5}$/', $student_id)) {
            $err = "Malformed Student ID format. Must strictly follow '2000-00001'.";
        } else {
            try {
                // Check for duplicates
                $chk = $pdo->prepare("SELECT id FROM students WHERE student_id = ? OR email = ?");
                $chk->execute([$student_id, $email]);
                
                if ($chk->fetch()) {
                    $err = "Conflict Error: That Student ID or Email address is already registered.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO students (student_id, name, email, password_hash, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$student_id, $name, $email, $password]);
                    $msg = "Student account successfully provisioned! Proceed to the Student Portal.";
                }
            } catch (PDOException $e) {
                $err = "Database Registration Error: " . $e->getMessage();
            }
        }
    } else {
        // Employee logic mapping execution
        $username = trim($_POST['username']);
        $selected_roles = $_POST['roles'] ?? [];

        if (empty($selected_roles)) {
            $err = "Constraint Error: You must select at least one role designation for employees.";
        } else {
            try {
                // Check for duplicates
                $chk = $pdo->prepare("SELECT id FROM employees WHERE username = ? OR email = ?");
                $chk->execute([$username, $email]);
                
                if ($chk->fetch()) {
                    $err = "Conflict Error: That Username or Email address is already registered.";
                } else {
                    $pdo->beginTransaction();

                    // Insert Base Employee Record
                    $stmt = $pdo->prepare("INSERT INTO employees (username, name, email, password_hash, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$username, $name, $email, $password]);
                    $employee_id = $pdo->lastInsertId();

                    // Link multiple assigned roles securely (Supports Instructor + Dept Head concurrently)
                    $role_stmt = $pdo->prepare("INSERT INTO employee_roles (employee_id, role_id) VALUES (?, ?)");
                    foreach ($selected_roles as $role_id) {
                        $role_stmt->execute([$employee_id, $role_id]);
                    }

                    $pdo->commit();
                    $msg = "Employee account successfully provisioned with custom multi-role assignments!";
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $err = "Database Registration Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Provisioning - EVSU-OC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-maroon { background-color: #800000; }
        .text-maroon { color: #800000; }
        .border-maroon { border-color: #800000; }
        .focus-maroon:focus { border-color: #800000; ring-color: #800000; }
    </style>
</head>
<body class="bg-[#FBFBFB] min-h-screen flex justify-center items-center p-4 font-sans">
    <div class="bg-white border border-gray-100 rounded-lg p-8 w-full max-w-lg shadow-sm">
        
        <div class="mb-6">
            <a href="index.php" class="text-xs text-gray-400 hover:text-maroon flex items-center gap-1 mb-2">← Back to Portal Select</a>
            <h2 class="text-2xl font-bold text-maroon uppercase tracking-wide">Account Registration Desk</h2>
            <p class="text-gray-400 text-xs mt-1">Populate institutional profiles into the system-wide storage matrix.</p>
        </div>

        <?php if($msg): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs p-3 rounded mb-4"><?= $msg ?></div>
        <?php endif; ?>
        <?php if($err): ?>
            <div class="bg-red-50 border border-red-100 text-red-800 text-xs p-3 rounded mb-4"><?= $err ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Target Portal Classification</label>
                <select name="account_type" id="account_type" onchange="toggleFormLayout(this.value)" class="w-full px-3 py-2 border border-gray-200 rounded text-xs outline-none bg-gray-50 focus:border-maroon font-bold text-gray-700">
                    <option value="student">Student Profile</option>
                    <option value="employee">Employee Profile (Admin, Registrar, Chair, Instructor)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Full Legal Name</label>
                <input type="text" name="name" required placeholder="e.g., John Doe" class="w-full px-3 py-2 border border-gray-200 rounded text-xs outline-none focus:border-maroon">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Institutional Email Address</label>
                <input type="email" name="email" required placeholder="e.g., john.doe@evsu.edu.ph" class="w-full px-3 py-2 border border-gray-200 rounded text-xs outline-none focus:border-maroon">
            </div>

            <div id="student_inputs_block" class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Student ID String Input Format</label>
                <input type="text" name="student_id" id="student_id" placeholder="2000-00001" pattern="\d{4}-\d{5}" title="Format must strictly follow: YYYY-XXXXX" class="w-full px-3 py-2 border border-gray-200 rounded text-xs outline-none focus:border-maroon font-mono">
                <p class="text-[10px] text-gray-400 mt-1">Enforces strict 4-digit year value, dashboard hyphen separator, and 5-digit index identifier tracking sequences.</p>
            </div>

            <div id="employee_inputs_block" class="mb-4 hidden">
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Workstation System Username</label>
                    <input type="text" name="username" id="username" placeholder="e.g., john.doe" class="w-full px-3 py-2 border border-gray-200 rounded text-xs outline-none focus:border-maroon">
                </div>
                
                <div class="p-3 bg-gray-50 rounded border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-2 text-maroon">Multi-Role Allocation Map</label>
                    <p class="text-[10px] text-gray-400 mb-2">Check all structural assignments that apply (e.g., an Instructor who can dynamically assume a Department Head role perspective).</p>
                    <div class="space-y-2">
                        <?php foreach($available_roles as $role): ?>
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-700">
                                <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>" class="w-3.5 h-3.5 text-maroon border-gray-300 rounded focus:ring-maroon">
                                <?= htmlspecialchars($role['role_name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Secure Password Credential</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-3 py-2 border border-gray-200 rounded text-xs outline-none focus:border-maroon">
            </div>

            <button type="submit" class="w-full py-2.5 rounded text-white bg-maroon font-bold text-xs uppercase tracking-wider hover:bg-[#FFD700] hover:text-black transition duration-200 shadow-sm">
                Commit Registration Log Record
            </button>
        </form>
    </div>

    <script>
        function toggleFormLayout(val) {
            const studentBlock = document.getElementById('student_inputs_block');
            const employeeBlock = document.getElementById('employee_inputs_block');
            const studentId = document.getElementById('student_id');
            const username = document.getElementById('username');

            if (val === 'student') {
                studentBlock.classList.remove('hidden');
                employeeBlock.classList.add('hidden');
                studentId.setAttribute('required', 'required');
                username.removeAttribute('required');
            } else {
                studentBlock.classList.add('hidden');
                employeeBlock.classList.remove('hidden');
                studentId.removeAttribute('required');
                username.setAttribute('required', 'required');
            }
        }
        
        // Document run initial configuration hook initialization
        document.addEventListener("DOMContentLoaded", function() {
            toggleFormLayout(document.getElementById('account_type').value);
        });
    </script>
</body>
</html>