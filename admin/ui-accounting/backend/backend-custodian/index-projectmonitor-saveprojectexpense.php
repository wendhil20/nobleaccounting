<?php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$id              = intval($body['id'] ?? 0);
$project_id      = intval($body['project_id'] ?? 0);
$title           = trim($body['title'] ?? '');
$particulars     = trim($body['particulars'] ?? '');
$amount          = $body['amount'] !== '' ? floatval($body['amount']) : null;
$mode_of_payment = trim($body['mode_of_payment'] ?? '');
$payment_date    = trim($body['payment_date'] ?? '') ?: null;
$reference       = trim($body['reference'] ?? '');
$remarks         = trim($body['remarks'] ?? '');

// ── INSERT or UPDATE ──────────────────────────────────────────────────────────
if ($id) {
    $stmt = $conn->prepare("UPDATE nobleprojectexpense SET title=?, particulars=?, amount=?, mode_of_payment=?, payment_date=?, reference=?, remarks=? WHERE id=?");
    $stmt->bind_param("ssdssssi", $title, $particulars, $amount, $mode_of_payment, $payment_date, $reference, $remarks, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO nobleprojectexpense (project_id, title, particulars, amount, mode_of_payment, payment_date, reference, remarks) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issdssss", $project_id, $title, $particulars, $amount, $mode_of_payment, $payment_date, $reference, $remarks);
}

$result = $stmt->execute();

// ── RECOMPUTE POSSIBLE INCOME/LOSS at i-save sa nobleprojectmonitor ───────────
if ($result && $project_id) {
    $s = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM nobleprojectexpense WHERE project_id = ?");
    $s->bind_param("i", $project_id);
    $s->execute();
    $s->bind_result($totalExpenses);
    $s->fetch();
    $s->close();

    $s2 = $conn->prepare("SELECT COALESCE(contract_amount, 0) FROM nobleprojectmonitor WHERE id = ?");
    $s2->bind_param("i", $project_id);
    $s2->execute();
    $s2->bind_result($contractAmount);
    $s2->fetch();
    $s2->close();

    $incomeLoss = $contractAmount - $totalExpenses;

    $s3 = $conn->prepare("UPDATE nobleprojectmonitor SET possible_income_loss = ? WHERE id = ?");
    $s3->bind_param("di", $incomeLoss, $project_id);
    $s3->execute();
    $s3->close();
}

echo json_encode(['success' => $result, 'error' => $conn->error]);