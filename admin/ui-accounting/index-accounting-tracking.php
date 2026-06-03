<?php
// index-accounting-tracking.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$sql = "
    SELECT 
        br.*,
        u.name    AS requestor_full_name,
        ab.name   AS approved_by_name,
        rb.name   AS received_by_name,
        sb.name   AS sent_to_name,

        v.id               AS voucher_id,
        v.status           AS voucher_status,
        v.certified_by     AS v_certified_by,
        v.certified_at     AS v_certified_at,
        v.prepared_by      AS v_prepared_by,
        v.prepared_at      AS v_prepared_at,
        v.approved_by      AS v_approved_by,
        v.approved_at      AS v_approved_at,
        v.released_by      AS v_released_by,
        v.released_at      AS v_released_at,
        v.received_by      AS v_received_by,
        v.received_at      AS v_received_at,

        vc.name  AS v_certified_name,
        vp.name  AS v_prepared_name,
        va.name  AS v_approved_name,
        vrl.name AS v_released_name,
        vrv.name AS v_received_name

    FROM noblebudgetrequest br
    LEFT JOIN noblerole u   ON br.user_id     = u.id
    LEFT JOIN noblerole ab  ON br.approved_by = ab.id
    LEFT JOIN noblerole rb  ON br.received_by = rb.id
    LEFT JOIN noblerole sb  ON br.sent_to     = sb.id
    LEFT JOIN noblevoucher v    ON v.request_id  = br.id
    LEFT JOIN noblerole vc  ON v.certified_by = vc.id
    LEFT JOIN noblerole vp  ON v.prepared_by  = vp.id
    LEFT JOIN noblerole va  ON v.approved_by  = va.id
    LEFT JOIN noblerole vrl ON v.released_by  = vrl.id
    LEFT JOIN noblerole vrv ON v.received_by  = vrv.id
    ORDER BY br.created_at DESC
";
$result = $conn->query($sql);
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Request Tracking</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
    <style>
        .step-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .pulse-dot::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            opacity: 0.4;
            animation: pulse 1.8s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.4; }
            70% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        .badge-pending  { background:#fef9c3; color:#854d0e; }
        .badge-approved { background:#dcfce7; color:#166534; }
        .badge-rejected { background:#fee2e2; color:#991b1b; }
        .badge-received { background:#dbeafe; color:#1e40af; }
        .badge-voucher  { background:#f3e8ff; color:#6b21a8; }

        .modal-inner::-webkit-scrollbar { width: 4px; }
        .modal-inner::-webkit-scrollbar-track { background: transparent; }
        .modal-inner::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .modal-inner::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="bg-slate-100">
    <main id="main-content" class="md:ml-56 pt-20 md:pt-5 min-h-screen p-4 md:p-8 transition-all duration-300">

        <!-- Header -->
        <div class="mb-6">
            <div class="mb-3">
                <h1 class="text-xl font-bold text-slate-800">Budget Request Tracking</h1>
                <p class="text-sm text-slate-500 mt-0.5">Real-time status of all budget requests</p>
            </div>
            <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:gap-2">
                <span class="text-xs px-3 py-2 sm:py-1 rounded-xl sm:rounded-full bg-yellow-100 text-yellow-800 font-medium text-center flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1">
                    <i class="fas fa-clock"></i>
                    <span class="font-bold sm:font-medium"><?= count(array_filter($requests, fn($r) => $r['status'] === 'pending')) ?></span>
                    <span class="text-[10px] sm:text-xs opacity-70 sm:opacity-100">Pending</span>
                </span>
                <span class="text-xs px-3 py-2 sm:py-1 rounded-xl sm:rounded-full bg-green-100 text-green-800 font-medium text-center flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1">
                    <i class="fas fa-check-circle"></i>
                    <span class="font-bold sm:font-medium"><?= count(array_filter($requests, fn($r) => $r['status'] === 'approved')) ?></span>
                    <span class="text-[10px] sm:text-xs opacity-70 sm:opacity-100">Approved</span>
                </span>
                <span class="text-xs px-3 py-2 sm:py-1 rounded-xl sm:rounded-full bg-red-100 text-red-800 font-medium text-center flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1">
                    <i class="fas fa-times-circle"></i>
                    <span class="font-bold sm:font-medium"><?= count(array_filter($requests, fn($r) => $r['status'] === 'rejected')) ?></span>
                    <span class="text-[10px] sm:text-xs opacity-70 sm:opacity-100">Rejected</span>
                </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm border border-slate-100 rounded-xl p-4 mb-5 flex flex-col sm:flex-row gap-3">
            <input type="text" id="searchInput" placeholder="Search by name, control no, purpose..."
                class="flex-1 border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <div class="flex gap-3">
                <select id="statusFilter"
                    class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="categoryFilter"
                    class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Category</option>
                    <option value="project">Project</option>
                    <option value="client">Client</option>
                    <option value="nhcc">NHCC</option>
                </select>
            </div>
        </div>

        <!-- Desktop Table (md and up) -->
        <div class="hidden md:block bg-white shadow-sm border border-slate-100 rounded-xl overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[60vh]">
                <table class="w-full text-sm" id="requestTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Control No.</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Requestor</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Purpose</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Category</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Date</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase text-xs tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-16 text-slate-400">
                                    <div class="text-4xl mb-2"><i class="fas fa-inbox"></i></div>
                                    <div>No budget requests found.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($requests as $req): ?>
                            <?php
                            $status = $req['status'];
                            $hasVoucher = !empty($req['voucher_id']);
                            $vStatus = $req['voucher_status'] ?? '';

                            if ($hasVoucher && $vStatus === 'released') {
                                $badgeClass = 'badge-received'; $displayStatus = 'Released';
                            } elseif ($hasVoucher && !empty($req['v_received_at'])) {
                                $badgeClass = 'badge-received'; $displayStatus = 'Received';
                            } elseif ($hasVoucher) {
                                $badgeClass = 'badge-voucher';
                                $displayStatus = 'Voucher: ' . ucfirst(str_replace('_', ' ', $vStatus));
                            } else {
                                $badgeClass = match ($status) {
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    default    => 'badge-pending',
                                };
                                $displayStatus = ucfirst($status);
                            }
                            ?>
                            <tr class="hover:bg-slate-50 cursor-pointer request-row"
                                data-id="<?= $req['id'] ?>"
                                data-status="<?= $status ?>"
                                data-category="<?= $req['request_category'] ?>"
                                data-search="<?= strtolower(($req['control_no'] ?? '') . ' ' . ($req['requestor_name'] ?? '') . ' ' . ($req['purpose'] ?? '')) ?>">
                                <td class="px-5 py-4 font-mono font-semibold text-slate-700"><?= htmlspecialchars($req['control_no'] ?? '') ?></td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-slate-700"><?= htmlspecialchars($req['requestor_name'] ?? '—') ?></div>
                                    <div class="text-xs text-slate-400"><?= htmlspecialchars($req['sent_to_name'] ?? '—') ?></div>
                                </td>
                                <td class="px-5 py-4 text-slate-600 max-w-[200px] truncate"><?= htmlspecialchars($req['purpose'] ?? '') ?></td>
                                <td class="px-5 py-4">
                                    <span class="text-xs px-2 py-1 rounded-md bg-slate-100 text-slate-600 capitalize"><?= htmlspecialchars($req['request_category'] ?? '—') ?></span>
                                </td>
                                <td class="px-5 py-4 text-slate-500 text-xs">
                                    <div><?= $req['date_requested'] ? date('M d, Y', strtotime($req['date_requested'])) : '—' ?></div>
                                    <div class="text-slate-400"><?= $req['created_at'] ? date('h:i A', strtotime($req['created_at'])) : '' ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badgeClass ?>"><?= $displayStatus ?></span>
                                </td>
                                <td class="px-5 py-4">
                                    <button onclick="openTracker(<?= htmlspecialchars(json_encode($req), ENT_QUOTES) ?>)"
                                        class="text-blue-500 hover:text-blue-700 text-xs font-medium flex items-center gap-1">
                                        <i class="fas fa-route"></i> Track
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards (below md) -->
        <div class="md:hidden overflow-y-auto flex flex-col gap-3" style="max-height:65vh; padding-right:2px;" id="mobile-cards">
            <?php if (empty($requests)): ?>
                <div class="bg-white rounded-xl border border-slate-100 py-16 text-center text-slate-400">
                    <div class="text-4xl mb-2"><i class="fas fa-inbox"></i></div>
                    <div class="text-sm">No budget requests found.</div>
                </div>
            <?php endif; ?>
            <?php foreach ($requests as $req): ?>
                <?php
                $status = $req['status'];
                $hasVoucher = !empty($req['voucher_id']);
                $vStatus = $req['voucher_status'] ?? '';

                if ($hasVoucher && $vStatus === 'released') {
                    $badgeClass = 'badge-received'; $displayStatus = 'Released';
                } elseif ($hasVoucher && !empty($req['v_received_at'])) {
                    $badgeClass = 'badge-received'; $displayStatus = 'Received';
                } elseif ($hasVoucher) {
                    $badgeClass = 'badge-voucher';
                    $displayStatus = 'Voucher: ' . ucfirst(str_replace('_', ' ', $vStatus));
                } else {
                    $badgeClass = match ($status) {
                        'approved' => 'badge-approved',
                        'rejected' => 'badge-rejected',
                        default    => 'badge-pending',
                    };
                    $displayStatus = ucfirst($status);
                }
                ?>
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-4 py-4 request-row"
                    data-id="<?= $req['id'] ?>"
                    data-status="<?= $status ?>"
                    data-category="<?= $req['request_category'] ?>"
                    data-search="<?= strtolower(($req['control_no'] ?? '') . ' ' . ($req['requestor_name'] ?? '') . ' ' . ($req['purpose'] ?? '')) ?>">

                    <!-- Top row: control no + status -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="font-mono font-bold text-slate-700 text-sm"><?= htmlspecialchars($req['control_no'] ?? '—') ?></span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?= $badgeClass ?> shrink-0"><?= $displayStatus ?></span>
                    </div>

                    <!-- Purpose -->
                    <p class="text-sm text-slate-700 font-medium truncate mb-1"><?= htmlspecialchars($req['purpose'] ?? '—') ?></p>

                    <!-- Meta row -->
                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-400 mb-3">
                        <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($req['requestor_name'] ?? '—') ?></span>
                        <span><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($req['request_category'] ?? '—') ?></span>
                        <span><i class="fas fa-calendar mr-1"></i><?= $req['date_requested'] ? date('M d, Y', strtotime($req['date_requested'])) : '—' ?></span>
                    </div>

                    <!-- Track button -->
                    <button onclick="openTracker(<?= htmlspecialchars(json_encode($req), ENT_QUOTES) ?>)"
                        class="w-full text-center bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-semibold py-2 rounded-lg transition-colors">
                        <i class="fas fa-route mr-1"></i> View Tracking
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Tracker Modal -->
    <div id="trackerModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4"
        style="overflow:hidden;">
        <div class="modal-inner bg-white w-full max-w-lg rounded-2xl"
            style="max-height:90vh; overflow-y:auto; overflow-x:hidden; scrollbar-width:thin; scrollbar-color:#cbd5e1 transparent;">

            <!-- Modal Header -->
            <div class="px-5 py-5 sticky top-0 z-10 rounded-t-2xl" style="background:#1e293b;">
                <div class="flex justify-between items-start">
                    <div class="min-w-0 flex-1 pr-4">
                        <p class="text-slate-400 text-xs uppercase tracking-widest mb-1">Budget Request</p>
                        <h2 class="text-white font-bold text-base leading-tight" id="modal-control-no">—</h2>
                        <p class="text-slate-300 text-sm mt-1 line-clamp-2" id="modal-purpose">—</p>
                    </div>
                    <button onclick="closeTracker()" class="text-slate-400 hover:text-white shrink-0 mt-1">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-3 text-xs text-slate-300">
                    <span><i class="fas fa-user mr-1"></i><span id="modal-requestor">—</span></span>
                    <span class="hidden sm:inline">·</span>
                    <span><i class="fas fa-calendar mr-1"></i><span id="modal-date">—</span></span>
                    <span class="hidden sm:inline">·</span>
                    <span id="modal-category-badge" class="px-2 py-0.5 rounded-full bg-slate-600 text-slate-200">—</span>
                </div>
            </div>

            <!-- Tracker Steps -->
            <div class="px-5 py-5">
                <p class="text-xs text-slate-400 uppercase tracking-widest mb-5 font-semibold">
                    <i class="fas fa-stream mr-1"></i> Request Timeline
                </p>
                <div class="flex flex-col" id="tracker-steps"></div>
            </div>

            <!-- Reject Comment -->
            <div id="reject-block" class="hidden mx-5 mb-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3">
                <p class="text-xs font-semibold text-red-500 mb-1"><i class="fas fa-ban mr-1"></i> Rejection Reason</p>
                <p class="text-sm text-red-700" id="modal-reject-comment">—</p>
            </div>

            <!-- Footer -->
            <div class="px-5 pb-5">
                <div class="bg-slate-50 rounded-xl px-4 py-3 text-xs text-slate-400 flex flex-col sm:flex-row sm:justify-between gap-1">
                    <span><i class="fas fa-hashtag mr-1"></i> Ref: <span id="modal-reference" class="text-slate-600 font-medium">—</span></span>
                    <span><i class="fas fa-paper-plane mr-1"></i> Sent to: <span id="modal-sent-to" class="text-slate-600 font-medium">—</span></span>
                </div>
            </div>

        </div>
    </div>

    <script>
        function fdt(val) {
            if (!val || val === '0000-00-00 00:00:00' || val === '0000-00-00') return null;
            const d = new Date(val);
            if (isNaN(d)) return null;
            return d.toLocaleString('en-PH', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            });
        }

        function openTracker(req) {
            document.getElementById('modal-control-no').textContent = req.control_no || '—';
            document.getElementById('modal-purpose').textContent = req.purpose || '—';
            document.getElementById('modal-requestor').textContent = req.requestor_name || '—';
            document.getElementById('modal-date').textContent = req.date_requested || '—';
            document.getElementById('modal-category-badge').textContent = req.request_category || '—';
            document.getElementById('modal-reference').textContent = req.request_reference || '—';
            document.getElementById('modal-sent-to').textContent = req.sent_to_name || '—';

            const rejectBlock = document.getElementById('reject-block');
            if (req.status === 'rejected' && req.reject_comment) {
                rejectBlock.classList.remove('hidden');
                document.getElementById('modal-reject-comment').textContent = req.reject_comment;
            } else {
                rejectBlock.classList.add('hidden');
            }

            renderSteps(buildSteps(req));

            const modal = document.getElementById('trackerModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function buildSteps(r) {
            const isApproved = r.status === 'approved';
            const isRejected = r.status === 'rejected';

            return [
                {
                    label: 'Request Submitted',
                    sub: r.requestor_name || '—',
                    time: fdt(r.created_at),
                    done: true, active: false,
                    icon: 'fa-file-alt'
                },
                {
                    label: isRejected ? 'Request Rejected' : 'Approved by Accounting',
                    sub: isRejected ? (r.reject_comment ? 'See rejection reason below' : 'No comment') : (r.approved_by_name || 'Awaiting approval'),
                    time: (isApproved || isRejected) ? fdt(r.approved_at) : null,
                    done: isApproved || isRejected,
                    active: !isApproved && !isRejected,
                    rejected: isRejected,
                    icon: isRejected ? 'fa-times' : 'fa-check-double'
                },
                {
                    label: 'Received by Staff',
                    sub: r.received_by_name || 'Awaiting receipt',
                    time: fdt(r.received_at),
                    done: !!r.received_at,
                    active: isApproved && !r.received_at,
                    icon: 'fa-hand-holding'
                },
                {
                    label: 'Voucher — Certified',
                    sub: r.v_certified_name || 'Awaiting certification',
                    time: fdt(r.v_certified_at),
                    done: !!r.v_certified_at,
                    active: !!r.received_at && !r.v_certified_at,
                    icon: 'fa-stamp'
                },
                {
                    label: 'Voucher — Prepared',
                    sub: r.v_prepared_name || 'Awaiting preparation',
                    time: fdt(r.v_prepared_at),
                    done: !!r.v_prepared_at,
                    active: !!r.v_certified_at && !r.v_prepared_at,
                    icon: 'fa-pen-nib'
                },
                {
                    label: 'Voucher — Approved',
                    sub: r.v_approved_name || 'Awaiting approval',
                    time: fdt(r.v_approved_at),
                    done: !!r.v_approved_at,
                    active: !!r.v_prepared_at && !r.v_approved_at,
                    icon: 'fa-clipboard-check'
                },
                {
                    label: 'Voucher — Released',
                    sub: r.v_released_name || 'Awaiting release',
                    time: fdt(r.v_released_at),
                    done: !!r.v_released_at,
                    active: !!r.v_approved_at && !r.v_released_at,
                    icon: 'fa-paper-plane'
                },
                {
                    label: 'Voucher — Received',
                    sub: r.v_received_name || 'Awaiting receipt',
                    time: fdt(r.v_received_at),
                    done: !!r.v_received_at,
                    active: !!r.v_released_at && !r.v_received_at,
                    icon: 'fa-box-open'
                }
            ];
        }

        function renderSteps(steps) {
            const container = document.getElementById('tracker-steps');
            container.innerHTML = '';

            steps.forEach((step, i) => {
                const isLast = i === steps.length - 1;
                let dotBg, dotText, lineColor;

                if (step.rejected) {
                    dotBg = 'bg-red-100'; dotText = 'text-red-500'; lineColor = '#fecaca';
                } else if (step.done) {
                    dotBg = 'bg-green-100'; dotText = 'text-green-600'; lineColor = '#86efac';
                } else if (step.active) {
                    dotBg = 'bg-blue-100'; dotText = 'text-blue-500'; lineColor = '#bfdbfe';
                } else {
                    dotBg = 'bg-slate-100'; dotText = 'text-slate-400'; lineColor = '#e2e8f0';
                }

                const pulseClass = (step.active && !step.done) ? 'pulse-dot' : '';
                const labelColor = step.done ? 'text-slate-800' : step.rejected ? 'text-red-600' : 'text-slate-400';
                const subColor   = step.done ? 'text-slate-500' : 'text-slate-300';

                const el = document.createElement('div');
                el.className = 'flex gap-3';
                el.innerHTML = `
                    <div class="flex flex-col items-center">
                        <div class="step-dot ${dotBg} ${dotText} ${pulseClass}">
                            <i class="fas ${step.icon}"></i>
                        </div>
                        ${!isLast ? `<div class="w-0.5 flex-1 mt-1 mb-1 min-h-[24px]" style="background:${lineColor}"></div>` : ''}
                    </div>
                    <div class="pb-5 flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 flex-wrap">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm ${labelColor} leading-tight">${step.label}</p>
                                <p class="text-xs ${subColor} mt-0.5 truncate">${step.sub}</p>
                            </div>
                            ${step.time ? `<span class="text-[10px] text-slate-400 whitespace-nowrap shrink-0 mt-0.5">${step.time}</span>` : ''}
                        </div>
                    </div>
                `;
                container.appendChild(el);
            });
        }

        function closeTracker() {
            const modal = document.getElementById('trackerModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('trackerModal').addEventListener('click', function (e) {
            if (e.target === this) closeTracker();
        });

        // Search & filter — works on both table rows AND mobile cards
        function filterTable() {
            const search   = document.getElementById('searchInput').value.toLowerCase();
            const status   = document.getElementById('statusFilter').value;
            const category = document.getElementById('categoryFilter').value;

            document.querySelectorAll('.request-row').forEach(row => {
                const ok = (!search   || row.dataset.search.includes(search))
                        && (!status   || row.dataset.status === status)
                        && (!category || row.dataset.category === category);
                row.style.display = ok ? '' : 'none';
            });
        }

        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('categoryFilter').addEventListener('change', filterTable);
    </script>

</body>
</html>