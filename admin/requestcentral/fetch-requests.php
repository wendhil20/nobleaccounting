<?php
// admin/requestcentral/fetch-requests.php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);

if (!$user_id) {
    echo json_encode([]);
    exit;
}

$result = $conn->query("SELECT b.*, 
    n.name as sender_name, 
    n.email as sender_email,
    r.name as approver_name,
    r.role as approver_role,
    recv.name as receiver_name
    FROM noblebudgetrequest b
    LEFT JOIN nobleaccount n ON b.user_id = n.id
    LEFT JOIN noblerole r ON b.approved_by = r.id
    LEFT JOIN noblerole recv ON b.received_by = recv.id
    WHERE b.sent_to = $user_id
    ORDER BY b.created_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $data[] = $row;
}

echo json_encode($data);