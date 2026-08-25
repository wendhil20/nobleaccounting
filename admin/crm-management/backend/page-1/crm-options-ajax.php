<?php
// crm-options-ajax.php
include ROOT_PATH . '/network/connect.php';

header('Content-Type: application/json');

$allowedTypes = [
    'measuring_space' => 'noblecrm_measuring_space',
    'project_scope'   => 'noblecrm_project_scope',
];

$type   = $_REQUEST['type'] ?? '';
$action = $_REQUEST['action'] ?? '';

if (!isset($allowedTypes[$type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid type.']);
    exit;
}
$table = $allowedTypes[$type];

switch ($action) {

    case 'list':
        $result = $conn->query("SELECT id, label FROM {$table} ORDER BY label ASC");
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        echo json_encode(['success' => true, 'items' => $items]);
        break;

    case 'add':
        $label = trim($_POST['label'] ?? '');
        if ($label === '') {
            echo json_encode(['success' => false, 'message' => 'Label is required.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO {$table} (label) VALUES (?)");
        $stmt->bind_param("s", $label);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'label' => $label]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add.']);
        }
        $stmt->close();
        break;

    case 'edit':
        $id    = intval($_POST['id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        if ($id <= 0 || $label === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE {$table} SET label = ? WHERE id = ?");
        $stmt->bind_param("si", $label, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update.']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid id.']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete.']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}