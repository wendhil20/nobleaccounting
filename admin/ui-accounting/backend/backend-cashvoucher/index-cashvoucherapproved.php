<?php
// index-cashvoucherapproved.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/network/cache-helper.php';

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

// Bago ang check ng prepared_by, kunin muna ang signature
$sigRow = $conn->query("SELECT path FROM noblesignature WHERE user_id = $user_id AND is_active = 1 LIMIT 1");
$sigPath = ($sigRow && $sigRow->num_rows) ? $sigRow->fetch_assoc()['path'] : null;

// Tapos palitan ang UPDATE:
$stmt = $conn->prepare("UPDATE noblevoucher SET approved_by = ?, approved_at = NOW(), approved_signature = ?, status = 'ready_to_release' WHERE id = ?");
$stmt->bind_param("isi", $user_id, $sigPath, $voucher_id);
$success = $stmt->execute();


if ($success && $stmt->affected_rows > 0) {

    clearCache('cashvoucher_all');
    clearCache('custodian_received_requests');
    
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
    
    $link = '/accountingcustodian'; // ← page ng custodian
    
    $stmt2 = $conn->prepare("
        INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 0, NOW(), ?, ?)
    ");
    $stmt2->bind_param("iisis", $cid, $request_id, $message, $user_id, $link);
    $stmt2->execute();
}
}

echo json_encode(['success' => $success]);