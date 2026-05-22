<?php
// fetch-budget-graph-data.php
// Returns JSON: monthly budget request counts grouped by user

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

// Get year filter (default: current year)
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$sql = "
    SELECT 
        requestor_name,
        MONTH(date_requested) AS month,
        COUNT(*) AS request_count
    FROM noblebudgetrequest
    WHERE YEAR(date_requested) = ?
    GROUP BY requestor_name, MONTH(date_requested)
    ORDER BY requestor_name, month
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();

$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$data = [];

while ($row = $result->fetch_assoc()) {
    $name = $row['requestor_name'];
    $monthIndex = (int)$row['month'] - 1;

    if (!isset($data[$name])) {
        $data[$name] = array_fill(0, 12, 0);
    }
    $data[$name][$monthIndex] = (int)$row['request_count'];
}

echo json_encode([
    'months' => $months,
    'users'  => $data,
    'year'   => $year,
]);

$stmt->close();
?>