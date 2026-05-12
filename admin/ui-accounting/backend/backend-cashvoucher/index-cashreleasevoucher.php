<?php
//index-cashreleasevoucher.php
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/network/mailer.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$voucher_id = intval($body['voucher_id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);
$manual_name = trim($body['manual_name'] ?? '');
$manual_date = trim($body['manual_date'] ?? '');

if (!$voucher_id || !$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Kunin ang voucher + request info + requestor email
$vRow = $conn->query("
    SELECT v.*, b.control_no, b.purpose, b.user_id as requestor_id,
           a.email as requestor_email, a.name as requestor_name
    FROM noblevoucher v
    LEFT JOIN noblebudgetrequest b ON v.request_id = b.id
    LEFT JOIN nobleaccount a ON b.user_id = a.id
    WHERE v.id = $voucher_id LIMIT 1
")->fetch_assoc();

if (!$vRow) {
    echo json_encode(['success' => false, 'error' => 'Not found']);
    exit;
}

if ($manual_name) {
    // Manual receiver
    $stmt = $conn->prepare("UPDATE noblevoucher 
        SET status = 'released', released_by = ?, released_at = NOW(),
            manual_receiver_name = ?, manual_receiver_date = ?
        WHERE id = ?");
    $manual_date_val = $manual_date ?: date('Y-m-d');
    $stmt->bind_param("issi", $user_id, $manual_name, $manual_date_val, $voucher_id);
} else {
    $stmt = $conn->prepare("UPDATE noblevoucher 
        SET status = 'released', released_by = ?, released_at = NOW()
        WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $voucher_id);
}

$success = $stmt->execute();

// Send email sa requestor
if ($success && $vRow['requestor_email']) {
    $control_no = $vRow['control_no'];
    $purpose = $vRow['purpose'];
    $name = $vRow['requestor_name'];

    $subject = "Cash Voucher Released $control_no";
    // Kunin ang receiver name
    $receiverName = $manual_name ?: ($vRow['receiver_name'] ?? 'the designated receiver');

    $emailBody = "
<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
    <div style='background:#f97316;padding:20px;text-align:center;'>
        <h2 style='color:white;margin:0;'>NobleHome Accounting</h2>
    </div>
    <div style='padding:30px;background:#fff;border:1px solid #eee;'>
        <p style='font-size:15px;'>Hi <strong>$name</strong>,</p>
        <p style='font-size:14px;color:#555;'>Your cash voucher request has been <strong style='color:#16a34a;'>released</strong>.</p>
        <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin:20px 0;'>
            <p style='margin:0 0 8px;font-size:13px;'><strong>Control No.:</strong> $control_no</p>
            <p style='margin:0 0 8px;font-size:13px;'><strong>Purpose:</strong> $purpose</p>
            <p style='margin:0;font-size:13px;'><strong>Received By:</strong> $receiverName</p>
        </div>
        <p style='font-size:13px;color:#888;'>Please log in to the system to view and accept your voucher.</p>
    </div>
    <div style='padding:15px;text-align:center;font-size:11px;color:#aaa;'>
        &copy; " . date('Y') . " NobleHome Construction Corporation
    </div>
</div>";

    sendMail($vRow['requestor_email'], $name, $subject, $emailBody);
}

echo json_encode(['success' => $success]);