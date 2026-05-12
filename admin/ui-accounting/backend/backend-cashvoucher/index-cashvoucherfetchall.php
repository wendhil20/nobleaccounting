<?php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT b.*,
    n.name as requestor_name,
    v.id as voucher_id,
    v.payee as voucher_payee,
    v.address as voucher_address,
    v.status as voucher_status,
    v.title as voucher_title,
    v.second_no as voucher_second_no,
    v.purpose as voucher_purpose,
    v.prepared_by, v.prepared_at,
    v.certified_by, v.certified_at,
    v.approved_by, v.approved_at,
    v.received_by, v.received_at,
    prep.name as prepared_name,
    cert.name as certified_name,
    appr.name as approver_name,
    recv.name as receiver_name
    FROM noblebudgetrequest b
    LEFT JOIN noblerole n ON b.user_id = n.id
    LEFT JOIN noblevoucher v ON b.id = v.request_id
    LEFT JOIN noblerole prep ON v.prepared_by = prep.id
    LEFT JOIN noblerole cert ON v.certified_by = cert.id
    LEFT JOIN noblerole appr ON v.approved_by = appr.id
    LEFT JOIN noblerole recv ON v.received_by = recv.id
    WHERE v.id IS NOT NULL
    ORDER BY v.created_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $data[] = $row;
}
echo json_encode($data);