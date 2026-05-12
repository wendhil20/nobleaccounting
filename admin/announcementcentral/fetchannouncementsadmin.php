<?php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$result = $conn->query("
    SELECT a.*, n.name as posted_by_name
    FROM nobleannouncement a
    LEFT JOIN noblerole n ON n.id = a.posted_by
    ORDER BY a.created_at DESC
");

$rows = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);