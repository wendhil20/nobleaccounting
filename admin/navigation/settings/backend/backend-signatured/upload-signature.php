<?php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['signature']) || $_FILES['signature']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file received or upload error.']);
    exit;
}

$file    = $_FILES['signature'];
$label   = trim($_POST['label'] ?? 'Signature');
$user_id = intval($_SESSION['account_id'] ?? 0);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'image/png') {
    echo json_encode(['success' => false, 'message' => 'Only PNG files are allowed.']);
    exit;
}
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File exceeds the 10 MB size limit.']);
    exit;
}

$uploadDir = ROOT_PATH . '/uploads/signature/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename    = 'sig_' . $user_id . '_' . time() . '.png';
$destination = $uploadDir . $filename;
$dbPath      = 'uploads/signature/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the file.']);
    exit;
}

// Kunin kung ilan na ang signatures niya — kung wala pa, ito ang magiging active
$countRow = $conn->query("SELECT COUNT(*) as cnt FROM noblesignature WHERE user_id = $user_id");
$count    = $countRow->fetch_assoc()['cnt'];
$isActive = ($count == 0) ? 1 : 0;

$stmt = $conn->prepare("INSERT INTO noblesignature (user_id, label, path, is_active) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issi", $user_id, $label, $dbPath, $isActive);
$stmt->execute();
$new_id = $stmt->insert_id;
$stmt->close();

// Kung active, i-update ang noblerole
if ($isActive) {
   $conn->query("UPDATE noblerole SET active_signature_id = $new_id WHERE id = $user_id");

}

echo json_encode(['success' => true, 'message' => 'Signature saved successfully.', 'id' => $new_id, 'is_active' => $isActive]);
exit;