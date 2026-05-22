<?php
// sidebar-fetch-notifications.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);
if (!$user_id) { echo json_encode([]); exit; }

$result = $conn->query("
    SELECT n.id, n.message, n.is_read, n.created_at,
           n.link,                          
           b.control_no, b.requestor_name, b.id as request_id,
           b.date_requested,
           actor.name as sender_name
    FROM noblenotification n
    LEFT JOIN noblebudgetrequest b ON n.request_id = b.id
    LEFT JOIN noblerole actor ON n.sender_id = actor.id
    WHERE n.user_id = $user_id
    ORDER BY n.created_at DESC
    LIMIT 20
");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);