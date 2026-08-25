<?php
//hrbranch.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id = intval($body['id'] ?? 0);
$branch = trim($body['branch'] ?? '');

if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid account id.']);
    exit;
}

$stmt = $conn->prepare("UPDATE noblerole SET branch = ? WHERE id = ?");
$stmt->bind_param("si", $branch, $id);
$result = $stmt->execute();

echo json_encode([
    'success' => $result,
    'affected' => $stmt->affected_rows,
    'id' => $id,
    'branch' => $branch,
    'db_error' => $conn->error
]);