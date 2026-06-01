<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id   = intval($body['id'] ?? 0);
$name = strtoupper(trim($body['name'] ?? ''));

if (!$name) {
    echo json_encode(['success' => false, 'message' => 'Department name is required.']);
    exit;
}

if ($id) {
    $stmt = $conn->prepare("UPDATE noblepettycashdepartment SET name = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO noblepettycashdepartment (name) VALUES (?)");
    $stmt->bind_param("s", $name);
}

$success = $stmt->execute();
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Saved.' : $conn->error,
    'insert_id' => $id ?: $conn->insert_id,
]);