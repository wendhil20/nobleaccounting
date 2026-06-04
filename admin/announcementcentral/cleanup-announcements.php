<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$conn->query("
    DELETE FROM nobleannouncement 
    WHERE expires_at IS NOT NULL 
      AND expires_at < NOW()
");

echo json_encode(['success' => true]);