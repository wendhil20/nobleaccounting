<?php
// index-projectmonitor-fetchproject.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT id, name FROM noblepettycashdepartment WHERE is_active = 1 ORDER BY name ASC");
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);