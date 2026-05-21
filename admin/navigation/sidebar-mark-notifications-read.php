<?php
// sidebar-mark-notifications-read.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);
if (!$user_id) { echo json_encode(['success' => false]); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$notif_id = intval($body['id'] ?? 0); // 0 = mark all

if ($notif_id) {
    $stmt = $conn->prepare("UPDATE noblenotification SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE noblenotification SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
}

echo json_encode(['success' => $stmt->execute()]);