<?php
// index-accounting-crmlist.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$acctListAjaxUrl = BASE_URL . '/accountingcrmlistajax';
$acctDetailUrl = BASE_URL . '/accountingcrmdetail';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting — Approved 2D & Quotation</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8 overflow-x-hidden">

        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">CRM Management</p>
                    <h1 class="text-gray-900 text-xl font-semibold">Approved 2D &amp; Quotation</h1>
                    <p class="text-gray-400 text-xs mt-0.5">Client submissions approved for accounting review.</p>
                </div>
                <a href="<?= BASE_URL ?>/accountingpaymentmethods"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-amber-700 transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Payment Methods
                </a>
            </div>

            <!-- Toolbar: search + tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div class="relative w-full sm:w-72 min-w-0">
                    <input id="acctListSearch" type="text" placeholder="Search control no. / client / contact / branch"
                        class="w-full pl-8 pr-7 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-100 focus:border-amber-600 bg-white transition-colors">
                    <svg class="absolute left-2 top-1.5 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <button type="button" id="acctListSearchClear"
                        class="hidden absolute right-2 top-1.5 text-gray-300 hover:text-gray-500 text-base leading-none w-4 h-4">&times;</button>
                </div>

                <div id="acctListTabs" class="flex items-center gap-1.5">
                    <button type="button" data-filter="unread"
                        class="acctList-tab px-3 py-1.5 text-[11px] font-semibold rounded-lg border transition-colors flex items-center gap-1.5">
                        New
                        <span id="acctListNewCount"
                            class="inline-flex items-center justify-center min-w-[16px] h-[16px] px-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">0</span>
                    </button>
                    <button type="button" data-filter="all"
                        class="acctList-tab px-3 py-1.5 text-[11px] font-semibold rounded-lg border transition-colors flex items-center gap-1.5">
                        All
                        <span id="acctListAllCount"
                            class="inline-flex items-center justify-center min-w-[16px] h-[16px] px-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">0</span>
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-left text-[10px] uppercase tracking-wide text-gray-500">
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap"></th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Control No.</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Client</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Branch</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Sales Staff</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Project Type</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Designer</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap text-right">Contract Amount</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Deposit</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Approved</th>
                                <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody id="acctListTbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

            <p id="acctListCount" class="text-[11px] text-gray-400 mt-2.5"></p>
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

        const ACCT_LIST_AJAX_URL = <?= json_encode($acctListAjaxUrl) ?>;
        const ACCT_DETAIL_URL = <?= json_encode($acctDetailUrl) ?>;
        const ACCT_LIST_POLL_INTERVAL_MS = 8000;

        let acctListSearchTerm = '';
        let acctListFilter = 'all';
        let acctListLastSignature = '';
        let acctListPollTimer = null;
        let acctListSearchDebounce = null;

        function acctListEscapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function acctListFormatDate(value) {
            if (!value) return '—';
            const dt = new Date(value.replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;
            return dt.toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function acctListFormatDateTimeLong(value) {
            if (!value) return '—';
            const dt = new Date(value.replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;
            return dt.toLocaleString('en-PH', {
                year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            });
        }

        // Relative "time ago" label — falls back to the short date once it's old.
        function acctListTimeAgo(value) {
            if (!value) return '—';
            const dt = new Date(value.replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;

            const diffSec = Math.floor((Date.now() - dt.getTime()) / 1000);
            if (diffSec < 0) return acctListFormatDate(value); // clock skew / future date, just show date
            if (diffSec < 60) return 'Just now';

            const diffMin = Math.floor(diffSec / 60);
            if (diffMin < 60) return `${diffMin}m ago`;

            const diffHr = Math.floor(diffMin / 60);
            if (diffHr < 24) return `${diffHr}h ago`;

            const diffDay = Math.floor(diffHr / 24);
            if (diffDay < 7) return `${diffDay}d ago`;

            return acctListFormatDate(value);
        }

        function acctListCurrency(value) {
            if (value === null || value === undefined || value === '') return '—';
            const num = Number(String(value).replace(/,/g, ''));
            if (isNaN(num)) return acctListEscapeHtml(value);
            return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function acctListText(value) {
            return (value === null || value === undefined || value === '')
                ? '<span class="text-gray-300">—</span>'
                : acctListEscapeHtml(value);
        }

        function acctListDepositBadge(status) {
            const isNtp = status === 'Notice to Proceed';
            const cls = isNtp
                ? 'bg-green-50 text-green-700 border-green-200'
                : 'bg-amber-50 text-amber-700 border-amber-200';
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold border whitespace-nowrap ${cls}">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 ${isNtp ? 'bg-green-600' : 'bg-amber-600'}"></span>
                        ${isNtp ? 'NTP' : 'Hold'}
                    </span>`;
        }

        function acctListNewDot(isRead) {
            if (isRead) return '';
            return `<span class="relative flex h-2 w-2 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>`;
        }

        // Navigates to the dedicated detail page for a given row id.
        function acctListGoToDetail(id) {
            window.location.href = `${ACCT_DETAIL_URL}?id=${id}`;
        }

        // ── Tabs ──
        function acctListInitTabs() {
            document.querySelectorAll('.acctList-tab').forEach(btn => {
                btn.addEventListener('click', () => {
                    acctListFilter = btn.dataset.filter;
                    acctListLastSignature = '';
                    acctListRenderTabs();
                    acctListFetchList();
                });
            });
            acctListRenderTabs();
        }

        function acctListRenderTabs() {
            document.querySelectorAll('.acctList-tab').forEach(btn => {
                const active = btn.dataset.filter === acctListFilter;
                btn.classList.toggle('bg-amber-700', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('border-amber-700', active);
                btn.classList.toggle('bg-white', !active);
                btn.classList.toggle('text-gray-600', !active);
                btn.classList.toggle('border-gray-300', !active);
                btn.classList.toggle('hover:bg-gray-50', !active);
            });
        }

        function acctListSkeletonRows(count = 6) {
            const tbody = document.getElementById('acctListTbody');
            tbody.innerHTML = Array.from({ length: count }).map(() => `
                <tr>
                    ${Array.from({ length: 11 }).map(() => `
                        <td class="px-4 py-3"><div class="h-3 rounded bg-gray-100 animate-pulse"></div></td>
                    `).join('')}
                </tr>
            `).join('');
        }

        function acctListEmptyState() {
            const message = acctListSearchTerm
                ? `No submissions match "${acctListEscapeHtml(acctListSearchTerm)}".`
                : (acctListFilter === 'unread' ? 'No new approvals.' : 'No approved submissions found.');
            return `
                <tr>
                    <td colspan="11" class="p-0">
                        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-gray-400 text-xs">${message}</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        function acctListRenderRows(rows) {
            const tbody = document.getElementById('acctListTbody');

            if (rows.length === 0) {
                tbody.innerHTML = acctListEmptyState();
                return;
            }

            tbody.innerHTML = rows.map(row => `
                <tr class="hover:bg-amber-50/40 transition-colors cursor-pointer" onclick="acctListGoToDetail(${row.id})">
                    <td class="px-4 py-2.5 w-5">${acctListNewDot(row.is_read)}</td>
                    <td class="px-4 py-2.5">
                        <span class="font-mono text-[11px] font-semibold text-amber-700">${acctListEscapeHtml(row.control_no)}</span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-800 font-medium">${acctListText(row.client_name)}</td>
                    <td class="px-4 py-2.5 text-gray-600">${acctListText(row.branch)}</td>
                    <td class="px-4 py-2.5 text-gray-600">${acctListText(row.sales_staff_name)}</td>
                    <td class="px-4 py-2.5">
                        ${row.project_type
                            ? `<span class="inline-block px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">${acctListEscapeHtml(row.project_type)}</span>`
                            : '<span class="text-gray-300">—</span>'}
                    </td>
                    <td class="px-4 py-2.5 text-gray-600">${acctListText(row.designer_name)}</td>
                    <td class="px-4 py-2.5 text-right text-gray-800 font-semibold whitespace-nowrap">${acctListCurrency(row.contract_amount)}</td>
                    <td class="px-4 py-2.5">${acctListDepositBadge(row.deposit_status)}</td>
                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap" title="${acctListEscapeHtml(acctListFormatDateTimeLong(row.reviewed_at))}">${acctListTimeAgo(row.reviewed_at)}</td>
                    <td class="px-4 py-2.5 text-right" onclick="event.stopPropagation()">
                        <button type="button" onclick="acctListGoToDetail(${row.id})"
                            class="px-2.5 py-1.5 text-[11px] font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            View
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        async function acctListFetchList({ silent = false } = {}) {
            if (!silent) acctListSkeletonRows();
            try {
                const url = `${ACCT_LIST_AJAX_URL}?action=list&q=${encodeURIComponent(acctListSearchTerm)}&filter=${encodeURIComponent(acctListFilter)}`;
                const res = await fetch(url);
                const data = await res.json();

                if (!data.success) {
                    if (!silent) crmShowToast('Failed to load submissions.', 'error');
                    return;
                }

                const signature = JSON.stringify(data.rows.map(r => r.id + ':' + r.is_read)) + acctListFilter;
                if (signature !== acctListLastSignature) {
                    acctListRenderRows(data.rows);
                    acctListLastSignature = signature;
                }

                document.getElementById('acctListCount').textContent =
                    `${data.count} submission${data.count === 1 ? '' : 's'} found`;

                if (acctListFilter === 'all') {
                    const allCountEl = document.getElementById('acctListAllCount');
                    if (allCountEl) allCountEl.textContent = data.count > 99 ? '99+' : data.count;
                }

                // "New" badge: when we're on the unread tab, data.count already IS the
                // unread count. When we're on "all", derive it from the rows we have.
                const unreadCount = acctListFilter === 'unread'
                    ? data.count
                    : data.rows.filter(r => !r.is_read).length;
                const newCountEl = document.getElementById('acctListNewCount');
                if (newCountEl) newCountEl.textContent = unreadCount > 99 ? '99+' : unreadCount;

            } catch (e) {
                console.error('acctListFetchList:', e);
                if (!silent) crmShowToast('Connection error while fetching submissions.', 'error');
            }
        }

        function acctListStartPolling() {
            if (acctListPollTimer) clearInterval(acctListPollTimer);
            acctListPollTimer = setInterval(() => acctListFetchList({ silent: true }), ACCT_LIST_POLL_INTERVAL_MS);
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (acctListPollTimer) clearInterval(acctListPollTimer);
            } else {
                acctListFetchList({ silent: true });
                acctListStartPolling();
            }
        });

        const acctListSearchInput = document.getElementById('acctListSearch');
        const acctListSearchClear = document.getElementById('acctListSearchClear');

        acctListSearchInput.addEventListener('input', function () {
            acctListSearchClear.classList.toggle('hidden', this.value.length === 0);
            clearTimeout(acctListSearchDebounce);
            const value = this.value;
            acctListSearchDebounce = setTimeout(() => {
                acctListSearchTerm = value.trim();
                acctListLastSignature = '';
                acctListFetchList();
            }, 350);
        });

        acctListSearchClear.addEventListener('click', () => {
            acctListSearchInput.value = '';
            acctListSearchClear.classList.add('hidden');
            acctListSearchTerm = '';
            acctListLastSignature = '';
            acctListFetchList();
            acctListSearchInput.focus();
        });

        acctListInitTabs();
        acctListFetchList().then(acctListStartPolling);
    </script>
</body>

</html>