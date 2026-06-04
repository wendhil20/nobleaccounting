<?php
// pettycashtwo-save-account-title.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id    = intval($body['id'] ?? 0);
$title = strtoupper(trim($body['title'] ?? ''));

if (!$title) {
    echo json_encode(['success' => false, 'message' => 'Title is required.']);
    exit;
}

if ($id) {
    $stmt = $conn->prepare("UPDATE noblepettycashtitleaccount SET title = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $title, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO noblepettycashtitleaccount (title) VALUES (?)");
    $stmt->bind_param("s", $title);
}

$success = $stmt->execute();
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Saved.' : $conn->error,
    'insert_id' => $id ?: $conn->insert_id,
]);