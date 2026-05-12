<?php
// fetchannouncements.php
session_name('noblerequest');
session_start();
include ROOT_PATH . '/network/connect.php';
header('Content-Type: application/json');

$result = $conn->query("
    SELECT a.*, n.name as posted_by_name
    FROM nobleannouncement a
    LEFT JOIN noblerole n ON n.id = a.posted_by
    WHERE a.is_active = 1
    ORDER BY a.created_at DESC
    LIMIT 10
");

$rows = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);