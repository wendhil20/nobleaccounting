<?php
// index-projectmonitor-saveprojectexpense.php — Route: POST /saveprojectexpense
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id = intval($body['id'] ?? 0);
$project_id = intval($body['project_id'] ?? 0);
$particulars = trim($body['particulars'] ?? '');
$amount = $body['amount'] !== '' ? floatval($body['amount']) : null;
$mode_of_payment = trim($body['mode_of_payment'] ?? '');
$payment_date = trim($body['payment_date'] ?? '') ?: null;
$reference = trim($body['reference'] ?? '');
$remarks = trim($body['remarks'] ?? '');
$title = trim($body['title'] ?? '');

// UPDATE:
$stmt = $conn->prepare("UPDATE nobleprojectexpense SET title=?, particulars=?, amount=?, mode_of_payment=?, payment_date=?, reference=?, remarks=? WHERE id=?");
$stmt->bind_param("ssdssssi", $title, $particulars, $amount, $mode_of_payment, $payment_date, $reference, $remarks, $id);

// INSERT:
$stmt = $conn->prepare("INSERT INTO nobleprojectexpense (project_id, title, particulars, amount, mode_of_payment, payment_date, reference, remarks) VALUES (?,?,?,?,?,?,?,?)");
$stmt->bind_param("issdssss", $project_id, $title, $particulars, $amount, $mode_of_payment, $payment_date, $reference, $remarks);

$result = $stmt->execute();
echo json_encode(['success' => $result, 'error' => $conn->error]);