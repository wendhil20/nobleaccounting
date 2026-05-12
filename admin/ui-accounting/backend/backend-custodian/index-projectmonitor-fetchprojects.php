<?php
// index-projectmonitor-fetchprojects.php — Route: GET /fetchprojects
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT * FROM nobleprojectmonitor ORDER BY created_at DESC");
$data = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data);