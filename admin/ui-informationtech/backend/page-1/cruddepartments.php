<?php
//branchdepartments.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_IT];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("SELECT id, name FROM noblebranch ORDER BY name ASC");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? '';

    if ($action === 'add') {
        $name = trim($body['name'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Branch name is required.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO noblebranch (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'id' => $conn->insert_id, 'message' => $ok ? '' : $conn->error]);
        $stmt->close();
        exit;
    }

    if ($action === 'edit') {
        $id = intval($body['id'] ?? 0);
        $name = trim($body['name'] ?? '');
        if ($id === 0 || $name === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE noblebranch SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? '' : $conn->error]);
        $stmt->close();
        exit;
    }

    if ($action === 'delete') {
        $id = intval($body['id'] ?? 0);
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid id.']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM noblebranch WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? '' : $conn->error]);
        $stmt->close();
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);