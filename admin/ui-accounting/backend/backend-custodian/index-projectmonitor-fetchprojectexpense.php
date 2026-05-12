<?php
// index-projectmonitor-fetchprojectexpense.php — Route: GET /fetchprojectexpense
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$project_id = intval($_GET['project_id'] ?? 0);
if (!$project_id) { echo json_encode([]); exit; }

$stmt = $conn->prepare("SELECT * FROM nobleprojectexpense WHERE project_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $project_id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));