<?php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$action = $body['action'] ?? '';
$id     = intval($body['id'] ?? 0);

if ($action === 'deactivate') {
    $stmt = $conn->prepare("UPDATE nobleannouncement SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
} elseif ($action === 'activate') {
    $stmt = $conn->prepare("UPDATE nobleannouncement SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
} elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM nobleannouncement WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$success = $stmt->execute();
echo json_encode(['success' => $success]);