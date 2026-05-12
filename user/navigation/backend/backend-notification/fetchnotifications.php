<?php
session_name('noblehome');
session_start();
header('Content-Type: application/json');

include ROOT_PATH . '/network/connect.php';

$user_id = intval($_SESSION['account_id'] ?? 0);
if (!$user_id) { echo json_encode([]); exit; }

$stmt = $conn->prepare("
    SELECT id, message, is_read, created_at 
    FROM nobleusernotification 
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);