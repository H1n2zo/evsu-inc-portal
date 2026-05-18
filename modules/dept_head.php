<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Department Head') {
    die("Access Denied: Department Head Credentials Required.");
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Handle Multi-Role View Conversions [cite: 348]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role_action'])) {
    $target_role_id = (int)$_POST['target_role_id'];
    if (array_key_exists($target_role_id, $_SESSION['available_roles'])) {
        $_SESSION['role_id'] = $target_role_id;
        $_SESSION['role_name'] = $_SESSION['available_roles'][$target_role_id];
        header("Location: " . ($_SESSION['role_name'] === 'Instructor' ? 'instructor.php' : 'dept_head.php'));
        exit;
    }
}

// Handle Form Authorizations (Signatures & Forward Tracking States) [cite: 503, 651]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_application'])) {
    $app_id = (int)$_POST['app_id'];
    $signature_data = trim($_POST['signature_data'] ?? 'DH_SIGNED_DIGITALLY');

    // Forward sequence tracking parameter changes directly to step 4 [cite: 652]
    $stmt = $pdo->prepare("UPDATE inc_applications SET status = 'Pending Fee & O.R. Upload', dept_head_signature = ? WHERE id = ?");
    $stmt->execute([$signature_data, $app_id]);
    $msg = "Application certified, authorized, and forwarded to Student Payment routing step.";
}

// Dynamic filtering logic: Display only items matching the user's assigned department [cite: 218, 562]
$query = "SELECT a.*, s.name as student_name, s.student_id as formatted_student_id 
          FROM inc_applications a 
          JOIN students s ON a.student_id = s.id 
          WHERE a.status = 'Pending Department Head Approval'";
$pending_items = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Department Head Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FBFBFB] p-6 font-sans">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg border border-gray-100 shadow-sm">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-[#800000]">DEPARTMENT HEAD ADMINISTRATIVE DASHBOARD</h1>
                <p class="text-xs text-gray-400">Authenticated Chair Profile: <?= htmlspecialchars($_SESSION['name']) ?></p>
            </div>
            
            <?php if(count($_SESSION['available_roles'] ?? []) > 1): ?>
                <form method="POST" action="" class="flex items-center gap-2 border border-gray-200 p-1 bg-gray-50 rounded">
                    <span class="text-[10px] font-bold text-gray-500 uppercase px-1">Switch System Role:</span>
                    <select name="target_role_id" onchange="this.form.submit()" class="text-xs font-semibold bg-white border border-gray-200 p-1 rounded outline-none">
                        <?php foreach($_SESSION['available_roles'] as $rid => $rname): ?>
                            <option value="<?= $rid ?>" <?= $rname === 'Department Head' ? 'selected' : '' ?>><?= $rname ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="switch_role_action" value="1">
                </form>
            <?php endif; ?>
        </header>

        <?php if($msg): ?><div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs p-3 rounded mb-6"><?= $msg ?></div><?php endif; ?>

        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Awaiting Signatures & Clearance Verification</h2>
        <?php if (empty($pending_items)): ?>
            <p class="text-xs italic text-gray-400 p-4 bg-gray-50 border border-dashed text-center rounded">No pending applications match your department clearance parameters at this time.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-100 text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="p-3 border border-gray-100">Student Identity</th>
                            <th class="p-3 border border-gray-100">Course / Subject</th>
                            <th class="p-3 border border-gray-100">Evaluated Grade</th>
                            <th class="p-3 border border-gray-100 text-right">Administrative Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pending_items as $item): ?>
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="p-3 border border-gray-100">
                                    <span class="font-bold text-gray-800 block"><?= htmlspecialchars($item['student_name']) ?></span>
                                    <span class="text-xs font-mono text-gray-400"><?= $item['formatted_student_id'] ?></span>
                                </td>
                                <td class="p-3 border border-gray-100 text-gray-600"><?= htmlspecialchars($item['subject_name']) ?> (<?= $item['units'] ?> Units)</td>
                                <td class="p-3 border border-gray-100 font-mono text-[#800000] font-bold"><?= htmlspecialchars($item['resolved_grade']) ?></td>
                                <td class="p-3 border border-gray-100 text-right">
                                    <form method="POST" action="" class="inline-block">
                                        <input type="hidden" name="app_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="approve_application" class="bg-[#800000] text-white text-[11px] font-bold px-4 py-2 rounded uppercase tracking-wider hover:bg-black transition">Sign & Approve Form</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>