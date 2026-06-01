<?php
// index-projectmonitor-save.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$name = trim($body['name'] ?? '');
$id   = intval($body['id'] ?? 0);

if (!$name) {
    echo json_encode(['success' => false, 'error' => 'Name required']);
    exit;
}

if ($id) {
    // UPDATE existing
    $stmt = $conn->prepare("UPDATE noblepettycashdepartment SET name = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
} else {
    // INSERT new
    $stmt = $conn->prepare("INSERT INTO noblepettycashdepartment (name, is_active, created_at, updated_at) VALUES (?, 1, NOW(), NOW())");
    $stmt->bind_param("s", $name);
}

$result = $stmt->execute();
echo json_encode(['success' => $result, 'error' => $conn->error]);