<?php
// index-cashvoucherapproved.php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body       = json_decode(file_get_contents('php://input'), true);
$voucher_id = intval($body['voucher_id'] ?? 0);
$user_id    = intval($_SESSION['account_id'] ?? 0);

if (!$voucher_id || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// ← DITO ilagay
$check = $conn->query("SELECT prepared_by FROM noblevoucher WHERE id = $voucher_id LIMIT 1")->fetch_assoc();
if (!$check || !$check['prepared_by']) {
    echo json_encode(['success' => false, 'error' => 'Voucher must be prepared first before approving.']);
    exit;
}

$stmt = $conn->prepare("UPDATE noblevoucher SET approved_by = ?, approved_at = NOW(), status = 'ready_to_release' WHERE id = ?");
$stmt->bind_param("ii", $user_id, $voucher_id);
$success = $stmt->execute();



if ($success && $stmt->affected_rows > 0) {
    $vRow = $conn->query("
        SELECT v.request_id, b.control_no 
        FROM noblevoucher v
        LEFT JOIN noblebudgetrequest b ON v.request_id = b.id
        WHERE v.id = $voucher_id LIMIT 1
    ")->fetch_assoc();

    $control_no = $vRow['control_no'] ?? '';
    $request_id = intval($vRow['request_id'] ?? 0);
    $message = "Cash voucher $control_no has been approved and is ready to release. check your cash voucher request list.";

    $custodians = $conn->query("
        SELECT id FROM noblerole 
        WHERE role = 'ACCOUNTING AND FINANCE DEPARTMENT' 
        AND position = 'custodian'
    ");

    while ($c = $custodians->fetch_assoc()) {
        $cid = intval($c['id']);
        $stmt2 = $conn->prepare("
            INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id)
            VALUES (?, ?, ?, 0, NOW(), ?)
        ");
        $stmt2->bind_param("iisi", $cid, $request_id, $message, $user_id);
        $stmt2->execute();
    }
}

echo json_encode(['success' => $success]);