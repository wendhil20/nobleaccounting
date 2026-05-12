<?php
// index-projectmonitor-saveproject.php — Route: POST /saveproject
session_name('nobleadmin');
session_start();
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$user_id = intval($_SESSION['account_id'] ?? 0);

$id = intval($body['id'] ?? 0);
$project_name = trim($body['project_name'] ?? '');
$job_order = trim($body['job_order'] ?? '');
$project_scope = trim($body['project_scope'] ?? '');
$purchase_order = trim($body['purchase_order'] ?? '');
$client_name = trim($body['client_name'] ?? '');
$notice_to_proceed = trim($body['notice_to_proceed'] ?? '');
$contract_amount = $body['contract_amount'] !== '' ? floatval($body['contract_amount']) : null;
$billing_order_1 = trim($body['billing_order_1'] ?? '');
$sales_person = trim($body['sales_person'] ?? '');
$billing_order_2 = trim($body['billing_order_2'] ?? '');
$address = trim($body['address'] ?? '');
$status = trim($body['status'] ?? '');

if (!$project_name) {
    echo json_encode(['success' => false, 'error' => 'Project name required']);
    exit;
}

if ($id) {
    // UPDATE
    $stmt = $conn->prepare("UPDATE nobleprojectmonitor SET
        project_name=?, job_order=?, project_scope=?, purchase_order=?,
        client_name=?, notice_to_proceed=?, contract_amount=?,
        billing_order_1=?, sales_person=?, billing_order_2=?, address=?, status=?
        WHERE id=?");
    $stmt->bind_param(
        "ssssssdsssssi",
        $project_name,
        $job_order,
        $project_scope,
        $purchase_order,
        $client_name,
        $notice_to_proceed,
        $contract_amount,
        $billing_order_1,
        $sales_person,
        $billing_order_2,
        $address,
        $status,
        $id
    );
} else {
    // INSERT
    $stmt = $conn->prepare("INSERT INTO nobleprojectmonitor
        (project_name, job_order, project_scope, purchase_order, client_name,
         notice_to_proceed, contract_amount, billing_order_1, sales_person,
         billing_order_2, address, status, created_by)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    // I-generate ang reference number
    function generateProjectRef($conn)
    {
        $year = date('Y');
        do {
            $rand = mt_rand(10000000, 99999999);
            $ref = 'NHPROJECT-' . $year . $rand;
            $stmt = $conn->prepare("SELECT id FROM nobleprojectmonitor WHERE reference_no = ? LIMIT 1");
            $stmt->bind_param("s", $ref);
            $stmt->execute();
            $stmt->store_result();
        } while ($stmt->num_rows > 0);
        return $ref;
    }

    // Sa INSERT section, dagdag lang:
    $reference_no = generateProjectRef($conn);

    $stmt = $conn->prepare("INSERT INTO nobleprojectmonitor
    (reference_no, project_name, job_order, project_scope, purchase_order, client_name,
     notice_to_proceed, contract_amount, billing_order_1, sales_person,
     billing_order_2, address, status, created_by)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssssdssssssi",
        $reference_no,
        $project_name,
        $job_order,
        $project_scope,
        $purchase_order,
        $client_name,
        $notice_to_proceed,
        $contract_amount,
        $billing_order_1,
        $sales_person,
        $billing_order_2,
        $address,
        $status,
        $user_id
    );
}

$result = $stmt->execute();
echo json_encode(['success' => $result, 'error' => $conn->error, 'insert_id' => $conn->insert_id]);