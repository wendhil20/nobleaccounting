<?php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body    = json_decode(file_get_contents('php://input'), true);
$id      = intval($body['id'] ?? 0);
$action  = in_array($body['action'], ['approved', 'rejected']) ? $body['action'] : null;
$user_id = intval($_SESSION['account_id'] ?? 0);
$role_result = $conn->query("SELECT id FROM noblerole WHERE id = $user_id LIMIT 1");
$comment = trim($body['comment'] ?? '');

if (!$id || !$action || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}


if ($action === 'approved') {
    $stmt = $conn->prepare("UPDATE noblebudgetrequest 
        SET status = ?, approved_by = ?, approved_at = NOW(), reject_comment = NULL
        WHERE id = ? AND sent_to = ?");
    $stmt->bind_param("siii", $action, $user_id, $id, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE noblebudgetrequest 
        SET status = ?, reject_comment = ?, approved_by = NULL, approved_at = NULL
        WHERE id = ? AND sent_to = ?");
    $stmt->bind_param("ssii", $action, $comment, $id, $user_id);
}

echo json_encode([
    'success'  => $stmt->execute(), 
    'affected' => $stmt->affected_rows,
    'debug'    => [
        'user_id' => $user_id,
        'id'      => $id,
        'action'  => $action,
        'comment' => $comment
    ]
]);