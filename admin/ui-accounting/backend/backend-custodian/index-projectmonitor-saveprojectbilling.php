<?php
// index-projectmonitor-saveprojectbilling.php — Route: POST /saveprojectbilling

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body         = json_decode(file_get_contents('php://input'), true);
$id           = intval($body['id'] ?? 0);
$project_id   = intval($body['project_id'] ?? 0);
$particulars  = trim($body['particulars'] ?? '');
$amount       = $body['amount'] !== '' ? floatval($body['amount']) : null;
$bank_check   = trim($body['bank_check'] ?? '');
$payment_date = trim($body['payment_date'] ?? '') ?: null;
$reference    = trim($body['reference'] ?? '');
$remarks      = trim($body['remarks'] ?? '');

if ($id) {
    $stmt = $conn->prepare("UPDATE nobleprojectbilling SET particulars=?, amount=?, bank_check=?, payment_date=?, reference=?, remarks=? WHERE id=?");
   $stmt->bind_param("sdssssi", $particulars, $amount, $bank_check, $payment_date, $reference, $remarks, $id);

} else {
    $stmt = $conn->prepare("INSERT INTO nobleprojectbilling (project_id, particulars, amount, bank_check, payment_date, reference, remarks) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("isdssss", $project_id, $particulars, $amount, $bank_check, $payment_date, $reference, $remarks);

}

$result = $stmt->execute();
echo json_encode(['success' => $result, 'error' => $conn->error]);