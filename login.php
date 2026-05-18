<?php
require_once 'config/database.php';
session_start();

$portal = isset($_GET['portal']) && $_GET['portal'] === 'employee' ? 'employee' : 'student';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $portal_type = $_POST['portal_type'];
    $password = $_POST['password'];

    if ($portal_type === 'student') {
        $student_id = trim($_POST['student_id']);
        // Verify format structure regex match
        if (!preg_match('/^\d{4}-\d{5}$/', $student_id)) {
            $error = "Malformed Student ID format. Must match standard pattern '2000-00001'.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? AND is_active = 1");
            $stmt->execute([$student_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role_name'] = 'Student';
                $_SESSION['name'] = $user['name'];
                header("Location: modules/student.php");
                exit;
            } else { $error = "Invalid Student ID or password credentials."; }
        }
    } else {
        $username = trim($_POST['username']);
        $stmt = $pdo->prepare("SELECT e.*, r.role_name, r.id as role_id FROM employees e 
                               JOIN employee_roles er ON e.id = er.employee_id 
                               JOIN roles r ON er.role_id = r.id 
                               WHERE e.username = ? AND e.is_active = 1");
        $stmt->execute([$username]);
        $users = $stmt->fetchAll();

        if ($users && password_verify($password, $users[0]['password_hash'])) {
            $_SESSION['user_id'] = $users[0]['id'];
            $_SESSION['name'] = $users[0]['name'];
            
            // BUILD EMPLOYEE MULTI-ROLE DESIGNATION REFERENCE LIST
            $roles = [];
            foreach($users as $u) { 
                $roles[$u['role_id']] = $u['role_name']; 
            }
            $_SESSION['available_roles'] = $roles; // Stores all mapped profile configurations
            
            // Default initial workspace selection definitions
            reset($roles);
            $_SESSION['role_id'] = key($roles);
            $_SESSION['role_name'] = current($roles);

            // ROUTE TO THE MASTER DASHBOARD CONTROLLER FRAMEWORK
            header("Location: modules/dashboard.php");
            exit;
        } else { 
            $error = "Invalid Username or password credentials."; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EVSU-OC INC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FBFBFB] min-h-screen flex justify-center items-center px-4">
    <div class="bg-white border border-gray-100 rounded-lg p-8 w-full max-w-md shadow-sm">
        <div class="mb-6">
            <a href="index.php" class="text-xs text-gray-400 hover:text-[#800000] flex items-center gap-1 mb-2">← Back to Portal Select</a>
            <h2 class="text-2xl font-bold text-[#800000] uppercase tracking-wide"><?= ucfirst($portal) ?> Portal Login</h2>
            <p class="text-gray-400 text-xs mt-1">Provide credentials to verify your workstation access authorization.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-700 text-xs p-3 rounded mb-4 border border-red-100"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="portal_type" value="<?= $portal ?>">

            <?php if ($portal === 'student'): ?>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Student ID</label>
                    <input type="text" name="student_id" required placeholder="2000-00001" pattern="\d{4}-\d{5}" title="Format must strictly be: YYYY-XXXXX"
                           class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:outline-none focus:border-[#800000]">
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Employee Account Username</label>
                    <input type="text" name="username" required placeholder="sir.panda"
                           class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:outline-none focus:border-[#800000]">
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Password Credentials</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:outline-none focus:border-[#800000]">
            </div>

            <button type="submit" class="w-full py-2.5 rounded text-white bg-[#800000] font-semibold hover:bg-[#FFD700] hover:text-black transition duration-200">
                Authenticate Workstation
            </button>
        </form>
    </div>
</body>
</html>