<?php
// index-projectmonitor-deleteprojectexpense.php — Route: POST /deleteprojectexpense
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id   = intval($body['id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM nobleprojectbilling WHERE id = ?");
$stmt->bind_param("i", $id);
echo json_encode(['success' => $stmt->execute()]);