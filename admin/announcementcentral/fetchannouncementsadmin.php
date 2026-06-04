<?php
// fecthannouncementsadmin.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

// Then fetch active ones
$result = $conn->query("
    SELECT a.*, n.name as posted_by_name
    FROM nobleannouncement a
    LEFT JOIN noblerole n ON n.id = a.posted_by
    WHERE a.is_active = 1
      AND (a.expires_at IS NULL OR a.expires_at > NOW())
    ORDER BY a.created_at DESC
");

$rows = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);