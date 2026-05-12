<?php
// index-fetch-my-requests.php
// Route: GET /fetchmyrequests
session_name('noblehome');
session_start();
include ROOT_PATH . '/network/connect.php';
if (empty($_SESSION['logged_in'])) { echo json_encode([]); exit; }

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);

$result = $conn->query("
    SELECT b.*,
           r.name  AS approver_name,
           rc.name AS receiver_name
    FROM noblebudgetrequest b
    LEFT JOIN noblerole  r  ON b.approved_by  = r.id
    LEFT JOIN noblerole  rc ON b.received_by  = rc.id
    WHERE b.user_id = $user_id
    ORDER BY b.id DESC
");

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['items'] = json_decode($row['items'], true);
    $data[] = $row;
}
echo json_encode($data);