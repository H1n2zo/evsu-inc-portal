<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Registrar') {
    die("Access Denied: Registrar Authorization Matrix Level Required.");
}

$msg = ''; $err = '';

// Handle Step 5 Validation Action: Payment verification processing [cite: 653]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    $app_id = (int)$_POST['app_id'];
    $action_choice = $_POST['action_choice']; // 'approve' or 'reject'
    
    if ($action_choice === 'approve') {
        $stmt = $pdo->prepare("UPDATE inc_applications SET status = 'Pending Final Grade Posting' WHERE id = ?");
        $stmt->execute([$app_id]);
        $msg = "Payment confirmed. Application advanced to Grade Posting step.";
    } else {
        $reason = trim($_POST['rejection_reason']);
        if (empty($reason)) {
            $err = "Mandatory Requirement Error: You must supply a reason for returning the application.";
        } else {
            // Revert state track parameters backwards sequentially [cite: 747]
            $stmt = $pdo->prepare("UPDATE inc_applications SET status = 'Pending Fee & O.R. Upload', rejection_notes = ? WHERE id = ?");
            $stmt->execute([$reason, $app_id]);
            $msg = "Application returned to student dashboard tracking with descriptive correction notes.";
        }
    }
}

// Handle Step 6 Validation Action: Official Record Grade Posting [cite: 654]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_grade_record'])) {
    $app_id = (int)$_POST['app_id'];
    
    // Execute atomic workflow completion block updates [cite: 655]
    $stmt = $pdo->prepare("UPDATE inc_applications SET status = 'Resolved' WHERE id = ?");
    $stmt->execute([$app_id]);
    $msg = "Grade record permanently committed. Form status marked as Resolved.";
}

// Retrieve records requiring cross-checking or permanent log storage entries
$payment_queue = $pdo->query("SELECT a.*, s.name as student_name FROM inc_applications a JOIN students s ON a.student_id = s.id WHERE a.status = 'Pending Registrar Verification'")->fetchAll();
$posting_queue = $pdo->query("SELECT a.*, s.name as student_name FROM inc_applications a JOIN students s ON a.student_id = s.id WHERE a.status = 'Pending Final Grade Posting'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Registrar Desk Workspace - EVSU-OC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FBFBFB] p-6 font-sans">
    <div class="max-w-7xl mx-auto space-y-8">
        <header class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-[#800000]">REGISTRAR OFFICE CLEARANCE TERMINAL</h1>
                <p class="text-xs text-gray-400">Institutional Session Operator: <?= htmlspecialchars($_SESSION['name']) ?></p>
            </div>
            <a href="../login.php" class="text-xs font-bold text-[#800000] hover:text-black transition">Log Out</a>
        </header>

        <?php if($msg): ?><div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs p-3 rounded"><?= $msg ?></div><?php endif; ?>
        <?php if($err): ?><div class="bg-red-50 border border-red-100 text-red-800 text-xs p-3 rounded"><?= $err ?></div><?php endif; ?>

        <section class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Verification Desk: Pending Payment Reviews</h2>
            <?php if(empty($payment_queue)): ?>
                <p class="text-xs italic text-gray-400 bg-gray-50 p-4 text-center rounded border border-dashed">No uploaded receipts require checking at this time.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach($payment_queue as $ticket): ?>
                        <div class="grid md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="space-y-2">
                                <h3 class="text-xs font-bold text-[#800000] uppercase tracking-wider">Verification Reference Details</h3>
                                <p class="text-xs text-gray-600"><strong>Student Name:</strong> <?= htmlspecialchars($ticket['student_name']) ?></p>
                                <p class="text-xs text-gray-600"><strong>Course / Subject:</strong> <?= htmlspecialchars($ticket['subject_name']) ?></p>
                                <p class="text-xs text-gray-600"><strong>Computed System Fee:</strong> ₱<?= $ticket['total_fee'] ?></p>
                                <p class="text-xs font-mono text-gray-700 bg-amber-100/60 p-2 rounded inline-block"><strong>Typed O.R. String:</strong> <?= htmlspecialchars($ticket['or_number']) ?></p>
                                
                                <form method="POST" action="" class="pt-4 space-y-3 border-t border-gray-200">
                                    <input type="hidden" name="app_id" value="<?= $ticket['id'] ?>">
                                    <div class="flex gap-4 items-center">
                                        <label class="text-xs font-bold text-gray-600"><input type="radio" name="action_choice" value="approve" checked onclick="document.getElementById('rej_box_<?= $ticket['id'] ?>').classList.add('hidden')"> Accept Payment</label>
                                        <label class="text-xs font-bold text-gray-600"><input type="radio" name="action_choice" value="reject" onclick="document.getElementById('rej_box_<?= $ticket['id'] ?>').classList.remove('hidden')"> Return Form</label>
                                    </div>
                                    <div id="rej_box_<?= $ticket['id'] ?>" class="hidden">
                                        <textarea name="rejection_reason" placeholder="State reasons for refusal..." class="w-full p-2 border border-gray-300 rounded text-xs outline-none focus:border-[#800000] bg-white h-16"></textarea>
                                    </div>
                                    <button type="submit" name="verify_payment" class="bg-[#800000] text-white text-xs font-bold py-2 px-4 rounded hover:bg-black transition uppercase">Commit Action Decision</button>
                                </form>
                            </div>
                            
                            <div class="border border-gray-200 rounded bg-white p-2 flex items-center justify-center min-h-[200px]">
                                <?php if(strtolower(pathinfo($ticket['receipt_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                    <a href="../uploads/<?= $ticket['receipt_path'] ?>" target="_blank" class="text-xs text-[#800000] underline font-bold">Open File Attachment Link (PDF View)</a>
                                <?php else: ?>
                                    <img src="../uploads/<?= $ticket['receipt_path'] ?>" alt="Receipt Image Proof" class="max-h-60 object-contain rounded">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?></section>

        <section class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Posting Desk: Final Grade Matrix Integrations</h2>
            <?php if(empty($posting_queue)): ?>
                <p class="text-xs italic text-gray-400 bg-gray-50 p-4 text-center rounded border border-dashed">No verified items require processing into the system index right now.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-100 text-sm text-left">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <th class="p-3 border border-gray-100">Student Target</th>
                                <th class="p-3 border border-gray-100">Course Code Component</th>
                                <th class="p-3 border border-gray-100">Resolved Mark Value</th>
                                <th class="p-3 border border-gray-100 text-right">System Sync Command</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($posting_queue as $post): ?>
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="p-3 border border-gray-100 font-bold text-gray-800"><?= htmlspecialchars($post['student_name']) ?></td>
                                    <td class="p-3 border border-gray-100 text-gray-600"><?= htmlspecialchars($post['subject_name']) ?></td>
                                    <td class="p-3 border border-gray-100 font-mono font-bold text-[#800000]"><?= htmlspecialchars($post['resolved_grade']) ?></td>
                                    <td class="p-3 border border-gray-100 text-right">
                                        <form method="POST" action="">
                                            <input type="hidden" name="app_id" value="<?= $post['id'] ?>">
                                            <button type="submit" name="post_grade_record" class="bg-[#800000] text-white font-bold text-[11px] px-4 py-2 rounded uppercase tracking-wider hover:bg-emerald-700 transition">Post Grade to Record</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>