<?php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body    = json_decode(file_get_contents('php://input'), true);
$id      = intval($body['id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);

if (!$id || !$user_id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("UPDATE noblebudgetrequest 
    SET received_by = ?, received_at = NOW()
    WHERE id = ? AND status = 'approved'");
$stmt->bind_param("ii", $user_id, $id);

echo json_encode(['success' => $stmt->execute(), 'affected' => $stmt->affected_rows]);