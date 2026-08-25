<?php
// check2dquotationajax.php


include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_SUPERADMIN];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

header('Content-Type: application/json');

$currentUserId = intval($_SESSION['account_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// Statuses that are actually visible to the approver. 'Draft' means the
// designer/sales hasn't submitted yet, so it's excluded entirely.
const CHK2D_VISIBLE_STATUSES = ['Waiting for Approval', 'Approved', 'For Revision'];
const CHK2D_REVIEW_DECISIONS = ['Approved', 'For Revision'];
// NEW-3D
const CHK2D_3D_STAGES_VISIBLE = ['Waiting for Approval', 'Approved', 'For Revision'];

function chk2dBaseQuery(): string
{
    return "
        SELECT
            q.id, q.inquiry_id, q.design_2d_path, q.design_2d_uploaded_by, q.design_2d_uploaded_role,
            q.design_2d_done, q.design_2d_uploaded_at,
            q.quotation_path, q.quotation_uploaded_by, q.quotation_uploaded_role,
            q.quotation_done, q.quotation_uploaded_at,
            q.design_2d_review_status, q.design_2d_remarks,
            q.quotation_review_status, q.quotation_remarks,
            q.include_3d, q.design_3d_stage, q.design_3d_path, q.design_3d_uploaded_by, q.design_3d_uploaded_role,
            q.design_3d_done, q.design_3d_uploaded_at, q.design_3d_review_status, q.design_3d_remarks,
            q.status, q.remarks, q.submitted_at, q.created_at, q.reviewed_at,
            i.control_no, i.client_name, i.contact_number, i.project_type, i.branch,
            d2u.name AS design_2d_uploader_name,
            qtu.name AS quotation_uploader_name,
            d3u.name AS design_3d_uploader_name
        FROM noblecrm_2dquotation q
        -- Isa lang ang dapat makita kada inquiry sa approval queue — yung
        -- pinaka-huling submission cycle. Yung mga naunang cycle (na naging
        -- For Revision na noon) ay makikita na lang sa 'Prior Submissions'
        -- history sa 2d-and-quotation.php ng designer/sales, hindi na dapat
        -- lumabas dito bilang hiwalay/duplicate na row.
        JOIN (
            SELECT inquiry_id, MAX(id) AS max_id
            FROM noblecrm_2dquotation
            GROUP BY inquiry_id
        ) latest ON latest.inquiry_id = q.inquiry_id AND latest.max_id = q.id
        JOIN noblecrminquiry i ON i.id = q.inquiry_id
        LEFT JOIN noblerole d2u ON d2u.id = q.design_2d_uploaded_by
        LEFT JOIN noblerole qtu ON qtu.id = q.quotation_uploaded_by
        LEFT JOIN noblerole d3u ON d3u.id = q.design_3d_uploaded_by
    ";
}

function chk2dRoleLabel(?string $role): string
{
    if ($role === 'sales') return 'Sales';
    if ($role === 'designer') return 'Designer';
    return '—';
}

// NEW-3D: a row can need attention for two independent reasons now — the
// main 2D+Quotation(+bundled 3D) cycle, OR a sequential 3D-only submission
// sitting on top of an already-Approved main cycle. This tells the
// frontend which one it's looking at so it can render the right modal.
function chk2dReviewTarget(array $row): string
{
    if ($row['status'] === 'Waiting for Approval') {
        return (int) $row['include_3d'] === 1 ? 'main_with_3d' : 'main';
    }
    if ($row['status'] === 'Approved' && ($row['design_3d_stage'] ?? 'Locked') === 'Waiting for Approval') {
        return '3d_only';
    }
    return 'none';
}

function chk2dFormatRow(array $row): array
{
    return [
        'id'                        => (int) $row['id'],
        'inquiry_id'                => (int) $row['inquiry_id'],
        'control_no'                => $row['control_no'],
        'client_name'               => $row['client_name'],
        'contact_number'            => $row['contact_number'],
        'project_type'              => $row['project_type'],
        'branch'                    => $row['branch'],
        'design_2d_path'            => $row['design_2d_path'],
        'design_2d_uploader_name'   => $row['design_2d_uploader_name'] ?? '—',
        'design_2d_uploaded_role'   => chk2dRoleLabel($row['design_2d_uploaded_role']),
        'design_2d_uploaded_at'     => $row['design_2d_uploaded_at'],
        'design_2d_review_status'   => $row['design_2d_review_status'] ?? 'Pending',
        'design_2d_remarks'         => $row['design_2d_remarks'],
        'quotation_path'            => $row['quotation_path'],
        'quotation_uploader_name'   => $row['quotation_uploader_name'] ?? '—',
        'quotation_uploaded_role'   => chk2dRoleLabel($row['quotation_uploaded_role']),
        'quotation_uploaded_at'     => $row['quotation_uploaded_at'],
        'quotation_review_status'   => $row['quotation_review_status'] ?? 'Pending',
        'quotation_remarks'         => $row['quotation_remarks'],
        // NEW-3D
        'include_3d'                => (bool) $row['include_3d'],
        'design_3d_stage'           => $row['design_3d_stage'] ?? 'Locked',
        'design_3d_path'            => $row['design_3d_path'],
        'design_3d_uploader_name'   => $row['design_3d_uploader_name'] ?? '—',
        'design_3d_uploaded_role'   => chk2dRoleLabel($row['design_3d_uploaded_role']),
        'design_3d_uploaded_at'     => $row['design_3d_uploaded_at'],
        'design_3d_review_status'   => $row['design_3d_review_status'] ?? 'Pending',
        'design_3d_remarks'         => $row['design_3d_remarks'],
        'review_target'             => chk2dReviewTarget($row),
        'status'                    => $row['status'],
        'remarks'                   => $row['remarks'],
        'submitted_at'              => $row['submitted_at'],
        'reviewed_at'               => $row['reviewed_at'],
        'created_at'                => $row['created_at'],
    ];
}

// ═══════════════════════════════════════════════════════════
// NOTIFICATIONS
// ═══════════════════════════════════════════════════════════

function chk2dNotifyAccountingHead(mysqli $conn, int $inquiryId, array $record, int $senderId): void
{
    $stmt = $conn->prepare("SELECT id FROM noblerole WHERE role = ? AND position = ?");
    $role = ROLE_ACCOUNTING;      // ⚠️ verify column name `role`
    $position = POSITION_HEAD;    // ⚠️ verify column name `position`
    $stmt->bind_param("ss", $role, $position);
    $stmt->execute();
    $accountingHeads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($accountingHeads)) {
        return;
    }

    $message = "2D and Quotation approved for {$record['client_name']} (Control No. {$record['control_no']})";
    $link = "/crmaccounting?id={$inquiryId}"; // ⚠️ verify intended destination for accounting
    $controlNo = $record['control_no'];

    $stmt = $conn->prepare("
        INSERT INTO noblenotification
            (user_id, request_id, control_no, type, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 'crm_2dquotation', ?, 0, NOW(), ?, ?)
    ");
    $stmt->bind_param("iissis", $headId, $inquiryId, $controlNo, $message, $senderId, $link);

    foreach ($accountingHeads as $head) {
        $headId = (int) $head['id'];
        $stmt->execute();
    }
    $stmt->close();
}

// Notify everyone under a given department role (e.g. ROLE_DESIGNER,
// ROLE_SALES) that a file was sent back for revision. Role-based — hindi
// yung specific uploader lang ang tinitingnan, kundi lahat ng miyembro ng
// department na iyon. Parehong pattern ng chk2dNotifyAccountingHead sa
// itaas (walang position filter dito — buong department, hindi head lang).
function chk2dNotifyRoleRevision(
    mysqli $conn,
    string $role,
    int $inquiryId,
    array $record,
    int $senderId,
    string $fileLabel,
    ?string $remarks
): void {
    $stmt = $conn->prepare("SELECT id FROM noblerole WHERE role = ?"); // ⚠️ verify column name `role`
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($members)) {
        return;
    }

    $message = "{$fileLabel} for {$record['client_name']} (Control No. {$record['control_no']}) needs revision";
    if ($remarks) {
        $message .= ": {$remarks}";
    }
    $link = "/crm2dquotation?id={$inquiryId}"; // ⚠️ verify actual route ng designer/sales 2d-and-quotation.php
    $controlNo = $record['control_no'];

    $stmt = $conn->prepare("
        INSERT INTO noblenotification
            (user_id, request_id, control_no, type, message, is_read, created_at, sender_id, link)
        VALUES (?, ?, ?, 'crm_2dquotation', ?, 0, NOW(), ?, ?)
    ");
    $stmt->bind_param("iissis", $memberId, $inquiryId, $controlNo, $message, $senderId, $link);

    foreach ($members as $member) {
        $memberId = (int) $member['id'];
        $stmt->execute();
    }
    $stmt->close();
}

if ($action === 'list') {

    $search = trim($_GET['q'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');

    $placeholders = implode(',', array_fill(0, count(CHK2D_VISIBLE_STATUSES), '?'));
    // NEW-3D: a row also belongs in the queue if it's Approved overall but
    // still has a 3D file waiting on its own (sequential flow) — that
    // wouldn't otherwise match q.status IN (...) once status = 'Approved'.
    $sql = chk2dBaseQuery() . "
        WHERE (
            q.status IN ({$placeholders})
            OR (q.status = 'Approved' AND q.design_3d_stage = 'Waiting for Approval')
        )
    ";
    $types = str_repeat('s', count(CHK2D_VISIBLE_STATUSES));
    $params = CHK2D_VISIBLE_STATUSES;

    if ($statusFilter !== '' && in_array($statusFilter, CHK2D_VISIBLE_STATUSES, true)) {
        $sql .= " AND q.status = ? ";
        $types .= "s";
        $params[] = $statusFilter;
    }

    if ($search !== '') {
        $sql .= " AND (i.control_no LIKE ? OR i.client_name LIKE ? OR i.contact_number LIKE ?) ";
        $like = '%' . $search . '%';
        $types .= "sss";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY (q.submitted_at IS NULL), q.submitted_at DESC, q.created_at DESC LIMIT 200 ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = chk2dFormatRow($row);
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

    $sql = "
        SELECT
            q.id, q.inquiry_id, q.design_2d_path, q.design_2d_uploaded_by, q.design_2d_uploaded_role,
            q.design_2d_done, q.design_2d_uploaded_at,
            q.quotation_path, q.quotation_uploaded_by, q.quotation_uploaded_role,
            q.quotation_done, q.quotation_uploaded_at,
            q.design_2d_review_status, q.design_2d_remarks,
            q.quotation_review_status, q.quotation_remarks,
            q.include_3d, q.design_3d_stage, q.design_3d_path, q.design_3d_uploaded_by, q.design_3d_uploaded_role,
            q.design_3d_done, q.design_3d_uploaded_at, q.design_3d_review_status, q.design_3d_remarks,
            q.status, q.remarks, q.submitted_at, q.created_at, q.reviewed_at,
            i.control_no, i.client_name, i.contact_number, i.project_type, i.branch,
            d2u.name AS design_2d_uploader_name,
            qtu.name AS quotation_uploader_name,
            d3u.name AS design_3d_uploader_name
        FROM noblecrm_2dquotation q
        JOIN noblecrminquiry i ON i.id = q.inquiry_id
        LEFT JOIN noblerole d2u ON d2u.id = q.design_2d_uploaded_by
        LEFT JOIN noblerole qtu ON qtu.id = q.quotation_uploaded_by
        LEFT JOIN noblerole d3u ON d3u.id = q.design_3d_uploaded_by
        WHERE q.id = ? LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'record'  => chk2dFormatRow($row),
    ]);
    exit;
}

// ── review — the main 2D + Quotation (+ bundled 3D) decision ──

if ($action === 'review') {

    $id = intval($_POST['id'] ?? 0);
    $design2dDecision  = trim($_POST['design_2d_decision'] ?? '');
    $design2dRemarks   = trim($_POST['design_2d_remarks'] ?? '');
    $quotationDecision = trim($_POST['quotation_decision'] ?? '');
    $quotationRemarks  = trim($_POST['quotation_remarks'] ?? '');
    // NEW-3D: only required/used when the record's include_3d = 1.
    $design3dDecision  = trim($_POST['design_3d_decision'] ?? '');
    $design3dRemarks   = trim($_POST['design_3d_remarks'] ?? '');

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid record.']);
        exit;
    }

    if (!in_array($design2dDecision, CHK2D_REVIEW_DECISIONS, true)
        || !in_array($quotationDecision, CHK2D_REVIEW_DECISIONS, true)) {
        echo json_encode(['success' => false, 'message' => 'Please decide on both the 2D file and the Quotation file.']);
        exit;
    }

    if ($design2dDecision === 'For Revision' && $design2dRemarks === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide remarks for the 2D file revision.']);
        exit;
    }

    if ($quotationDecision === 'For Revision' && $quotationRemarks === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide remarks for the Quotation file revision.']);
        exit;
    }

    // Kailangan ang buong record (hindi lang status) para may client_name /
    // control_no na tunay na maipapasa sa notifications kapag Approved
    // (Accounting Head) o For Revision (Designer / Sales department).
    $stmt = $conn->prepare("
        SELECT q.status, q.inquiry_id, q.include_3d, i.control_no, i.client_name
        FROM noblecrm_2dquotation q
        JOIN noblecrminquiry i ON i.id = q.inquiry_id
        WHERE q.id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    if ($current['status'] !== 'Waiting for Approval') {
        echo json_encode(['success' => false, 'message' => 'Only submissions waiting for approval can be reviewed.']);
        exit;
    }

    $include3d = (int) $current['include_3d'];

    // NEW-3D: if this submission bundled a 3D file, its decision is
    // required too before the review can be saved.
    if ($include3d) {
        if (!in_array($design3dDecision, CHK2D_REVIEW_DECISIONS, true)) {
            echo json_encode(['success' => false, 'message' => 'Please decide on the 3D file as well.']);
            exit;
        }
        if ($design3dDecision === 'For Revision' && $design3dRemarks === '') {
            echo json_encode(['success' => false, 'message' => 'Please provide remarks for the 3D file revision.']);
            exit;
        }

        // 🔁 Cascade rule: kapag "For Revision" ang 3D, ang 2D ay
        // kasabay na rin dapat i-revise, kahit "Approved" ang napili ng
        // reviewer dito — dahil karaniwan, ang 3D ay direktang hango sa
        // 2D file, kaya kung mali ang 3D, malamang kailangan ding ayusin
        // ang 2D. I-override lang kung hindi pa "For Revision" ang 2D.
        if ($design3dDecision === 'For Revision' && $design2dDecision !== 'For Revision') {
            $design2dDecision = 'For Revision';
            $cascadeNote = 'Automatically sent back together with the 3D file revision.';
            $design2dRemarks = $design2dRemarks !== ''
                ? $design2dRemarks . "\n\n" . $cascadeNote
                : $cascadeNote;
        }
    }

    $overallStatus = ($design2dDecision === 'Approved' && $quotationDecision === 'Approved'
        && (!$include3d || $design3dDecision === 'Approved'))
        ? 'Approved'
        : 'For Revision';

    $design2dRemarksToSave  = $design2dDecision === 'For Revision' ? $design2dRemarks : null;
    $quotationRemarksToSave = $quotationDecision === 'For Revision' ? $quotationRemarks : null;
    $design3dRemarksToSave  = ($include3d && $design3dDecision === 'For Revision') ? $design3dRemarks : null;

    // Legacy `remarks` column: panatilihin itong updated (combined summary)
    // para sa mga lumang bahagi ng system na umaasa pa dito.
    $combinedRemarks = trim(implode("\n\n", array_filter([
        $design2dRemarksToSave ? "2D: {$design2dRemarksToSave}" : '',
        $quotationRemarksToSave ? "Quotation: {$quotationRemarksToSave}" : '',
        $design3dRemarksToSave ? "3D: {$design3dRemarksToSave}" : '',
    ]))) ?: null;

    // NEW-3D / FIX: design_3d_stage must be resolved unconditionally, not
    // just when include_3d = 1 — this is the switch that actually unlocks
    // the sequential "3D comes later" flow. Without it, a non-bundled
    // submission's 3D slot stays stuck on 'Locked' forever after approval,
    // which is why the 3D section wasn't showing up on the designer page.
    if ($include3d) {
        // Bundled cycle: 3D's stage mirrors whatever the batch outcome was.
        $new3dStage = $overallStatus; // 'Approved' or 'For Revision'
    } elseif ($overallStatus === 'Approved') {
        // Sequential flow: 2D & Quotation just got approved without 3D —
        // unlock the 3D upload slot now.
        $new3dStage = 'Draft';
    } else {
        // 2D/Quotation sent back for revision — 3D isn't relevant yet.
        $new3dStage = 'Locked';
    }

    $stmt = $conn->prepare("
        UPDATE noblecrm_2dquotation
        SET status = ?,
            design_2d_review_status = ?, design_2d_remarks = ?,
            quotation_review_status = ?, quotation_remarks = ?,
            design_3d_review_status = IF(? = 1, ?, design_3d_review_status),
            design_3d_remarks = IF(? = 1, ?, design_3d_remarks),
            design_3d_stage = ?,
            design_3d_reviewed_at = IF(? = 1, NOW(), design_3d_reviewed_at),
            remarks = ?,
            reviewed_at = NOW()
        WHERE id = ?
    ");
    // Types, one per placeholder above in order:
    // status(s), 2d_decision(s), 2d_remarks(s), quot_decision(s), quot_remarks(s),
    // [IF] include3d(i), 3d_decision(s), [IF] include3d(i), 3d_remarks(s),
    // 3d_stage(s), [IF] include3d(i), remarks(s), id(i)  =>  13 placeholders total
    $types = 's' . 's' . 's' . 's' . 's' . 'i' . 's' . 'i' . 's' . 's' . 'i' . 's' . 'i';
    $stmt->bind_param(
        $types,
        $overallStatus,
        $design2dDecision, $design2dRemarksToSave,
        $quotationDecision, $quotationRemarksToSave,
        $include3d, $design3dDecision,
        $include3d, $design3dRemarksToSave,
        $new3dStage,
        $include3d,
        $combinedRemarks,
        $id
    );
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $recordForNotif = [
            'control_no'  => $current['control_no'],
            'client_name' => $current['client_name'],
        ];

        if ($overallStatus === 'Approved') {
            // 🔔 Fully approved (2D + Quotation, + 3D if bundled) — notify the Accounting Head.
            chk2dNotifyAccountingHead(
                $conn,
                (int) $current['inquiry_id'],
                $recordForNotif,
                $currentUserId
            );
        } else {
            // 🔔 Sent back for revision — notify per department role
            // (buong Design department pag na-revision yung 2D — kasama na
            // dito ang cascade mula sa 3D — buong Sales department pag
            // na-revision yung Quotation).
            if ($design2dDecision === 'For Revision') {
                chk2dNotifyRoleRevision(
                    $conn,
                    ROLE_DESIGNER,
                    (int) $current['inquiry_id'],
                    $recordForNotif,
                    $currentUserId,
                    '2D File',
                    $design2dRemarksToSave
                );
            }

            if ($quotationDecision === 'For Revision') {
                chk2dNotifyRoleRevision(
                    $conn,
                    ROLE_SALES,
                    (int) $current['inquiry_id'],
                    $recordForNotif,
                    $currentUserId,
                    'Quotation File',
                    $quotationRemarksToSave
                );
            }
        }
    }

    $message = $overallStatus === 'Approved' ? 'Submission approved.' : 'Sent back for revision.';
    echo json_encode(['success' => (bool) $ok, 'message' => $ok ? $message : 'Failed to save review.']);
    exit;
}

// ── review_3d — sequential flow only: 2D & Quotation were already
// Approved in a prior review; this decides on the 3D file alone.
// NEW-3D.
// ═══════════════════════════════════════════════════════════
if ($action === 'review_3d') {

    $id = intval($_POST['id'] ?? 0);
    $design3dDecision = trim($_POST['design_3d_decision'] ?? '');
    $design3dRemarks  = trim($_POST['design_3d_remarks'] ?? '');

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid record.']);
        exit;
    }
    if (!in_array($design3dDecision, CHK2D_REVIEW_DECISIONS, true)) {
        echo json_encode(['success' => false, 'message' => 'Please decide on the 3D file.']);
        exit;
    }
    if ($design3dDecision === 'For Revision' && $design3dRemarks === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide remarks for the 3D file revision.']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT q.*, i.control_no, i.client_name
        FROM noblecrm_2dquotation q
        JOIN noblecrminquiry i ON i.id = q.inquiry_id
        WHERE q.id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }
    if ($current['status'] !== 'Approved' || ($current['design_3d_stage'] ?? 'Locked') !== 'Waiting for Approval') {
        echo json_encode(['success' => false, 'message' => 'This 3D file is not currently waiting for approval.']);
        exit;
    }

    $recordForNotif = [
        'control_no'  => $current['control_no'],
        'client_name' => $current['client_name'],
    ];

    if ($design3dDecision === 'Approved') {
        // Simple case: mark the 3D file itself Approved. The overall row
        // stays 'Approved' — the whole submission (2D, Quotation, 3D) is
        // now fully settled.
        $stmt = $conn->prepare("
            UPDATE noblecrm_2dquotation
            SET design_3d_stage = 'Approved', design_3d_review_status = 'Approved',
                design_3d_remarks = NULL, design_3d_reviewed_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        $message = 'Submission approved.';

    } else {
        // 🔁 Cascade rule applies here too: 3D revision means the 2D file
        // needs rework. The 2D+Quotation row is already 'Approved' and
        // locked, so we open a NEW cycle: 2D goes back to 'For Revision'
        // (re-upload required), Quotation carries forward as Approved
        // (untouched, no re-upload needed), and 3D carries its existing
        // file forward with the reviewer's remarks attached, needing
        // re-upload as well. The new cycle bundles 3D (include_3d = 1)
        // since they now need to move together.
        $cascadeNote = 'Revision requested on the 3D file — please also review/update the 2D file.';

        $newStatus = 'For Revision';
        $include3d = 1;
        $newStage3d = 'Draft';

        $stmt = $conn->prepare("
            INSERT INTO noblecrm_2dquotation
                (inquiry_id, status, created_at, include_3d,
                 design_2d_done, design_2d_path, design_2d_uploaded_role, design_2d_uploaded_by, design_2d_review_status, design_2d_remarks,
                 quotation_done, quotation_path, quotation_uploaded_role, quotation_uploaded_by, quotation_uploaded_at, quotation_review_status,
                 design_3d_done, design_3d_path, design_3d_uploaded_role, design_3d_uploaded_by, design_3d_review_status, design_3d_remarks, design_3d_stage)
            VALUES
                (?, ?, NOW(), ?,
                 0, ?, ?, ?, 'For Revision', ?,
                 1, ?, ?, ?, NOW(), 'Approved',
                 0, ?, ?, ?, 'For Revision', ?, ?)
        ");
        // Built explicitly (not hand-typed as one string) so the type
        // list can't silently drift out of sync with the params below:
        // i, s, i,  s, s, i, s,  s, s, i,  s, s, i, s, s
        $types = 'i' . 's' . 'i' . 's' . 's' . 'i' . 's' . 's' . 's' . 'i' . 's' . 's' . 'i' . 's' . 's';
        $design2dUploadedBy = (int) $current['design_2d_uploaded_by'];
        $quotationUploadedBy = (int) $current['quotation_uploaded_by'];
        $design3dUploadedBy = (int) $current['design_3d_uploaded_by'];
        $stmt->bind_param(
            $types,
            $current['inquiry_id'], $newStatus, $include3d,
            $current['design_2d_path'], $current['design_2d_uploaded_role'], $design2dUploadedBy, $cascadeNote,
            $current['quotation_path'], $current['quotation_uploaded_role'], $quotationUploadedBy,
            $current['design_3d_path'], $current['design_3d_uploaded_role'], $design3dUploadedBy, $design3dRemarks, $newStage3d
        );
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            chk2dNotifyRoleRevision(
                $conn,
                ROLE_DESIGNER,
                (int) $current['inquiry_id'],
                $recordForNotif,
                $currentUserId,
                '2D File (triggered by 3D revision)',
                $cascadeNote
            );
        }

        $message = 'Sent back for revision — the 2D file has also been reopened for rework.';
    }

    echo json_encode(['success' => (bool) $ok, 'message' => $ok ? $message : 'Failed to save review.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);