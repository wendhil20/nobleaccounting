<?php
// index-fetch-heads.php

include ROOT_PATH . '/network/connect.php';

if (empty($_SESSION['logged_in'])) {
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$result = $conn->query("SELECT id, name, role FROM noblerole WHERE position = 'head' ORDER BY role ASC");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);