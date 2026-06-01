<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$name = trim($body['name'] ?? '');

if (!$name) {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

// Check duplicate
$check = $conn->prepare("SELECT id FROM noblepettycashdepartment WHERE name = ?");
$check->bind_param('s', $name);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Name already exists.']);
    exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO noblepettycashdepartment (name, is_active) VALUES (?, 1)");
$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);