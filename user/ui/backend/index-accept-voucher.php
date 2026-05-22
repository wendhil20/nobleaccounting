<?php
// index-accept-voucher.php
include ROOT_PATH . '/network/connect.php';
if (empty($_SESSION['logged_in'])) { echo json_encode(['success' => false]); exit; }
header('Content-Type: application/json');

$body       = json_decode(file_get_contents('php://input'), true);
$voucher_id = intval($body['voucher_id'] ?? 0);
$user_id    = intval($_SESSION['account_id'] ?? 0);

if (!$voucher_id || !$user_id) { echo json_encode(['success' => false]); exit; }

$stmt = $conn->prepare("UPDATE noblevoucher 
    SET received_by = ?, received_at = NOW()
    WHERE id = ? AND status = 'released' AND received_by IS NULL");
$stmt->bind_param("ii", $user_id, $voucher_id);

echo json_encode(['success' => $stmt->execute(), 'affected' => $stmt->affected_rows]);