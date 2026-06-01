<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id = (int)($body['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM noblepettycashdepartment WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);