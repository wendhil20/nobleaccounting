<?php
// index-projectmonitor-main.php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
$allowedPositions = [POSITION_CUSTODIAN];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Project Monitoring</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
    <style>
        /* ── Mode Selector Buttons ────────────────────────────── */
        .mode-btn {
            flex: 1;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 8px;
            cursor: pointer;
            background: white;
            transition: all .2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6b7280;
        }

        .mode-btn i {
            font-size: 18px;
        }

        .mode-btn:hover {
            border-color: #f97316;
            color: #f97316;
            background: #fff7ed;
        }

        .mode-btn.active-billing {
            border-color: #f97316;
            background: #fff7ed;
            color: #f97316;
        }

        .mode-btn.active-expense {
            border-color: #374151;
            background: #f9fafb;
            color: #374151;
        }

        .mode-btn.active-hybrid {
            border-color: #7c3aed;
            background: #f5f3ff;
            color: #7c3aed;
        }

        /* ── Progress Bar ─────────────────────────────────────── */
        .progress-track {
            width: 100%;
            height: 5px;
            background: #e5e7eb;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .6s ease;
        }

        .progress-fill.full {
            background: #16a34a;
        }

        /* 100% */
        .progress-fill.high {
            background: #22c55e;
        }

        /* 75–99% */
        .progress-fill.mid {
            background: #f97316;
        }

        /* 40–74% */
        .progress-fill.low {
            background: #ef4444;
        }

        /* 0–39% */

        /* ── Section Dividers in Modal ────────────────────────── */
        .section-tag {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .section-tag.billing {
            background: #f97316;
            color: white;
        }

        .section-tag.expense {
            background: #374151;
            color: white;
        }

        .section-tag.hybrid-b {
            background: #f97316;
            color: white;
        }

        .section-tag.hybrid-e {
            background: #7c3aed;
            color: white;
        }
    </style>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8">

        <!-- ── Page Header ─────────────────────────────────────────── -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Project Monitoring</h1>
                <p class="text-sm text-gray-400 mt-1">Accounting Report</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- NEW: Add Entry button -->
                <button onclick="openEntryModal()"
                    class="flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Add Entry
                </button>
                <!-- Existing: New Project button -->
                <button onclick="openAddModal()"
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                    <i class="fa-solid fa-plus"></i> New Project
                </button>
            </div>
        </div>

        <!-- ── Projects Table ──────────────────────────────────────── -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">All Projects</span>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="search-input" placeholder="Search..."
                            class="pl-8 pr-4 py-1.5 text-xs border border-gray-200 rounded-full outline-none focus:border-amber-400 w-48">
                    </div>
                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                            <th class="px-5 py-3 text-left">Ref No.</th>
                            <th class="px-5 py-3 text-left">Project Name</th>
                            <th class="px-5 py-3 text-left">Client</th>
                            <th class="px-5 py-3 text-left">Contract Amount / Payment</th>
                            <th class="px-5 py-3 text-left">Sales Person</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Date</th>
                            <th class="px-5 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="projects-tbody">
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-gray-400">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ════════════════════════════════════════════════════════════
     ADD / EDIT PROJECT MODAL
═════════════════════════════════════════════════════════════ -->
    <div id="add-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 overflow-y-auto">
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl my-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-sm uppercase tracking-widest text-gray-800">New Project</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="mb-4">
                    <div
                        class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded mb-3 inline-block">
                        Basic Information
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Project
                                Name</label>
                            <input id="f-project-name" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Job
                                Order</label>
                            <input id="f-job-order" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Project
                                Scope</label>
                            <input id="f-project-scope" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Purchase
                                Order</label>
                            <input id="f-purchase-order" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Client
                                Name</label>
                            <input id="f-client-name" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Notice
                                to Proceed</label>
                            <input id="f-notice-to-proceed" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Contract
                                Amount</label>
                            <input id="f-contract-amount" type="number" step="0.01"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">(1)
                                Billing Order #</label>
                            <input id="f-billing-order-1" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Sales
                                Person</label>
                            <input id="f-sales-person" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">(2)
                                Billing Order #</label>
                            <input id="f-billing-order-2" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div class="col-span-2">
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Address</label>
                            <input id="f-address" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Status</label>
                            <select id="f-status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                                <option value="">— Select —</option>
                                <option>Ongoing</option>
                                <option>Completed</option>
                                <option>On Hold</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                <button onclick="closeAddModal()"
                    class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded border border-gray-200">Cancel</button>
                <button onclick="saveProject()"
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Save Project
                </button>
            </div>
        </div>
    </div>


    <!-- ════════════════════════════════════════════════════════════
     ADD ENTRY MODAL  (Billing / Expense / Hybrid)
═════════════════════════════════════════════════════════════ -->
    <div id="entry-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 overflow-y-auto">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl my-8">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-sm uppercase tracking-widest text-gray-800">Add Entry</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">Select a project, choose a mode, then fill in the
                        details.</p>
                </div>
                <button onclick="closeEntryModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">

                <!-- Step 1: Project Selector -->
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5 block">
                        <span
                            class="inline-flex items-center justify-center w-4 h-4 bg-orange-500 text-white rounded-full text-[9px] font-black mr-1">1</span>
                        Select Project
                    </label>
                    <select id="entry-project-id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                        <option value="">— Choose a project —</option>
                    </select>
                </div>

                <!-- Step 2: Mode Selector -->
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5 block">
                        <span
                            class="inline-flex items-center justify-center w-4 h-4 bg-orange-500 text-white rounded-full text-[9px] font-black mr-1">2</span>
                        Choose Mode
                    </label>
                    <div class="flex gap-3">
                        <button type="button" class="mode-btn" id="mode-billing" onclick="setMode('billing')">
                            <i class="fa-solid fa-file-invoice-dollar text-orange-400"></i>
                            Billing
                        </button>
                        <button type="button" class="mode-btn" id="mode-expense" onclick="setMode('expense')">
                            <i class="fa-solid fa-receipt text-slate-500"></i>
                            Expense
                        </button>
                        <button type="button" class="mode-btn" id="mode-hybrid" onclick="setMode('hybrid')">
                            <i class="fa-solid fa-layer-group text-violet-500"></i>
                            Hybrid
                        </button>
                    </div>
                </div>

                <!-- Step 3: Fields (shown based on mode) -->
                <div id="entry-fields" class="hidden space-y-4">

                    <!-- ─── BILLING FIELDS ─────────────────────────────── -->
                    <div id="billing-fields" class="hidden">
                        <span class="section-tag billing">Billed &amp; Paid by Client</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Particulars</label>
                                <input id="b-particulars" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                    placeholder="e.g. Progress Billing #1">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Amount</label>
                                <input id="b-amount" type="number" step="0.01"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Bank
                                    / Check</label>
                                <input id="b-bank-check" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                    placeholder="e.g. BDO Check #123">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Payment
                                    Date</label>
                                <input id="b-payment-date" type="date"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Reference</label>
                                <input id="b-reference" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                    placeholder="Reference No.">
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Remarks</label>
                                <input id="b-remarks" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                    placeholder="Optional notes">
                            </div>
                        </div>
                    </div>

                    <!-- ─── EXPENSE FIELDS ─────────────────────────────── -->
                    <div id="expense-fields" class="hidden">
                        <span class="section-tag expense">Costs / Expenses</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Title</label>
                                <input id="e-title" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400"
                                    placeholder="e.g. Labor, Materials">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Mode
                                    of Payment</label>
                                <select id="e-mode"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400">
                                    <option value="">— Select —</option>
                                    <option>Cash</option>
                                    <option>Check</option>
                                    <option>Bank Transfer</option>
                                    <option>GCash</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Particulars</label>
                                <input id="e-particulars" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400"
                                    placeholder="e.g. Purchase of cement bags">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Amount</label>
                                <input id="e-amount" type="number" step="0.01"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Payment
                                    Date</label>
                                <input id="e-payment-date" type="date"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Reference</label>
                                <input id="e-reference" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400"
                                    placeholder="Reference No.">
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Remarks</label>
                                <input id="e-remarks" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400"
                                    placeholder="Optional notes">
                            </div>
                        </div>
                    </div>

                </div><!-- /entry-fields -->
            </div>

            <!-- Footer -->
            <div
                class="flex items-center justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                <p id="entry-mode-hint" class="text-[10px] text-gray-400 italic">Select a mode above to continue.</p>
                <div class="flex items-center gap-2">
                    <button onclick="closeEntryModal()"
                        class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded border border-gray-200">Cancel</button>
                    <button id="entry-save-btn" onclick="saveEntry()" disabled
                        class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> Save Entry
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ════════════════════════════════════════════════════════════
     SCRIPTS
═════════════════════════════════════════════════════════════ -->
    <script>
        let allProjects = [];
        let currentMode = null; // 'billing' | 'expense' | 'hybrid'

        // ── PROJECTS TABLE ─────────────────────────────────────────────────────────────

        function fetchProjects() {
            fetch('<?= BASE_URL ?>/fetchprojects')
                .then(res => res.json())
                .then(data => {
                    allProjects = data;
                    renderProjects(data);
                    populateProjectDropdown(data);
                });
        }

        function renderProjects(data) {
            const tbody = document.getElementById('projects-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No projects yet.</td></tr>`;
                return;
            }

            const statusColors = {
                'Ongoing': 'bg-blue-100 text-blue-700',
                'Completed': 'bg-green-100 text-green-700',
                'On Hold': 'bg-yellow-100 text-yellow-700',
                'Cancelled': 'bg-red-100 text-red-700',
            };

            tbody.innerHTML = data.map(row => {
                const statusCls = statusColors[row.status] ?? 'bg-gray-100 text-gray-500';
                const date = row.created_at
                    ? new Date(row.created_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                    : '—';
                const amount = row.contract_amount
                    ? '₱ ' + parseFloat(row.contract_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                    : '—';

                // ── Progress bar ──────────────────────────────────────
                const contract = parseFloat(row.contract_amount) || 0;
                const credited = parseFloat(row.total_credited) || 0;
                const pct = contract > 0 ? Math.min((credited / contract) * 100, 100) : 0;
                const pctDisplay = pct.toFixed(1);
                const fillClass = pct >= 100 ? 'full' : pct >= 75 ? 'high' : pct >= 40 ? 'mid' : 'low';
                const pctColor = pct >= 100 ? 'text-green-600' : pct >= 75 ? 'text-green-500' : pct >= 40 ? 'text-orange-500' : 'text-red-500';
                const creditedFmt = '₱ ' + credited.toLocaleString('en-PH', { minimumFractionDigits: 2 });
                const progressHTML = contract > 0 ? `
            <div class="text-[10px] text-gray-400 mt-1 flex items-center justify-between">
                <span>${creditedFmt} credited</span>
                <span class="font-bold ${pctColor}">${pctDisplay}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill ${fillClass}" style="width:${pct}%"></div>
            </div>` : '<span class="text-[10px] text-gray-300">No contract amount set</span>';

                return `
        <tr class="border-t border-gray-100 hover:bg-orange-50 transition-colors cursor-pointer"
            onclick="window.location='<?= BASE_URL ?>/projectdetail?id=${row.id}'">
            <td class="px-5 py-3 font-mono text-xs text-orange-500">${row.reference_no ?? '—'}</td>
            <td class="px-5 py-3 font-medium text-gray-800">${row.project_name}</td>
            <td class="px-5 py-3 text-gray-600">${row.client_name ?? '—'}</td>
            <td class="px-5 py-3 font-mono text-xs text-gray-700 min-w-[180px]">
                <div>${amount}</div>
                ${progressHTML}
            </td>
            <td class="px-5 py-3 text-gray-600">${row.sales_person ?? '—'}</td>
            <td class="px-5 py-3">
                <span class="${statusCls} text-[10px] font-semibold px-2 py-1 rounded-full uppercase">${row.status ?? '—'}</span>
            </td>
            <td class="px-5 py-3 text-xs text-gray-400">${date}</td>
            <td class="px-5 py-3" onclick="event.stopPropagation()">
                <button onclick="openAddModal(${row.id})"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                    <i class="fa-solid fa-pen mr-1"></i>Edit
                </button>
            </td>
        </tr>`;
            }).join('');
        }

        // ── NEW PROJECT MODAL ──────────────────────────────────────────────────────────

        function openAddModal(id = null) {
            ['project-name', 'job-order', 'project-scope', 'purchase-order', 'client-name',
                'notice-to-proceed', 'contract-amount', 'billing-order-1', 'sales-person',
                'billing-order-2', 'address', 'status'].forEach(f => {
                    document.getElementById('f-' + f).value = '';
                });

            if (id) {
                const row = allProjects.find(p => p.id == id);
                if (row) {
                    document.getElementById('f-project-name').value = row.project_name ?? '';
                    document.getElementById('f-job-order').value = row.job_order ?? '';
                    document.getElementById('f-project-scope').value = row.project_scope ?? '';
                    document.getElementById('f-purchase-order').value = row.purchase_order ?? '';
                    document.getElementById('f-client-name').value = row.client_name ?? '';
                    document.getElementById('f-notice-to-proceed').value = row.notice_to_proceed ?? '';
                    document.getElementById('f-contract-amount').value = row.contract_amount ?? '';
                    document.getElementById('f-billing-order-1').value = row.billing_order_1 ?? '';
                    document.getElementById('f-sales-person').value = row.sales_person ?? '';
                    document.getElementById('f-billing-order-2').value = row.billing_order_2 ?? '';
                    document.getElementById('f-address').value = row.address ?? '';
                    document.getElementById('f-status').value = row.status ?? '';
                }
            }

            document.getElementById('add-modal').dataset.editId = id ?? '';
            document.getElementById('add-modal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('add-modal').classList.add('hidden');
        }

        function saveProject() {
            const editId = document.getElementById('add-modal').dataset.editId;
            const payload = {
                id: editId ? parseInt(editId) : null,
                project_name: document.getElementById('f-project-name').value,
                job_order: document.getElementById('f-job-order').value,
                project_scope: document.getElementById('f-project-scope').value,
                purchase_order: document.getElementById('f-purchase-order').value,
                client_name: document.getElementById('f-client-name').value,
                notice_to_proceed: document.getElementById('f-notice-to-proceed').value,
                contract_amount: document.getElementById('f-contract-amount').value,
                billing_order_1: document.getElementById('f-billing-order-1').value,
                sales_person: document.getElementById('f-sales-person').value,
                billing_order_2: document.getElementById('f-billing-order-2').value,
                address: document.getElementById('f-address').value,
                status: document.getElementById('f-status').value,
            };

            if (!payload.project_name) { alert('Project Name is required.'); return; }

            fetch('<?= BASE_URL ?>/saveproject', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeAddModal();
                        fetchProjects();
                        showToast(editId ? 'Project updated!' : 'Project saved!');
                    } else {
                        alert('Failed: ' + (data.error ?? 'Unknown error'));
                    }
                });
        }

        // ── ADD ENTRY MODAL ────────────────────────────────────────────────────────────

        function populateProjectDropdown(projects) {
            const sel = document.getElementById('entry-project-id');
            // Keep placeholder
            sel.innerHTML = '<option value="">— Choose a project —</option>';
            projects.forEach(p => {
                const label = `${p.project_name}${p.reference_no ? '  [' + p.reference_no + ']' : ''}`;
                sel.innerHTML += `<option value="${p.id}">${label}</option>`;
            });
        }

        function openEntryModal() {
            // Reset
            document.getElementById('entry-project-id').value = '';
            resetMode();
            document.getElementById('entry-modal').classList.remove('hidden');
        }

        function closeEntryModal() {
            document.getElementById('entry-modal').classList.add('hidden');
            resetMode();
        }

        function resetMode() {
            currentMode = null;
            ['mode-billing', 'mode-expense', 'mode-hybrid'].forEach(id => {
                document.getElementById(id).className = 'mode-btn';
            });
            document.getElementById('entry-fields').classList.add('hidden');
            document.getElementById('billing-fields').classList.add('hidden');
            document.getElementById('expense-fields').classList.add('hidden');
            document.getElementById('entry-save-btn').disabled = true;
            document.getElementById('entry-mode-hint').textContent = 'Select a mode above to continue.';

            // Clear billing fields
            ['b-particulars', 'b-amount', 'b-bank-check', 'b-payment-date', 'b-reference', 'b-remarks'].forEach(id => {
                document.getElementById(id).value = '';
            });
            // Clear expense fields
            ['e-title', 'e-particulars', 'e-amount', 'e-mode', 'e-payment-date', 'e-reference', 'e-remarks'].forEach(id => {
                document.getElementById(id).value = '';
            });
        }

        function setMode(mode) {
            currentMode = mode;

            // Reset mode button styles
            document.getElementById('mode-billing').className = 'mode-btn';
            document.getElementById('mode-expense').className = 'mode-btn';
            document.getElementById('mode-hybrid').className = 'mode-btn';

            // Activate selected
            const modeClass = { billing: 'mode-btn active-billing', expense: 'mode-btn active-expense', hybrid: 'mode-btn active-hybrid' };
            document.getElementById('mode-' + mode).className = modeClass[mode];

            // Show/hide fields
            const billingFields = document.getElementById('billing-fields');
            const expenseFields = document.getElementById('expense-fields');
            const entryFields = document.getElementById('entry-fields');
            const hint = document.getElementById('entry-mode-hint');
            const saveBtn = document.getElementById('entry-save-btn');

            entryFields.classList.remove('hidden');
            billingFields.classList.add('hidden');
            expenseFields.classList.add('hidden');

            if (mode === 'billing') {
                billingFields.classList.remove('hidden');
                // Swap section tag color
                billingFields.querySelector('.section-tag').className = 'section-tag billing';
                hint.textContent = 'Billing mode — fill in the payment received from the client.';
            } else if (mode === 'expense') {
                expenseFields.classList.remove('hidden');
                expenseFields.querySelector('.section-tag').className = 'section-tag expense';
                hint.textContent = 'Expense mode — record a cost or payment made.';
            } else if (mode === 'hybrid') {
                billingFields.classList.remove('hidden');
                expenseFields.classList.remove('hidden');
                billingFields.querySelector('.section-tag').className = 'section-tag hybrid-b';
                expenseFields.querySelector('.section-tag').className = 'section-tag hybrid-e';
                hint.textContent = 'Hybrid mode — fill both a billing and an expense entry at once.';
            }

            saveBtn.disabled = false;
        }

        async function saveEntry() {
            const projectId = document.getElementById('entry-project-id').value;
            if (!projectId) { alert('Please select a project first.'); return; }
            if (!currentMode) { alert('Please choose a mode.'); return; }

            const saveBtn = document.getElementById('entry-save-btn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving...';

            const promises = [];

            // ── Billing payload ──────────────────────────────────────
            if (currentMode === 'billing' || currentMode === 'hybrid') {
                const bPayload = {
                    id: null,
                    project_id: parseInt(projectId),
                    particulars: document.getElementById('b-particulars').value,
                    amount: document.getElementById('b-amount').value,
                    bank_check: document.getElementById('b-bank-check').value,
                    payment_date: document.getElementById('b-payment-date').value,
                    reference: document.getElementById('b-reference').value,
                    remarks: document.getElementById('b-remarks').value,
                };
                promises.push(
                    fetch('<?= BASE_URL ?>/saveprojectbilling', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(bPayload)
                    }).then(r => r.json())
                );
            }

            // ── Expense payload ──────────────────────────────────────
            if (currentMode === 'expense' || currentMode === 'hybrid') {
                const ePayload = {
                    id: null,
                    project_id: parseInt(projectId),
                    title: document.getElementById('e-title').value,
                    particulars: document.getElementById('e-particulars').value,
                    amount: document.getElementById('e-amount').value,
                    mode_of_payment: document.getElementById('e-mode').value,
                    payment_date: document.getElementById('e-payment-date').value,
                    reference: document.getElementById('e-reference').value,
                    remarks: document.getElementById('e-remarks').value,
                };
                promises.push(
                    fetch('<?= BASE_URL ?>/saveprojectexpense', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(ePayload)
                    }).then(r => r.json())
                );
            }

            try {
                const results = await Promise.all(promises);
                const allOk = results.every(r => r.success);
                if (allOk) {
                    closeEntryModal();
                    showToast(currentMode === 'hybrid' ? 'Billing & Expense saved!' : (currentMode === 'billing' ? 'Billing saved!' : 'Expense saved!'));
                } else {
                    const err = results.find(r => !r.success);
                    alert('Save failed: ' + (err?.error ?? 'Unknown error'));
                }
            } catch (err) {
                alert('Network error: ' + err.message);
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk text-xs"></i> Save Entry';
            }
        }

        // ── UTILITIES ──────────────────────────────────────────────────────────────────

        function showToast(msg) {
            const t = document.createElement('div');
            t.className = 'fixed bottom-6 right-6 z-[999] bg-green-500 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg flex items-center gap-2 opacity-0 transition-all duration-300';
            t.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${msg}`;
            document.body.appendChild(t);
            requestAnimationFrame(() => t.classList.remove('opacity-0'));
            setTimeout(() => { t.classList.add('opacity-0'); setTimeout(() => t.remove(), 300); }, 3000);
        }

        document.getElementById('search-input').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            renderProjects(allProjects.filter(r =>
                r.project_name?.toLowerCase().includes(q) ||
                r.client_name?.toLowerCase().includes(q) ||
                r.sales_person?.toLowerCase().includes(q) ||
                r.status?.toLowerCase().includes(q)
            ));
        });

        // ── INIT ───────────────────────────────────────────────────────────────────────
        fetchProjects();
        setInterval(fetchProjects, 15000);
    </script>
</body>

</html>