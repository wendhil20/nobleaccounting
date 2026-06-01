<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);

if (empty($body['payment_date']) || empty($body['particulars'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Payment Date and Particulars are required.']);
    exit;
}

$id                    = !empty($body['id']) ? (int)$body['id'] : null;
$payment_date          = $body['payment_date'];
$collection_receipt_no = trim($body['collection_receipt_no'] ?? '') ?: null;
$type_of_sale          = trim($body['type_of_sale'] ?? '') ?: null;
$deposit_reference_no  = trim($body['deposit_reference_no'] ?? '') ?: null;
$payee                 = trim($body['payee'] ?? '') ?: null;
$payor                 = trim($body['payor'] ?? '') ?: null;
$particulars           = trim($body['particulars']);
$sales_person          = trim($body['sales_person'] ?? '') ?: null;
$amount                = isset($body['amount']) ? (float)$body['amount'] : 0;

$inserted_by = $_SESSION['username'] ?? 'Unknown';

if ($id) {
    // UPDATE — preserve original inserted_by
    $stmt = $conn->prepare("
        UPDATE noblegeneralsheet SET
            payment_date          = ?,
            collection_receipt_no = ?,
            type_of_sale          = ?,
            deposit_reference_no  = ?,
            payee                 = ?,
            payor                 = ?,
            particulars           = ?,
            sales_person          = ?,
            amount                = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        'ssssssssd' . 'i',
        $payment_date, $collection_receipt_no, $type_of_sale,
        $deposit_reference_no, $payee, $payor,
        $particulars, $sales_person, $amount,
        $id
    );
} else {
    // INSERT — record who added it
    $stmt = $conn->prepare("
        INSERT INTO noblegeneralsheet
            (payment_date, collection_receipt_no, type_of_sale, deposit_reference_no,
             payee, payor, particulars, sales_person, amount, inserted_by)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'ssssssssd' . 's',
        $payment_date, $collection_receipt_no, $type_of_sale,
        $deposit_reference_no, $payee, $payor,
        $particulars, $sales_person, $amount,
        $inserted_by
    );
}

$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);