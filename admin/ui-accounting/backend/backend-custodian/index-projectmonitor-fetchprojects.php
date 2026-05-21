<?php
// index-projectmonitor-fetchprojects.php — Route: GET /fetchprojects

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$result = $conn->query("
    SELECT
        p.*,
        COALESCE(SUM(b.amount), 0) AS total_credited
    FROM nobleprojectmonitor p
    LEFT JOIN nobleprojectbilling b ON b.project_id = p.id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);