<?php
// index-accounting-crmdetail.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$acctListAjaxUrl = BASE_URL . '/accountingcrmlistajax';
$acctListUrl = BASE_URL . '/crmaccounting';
$acctPaymentMethodsUrl = BASE_URL . '/accountingpaymentmethods';

$recordId = intval($_GET['id'] ?? 0);
if ($recordId <= 0) {
    header('Location: ' . $acctListUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting — Submission Details</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8 overflow-x-hidden">

        <div class="acct-detail-doc max-w-3xl mx-auto">

            <!-- Back link -->
            <a href="<?= htmlspecialchars($acctListUrl) ?>"
                class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-gray-500 hover:text-amber-700 mb-4 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Back to list
            </a>

            <!-- Document Card -->
            <div id="acctDetailCard" class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">

                <!-- Document Header / Letterhead -->
                <div class="px-8 pt-7 pb-5 bg-gradient-to-b from-amber-50/70 to-white">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <img src="<?= BASE_URL ?>/icon/logo.png" class="h-12 w-12 object-contain drop-shadow-sm" alt="logo">
                            <div>
                                <p class="text-amber-700 text-[11px] font-bold tracking-[0.15em] uppercase mb-1">
                                    Approved 2D &amp; Quotation
                                </p>
                                <h1 class="acct-detail-serif text-gray-900 text-[27px] leading-tight font-bold">
                                    Submission Details
                                </h1>
                            </div>
                        </div>
                        <div class="text-right shrink-0 flex flex-col items-end gap-2">
                            <div class="bg-white/80 border border-amber-100 rounded-lg px-3.5 py-2">
                                <p class="text-[10px] text-amber-700/80 font-bold uppercase tracking-[0.15em] mb-1">Control No.</p>
                                <p id="acctDetailControlNo" class="text-gray-900 font-mono font-bold text-sm">—</p>
                            </div>
                            <div id="acctDetailDepositBadgeWrap"></div>
                        </div>
                    </div>
                </div>

                <!-- Thick divider -->
                <div class="h-[3px] bg-gray-900 mx-8"></div>

                <!-- Document Meta Row -->
                <div id="acctDetailMetaRow"
                    class="px-8 py-3.5 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-y-1.5 gap-x-6 text-[12.5px]">
                    <span class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4" /></svg>
                        Branch: <span id="acctDetailBranch" class="font-semibold text-gray-800">—</span>
                    </span>
                    <span class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Sales Staff: <span id="acctDetailSalesStaff" class="font-semibold text-gray-800">—</span>
                    </span>
                    <span class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-3.5 h-3.5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Approved On: <span id="acctDetailApprovedOn" class="font-semibold text-gray-800">—</span>
                    </span>
                </div>

                <!-- Body -->
                <div class="px-8 py-6">
                    <div id="acctDetailLoading" class="space-y-3 py-2">
                        <?php for ($i = 0; $i < 8; $i++): ?>
                            <div class="h-4 rounded bg-gray-100 animate-pulse"></div>
                        <?php endfor; ?>
                    </div>

                    <div id="acctDetailContent" class="hidden space-y-7">

                        <!-- Client & Project section -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-1 h-3.5 bg-amber-600 rounded-full"></span>
                                <p class="text-[11px] text-amber-700 font-bold tracking-[0.15em] uppercase">
                                    Client &amp; Project
                                </p>
                            </div>
                            <div id="acctDetailClientRows" class="bg-gray-50/70 border border-gray-100 rounded-xl px-4"></div>
                        </div>

                        <!-- Files section -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-1 h-3.5 bg-amber-600 rounded-full"></span>
                                <p class="text-[11px] text-amber-700 font-bold tracking-[0.15em] uppercase">
                                    Files
                                </p>
                            </div>
                            <div id="acctDetailFileRows" class="space-y-2.5"></div>
                        </div>

                        <!-- Deposit / Notice to Proceed section -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-1 h-3.5 bg-amber-600 rounded-full"></span>
                                    <p class="text-[11px] text-amber-700 font-bold tracking-[0.15em] uppercase">
                                        Deposit &amp; Notice to Proceed
                                    </p>
                                </div>
                                <a href="<?= htmlspecialchars($acctPaymentMethodsUrl) ?>" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-400 hover:text-amber-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Manage payment methods
                                </a>
                            </div>
                            <div id="acctDetailDepositSection" class="border border-gray-100 rounded-xl overflow-hidden"></div>
                        </div>

                    </div>
                </div>

                <!-- Document footer strip -->
                <div class="px-8 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-[11px] text-gray-500 italic">This record is maintained by the Accounting Department.</p>
                    <p id="acctDetailFooterControlNo" class="text-[11px] text-gray-400 font-mono font-semibold">—</p>
                </div>
            </div>
        </div>

        <!-- Toast container -->
        <div id="crmToastContainer"
            class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
        </div>

    </main>

    <style>
        .acct-detail-doc .acct-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid #E5E7EB;
            font-size: .8125rem;
        }

        .acct-detail-doc .acct-detail-row:last-child {
            border-bottom: none;
        }

        .acct-detail-doc .acct-detail-label {
            color: #6B7280;
            font-weight: 500;
            white-space: nowrap;
        }

        .acct-detail-doc .acct-detail-value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }
    </style>

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

        const ACCT_LIST_AJAX_URL = <?= json_encode($acctListAjaxUrl) ?>;
        const ACCT_RECORD_ID = <?= json_encode($recordId) ?>;
        const ACCT_DETAIL_POLL_INTERVAL_MS = 8000;

        let acctDetailPollTimer = null;
        let acctDetailLastSignature = '';
        let acctDetailPaymentMethods = null; // cached after first fetch
        let acctDetailDepositSubmitting = false;

        function acctDetailEscapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        // Falls back to '—' for any field the backend hasn't returned yet.
        function acctDetailField(r, key) {
            const val = r?.[key];
            return (val === undefined || val === null || val === '') ? null : val;
        }

        function acctDetailFormatDateTimeLong(value) {
            if (!value) return '—';
            const dt = new Date(String(value).replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;
            return dt.toLocaleString('en-PH', {
                year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            });
        }

        function acctDetailTimeAgo(value) {
            if (!value) return '—';
            const dt = new Date(String(value).replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;

            const diffSec = Math.floor((Date.now() - dt.getTime()) / 1000);
            if (diffSec < 0) return acctDetailFormatDateTimeLong(value);
            if (diffSec < 60) return 'Just now';
            const diffMin = Math.floor(diffSec / 60);
            if (diffMin < 60) return `${diffMin}m ago`;
            const diffHr = Math.floor(diffMin / 60);
            if (diffHr < 24) return `${diffHr}h ago`;
            const diffDay = Math.floor(diffHr / 24);
            if (diffDay < 7) return `${diffDay}d ago`;
            return acctDetailFormatDateTimeLong(value);
        }

        function acctDetailCurrency(value) {
            if (value === null || value === undefined || value === '') return '—';
            const num = Number(String(value).replace(/,/g, ''));
            if (isNaN(num)) return acctDetailEscapeHtml(value);
            return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function acctDetailRow(label, value) {
            return `
                <div class="acct-detail-row">
                    <span class="acct-detail-label">${label}</span>
                    <span class="acct-detail-value">${value}</span>
                </div>
            `;
        }

        // Small colored pill for a file's review status.
        function acctDetailReviewBadge(status) {
            const map = {
                'Approved': 'bg-green-100 text-green-800 border-green-300',
                'For Revision': 'bg-red-100 text-red-800 border-red-300',
                'Pending': 'bg-gray-100 text-gray-600 border-gray-300',
            };
            const cls = map[status] || map['Pending'];
            return `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border whitespace-nowrap ${cls}">
                        ${status === 'Approved' ? '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
                        ${acctDetailEscapeHtml(status || 'Pending')}
                    </span>`;
        }

        // PDF file icon used on each file card.
        function acctDetailPdfIcon(hasFile) {
            return `
                <div class="shrink-0 w-9 h-9 rounded-lg flex items-center justify-center ${hasFile ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-300'}">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            `;
        }

        // One file card: icon, name, "View PDF" link, uploader (name +
        // role), and the Approved/For Revision/Pending badge. Falls back to
        // "No file uploaded" when the path is empty (e.g. 3D not submitted).
        function acctDetailFileCard(label, path, uploaderName, uploaderRole, reviewStatus) {
            const link = path
                ? `<a href="${acctDetailEscapeHtml(path)}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-900 hover:underline font-semibold text-[13px]">
                        View PDF
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>`
                : `<span class="text-gray-400 text-[13px]">No file uploaded</span>`;

            const uploaderLine = path
                ? `<p class="text-[11.5px] text-gray-500 mt-0.5">${acctDetailEscapeHtml(uploaderName || '—')} · ${acctDetailEscapeHtml(uploaderRole || '—')}</p>`
                : '';

            return `
                <div class="flex items-center justify-between gap-3 border border-gray-200 rounded-xl px-4 py-3 bg-white hover:border-amber-200 hover:shadow-sm transition-all">
                    <div class="flex items-center gap-3 min-w-0">
                        ${acctDetailPdfIcon(!!path)}
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-gray-800">${acctDetailEscapeHtml(label)}</p>
                            ${link}
                            ${uploaderLine}
                        </div>
                    </div>
                    ${acctDetailReviewBadge(reviewStatus)}
                </div>
            `;
        }

        // ── Deposit / Notice to Proceed ──

        function acctDetailDepositBadge(status) {
            const isNtp = status === 'Notice to Proceed';
            const cls = isNtp
                ? 'bg-green-100 text-green-800 border-green-300'
                : 'bg-amber-100 text-amber-800 border-amber-300';
            return `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold border whitespace-nowrap ${cls}">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 ${isNtp ? 'bg-green-600' : 'bg-amber-600'}"></span>
                        ${isNtp ? 'Notice to Proceed' : 'Hold'}
                    </span>`;
        }

        async function acctDetailGetPaymentMethods() {
            if (acctDetailPaymentMethods) return acctDetailPaymentMethods;
            try {
                const res = await fetch(`${ACCT_LIST_AJAX_URL}?action=payment_methods`);
                const data = await res.json();
                acctDetailPaymentMethods = data.success ? data.items : [];
            } catch (e) {
                console.error('acctDetailGetPaymentMethods:', e);
                acctDetailPaymentMethods = [];
            }
            return acctDetailPaymentMethods;
        }

        // Renders the badge next to the Control No., and the section body:
        // upload form when still on Hold (and main status is fully
        // Approved), or a read-only summary once Notice to Proceed.
        async function acctDetailRenderDeposit(r) {
            document.getElementById('acctDetailDepositBadgeWrap').innerHTML = acctDetailDepositBadge(r.deposit_status);

            const section = document.getElementById('acctDetailDepositSection');

            if (r.deposit_status === 'Notice to Proceed') {
                const slipLink = r.deposit_slip_path
                    ? `<a href="${acctDetailEscapeHtml(r.deposit_slip_path)}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-900 hover:underline font-semibold text-[13px]">
                            View Deposit Slip
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>`
                    : `<span class="text-gray-400 text-[13px]">No file</span>`;

                section.innerHTML = `
                    <div class="bg-green-50/40 px-4 py-4 space-y-1">
                        ${acctDetailRow('Amount Deposited', acctDetailCurrency(r.deposit_amount))}
                        ${acctDetailRow('Payment Method', acctDetailEscapeHtml(r.deposit_payment_method_name || '—'))}
                        ${acctDetailRow('Deposit Slip', slipLink)}
                        ${acctDetailRow('Logged By', `${acctDetailEscapeHtml(r.deposit_uploader_name || '—')} · ${acctDetailEscapeHtml(r.deposit_uploaded_role || '—')}`)}
                        ${acctDetailRow('Logged On', acctDetailFormatDateTimeLong(r.deposit_uploaded_at))}
                    </div>
                `;
                return;
            }

            // Still on Hold. If the main review isn't fully Approved yet
            // (shouldn't normally happen on this page, but just in case),
            // show an informational note instead of the form.
            if (r.status !== 'Approved') {
                section.innerHTML = `
                    <div class="px-4 py-5 text-center text-gray-400 text-xs">
                        Deposit slip can be logged once all files are approved.
                    </div>
                `;
                return;
            }

            const methods = await acctDetailGetPaymentMethods();
            const optionsHtml = methods.length
                ? methods.map(m => `<option value="${m.id}">${acctDetailEscapeHtml(m.name)}</option>`).join('')
                : '';

            section.innerHTML = `
                <form id="acctDepositForm" class="px-4 py-4 space-y-3 bg-amber-50/30">
                    <p class="text-[12px] text-gray-500">Log the deposit to move this submission to <span class="font-semibold text-gray-700">Notice to Proceed</span> — Cutting List, Operations, and Design will be notified.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Amount Deposited</label>
                            <input type="number" step="0.01" min="0.01" required id="acctDepositAmount" placeholder="0.00"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-100 focus:border-amber-600 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Payment Method</label>
                            <select id="acctDepositMethod" required
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-100 focus:border-amber-600 bg-white">
                                <option value="" disabled selected>Select method</option>
                                ${optionsHtml}
                            </select>
                            ${methods.length === 0 ? `<p class="text-[10.5px] text-red-500 mt-1">No payment methods set up yet — <a href="<?= htmlspecialchars($acctPaymentMethodsUrl) ?>" target="_blank" class="underline">add one</a>.</p>` : ''}
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Deposit Slip (PDF, JPG, or PNG)</label>
                        <input type="file" required id="acctDepositSlip" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-gray-300 file:bg-white file:text-xs file:font-medium file:text-gray-600 hover:file:bg-gray-50">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" id="acctDepositSubmitBtn"
                            class="px-4 py-2 text-xs font-semibold text-white bg-amber-700 rounded-lg hover:bg-amber-800 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            Mark as Notice to Proceed
                        </button>
                    </div>
                </form>
            `;

            document.getElementById('acctDepositForm').addEventListener('submit', acctDetailSubmitDeposit);
        }

        async function acctDetailSubmitDeposit(e) {
            e.preventDefault();
            if (acctDetailDepositSubmitting) return;

            const amount = document.getElementById('acctDepositAmount').value;
            const methodId = document.getElementById('acctDepositMethod').value;
            const fileInput = document.getElementById('acctDepositSlip');
            const file = fileInput.files[0];

            if (!amount || Number(amount) <= 0) {
                crmShowToast('Please enter a valid deposit amount.', 'error');
                return;
            }
            if (!methodId) {
                crmShowToast('Please select a payment method.', 'error');
                return;
            }
            if (!file) {
                crmShowToast('Please attach the deposit slip.', 'error');
                return;
            }

            const btn = document.getElementById('acctDepositSubmitBtn');
            acctDetailDepositSubmitting = true;
            btn.disabled = true;
            btn.textContent = 'Saving…';

            const formData = new FormData();
            formData.append('action', 'upload_deposit');
            formData.append('id', ACCT_RECORD_ID);
            formData.append('deposit_amount', amount);
            formData.append('deposit_payment_method_id', methodId);
            formData.append('deposit_slip', file);

            try {
                const res = await fetch(ACCT_LIST_AJAX_URL, { method: 'POST', body: formData });
                const data = await res.json();

                if (!data.success) {
                    crmShowToast(data.message || 'Failed to save deposit details.', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Mark as Notice to Proceed';
                    acctDetailDepositSubmitting = false;
                    return;
                }

                crmShowToast(data.message || 'Marked as Notice to Proceed.');
                acctDetailLastSignature = ''; // force re-render on next load
                acctDetailDepositSubmitting = false;
                acctDetailLoad();
            } catch (e) {
                console.error('acctDetailSubmitDeposit:', e);
                crmShowToast('Connection error. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = 'Mark as Notice to Proceed';
                acctDetailDepositSubmitting = false;
            }
        }

        async function acctDetailLoad({ silent = false } = {}) {
            const loading = document.getElementById('acctDetailLoading');
            const content = document.getElementById('acctDetailContent');

            try {
                // Same ajax action the list's modal used to call — this also marks the
                // notification as read server-side.
                const res = await fetch(`${ACCT_LIST_AJAX_URL}?action=detail&id=${ACCT_RECORD_ID}`);
                const data = await res.json();

                if (!data.success) {
                    // Silent polls stay quiet on failure — the visible content already
                    // on screen is left as-is instead of being replaced with an error.
                    if (!silent) {
                        loading.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">${acctDetailEscapeHtml(data.message || 'Record not found.')}</p>`;
                        crmShowToast(data.message || 'Record not found.', 'error');
                    }
                    return;
                }

                const r = data.record;

                // Skip the re-render entirely if nothing meaningful changed since
                // the last poll — avoids flashing/re-flowing the page every 8s.
                // Don't skip while the deposit form is actively being filled in
                // (deposit_status still 'Hold' means nothing server-side changed
                // there, so a signature match is fine and won't wipe user input).
                const signature = JSON.stringify([
                    r.design_2d_review_status, r.quotation_review_status, r.design_3d_review_status,
                    r.design_2d_path, r.quotation_path, r.design_3d_path,
                    r.show_3d, r.status, r.reviewed_at,
                    r.deposit_status, r.deposit_slip_path,
                ]);
                if (silent && signature === acctDetailLastSignature) {
                    return;
                }
                acctDetailLastSignature = signature;

                // Header + meta row
                document.getElementById('acctDetailControlNo').textContent = r.control_no ?? '—';
                document.getElementById('acctDetailFooterControlNo').textContent = r.control_no ?? '—';
                document.getElementById('acctDetailBranch').textContent = acctDetailField(r, 'branch') ?? '—';
                document.getElementById('acctDetailSalesStaff').textContent = acctDetailField(r, 'sales_staff_name') ?? '—';
                document.getElementById('acctDetailApprovedOn').textContent = acctDetailFormatDateTimeLong(r.reviewed_at);

                // Client & Project rows
                const clientRows = [
                    acctDetailRow('Client', acctDetailEscapeHtml(r.client_name)),
                    acctDetailRow('Contact Number', acctDetailEscapeHtml(r.contact_number)),
                    acctDetailRow('Address', acctDetailEscapeHtml(acctDetailField(r, 'address') ?? '—')),
                    acctDetailRow('Project Type', acctDetailEscapeHtml(acctDetailField(r, 'project_type') ?? '—')),
                    acctDetailRow('Scope of Project', acctDetailEscapeHtml(acctDetailField(r, 'project_scope') ?? '—')),
                    acctDetailRow('Measuring Space', acctDetailEscapeHtml(acctDetailField(r, 'measuring_space') ?? '—')),
                    acctDetailRow('Measurement Date &amp; Time', acctDetailFormatDateTimeLong(acctDetailField(r, 'measurement_datetime'))),
                    acctDetailRow('Designer Assign', acctDetailEscapeHtml(acctDetailField(r, 'designer_name') ?? '—')),
                    acctDetailRow('Contract Amount', acctDetailCurrency(acctDetailField(r, 'contract_amount'))),
                ].join('');
                document.getElementById('acctDetailClientRows').innerHTML = clientRows;

                // File cards — 2D and Quotation always shown; 3D only when
                // relevant (bundled with this approval, or unlocked via the
                // sequential flow), same "show_3d" rule the backend computes.
                const fileCards = [
                    acctDetailFileCard('2D File', r.design_2d_path, r.design_2d_uploader_name, r.design_2d_uploaded_role, r.design_2d_review_status),
                    acctDetailFileCard('Quotation File', r.quotation_path, r.quotation_uploader_name, r.quotation_uploaded_role, r.quotation_review_status),
                    r.show_3d
                        ? acctDetailFileCard('3D File', r.design_3d_path, r.design_3d_uploader_name, r.design_3d_uploaded_role, r.design_3d_review_status)
                        : '',
                ].join('');
                document.getElementById('acctDetailFileRows').innerHTML = fileCards;

                // Deposit / Notice to Proceed
                await acctDetailRenderDeposit(r);

                loading.classList.add('hidden');
                content.classList.remove('hidden');

            } catch (e) {
                console.error('acctDetailLoad:', e);
                if (!silent) {
                    loading.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">Connection error. Please try again.</p>`;
                    crmShowToast('Connection error while loading the record.', 'error');
                }
            }
        }

        function acctDetailStartPolling() {
            if (acctDetailPollTimer) clearInterval(acctDetailPollTimer);
            acctDetailPollTimer = setInterval(() => acctDetailLoad({ silent: true }), ACCT_DETAIL_POLL_INTERVAL_MS);
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (acctDetailPollTimer) clearInterval(acctDetailPollTimer);
            } else {
                acctDetailLoad({ silent: true });
                acctDetailStartPolling();
            }
        });

        acctDetailLoad().then(acctDetailStartPolling);
    </script>
</body>

</html>