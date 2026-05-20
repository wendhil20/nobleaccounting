<?php
// index-staff-mark-received.php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id = intval($body['id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);

if (!$id || !$user_id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("UPDATE noblebudgetrequest 
    SET received_by = ?, received_at = NOW()
    WHERE id = ? AND status = 'approved'");
$stmt->bind_param("ii", $user_id, $id);
$success = $stmt->execute();
$affected = $stmt->affected_rows;

// Notify custodians
if ($success && $affected > 0) {
    $req = $conn->query("SELECT control_no FROM noblebudgetrequest WHERE id = $id LIMIT 1");
    $reqRow = $req->fetch_assoc();
    $control_no = $reqRow['control_no'] ?? '';
    $message = "Request $control_no has been marked as received and ready for Cash Voucher. check your cash voucher request.";

    $custodians = $conn->query("
        SELECT id FROM noblerole 
        WHERE role = 'ACCOUNTING AND FINANCE DEPARTMENT' 
        AND position = 'custodian'
    ");

    while ($c = $custodians->fetch_assoc()) {
        $cid = intval($c['id']);

        $link = '/accountingcustodian'; // ← page ng custodian

        $stmt2 = $conn->prepare("
        INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 0, NOW(), ?, ?)
    ");
        $stmt2->bind_param("iisis", $cid, $id, $message, $user_id, $link);
        $stmt2->execute();
    }

}

echo json_encode(['success' => $success, 'affected' => $affected]);