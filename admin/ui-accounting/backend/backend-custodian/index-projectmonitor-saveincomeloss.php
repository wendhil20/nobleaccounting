<?php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body       = json_decode(file_get_contents('php://input'), true);
$project_id = intval($body['project_id'] ?? 0);
$incomeLoss = floatval($body['income_loss'] ?? 0);

$stmt = $conn->prepare("UPDATE nobleprojectmonitor SET possible_income_loss = ? WHERE id = ?");
$stmt->bind_param("di", $incomeLoss, $project_id);
echo json_encode(['success' => $stmt->execute()]);