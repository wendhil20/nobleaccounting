<?php
// index-accounting-paymentmethodsajax.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

$currentUserId = intval($_SESSION['account_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// Active-only list, used by the deposit form's dropdown and by this
// settings page (soft-deleted / inactive rows are simply hidden here too).
if ($action === 'list') {
    $stmt = $conn->prepare("SELECT id, name FROM noblecrm_paymentmethod WHERE is_active = 1 ORDER BY name ASC");
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Please enter a name.']);
        exit;
    }
    if (mb_strlen($name) > 100) {
        echo json_encode(['success' => false, 'message' => 'Name is too long.']);
        exit;
    }

    // Avoid obvious duplicates among active methods.
    $stmt = $conn->prepare("SELECT id FROM noblecrm_paymentmethod WHERE name = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($exists) {
        echo json_encode(['success' => false, 'message' => 'That payment method already exists.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO noblecrm_paymentmethod (name, created_by) VALUES (?, ?)");
    $stmt->bind_param('si', $name, $currentUserId);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Added.' : 'Failed to add.']);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item.']);
        exit;
    }

    // Soft delete — deposits already logged with this method keep their
    // payment_method_id and still display the name via the join; the
    // method just disappears from future dropdowns.
    $stmt = $conn->prepare("UPDATE noblecrm_paymentmethod SET is_active = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Deleted.' : 'Failed to delete.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);