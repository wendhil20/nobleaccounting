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

$id = !empty($body['id']) ? (int) $body['id'] : null;
$payment_date = $body['payment_date'];
$collection_receipt_no = trim($body['collection_receipt_no'] ?? '') ?: null;
$deposit_reference_no = trim($body['deposit_reference_no'] ?? '') ?: null;
$reference_mode = in_array($body['reference_mode'] ?? '', ['collection', 'deposit']) ? $body['reference_mode'] : 'collection';
$reference = trim($body['reference'] ?? '') ?: null;
$department = trim($body['department'] ?? '') ?: null;
$type_of_sale = trim($body['type_of_sale'] ?? '') ?: null;
$payee = trim($body['payee'] ?? '') ?: null;
$payor = trim($body['payor'] ?? '') ?: null;
$particulars = trim($body['particulars']);
$sales_person = trim($body['sales_person'] ?? '') ?: null;
$amount = isset($body['amount']) ? (float) $body['amount'] : 0;
$inserted_by = $_SESSION['username'] ?? 'Unknown';

if ($id) {
    $stmt = $conn->prepare("
        UPDATE noblegeneralsheet SET
            payment_date          = ?,
            collection_receipt_no = ?,
            deposit_reference_no  = ?,
            reference_mode        = ?,
            reference             = ?,
            department            = ?,
            type_of_sale          = ?,
            payee                 = ?,
            payor                 = ?,
            particulars           = ?,
            sales_person          = ?,
            amount                = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        'ssssssssssdsi',
        $payment_date,
        $collection_receipt_no,
        $deposit_reference_no,
        $reference_mode,
        $reference,
        $department,
        $type_of_sale,
        $payee,
        $payor,
        $particulars,
        $sales_person,
        $amount,
        $id
    );
} else {
    $stmt = $conn->prepare("
        INSERT INTO noblegeneralsheet
            (payment_date, collection_receipt_no, deposit_reference_no,
             reference_mode, reference, department,
             type_of_sale, payee, payor,
             particulars, sales_person, amount, inserted_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'ssssssssssdss',
        $payment_date,
        $collection_receipt_no,
        $deposit_reference_no,
        $reference_mode,
        $reference,
        $department,
        $type_of_sale,
        $payee,
        $payor,
        $particulars,
        $sales_person,
        $amount,
        $inserted_by
    );
}

$stmt->execute();
$saved_id = $id ? $id : $conn->insert_id;
$stmt->close();

// ── AUTO-LINK TO PROJECT BILLING ──────────────────────────────────────────────
if ($department) {
    // Find project matching the department name
    $ps = $conn->prepare("SELECT id FROM nobleprojectmonitor WHERE project_name = ? LIMIT 1");
    $ps->bind_param("s", $department);
    $ps->execute();
    $ps->bind_result($linked_project_id);
    $ps->fetch();
    $ps->close();

    if ($linked_project_id) {
        // Check if already linked
        $chk = $conn->prepare("SELECT id FROM nobleprojectbilling WHERE generalsheet_source_id = ? LIMIT 1");
        $chk->bind_param("i", $saved_id);
        $chk->execute();
        $chk->bind_result($existing_billing_id);
        $chk->fetch();
        $chk->close();

        if ($existing_billing_id) {
            // Update existing
            $bs = $conn->prepare("UPDATE nobleprojectbilling SET particulars=?, amount=?, payment_date=?, reference=? WHERE id=?");
            $bs->bind_param("ssdsi", $particulars, $amount, $payment_date, $reference, $existing_billing_id);
            $bs->execute();
            $bs->close();
        } else {
            // Insert new
            $bs = $conn->prepare("INSERT INTO nobleprojectbilling (project_id, particulars, amount, payment_date, reference, generalsheet_source_id) VALUES (?,?,?,?,?,?)");
            $bs->bind_param("isdssi", $linked_project_id, $particulars, $amount, $payment_date, $reference, $saved_id);
            $bs->execute();
            $bs->close();
        }
    }
}
// ─────────────────────────────────────────────────────────────────────────────

echo json_encode(['success' => true]);