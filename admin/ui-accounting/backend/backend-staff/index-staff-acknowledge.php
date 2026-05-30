<?php
// index-staff-acknowlegde.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/network/cache-helper.php';

header('Content-Type: application/json');

$cached = getCache('staff_acknowledged_requests', 60);
if ($cached !== false) {
    echo $cached;
    exit;
}

$result = $conn->query("SELECT b.*, 
    n.name as sender_name, 
    n.email as sender_email,
    r.name as approver_name,
    COALESCE(
        b.approver_signature_path,
        CASE WHEN b.status = 'approved' 
             THEN (SELECT path FROM noblesignature WHERE user_id = b.approved_by AND is_active = 1 LIMIT 1)
             ELSE NULL 
        END
    ) as approver_signature,
    b.receiver_signature_path as receiver_signature,
    recv.name as receiver_name
    FROM noblebudgetrequest b
    LEFT JOIN nobleaccount n ON b.user_id = n.id
    LEFT JOIN noblerole r ON b.approved_by = r.id
    LEFT JOIN noblerole recv ON b.received_by = recv.id
    WHERE b.status = 'approved' AND b.received_by IS NOT NULL
    ORDER BY b.received_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $row['attachments'] = json_decode($row['attachments'] ?? '[]', true);
    $data[] = $row;
}

$json = json_encode($data);
setCache('staff_acknowledged_requests', $json);
echo $json;