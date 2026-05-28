<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$sig_id  = intval($_POST['id'] ?? 0);
$user_id = intval($_SESSION['account_id'] ?? 0);

if (!$sig_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing ID.']);
    exit;
}

if (!isset($_FILES['signature']) || $_FILES['signature']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file received or upload error.']);
    exit;
}

// Verify na sa kanya talaga yung signature
$row = $conn->query("SELECT path FROM noblesignature WHERE id = $sig_id AND user_id = $user_id LIMIT 1");
if (!$row || !$row->num_rows) {
    echo json_encode(['success' => false, 'message' => 'Signature not found.']);
    exit;
}
$oldPath = $row->fetch_assoc()['path'];

$file = $_FILES['signature'];

// Frontend nag-convert na sa WebP, pero double-check pa rin
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = ['image/webp', 'image/png'];
if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['success' => false, 'message' => 'Only WebP or PNG files are allowed.']);
    exit;
}
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File exceeds the 10 MB size limit.']);
    exit;
}

$uploadDir = ROOT_PATH . '/uploads/signature/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$ext         = ($mime === 'image/webp') ? 'webp' : 'png';
$filename    = 'sig_' . $user_id . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;
$dbPath      = 'uploads/signature/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the file.']);
    exit;
}

// I-delete ang lumang file
$fullOldPath = ROOT_PATH . '/' . $oldPath;
if (file_exists($fullOldPath)) unlink($fullOldPath);

// I-update ang database
$stmt = $conn->prepare("UPDATE noblesignature SET path = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $dbPath, $sig_id, $user_id);
$stmt->execute();
$stmt->close();

// Kung ito ang active signature, i-update din ang noblerole
$conn->query("UPDATE noblerole SET signature = '$dbPath' WHERE id = $user_id AND active_signature_id = $sig_id");

echo json_encode(['success' => true, 'message' => 'Signature updated successfully.', 'path' => $dbPath]);
exit;