<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Student') {
    die("Access Denied: Student Portal Credentials Required.");
}

$student_id = $_SESSION['user_id'];
$msg = ''; $err = '';

// Handle Step 1: Submitting a new Incomplete Application tracking ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_inc'])) {
    $subject = trim($_POST['subject_name']);
    $units = (int)$_POST['units'];
    $instructor_id = (int)$_POST['instructor_id'];
    $computed_fee = $units * 50; // Formula requirement

    // Verify system constraints for duplicate tracking records
    $check = $pdo->prepare("SELECT id FROM inc_applications WHERE student_id = ? AND subject_name = ? AND status != 'Resolved'");
    $check->execute([$student_id, $subject]);
    
    if ($check->fetch()) {
        $err = "An active, unresolved application already exists for this specific course item.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO inc_applications (student_id, subject_name, units, total_fee, instructor_id, status) VALUES (?, ?, ?, ?, ?, 'Pending Instructor Evaluation')");
        $stmt->execute([$student_id, $subject, $units, $computed_fee, $instructor_id]);
        $msg = "INC Application recorded successfully! Awaiting instructor evaluation tier.";
    }
}

// Handle Step 4: Cashier Receipt & O.R. Data Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_receipt'])) {
    $app_id = (int)$_POST['app_id'];
    $or_number = trim($_POST['or_number']);
    
    if (empty($_FILES['receipt_file']['name'])) {
        $err = "Mandatory Requirement: Proof of payment document missing.";
    } else {
        $file = $_FILES['receipt_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf']; // Permitted structures
        
        if (!in_array($ext, $allowed)) {
            $err = "Invalid formatting: System only accepts JPG, PNG, and PDF outputs.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB verification size limit
            $err = "File size breach. Uploaded attachment exceeds the system-wide 5MB limit.";
        } else {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $filename = "OR_" . $or_number . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
                $stmt = $pdo->prepare("UPDATE inc_applications SET or_number = ?, receipt_path = ?, status = 'Pending Registrar Verification' WHERE id = ? AND student_id = ?");
                $stmt->execute([$or_number, $filename, $app_id, $student_id]);
                $msg = "Official Receipt logged successfully. Status forwarded to Registrar Desk.";
            } else {
                $err = "An internal error occurred while saving the uploaded attachment.";
            }
        }
    }
}

// Retrieve active operational states mapped explicitly to this single student
$apps = $pdo->prepare("SELECT a.*, e.name as instructor_name FROM inc_applications a JOIN employees e ON a.instructor_id = e.id WHERE a.student_id = ?");
$apps->execute([$student_id]);
$my_applications = $apps->fetchAll();

$instructors = $pdo->query("SELECT e.id, e.name FROM employees e JOIN employee_roles er ON e.id = er.employee_id WHERE er.role_id = (SELECT id FROM roles WHERE role_name='Instructor')")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Student Portal - EVSU-OC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FBFBFB] p-6 font-sans">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-[#800000]">STUDENT CENTRAL PORTAL</h1>
                <p class="text-xs text-gray-400">Account Owner: <?= htmlspecialchars($_SESSION['name']) ?></p>
            </div>
            <a href="../login.php" class="text-xs font-bold text-[#800000] hover:text-black transition">Log Out</a>
        </header>

        <?php if($msg): ?><div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs p-3 rounded mb-4"><?= $msg ?></div><?php endif; ?>
        <?php if($err): ?><div class="bg-red-50 border border-red-100 text-red-800 text-xs p-3 rounded mb-4"><?= $err ?></div><?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm h-fit">
                <h2 class="text-sm font-bold text-[#800000] uppercase tracking-wider mb-4">File New INC Grade Resolution</h2>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Course/Subject Title</label>
                        <input type="text" name="subject_name" required placeholder="e.g., Software Engineering" class="w-full p-2 border border-gray-200 rounded text-xs focus:border-[#800000] outline-none">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Course Units</label>
                        <input type="number" id="units_input" name="units" min="1" max="5" required class="w-full p-2 border border-gray-200 rounded text-xs focus:border-[#800000] outline-none" oninput="document.getElementById('fee_preview').innerText = this.value * 50">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Assigned Course Evaluator (Instructor)</label>
                        <select name="instructor_id" required class="w-full p-2 border border-gray-200 rounded text-xs focus:border-[#800000] outline-none">
                            <?php foreach($instructors as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4 bg-gray-50 p-3 rounded border border-dashed text-center">
                        <span class="text-xs text-gray-500 block uppercase font-bold tracking-wider">Computed Verification Fee</span>
                        <span class="text-lg font-black text-[#800000]">₱<span id="fee_preview">0</span>.00</span>
                    </div>
                    <button type="submit" name="apply_inc" class="w-full py-2 bg-[#800000] text-white font-bold text-xs rounded uppercase tracking-wider hover:bg-[#FFD700] hover:text-black transition duration-200">Submit Application</button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">My Active Tracked Clearances</h2>
                <?php if (empty($my_applications)): ?>
                    <p class="text-xs italic text-gray-400 bg-white p-4 text-center rounded border border-gray-100 shadow-sm">No historical or active INC records tracked within this terminal session.</p>
                <?php else: ?>
                    <?php foreach($my_applications as $app): ?>
                        <div class="bg-white p-5 rounded-lg border border-gray-100 shadow-sm flex flex-col justify-between md:flex-row gap-4 items-start md:items-center">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-bold text-gray-800"><?= htmlspecialchars($app['subject_name']) ?></h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono tracking-wider font-bold <?= $app['status'] === 'Resolved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>"><?= $app['status'] ?></span>
                                </div>
                                <p class="text-xs text-gray-400">Instructor: <?= htmlspecialchars($app['instructor_name']) ?> | Units: <?= $app['units'] ?> | Fee: ₱<?= $app['total_fee'] ?></p>
                                <?php if($app['resolved_grade']): ?>
                                    <p class="text-xs font-bold text-emerald-700 mt-1">Resolved Grade Input: <?= htmlspecialchars($app['resolved_grade']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($app['status'] === 'Pending Fee & O.R. Upload'): ?>
                                <form method="POST" action="" enctype="multipart/form-data" class="bg-gray-50 p-3 rounded border border-gray-200 w-full md:w-auto">
                                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                    <div class="flex flex-col gap-2">
                                        <input type="text" name="or_number" required placeholder="O.R. Code Number" class="p-1.5 border border-gray-300 rounded text-xs outline-none bg-white">
                                        <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required class="text-xs text-gray-500">
                                        <button type="submit" name="upload_receipt" class="bg-[#800000] text-white font-bold text-[11px] py-1.5 rounded hover:bg-black transition uppercase tracking-wider">Upload O.R. Reference</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>