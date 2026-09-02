<?php
// crm2dquotationajax.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_SALES, ROLE_DESIGNER];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

$currentUserId = intval($_SESSION['account_id'] ?? 0);

// ⚠️ Adjust this if your session stores the role under a different key
$currentUserRole = $_SESSION['role'] ?? '';
$isSales = ($currentUserRole === ROLE_SALES);
$ownerColumn = $isSales ? 'sales_staff_id' : 'designer_id';
$roleLabel = $isSales ? 'sales' : 'designer';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$inquiryId = intval($_POST['inquiry_id'] ?? $_GET['inquiry_id'] ?? 0);
$slot = $_POST['slot'] ?? $_GET['slot'] ?? '';

function q2dRespond(bool $success, string $message = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($inquiryId <= 0) {
    q2dRespond(false, 'Missing or invalid inquiry reference.');
}

// NEW-3D: '3d' is now a valid slot for save_slot / unlock_slot.
if (in_array($action, ['save_slot', 'unlock_slot'], true) && !in_array($slot, ['2d', 'quotation', '3d'], true)) {
    q2dRespond(false, 'Invalid slot.');
}

// Verify the inquiry belongs to the current user (sales or designer, depending on role)
// NEW-STEP1: also pull design_progress / design_confirmed(_at/_by) so the
// state action and the two new actions below don't need a second query.
// NEW-CONTRACT: also pull contract_amount so it can be shown on the page
// and validated against on save_contract_amount.
$stmt = $conn->prepare("
    SELECT id, control_no, client_name, status, mode,
           design_progress, design_confirmed, design_confirmed_at, design_confirmed_by, clientstatus,
           contract_amount
    FROM noblecrminquiry
    WHERE id = ? AND {$ownerColumn} = ?
    LIMIT 1
");
$stmt->bind_param("ii", $inquiryId, $currentUserId);
$stmt->execute();
$inquiry = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$inquiry) {
    q2dRespond(false, 'Inquiry not found, or not assigned to you.');
}
if (!in_array($inquiry['status'], ['In Progress', 'Approved', 'For Revision'], true)) {
    q2dRespond(false, 'The site visit must be completed first.');
}

function q2dGetLatestEntry(mysqli $conn, int $inquiryId): ?array
{
    $stmt = $conn->prepare("
        SELECT * FROM noblecrm_2dquotation
        WHERE inquiry_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $inquiryId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function q2dGetHistory(mysqli $conn, int $inquiryId): array
{
    $stmt = $conn->prepare("
        SELECT * FROM noblecrm_2dquotation
        WHERE inquiry_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $inquiryId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function q2dAccountName(mysqli $conn, ?int $accountId): string
{
    if (!$accountId) return '—';
    $stmt = $conn->prepare("SELECT name FROM noblerole WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['name'] ?? '—';
}

function q2dRoleLabel(?string $role): string
{
    if ($role === 'sales') return 'Sales';
    if ($role === 'designer') return 'Designer';
    return '—';
}

function q2dStatusStyle(string $status): array
{
    switch ($status) {
        case 'Approved':
            return ['border-green-800 text-green-800', 'Approved'];
        case 'For Revision':
            return ['border-red-700 text-red-700', 'For Revision'];
        case 'Waiting for Approval':
            return ['border-amber-700 text-amber-700', 'Waiting for Approval'];
        default:
            return ['border-gray-400 text-gray-600', 'Draft'];
    }
}

function q2dUrl(?string $path): ?string
{
    return !empty($path) ? BASE_URL . '/' . $path : null;
}

// ═══════════════════════════════════════════════════════════
// NOTIFICATIONS
// ═══════════════════════════════════════════════════════════

// NEW-3D: added $is3dOnly so the sequential "3D submitted after 2D &
// Quotation were already approved" case gets its own, clearer message
// instead of re-announcing "New 2D and Quotation submission".
function q2dNotifySuperAdmins(mysqli $conn, int $inquiryId, array $inquiry, int $senderId, bool $is3dOnly = false): void
{
    $stmt = $conn->prepare("SELECT id FROM noblerole WHERE role = ?");
    $stmt->bind_param("s", $role);
    $role = ROLE_SUPERADMIN; // ⚠️ verify constant name
    $stmt->execute();
    $superAdmins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($superAdmins)) {
        return;
    }

    $message = $is3dOnly
        ? "New 3D file submission from {$inquiry['client_name']} (Control No. {$inquiry['control_no']})"
        : "New 2D and Quotation submission from {$inquiry['client_name']} (Control No. {$inquiry['control_no']})";
    $link = "/crm-main?id={$inquiryId}"; // ⚠️ verify expected param name
    $controlNo = $inquiry['control_no'];

    $stmt = $conn->prepare("
        INSERT INTO noblenotification
            (user_id, request_id, control_no, type, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 'crm_inquiry', ?, 0, NOW(), ?, ?)
    ");
    $stmt->bind_param("iissis", $adminId, $inquiryId, $controlNo, $message, $senderId, $link);

    foreach ($superAdmins as $admin) {
        $adminId = (int) $admin['id'];
        $stmt->execute();
    }
    $stmt->close();
}

function q2dGetOrCreateDraft(mysqli $conn, int $inquiryId): array
{
    $latest = q2dGetLatestEntry($conn, $inquiryId);

    if ($latest && in_array($latest['status'], ['Draft', 'Waiting for Approval'], true)) {
        return $latest;
    }

    $carry2dDone   = 0; $carry2dPath   = null; $carry2dRole   = null; $carry2dBy   = null; $carry2dReview   = 'Pending';
    $carryQuotDone = 0; $carryQuotPath = null; $carryQuotRole = null; $carryQuotBy = null; $carryQuotReview = 'Pending';
    // NEW-3D: 3D carries the same way 2D/Quotation already do.
    $carry3dDone   = 0; $carry3dPath   = null; $carry3dRole   = null; $carry3dBy   = null; $carry3dReview   = 'Pending';
    $carryInclude3d = 0;
    $carry3dStage   = 'Locked';

    if ($latest && $latest['status'] === 'For Revision') {
        // Keep bundling 3D in the new cycle if the prior cycle bundled it
        // (this also covers the case where check2dquotationajax.php opened
        // this row specifically because 3D needed the 2D file redone).
        $carryInclude3d = (int) ($latest['include_3d'] ?? 0);

        if (($latest['design_2d_review_status'] ?? null) === 'Approved') {
            $carry2dDone   = 1;
            $carry2dPath   = $latest['design_2d_path'];
            $carry2dRole   = $latest['design_2d_uploaded_role'];
            $carry2dBy     = $latest['design_2d_uploaded_by'];
            // I-carry rin ang review_status mismo — kung hindi, babalik itong
            // 'Pending' sa bagong row kahit Approved na talaga ito, kaya
            // lalabas itong 'Pending' ulit sa approval queue ng super admin.
            $carry2dReview = 'Approved';
        }
        if (($latest['quotation_review_status'] ?? null) === 'Approved') {
            $carryQuotDone   = 1;
            $carryQuotPath   = $latest['quotation_path'];
            $carryQuotRole   = $latest['quotation_uploaded_role'];
            $carryQuotBy     = $latest['quotation_uploaded_by'];
            $carryQuotReview = 'Approved';
        }
        // NEW-3D: only carried as "done/Approved" if it was actually
        // Approved in the prior cycle. If 'For Revision' is what triggered
        // this new cycle in the first place, it stays un-done here on
        // purpose — it needs to be re-uploaded.
        if (($latest['design_3d_review_status'] ?? null) === 'Approved') {
            $carry3dDone   = 1;
            $carry3dPath   = $latest['design_3d_path'];
            $carry3dRole   = $latest['design_3d_uploaded_role'];
            $carry3dBy     = $latest['design_3d_uploaded_by'];
            $carry3dReview = 'Approved';
        }
        if ($carryInclude3d) {
            $carry3dStage = 'Draft';
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO noblecrm_2dquotation
            (inquiry_id, status, created_at, include_3d,
             design_2d_done, design_2d_path, design_2d_uploaded_role, design_2d_uploaded_by, design_2d_uploaded_at, design_2d_review_status,
             quotation_done, quotation_path, quotation_uploaded_role, quotation_uploaded_by, quotation_uploaded_at, quotation_review_status,
             design_3d_done, design_3d_path, design_3d_uploaded_role, design_3d_uploaded_by, design_3d_uploaded_at, design_3d_review_status, design_3d_stage)
        VALUES
            (?, 'Draft', NOW(), ?,
             ?, ?, ?, ?, IF(? = 1, NOW(), NULL), ?,
             ?, ?, ?, ?, IF(? = 1, NOW(), NULL), ?,
             ?, ?, ?, ?, IF(? = 1, NOW(), NULL), ?, ?)
    ");
    // Built with str_repeat instead of hand-typed so the type string can't
    // drift out of sync with the parameter list above.
    $types = 'ii' . str_repeat('issiis', 3) . 's';
    $stmt->bind_param(
        $types,
        $inquiryId, $carryInclude3d,
        $carry2dDone, $carry2dPath, $carry2dRole, $carry2dBy, $carry2dDone, $carry2dReview,
        $carryQuotDone, $carryQuotPath, $carryQuotRole, $carryQuotBy, $carryQuotDone, $carryQuotReview,
        $carry3dDone, $carry3dPath, $carry3dRole, $carry3dBy, $carry3dDone, $carry3dReview,
        $carry3dStage
    );
    $stmt->execute();
    $stmt->close();

    return q2dGetLatestEntry($conn, $inquiryId);
}

// Ang row na sinundan ng kasalukuyang draft — ibig sabihin, ang row na
// nag-trigger ng bagong draft (hal. yung 'For Revision' na row). Ginagamit
// para malaman kung may dating file pa rin sa lumang cycle na kailangang
// tunay na mapalitan (deleted) sa halip na iwanan bilang duplicate.
function q2dGetPreviousEntry(mysqli $conn, int $inquiryId, int $excludeId): ?array
{
    $stmt = $conn->prepare("
        SELECT * FROM noblecrm_2dquotation
        WHERE inquiry_id = ? AND id != ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("ii", $inquiryId, $excludeId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// Tunay na pagpapalit ng file para sa isang slot: binubura ang lumang
// physical file (kahit pa galing 'yon sa nakaraang submission cycle), at
// inaalis din ang reference dito sa ANUMANG ibang row na may parehong path
// (hal. ang na-supersede nang Prior Submission entry) — para hindi na
// lumabas doon ang patay na 'View' link; magiging '—' na lang ito.
function q2dReplaceSlotFile(mysqli $conn, int $inquiryId, int $draftId, string $pathField, ?string $oldPath): void
{
    if (empty($oldPath)) {
        return;
    }

    @unlink(ROOT_PATH . '/' . $oldPath);

    $stmt = $conn->prepare("
        UPDATE noblecrm_2dquotation
        SET {$pathField} = NULL
        WHERE inquiry_id = ? AND id != ? AND {$pathField} = ?
    ");
    $stmt->bind_param("iis", $inquiryId, $draftId, $oldPath);
    $stmt->execute();
    $stmt->close();
}

function q2dSaveUploadedPdf(array $file, string $prefix, int $inquiryId, int $maxBytes, string $uploadDir): array
{
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['path' => null, 'error' => 'File is too large for the server to accept.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Upload failed. Please try again.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($ext !== 'pdf' || $mime !== 'application/pdf') {
        return ['path' => null, 'error' => 'File must be a valid PDF.'];
    }
    if ($file['size'] > $maxBytes) {
        return ['path' => null, 'error' => 'File exceeds the 15MB limit.'];
    }

    $safeName = $prefix . '_' . $inquiryId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
    $destPath = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['path' => null, 'error' => 'Something went wrong while saving the file.'];
    }

    return ['path' => 'uploads/crm-2dquotation/' . $safeName, 'error' => null];
}

// NEW-3D: 3D accepts either a PDF (stored as-is, same as 2D/Quotation) or
// an image (jpg/png/webp), which gets normalized to .webp on save so
// every 3D asset in storage is a consistent format.
function q2dSaveUploaded3dFile(array $file, int $inquiryId, int $maxBytes, string $uploadDir): array
{
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['path' => null, 'error' => 'File is too large for the server to accept.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Upload failed. Please try again.'];
    }
    if ($file['size'] > $maxBytes) {
        return ['path' => null, 'error' => 'File exceeds the 15MB limit.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $safeBase = '3d_' . $inquiryId . '_' . time() . '_' . bin2hex(random_bytes(4));

    if ($mime === 'application/pdf') {
        $destPath = $uploadDir . $safeBase . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['path' => null, 'error' => 'Something went wrong while saving the file.'];
        }
        return ['path' => 'uploads/crm-2dquotation/' . $safeBase . '.pdf', 'error' => null];
    }

    $allowedImageMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedImageMimes, true)) {
        return ['path' => null, 'error' => 'The 3D file must be a PDF or an image (JPG, PNG, WEBP).'];
    }

    $srcImage = null;
    if ($mime === 'image/jpeg') $srcImage = @imagecreatefromjpeg($file['tmp_name']);
    if ($mime === 'image/png')  $srcImage = @imagecreatefrompng($file['tmp_name']);
    if ($mime === 'image/webp') $srcImage = @imagecreatefromwebp($file['tmp_name']);

    if (!$srcImage) {
        return ['path' => null, 'error' => 'Could not read the uploaded image.'];
    }

    // Preserve transparency for PNG/WEBP sources.
    imagepalettetotruecolor($srcImage);
    imagealphablending($srcImage, true);
    imagesavealpha($srcImage, true);

    $destPath = $uploadDir . $safeBase . '.webp';
    if (!imagewebp($srcImage, $destPath, 90)) {
        imagedestroy($srcImage);
        return ['path' => null, 'error' => 'Something went wrong while converting the image.'];
    }
    imagedestroy($srcImage);

    return ['path' => 'uploads/crm-2dquotation/' . $safeBase . '.webp', 'error' => null];
}

// ═══════════════════════════════════════════════════════════
// state — full JSON snapshot of the page, used for the initial
// render AND every poll / post-action refresh. Nothing about the
// page's content is rendered server-side (in PHP markup) anymore —
// this is the single source of truth the frontend renders from.
// ═══════════════════════════════════════════════════════════
if ($action === 'state') {

    $qHistory = q2dGetHistory($conn, $inquiryId);
    $latest = $qHistory[0] ?? null;

    // SELF-HEAL: rows approved before the fix that resolves design_3d_stage
    // on every review (not just bundled ones) can be stuck showing
    // design_3d_stage = 'Locked' forever even though 2D & Quotation are
    // already Approved and the sequential 3D upload should be open. Catch
    // and correct that here so old data heals itself on next read, instead
    // of needing a manual SQL fix per row.
    if (
        $latest
        && $latest['status'] === 'Approved'
        && (int) ($latest['include_3d'] ?? 0) === 0
        && ($latest['design_3d_stage'] ?? 'Locked') === 'Locked'
    ) {
        $healStage = 'Draft';
        $stmt = $conn->prepare("UPDATE noblecrm_2dquotation SET design_3d_stage = ? WHERE id = ?");
        $stmt->bind_param("si", $healStage, $latest['id']);
        $stmt->execute();
        $stmt->close();

        $latest['design_3d_stage'] = 'Draft';
        $qHistory[0] = $latest;
    }

    $activeDraftRow = null;
    if ($latest && in_array($latest['status'], ['Draft', 'Waiting for Approval'], true)) {
        $activeDraftRow = $latest;
    }

    $isLocked = $activeDraftRow && $activeDraftRow['status'] === 'Waiting for Approval';
    $pastEntries = $activeDraftRow ? array_slice($qHistory, 1) : $qHistory;

    // Kung ang pinakabagong row ay Approved na (parehong file na-approve),
    // wala nang dapat gawin — ipakita ito bilang locked/completed summary
    // (View PDF + Approved badge), HINDI 'No Active Submission' upload
    // prompts. Dati, walang branch para dito kaya nabagsak ito sa fresh-
    // upload fallback kahit approved na pala talaga ang buong submission.
    $completedRow = null;
    if (!$activeDraftRow && $latest && $latest['status'] === 'Approved') {
        $completedRow = $latest;
    }

    $revisionEntryRow = null;
    $design2dNeedsRevision = false;
    $quotationNeedsRevision = false;

    if (!$activeDraftRow && !$completedRow && !empty($qHistory) && $qHistory[0]['status'] === 'For Revision') {
        $revisionEntryRow = $qHistory[0];
        $design2dNeedsRevision = ($revisionEntryRow['design_2d_review_status'] ?? 'For Revision') === 'For Revision';
        $quotationNeedsRevision = ($revisionEntryRow['quotation_review_status'] ?? 'For Revision') === 'For Revision';
    }

    $activeDraftJson = null;
    if ($activeDraftRow) {
        [$statusClass, $statusLabel] = q2dStatusStyle($activeDraftRow['status']);
        $activeDraftJson = [
            'id'           => (int) $activeDraftRow['id'],
            'status'       => $activeDraftRow['status'],
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'is_locked'    => $isLocked,
            'both_done'    => (bool) $activeDraftRow['design_2d_done'] && (bool) $activeDraftRow['quotation_done'],
            'design_2d'    => [
                'done'                => (bool) $activeDraftRow['design_2d_done'],
                'path'                => $activeDraftRow['design_2d_path'],
                'url'                 => q2dUrl($activeDraftRow['design_2d_path']),
                'filename'            => !empty($activeDraftRow['design_2d_path']) ? basename($activeDraftRow['design_2d_path']) : null,
                'uploaded_by_name'    => q2dAccountName($conn, $activeDraftRow['design_2d_uploaded_by'] ? (int) $activeDraftRow['design_2d_uploaded_by'] : null),
                'uploaded_role_label' => q2dRoleLabel($activeDraftRow['design_2d_uploaded_role']),
            ],
            'quotation'    => [
                'done'                => (bool) $activeDraftRow['quotation_done'],
                'path'                => $activeDraftRow['quotation_path'],
                'url'                 => q2dUrl($activeDraftRow['quotation_path']),
                'filename'            => !empty($activeDraftRow['quotation_path']) ? basename($activeDraftRow['quotation_path']) : null,
                'uploaded_by_name'    => q2dAccountName($conn, $activeDraftRow['quotation_uploaded_by'] ? (int) $activeDraftRow['quotation_uploaded_by'] : null),
                'uploaded_role_label' => q2dRoleLabel($activeDraftRow['quotation_uploaded_role']),
            ],
        ];
    }

    $completedEntryJson = null;
    if ($completedRow) {
        $completedEntryJson = [
            'reviewed_at' => $completedRow['reviewed_at'] ? date('F d, Y g:i A', strtotime($completedRow['reviewed_at'])) : null,
            'design_2d'   => [
                'url'                 => q2dUrl($completedRow['design_2d_path']),
                'uploaded_by_name'    => q2dAccountName($conn, $completedRow['design_2d_uploaded_by'] ? (int) $completedRow['design_2d_uploaded_by'] : null),
                'uploaded_role_label' => q2dRoleLabel($completedRow['design_2d_uploaded_role']),
            ],
            'quotation'   => [
                'url'                 => q2dUrl($completedRow['quotation_path']),
                'uploaded_by_name'    => q2dAccountName($conn, $completedRow['quotation_uploaded_by'] ? (int) $completedRow['quotation_uploaded_by'] : null),
                'uploaded_role_label' => q2dRoleLabel($completedRow['quotation_uploaded_role']),
            ],
        ];
    }

    $revisionEntryJson = null;
    if ($revisionEntryRow) {
        $revisionEntryJson = [
            'design_2d_needs_revision' => $design2dNeedsRevision,
            'quotation_needs_revision' => $quotationNeedsRevision,
            'design_2d' => [
                'path'    => $revisionEntryRow['design_2d_path'],
                'url'     => q2dUrl($revisionEntryRow['design_2d_path']),
                'remarks' => $revisionEntryRow['design_2d_remarks'],
            ],
            'quotation' => [
                'path'    => $revisionEntryRow['quotation_path'],
                'url'     => q2dUrl($revisionEntryRow['quotation_path']),
                'remarks' => $revisionEntryRow['quotation_remarks'],
            ],
        ];
    }

    $pastEntriesJson = array_map(function (array $entry): array {
        [$statusClass, $statusLabel] = q2dStatusStyle($entry['status']);

        $d2dReviewStatus = (!empty($entry['design_2d_review_status']) && $entry['design_2d_review_status'] !== 'Pending')
            ? $entry['design_2d_review_status'] : null;
        $qtReviewStatus = (!empty($entry['quotation_review_status']) && $entry['quotation_review_status'] !== 'Pending')
            ? $entry['quotation_review_status'] : null;
        $d3dReviewStatus = (!empty($entry['design_3d_review_status']) && $entry['design_3d_review_status'] !== 'Pending')
            ? $entry['design_3d_review_status'] : null;

        return [
            'design_2d' => [
                'path'          => $entry['design_2d_path'],
                'url'           => q2dUrl($entry['design_2d_path']),
                'review_status' => $d2dReviewStatus,
                'review_class'  => $d2dReviewStatus ? q2dStatusStyle($d2dReviewStatus)[0] : null,
            ],
            'quotation' => [
                'path'          => $entry['quotation_path'],
                'url'           => q2dUrl($entry['quotation_path']),
                'review_status' => $qtReviewStatus,
                'review_class'  => $qtReviewStatus ? q2dStatusStyle($qtReviewStatus)[0] : null,
            ],
            // NEW-3D: only meaningful when this past cycle actually bundled 3D.
            'design_3d' => [
                'included'      => (bool) ($entry['include_3d'] ?? 0),
                'path'          => $entry['design_3d_path'],
                'url'           => q2dUrl($entry['design_3d_path']),
                'review_status' => $d3dReviewStatus,
                'review_class'  => $d3dReviewStatus ? q2dStatusStyle($d3dReviewStatus)[0] : null,
            ],
            'submitted_at'       => $entry['submitted_at'] ? date('M d, Y g:i A', strtotime($entry['submitted_at'])) : null,
            'status'             => $entry['status'],
            'status_label'       => $statusLabel,
            'status_class'       => $statusClass,
            'design_2d_remarks'  => $entry['design_2d_remarks'] ?? null,
            'quotation_remarks'  => $entry['quotation_remarks'] ?? null,
            'remarks'            => $entry['remarks'] ?? null,
        ];
    }, $pastEntries);

    // NEW-3D: 3D fields live on whichever row is "latest", regardless of
    // which branch above fired (active draft / completed / revision) —
    // exposed once at the top level so the frontend doesn't need to dig
    // into each branch separately.
    $include3d = (int) ($latest['include_3d'] ?? 0);
    $design3dJson = $latest ? [
        'include_3d'          => (bool) $include3d,
        'stage'                => $latest['design_3d_stage'] ?? 'Locked',
        'done'                 => (bool) ($latest['design_3d_done'] ?? false),
        'path'                 => $latest['design_3d_path'] ?? null,
        'url'                  => q2dUrl($latest['design_3d_path'] ?? null),
        'filename'             => !empty($latest['design_3d_path']) ? basename($latest['design_3d_path']) : null,
        'uploaded_by_name'     => q2dAccountName($conn, !empty($latest['design_3d_uploaded_by']) ? (int) $latest['design_3d_uploaded_by'] : null),
        'uploaded_role_label'  => q2dRoleLabel($latest['design_3d_uploaded_role'] ?? null),
        'review_status'        => $latest['design_3d_review_status'] ?? 'Pending',
        'remarks'              => $latest['design_3d_remarks'] ?? null,
        // The toggle is only editable while there's an unlocked Draft cycle.
        'toggle_editable'      => (bool) ($activeDraftRow && $activeDraftRow['status'] === 'Draft'),
    ] : null;

    // NEW-STEP1: 0%/50%/100% progress + staff-marked customer confirmation.
    // Habang hindi pa naka-confirm, ito lang ang ipapakita sa frontend —
    // gate bago ma-unlock yung Step 2 (2D & Quotation) content.
 $isReadyForQuotation = ($inquiry['mode'] ?? 'site_visit') === 'ready_for_quotation';

$step1Json = [
    'progress'          => $isReadyForQuotation ? '100' : ($inquiry['design_progress'] ?? '0'),
    'confirmed'         => $isReadyForQuotation ? true : (bool) ($inquiry['design_confirmed'] ?? 0),
    'confirmed_at'      => (!$isReadyForQuotation && !empty($inquiry['design_confirmed_at']))
        ? date('F d, Y g:i A', strtotime($inquiry['design_confirmed_at'])) : null,
    'confirmed_by_name' => $isReadyForQuotation ? null : q2dAccountName($conn, $inquiry['design_confirmed_by'] ? (int) $inquiry['design_confirmed_by'] : null),
    'client_status'     => $inquiry['clientstatus'] ?? null,
    'auto_confirmed'    => $isReadyForQuotation, // para hindi ipakita ng frontend yung "confirmed by X at Y" badge line
];
    // NEW-CONTRACT: quotation_done is exposed at the top level (not just
    // buried inside active_draft/completed_entry/revision_entry) because
    // the Contract Amount box needs to know "is the Quotation file marked
    // Done" regardless of which of those three branches is currently
    // rendering — it can be true even while status is still 'Draft'
    // (quotation done, 2D not yet done) or 'For Revision' (quotation was
    // approved in a prior cycle and carried over as done).
    $quotationDone = (bool) ($latest['quotation_done'] ?? false);

    q2dRespond(true, '', [
        'inquiry' => [
            'control_no'      => $inquiry['control_no'],
            'client_name'     => $inquiry['client_name'],
            'contract_amount' => $inquiry['contract_amount'],
        ],
        'is_sales'        => $isSales,
        'quotation_done'  => $quotationDone,
        'step1'           => $step1Json, // NEW-STEP1
        'active_draft'    => $activeDraftJson,
        'completed_entry' => $completedEntryJson,
        'revision_entry'  => $revisionEntryJson,
        'past_entries'    => $pastEntriesJson,
        'design_3d'       => $design3dJson,
        'server_time'    => date('c'),
    ]);
}

// ═══════════════════════════════════════════════════════════
// save_progress — manual update ng 0/50/100% (NEW-STEP1).
// Sales o Designer lang, at hindi na pwede kapag na-confirm na.
// ═══════════════════════════════════════════════════════════
if ($action === 'save_progress') {

    if ((int) ($inquiry['design_confirmed'] ?? 0) === 1) {
        q2dRespond(false, 'This has already been confirmed by the customer.');
    }

    $progress = $_POST['progress'] ?? '';
    if (!in_array($progress, ['0', '50', '100'], true)) {
        q2dRespond(false, 'Invalid progress value.');
    }

    $stmt = $conn->prepare("UPDATE noblecrminquiry SET design_progress = ? WHERE id = ?");
    $stmt->bind_param("si", $progress, $inquiryId);
    $stmt->execute();
    $stmt->close();

    q2dRespond(true, 'Progress updated.');
}

// ═══════════════════════════════════════════════════════════
// confirm_customer — i-mamark na naaprubahan na ng customer
// (NEW-STEP1). Staff lang ang pumipindot nito, walang customer
// login. Kailangan muna 100% bago pwede i-confirm.
// ═══════════════════════════════════════════════════════════
if ($action === 'confirm_customer') {

    if ((int) ($inquiry['design_confirmed'] ?? 0) === 1) {
        q2dRespond(false, 'This has already been confirmed.');
    }
    if (($inquiry['design_progress'] ?? '0') !== '100') {
        q2dRespond(false, 'Progress must be at 100% before confirming.');
    }

    $clientStatus = 'Client Review & Approval';

    $stmt = $conn->prepare("
        UPDATE noblecrminquiry
        SET design_confirmed = 1, design_confirmed_at = NOW(), design_confirmed_by = ?, clientstatus = ?
        WHERE id = ?
    ");
    $stmt->bind_param("isi", $currentUserId, $clientStatus, $inquiryId);
    $stmt->execute();
    $stmt->close();

    q2dRespond(true, 'Confirmed by customer.');
}

// ═══════════════════════════════════════════════════════════
// save_toggle — turn "submit 3D together" on/off for the current
// editable Draft. NEW-3D.
// ═══════════════════════════════════════════════════════════
if ($action === 'save_toggle') {

    $include3d = (intval($_POST['include_3d'] ?? 0) === 1) ? 1 : 0;

    $draft = q2dGetOrCreateDraft($conn, $inquiryId);
    if ($draft['status'] !== 'Draft') {
        q2dRespond(false, 'This submission is locked and can no longer be edited.');
    }

    // Kung OFF, i-lock lang ang stage — hindi burahin ang naka-Draft nang
    // 3D file kung sakaling na-toggle ON tapos OFF ulit bago mag-submit.
    $stage = $include3d ? 'Draft' : 'Locked';

    $stmt = $conn->prepare("
        UPDATE noblecrm_2dquotation
        SET include_3d = ?, design_3d_stage = ?
        WHERE id = ?
    ");
    $stmt->bind_param("isi", $include3d, $stage, $draft['id']);
    $stmt->execute();
    $stmt->close();

    q2dRespond(true, 'Updated.');
}

// ═══════════════════════════════════════════════════════════
// save_slot — upload (kung may bagong file) + mark that slot as Done
// ═══════════════════════════════════════════════════════════
if ($action === 'save_slot') {

    // NEW-3D: the 3D slot's editability is governed by design_3d_stage
    // (which can be 'Draft' whether the overall row status is 'Draft'
    // (bundled) or 'Approved' (sequential/after-approval upload)) — not
    // by the overall row status the way 2D/Quotation are. Handle it as
    // its own branch and exit early.
    if ($slot === '3d') {
        $latest = q2dGetLatestEntry($conn, $inquiryId);

        if (!$latest || ($latest['design_3d_stage'] ?? 'Locked') !== 'Draft') {
            q2dRespond(false, '3D upload is not open right now.');
        }
        if ((int) $latest['design_3d_done'] === 1) {
            q2dRespond(false, 'This file is already marked done. Click Edit first.');
        }

        $existingPath = $latest['design_3d_path'] ?? null;
        $newPath = null;

        if (!empty($_FILES['design_3d_file']) && $_FILES['design_3d_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $maxBytes = 15 * 1024 * 1024; // 15MB
            $uploadDir = ROOT_PATH . '/uploads/crm-2dquotation/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $result = q2dSaveUploaded3dFile($_FILES['design_3d_file'], $inquiryId, $maxBytes, $uploadDir);
            if (!$result['path']) {
                q2dRespond(false, $result['error']);
            }

            q2dReplaceSlotFile($conn, $inquiryId, (int) $latest['id'], 'design_3d_path', $existingPath);
            $newPath = $result['path'];

        } elseif (empty($latest['design_3d_path'])) {
            q2dRespond(false, 'Please attach a PDF or image before marking this as done.');
        }

        if ($newPath) {
            $stmt = $conn->prepare("
                UPDATE noblecrm_2dquotation
                SET design_3d_path = ?, design_3d_done = 1, design_3d_uploaded_role = ?, design_3d_uploaded_by = ?, design_3d_uploaded_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssii", $newPath, $roleLabel, $currentUserId, $latest['id']);
        } else {
            $stmt = $conn->prepare("
                UPDATE noblecrm_2dquotation
                SET design_3d_done = 1, design_3d_uploaded_role = ?, design_3d_uploaded_by = ?, design_3d_uploaded_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sii", $roleLabel, $currentUserId, $latest['id']);
        }
        $stmt->execute();
        $stmt->close();

        q2dRespond(true, 'Saved.');
    }

    $draft = q2dGetOrCreateDraft($conn, $inquiryId);

    if ($draft['status'] !== 'Draft') {
        q2dRespond(false, 'This submission is locked and can no longer be edited.');
    }

    $doneField = $slot === '2d' ? 'design_2d_done' : 'quotation_done';
    $pathField = $slot === '2d' ? 'design_2d_path' : 'quotation_path';
    $roleField = $slot === '2d' ? 'design_2d_uploaded_role' : 'quotation_uploaded_role';
    $byField   = $slot === '2d' ? 'design_2d_uploaded_by' : 'quotation_uploaded_by';
    $atField   = $slot === '2d' ? 'design_2d_uploaded_at' : 'quotation_uploaded_at';

    if ((int) $draft[$doneField] === 1) {
        q2dRespond(false, 'This file is already marked done. Click Edit first.');
    }

    $fileKey = $slot === '2d' ? 'design_2d_pdf' : 'quotation_pdf';
    $newPath = null;

    // Kunin ang "kasalukuyang" file ng slot na ito bago pa man mapalitan —
  
    $existingPath = $draft[$pathField] ?? null;
    if (empty($existingPath)) {
        $prevEntry = q2dGetPreviousEntry($conn, $inquiryId, (int) $draft['id']);
        if ($prevEntry && !empty($prevEntry[$pathField])) {
            $existingPath = $prevEntry[$pathField];
        }
    }

    if (!empty($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {

        $maxBytes = 15 * 1024 * 1024; // 15MB
        $uploadDir = ROOT_PATH . '/uploads/crm-2dquotation/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $result = q2dSaveUploadedPdf($_FILES[$fileKey], $slot === '2d' ? 'design2d' : 'quotation', $inquiryId, $maxBytes, $uploadDir);

        if (!$result['path']) {
            q2dRespond(false, $result['error']);
        }

        // I-replace nang tunay ang PDF — burahin ang dating file (kahit pa
        // galing sa nakaraang submission cycle) at alisin ang reference
        // dito sa Prior Submissions, para hindi na mag-iwan ng dead link.
        q2dReplaceSlotFile($conn, $inquiryId, (int) $draft['id'], $pathField, $existingPath);

        $newPath = $result['path'];

    } elseif (empty($draft[$pathField])) {
        q2dRespond(false, 'Please attach a PDF before marking this as done.');
    }

    if ($newPath) {
        $stmt = $conn->prepare("
            UPDATE noblecrm_2dquotation
            SET {$pathField} = ?, {$doneField} = 1, {$roleField} = ?, {$byField} = ?, {$atField} = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ssii", $newPath, $roleLabel, $currentUserId, $draft['id']);
    } else {
        // Re-confirming an existing file na hindi pinalitan
        $stmt = $conn->prepare("
            UPDATE noblecrm_2dquotation
            SET {$doneField} = 1, {$roleField} = ?, {$byField} = ?, {$atField} = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sii", $roleLabel, $currentUserId, $draft['id']);
    }
    $stmt->execute();
    $stmt->close();

    q2dRespond(true, 'Saved.');
}

// ═══════════════════════════════════════════════════════════
// unlock_slot — i-undo ang Done, para maka-edit ulit
// ═══════════════════════════════════════════════════════════
if ($action === 'unlock_slot') {

    // NEW-3D
    if ($slot === '3d') {
        $latest = q2dGetLatestEntry($conn, $inquiryId);
        if (!$latest || ($latest['design_3d_stage'] ?? 'Locked') !== 'Draft') {
            q2dRespond(false, 'Nothing to edit right now.');
        }
        $stmt = $conn->prepare("UPDATE noblecrm_2dquotation SET design_3d_done = 0 WHERE id = ?");
        $stmt->bind_param("i", $latest['id']);
        $stmt->execute();
        $stmt->close();
        q2dRespond(true, 'Unlocked.');
    }

    $draft = q2dGetLatestEntry($conn, $inquiryId);

    if (!$draft || $draft['status'] !== 'Draft') {
        q2dRespond(false, 'Nothing to edit right now.');
    }

    $doneField = $slot === '2d' ? 'design_2d_done' : 'quotation_done';

    $stmt = $conn->prepare("UPDATE noblecrm_2dquotation SET {$doneField} = 0 WHERE id = ?");
    $stmt->bind_param("i", $draft['id']);
    $stmt->execute();
    $stmt->close();

    q2dRespond(true, 'Unlocked.');
}

// ═══════════════════════════════════════════════════════════
// save_contract_amount — NEW-CONTRACT. Sales-only. Pwede lang
// i-set/i-update ang Contract Amount kapag naka-mark na "Done" na
// ang Quotation file sa pinaka-huling 2D & Quotation entry (hindi
// pa kailangan Approved — pagka-Done na ng quotation, unlocked na).
// Nakatira ang value sa noblecrminquiry.contract_amount (parehong
// column na dati'y pinupunan sa initial CRM inquiry form — ngayon
// dito na lang ito pinupunan, hindi na sa una).
// ═══════════════════════════════════════════════════════════
if ($action === 'save_contract_amount') {

    if (!$isSales) {
        q2dRespond(false, 'Only Sales can set the Contract Amount.');
    }

    $latest = q2dGetLatestEntry($conn, $inquiryId);
    if (!$latest || !$latest['quotation_done']) {
        q2dRespond(false, 'The Quotation file must be marked done first.');
    }

    $rawAmount = trim($_POST['contract_amount'] ?? '');
    $cleanAmount = preg_replace('/[^0-9.]/', '', $rawAmount);

    if ($cleanAmount === '' || !is_numeric($cleanAmount) || (float) $cleanAmount <= 0) {
        q2dRespond(false, 'Please enter a valid contract amount.');
    }

    $stmt = $conn->prepare("
        UPDATE noblecrminquiry SET contract_amount = ?
        WHERE id = ? AND sales_staff_id = ?
    ");
    $stmt->bind_param("dii", $cleanAmount, $inquiryId, $currentUserId);
    $stmt->execute();
    $stmt->close();

    q2dRespond(true, 'Contract amount saved.');
}

// ═══════════════════════════════════════════════════════════
// submit_final — i-lock ang buong submission para sa review,
// at i-notify ang super admin. Kung include_3d = 1, kasama na
// rin ang 3D sa parehong submission cycle. NEW-3D: extended.
// ═══════════════════════════════════════════════════════════
if ($action === 'submit_final') {

    $draft = q2dGetLatestEntry($conn, $inquiryId);

    if (!$draft || $draft['status'] !== 'Draft') {
        q2dRespond(false, 'Nothing to submit right now.');
    }
    if (!$draft['design_2d_done'] || !$draft['quotation_done']) {
        q2dRespond(false, 'Both the 2D File and Quotation File must be marked done before submitting.');
    }

    $include3d = (int) ($draft['include_3d'] ?? 0);
    if ($include3d && !$draft['design_3d_done']) {
        q2dRespond(false, 'The 3D File must be marked done before submitting, or turn off "Submit 3D together" to submit 2D and Quotation only.');
    }

    $newStatus = 'Waiting for Approval';
    $new3dStage = $include3d ? 'Waiting for Approval' : 'Locked';

    $stmt = $conn->prepare("
        UPDATE noblecrm_2dquotation
        SET status = ?, submitted_at = NOW(), design_3d_stage = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $newStatus, $new3dStage, $draft['id']);
    $stmt->execute();
    $stmt->close();

    // 🔔 Notify super admin that a submission is ready for review.
    q2dNotifySuperAdmins($conn, $inquiryId, $inquiry, $currentUserId);

    q2dRespond(true, 'Submitted for approval.');
}

// ═══════════════════════════════════════════════════════════
// submit_3d — sequential flow only: 2D & Quotation are already
// Approved (include_3d was 0 for that cycle), and the 3D slot has
// since unlocked (design_3d_stage = 'Draft'). This submits JUST
// the 3D file for its own review. NEW-3D.
// ═══════════════════════════════════════════════════════════
if ($action === 'submit_3d') {

    $latest = q2dGetLatestEntry($conn, $inquiryId);

    if (!$latest || $latest['status'] !== 'Approved') {
        q2dRespond(false, 'The 2D File and Quotation must be approved first.');
    }
    if ((int) ($latest['include_3d'] ?? 0) === 1) {
        q2dRespond(false, 'This submission already includes 3D in its main review.');
    }
    if (($latest['design_3d_stage'] ?? 'Locked') !== 'Draft') {
        q2dRespond(false, '3D upload is not open right now.');
    }
    if (!$latest['design_3d_done']) {
        q2dRespond(false, 'The 3D File must be marked done before submitting.');
    }

    $newStage = 'Waiting for Approval';
    $stmt = $conn->prepare("UPDATE noblecrm_2dquotation SET design_3d_stage = ? WHERE id = ?");
    $stmt->bind_param("si", $newStage, $latest['id']);
    $stmt->execute();
    $stmt->close();

    q2dNotifySuperAdmins($conn, $inquiryId, $inquiry, $currentUserId, true);

    q2dRespond(true, 'Submitted for approval.');
}

q2dRespond(false, 'Unknown action.');