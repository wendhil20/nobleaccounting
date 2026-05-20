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

error_log(print_r($body, true));

$control_no     = trim($body['control_no'] ?? '');
$user_id        = intval($_SESSION['account_id'] ?? 0);
$requestor_name = trim($body['requestor_name'] ?? '');
$purpose        = trim($body['purpose'] ?? '');
$date_requested = trim($body['date_requested'] ?? '');
$sent_to        = intval($body['sent_to'] ?? 0);
$items          = json_encode($body['items'] ?? []);

// ── Attachments ──────────────────────────────────────
$attachments     = $body['attachments'] ?? [];
$savedPaths      = [];

if (!empty($attachments)) {
    $uploadDir = ROOT_PATH . '/uploads/attachments/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];

    foreach ($attachments as $file) {
        // Detect MIME from base64 header
        if (!preg_match('#^data:(image/\w+);base64,#', $file['data'], $matches)) continue;
        if (!in_array($matches[1], $allowed)) continue;

        $base64  = preg_replace('#^data:image/\w+;base64,#', '', $file['data']);
        $binary  = base64_decode($base64);
        if (!$binary) continue;

        $filename = uniqid('att_', true) . '.webp';
        file_put_contents($uploadDir . $filename, $binary);
        $savedPaths[] = 'uploads/attachments/' . $filename;
    }
}

$attachmentsJson = json_encode($savedPaths);
// ─────────────────────────────────────────────────────

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
    (control_no, user_id, requestor_name, purpose, date_requested, sent_to, items, attachments) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssiss",
    $control_no,
    $user_id,
    $requestor_name,
    $purpose,
    $date_requested,
    $sent_to,
    $items,
    $attachmentsJson
);

$result = $stmt->execute();

if ($result && $stmt->affected_rows > 0) {
    $new_request_id = $conn->insert_id;

    $message = "New budget request from {$requestor_name}: {$purpose} check your request list.";
    
    $link = '/accounting'; // ← didirekta sa request list ng head
    
    $notif_stmt = $conn->prepare("
        INSERT INTO noblenotification (user_id, request_id, message, link) 
        VALUES (?, ?, ?, ?)
    ");
    $notif_stmt->bind_param("iiss", $sent_to, $new_request_id, $message, $link);
    $notif_stmt->execute();
}

echo json_encode([
    'success'  => $result,
    'error'    => $conn->error,
    'affected' => $stmt->affected_rows
]);