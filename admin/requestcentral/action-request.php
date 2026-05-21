<?php
// action-request.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id = intval($body['id'] ?? 0);
$action = in_array($body['action'], ['approved', 'rejected']) ? $body['action'] : null;
$user_id = intval($_SESSION['account_id'] ?? 0);
$comment = trim($body['comment'] ?? '');

if (!$id || !$action || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

if ($action === 'approved') {
    $stmt = $conn->prepare("UPDATE noblebudgetrequest 
        SET status = ?, approved_by = ?, approved_at = NOW(), reject_comment = NULL
        WHERE id = ? AND sent_to = ?");
    $stmt->bind_param("siii", $action, $user_id, $id, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE noblebudgetrequest 
        SET status = ?, reject_comment = ?, approved_by = NULL, approved_at = NULL
        WHERE id = ? AND sent_to = ?");
    $stmt->bind_param("ssii", $action, $comment, $id, $user_id);
}

$success = $stmt->execute();
$affected = $stmt->affected_rows;

// Mag-notify sa lahat ng STAFF pagkatapos ng action
if ($success && $affected > 0) {

    // ← user_id ang column, hindi sent_by
    $req = $conn->query("SELECT control_no, user_id FROM noblebudgetrequest WHERE id = $id LIMIT 1");
    $reqRow = $req->fetch_assoc();
    $control_no = $reqRow['control_no'] ?? '';
    $requestor_id = intval($reqRow['user_id'] ?? 0);

    $message = $action === 'approved'
        ? "Request $control_no has been approved."
        : "Request $control_no has been rejected. Reason: $comment";

 // ← FIXED: $requestor_id na, hindi $requestor_account_id
    if ($requestor_id) {
        $stmtUserNotif = $conn->prepare("
            INSERT INTO nobleusernotification (user_id, request_id, message, is_read, created_at, sender_id)
            VALUES (?, ?, ?, 0, NOW(), ?)
        ");
        $stmtUserNotif->bind_param("iisi", $requestor_id, $id, $message, $user_id);
        $stmtUserNotif->execute();
    }

    // Existing — notify staff sa accounting
    $staffResult = $conn->query("
        SELECT id FROM noblerole 
        WHERE role = 'ACCOUNTING AND FINANCE DEPARTMENT' 
        AND position = 'staff'
    ");

    while ($staff = $staffResult->fetch_assoc()) {
        $staff_id = intval($staff['id']);
        $link = '/accountingstaff'; // ← page ng staff

        $stmt2 = $conn->prepare("
            INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id, link)
            VALUES (?, ?, ?, 0, NOW(), ?, ?)
        ");
        $stmt2->bind_param("iisis", $staff_id, $id, $message, $user_id, $link);
        $stmt2->execute();
    }
}

echo json_encode([
    'success' => $success,
    'affected' => $affected,
    'debug' => [
        'user_id' => $user_id,
        'id' => $id,
        'action' => $action,
        'comment' => $comment
    ]
]);