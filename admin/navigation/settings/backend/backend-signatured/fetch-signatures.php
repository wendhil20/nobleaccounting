<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);
$result  = $conn->query("SELECT * FROM noblesignature WHERE user_id = $user_id ORDER BY created_at DESC");
$data    = [];
while ($row = $result->fetch_assoc()) $data[] = $row;

echo json_encode($data);