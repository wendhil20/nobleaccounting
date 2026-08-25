<?php
// index-authguard.php
//
// USAGE:
//   Default (basic login check lang):
//     include ROOT_PATH . '/admin/authentication/index-authguard.php';
//
//   Option A — Main Branch lang ang papayagan sa page na ito:
//     $mainBranchOnly = true;
//     include ROOT_PATH . '/admin/authentication/index-authguard.php';
//
//   Option B — partikular na listahan ng branches lang:
//     $allowedBranches = ['Balintawak', 'Bulacan'];
//     include ROOT_PATH . '/admin/authentication/index-authguard.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/loginadmin');
    exit;
}

if (empty($_SESSION['branch']) || empty($_SESSION['role'])) {
    session_destroy();
    header('Location: ' . BASE_URL . '/loginadmin');
    exit;
}

// --- Branch resolution (available na sa lahat ng naka-include nito) ---
$sessionBranch = $_SESSION['branch'];
$sessionRole   = $_SESSION['role'];
$isMainBranch  = false;

$branchCheckStmt = $conn->prepare("SELECT is_main FROM noblebranch WHERE name = ? LIMIT 1");
$branchCheckStmt->bind_param("s", $sessionBranch);
$branchCheckStmt->execute();
$branchCheckStmt->bind_result($branchIsMainRaw);
if ($branchCheckStmt->fetch()) {
    $isMainBranch = ((int) $branchIsMainRaw === 1);
}
$branchCheckStmt->close();

// --- Optional per-page branch restriction ---
$branchAccessDenied = false;

if (!empty($mainBranchOnly) && $mainBranchOnly === true) {
    if (!$isMainBranch) {
        $branchAccessDenied = true;
    }
}

if (!empty($allowedBranches) && is_array($allowedBranches)) {
    if (!in_array($sessionBranch, $allowedBranches, true)) {
        $branchAccessDenied = true;
    }
}

if ($branchAccessDenied) {
    // Role-based fallback redirect — dito idinideretso base sa role
    // ang user kung saan pahihintulutan siyang pumunta imbes na
    // laging /crm-ma
    $roleFallbackRoutes = [
        'SALES AND MARKETING DEPARTMENT' => 'crmsales',
        'DESIGN DEPARTMENT'              => 'crmdesigner',
        // idagdag pa dito kung may ibang roles na may sariling
        // non-main-branch fallback page
    ];

    $fallbackRoute = $roleFallbackRoutes[$sessionRole] ?? 'crm-ma';

    header('Location: ' . BASE_URL . '/' . $fallbackRoute);
    exit;
}
?>