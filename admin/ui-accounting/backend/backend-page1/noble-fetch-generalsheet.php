<?php
// noble-fetch-generalsheet.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$month = $_GET['month'] ?? date('Y-m');

// Validate month format
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}

$stmt = $conn->prepare("
    SELECT
        id,
        payment_date,
        collection_receipt_no,
        type_of_sale,
        deposit_reference_no,
        payee,
        payor,
        particulars,
        sales_person,
        department,
        reference_mode,
        amount,
        inserted_by,
        created_at
    FROM noblegeneralsheet
    WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?
    ORDER BY payment_date ASC, id ASC
");

$stmt->bind_param('s', $month);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$stmt->close();

echo json_encode($rows);