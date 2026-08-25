<?php
//site.php

// Change this if your system uses a different route/path.
$crmDesignerAjaxUrl = BASE_URL . '/crmdesignerajax';

// URL to the Site Visit form — redirected here when "Proceed" is clicked
$crmSiteVisitUrl = BASE_URL . '/crmsitevisit';

// URL to the 2D & Quotation page — unlocked once the site visit is done
$crm2dQuotationUrl = BASE_URL . '/crm2dquotation';

?>

<div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="mb-4">
        <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">CRM Management</p>
        <h1 class="text-gray-900 text-xl font-semibold">Assigned Inquiries</h1>

        <!-- Search -->
        <div class="relative mt-3 max-w-xs">
            <input id="crmDesignerSearch" type="text" placeholder="Search control no. / client / contact"
                class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:border-amber-600 bg-white">
            <svg class="absolute left-2 top-1.5 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-[10px] uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Control No.</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Client Name</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Contact No.</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Project Type</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Filed By</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Date Assigned</th>
                        <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody id="crmDesignerTbody" class="divide-y divide-gray-100">
                    <tr id="crmDesignerLoadingRow">
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-xs">
                            Loading assigned inquiries…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p id="crmDesignerCount" class="text-[11px] text-gray-400 mt-2.5"></p>
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
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3">
            <span id="crmDetailStatusBadge"></span>
            <div class="flex items-center gap-2">
                <button type="button" id="crmDetailSiteVisitBtn" onclick="crmProceedFromModal()"
                    class="px-3.5 py-1.5 text-xs font-medium text-white bg-amber-700 rounded-lg hover:bg-amber-800 whitespace-nowrap">
                    Proceed
                </button>
                <button type="button" id="crmDetail2dBtn" onclick="crm2dQuotationFromModal()" disabled
                    class="px-3.5 py-1.5 text-xs font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed whitespace-nowrap">
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

<!-- Toast container: bottom-right, Tailwind-only -->
<div id="crmToastContainer"
    class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
</div>

<script>
    // ═══════════════════════════════════════════════════════════
    // TOAST NOTIFICATIONS (bottom-right, Tailwind-only)
    // ═══════════════════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════════════════
    // REALTIME LIST (polling)
    // ═══════════════════════════════════════════════════════════
    const CRM_DESIGNER_AJAX_URL = <?= json_encode($crmDesignerAjaxUrl) ?>;
    const CRM_SITEVISIT_URL = <?= json_encode($crmSiteVisitUrl) ?>;
    const CRM_2D_QUOTATION_URL = <?= json_encode($crm2dQuotationUrl) ?>;
    const CRM_POLL_INTERVAL_MS = 8000;

    let crmDesignerSearchTerm = '';
    let crmDesignerLastSignature = '';
    let crmDesignerPollTimer = null;
    let crmDesignerSearchDebounce = null;
    let crmDetailCurrentId = null;

    function crmEscapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function crmFormatCurrency(value) {
        const num = Number(value);
        if (!value || isNaN(num)) return '—';
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

    function crmStatusBadge(status) {
        const isDone = status === 'In Progress';
        const cls = isDone
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-amber-50 text-amber-700 border-amber-200';
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap ${cls}">${crmEscapeHtml(status)}</span>`;
    }

    function crmActionCell(row) {
        const isDone = row.status === 'In Progress';

        // Site Visit button: always clickable so the designer can still view
        // (or continue filling out) what was uploaded, even after proceeding.
        const siteVisitBtn = `
            <button type="button" onclick="crmProceed(${row.id})"
                class="px-2.5 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap ${
                    isDone
                        ? 'text-amber-700 bg-white border border-amber-200 hover:bg-amber-50'
                        : 'text-white bg-amber-700 hover:bg-amber-800'
                }">
                ${isDone ? 'View Site Visit' : 'Proceed'}
            </button>
        `;

        // 2D & Quotation button: locked until the site visit has been completed
        const quotationBtn = isDone
            ? `<button type="button" onclick="crm2dQuotation(${row.id})"
                    class="px-2.5 py-1.5 text-[11px] font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 transition-colors whitespace-nowrap">
                    2D &amp; Quotation
               </button>`
            : `<button type="button" disabled title="Complete the site visit first"
                    class="px-2.5 py-1.5 text-[11px] font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed whitespace-nowrap">
                    2D &amp; Quotation
               </button>`;

        return `<div class="flex items-center justify-end gap-1.5">${siteVisitBtn}${quotationBtn}</div>`;
    }

    function crmRenderRows(rows) {
        const tbody = document.getElementById('crmDesignerTbody');

        if (rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-xs">
                        No inquiries assigned to you yet.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr class="hover:bg-amber-50/40 transition-colors" data-row-id="${row.id}">
                <td class="px-4 py-2.5">
                    <button type="button"
                        onclick="crmOpenDetailModal(${row.id})"
                        class="font-mono text-[11px] font-semibold text-amber-700 hover:text-amber-900 hover:underline underline-offset-2 whitespace-nowrap">
                        ${crmEscapeHtml(row.control_no)}
                    </button>
                </td>
                <td class="px-4 py-2.5 text-gray-800">${crmEscapeHtml(row.client_name)}</td>
                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">${crmEscapeHtml(row.contact_number)}</td>
                <td class="px-4 py-2.5 text-gray-600">${crmEscapeHtml(row.project_type) || '—'}</td>
                <td class="px-4 py-2.5 text-gray-600">${crmEscapeHtml(row.sales_name)}</td>
                <td class="px-4 py-2.5" data-status-cell>${crmStatusBadge(row.status)}</td>
                <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">${crmFormatDate(row.created_at)}</td>
                <td class="px-4 py-2.5 text-right" data-action-cell>${crmActionCell(row)}</td>
            </tr>
        `).join('');
    }

    async function crmFetchList({ silent = false } = {}) {
        try {
            const url = `${CRM_DESIGNER_AJAX_URL}?action=list&q=${encodeURIComponent(crmDesignerSearchTerm)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                if (!silent) crmShowToast('Failed to load assigned inquiries.', 'error');
                return;
            }

            const signature = JSON.stringify(data.rows.map(r => r.id + ':' + r.status));
            if (signature !== crmDesignerLastSignature) {
                crmRenderRows(data.rows);
                crmDesignerLastSignature = signature;
            }

            document.getElementById('crmDesignerCount').textContent =
                `${data.count} inquiry${data.count === 1 ? '' : 'ies'} assigned`;

        } catch (e) {
            console.error('crmFetchList:', e);
            if (!silent) crmShowToast('Connection error while fetching inquiries.', 'error');
        }
    }

    function crmStartPolling() {
        if (crmDesignerPollTimer) clearInterval(crmDesignerPollTimer);
        crmDesignerPollTimer = setInterval(() => crmFetchList({ silent: true }), CRM_POLL_INTERVAL_MS);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (crmDesignerPollTimer) clearInterval(crmDesignerPollTimer);
        } else {
            crmFetchList({ silent: true });
            crmStartPolling();
        }
    });

    document.getElementById('crmDesignerSearch').addEventListener('input', function () {
        clearTimeout(crmDesignerSearchDebounce);
        const value = this.value;
        crmDesignerSearchDebounce = setTimeout(() => {
            crmDesignerSearchTerm = value.trim();
            crmDesignerLastSignature = '';
            crmFetchList();
        }, 350);
    });

    crmFetchList().then(crmStartPolling);

    // ═══════════════════════════════════════════════════════════
    // PROCEED ACTION — redirect to the Site Visit form
    // ═══════════════════════════════════════════════════════════
    function crmProceed(id) {
        window.location.href = `${CRM_SITEVISIT_URL}?id=${id}`;
    }

    function crmProceedFromModal() {
        if (crmDetailCurrentId) crmProceed(crmDetailCurrentId);
    }

    // ═══════════════════════════════════════════════════════════
    // 2D & QUOTATION ACTION — only reachable once site visit is done
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
        const siteVisitBtn = document.getElementById('crmDetailSiteVisitBtn');
        const quotationBtn = document.getElementById('crmDetail2dBtn');
        crmDetailCurrentId = id;

        document.getElementById('crmDetailControlNo').textContent = 'Loading…';
        document.getElementById('crmDetailStatusBadge').innerHTML = '';
        siteVisitBtn.textContent = 'Proceed';
        quotationBtn.disabled = true;
        body.innerHTML = `<p class="text-sm text-gray-400 py-6 text-center">Fetching details…</p>`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        try {
            const res = await fetch(`${CRM_DESIGNER_AJAX_URL}?action=detail&id=${id}`);
            const data = await res.json();

            if (!data.success) {
                body.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">${crmEscapeHtml(data.message || 'Record not found.')}</p>`;
                return;
            }

            const r = data.record;
            const isDone = r.status === 'In Progress';

            document.getElementById('crmDetailControlNo').textContent = r.control_no;
            document.getElementById('crmDetailStatusBadge').innerHTML = crmStatusBadge(r.status);

            // Site Visit button stays enabled either way, so uploaded photos
            // and details remain viewable even after the visit is logged.
            siteVisitBtn.textContent = isDone ? 'View Site Visit' : 'Proceed';

            // 2D & Quotation only unlocks once the site visit is completed.
            quotationBtn.disabled = !isDone;

            body.innerHTML = [
                crmDetailRow('Client Name', crmEscapeHtml(r.client_name)),
                crmDetailRow('Address', crmEscapeHtml(r.address) || '—'),
                crmDetailRow('Contact Number', crmEscapeHtml(r.contact_number)),
                crmDetailRow('Type of Project', crmEscapeHtml(r.project_type) || '—'),
                crmDetailRow('Scope of Project', crmEscapeHtml(r.project_scope) || '—'),
                crmDetailRow('Measuring Space', crmEscapeHtml(r.measuring_space) || '—'),
                crmDetailRow('Measurement Date &amp; Time', crmFormatDateTimeLong(r.measurement_datetime)),
                crmDetailRow('Contract Amount', crmFormatCurrency(r.contract_amount)),
                crmDetailRow('Branch', crmEscapeHtml(r.branch) || '—'),
                crmDetailRow('Filed By', crmEscapeHtml(r.sales_name)),
                crmDetailRow('Date Assigned', crmFormatDateTimeLong(r.created_at)),
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
</script>