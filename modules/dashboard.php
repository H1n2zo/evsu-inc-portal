<?php
require_once '../config/database.php';
session_start();

// Redirect back if there is no authenticated session active
if (!isset($_SESSION['role_name']) || !isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$current_role_name = $_SESSION['role_name'];
$user_id = $_SESSION['user_id'];
$msg = '';

// UNIVERSAL MULTI-ROLE SWITCHER LOGIC
// Allows ANY employee to pivot context instantly to any role mapped to their account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role_action'])) {
    $target_role_id = (int)$_POST['target_role_id'];
    if (isset($_SESSION['available_roles'][$target_role_id])) {
        $_SESSION['role_id'] = $target_role_id;
        $_SESSION['role_name'] = $_SESSION['available_roles'][$target_role_id];
        
        // Define dynamic structural destination pathways
        $routing_map = [
            'Admin' => 'admin.php',
            'Registrar' => 'registrar.php',
            'Department Head' => 'dept_head.php',
            'Instructor' => 'instructor.php'
        ];
        
        // Redirect to their specific dashboard file context if it exists, otherwise refresh
        $destination = $routing_map[$_SESSION['role_name']] ?? 'dashboard.php';
        header("Location: " . $destination);
        exit;
    }
}

// Fetch only the allowed functional system modules determined by the Administrator Matrix
$allowed_modules = [];
if ($current_role_name === 'Student') {
    // Students query their explicit permission mapping tracking array
    $stmt = $pdo->prepare("SELECT m.* FROM system_modules m 
                           JOIN module_permissions p ON m.id = p.module_id 
                           WHERE p.role_id = (SELECT id FROM roles WHERE role_name = 'Student')");
    $stmt->execute();
    $allowed_modules = $stmt->fetchAll();
} else {
    // Employees query using their currently active contextual role context structure
    $stmt = $pdo->prepare("SELECT m.* FROM system_modules m 
                           JOIN module_permissions p ON m.id = p.module_id 
                           WHERE p.role_id = ?");
    $stmt->execute([$_SESSION['role_id']]);
    $allowed_modules = $stmt->fetchAll();
}

// Fetch some minimalist summary counters to make the dashboard functional
$metrics = ['pending' => 0, 'resolved' => 0];
if ($current_role_name === 'Student') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as c, status FROM inc_applications WHERE student_id = ? GROUP BY status");
    $stmt->execute([$user_id]);
    foreach($stmt->fetchAll() as $r) {
        if ($r['status'] === 'Resolved') $metrics['resolved'] += $r['c'];
        else $metrics['pending'] += $r['c'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Workspace - EVSU-OC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-maroon { background-color: #800000; }
        .text-maroon { color: #800000; }
        .border-maroon { border-color: #800000; }
        .hover-gold:hover { background-color: #FFD700; color: #222222; }
    </style>
</head>
<body class="bg-[#FBFBFB] min-h-screen flex font-sans">

    <aside class="w-64 bg-maroon text-white flex flex-col justify-between shadow-md">
        <div>
            <div class="p-6 border-b border-red-900/40">
                <p class="text-xs uppercase tracking-widest text-[#FFD700] font-bold">EVSU-OC Campus</p>
                <h2 class="text-sm font-semibold truncate mt-1"><?= htmlspecialchars($_SESSION['name']) ?></h2>
                <span class="text-[10px] bg-black/20 text-red-100 px-2 py-0.5 rounded mt-1 inline-block uppercase tracking-wider font-mono"><?= $current_role_name ?></span>
            </div>
            
            <nav class="p-4 space-y-1">
                <?php if (empty($allowed_modules)): ?>
                    <p class="text-xs text-red-200/50 italic p-2">Workspace configurations restricted by Admin.</p>
                <?php else: ?>
                    <?php foreach($allowed_modules as $mod): ?>
                        <a href="<?= htmlspecialchars($mod['slug']) ?>.php" class="block px-4 py-2.5 rounded text-xs uppercase font-bold tracking-wider hover:bg-black/10 transition text-white">
                            • <?= htmlspecialchars($mod['module_name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
        </div>
        
        <div class="p-4 border-t border-red-900/40">
            <a href="../login.php" class="block text-center py-2 bg-black/20 text-xs font-bold uppercase tracking-wider rounded hover:bg-black/40 transition">Terminate Session</a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 bg-white border border-gray-100 p-5 rounded-lg shadow-sm">
            <div>
                <h2 class="text-xs font-mono text-gray-400 uppercase tracking-widest">Central Workspace Entry</h2>
                <h1 class="text-xl font-black text-gray-800 uppercase tracking-wide">Welcome Back, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            </div>

            <?php if($current_role_name !== 'Student' && count($_SESSION['available_roles'] ?? []) > 1): ?>
                <form method="POST" action="" class="flex items-center gap-2 border border-gray-200 p-1.5 rounded bg-gray-50 shadow-inner">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider px-1">Switch Control Profile:</label>
                    <select name="target_role_id" onchange="this.form.submit()" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded p-1 outline-none focus:ring-1 focus:ring-maroon">
                        <?php foreach($_SESSION['available_roles'] as $rid => $rname): ?>
                            <option value="<?= $rid ?>" <?= $rname === $current_role_name ? 'selected' : '' ?>><?= htmlspecialchars($rname) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="switch_role_action" value="1">
                </form>
            <?php endif; ?>
        </header>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Workspace Identity</h3>
                <p class="text-lg font-bold text-maroon uppercase mt-1"><?= $current_role_name ?></p>
                <p class="text-[11px] text-gray-400 mt-1">Modules are dynamically populated based on this profile view context.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Resolutions Tracking</h3>
                <p class="text-2xl font-black text-gray-800 mt-1"><?= $metrics['pending'] ?></p>
                <p class="text-[11px] text-gray-400 mt-1">Forms tracking within step sequences inside your current authorization loop.</p>
            </div>

            <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm sm:col-span-2 lg:col-span-1">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Archived Complete Trackings</h3>
                <p class="text-2xl font-black text-emerald-600 mt-1"><?= $metrics['resolved'] ?></p>
                <p class="text-[11px] text-gray-400 mt-1">INC clearances that have achieved step 7 (Resolved execution).</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm text-center max-w-2xl mx-auto mt-12">
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-sm font-bold text-gray-700 uppercase mb-1">Minimalist Layout Notice</h3>
            <p class="text-xs text-gray-400 leading-relaxed">To preserve security tracking workflows and ensure UI compliance, modules are entirely hidden from the layout when deactivated by the Administrator instead of using traditional lock icons or disabled tags.</p>
        </div>
    </main>
</body>
</html>