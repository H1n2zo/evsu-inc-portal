<?php
require_once '../config/database.php';
session_start();

// Handle instant manual view contextual switches
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role_action'])) {
    $target_role_id = (int)$_POST['target_role_id'];
    if (array_key_exists($target_role_id, $_SESSION['available_roles'])) {
        $_SESSION['role_id'] = $target_role_id;
        $_SESSION['role_name'] = $_SESSION['available_roles'][$target_role_id];
        
        $landing = ['Registrar' => 'registrar.php', 'Department Head' => 'dept_head.php', 'Instructor' => 'instructor.php'];
        header("Location: " . $landing[$_SESSION['role_name']]);
        exit;
    }
}

$current_role_id = $_SESSION['role_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Query the system to retrieve allowed modules configured by the Admin
$stmt = $pdo->prepare("SELECT m.* FROM system_modules m 
                       JOIN module_permissions p ON m.id = p.module_id 
                       WHERE p.role_id = ?");
$stmt->execute([$current_role_id]);
$allowed_modules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title><?= $_SESSION['role_name'] ?> Control Space</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FBFBFB] min-h-screen flex font-sans">
    
    <aside class="w-64 bg-[#800000] text-white flex flex-col justify-between">
        <div>
            <div class="p-6 border-b border-red-900/40">
                <p class="text-xs uppercase tracking-widest text-[#FFD700] font-bold">EVSU-OC Terminal</p>
                <h2 class="text-sm font-semibold truncate mt-1"><?= htmlspecialchars($_SESSION['name']) ?></h2>
            </div>
            <nav class="p-4 space-y-1">
                <?php if (empty($allowed_modules)): ?>
                    <p class="text-xs text-red-200/60 italic p-2">All workspace modules pruned by administrator.</p>
                <?php else: ?>
                    <?php foreach($allowed_modules as $mod): ?>
                        <a href="<?= htmlspecialchars($mod['slug']) ?>.php" class="block px-4 py-2 rounded text-xs uppercase font-semibold tracking-wider hover:bg-black/10 transition text-white">
                            <?= htmlspecialchars($mod['module_name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
        </div>
        <div class="p-4 border-t border-red-900/40 text-xs">
            <a href="../login.php" class="block text-center py-2 bg-black/20 rounded hover:bg-black/40 transition">Exit Session</a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8 bg-white border border-gray-100 p-4 rounded-lg shadow-sm">
            <div>
                <h2 class="text-xs font-mono text-gray-400 uppercase tracking-wider">Active Workspace Mode</h2>
                <h1 class="text-lg font-bold text-gray-800 uppercase tracking-wide"><?= $_SESSION['role_name'] ?> View</h1>
            </div>

            <?php if(count($_SESSION['available_roles'] ?? []) > 1): ?>
                <form method="POST" action="" class="flex items-center gap-2 border border-gray-200 p-1.5 rounded bg-gray-50">
                    <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider px-2">Switch View Context:</label>
                    <select name="target_role_id" onchange="this.form.submit()" class="text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded p-1 focus:outline-none focus:ring-1 focus:ring-[#800000]">
                        <?php foreach($_SESSION['available_roles'] as $rid => $rname): ?>
                            <option value="<?= $rid ?>" <?= $rid == $current_role_id ? 'selected' : '' ?>><?= htmlspecialchars($rname) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="switch_role_action" value="1">
                </form>
            <?php endif; ?>
        </header>

        <div class="bg-white border border-gray-100 rounded-lg p-6 shadow-sm min-h-[300px] flex items-center justify-center text-center">
            <div>
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-sm font-medium text-gray-700">Workspace successfully rendered through dynamic role-filtering.</p>
                <p class="text-xs text-gray-400 max-w-sm mt-1 mx-auto">Modules are displayed contextually based on the user's role configuration settings.</p>
            </div>
        </div>
    </main>
</body>
</html>