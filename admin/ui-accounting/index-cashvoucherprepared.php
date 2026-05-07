<?php
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

$stmt = $conn->prepare("UPDATE noblevoucher SET prepared_by = ?, prepared_at = NOW() WHERE id = ?");
$stmt->bind_param("ii", $user_id, $voucher_id);
echo json_encode(['success' => $stmt->execute()]);