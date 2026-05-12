<?php
session_name('noblehome');
session_start();
header('Content-Type: application/json');

include ROOT_PATH . '/network/connect.php';

$user_id = intval($_SESSION['account_id'] ?? 0);
$body = json_decode(file_get_contents('php://input'), true);
$id = $body['id'] ?? null;

if ($id === 'all') {
    $stmt = $conn->prepare("UPDATE nobleusernotification SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("UPDATE nobleusernotification SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", intval($id), $user_id);
}

$stmt->execute();
echo json_encode(['success' => true]);