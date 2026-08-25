<?php
// 2d-and-quotation.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_SALES, ROLE_DESIGNER];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$qError = '';
$currentUserId = intval($_SESSION['account_id'] ?? 0);

// ⚠️ Adjust this if your session stores the role under a different key
$currentUserRole = $_SESSION['role'] ?? '';
$isSales = ($currentUserRole === ROLE_SALES);
$ownerColumn = $isSales ? 'sales_staff_id' : 'designer_id';

$inquiryId = intval($_GET['id'] ?? 0);

if ($inquiryId <= 0) {
    $qError = 'Missing or invalid inquiry reference.';
} else {
    $stmt = $conn->prepare("
        SELECT id, control_no, client_name, address, contact_number, status
        FROM noblecrminquiry
        WHERE id = ? AND {$ownerColumn} = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $inquiryId, $currentUserId);
    $stmt->execute();
    $inquiry = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$inquiry) {
        $qError = 'Inquiry not found, or not assigned to you.';
    } elseif (!in_array($inquiry['status'], ['In Progress', 'Approved', 'For Revision'], true)) {
        $qError = 'The site visit must be completed before 2D and Quotation can be submitted.';
    }
}

$crmBackUrl = $isSales ? (BASE_URL . '/crmsaleslist') : (BASE_URL . '/crmdesigner');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2D and Quotation</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-gray-50 font-['Barlow_Condensed']">
    <main class="ml-56 min-h-screen p-6">

        <div class="max-w-4xl mx-auto">

            <div class="bg-white border border-gray-300 shadow-sm">

                <!-- Letterhead -->
                <div class="border-b-2 border-gray-800 px-7 pt-6 pb-5 flex items-start justify-between font-semibold">
                    <div>
                        <p class="text-[10px] tracking-[0.2em] uppercase text-gray-500 mb-1">Client Relationship Management</p>
                        <h1 class="text-xl font-bold text-gray-900 tracking-wide">2D and Quotation</h1>
                    </div>
                    <a href="<?= htmlspecialchars($crmBackUrl) ?>"
                        class="text-xs font-medium text-gray-600 px-3 py-1.5 hover:bg-gray-100 transition-colors">
                         Back to List
                    </a>
                </div>

                <div class="px-7 py-6 text-sm">

                    <?php if (!empty($qError)): ?>
                        <div class="border border-gray-400 bg-gray-50 text-gray-800 text-sm px-4 py-2.5 mb-5">
                            <strong class="uppercase text-[11px] tracking-wide">Notice:</strong>
                            <?= htmlspecialchars($qError) ?>
                        </div>
                    <?php else: ?>

                        <!-- Everything below is rendered/refreshed by JS from crm2dquotationajax.php?action=state -->
                        <div id="q2dRoot">
                            <div class="space-y-3 py-4">
                                <div class="h-6 rounded bg-gray-100 animate-pulse w-1/3"></div>
                                <div class="h-24 rounded bg-gray-100 animate-pulse"></div>
                                <div class="h-24 rounded bg-gray-100 animate-pulse"></div>
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

                <div class="border-t border-gray-300 px-7 py-3 text-[11px] text-gray-400 flex justify-between">
                    <span>Generated on <?= date('F d, Y g:i A') ?></span>
                    <span id="q2dUpdatedAt">2D and Quotation</span>
                </div>

            </div>

        </div>

        <div id="crmToastContainer"
            class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
        </div>

        <?php if (empty($qError)): ?>
        <script>
            const Q2D_AJAX_URL = <?= json_encode(BASE_URL . '/crm2dquotationajax') ?>;
            const Q2D_INQUIRY_ID = <?= (int) $inquiryId ?>;
            const Q2D_POLL_INTERVAL_MS = 8000;

            let q2dPollTimer = null;
            let q2dLastSignature = '';
            // Tracks whether the user has an in-progress, not-yet-saved file
            // selection in a given slot. While true for a slot, a *silent*
            // background poll won't re-render that slot's markup out from
            // under them (it would wipe the picked file). An explicit action
            // (save/unlock/submit) or a manual open always re-renders fully.
            // NEW-3D: '3d' added alongside '2d' / 'quotation'.
            let q2dPendingSelection = { '2d': false, quotation: false, '3d': false };

            function crmShowToast(message, type = 'success', duration = 4000) {
                const container = document.getElementById('crmToastContainer');
                const palette = type === 'success'
                    ? { wrap: 'bg-white border-gray-800 text-gray-800', icon: 'bg-gray-800 text-white', symbol: '✓' }
                    : { wrap: 'bg-white border-gray-400 text-gray-800', icon: 'bg-gray-400 text-white', symbol: '!' };

                const toast = document.createElement('div');
                toast.className = `pointer-events-auto flex items-start gap-2.5 border rounded-none shadow-lg px-4 py-3 text-sm
            ${palette.wrap}
            translate-x-6 opacity-0 scale-95 transition-all duration-300 ease-out`;
                toast.innerHTML = `
            <span class="shrink-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold ${palette.icon}">${palette.symbol}</span>
            <span class="flex-1 leading-relaxed">${message}</span>
            <button type="button" class="shrink-0 text-current opacity-50 hover:opacity-100 text-base leading-none" aria-label="Close">&times;</button>
        `;
                container.appendChild(toast);
                requestAnimationFrame(() => toast.classList.remove('translate-x-6', 'opacity-0', 'scale-95'));
                const remove = () => {
                    toast.classList.add('translate-x-6', 'opacity-0', 'scale-95');
                    setTimeout(() => toast.remove(), 300);
                };
                toast.querySelector('button').addEventListener('click', remove);
                if (duration > 0) setTimeout(remove, duration);
            }

            function q2dEscapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str ?? '';
                return div.innerHTML;
            }

            // ═══════════════════════════════════════════════════════════
            // TEMPLATES
            // ═══════════════════════════════════════════════════════════

            const Q2D_UPLOAD_SVG = `<svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>`;

            // NEW-3D: '3d' -> design_3d_file (accepts PDF or image, converted
            // server-side to webp; see q2dSlotUpload3dView below for its own
            // dedicated markup/accept attribute).
            function q2dInputId(slot) {
                if (slot === '2d') return 'design_2d_pdf';
                if (slot === 'quotation') return 'quotation_pdf';
                return 'design_3d_file';
            }

            // Slot already marked Done — shows the link, uploader, and an
            // Edit button (Edit only shown when the parent draft isn't locked).
            function q2dSlotDoneView(fileData, showEdit, slot) {
                return `
                    <div class="text-sm">
                        <a href="${q2dEscapeHtml(fileData.url)}" target="_blank"
                            class="text-amber-700 hover:underline font-medium block mb-1">View File</a>
                        <p class="text-[11px] text-gray-500">
                            Uploaded by: ${q2dEscapeHtml(fileData.uploaded_by_name)}
                            (${q2dEscapeHtml(fileData.uploaded_role_label)})
                        </p>
                        ${showEdit ? `
                            <button type="button" onclick="q2dUnlock('${slot}')"
                                class="mt-2 text-xs font-medium text-gray-600 border border-gray-400 px-3 py-1.5 hover:bg-gray-100">
                                Edit
                            </button>
                        ` : ''}
                    </div>
                `;
            }

            // Upload UI — used both for "not done yet" active-draft slots and
            // for revision/fresh-submission slots. `currentLabel` carries the
            // "Current: filename.pdf" text when there's an existing (unlocked)
            // file already attached to the slot; the Mark-as-Done button starts
            // enabled in that case since no new file needs to be picked.
            function q2dSlotUploadView(slot, currentLabel) {
                const inputId = q2dInputId(slot);
                const hasCurrent = currentLabel.startsWith('Current: ');
                return `
                    <label for="${inputId}"
                        class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-gray-400 py-5 px-3 cursor-pointer hover:border-gray-800 hover:bg-gray-50 transition-colors">
                        ${Q2D_UPLOAD_SVG}
                        <span id="${inputId}_label" class="text-sm text-gray-600 text-center w-full truncate px-1">${q2dEscapeHtml(currentLabel)}</span>
                        <span class="text-[11px] text-gray-400">PDF only, max 15MB</span>
                    </label>
                    <input id="${inputId}" type="file" accept="application/pdf" class="hidden">
                    <a id="${inputId}_preview" href="#" target="_blank" rel="noopener"
                        class="hidden mt-1.5 items-center gap-1 text-xs font-medium text-amber-700 hover:underline">
                        View selected PDF &rarr;
                    </a>
                    <button type="button" id="${inputId}_done_btn" onclick="q2dSaveSlot('${slot}')" ${hasCurrent ? '' : 'disabled'}
                        class="mt-3 w-full px-4 py-2 text-sm font-semibold uppercase tracking-wide text-white bg-gray-900 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                        Mark as Done
                    </button>
                `;
            }

            // NEW-3D: same pattern as q2dSlotUploadView, but accepts PDF or
            // image (server converts images to webp on save).
            function q2dSlotUpload3dView(currentLabel) {
                const inputId = 'design_3d_file';
                const hasCurrent = currentLabel.startsWith('Current: ');
                return `
                    <label for="${inputId}"
                        class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-gray-400 py-5 px-3 cursor-pointer hover:border-gray-800 hover:bg-gray-50 transition-colors">
                        ${Q2D_UPLOAD_SVG}
                        <span id="${inputId}_label" class="text-sm text-gray-600 text-center w-full truncate px-1">${q2dEscapeHtml(currentLabel)}</span>
                        <span class="text-[11px] text-gray-400">PDF or image (JPG/PNG/WEBP), max 15MB</span>
                    </label>
                    <input id="${inputId}" type="file" accept="application/pdf,image/jpeg,image/png,image/webp" class="hidden">
                    <a id="${inputId}_preview" href="#" target="_blank" rel="noopener"
                        class="hidden mt-1.5 items-center gap-1 text-xs font-medium text-amber-700 hover:underline">
                        View selected file &rarr;
                    </a>
                    <button type="button" id="${inputId}_done_btn" onclick="q2dSaveSlot('3d')" ${hasCurrent ? '' : 'disabled'}
                        class="mt-3 w-full px-4 py-2 text-sm font-semibold uppercase tracking-wide text-white bg-gray-900 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                        Mark as Done
                    </button>
                `;
            }

            function q2dRevisionRemarksBox(remarks) {
                if (!remarks) return '';
                return `
                    <div class="border-l-4 border-red-700 bg-red-50 px-3 py-2 mb-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-red-700 mb-0.5">Revision Remarks</p>
                        <p class="text-xs text-red-800">${q2dEscapeHtml(remarks).replace(/\n/g, '<br>')}</p>
                    </div>
                `;
            }

            // "Already approved in a prior cycle" locked summary — shown in
            // revision mode for whichever file did NOT need revision.
            function q2dApprovedNoReuploadView(fileData) {
                return `
                    <div class="text-sm">
                        <span class="inline-block text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 border border-green-800 text-green-800 mb-2">Approved</span>
                        ${fileData.url ? `<a href="${q2dEscapeHtml(fileData.url)}" target="_blank" class="text-amber-700 hover:underline font-medium block mb-1">View File</a>` : ''}
                        <p class="text-[11px] text-gray-500">No re-upload needed.</p>
                    </div>
                `;
            }

            // NEW-3D: toggle for "submit 3D together with 2D & Quotation".
            // Only rendered while there's an unlocked Draft cycle to attach it
            // to (state.design_3d.toggle_editable).
            function q2dRenderToggle(design3d) {
                if (!design3d || !design3d.toggle_editable) return '';
                const checked = design3d.include_3d ? 'checked' : '';
                return `
                    <label class="flex items-center gap-2.5 mb-5 text-sm text-gray-700 cursor-pointer select-none">
                        <input type="checkbox" id="q2dInclude3dToggle" ${checked}
                            onchange="q2dToggleInclude3d(this.checked)"
                            class="w-4 h-4 accent-gray-900">
                        Submit 3D together with 2D &amp; Quotation
                        <span class="text-[11px] text-gray-400 font-normal">
                            (off = 3D unlocks only after 2D &amp; Quotation are approved)
                        </span>
                    </label>
                `;
            }

            // Fully-approved submission — the whole thing is settled, so this
            // is a locked, read-only summary. No upload prompts, no Submit
            // bar; just the approved files and who uploaded them.
            function q2dRenderCompletedView(completedEntry) {
                const reviewedLine = completedEntry.reviewed_at
                    ? `<p class="text-xs text-gray-400">Reviewed ${q2dEscapeHtml(completedEntry.reviewed_at)}</p>`
                    : '';

                return `
                    <div class="flex items-center justify-between border-l-4 border-gray-800 bg-gray-50 px-4 py-2.5 mb-6">
                        <div>
                            <p class="text-sm text-gray-800">
                                <strong class="uppercase tracking-wide text-[11px]">Status:</strong>
                                <span class="inline-block text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 border ml-1 border-green-800 text-green-800">Approved</span>
                            </p>
                            ${reviewedLine}
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 italic mb-6">
                        Both files have been approved. No further action is needed.
                    </p>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="border border-gray-300 p-4" data-slot-container="2d">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">2D File</p>
                            ${q2dSlotDoneView(completedEntry.design_2d, false, '2d')}
                        </div>
                        <div class="border border-gray-300 p-4" data-slot-container="quotation">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">Quotation File</p>
                            ${q2dSlotDoneView(completedEntry.quotation, false, 'quotation')}
                        </div>
                    </div>
                `;
            }

            function q2dRenderStatusBanner(activeDraft) {
                return `
                    <div class="flex items-center justify-between border-l-4 border-gray-800 bg-gray-50 px-4 py-2.5 mb-6">
                        <p class="text-sm text-gray-800">
                            <strong class="uppercase tracking-wide text-[11px]">Status:</strong>
                            <span class="inline-block text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 border ml-1 ${activeDraft.status_class}">${q2dEscapeHtml(activeDraft.status_label)}</span>
                        </p>
                    </div>
                    ${activeDraft.is_locked ? `
                        <p class="text-sm text-gray-500 italic mb-6">
                            Both files have been submitted and are waiting for approval. This can no longer be edited unless sent back for revision.
                        </p>
                    ` : ''}
                `;
            }

            // NEW-3D: now takes design3d so it can render a 3rd column when
            // this cycle bundles 3D together with 2D & Quotation.
            function q2dRenderActiveDraftSlots(activeDraft, design3d) {
                const twoD = activeDraft.design_2d;
                const quot = activeDraft.quotation;
                const locked = activeDraft.is_locked;
                const include3d = !!(design3d && design3d.include_3d);

                const twoDInner = twoD.done
                    ? q2dSlotDoneView(twoD, !locked, '2d')
                    : q2dSlotUploadView('2d', twoD.path ? `Current: ${twoD.filename}` : 'Click to upload 2D PDF');

                const quotInner = quot.done
                    ? q2dSlotDoneView(quot, !locked, 'quotation')
                    : q2dSlotUploadView('quotation', quot.path ? `Current: ${quot.filename}` : 'Click to upload Quotation PDF');

                let threeDBlock = '';
                if (include3d) {
                    const threeDInner = design3d.done
                        ? q2dSlotDoneView(design3d, !locked, '3d')
                        : q2dSlotUpload3dView(design3d.path ? `Current: ${design3d.filename}` : 'Click to upload 3D file');

                    threeDBlock = `
                        <div class="border border-gray-300 p-4" data-slot-container="3d">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">3D File</p>
                            ${threeDInner}
                        </div>
                    `;
                }

                const gridClass = include3d ? 'grid grid-cols-3 gap-4 mb-6' : 'grid grid-cols-2 gap-4 mb-6';

                return `
                    <div class="${gridClass}">
                        <div class="border border-gray-300 p-4" data-slot-container="2d">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">2D File</p>
                            ${twoDInner}
                        </div>
                        <div class="border border-gray-300 p-4" data-slot-container="quotation">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">Quotation File</p>
                            ${quotInner}
                        </div>
                        ${threeDBlock}
                    </div>
                `;
            }

            // NEW-3D: both_done now also requires the 3D slot when bundled.
            // (The server enforces this too in submit_final — this is just so
            // the button's disabled state reflects it immediately.)
            function q2dRenderSubmitBar(activeDraft, design3d) {
                if (activeDraft.is_locked) return '';
                const include3d = !!(design3d && design3d.include_3d);
                const allDone = activeDraft.both_done && (!include3d || design3d.done);
                return `
                    <div class="pt-3 border-t border-gray-200 flex items-center justify-end gap-3">
                        ${!allDone ? `<p class="text-xs text-gray-400">Complete ${include3d ? 'all three files' : 'both files'} first.</p>` : ''}
                        <button type="button" id="q2dSubmitBtn" onclick="q2dSubmitFinal()" ${allDone ? '' : 'disabled'}
                            class="px-5 py-2 text-sm font-semibold uppercase tracking-wide text-white bg-gray-900 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            Submit for Approval
                        </button>
                    </div>
                `;
            }

            function q2dRenderRevisionOrFreshSlots(revisionEntry) {
                const headerBlock = revisionEntry ? `
                    <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4">
                        Re-upload Files
                    </h2>
                    <p class="text-sm text-gray-500 italic mb-6">
                        ${revisionEntry.design_2d_needs_revision && revisionEntry.quotation_needs_revision
                            ? 'Both the 2D and Quotation files need revision. Attach the corrected PDFs below.'
                            : revisionEntry.design_2d_needs_revision
                                ? 'Only the 2D file needs revision. The Quotation file was already approved and does not need to be re-uploaded.'
                                : 'Only the Quotation file needs revision. The 2D file was already approved and does not need to be re-uploaded.'}
                    </p>
                ` : `
                    <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4">
                        No Active Submission
                    </h2>
                    <p class="text-sm text-gray-500 italic mb-6">
                        No 2D and Quotation files have been submitted yet for this inquiry. Attach a PDF below to start.
                    </p>
                `;

                const twoDInner = (revisionEntry && !revisionEntry.design_2d_needs_revision)
                    ? q2dApprovedNoReuploadView(revisionEntry.design_2d)
                    : `${revisionEntry ? q2dRevisionRemarksBox(revisionEntry.design_2d.remarks) : ''}${q2dSlotUploadView('2d', 'Click to upload 2D PDF')}`;

                const quotInner = (revisionEntry && !revisionEntry.quotation_needs_revision)
                    ? q2dApprovedNoReuploadView(revisionEntry.quotation)
                    : `${revisionEntry ? q2dRevisionRemarksBox(revisionEntry.quotation.remarks) : ''}${q2dSlotUploadView('quotation', 'Click to upload Quotation PDF')}`;

                return `
                    ${headerBlock}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="border border-gray-300 p-4" data-slot-container="2d">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">2D File</p>
                            ${twoDInner}
                        </div>
                        <div class="border border-gray-300 p-4" data-slot-container="quotation">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-2">Quotation File</p>
                            ${quotInner}
                        </div>
                    </div>
                `;
            }

            function q2dFileCell(fileData) {
                if (!fileData.url) return '—';
                const reviewLine = fileData.review_status
                    ? `<span class="block text-[10px] font-semibold uppercase tracking-wide mt-0.5 ${fileData.review_class}">${q2dEscapeHtml(fileData.review_status)}</span>`
                    : '';
                return `<a href="${q2dEscapeHtml(fileData.url)}" target="_blank" class="text-amber-700 hover:underline">View File</a>${reviewLine}`;
            }

            // NEW-3D: past-entries table now has a 3D column too (only shown
            // when that particular past cycle actually bundled 3D).
            function q2dRenderPastEntries(pastEntries) {
                if (!pastEntries || pastEntries.length === 0) return '';

                const anyBundled3d = pastEntries.some(e => e.design_3d && e.design_3d.included);

                const rows = pastEntries.map(entry => {
                    const hasPerFileRemarks = !!entry.design_2d_remarks || !!entry.quotation_remarks;
                    const remarksCell = hasPerFileRemarks
                        ? `${entry.design_2d_remarks ? `<p class="mb-1"><span class="font-semibold text-gray-700">2D:</span> ${q2dEscapeHtml(entry.design_2d_remarks).replace(/\n/g, '<br>')}</p>` : ''}${entry.quotation_remarks ? `<p><span class="font-semibold text-gray-700">Quotation:</span> ${q2dEscapeHtml(entry.quotation_remarks).replace(/\n/g, '<br>')}</p>` : ''}`
                        : (entry.remarks ? q2dEscapeHtml(entry.remarks).replace(/\n/g, '<br>') : '—');

                    const threeDCell = anyBundled3d
                        ? `<td class="px-4 py-2">${entry.design_3d && entry.design_3d.included ? q2dFileCell(entry.design_3d) : '<span class="text-gray-300">—</span>'}</td>`
                        : '';

                    return `
                        <tr class="border-b border-gray-200 last:border-b-0">
                            <td class="px-4 py-2">${q2dFileCell(entry.design_2d)}</td>
                            <td class="px-4 py-2">${q2dFileCell(entry.quotation)}</td>
                            ${threeDCell}
                            <td class="px-4 py-2 text-gray-600 whitespace-nowrap">${entry.submitted_at ? q2dEscapeHtml(entry.submitted_at) : '—'}</td>
                            <td class="px-4 py-2">
                                <span class="text-[11px] font-semibold uppercase tracking-wide ${entry.status_class}">${q2dEscapeHtml(entry.status_label)}</span>
                            </td>
                            <td class="px-4 py-2 text-gray-600 max-w-[220px]">${remarksCell}</td>
                        </tr>
                    `;
                }).join('');

                return `
                    <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4 mt-8">
                        Prior Submissions
                    </h2>
                    <table class="w-full border border-gray-300 text-sm mb-4">
                        <thead>
                            <tr class="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                                <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">2D File</th>
                                <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">Quotation File</th>
                                ${anyBundled3d ? `<th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">3D File</th>` : ''}
                                <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">Submitted</th>
                                <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">Result</th>
                                <th class="text-left font-medium px-4 py-2 border-b border-gray-200">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                `;
            }

            // NEW-3D: standalone "3D File" section for the sequential flow —
            // shown whenever 3D is NOT bundled into the current cycle but has
            // been unlocked (i.e. 2D & Quotation are Approved), is waiting for
            // approval on its own, is already Approved, or needs its own
            // revision.
            function q2dRender3dStandaloneSection(design3d) {
                if (!design3d || design3d.include_3d || design3d.stage === 'Locked') return '';

                const stage = design3d.stage; // Draft | Waiting for Approval | Approved | For Revision
                let inner;

                if (stage === 'Approved') {
                    inner = `
                        <div class="border border-gray-300 p-4" data-slot-container="3d">
                            ${q2dSlotDoneView(design3d, false, '3d')}
                        </div>
                    `;
                } else if (stage === 'Waiting for Approval') {
                    inner = `
                        <p class="text-sm text-gray-500 italic mb-3">3D file submitted, waiting for approval.</p>
                        <div class="border border-gray-300 p-4" data-slot-container="3d">${q2dSlotDoneView(design3d, false, '3d')}</div>
                    `;
                } else {
                    // Draft (open for upload) or For Revision (re-upload)
                    const remarksBox = (stage === 'For Revision' && design3d.remarks)
                        ? q2dRevisionRemarksBox(design3d.remarks) : '';
                    const uploadInner = design3d.done
                        ? q2dSlotDoneView(design3d, true, '3d')
                        : q2dSlotUpload3dView(design3d.path ? `Current: ${design3d.filename}` : 'Click to upload 3D file');
                    inner = `
                        <div class="border border-gray-300 p-4" data-slot-container="3d">
                            ${remarksBox}
                            ${uploadInner}
                        </div>
                        <div class="pt-3 mt-3 border-t border-gray-200 flex items-center justify-end gap-3">
                            ${!design3d.done ? `<p class="text-xs text-gray-400">Complete the 3D file first.</p>` : ''}
                            <button type="button" onclick="q2dSubmit3d()" ${design3d.done ? '' : 'disabled'}
                                class="px-5 py-2 text-sm font-semibold uppercase tracking-wide text-white bg-gray-900 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                                Submit 3D for Approval
                            </button>
                        </div>
                    `;
                }

                return `
                    <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4 mt-8">
                        3D File
                    </h2>
                    ${inner}
                `;
            }

            function q2dInquirySummary(inquiry) {
                return `
                    <table class="w-full border border-gray-300 text-sm mb-6">
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <td class="w-32 bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                    Control No.
                                </td>
                                <td class="px-4 py-2.5 font-semibold text-gray-900">
                                    ${q2dEscapeHtml(inquiry.control_no)}
                                </td>
                                <td class="w-28 bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-l border-gray-200">
                                    Client
                                </td>
                                <td class="px-4 py-2.5 text-gray-900">
                                    ${q2dEscapeHtml(inquiry.client_name)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                `;
            }

            function q2dRenderRoot(state) {
                const root = document.getElementById('q2dRoot');
                const design3d = state.design_3d;

                let body = q2dInquirySummary(state.inquiry);
                body += q2dRenderToggle(design3d); // NEW-3D

                if (state.active_draft) {
                    body += q2dRenderStatusBanner(state.active_draft);
                    body += q2dRenderActiveDraftSlots(state.active_draft, design3d); // NEW-3D: pass design3d
                    body += q2dRenderSubmitBar(state.active_draft, design3d); // NEW-3D: pass design3d
                } else if (state.completed_entry) {
                    body += q2dRenderCompletedView(state.completed_entry);
                    body += q2dRender3dStandaloneSection(design3d); // NEW-3D
                } else {
                    body += q2dRenderRevisionOrFreshSlots(state.revision_entry);
                    body += q2dRender3dStandaloneSection(design3d); // NEW-3D
                }

                body += q2dRenderPastEntries(state.past_entries);

                root.innerHTML = body;

                // Reset pending-selection tracking and (re)bind the upload
                // inputs that exist in the freshly rendered markup.
                q2dPendingSelection = { '2d': false, quotation: false, '3d': false };
                q2dBindLabel('2d');
                q2dBindLabel('quotation');
                q2dBindLabel('3d'); // NEW-3D
            }

            // ── Filename label + preview link + enable "Mark as Done" once may napiling file ──
            function q2dBindLabel(slot) {
                const inputId = q2dInputId(slot);
                const input = document.getElementById(inputId);
                const label = document.getElementById(`${inputId}_label`);
                const preview = document.getElementById(`${inputId}_preview`);
                const doneBtn = document.getElementById(`${inputId}_done_btn`);
                if (!input || !label || !preview) return;

                let currentObjectUrl = null;
                const fallback = label.textContent;

                input.addEventListener('change', function () {
                    if (currentObjectUrl) {
                        URL.revokeObjectURL(currentObjectUrl);
                        currentObjectUrl = null;
                    }

                    if (this.files.length) {
                        const file = this.files[0];
                        label.textContent = file.name;
                        label.title = file.name;

                        currentObjectUrl = URL.createObjectURL(file);
                        preview.href = currentObjectUrl;
                        preview.classList.remove('hidden');
                        preview.classList.add('flex');

                        if (doneBtn) doneBtn.disabled = false;
                        q2dPendingSelection[slot] = true;
                    } else {
                        label.textContent = fallback;
                        label.title = '';
                        preview.classList.add('hidden');
                        preview.classList.remove('flex');
                        preview.href = '#';
                        q2dPendingSelection[slot] = fallback.startsWith('Current: ') ? true : false;
                        if (doneBtn) doneBtn.disabled = !fallback.startsWith('Current: ');
                    }
                });
            }

            // ═══════════════════════════════════════════════════════════
            // STATE FETCH / POLLING
            // ═══════════════════════════════════════════════════════════
            async function q2dFetchState({ silent = false } = {}) {
                try {
                    const res = await fetch(`${Q2D_AJAX_URL}?action=state&inquiry_id=${Q2D_INQUIRY_ID}`);
                    const data = await res.json();

                    if (!data.success) {
                        if (!silent) {
                            document.getElementById('q2dRoot').innerHTML = `
                                <div class="border border-gray-400 bg-gray-50 text-gray-800 text-sm px-4 py-2.5">
                                    <strong class="uppercase text-[11px] tracking-wide">Notice:</strong>
                                    ${q2dEscapeHtml(data.message || 'Unable to load this submission.')}
                                </div>
                            `;
                        }
                        return;
                    }

                    // While the user has an unsaved file picked in some slot,
                    // don't let a silent background poll blow it away.
                    const hasPendingSelection = q2dPendingSelection['2d'] || q2dPendingSelection.quotation || q2dPendingSelection['3d'];
                    if (silent && hasPendingSelection) {
                        return;
                    }

                    // NEW-3D: include design_3d in the signature so 3D-only
                    // changes (e.g. sequential submit/approve) still trigger
                    // a re-render.
                    const signature = JSON.stringify(data.active_draft) + JSON.stringify(data.completed_entry)
                        + JSON.stringify(data.revision_entry) + JSON.stringify(data.past_entries)
                        + JSON.stringify(data.design_3d);
                    if (signature !== q2dLastSignature) {
                        q2dRenderRoot(data);
                        q2dLastSignature = signature;
                    }

                    const now = new Date();
                    const updatedEl = document.getElementById('q2dUpdatedAt');
                    if (updatedEl) {
                        updatedEl.textContent = `Updated ${now.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', second: '2-digit' })}`;
                    }

                } catch (e) {
                    console.error('q2dFetchState:', e);
                    if (!silent) crmShowToast('Connection error while loading this submission.', 'error');
                }
            }

            function q2dStartPolling() {
                if (q2dPollTimer) clearInterval(q2dPollTimer);
                q2dPollTimer = setInterval(() => q2dFetchState({ silent: true }), Q2D_POLL_INTERVAL_MS);
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    if (q2dPollTimer) clearInterval(q2dPollTimer);
                } else {
                    q2dFetchState({ silent: true });
                    q2dStartPolling();
                }
            });

            q2dFetchState().then(q2dStartPolling);

            // ═══════════════════════════════════════════════════════════
            // ACTIONS — save_slot / unlock_slot / submit_final now just
            // refresh state (q2dFetchState) instead of reloading the page.
            // ═══════════════════════════════════════════════════════════

            // NEW-3D: toggle "submit 3D together" on/off.
            async function q2dToggleInclude3d(checked) {
                const formData = new FormData();
                formData.append('action', 'save_toggle');
                formData.append('inquiry_id', Q2D_INQUIRY_ID);
                formData.append('include_3d', checked ? '1' : '0');

                try {
                    const res = await fetch(Q2D_AJAX_URL, { method: 'POST', body: formData });
                    const data = await res.json();

                    if (!data.success) {
                        crmShowToast(data.message || 'Something went wrong.', 'error');
                        return;
                    }

                    q2dLastSignature = '';
                    await q2dFetchState();
                } catch (e) {
                    console.error('q2dToggleInclude3d:', e);
                    crmShowToast('Connection error. Please try again.', 'error');
                }
            }

            async function q2dSaveSlot(slot) {
                const inputId = q2dInputId(slot);
                // NEW-3D: 3D's field name is design_3d_file (not *_pdf), since
                // it can be a PDF or an image.
                const fileKey = slot === '2d' ? 'design_2d_pdf' : (slot === 'quotation' ? 'quotation_pdf' : 'design_3d_file');
                const input = document.getElementById(inputId);

                const formData = new FormData();
                formData.append('action', 'save_slot');
                formData.append('inquiry_id', Q2D_INQUIRY_ID);
                formData.append('slot', slot);
                if (input && input.files.length) {
                    formData.append(fileKey, input.files[0]);
                }

                try {
                    const res = await fetch(Q2D_AJAX_URL, { method: 'POST', body: formData });
                    const data = await res.json();

                    if (!data.success) {
                        crmShowToast(data.message || 'Something went wrong.', 'error');
                        return;
                    }

                    q2dPendingSelection[slot] = false;
                    q2dLastSignature = ''; // force re-render even if signature looks unchanged
                    crmShowToast(data.message || 'Saved.');
                    await q2dFetchState();
                } catch (e) {
                    console.error('q2dSaveSlot:', e);
                    crmShowToast('Connection error. Please try again.', 'error');
                }
            }

            async function q2dUnlock(slot) {
                const formData = new FormData();
                formData.append('action', 'unlock_slot');
                formData.append('inquiry_id', Q2D_INQUIRY_ID);
                formData.append('slot', slot);

                try {
                    const res = await fetch(Q2D_AJAX_URL, { method: 'POST', body: formData });
                    const data = await res.json();

                    if (!data.success) {
                        crmShowToast(data.message || 'Something went wrong.', 'error');
                        return;
                    }

                    q2dLastSignature = '';
                    await q2dFetchState();
                } catch (e) {
                    console.error('q2dUnlock:', e);
                    crmShowToast('Connection error. Please try again.', 'error');
                }
            }

            async function q2dSubmitFinal() {
                const formData = new FormData();
                formData.append('action', 'submit_final');
                formData.append('inquiry_id', Q2D_INQUIRY_ID);

                try {
                    const res = await fetch(Q2D_AJAX_URL, { method: 'POST', body: formData });
                    const data = await res.json();

                    if (!data.success) {
                        crmShowToast(data.message || 'Something went wrong.', 'error');
                        return;
                    }

                    q2dLastSignature = '';
                    crmShowToast(data.message || 'Submitted for approval.');
                    await q2dFetchState();
                } catch (e) {
                    console.error('q2dSubmitFinal:', e);
                    crmShowToast('Connection error. Please try again.', 'error');
                }
            }

            // NEW-3D: sequential-flow submit — only used when 3D was NOT
            // bundled and has since unlocked after 2D & Quotation approval.
            async function q2dSubmit3d() {
                const formData = new FormData();
                formData.append('action', 'submit_3d');
                formData.append('inquiry_id', Q2D_INQUIRY_ID);

                try {
                    const res = await fetch(Q2D_AJAX_URL, { method: 'POST', body: formData });
                    const data = await res.json();

                    if (!data.success) {
                        crmShowToast(data.message || 'Something went wrong.', 'error');
                        return;
                    }

                    q2dLastSignature = '';
                    crmShowToast(data.message || 'Submitted for approval.');
                    await q2dFetchState();
                } catch (e) {
                    console.error('q2dSubmit3d:', e);
                    crmShowToast('Connection error. Please try again.', 'error');
                }
            }
        </script>
        <?php endif; ?>

    </main>
</body>

</html>