<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);

$id               = intval($body['id'] ?? 0);
$date             = $body['date'] ?? null;
$reference_no     = trim($body['reference_no'] ?? '');
$account_title    = trim($body['account_title'] ?? '');
$particulars      = trim($body['particulars'] ?? '');
$project_dept     = trim($body['project_department'] ?? '');
$in_charge        = trim($body['in_charge'] ?? '');
$actual           = floatval($body['actual'] ?? 0);
$supplier_corp    = trim($body['supplier_name_corp'] ?? '');
$supplier_indiv   = trim($body['supplier_name_indiv'] ?? '');
$address          = trim($body['address'] ?? '');
$tin              = trim($body['tin'] ?? '');
$vatable_amount   = floatval($body['vatable_amount'] ?? 0);
$vat              = floatval($body['vat'] ?? 0);
$total            = floatval($body['total'] ?? 0);
$non_vat          = floatval($body['non_vat'] ?? 0);
$no_sales_invoice = trim($body['no_sales_invoice'] ?? '');
$vat_exempt       = floatval($body['vat_exempt'] ?? 0);

// ✅ DAGDAG — kunin sa session
$inserted_by = $_SESSION['username'] ?? 'Unknown';

if (!$date || !$particulars) {
    echo json_encode(['success' => false, 'message' => 'Date and Particulars are required.']);
    exit;
}

if ($id) {
    // UPDATE — huwag baguhin ang inserted_by
    $stmt = $conn->prepare("
        UPDATE noblepettycashcustodiantwo SET
            date = ?, reference_no = ?, account_title = ?, particulars = ?,
            project_department = ?, in_charge = ?, actual = ?,
            supplier_name_corp = ?, supplier_name_indiv = ?, address = ?, tin = ?,
            vatable_amount = ?, vat = ?, total = ?, non_vat = ?,
            no_sales_invoice = ?, vat_exempt = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param(
        "ssssssdssssddddssdi",
        $date, $reference_no, $account_title, $particulars,
        $project_dept, $in_charge, $actual,
        $supplier_corp, $supplier_indiv, $address, $tin,
        $vatable_amount, $vat, $total, $non_vat,
        $no_sales_invoice, $vat_exempt, $id
    );
} else {
    // ✅ INSERT — isama ang inserted_by
    $stmt = $conn->prepare("
        INSERT INTO noblepettycashcustodiantwo
            (date, reference_no, account_title, particulars,
             project_department, in_charge, actual,
             supplier_name_corp, supplier_name_indiv, address, tin,
             vatable_amount, vat, total, non_vat,
             no_sales_invoice, vat_exempt, inserted_by, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param(
        "ssssssdssssddddsss",  // ✅ dagdag na 's' sa dulo para sa inserted_by
        $date, $reference_no, $account_title, $particulars,
        $project_dept, $in_charge, $actual,
        $supplier_corp, $supplier_indiv, $address, $tin,
        $vatable_amount, $vat, $total, $non_vat,
        $no_sales_invoice, $vat_exempt, $inserted_by
    );
}

$success = $stmt->execute();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Saved.' : $conn->error,
    'insert_id' => $id ? $id : $conn->insert_id,
]);