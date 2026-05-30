<?php
// ping-request.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/network/cache-helper.php';

header('Content-Type: application/json');

$user_id = intval($_SESSION['account_id'] ?? 0);

// ← DAGDAG DITO — GET = fetch staff list
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $cached = getCache('ping_staff_list', 60); // 1 minute
    if ($cached !== false) {
        echo $cached;
        exit;
    }

    $result = $conn->query("
        SELECT id, name, email 
        FROM noblerole 
        WHERE position = 'staff'
    ");

    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }

    $json = json_encode($staff);
    setCache('ping_staff_list', $json);
    
    echo $json;
    exit;
}

// POST — send ping
$body = json_decode(file_get_contents('php://input'), true);
$request_id = intval($body['request_id'] ?? 0);
$staff_ids  = $body['staff_ids'] ?? [];
$message    = trim($body['message'] ?? '');

if (!$request_id || !$user_id || empty($staff_ids)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$req = $conn->query("SELECT control_no FROM noblebudgetrequest WHERE id = $request_id LIMIT 1");
$reqRow = $req->fetch_assoc();
$control_no = $reqRow['control_no'] ?? '';

$ping_message = $message
    ? '<i class="fa-solid fa-bell"></i> You were pinged on Request ' . $control_no . ': ' . $message
    : '<i class="fa-solid fa-bell"></i> You were pinged on Request ' . $control_no . '. Please check it.';

$success = true;
foreach ($staff_ids as $staff_id) {
    $staff_id = intval($staff_id);
    $stmt = $conn->prepare("
        INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id)
        VALUES (?, ?, ?, 0, NOW(), ?)
    ");
    $stmt->bind_param("iisi", $staff_id, $request_id, $ping_message, $user_id);
    if (!$stmt->execute()) $success = false;
}

echo json_encode(['success' => $success, 'control_no' => $control_no]);