<?php
// sidebar-fetch-notifications.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/network/cache-helper.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);
if (!$user_id) { echo json_encode([]); exit; }


// DELETE once per hour lang, hindi every 5 seconds
$cleanupKey = "notif_cleanup_{$user_id}";
if (getCache($cleanupKey, 3600) === false) {
    $conn->query("DELETE FROM noblenotification WHERE created_at < NOW() - INTERVAL 30 DAY");
    setCache($cleanupKey, '1', 3600); // 1 hour
}

$result = $conn->query("
    SELECT n.id, n.message, n.is_read, n.created_at, n.link,
           n.control_no, n.type, n.request_id,
           b.requestor_name, b.date_requested,
           actor.name as sender_name
    FROM noblenotification n
    LEFT JOIN noblebudgetrequest b 
        ON n.request_id = b.id AND (n.type IS NULL OR n.type = 'budget')
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