<?php
// index-submit-request.php
session_name('noblehome');
session_start();

include ROOT_PATH . '/network/connect.php';

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);

// Debug — tingnan kung ano ang natanggap
error_log(print_r($body, true));

$control_no     = trim($body['control_no'] ?? '');
$user_id        = intval($_SESSION['account_id'] ?? 0); // ← account_id ang tama
$requestor_name = trim($body['requestor_name'] ?? '');
$purpose        = trim($body['purpose'] ?? '');
$date_requested = trim($body['date_requested'] ?? '');
$sent_to        = intval($body['sent_to'] ?? 0);
$items          = json_encode($body['items'] ?? []);

if (!$control_no || !$requestor_name || !$purpose || !$sent_to || !$user_id) {
    echo json_encode([
        'success' => false, 
        'error'   => 'Missing fields',
        'debug'   => [
            'control_no'     => $control_no,
            'user_id'        => $user_id,
            'requestor_name' => $requestor_name,
            'purpose'        => $purpose,
            'sent_to'        => $sent_to,
        ]
    ]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO noblebudgetrequest 
    (control_no, user_id, requestor_name, purpose, date_requested, sent_to, items) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssis", $control_no, $user_id, $requestor_name, $purpose, $date_requested, $sent_to, $items);

$result = $stmt->execute();

if ($result && $stmt->affected_rows > 0) {
    // Kunin yung bagong insert na request id
    $new_request_id = $conn->insert_id;

    // I-insert ang notification para sa head
    $message = "New budget request from {$requestor_name}: {$purpose} check your request list.";
    $notif_stmt = $conn->prepare("INSERT INTO noblenotification (user_id, request_id, message) VALUES (?, ?, ?)");
    $notif_stmt->bind_param("iis", $sent_to, $new_request_id, $message);
    $notif_stmt->execute();
}

echo json_encode([
    'success'  => $result,
    'error'    => $conn->error,
    'affected' => $stmt->affected_rows
]);