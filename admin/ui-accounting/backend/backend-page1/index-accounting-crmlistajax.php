<?php
// index-accounting-crmlistajax.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

$currentUserId = intval($_SESSION['account_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// Departments that get notified once a submission is marked Notice to
// Proceed (production-side departments — they can now start work).
// ⚠️ verify this is the exact set the business wants notified.
const ACCT_NTP_NOTIFY_ROLES = [ROLE_CUTTING, ROLE_OPERATIONS, ROLE_DESIGNER];

// Where deposit slip uploads are stored. ⚠️ adjust to match wherever the
// rest of the system keeps uploaded files (e.g. an existing upload
// helper/constant), if different from this.
const ACCT_DEPOSIT_UPLOAD_DIR = ROOT_PATH . '/uploads/deposit-slips';
const ACCT_DEPOSIT_UPLOAD_URL_PREFIX = '/uploads/deposit-slips';
const ACCT_DEPOSIT_ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png'];
const ACCT_DEPOSIT_MAX_BYTES = 10 * 1024 * 1024; // 10MB

// Same labeling convention as check2dquotationajax.php's chk2dRoleLabel().
function acctListRoleLabel(?string $role): string
{
    if ($role === 'sales') return 'Sales';
    if ($role === 'designer') return 'Designer';
    if ($role === 'accounting') return 'Accounting';
    return '—';
}

function acctListBaseQuery(): string
{

    return "
        SELECT
            q.id, q.inquiry_id,
            q.design_2d_path, q.design_2d_uploaded_by, q.design_2d_uploaded_role,
            q.design_2d_review_status, q.design_2d_remarks,
            q.quotation_path, q.quotation_uploaded_by, q.quotation_uploaded_role,
            q.quotation_review_status, q.quotation_remarks,
            q.include_3d, q.design_3d_stage, q.design_3d_path,
            q.design_3d_uploaded_by, q.design_3d_uploaded_role,
            q.design_3d_review_status, q.design_3d_remarks,
            q.status, q.reviewed_at, q.submitted_at,
            q.deposit_status, q.deposit_slip_path, q.deposit_amount,
            q.deposit_payment_method_id, q.deposit_uploaded_by,
            q.deposit_uploaded_role, q.deposit_uploaded_at,
            i.control_no, i.client_name, i.contact_number, i.project_type, i.branch,
            i.address, i.project_scope, i.measuring_space, i.measurement_datetime,
            i.contract_amount, i.designer_id, i.sales_staff_id,
            d.name AS designer_name,
            s.name AS sales_staff_name,
            d2u.name AS design_2d_uploader_name,
            qtu.name AS quotation_uploader_name,
            d3u.name AS design_3d_uploader_name,
            depu.name AS deposit_uploader_name,
            pm.name AS deposit_payment_method_name,
            n.id AS notification_id, n.is_read
        FROM noblecrm_2dquotation q
        JOIN (
            SELECT inquiry_id, MAX(id) AS max_id
            FROM noblecrm_2dquotation
            GROUP BY inquiry_id
        ) latest ON latest.inquiry_id = q.inquiry_id AND latest.max_id = q.id
        JOIN noblecrminquiry i ON i.id = q.inquiry_id
        LEFT JOIN noblerole d ON d.id = i.designer_id
        LEFT JOIN noblerole s ON s.id = i.sales_staff_id
        LEFT JOIN noblerole d2u ON d2u.id = q.design_2d_uploaded_by
        LEFT JOIN noblerole qtu ON qtu.id = q.quotation_uploaded_by
        LEFT JOIN noblerole d3u ON d3u.id = q.design_3d_uploaded_by
        LEFT JOIN noblerole depu ON depu.id = q.deposit_uploaded_by
        LEFT JOIN noblecrm_paymentmethod pm ON pm.id = q.deposit_payment_method_id
        LEFT JOIN noblenotification n ON n.request_id = q.inquiry_id AND n.user_id = ?
    ";
}

function acctListFormatRow(array $row): array
{
    // Whether the 3D section is worth showing at all — either this
    // submission bundled a 3D file, or the sequential 3D stage has moved
    // past its initial 'Locked' default.
    $show3d = ((int) $row['include_3d'] === 1) || (($row['design_3d_stage'] ?? 'Locked') !== 'Locked');

    return [
        'id'                    => (int) $row['id'],
        'inquiry_id'            => (int) $row['inquiry_id'],
        'control_no'            => $row['control_no'],
        'client_name'           => $row['client_name'],
        'contact_number'        => $row['contact_number'],
        'project_type'          => $row['project_type'],
        'branch'                => $row['branch'],
        'address'               => $row['address'],
        'project_scope'         => $row['project_scope'],
        'measuring_space'       => $row['measuring_space'],
        'measurement_datetime'  => $row['measurement_datetime'],
        'contract_amount'       => $row['contract_amount'],
        'designer_name'         => $row['designer_name'],
        'sales_staff_name'      => $row['sales_staff_name'],

        'design_2d_path'          => $row['design_2d_path'],
        'design_2d_uploader_name' => $row['design_2d_uploader_name'] ?? '—',
        'design_2d_uploaded_role' => acctListRoleLabel($row['design_2d_uploaded_role']),
        'design_2d_review_status' => $row['design_2d_review_status'] ?? 'Pending',
        'design_2d_remarks'       => $row['design_2d_remarks'],

        'quotation_path'          => $row['quotation_path'],
        'quotation_uploader_name' => $row['quotation_uploader_name'] ?? '—',
        'quotation_uploaded_role' => acctListRoleLabel($row['quotation_uploaded_role']),
        'quotation_review_status' => $row['quotation_review_status'] ?? 'Pending',
        'quotation_remarks'       => $row['quotation_remarks'],

        'include_3d'              => (bool) $row['include_3d'],
        'show_3d'                 => $show3d,
        'design_3d_stage'         => $row['design_3d_stage'] ?? 'Locked',
        'design_3d_path'          => $row['design_3d_path'],
        'design_3d_uploader_name' => $row['design_3d_uploader_name'] ?? '—',
        'design_3d_uploaded_role' => acctListRoleLabel($row['design_3d_uploaded_role']),
        'design_3d_review_status' => $row['design_3d_review_status'] ?? 'Pending',
        'design_3d_remarks'       => $row['design_3d_remarks'],

        'status'                => $row['status'],
        'reviewed_at'           => $row['reviewed_at'],
        'submitted_at'          => $row['submitted_at'],

        // Deposit slip / Notice to Proceed
        'deposit_status'              => $row['deposit_status'] ?? 'Hold',
        'deposit_slip_path'           => $row['deposit_slip_path'],
        'deposit_amount'              => $row['deposit_amount'],
        'deposit_payment_method_id'   => $row['deposit_payment_method_id'] !== null ? (int) $row['deposit_payment_method_id'] : null,
        'deposit_payment_method_name' => $row['deposit_payment_method_name'],
        'deposit_uploader_name'       => $row['deposit_uploader_name'] ?? '—',
        'deposit_uploaded_role'       => acctListRoleLabel($row['deposit_uploaded_role']),
        'deposit_uploaded_at'         => $row['deposit_uploaded_at'],

        // Walang notification row (n.id null) = matagal nang record bago
        // pa naidagdag ang notification hook — treat as read na lang.
        'is_read'               => $row['notification_id'] === null ? 1 : (int) $row['is_read'],
    ];
}

// Notifies every member of each given department role that a submission
// is now Notice to Proceed and ready for production. Same "whole
// department, not just head" pattern as check2dquotationajax.php's
// chk2dNotifyRoleRevision().
function acctListNotifyDepartmentsNTP(mysqli $conn, int $inquiryId, array $record, int $senderId): void
{
    $message = "Notice to Proceed: {$record['client_name']} (Control No. {$record['control_no']}) — deposit received, ready for production.";
    $link = "/crmcuttinglist?id={$inquiryId}"; // ⚠️ verify actual destination route per department
    $controlNo = $record['control_no'];

    $stmt = $conn->prepare("SELECT id FROM noblerole WHERE role = ?"); // ⚠️ verify column name `role`
    $insertStmt = $conn->prepare("
        INSERT INTO noblenotification
            (user_id, request_id, control_no, type, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 'crm_deposit_ntp', ?, 0, NOW(), ?, ?)
    ");

    foreach (ACCT_NTP_NOTIFY_ROLES as $role) {
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($members as $member) {
            $memberId = (int) $member['id'];
            $insertStmt->bind_param('iissis', $memberId, $inquiryId, $controlNo, $message, $senderId, $link);
            $insertStmt->execute();
        }
    }

    $stmt->close();
    $insertStmt->close();
}

if ($action === 'list') {

    $search = trim($_GET['q'] ?? '');
    $filter = trim($_GET['filter'] ?? 'unread');

    $sql = acctListBaseQuery() . " WHERE q.status = 'Approved' ";
    $types = 'i';
    $params = [$currentUserId];

    if ($filter === 'unread') {
        $sql .= " AND n.id IS NOT NULL AND n.is_read = 0 ";
    }

    if ($search !== '') {
        $sql .= " AND (i.control_no LIKE ? OR i.client_name LIKE ? OR i.contact_number LIKE ? OR i.branch LIKE ?) ";
        $like = '%' . $search . '%';
        $types .= 'ssss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY (q.reviewed_at IS NULL), q.reviewed_at DESC LIMIT 200 ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = acctListFormatRow($row);
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'rows'    => $rows,
        'count'   => count($rows),
        'server_time' => date('c'),
    ]);
    exit;
}

if ($action === 'detail') {

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid record.']);
        exit;
    }

    $sql = acctListBaseQuery() . " WHERE q.id = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $currentUserId, $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    if ($row['status'] !== 'Approved') {
        echo json_encode(['success' => false, 'message' => 'Not yet approved.']);
        exit;
    }

    // Mark this accounting user's notification for this inquiry as read.
    if ($row['notification_id'] !== null && (int) $row['is_read'] === 0) {
        $stmt = $conn->prepare("UPDATE noblenotification SET is_read = 1 WHERE id = ?");
        $stmt->bind_param('i', $row['notification_id']);
        $stmt->execute();
        $stmt->close();
        $row['is_read'] = 1;
    }

    echo json_encode([
        'success' => true,
        'record'  => acctListFormatRow($row),
    ]);
    exit;
}

// Active payment methods for the deposit form's dropdown.
if ($action === 'payment_methods') {
    $stmt = $conn->prepare("SELECT id, name FROM noblecrm_paymentmethod WHERE is_active = 1 ORDER BY name ASC");
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// Accounting logs the deposit slip + amount + payment method. Only valid
// once the submission's main status is fully 'Approved' and it is still
// sitting on 'Hold' (prevents re-submitting on top of an existing NTP).
if ($action === 'upload_deposit') {

    $id = intval($_POST['id'] ?? 0);
    $amount = trim($_POST['deposit_amount'] ?? '');
    $paymentMethodId = intval($_POST['deposit_payment_method_id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid record.']);
        exit;
    }
    if ($amount === '' || !is_numeric($amount) || (float) $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid deposit amount.']);
        exit;
    }
    if ($paymentMethodId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a payment method.']);
        exit;
    }
    if (!isset($_FILES['deposit_slip']) || $_FILES['deposit_slip']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Please attach the deposit slip.']);
        exit;
    }

    $file = $_FILES['deposit_slip'];
    if ($file['size'] > ACCT_DEPOSIT_MAX_BYTES) {
        echo json_encode(['success' => false, 'message' => 'File is too large (max 10MB).']);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ACCT_DEPOSIT_ALLOWED_EXT, true)) {
        echo json_encode(['success' => false, 'message' => 'Only PDF, JPG, or PNG files are allowed.']);
        exit;
    }

    // Confirm the record exists, is Approved, and still on Hold.
    $stmt = $conn->prepare("
        SELECT q.status, q.deposit_status, q.inquiry_id, i.control_no, i.client_name
        FROM noblecrm_2dquotation q
        JOIN noblecrminquiry i ON i.id = q.inquiry_id
        WHERE q.id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }
    if ($current['status'] !== 'Approved') {
        echo json_encode(['success' => false, 'message' => 'This submission is not fully approved yet.']);
        exit;
    }
    if ($current['deposit_status'] !== 'Hold') {
        echo json_encode(['success' => false, 'message' => 'A deposit has already been logged for this submission.']);
        exit;
    }

    // Confirm the payment method exists (active).
    $stmt = $conn->prepare("SELECT id FROM noblecrm_paymentmethod WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param('i', $paymentMethodId);
    $stmt->execute();
    $pmExists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$pmExists) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid payment method.']);
        exit;
    }

    if (!is_dir(ACCT_DEPOSIT_UPLOAD_DIR)) {
        mkdir(ACCT_DEPOSIT_UPLOAD_DIR, 0755, true);
    }

    $filename = 'deposit_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = ACCT_DEPOSIT_UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file.']);
        exit;
    }

    $depositSlipPath = ACCT_DEPOSIT_UPLOAD_URL_PREFIX . '/' . $filename;
    $uploadedRole = 'accounting';

    $stmt = $conn->prepare("
        UPDATE noblecrm_2dquotation
        SET deposit_status = 'Notice to Proceed',
            deposit_slip_path = ?,
            deposit_amount = ?,
            deposit_payment_method_id = ?,
            deposit_uploaded_by = ?,
            deposit_uploaded_role = ?,
            deposit_uploaded_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param(
        'sdiisi',
        $depositSlipPath,
        $amount,
        $paymentMethodId,
        $currentUserId,
        $uploadedRole,
        $id
    );
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        acctListNotifyDepartmentsNTP(
            $conn,
            (int) $current['inquiry_id'],
            [
                'control_no'  => $current['control_no'],
                'client_name' => $current['client_name'],
            ],
            $currentUserId
        );
    }

    echo json_encode([
        'success' => (bool) $ok,
        'message' => $ok ? 'Marked as Notice to Proceed. Cutting List, Operations, and Design have been notified.' : 'Failed to save deposit details.',
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);