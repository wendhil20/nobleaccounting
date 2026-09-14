<?php
// site.php

// Change this if your system uses a different route/path.
$crmDesignerAjaxUrl = BASE_URL . '/crmdesignerajax';

// URL to the Site Visit form — redirected here when "Proceed" is clicked
$crmSiteVisitUrl = BASE_URL . '/crmsitevisit';

// URL to the 2D & Quotation page — unlocked once the site visit is done
$crm2dQuotationUrl = BASE_URL . '/crm2dquotation';

?>

<div class="max-w-[1600px] mx-auto">

    <!-- Header -->
    <div class="mb-4">
        <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">CRM Management</p>
        <h1 class="text-gray-900 text-xl font-semibold">Assigned Inquiries</h1>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SINGLE MERGED TABLE (Pending + In Progress)
    ═══════════════════════════════════════════════════════════ -->
    <div class="flex items-center gap-2 mb-2">
        <span id="crmAllCount" class="text-[11px] text-gray-400"></span>
    </div>

    <!-- Search + Filter -->
    <div class="flex items-center gap-2 mb-2">
        <div class="relative flex-1 min-w-0 max-w-sm">
            <input id="crmSearch" type="text" placeholder="Search control no. / client / contact"
                class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:border-amber-600 bg-white">
            <svg class="absolute left-2 top-1.5 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
        </div>
        <select id="crmStatusFilter"
            class="shrink-0 pl-2.5 pr-7 py-1.5 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-amber-600 text-gray-600">
            <option value="">All statuses</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
        </select>
        <select id="crmTypeFilter"
            class="shrink-0 pl-2.5 pr-7 py-1.5 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-amber-600 text-gray-600">
            <option value="">All project types</option>
        </select>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr
                        class="bg-gray-50 border-b border-gray-200 text-left text-[10px] uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Control No.</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Client Name</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Contact No.</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Project Type</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Filed By</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Date Assigned</th>
                    </tr>
                </thead>
                <tbody id="crmDesignerTbody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-xs">
                            Loading assigned inquiries…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p id="crmDesignerCount" class="text-[11px] text-gray-400 mt-2.5"></p>
</div>

<!-- ═══════════════════════════════════════════════════════════
     RIGHT-SIDE DETAIL PANEL (slides in from the right, replaces the old modal)
═══════════════════════════════════════════════════════════ -->
<div id="crmDetailOverlay" class="fixed inset-0 bg-black/30 hidden z-40" onclick="crmCloseDetailPanel()"></div>

<div id="crmDetailPanel" class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col
           translate-x-full transition-transform duration-300 ease-out">

    <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between shrink-0">
        <div>
            <p class="text-[10px] text-amber-700 font-semibold tracking-[0.15em] uppercase mb-0.5">Inquiry Detail</p>
            <h3 id="crmDetailControlNo" class="text-gray-900 font-mono font-semibold text-sm">—</h3>
        </div>
        <button type="button" onclick="crmCloseDetailPanel()"
            class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
    </div>

    <div id="crmDetailBody" class="px-5 py-4 overflow-y-auto space-y-0.5 flex-1">
        <!-- Populated via JS -->
    </div>

    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
        <div class="mb-3" id="crmDetailStatusBadge"></div>
        <div class="flex flex-col gap-2">
            <button type="button" id="crmDetailSiteVisitBtn" onclick="crmProceedFromPanel()"
                class="w-full px-3.5 py-2 text-xs font-medium text-white bg-amber-700 rounded-lg hover:bg-amber-800 whitespace-nowrap">
                Proceed
            </button>
            <button type="button" id="crmDetail2dBtn" onclick="crm2dQuotationFromPanel()" disabled
                class="w-full px-3.5 py-2 text-xs font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed whitespace-nowrap">
                2D &amp; Quotation
            </button>
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

    const CRM_DESIGNER_AJAX_URL = <?= json_encode($crmDesignerAjaxUrl) ?>;
    const CRM_SITEVISIT_URL = <?= json_encode($crmSiteVisitUrl) ?>;
    const CRM_2D_QUOTATION_URL = <?= json_encode($crm2dQuotationUrl) ?>;
    const CRM_POLL_INTERVAL_MS = 8000;

    let crmAllRows = [];
    let crmDesignerLastSignature = '';
    let crmDesignerPollTimer = null;
    let crmDetailCurrentId = null;

    // Single search + status + project-type filter state (one table now)
    let crmSearchTerm = '';
    let crmStatusFilterValue = '';
    let crmTypeFilterValue = '';
    let crmSearchDebounce = null;

    const CRM_VIEWED_KEY = 'crmDesignerViewedIds';

    function crmGetViewedIds() {
        try {
            const raw = localStorage.getItem(CRM_VIEWED_KEY);
            return raw ? new Set(JSON.parse(raw)) : new Set();
        } catch (e) {
            return new Set();
        }
    }

    function crmMarkViewed(id) {
        const viewed = crmGetViewedIds();
        if (viewed.has(id)) return; // already marked, nothing to do
        viewed.add(id);
        try {
            localStorage.setItem(CRM_VIEWED_KEY, JSON.stringify([...viewed]));
        } catch (e) {
            console.error('crmMarkViewed:', e);
        }
    }

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
        return dt.toLocaleDateString('en-PH', {
            year: 'numeric', month: 'long', day: 'numeric'
        });
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

    function crmRowHtml(row, viewedIds) {
        const isViewed = viewedIds.has(row.id);
        const isActive = crmDetailCurrentId === row.id;


        let rowBgCls = '';
        if (isActive) rowBgCls = 'bg-amber-100';
        else if (!isViewed) rowBgCls = 'bg-amber-50/30';

        const firstCellAccent = isActive
            ? 'border-l-4 border-l-amber-600 pl-3'
            : 'border-l-4 border-l-transparent pl-3';

        const unreadDot = isViewed
            ? ''
            : `<span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0" title="Not yet viewed"></span>`;
        const viewedLabel = isViewed
            ? `<span class="text-[10px] text-gray-400 whitespace-nowrap">Viewed</span>`
            : '';

        return `
            <tr class="hover:bg-amber-50/40 transition-colors cursor-pointer ${rowBgCls}" data-row-id="${row.id}"
                onclick="crmOpenDetailPanel(${row.id})">
                <td class="pr-4 py-2.5 align-top ${firstCellAccent}">
                    <div class="flex items-center gap-1.5">
                        ${unreadDot}
                        <span class="font-mono text-[11px] font-semibold text-amber-700 whitespace-nowrap">
                            ${crmEscapeHtml(row.control_no)}
                        </span>
                    </div>
                    ${viewedLabel}
                </td>
                <td class="px-4 py-2.5 text-gray-800 align-top">${crmEscapeHtml(row.client_name)}</td>
                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap align-top">${crmEscapeHtml(row.contact_number)}</td>
                <td class="px-4 py-2.5 text-gray-600 align-top">${crmEscapeHtml(row.project_type) || '—'}</td>
                <td class="px-4 py-2.5 text-gray-600 align-top">${crmEscapeHtml(row.sales_name)}</td>
                <td class="px-4 py-2.5 align-top" data-status-cell>${crmStatusBadge(row.status)}</td>
                <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap align-top">${crmFormatDate(row.created_at)}</td>
            </tr>
        `;
    }

    function crmRenderEmptyRow(message) {
        return `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-xs">
                    ${message}
                </td>
            </tr>
        `;
    }

    // Applies search term + status filter + project-type filter to the full row set.
    function crmFilterRows(rows, searchTerm, statusValue, projectType) {
        let out = rows;
        if (statusValue) {
            out = out.filter(r => r.status === statusValue);
        }
        if (projectType) {
            out = out.filter(r => (r.project_type || '') === projectType);
        }
        if (searchTerm) {
            const t = searchTerm.toLowerCase();
            out = out.filter(r =>
                (r.control_no || '').toLowerCase().includes(t) ||
                (r.client_name || '').toLowerCase().includes(t) ||
                (r.contact_number || '').toLowerCase().includes(t)
            );
        }
        return out;
    }

    // Rebuilds the "project type" <select> from the full row set, keeping
    // the current selection if still valid.
    function crmPopulateProjectFilter(selectEl, rows) {
        const current = selectEl.value;
        const types = [...new Set(rows.map(r => r.project_type).filter(Boolean))].sort();
        const options = ['<option value="">All project types</option>']
            .concat(types.map(t => `<option value="${crmEscapeHtml(t)}">${crmEscapeHtml(t)}</option>`));
        selectEl.innerHTML = options.join('');
        if (types.includes(current)) selectEl.value = current;
    }

    function crmRenderRows() {
        const typeFilterSelect = document.getElementById('crmTypeFilter');
        crmPopulateProjectFilter(typeFilterSelect, crmAllRows);

        const filteredRows = crmFilterRows(crmAllRows, crmSearchTerm, crmStatusFilterValue, crmTypeFilterValue);
        const viewedIds = crmGetViewedIds();

        const tbody = document.getElementById('crmDesignerTbody');
        tbody.innerHTML = filteredRows.length
            ? filteredRows.map(row => crmRowHtml(row, viewedIds)).join('')
            : crmRenderEmptyRow(crmAllRows.length ? 'No matching inquiries.' : 'No inquiries assigned yet.');

        document.getElementById('crmAllCount').textContent =
            `${filteredRows.length} of ${crmAllRows.length} inquir${crmAllRows.length === 1 ? 'y' : 'ies'}`;

        document.getElementById('crmDesignerCount').textContent =
            `${crmAllRows.length} inquir${crmAllRows.length === 1 ? 'y' : 'ies'} assigned`;
    }

    async function crmFetchList({ silent = false } = {}) {
        try {
            const url = `${CRM_DESIGNER_AJAX_URL}?action=list`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                if (!silent) crmShowToast('Failed to load assigned inquiries.', 'error');
                return;
            }

            const signature = JSON.stringify(data.rows.map(r => r.id + ':' + r.status + ':' + r.mode));
            crmAllRows = data.rows;
            if (signature !== crmDesignerLastSignature) {
                crmRenderRows();
                crmDesignerLastSignature = signature;
            }

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

    // Single search box + status filter + project-type filter for the merged table.
    document.getElementById('crmSearch').addEventListener('input', function () {
        clearTimeout(crmSearchDebounce);
        const value = this.value;
        crmSearchDebounce = setTimeout(() => {
            crmSearchTerm = value.trim();
            crmRenderRows();
        }, 250);
    });
    document.getElementById('crmStatusFilter').addEventListener('change', function () {
        crmStatusFilterValue = this.value;
        crmRenderRows();
    });
    document.getElementById('crmTypeFilter').addEventListener('change', function () {
        crmTypeFilterValue = this.value;
        crmRenderRows();
    });

    crmFetchList().then(crmStartPolling);

    // ═══════════════════════════════════════════════════════════
    // PROCEED ACTION — redirect to the Site Visit form
    // ═══════════════════════════════════════════════════════════
    function crmProceed(id) {
        window.location.href = `${CRM_SITEVISIT_URL}?id=${id}`;
    }

    function crmProceedFromPanel() {
        if (crmDetailCurrentId) crmProceed(crmDetailCurrentId);
    }

    // ═══════════════════════════════════════════════════════════
    // 2D & QUOTATION ACTION — only reachable once site visit is done
    // (or immediately, for "ready_for_quotation" mode inquiries)
    // ═══════════════════════════════════════════════════════════
    function crm2dQuotation(id) {
        window.location.href = `${CRM_2D_QUOTATION_URL}?id=${id}`;
    }

    function crm2dQuotationFromPanel() {
        if (crmDetailCurrentId) crm2dQuotation(crmDetailCurrentId);
    }

    function crmDetailRowHighlight(label, value) {
        return `
        <div class="flex justify-between items-center gap-3 py-1 px-3 my-1 rounded-md
                    bg-slate-50 border border-slate-300 text-[13px]">
            <span class="text-slate-600 font-medium flex items-center gap-1.5">
                <i class="fa-regular fa-calendar"></i>
                ${label}
            </span>
            <span class="text-slate-900 font-semibold text-right">${value}</span>
        </div>
    `;
    }

    // ═══════════════════════════════════════════════════════════
    // RIGHT-SIDE DETAIL PANEL (replaces the old center modal)
    // ═══════════════════════════════════════════════════════════
    function crmDetailRow(label, value) {
        return `
            <div class="flex justify-between gap-3 py-2 border-b border-gray-100 text-[13px] last:border-b-0">
                <span class="text-gray-400 whitespace-nowrap">${label}</span>
                <span class="text-gray-800 font-medium text-right">${value}</span>
            </div>
        `;
    }

    async function crmOpenDetailPanel(id) {
        const overlay = document.getElementById('crmDetailOverlay');
        const panel = document.getElementById('crmDetailPanel');
        const body = document.getElementById('crmDetailBody');
        const siteVisitBtn = document.getElementById('crmDetailSiteVisitBtn');
        const quotationBtn = document.getElementById('crmDetail2dBtn');
        crmDetailCurrentId = id;

        // Mark viewed immediately and re-render the table right away so the
        // unread dot disappears and the active-row highlight shows up the
        // moment the panel opens.
        crmMarkViewed(id);
        crmRenderRows();

        document.getElementById('crmDetailControlNo').textContent = 'Loading…';
        document.getElementById('crmDetailStatusBadge').innerHTML = '';
        siteVisitBtn.textContent = 'Proceed';
        siteVisitBtn.style.display = '';
        quotationBtn.disabled = true;
        body.innerHTML = `<p class="text-sm text-gray-400 py-6 text-center">Fetching details…</p>`;

        overlay.classList.remove('hidden');
        requestAnimationFrame(() => panel.classList.remove('translate-x-full'));

        try {
            // NOTE: crmdesignerajax.php's `action=detail` response must also
            // include "mode" on the record object.
            const res = await fetch(`${CRM_DESIGNER_AJAX_URL}?action=detail&id=${id}`);
            const data = await res.json();

            if (!data.success) {
                body.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">${crmEscapeHtml(data.message || 'Record not found.')}</p>`;
                return;
            }

            const r = data.record;
            const isDone = r.status === 'In Progress';
            const isReadyForQuotation = r.mode === 'ready_for_quotation';

            document.getElementById('crmDetailControlNo').textContent = r.control_no;
            document.getElementById('crmDetailStatusBadge').innerHTML = crmStatusBadge(r.status);

            // Site Visit button is hidden entirely for "ready_for_quotation"
            // inquiries — there is no site visit step to view or proceed to.
            if (isReadyForQuotation) {
                siteVisitBtn.style.display = 'none';
            } else {
                siteVisitBtn.style.display = '';
                // Site Visit button stays enabled either way, so uploaded photos
                // and details remain viewable even after the visit is logged.
                siteVisitBtn.textContent = isDone ? 'View Site Visit' : 'Proceed';
            }

            // 2D & Quotation only unlocks once the site visit is completed
            // (or immediately for "ready_for_quotation", since those rows
            // are inserted directly with status = 'In Progress').
            quotationBtn.disabled = !isDone;

            body.innerHTML = [
                crmDetailRow('Client Name', crmEscapeHtml(r.client_name)),
                crmDetailRow('Address', crmEscapeHtml(r.address) || '—'),
                crmDetailRow('Contact Number', crmEscapeHtml(r.contact_number)),
                crmDetailRow('Mode', isReadyForQuotation ? 'Ready for Quotation (No Site Visit)' : 'Site Visit Needed'),
                crmDetailRow('Type of Project', crmEscapeHtml(r.project_type) || '—'),
                crmDetailRow('Scope of Project', crmEscapeHtml(r.project_scope) || '—'),
                crmDetailRow('Measuring Space', crmEscapeHtml(r.measuring_space) || '—'),
                crmDetailRow('Measurement Date &amp; Time', crmFormatDateTimeLong(r.measurement_datetime)),
                crmDetailRowHighlight('Target Completion Date', crmFormatDate(r.target_completion_date)),
                crmDetailRow('Contract Amount', crmFormatCurrency(r.contract_amount)),
                crmDetailRow('Branch', crmEscapeHtml(r.branch) || '—'),
                crmDetailRow('Filed By', crmEscapeHtml(r.sales_name)),
                crmDetailRow('Date Assigned', crmFormatDateTimeLong(r.created_at)),
            ].join('');

        } catch (e) {
            console.error('crmOpenDetailPanel:', e);
            body.innerHTML = `<p class="text-sm text-red-500 py-6 text-center">Connection error. Please try again.</p>`;
        }
    }

    function crmCloseDetailPanel() {
        const overlay = document.getElementById('crmDetailOverlay');
        const panel = document.getElementById('crmDetailPanel');
        panel.classList.add('translate-x-full');
        setTimeout(() => overlay.classList.add('hidden'), 300);
        crmDetailCurrentId = null;
        crmRenderRows();
    }
</script>