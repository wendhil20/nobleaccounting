<?php
//crmlist.php
$crmListAjaxUrl = BASE_URL . '/crmlistajax';
$crm2dQuotationUrl = BASE_URL . '/crm2dquotation';
?>

<div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="mb-4">
        <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">CRM Management</p>
        <h1 class="text-gray-900 text-xl font-semibold">Sales &amp; Market List</h1>
    </div>

    <!-- Search -->
    <div class="relative w-full sm:w-64 mb-4">
        <input id="crmListSearch" type="text" placeholder="Search control no. / client / contact"
            class="w-full pl-8 pr-7 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-100 focus:border-amber-600 bg-white transition-colors">
        <svg class="absolute left-2 top-1.5 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
        <button type="button" id="crmListSearchClear"
            class="hidden absolute right-2 top-1.5 text-gray-300 hover:text-gray-500 text-base leading-none w-4 h-4">&times;</button>
    </div>

    <!-- Table Card (desktop / tablet) -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-[10px] uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-2.5 font-semibold select-none whitespace-nowrap">
                            <button type="button" class="crm-sort-th flex items-center gap-1 hover:text-gray-700" data-key="control_no">
                                Control No. <span class="crm-sort-arrow text-gray-300">↕</span>
                            </button>
                        </th>
                        <th class="px-4 py-2.5 font-semibold select-none whitespace-nowrap">
                            <button type="button" class="crm-sort-th flex items-center gap-1 hover:text-gray-700" data-key="client_name">
                                Client <span class="crm-sort-arrow text-gray-300">↕</span>
                            </button>
                        </th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Contact No.</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Project</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Designer</th>
                        <th class="px-4 py-2.5 font-semibold text-right select-none whitespace-nowrap">
                            <button type="button" class="crm-sort-th flex items-center gap-1 hover:text-gray-700 ml-auto" data-key="contract_amount">
                                Contract Amount <span class="crm-sort-arrow text-gray-300">↕</span>
                            </button>
                        </th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-2.5 font-semibold select-none whitespace-nowrap">
                            <button type="button" class="crm-sort-th flex items-center gap-1 hover:text-gray-700" data-key="created_at">
                                Date Filed <span class="crm-sort-arrow text-gray-300">↕</span>
                            </button>
                        </th>
                        <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody id="crmListTbody" class="divide-y divide-gray-100">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Card list (mobile) -->
    <div id="crmListCards" class="md:hidden space-y-2.5"></div>

    <p id="crmListCount" class="text-[11px] text-gray-400 mt-2.5"></p>
</div>

<!-- Detail Modal -->
<div id="crmDetailModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg overflow-hidden max-h-[85vh] flex flex-col">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-start justify-between">
            <div>
                <p class="text-[10px] text-amber-700 font-semibold tracking-[0.15em] uppercase mb-0.5">Inquiry Detail</p>
                <h3 id="crmDetailControlNo" class="text-gray-900 font-mono font-semibold text-sm">—</h3>
            </div>
            <button type="button" onclick="crmCloseDetailModal()"
                class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div id="crmDetailBody" class="px-5 py-3.5 overflow-y-auto space-y-0.5">
            <!-- Populated via JS -->
        </div>
        <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3">
            <span id="crmDetailStatusBadge"></span>
            <div class="flex items-center gap-2">
                <button type="button" id="crmDetail2dBtn" onclick="crm2dQuotationFromModal()" disabled
                    class="px-3.5 py-1.5 text-xs font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors">
                    2D &amp; Quotation
                </button>
                <button type="button" onclick="crmCloseDetailModal()"
                    class="px-3.5 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div id="crmToastContainer"
    class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
</div>

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

    const CRM_LIST_AJAX_URL = <?= json_encode($crmListAjaxUrl) ?>;
    const CRM_2D_QUOTATION_URL = <?= json_encode($crm2dQuotationUrl) ?>;
    const CRM_POLL_INTERVAL_MS = 8000;

    let crmListSearchTerm = '';
    let crmListLastSignature = '';
    let crmListPollTimer = null;
    let crmListSearchDebounce = null;
    let crmDetailCurrentId = null;
    let crmListRawRows = [];
    let crmSortKey = 'created_at';
    let crmSortDir = 'desc'; // 'asc' | 'desc'

    function crmEscapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function crmFormatCurrency(value, compact = false) {
        const num = Number(value);
        if (!value || isNaN(num)) return '—';
        if (compact) {
            return '₱' + num.toLocaleString('en-PH', { notation: 'compact', maximumFractionDigits: 1 });
        }
        return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function crmFormatDate(value) {
        if (!value) return '—';
        const dt = new Date(value.replace(' ', 'T'));
        if (isNaN(dt.getTime())) return value;
        return dt.toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function crmFormatDateTimeLong(value) {
        if (!value) return '—';
        const dt = new Date(value.replace(' ', 'T'));
        if (isNaN(dt.getTime())) return value;
        return dt.toLocaleString('en-PH', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: 'numeric', minute: '2-digit', hour12: true
        });
    }

    function crmInitials(name) {
        if (!name) return '?';
        const parts = name.trim().split(/\s+/).filter(Boolean);
        const initials = (parts[0]?.[0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '');
        return initials.toUpperCase() || '?';
    }

    function crmAvatarColor(name) {
        const palette = ['bg-amber-100 text-amber-700', 'bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700',
            'bg-rose-100 text-rose-700', 'bg-violet-100 text-violet-700', 'bg-cyan-100 text-cyan-700'];
        let hash = 0;
        for (const ch of (name || '')) hash = (hash * 31 + ch.charCodeAt(0)) % palette.length;
        return palette[Math.abs(hash) % palette.length];
    }

    function crmStatusBadge(status) {
        const isDone = status === 'In Progress';
        const cls = isDone
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-amber-50 text-amber-700 border-amber-200';
        const dot = isDone ? 'bg-blue-500' : 'bg-amber-500';
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap ${cls}">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0 ${dot}"></span>${crmEscapeHtml(status)}
                </span>`;
    }

    function crmActionButton(row, size = 'normal') {
        const isDone = row.status === 'In Progress';
        const sizeCls = size === 'small' ? 'px-3 py-1.5 text-xs w-full' : 'px-3 py-1.5 text-xs whitespace-nowrap';

        return isDone
            ? `<button type="button" onclick="crm2dQuotation(${row.id})"
                    class="${sizeCls} font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition-colors">
                    2D &amp; Quotation
               </button>`
            : `<button type="button" disabled title="Complete the site visit first"
                    class="${sizeCls} font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    2D &amp; Quotation
               </button>`;
    }

    function crmSkeletonRows(count = 5) {
        const tbody = document.getElementById('crmListTbody');
        tbody.innerHTML = Array.from({ length: count }).map(() => `
            <tr>
                ${Array.from({ length: 9 }).map(() => `
                    <td class="px-4 py-3"><div class="h-3 rounded bg-gray-100 animate-pulse"></div></td>
                `).join('')}
            </tr>
        `).join('');

        document.getElementById('crmListCards').innerHTML = Array.from({ length: 3 }).map(() => `
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 space-y-2">
                <div class="h-3 w-1/3 rounded bg-gray-100 animate-pulse"></div>
                <div class="h-4 w-2/3 rounded bg-gray-100 animate-pulse"></div>
                <div class="h-3 w-1/2 rounded bg-gray-100 animate-pulse"></div>
            </div>
        `).join('');
    }

    function crmEmptyState() {
        const message = crmListSearchTerm
            ? `No inquiries match "${crmEscapeHtml(crmListSearchTerm)}".`
            : 'No inquiries yet.';
        return `
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v-6.75A2.25 2.25 0 0111.25 8.25h1.5A2.25 2.25 0 0115 10.5v6.75m-9 0h12a1.5 1.5 0 001.5-1.5V7.5a1.5 1.5 0 00-.44-1.06l-3-3A1.5 1.5 0 0015 3H9a1.5 1.5 0 00-1.06.44l-3 3A1.5 1.5 0 004.5 7.5v8.25a1.5 1.5 0 001.5 1.5z" />
                </svg>
                <p class="text-gray-400 text-xs">${message}</p>
            </div>
        `;
    }

    function crmSortedRows(rows) {
        const sorted = [...rows].sort((a, b) => {
            let av = a[crmSortKey], bv = b[crmSortKey];
            if (crmSortKey === 'contract_amount') { av = Number(av) || 0; bv = Number(bv) || 0; }
            else if (crmSortKey === 'created_at') { av = new Date((av || '').replace(' ', 'T')).getTime() || 0; bv = new Date((bv || '').replace(' ', 'T')).getTime() || 0; }
            else { av = (av || '').toString().toLowerCase(); bv = (bv || '').toString().toLowerCase(); }
            if (av < bv) return crmSortDir === 'asc' ? -1 : 1;
            if (av > bv) return crmSortDir === 'asc' ? 1 : -1;
            return 0;
        });
        return sorted;
    }

    function crmUpdateSortArrows() {
        document.querySelectorAll('.crm-sort-th').forEach(btn => {
            const arrow = btn.querySelector('.crm-sort-arrow');
            if (btn.dataset.key === crmSortKey) {
                arrow.textContent = crmSortDir === 'asc' ? '↑' : '↓';
                arrow.classList.remove('text-gray-300');
                arrow.classList.add('text-amber-600');
            } else {
                arrow.textContent = '↕';
                arrow.classList.add('text-gray-300');
                arrow.classList.remove('text-amber-600');
            }
        });
    }

    function crmRenderRows(rows) {
        const tbody = document.getElementById('crmListTbody');
        const cardsWrap = document.getElementById('crmListCards');

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-0">${crmEmptyState()}</td></tr>`;
            cardsWrap.innerHTML = crmEmptyState();
            return;
        }

        const sorted = crmSortedRows(rows);

        tbody.innerHTML = sorted.map(row => `
            <tr class="hover:bg-amber-50/40 transition-colors cursor-pointer" onclick="crmOpenDetailModal(${row.id})">
                <td class="px-4 py-2.5">
                    <span class="font-mono text-[11px] font-semibold text-amber-700 hover:underline underline-offset-2">
                        ${crmEscapeHtml(row.control_no)}
                    </span>
                </td>
                <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-800">${crmEscapeHtml(row.client_name)}</span>
                    </div>
                </td>
                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">${crmEscapeHtml(row.contact_number)}</td>
                <td class="px-4 py-2.5 text-gray-600">${crmEscapeHtml(row.project_type) || '—'}</td>
                <td class="px-4 py-2.5 text-gray-600">${crmEscapeHtml(row.designer_name)}</td>
                <td class="px-4 py-2.5 text-gray-800 text-right tabular-nums whitespace-nowrap">${crmFormatCurrency(row.contract_amount)}</td>
                <td class="px-4 py-2.5">${crmStatusBadge(row.status)}</td>
                <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">${crmFormatDate(row.created_at)}</td>
                <td class="px-4 py-2.5 text-right" onclick="event.stopPropagation()">${crmActionButton(row)}</td>
            </tr>
        `).join('');

        cardsWrap.innerHTML = sorted.map(row => `
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 active:bg-amber-50/40 transition-colors" onclick="crmOpenDetailModal(${row.id})">
                <div class="flex items-start justify-between gap-3 mb-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-semibold ${crmAvatarColor(row.client_name)}">${crmEscapeHtml(crmInitials(row.client_name))}</span>
                        <div class="min-w-0">
                            <p class="text-gray-900 font-medium truncate">${crmEscapeHtml(row.client_name)}</p>
                            <p class="font-mono text-[11px] text-amber-700">${crmEscapeHtml(row.control_no)}</p>
                        </div>
                    </div>
                    ${crmStatusBadge(row.status)}
                </div>
                <div class="grid grid-cols-2 gap-y-1.5 text-xs text-gray-500 mb-2.5">
                    <span>${crmEscapeHtml(row.contact_number)}</span>
                    <span class="text-right">${crmEscapeHtml(row.project_type) || '—'}</span>
                    <span>${crmEscapeHtml(row.designer_name)}</span>
                    <span class="text-right font-medium text-gray-700 tabular-nums">${crmFormatCurrency(row.contract_amount)}</span>
                </div>
                <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                    <span class="text-[11px] text-gray-400">${crmFormatDate(row.created_at)}</span>
                    <div onclick="event.stopPropagation()">${crmActionButton(row, 'small')}</div>
                </div>
            </div>
        `).join('');
    }

    async function crmFetchList({ silent = false } = {}) {
        if (!silent && crmListRawRows.length === 0) crmSkeletonRows();
        try {
            const url = `${CRM_LIST_AJAX_URL}?action=list&q=${encodeURIComponent(crmListSearchTerm)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                if (!silent) crmShowToast('Failed to load inquiries.', 'error');
                return;
            }

            const signature = JSON.stringify(data.rows.map(r => r.id + ':' + r.status)) + crmSortKey + crmSortDir;
            crmListRawRows = data.rows;
            if (signature !== crmListLastSignature) {
                crmRenderRows(data.rows);
                crmListLastSignature = signature;
            }

            document.getElementById('crmListCount').textContent =
                `${data.count} inquiry${data.count === 1 ? '' : 'ies'} found`;

        } catch (e) {
            console.error('crmFetchList:', e);
            if (!silent) crmShowToast('Connection error while fetching inquiries.', 'error');
        }
    }

    function crmStartPolling() {
        if (crmListPollTimer) clearInterval(crmListPollTimer);
        crmListPollTimer = setInterval(() => crmFetchList({ silent: true }), CRM_POLL_INTERVAL_MS);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (crmListPollTimer) clearInterval(crmListPollTimer);
        } else {
            crmFetchList({ silent: true });
            crmStartPolling();
        }
    });

    const crmSearchInput = document.getElementById('crmListSearch');
    const crmSearchClear = document.getElementById('crmListSearchClear');

    crmSearchInput.addEventListener('input', function () {
        crmSearchClear.classList.toggle('hidden', this.value.length === 0);
        clearTimeout(crmListSearchDebounce);
        const value = this.value;
        crmListSearchDebounce = setTimeout(() => {
            crmListSearchTerm = value.trim();
            crmListLastSignature = '';
            crmFetchList();
        }, 350);
    });

    crmSearchClear.addEventListener('click', () => {
        crmSearchInput.value = '';
        crmSearchClear.classList.add('hidden');
        crmListSearchTerm = '';
        crmListLastSignature = '';
        crmFetchList();
        crmSearchInput.focus();
    });

    document.querySelectorAll('.crm-sort-th').forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.key;
            if (crmSortKey === key) {
                crmSortDir = crmSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                crmSortKey = key;
                crmSortDir = 'asc';
            }
            crmUpdateSortArrows();
            crmListLastSignature = '';
            crmRenderRows(crmListRawRows);
        });
    });

    crmUpdateSortArrows();
    crmFetchList().then(crmStartPolling);

    // ═══════════════════════════════════════════════════════════
    // 2D & QUOTATION ACTION
    // ═══════════════════════════════════════════════════════════
    function crm2dQuotation(id) {
        window.location.href = `${CRM_2D_QUOTATION_URL}?id=${id}`;
    }

    function crm2dQuotationFromModal() {
        if (crmDetailCurrentId) crm2dQuotation(crmDetailCurrentId);
    }

    // ═══════════════════════════════════════════════════════════
    // DETAIL MODAL
    // ═══════════════════════════════════════════════════════════
    function crmDetailRow(label, value) {
        return `
            <div class="flex justify-between gap-3 py-2 border-b border-gray-100 text-[13px] last:border-b-0">
                <span class="text-gray-400 whitespace-nowrap">${label}</span>
                <span class="text-gray-800 font-medium text-right">${value}</span>
            </div>
        `;
    }

    async function crmOpenDetailModal(id) {
        const modal = document.getElementById('crmDetailModal');
        const body = document.getElementById('crmDetailBody');
        const quotationBtn = document.getElementById('crmDetail2dBtn');
        crmDetailCurrentId = id;

        document.getElementById('crmDetailControlNo').textContent = 'Loading…';
        document.getElementById('crmDetailStatusBadge').innerHTML = '';
        quotationBtn.disabled = true;
        body.innerHTML = `
            <div class="space-y-2 py-1">
                ${Array.from({ length: 6 }).map(() => `<div class="h-4 rounded bg-gray-100 animate-pulse"></div>`).join('')}
            </div>
        `;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        try {
            const res = await fetch(`${CRM_LIST_AJAX_URL}?action=detail&id=${id}`);
            const data = await res.json();

            if (!data.success) {
                body.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">${crmEscapeHtml(data.message || 'Record not found.')}</p>`;
                return;
            }

            const r = data.record;
            const isDone = r.status === 'In Progress';

            document.getElementById('crmDetailControlNo').textContent = r.control_no;
            document.getElementById('crmDetailStatusBadge').innerHTML = crmStatusBadge(r.status);
            quotationBtn.disabled = !isDone;

            body.innerHTML = [
                crmDetailRow('Client Name', crmEscapeHtml(r.client_name)),
                crmDetailRow('Address', crmEscapeHtml(r.address) || '—'),
                crmDetailRow('Contact Number', crmEscapeHtml(r.contact_number)),
                crmDetailRow('Type of Project', crmEscapeHtml(r.project_type) || '—'),
                crmDetailRow('Scope of Project', crmEscapeHtml(r.project_scope) || '—'),
                crmDetailRow('Measuring Space', crmEscapeHtml(r.measuring_space) || '—'),
                crmDetailRow('Measurement Date &amp; Time', crmFormatDateTimeLong(r.measurement_datetime)),
                crmDetailRow('Designer Assign', crmEscapeHtml(r.designer_name)),
                crmDetailRow('Contract Amount', crmFormatCurrency(r.contract_amount)),
                crmDetailRow('Branch', crmEscapeHtml(r.branch) || '—'),
                crmDetailRow('Filed By', crmEscapeHtml(r.sales_name)),
                crmDetailRow('Date Filed', crmFormatDateTimeLong(r.created_at)),
            ].join('');

        } catch (e) {
            console.error('crmOpenDetailModal:', e);
            body.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">Connection error. Please try again.</p>`;
        }
    }

    function crmCloseDetailModal() {
        const modal = document.getElementById('crmDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        crmDetailCurrentId = null;
    }

    document.getElementById('crmDetailModal').addEventListener('click', function (e) {
        if (e.target === this) crmCloseDetailModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') crmCloseDetailModal();
    });
</script>