<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);

if (empty($body['id'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ID is required.']);
    exit;
}

$id = (int)$body['id'];

$stmt = $conn->prepare("DELETE FROM noblegeneralsheet WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);