<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);

if (empty($body['date']) || empty($body['particulars'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Date and Particulars are required.']);
    exit;
}

$id            = !empty($body['id']) ? (int)$body['id'] : null;
$entry_type    = in_array($body['entry_type'] ?? '', ['beginning', 'received']) ? $body['entry_type'] : 'received';
$date          = $body['date'];
$cash_inflows  = isset($body['cash_inflows']) ? (float)$body['cash_inflows'] : 0;
$voucher_no    = trim($body['voucher_no'] ?? '') ?: null;
$account_title = trim($body['account_title'] ?? '') ?: null;
$particulars   = trim($body['particulars']);
$department    = trim($body['department'] ?? '') ?: null;
$in_charge     = trim($body['in_charge'] ?? '') ?: null;
$actual        = isset($body['actual']) ? (float)$body['actual'] : 0;
$remarks       = trim($body['remarks'] ?? '') ?: null;
$reference     = trim($body['reference'] ?? '') ?: null;

// ✅ DAGDAG #1 — kunin ang session username
$inserted_by   = $_SESSION['username'] ?? 'Unknown';

// Kung beginning, 1 lang per month
if ($entry_type === 'beginning') {
    $month = date('Y-m', strtotime($date));
    $checkSql = "SELECT id FROM noblepettycashcustodian WHERE entry_type = 'beginning' AND DATE_FORMAT(date, '%Y-%m') = ?";
    if ($id) $checkSql .= " AND id != ?";
    $check = $conn->prepare($checkSql);
    if ($id) {
        $check->bind_param('si', $month, $id);
    } else {
        $check->bind_param('s', $month);
    }
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A Beginning Balance entry already exists for this month.']);
        exit;
    }
    $check->close();
}

if ($id) {
    // ✅ DAGDAG #2 — UPDATE: wag na baguhin ang inserted_by, original inserter pa rin
    $stmt = $conn->prepare("
        UPDATE noblepettycashcustodian SET
            entry_type    = ?,
            date          = ?,
            cash_inflows  = ?,
            voucher_no    = ?,
            account_title = ?,
            particulars   = ?,
            department    = ?,
            in_charge     = ?,
            actual        = ?,
            remarks       = ?,
            reference     = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        'ssdsssssdssi',
        $entry_type, $date, $cash_inflows, $voucher_no, $account_title,
        $particulars, $department, $in_charge,
        $actual, $remarks, $reference, $id
    );
} else {
    // ✅ DAGDAG #3 — INSERT: isama ang inserted_by
    $stmt = $conn->prepare("
        INSERT INTO noblepettycashcustodian
            (entry_type, date, cash_inflows, voucher_no, account_title, particulars, department, in_charge, actual, remarks, reference, inserted_by)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'ssdsssssdss' . 's',
        $entry_type, $date, $cash_inflows, $voucher_no, $account_title,
        $particulars, $department, $in_charge,
        $actual, $remarks, $reference, $inserted_by
    );
}

$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);