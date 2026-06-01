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

$inserted_by = $_SESSION['username'] ?? 'Unknown';

if (!$date || !$particulars) {
    echo json_encode(['success' => false, 'message' => 'Date and Particulars are required.']);
    exit;
}

if ($id) {
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
    "ssssssdssssddddsdi",
    $date, $reference_no, $account_title, $particulars,
    $project_dept, $in_charge, $actual,
    $supplier_corp, $supplier_indiv, $address, $tin,
    $vatable_amount, $vat, $total, $non_vat,
    $no_sales_invoice, $vat_exempt, $id
);
} else {
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
        "ssssssdssssddddsss",
        $date, $reference_no, $account_title, $particulars,
        $project_dept, $in_charge, $actual,
        $supplier_corp, $supplier_indiv, $address, $tin,
        $vatable_amount, $vat, $total, $non_vat,
        $no_sales_invoice, $vat_exempt, $inserted_by
    );
}

$success = $stmt->execute();

// ── AUTO-LINK TO PROJECT MONITOR ──────────────────────────────────────────────
if ($success && $project_dept) {
    $petty_id = $id ? $id : $conn->insert_id;

    $ps = $conn->prepare("SELECT id, contract_amount FROM nobleprojectmonitor WHERE project_name = ? LIMIT 1");
    $ps->bind_param("s", $project_dept);
    $ps->execute();
    $ps->bind_result($linked_project_id, $contractAmount);
    $ps->fetch();
    $ps->close();

    if (!$linked_project_id) {
        // Walang matching project — i-rollback yung pettycash insert/update
        if (!$id) {
            $rb = $conn->prepare("DELETE FROM noblepettycashcustodiantwo WHERE id = ?");
            $rb->bind_param("i", $petty_id);
            $rb->execute();
            $rb->close();
        }

        echo json_encode([
            'success' => false,
            'message' => "No project found matching \"$project_dept\". Please create the project first in Project Monitor.",
        ]);
        exit;
    }

    // Check if may existing linked expense
    $chk = $conn->prepare("SELECT id FROM nobleprojectexpense WHERE pettycash_source_id = ? LIMIT 1");
    $chk->bind_param("i", $petty_id);
    $chk->execute();
    $chk->bind_result($existing_expense_id);
    $chk->fetch();
    $chk->close();

    if ($existing_expense_id) {
        $es = $conn->prepare("UPDATE nobleprojectexpense SET title=?, particulars=?, amount=?, payment_date=?, reference=? WHERE id=?");
        $es->bind_param("ssdssi", $account_title, $particulars, $actual, $date, $reference_no, $existing_expense_id);
        $es->execute();
        $es->close();
    } else {
        $es = $conn->prepare("INSERT INTO nobleprojectexpense (project_id, title, particulars, amount, payment_date, reference, pettycash_source_id) VALUES (?,?,?,?,?,?,?)");
        $es->bind_param("issdssi", $linked_project_id, $account_title, $particulars, $actual, $date, $reference_no, $petty_id);
        $es->execute();
        $es->close();
    }

    // Recompute income/loss
    $s = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM nobleprojectexpense WHERE project_id = ?");
    $s->bind_param("i", $linked_project_id);
    $s->execute();
    $s->bind_result($totalExpenses);
    $s->fetch();
    $s->close();

    $incomeLoss = $contractAmount - $totalExpenses;
    $s3 = $conn->prepare("UPDATE nobleprojectmonitor SET possible_income_loss = ? WHERE id = ?");
    $s3->bind_param("di", $incomeLoss, $linked_project_id);
    $s3->execute();
    $s3->close();
}
// ─────────────────────────────────────────────────────────────────────────────

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Saved.' : $conn->error,
    'insert_id' => $id ? $id : $conn->insert_id,
]);