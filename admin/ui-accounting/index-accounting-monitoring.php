<?php
// index-accounting-monitoring.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Monitoring — Accounting</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
    <style>
        @media print {
            body > *:not(#modal-print-root) { display: none !important; }
            #modal-print-root {
                display: block !important;
                position: fixed; inset: 0;
                background: white; z-index: 99999;
                padding: 20px; overflow: visible;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>

<body class="bg-slate-100">

    <main class="ml-56 min-h-screen p-8">

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-sm font-bold text-gray-800">Project Monitoring</h1>
            <p class="text-[10px] text-gray-400 mt-0.5">Accounting overview — all projects</p>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-5 gap-4 mb-6">
            <?php
            $cards = [
                ['id' => 'm-total',    'label' => 'Total Projects'],
                ['id' => 'm-contract', 'label' => 'Total Contract Value'],
                ['id' => 'm-credited', 'label' => 'Total Credited'],
                ['id' => 'm-expense',  'label' => 'Total Expenses'],
                ['id' => 'm-income',   'label' => 'Possible Income / Loss'],
            ];
            foreach ($cards as $c): ?>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1"><?= $c['label'] ?></p>
                    <p class="text-lg font-bold text-gray-800" id="<?= $c['id'] ?>">—</p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filters + Search -->
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Status:</span>
                <?php
                $filters = ['all' => 'All', 'Ongoing' => 'Ongoing', 'Completed' => 'Completed', 'Pending' => 'Pending', 'Cancelled' => 'Cancelled'];
                foreach ($filters as $val => $label): ?>
                    <button
                        onclick="setFilter(this, '<?= $val ?>')"
                        class="pill-btn text-xs px-3 py-1.5 rounded-full border transition-all font-medium <?= $val === 'all' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-100' ?>">
                        <?= $label ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input id="search" type="text" placeholder="Search project or client…" oninput="applyFilters()"
                class="text-xs border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-orange-400 bg-white w-56 transition-all" />
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs" style="table-layout:fixed;">
                    <colgroup>
                        <col style="width:36px">
                        <col style="width:22%">
                        <col style="width:13%">
                        <col style="width:11%">
                        <col style="width:11%">
                        <col style="width:11%">
                        <col style="width:11%">
                        <col style="width:10%">
                        <col style="width:100px">
                    </colgroup>
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest text-gray-400 font-semibold">
                            <th class="px-3 py-3 text-center">#</th>
                            <th class="px-3 py-3 text-left">
                                Project
                                <button onclick="sortBy('project_name')" id="sort-name" class="sort-btn ml-1 text-gray-300 hover:text-gray-500"></button>
                            </th>
                            <th class="px-3 py-3 text-left">Client</th>
                            <th class="px-3 py-3 text-right">
                                Contract
                                <button onclick="sortBy('contract_amount')" id="sort-contract" class="sort-btn ml-1 text-gray-300 hover:text-gray-500"></button>
                            </th>
                            <th class="px-3 py-3 text-right">Credited</th>
                            <th class="px-3 py-3 text-right">Expenses</th>
                            <th class="px-3 py-3 text-right">Income / Loss</th>
                            <th class="px-3 py-3 text-right">Balance</th>
                            <th class="px-3 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="proj-tbody">
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                                <i class="fa-solid fa-spinner fa-spin mr-2 text-xs"></i> Loading projects…
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ═══════════════════════════ PROJECT DETAIL VIEW MODAL (view only) -->
    <div id="project-modal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/60 px-4 py-6 overflow-y-auto">
        <div class="bg-white w-full max-w-7xl rounded-2xl shadow-2xl flex flex-col" style="min-height:500px;">

            <!-- Modal Top Bar -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10 rounded-t-2xl no-print">
                <div class="flex items-center gap-3">
                    <button onclick="closeProjectModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                    </button>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800" id="modal-project-name">—</h2>
                        <p class="text-[10px] text-orange-500 font-mono" id="modal-reference-no"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.print()"
                        class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                        <i class="fa-solid fa-print text-xs"></i> Print
                    </button>
                    <a id="modal-export-btn" href="#"
                        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                        <i class="fa-solid fa-file-excel text-xs"></i> Export Excel
                    </a>
                    <button onclick="closeProjectModal()" class="text-gray-400 hover:text-red-500 ml-1">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div id="modal-body" class="p-6 flex-1">
                <div class="flex items-center justify-center py-20 text-gray-400 text-xs">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading…
                </div>
            </div>

        </div>
    </div>

    <script>
        let allProjects = [];
        let expenseMap  = {};
        let activeFilter = 'all';
        let sortKey = null;
        let sortDir = 'asc';
        let ACTIVE_PROJECT_ID      = null;
        let ACTIVE_CONTRACT_AMOUNT = 0;

        // ─── UTILS ───────────────────────────────────────────────────────────────────

        function peso(v) {
            return '₱ ' + Math.abs(parseFloat(v) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function badgeClass(status) {
            const map = {
                'ongoing':   'bg-green-100 text-green-700',
                'completed': 'bg-blue-100 text-blue-700',
                'pending':   'bg-amber-100 text-amber-700',
                'cancelled': 'bg-red-100 text-red-600',
            };
            return map[(status || '').toLowerCase()] || 'bg-gray-100 text-gray-500';
        }

        // ─── MAIN LIST ───────────────────────────────────────────────────────────────

        function setFilter(el, val) {
            document.querySelectorAll('.pill-btn').forEach(p => {
                p.className = 'pill-btn text-xs px-3 py-1.5 rounded-full border transition-all font-medium bg-white text-gray-500 border-gray-200 hover:bg-gray-100';
            });
            el.className = 'pill-btn text-xs px-3 py-1.5 rounded-full border transition-all font-medium bg-slate-800 text-white border-slate-800';
            activeFilter = val;
            applyFilters();
        }

        function sortBy(key) {
            if (sortKey === key) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else { sortKey = key; sortDir = 'asc'; }
            document.querySelectorAll('.sort-btn').forEach(b => b.textContent = '');
            const btn = document.getElementById(key === 'project_name' ? 'sort-name' : 'sort-contract');
            if (btn) btn.textContent = sortDir === 'asc' ? '↑' : '↓';
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('search').value.toLowerCase();
            let rows = allProjects.filter(p => {
                const matchStatus = activeFilter === 'all' || (p.status || '') === activeFilter;
                const matchQ = !q || (p.project_name || '').toLowerCase().includes(q) || (p.client_name || '').toLowerCase().includes(q);
                return matchStatus && matchQ;
            });
            if (sortKey) {
                rows = [...rows].sort((a, b) => {
                    let av = a[sortKey] || '', bv = b[sortKey] || '';
                    if (sortKey === 'contract_amount') { av = parseFloat(av) || 0; bv = parseFloat(bv) || 0; }
                    else { av = av.toString().toLowerCase(); bv = bv.toString().toLowerCase(); }
                    return sortDir === 'asc' ? (av > bv ? 1 : -1) : (av < bv ? 1 : -1);
                });
            }
            renderTable(rows);
            renderMetrics(rows);
        }

        function renderTable(rows) {
            const tbody = document.getElementById('proj-tbody');
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-10 text-center text-gray-400 text-xs">No projects match this filter.</td></tr>`;
                return;
            }
            tbody.innerHTML = rows.map((p, i) => {
                const contract   = parseFloat(p.contract_amount) || 0;
                const credited   = parseFloat(p.total_credited) || 0;
                const expense    = parseFloat(expenseMap[p.id] || 0);
                const incomeLoss = contract - expense;
                const balance    = contract - credited;
                const pct = contract > 0 ? Math.min(100, (credited / contract) * 100) : 0;
                const incomeColor = incomeLoss > 0 ? 'text-green-600' : incomeLoss < 0 ? 'text-red-500' : 'text-gray-400';
                const incomeSign  = incomeLoss < 0 ? '-' : '';

                return `<tr class="border-b border-gray-50 hover:bg-orange-50 cursor-pointer transition-colors"
                    onclick="openProjectModal(${p.id})">
                    <td class="px-3 py-3 text-center text-gray-300 font-mono">${i + 1}</td>
                    <td class="px-3 py-3">
                        <div class="font-semibold text-gray-800 truncate">${p.project_name || '—'}</div>
                        <div class="text-[10px] text-orange-500 font-mono mt-0.5">${p.reference_no || ''}</div>
                        <div class="w-full bg-gray-100 rounded-full h-1 mt-1.5">
                            <div class="bg-green-500 h-1 rounded-full" style="width:${pct.toFixed(1)}%"></div>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-gray-500 truncate">${p.client_name || '—'}</td>
                    <td class="px-3 py-3 text-right font-mono text-gray-700">${contract ? peso(contract) : '—'}</td>
                    <td class="px-3 py-3 text-right font-mono text-gray-700">${credited ? peso(credited) : '—'}</td>
                    <td class="px-3 py-3 text-right font-mono text-gray-700">${expense ? peso(expense) : '—'}</td>
                    <td class="px-3 py-3 text-right font-mono font-semibold ${incomeColor}">${(contract || expense) ? incomeSign + peso(incomeLoss) : '—'}</td>
                    <td class="px-3 py-3 text-right font-mono text-gray-700">${contract ? peso(balance) : '—'}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full ${badgeClass(p.status)}">${p.status || '—'}</span>
                    </td>
                </tr>`;
            }).join('');
        }

        function renderMetrics(rows) {
            let totalContract = 0, totalCredited = 0, totalExpense = 0;
            rows.forEach(p => {
                totalContract += parseFloat(p.contract_amount) || 0;
                totalCredited += parseFloat(p.total_credited) || 0;
                totalExpense  += parseFloat(expenseMap[p.id] || 0);
            });
            const incomeLoss = totalContract - totalExpense;
            document.getElementById('m-total').textContent    = rows.length;
            document.getElementById('m-contract').textContent = peso(totalContract);
            document.getElementById('m-credited').textContent = peso(totalCredited);
            document.getElementById('m-expense').textContent  = peso(totalExpense);
            const incEl = document.getElementById('m-income');
            incEl.textContent = (incomeLoss < 0 ? '-' : '') + peso(incomeLoss);
            incEl.className   = 'text-lg font-bold ' + (incomeLoss > 0 ? 'text-green-600' : incomeLoss < 0 ? 'text-red-500' : 'text-gray-800');
        }

        async function loadExpenses(projectIds) {
            await Promise.all(projectIds.map(async id => {
                try {
                    const r = await fetch(`${BASE_URL}/fetchprojectexpense?project_id=${id}`);
                    const data = await r.json();
                    expenseMap[id] = data.reduce((s, row) => s + (parseFloat(row.amount) || 0), 0);
                } catch { expenseMap[id] = 0; }
            }));
        }

        async function init() {
            try {
                const r    = await fetch(`${BASE_URL}/fetchprojects`);
                const data = await r.json();
                allProjects = data;
                await loadExpenses(data.map(p => p.id));
                applyFilters();
            } catch (e) {
                document.getElementById('proj-tbody').innerHTML =
                    `<tr><td colspan="9" class="px-4 py-10 text-center text-red-400 text-xs">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>Failed to load projects.
                    </td></tr>`;
            }
        }

        // ─── PROJECT DETAIL MODAL (VIEW ONLY) ────────────────────────────────────────

        async function openProjectModal(projectId) {
            ACTIVE_PROJECT_ID = projectId;
            const p = allProjects.find(x => x.id == projectId);
            if (!p) return;

            ACTIVE_CONTRACT_AMOUNT = parseFloat(p.contract_amount) || 0;
            document.getElementById('modal-project-name').textContent = p.project_name || '—';
            document.getElementById('modal-reference-no').textContent = p.reference_no || '';
            document.getElementById('modal-export-btn').href = `${BASE_URL}/exportprojectexcel?id=${projectId}`;
            document.getElementById('project-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Build basic info fields
            const ca = p.contract_amount
                ? '₱ ' + parseFloat(p.contract_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                : '';

            const fields = [
                ['Project Name',        p.project_name],
                ['Job Order',           p.job_order],
                ['Project Scope',       p.project_scope],
                ['Purchase Order',      p.purchase_order],
                ['Client Name',         p.client_name],
                ['Notice to Proceed',   p.notice_to_proceed],
                ['Contract Amount',     ca],
                ['(1) Billing Order #', p.billing_order_1],
                ['Sales Person',        p.sales_person],
                ['(2) Billing Order #', p.billing_order_2],
                ['Address',             p.address],
                ['Status',              p.status],
            ];

            const fieldsHtml = fields.map(([label, value]) => `
                <div style="display:flex; align-items:baseline; gap:6px;">
                    <span style="font-weight:700; text-transform:uppercase; font-size:9px; color:#374151; white-space:nowrap; min-width:110px;">${label} :</span>
                    <span style="border-bottom:1px solid #d1d5db; flex:1; padding-bottom:1px; font-size:11px;">${value || ''}</span>
                </div>`).join('');

            document.getElementById('modal-body').innerHTML = `

                <!-- Basic Information -->
                <div style="border-bottom:2px solid #e5e7eb; margin-bottom:20px; padding-bottom:14px;">
                    <div style="background:#f97316; padding:4px 14px; display:inline-block; margin-bottom:10px;">
                        <span style="font-size:9px; font-weight:700; color:white; text-transform:uppercase; letter-spacing:1px;">Basic Information</span>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 24px; padding:0 4px;">
                        ${fieldsHtml}
                    </div>
                </div>

                <!-- Section 1: Billing -->
                <div style="margin-bottom:20px; overflow-x:auto; overflow-y:auto; max-height:300px;">
                    <div style="background:#f97316; padding:3px 12px; display:inline-block; margin-bottom:6px;">
                        <span style="font-size:9px; font-weight:700; color:white; text-transform:uppercase; letter-spacing:1px;">1. Billed and Paid by Client / Owner</span>
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:10px;">
                        <thead>
                            <tr style="background:#374151; color:white;">
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:32px;">NO.</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:left;">PARTICULARS</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:right; width:110px;">AMOUNT</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:90px;">BANK / CHECK</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:90px;">PAYMENT DATE</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:90px;">REFERENCE</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:left; width:100px;">REMARKS</th>
                            </tr>
                        </thead>
                        <tbody id="billing-tbody">
                            <tr><td colspan="7" style="padding:16px; text-align:center; color:#9ca3af; font-size:11px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr style="background:#fef3c7;">
                                <td colspan="2" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-size:9px; text-transform:uppercase; letter-spacing:1px;">Total Amount Credited :</td>
                                <td id="billing-total" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-family:monospace; text-align:right;">₱ 0.00</td>
                                <td colspan="2" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-size:9px; text-transform:uppercase; text-align:right;">Total Balance :</td>
                                <td id="billing-balance" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-family:monospace; text-align:right;" colspan="2">₱ 0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Section 2: Expenses -->
                <div style="overflow-x:auto; overflow-y:auto; max-height:300px;">
                    <div style="background:#f97316; padding:3px 12px; display:inline-block; margin-bottom:6px;">
                        <span style="font-size:9px; font-weight:700; color:white; text-transform:uppercase; letter-spacing:1px;">2. Costs / Expenses</span>
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:10px;">
                        <thead>
                            <tr style="background:#374151; color:white;">
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:32px;">NO.</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:left; width:120px;">ACCOUNT TITLE</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:left;">PARTICULARS</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:right; width:110px;">AMOUNT</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:100px;">MODE OF PAYMENT</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:90px;">PAYMENT DATE</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:center; width:90px;">REFERENCE</th>
                                <th style="padding:5px 8px; border:1px solid #4b5563; text-align:left; width:100px;">REMARKS</th>
                            </tr>
                        </thead>
                        <tbody id="expense-tbody">
                            <tr><td colspan="8" style="padding:16px; text-align:center; color:#9ca3af; font-size:11px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr style="background:#fef3c7;">
                                <td colspan="3" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-size:9px; text-transform:uppercase; letter-spacing:1px;">Total Amount Paid :</td>
                                <td id="expense-total" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-family:monospace; text-align:right;" colspan="5">₱ 0.00</td>
                            </tr>
                            <tr style="background:#f0fdf4;">
                                <td colspan="3" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-size:9px; text-transform:uppercase; letter-spacing:1px;">Possible Income / Loss :</td>
                                <td id="income-loss" style="padding:5px 8px; border:1px solid #e5e7eb; font-weight:700; font-family:monospace; text-align:right;" colspan="5">₱ 0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="padding:10px 4px; font-size:8px; color:#9ca3af; text-align:right; border-top:1px solid #e5e7eb; margin-top:12px;">
                    Generated: ${new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' })} | ${p.reference_no || ''}
                </div>
            `;

            // Fetch data
            fetchBilling();
            fetchExpenses();
        }

        function closeProjectModal() {
            document.getElementById('project-modal').classList.add('hidden');
            document.body.style.overflow = '';
            ACTIVE_PROJECT_ID = null;
        }

        function fetchBilling() {
            fetch(`${BASE_URL}/fetchprojectbilling?project_id=${ACTIVE_PROJECT_ID}`)
                .then(r => r.json()).then(data => {
                    const tbody = document.getElementById('billing-tbody');
                    if (!tbody) return;
                    if (!data.length) {
                        tbody.innerHTML = `<tr><td colspan="7" style="padding:16px; text-align:center; color:#9ca3af; font-size:11px;">No billing entries yet.</td></tr>`;
                        document.getElementById('billing-total').textContent   = '₱ 0.00';
                        document.getElementById('billing-balance').textContent = '₱ 0.00';
                        return;
                    }
                    let total = 0;
                    tbody.innerHTML = data.map((row, i) => {
                        const amt = parseFloat(row.amount) || 0;
                        total += amt;
                        const bg = i % 2 === 1 ? '#f9fafb' : 'white';
                        return `<tr style="background:${bg};">
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center; color:#9ca3af;">${i + 1}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb;">${row.particulars ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:right; font-family:monospace;">₱ ${amt.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center;">${row.bank_check ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center;">${row.payment_date ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center;">${row.reference ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb;">${row.remarks ?? ''}</td>
                        </tr>`;
                    }).join('');
                    const balance = ACTIVE_CONTRACT_AMOUNT - total;
                    document.getElementById('billing-total').textContent   = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
                    document.getElementById('billing-balance').textContent = '₱ ' + balance.toLocaleString('en-PH', { minimumFractionDigits: 2 });
                });
        }

        function fetchExpenses() {
            fetch(`${BASE_URL}/fetchprojectexpense?project_id=${ACTIVE_PROJECT_ID}`)
                .then(r => r.json()).then(data => {
                    const tbody = document.getElementById('expense-tbody');
                    if (!tbody) return;
                    if (!data.length) {
                        tbody.innerHTML = `<tr><td colspan="8" style="padding:16px; text-align:center; color:#9ca3af; font-size:11px;">No expense entries yet.</td></tr>`;
                        document.getElementById('expense-total').textContent = '₱ 0.00';
                        document.getElementById('income-loss').textContent   = '₱ 0.00';
                        return;
                    }
                    let total = 0;
                    tbody.innerHTML = data.map((row, i) => {
                        const amt = parseFloat(row.amount) || 0;
                        total += amt;
                        const bg = i % 2 === 1 ? '#f9fafb' : 'white';
                        return `<tr style="background:${bg};">
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center; color:#9ca3af;">${i + 1}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb;">${row.title ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb;">${row.particulars ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:right; font-family:monospace;">₱ ${amt.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center;">${row.mode_of_payment ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center;">${row.payment_date ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center;">${row.reference ?? ''}</td>
                            <td style="padding:5px 8px; border:1px solid #e5e7eb;">${row.remarks ?? ''}</td>
                        </tr>`;
                    }).join('');
                    document.getElementById('expense-total').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
                    const incomeLoss   = ACTIVE_CONTRACT_AMOUNT - total;
                    const incomeLossEl = document.getElementById('income-loss');
                    if (incomeLoss < 0) {
                        incomeLossEl.style.color = '#dc2626';
                        incomeLossEl.textContent = '-₱ ' + Math.abs(incomeLoss).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                    } else {
                        incomeLossEl.style.color = '#16a34a';
                        incomeLossEl.textContent = '₱ ' + incomeLoss.toLocaleString('en-PH', { minimumFractionDigits: 2 });
                    }
                });
        }

        // Close on backdrop click
        document.getElementById('project-modal').addEventListener('click', function(e) {
            if (e.target === this) closeProjectModal();
        });

        // Escape to close
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeProjectModal();
        });

        init();
    </script>

</body>
</html>