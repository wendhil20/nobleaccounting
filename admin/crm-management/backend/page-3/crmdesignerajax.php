<?php
// crmdesignerajax.php
// JSON endpoint na ginagamit ng site.php (Designer List) para sa realtime polling,
// pag-fetch ng detail, at "Proceed" action.

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_DESIGNER];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

$currentDesignerId = intval($_SESSION['account_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

function crmDesignerBaseQuery()
{
    return "
        SELECT
            i.id, i.control_no, i.client_name, i.address, i.project_type,
            i.project_scope, i.measuring_space, i.measurement_datetime,
            i.contact_number, i.contract_amount, i.branch, i.created_at,
            i.status,
            s.name AS sales_name
        FROM noblecrminquiry i
        LEFT JOIN noblerole s ON s.id = i.sales_staff_id
    ";
}

function crmDesignerFormatRow($row)
{
    return [
        'id'                   => (int) $row['id'],
        'control_no'           => $row['control_no'],
        'client_name'          => $row['client_name'],
        'address'              => $row['address'],
        'project_type'         => $row['project_type'],
        'project_scope'        => $row['project_scope'],
        'measuring_space'      => $row['measuring_space'],
        'measurement_datetime' => $row['measurement_datetime'],
        'contact_number'       => $row['contact_number'],
        'contract_amount'      => $row['contract_amount'],
        'branch'               => $row['branch'],
        'status'               => $row['status'] ?: 'Pending',
        'sales_name'           => $row['sales_name'] ?? '—',
        'created_at'           => $row['created_at'],
    ];
}

if ($action === 'list') {

    $search = trim($_GET['q'] ?? '');

    $sql = crmDesignerBaseQuery() . " WHERE i.designer_id = ? ";
    $types = "i";
    $params = [$currentDesignerId];

    if ($search !== '') {
        $sql .= " AND (i.control_no LIKE ? OR i.client_name LIKE ? OR i.contact_number LIKE ?) ";
        $like = '%' . $search . '%';
        $types .= "sss";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY i.created_at DESC LIMIT 200 ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = crmDesignerFormatRow($row);
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

    $sql = crmDesignerBaseQuery() . " WHERE i.id = ? AND i.designer_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $currentDesignerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'record'  => crmDesignerFormatRow($row),
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);