<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body    = json_decode(file_get_contents('php://input'), true);
$sig_id  = intval($body['id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);

$row = $conn->query("SELECT path, is_active FROM noblesignature WHERE id = $sig_id AND user_id = $user_id LIMIT 1");
if (!$row || !$row->num_rows) {
    echo json_encode(['success' => false]);
    exit;
}
$sig = $row->fetch_assoc();

// Delete file
$fullPath = ROOT_PATH . '/' . $sig['path'];
if (file_exists($fullPath)) unlink($fullPath);

$conn->query("DELETE FROM noblesignature WHERE id = $sig_id");

// Kung active ang na-delete, i-set ang pinakabago bilang active
if ($sig['is_active']) {
    $next = $conn->query("SELECT id, path FROM noblesignature WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1");
    if ($next && $next->num_rows) {
        $n = $next->fetch_assoc();
        $conn->query("UPDATE noblesignature SET is_active = 1 WHERE id = {$n['id']}");
        $conn->query("UPDATE noblerole SET signature = '{$n['path']}', active_signature_id = {$n['id']} WHERE id = $user_id");
    } else {
        $conn->query("UPDATE noblerole SET signature = NULL, active_signature_id = NULL WHERE id = $user_id");
    }
}

echo json_encode(['success' => true]);