<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$month = $_GET['month'] ?? date('Y-m');
[$year, $mon] = explode('-', $month);

$stmt = $conn->prepare("
    SELECT * FROM noblepettycashcustodiantwo
    WHERE YEAR(date) = ? AND MONTH(date) = ?
    ORDER BY date ASC, id ASC
");
$stmt->bind_param("ii", $year, $mon);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);