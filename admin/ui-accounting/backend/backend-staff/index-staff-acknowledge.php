<?php
// index-staff-acknowlegde.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

// Lahat ng received na requests
$result = $conn->query("SELECT b.*, 
    n.name as sender_name, 
    n.email as sender_email,
    r.name as approver_name,
    recv.name as receiver_name
    FROM noblebudgetrequest b
    LEFT JOIN nobleaccount n ON b.user_id = n.id
    LEFT JOIN noblerole r ON b.approved_by = r.id
    LEFT JOIN noblerole recv ON b.received_by = recv.id
    WHERE b.received_by IS NOT NULL
    ORDER BY b.received_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $data[] = $row;
}

echo json_encode($data);