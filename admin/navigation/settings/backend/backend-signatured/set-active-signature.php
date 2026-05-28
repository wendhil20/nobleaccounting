<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body    = json_decode(file_get_contents('php://input'), true);
$sig_id  = intval($body['id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);

if (!$sig_id || !$user_id) {
    echo json_encode(['success' => false]);
    exit;
}

// Kunin ang path
$row = $conn->query("SELECT path FROM noblesignature WHERE id = $sig_id AND user_id = $user_id LIMIT 1");
if (!$row || !$row->num_rows) {
    echo json_encode(['success' => false, 'message' => 'Signature not found.']);
    exit;
}
$path = $row->fetch_assoc()['path'];

// I-deactivate lahat, i-activate ang pinili
$conn->query("UPDATE noblesignature SET is_active = 0 WHERE user_id = $user_id");
$conn->query("UPDATE noblesignature SET is_active = 1 WHERE id = $sig_id");

// I-update ang active_signature_id lang:
$conn->query("UPDATE noblerole SET active_signature_id = $sig_id WHERE id = $user_id");

echo json_encode(['success' => true]);