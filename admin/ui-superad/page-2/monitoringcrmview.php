<?php
// monitoringcrmview.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_SUPERADMIN];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$monAjaxUrl = BASE_URL . '/monitoringcrmajax';
$monListUrl = BASE_URL . '/monitoring';
$inquiryId = intval($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Monitoring — Tracking</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8 overflow-x-hidden">

        <div class="max-w-4xl mx-auto">

            <!-- Back link -->
            <a href="<?= htmlspecialchars($monListUrl) ?>"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-amber-700 transition-colors mb-4">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Monitoring
            </a>

            <!-- Header card -->
            <div id="monHeaderCard" class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-5">
                <div class="h-4 w-40 rounded bg-gray-100 animate-pulse mb-2"></div>
                <div class="h-6 w-64 rounded bg-gray-100 animate-pulse"></div>
            </div>

            <!-- Site Visit -->
            <div class="mb-3">
                <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase">Site Visit</p>
            </div>
            <div id="monSiteVisits" class="space-y-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <div class="h-3 w-full rounded bg-gray-100 animate-pulse mb-2"></div>
                    <div class="h-3 w-3/4 rounded bg-gray-100 animate-pulse"></div>
                </div>
            </div>

            <!-- Client Review & Approval (design progress) -->
            <div class="mb-3">
                <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase">Client Review &amp; Approval</p>
            </div>
            <div id="monDesignProgress" class="mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <div class="h-3 w-full rounded bg-gray-100 animate-pulse mb-2"></div>
                    <div class="h-3 w-3/4 rounded bg-gray-100 animate-pulse"></div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="mb-3">
                <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase">2D &amp; Quotation History</p>
            </div>
            <div id="monTimeline" class="space-y-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <div class="h-3 w-full rounded bg-gray-100 animate-pulse mb-2"></div>
                    <div class="h-3 w-3/4 rounded bg-gray-100 animate-pulse"></div>
                </div>
            </div>

        </div>

        <!-- Toast container -->
        <div id="crmToastContainer"
            class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
        </div>

    </main>

    <script>
        function crmShowToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('crmToastContainer');
            const palette = type === 'success'
                ? { wrap: 'bg-green-50 border-green-200 text-green-700', icon: 'bg-green-200 text-green-700', symbol: '✓' }
                : { wrap: 'bg-red-50 border-red-200 text-red-700', icon: 'bg-red-200 text-red-700', symbol: '!' };

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-2.5 border rounded-lg shadow-lg px-4 py-3 text-sm
                ${palette.wrap}
                translate-x-6 opacity-0 scale-95 transition-all duration-300 ease-out`;

            toast.innerHTML = `
                <span class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold ${palette.icon}">${palette.symbol}</span>
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

        const MON_AJAX_URL = <?= json_encode($monAjaxUrl) ?>;
        const MON_INQUIRY_ID = <?= json_encode($inquiryId) ?>;

        function monEscapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function monFormatDateTimeLong(value) {
            if (!value) return '—';
            const dt = new Date(value.replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;
            return dt.toLocaleString('en-PH', {
                year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            });
        }

        function monStageBadge(stageGroup, label) {
            const map = {
                'in_progress': 'bg-amber-50 text-amber-700 border-amber-200',
                'for_revision': 'bg-red-50 text-red-700 border-red-200',
                'completed': 'bg-green-50 text-green-700 border-green-200',
                'draft': 'bg-gray-50 text-gray-500 border-gray-200',
            };
            const dotMap = {
                'in_progress': 'bg-amber-500',
                'for_revision': 'bg-red-500',
                'completed': 'bg-green-500',
                'draft': 'bg-gray-400',
            };
            const cls = map[stageGroup] || map['draft'];
            const dot = dotMap[stageGroup] || dotMap['draft'];
            return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap ${cls}">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 ${dot}"></span>${monEscapeHtml(label)}
                    </span>`;
        }

        function monReviewBadge(status) {
            const map = {
                'Approved': 'bg-green-50 text-green-700 border-green-200',
                'For Revision': 'bg-red-50 text-red-700 border-red-200',
                'Pending': 'bg-gray-50 text-gray-500 border-gray-200',
            };
            const cls = map[status] || map['Pending'];
            return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border whitespace-nowrap ${cls}">${monEscapeHtml(status || 'Pending')}</span>`;
        }

        function monFileLine(label, path, uploaderName, uploaderRole, uploadedAt, reviewStatus, remarks) {
            const link = path
                ? `<a href="${monEscapeHtml(path)}" target="_blank" rel="noopener" class="text-amber-700 hover:underline font-medium">View File</a>`
                : `<span class="text-gray-300">Not uploaded</span>`;
            const meta = path
                ? `<span class="text-gray-400"> · ${monEscapeHtml(uploaderName)} (${monEscapeHtml(uploaderRole)}) · ${monFormatDateTimeLong(uploadedAt)}</span>`
                : '';
            const remarksHtml = remarks
                ? `<p class="text-xs text-red-700 mt-1">${monEscapeHtml(remarks)}</p>`
                : '';
            return `
                <div class="py-2 border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center justify-between gap-3 text-[13px]">
                        <span class="text-gray-500">${monEscapeHtml(label)}</span>
                        ${monReviewBadge(reviewStatus)}
                    </div>
                    <p class="text-xs mt-0.5">${link}${meta}</p>
                    ${remarksHtml}
                </div>
            `;
        }

        function monRenderCycle(cycle, index, total) {
            const showsThreeD = cycle.include_3d || cycle.design_3d_stage !== 'Locked';
            const cycleLabel = total > 1 ? `Cycle ${index + 1}` : 'Submission';

            return `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <p class="text-[10px] text-gray-400 font-semibold tracking-[0.1em] uppercase">${cycleLabel}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Submitted ${monFormatDateTimeLong(cycle.submitted_at)}</p>
                    </div>
                    <div class="px-5 py-1">
                        ${monFileLine('2D File', cycle.design_2d_path, cycle.design_2d_uploader_name, cycle.design_2d_uploaded_role, cycle.design_2d_uploaded_at, cycle.design_2d_review_status, cycle.design_2d_remarks)}
                        ${monFileLine('Quotation File', cycle.quotation_path, cycle.quotation_uploader_name, cycle.quotation_uploaded_role, cycle.quotation_uploaded_at, cycle.quotation_review_status, cycle.quotation_remarks)}
                        ${showsThreeD ? monFileLine('3D File', cycle.design_3d_path, cycle.design_3d_uploader_name, cycle.design_3d_uploaded_role, cycle.design_3d_uploaded_at, cycle.design_3d_review_status, cycle.design_3d_remarks) : ''}
                    </div>
                    ${cycle.reviewed_at ? `<div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100"><p class="text-[11px] text-gray-400">Last reviewed ${monFormatDateTimeLong(cycle.reviewed_at)}</p></div>` : ''}
                </div>
            `;
        }

        function monFormatMoney(value) {
            if (value === null || value === undefined || value === '') return '—';
            const num = Number(value);
            if (isNaN(num)) return '—';
            return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Same left-label / right-value row pattern as the intake form's
        // Review step (crm-main.php's .crm-confirm-row) — kept as plain
        // Tailwind here instead of a shared class since this page doesn't
        // load crm-main.php's stylesheet.
        function monDetailRow(label, value) {
            return `
                <div class="flex justify-between gap-3 py-2 border-b border-gray-100 text-[13px] last:border-b-0">
                    <span class="text-gray-400 whitespace-nowrap">${monEscapeHtml(label)}</span>
                    <span class="text-gray-800 font-medium text-right">${value || '—'}</span>
                </div>
            `;
        }

        function monRenderHeader(inquiry) {
            document.getElementById('monHeaderCard').innerHTML = `
                <div class="mb-4">
                    <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">Control No. ${monEscapeHtml(inquiry.control_no)}</p>
                    <h1 class="text-gray-900 text-xl font-semibold">${monEscapeHtml(inquiry.client_name)}</h1>
                    <p class="text-xs text-gray-400 mt-1">Filed ${monFormatDateTimeLong(inquiry.created_at)} ${inquiry.branch ? '· ' + monEscapeHtml(inquiry.branch) + ' Branch' : ''}</p>
                </div>
                <div class="pt-1 border-t border-gray-100">
                    ${monDetailRow('Address', monEscapeHtml(inquiry.address))}
                    ${monDetailRow('Contact Number', monEscapeHtml(inquiry.contact_number))}
                    ${monDetailRow('Type of Project', monEscapeHtml(inquiry.project_type))}
                    ${monDetailRow('Scope of Project', monEscapeHtml(inquiry.project_scope))}
                    ${monDetailRow('Measuring Space', monEscapeHtml(inquiry.measuring_space))}
                    ${monDetailRow('Measurement Date and Time', monFormatDateTimeLong(inquiry.measurement_datetime))}
                    ${monDetailRow('Sales Staff', monEscapeHtml(inquiry.sales_staff_name))}
                    ${monDetailRow('Designer Assign', monEscapeHtml(inquiry.designer_name))}
                    ${monDetailRow('Contract Amount', monFormatMoney(inquiry.contract_amount))}
                </div>
            `;
        }

        // ── Site Visit ──
        function monVisitedBadge(visited) {
            return visited
                ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap bg-green-50 text-green-700 border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Visited</span>`
                : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap bg-gray-50 text-gray-500 border-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Not Visited</span>`;
        }

        function monRenderSiteVisitCard(visit, label) {
            const photosHtml = visit.photos.length
                ? `<div class="grid grid-cols-6 sm:grid-cols-8 gap-2 mt-3">
                    ${visit.photos.map(p => `
                        <a href="${monEscapeHtml(p)}" target="_blank" rel="noopener" class="block aspect-square border border-gray-200 rounded overflow-hidden hover:border-amber-600 transition-colors">
                            <img src="${monEscapeHtml(p)}" class="w-full h-full object-cover">
                        </a>
                    `).join('')}
                   </div>`
                : `<p class="text-xs text-gray-400 mt-2">No photographs attached.</p>`;

            return `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <p class="text-[10px] text-gray-400 font-semibold tracking-[0.1em] uppercase">${label}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Logged ${monFormatDateTimeLong(visit.created_at)} · ${monEscapeHtml(visit.designer_name)}</p>
                        </div>
                        ${monVisitedBadge(visit.visited)}
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-[13px] text-gray-800">${monEscapeHtml(visit.address)}</p>
                        <p class="text-xs text-gray-400 mt-1">Visit date: ${monFormatDateTimeLong(visit.visit_datetime)}</p>
                        ${photosHtml}
                    </div>
                </div>
            `;
        }

        function monRenderSiteVisits(siteVisits) {
            const container = document.getElementById('monSiteVisits');
            if (!siteVisits || siteVisits.length === 0) {
                container.innerHTML = `
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <p class="text-sm text-gray-400">No site visit recorded yet for this inquiry.</p>
                    </div>
                `;
                return;
            }
            const ordered = [...siteVisits].reverse();
            const total = siteVisits.length;
            container.innerHTML = ordered
                .map((visit, i) => monRenderSiteVisitCard(visit, total > 1 ? `Visit ${total - i}` : 'Site Visit'))
                .join('');
        }

        // ── Client Review & Approval (design progress) ──
        function monRenderDesignProgress(dp) {
            const container = document.getElementById('monDesignProgress');
            const pct = Number(dp.progress || 0);

            const confirmedBlock = dp.confirmed
                ? `
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap bg-green-50 text-green-700 border-green-200">
                            <i class="fa-solid fa-circle-check"></i>Client Review & Approval
                        </span>
                        <p class="text-xs text-gray-400">${monFormatDateTimeLong(dp.confirmed_at)} · by ${monEscapeHtml(dp.confirmed_by_name)}</p>
                    </div>
                `
                : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap bg-amber-50 text-amber-700 border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Not Yet Confirmed
                   </span>`;

            container.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p class="text-[13px] text-gray-500">Design Progress</p>
                        <p class="text-sm font-semibold text-gray-800">${pct}%</p>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden mb-4">
                        <div class="h-full rounded-full bg-amber-600 transition-all" style="width:${pct}%"></div>
                    </div>
                    ${confirmedBlock}
                </div>
            `;
        }

        async function monLoadTimeline() {
            if (!MON_INQUIRY_ID || MON_INQUIRY_ID <= 0) {
                document.getElementById('monHeaderCard').innerHTML = `<p class="text-sm text-red-500">Invalid record.</p>`;
                document.getElementById('monSiteVisits').innerHTML = '';
                document.getElementById('monDesignProgress').innerHTML = '';
                document.getElementById('monTimeline').innerHTML = '';
                return;
            }

            try {
                const res = await fetch(`${MON_AJAX_URL}?action=timeline&id=${MON_INQUIRY_ID}`);
                const data = await res.json();

                if (!data.success) {
                    document.getElementById('monHeaderCard').innerHTML = `<p class="text-sm text-red-500">${monEscapeHtml(data.message || 'Record not found.')}</p>`;
                    document.getElementById('monSiteVisits').innerHTML = '';
                    document.getElementById('monDesignProgress').innerHTML = '';
                    document.getElementById('monTimeline').innerHTML = '';
                    return;
                }

                monRenderHeader(data.inquiry);
                monRenderSiteVisits(data.site_visits);
                monRenderDesignProgress(data.design_progress);

                const timelineEl = document.getElementById('monTimeline');
                if (data.cycles.length === 0) {
                    timelineEl.innerHTML = `
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                            <p class="text-sm text-gray-400">No 2D and Quotation submission yet for this inquiry.</p>
                        </div>
                    `;
                    return;
                }

                // Most recent cycle first, so the current status is what
                // greets you at the top of the page.
                const ordered = [...data.cycles].reverse();
                timelineEl.innerHTML = ordered
                    .map((cycle, i) => monRenderCycle(cycle, data.cycles.length - 1 - i, data.cycles.length))
                    .join('');

            } catch (e) {
                console.error('monLoadTimeline:', e);
                crmShowToast('Connection error while loading the record.', 'error');
            }
        }

        monLoadTimeline();
    </script>
</body>

</html>