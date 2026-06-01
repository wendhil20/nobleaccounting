<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

$month = $_GET['month'] ?? date('Y-m');

if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid month format']);
    exit;
}

$stmt = $conn->prepare("
    SELECT * FROM noblepettycashcustodian
    WHERE DATE_FORMAT(date, '%Y-%m') = ?
    ORDER BY entry_type = 'beginning' DESC, date ASC, id ASC
");
$stmt->bind_param('s', $month);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($rows);