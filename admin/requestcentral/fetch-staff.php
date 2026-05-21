<?php
// index-fetch-staff.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$result = $conn->query("
    SELECT r.id, n.name, n.email 
    FROM noblerole r
    LEFT JOIN nobleaccount n ON r.account_id = n.id
    WHERE r.role = 'ACCOUNTING AND FINANCE DEPARTMENT'
    AND r.position = 'staff'
");

$staff = [];
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}

echo json_encode($staff);