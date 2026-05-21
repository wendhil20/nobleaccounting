<?php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$voucher_id = intval($body['voucher_id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);

if (!$voucher_id || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$stmt = $conn->prepare("UPDATE noblevoucher SET prepared_by = ?, prepared_at = NOW() WHERE id = ?");
$stmt->bind_param("ii", $user_id, $voucher_id);
$success = $stmt->execute();

if ($success && $stmt->affected_rows > 0) {
    // Kunin ang control_no ng request
    $vRow = $conn->query("SELECT request_id, control_no FROM noblevoucher WHERE id = $voucher_id LIMIT 1")->fetch_assoc();
    $control_no = $vRow['control_no'] ?? '';
    $request_id = intval($vRow['request_id'] ?? 0);
    $message = "Cash voucher $control_no has been prepared and is ready for your approval. check your approval cash voucher list.";

    // Notify CUSTODIAN lang
    $custodians = $conn->query("
    SELECT id FROM noblerole 
    WHERE role = 'ACCOUNTING AND FINANCE DEPARTMENT' 
    AND position = 'head'
");

   while ($c = $custodians->fetch_assoc()) {
    $cid = intval($c['id']);
    
    $link = '/cashvoucherdashboard'; // ← page ng head
    
    $stmt2 = $conn->prepare("
        INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 0, NOW(), ?, ?)
    ");
    $stmt2->bind_param("iisis", $cid, $request_id, $message, $user_id, $link);
    $stmt2->execute();
}

}

echo json_encode(['success' => $success]);