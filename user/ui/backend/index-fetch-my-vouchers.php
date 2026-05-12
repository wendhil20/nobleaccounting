<?php
// user/ui/index-fetch-my-vouchers.php
session_name('noblehome');
session_start();
include ROOT_PATH . '/network/connect.php';
if (empty($_SESSION['logged_in'])) { echo json_encode([]); exit; }
header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);

$result = $conn->query("
    SELECT v.*, b.control_no, b.purpose, b.requestor_name, b.date_requested, b.items,
           prep.name as prepared_name, v.prepared_at,
           cert.name as certified_name, v.certified_at,
           appr.name as approver_name, v.approved_at,
           rel.name as released_by_name,
           recv.name as receiver_name, v.received_at
    FROM noblevoucher v
    LEFT JOIN noblebudgetrequest b ON v.request_id = b.id
    LEFT JOIN noblerole prep ON v.prepared_by = prep.id
    LEFT JOIN noblerole cert ON v.certified_by = cert.id
    LEFT JOIN noblerole appr ON v.approved_by = appr.id
    LEFT JOIN noblerole rel ON v.released_by = rel.id
    LEFT JOIN nobleaccount recv ON v.received_by = recv.id
    WHERE b.user_id = $user_id
    AND v.status = 'released'
    ORDER BY v.released_at DESC
");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $data[] = $row;
}
echo json_encode($data);