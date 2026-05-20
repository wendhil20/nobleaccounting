<?php
// index-custodian-submit-voucher.php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body       = json_decode(file_get_contents('php://input'), true);
$request_id = intval($body['request_id'] ?? 0);
$payee      = trim($body['payee'] ?? '');
$address    = trim($body['address'] ?? '');
$user_id    = intval($_SESSION['account_id'] ?? 0);
$purpose    = trim($body['purpose'] ?? '');
$title      = trim($body['title'] ?? '');
$second_no  = trim($body['second_no'] ?? '');

if (!$request_id || !$payee || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$req = $conn->query("SELECT control_no FROM noblebudgetrequest WHERE id = $request_id LIMIT 1");
$row = $req->fetch_assoc();
if (!$row) { echo json_encode(['success' => false, 'error' => 'Not found']); exit; }

$check = $conn->query("SELECT id FROM noblevoucher WHERE request_id = $request_id LIMIT 1");
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Already submitted']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO noblevoucher 
    (request_id, control_no, payee, address, purpose, title, second_no, certified_by, certified_at, created_at, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 'voucher_approval')");
$stmt->bind_param("issssssi", $request_id, $row['control_no'], $payee, $address, $purpose, $title, $second_no, $user_id);
$success = $stmt->execute();

// Notify custoassistant
if ($success) {
    $control_no = $row['control_no'];
    $message = "Cash voucher for $control_no has been submitted and needs to be prepared. Check Approval Cash Voucher.";

    $assistants = $conn->query("
        SELECT id FROM noblerole 
        WHERE role = 'ACCOUNTING AND FINANCE DEPARTMENT' 
        AND position = 'custoassistant'
    ");

    while ($a = $assistants->fetch_assoc()) {
    $aid = intval($a['id']);
    
    $link = '/cashvoucherdashboard'; // ← page ng custoassistant
    
    $stmt2 = $conn->prepare("
        INSERT INTO noblenotification (user_id, request_id, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 0, NOW(), ?, ?)
    ");
    $stmt2->bind_param("iisis", $aid, $request_id, $message, $user_id, $link);
    $stmt2->execute();
}
}

echo json_encode(['success' => $success]);