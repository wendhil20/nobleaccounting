<?php
// noble-fetch-pettycash-department.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$stmt = $conn->prepare("
    SELECT id, name 
    FROM noblepettycashdepartment 
    WHERE is_active = 1 
    ORDER BY name ASC
");
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$stmt->close();

echo json_encode($rows);