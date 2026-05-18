<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Admin') {
    die("Access Forbidden: Administrator Credentials Required.");
}

// Update authorization matrix on form request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_matrix'])) {
    $pdo->exec("TRUNCATE TABLE module_permissions");
    if (!empty($_POST['perms'])) {
        $stmt = $pdo->prepare("INSERT INTO module_permissions (role_id, module_id) VALUES (?, ?)");
        foreach ($_POST['perms'] as $role_id => $modules) {
            foreach ($modules as $module_id => $on) {
                $stmt->execute([$role_id, $module_id]);
            }
        }
    }
    $msg = "Role Access Control Matrix updated successfully. Unauthorized layouts cleared system-wide.";
}

$roles = $pdo->query("SELECT * FROM roles WHERE role_name != 'Admin'")->fetchAll();
$modules = $pdo->query("SELECT * FROM system_modules")->fetchAll();

// Fetch current active module access mapping permissions matrix
$perm_data = $pdo->query("SELECT * FROM module_permissions")->fetchAll();
$matrix = [];
foreach ($perm_data as $p) { $matrix[$p['role_id']][$p['module_id']] = true; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin Module Management - EVSU-OC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FBFBFB] p-8 font-sans">
    <div class="max-w-5xl mx-auto bg-white rounded-lg border border-gray-100 p-8 shadow-sm">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-[#800000]">ADMIN CONTROL DESK</h1>
                <p class="text-xs text-gray-400 mt-0.5">Dynamic Access Module Controller Matrix (Bypasses traditional lock layouts)</p>
            </div>
            <a href="../login.php" class="text-xs text-gray-500 hover:text-red-700 font-semibold">Terminate Session (Logout)</a>
        </div>

        <?php if(isset($msg)): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs p-3 rounded mb-6"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="update_matrix" value="1">
            <table class="w-full border-collapse border border-gray-100 rounded text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="p-4 border border-gray-100">Functional Module Name</th>
                        <?php foreach($roles as $r): ?>
                            <th class="p-4 border border-gray-100 text-center"><?= htmlspecialchars($r['role_name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modules as $m): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 border border-gray-100 font-medium text-gray-700">
                                <?= htmlspecialchars($m['module_name']) ?>
                                <span class="block text-[10px] text-gray-400 font-mono">/modules/<?= htmlspecialchars($m['slug']) ?>.php</span>
                            </td>
                            <?php foreach($roles as $r): ?>
                                <td class="p-4 border border-gray-100 text-center">
                                    <input type="checkbox" name="perms[<?= $r['id'] ?>][<?= $m['id'] ?>]" value="1"
                                        <?= isset($matrix[$r['id']][$m['id']]) ? 'checked' : '' ?>
                                        class="w-4 h-4 text-[#800000] border-gray-300 rounded focus:ring-[#800000]">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#800000] text-white text-xs uppercase font-bold tracking-wider rounded hover:bg-[#FFD700] hover:text-black transition duration-200">
                    Apply Global Settings & Prune Modules
                </button>
            </div>
        </form>
    </div>
</body>
</html>