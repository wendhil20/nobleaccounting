<?php
// save-announcement.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$template   = intval($_POST['template']   ?? 0);
$title      = trim($_POST['title']        ?? '');
$body       = trim($_POST['body']         ?? '');
$expires_at = trim($_POST['expires_at']   ?? '');
$posted_by  = intval($_SESSION['account_id'] ?? 0);

if (!$template || !$title || !$body || !$posted_by) {
    echo json_encode(['success' => false, 'error' => 'All fields required']);
    exit;
}

if (!$expires_at || !strtotime($expires_at)) {
    echo json_encode(['success' => false, 'error' => 'Invalid expiration date']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO nobleannouncement (template, title, body, posted_by, created_at, expires_at, is_active)
    VALUES (?, ?, ?, ?, NOW(), ?, 1)
");
$stmt->bind_param("issis", $template, $title, $body, $posted_by, $expires_at);
$success = $stmt->execute();

echo json_encode(['success' => $success]);