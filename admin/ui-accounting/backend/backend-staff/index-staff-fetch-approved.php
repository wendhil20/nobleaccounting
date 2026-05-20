<?php
// index-staff-fetch-approved.php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

// Approved pero hindi pa nareceive
$result = $conn->query("SELECT b.*, 
    n.name as sender_name, 
    n.email as sender_email,
    r.name as approver_name
    FROM noblebudgetrequest b
    LEFT JOIN nobleaccount n ON b.user_id = n.id
    LEFT JOIN noblerole r ON b.approved_by = r.id
    WHERE b.status = 'approved' 
    AND b.received_by IS NULL
    ORDER BY b.approved_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $row['attachments'] = json_decode($row['attachments'] ?? '[]', true); // ← dagdag
    $data[] = $row;
}

echo json_encode($data);