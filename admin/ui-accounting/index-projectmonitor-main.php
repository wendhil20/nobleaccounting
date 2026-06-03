<?php
// index-projectmonitor-main.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
$allowedPositions = [POSITION_CUSTODIAN, POSITION_HEAD];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <main id="main-content" class="md:ml-56 pt-20 md:pt-5 min-h-screen p-4 md:p-8 transition-all duration-300">

        <div class="mb-6 flex items-center justify-between gap-2">
            <div>
                <h1 class="text-base font-bold text-gray-800">Project Monitoring</h1>
                <p class="text-xs text-gray-400 mt-0.5">Accounting Report</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="openEntryModal()"
                    class="flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-[10px] font-semibold px-3 py-1.5 rounded-lg transition-all">
                    <i class="fa-solid fa-file-invoice-dollar text-[10px]"></i>
                    <span class="hidden sm:inline">Add Entry</span>
                    <span class="sm:hidden">Entry</span>
                </button>
                <button onclick="openAddModal()"
                    class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-semibold px-3 py-1.5 rounded-lg transition-all">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span class="hidden sm:inline">New Project</span>
                    <span class="sm:hidden">New</span>
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

            <!-- Desktop Table (md+) -->
            <div class="hidden md:block overflow-x-auto">
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

            <!-- Mobile Cards (below md) -->
            <div class="md:hidden" id="projects-cards">
                <div class="px-4 py-8 text-center text-gray-400 text-sm">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                </div>
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
                            <div class="flex gap-2">
                                <select id="f-project-name"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                                    <option value="">— Select —</option>
                                </select>
                                <button type="button" onclick="openProjectNameManager()"
                                    class="shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 text-gray-400 hover:text-orange-500 transition"
                                    title="Manage project names">
                                    <i class="fa-solid fa-gear text-xs"></i>
                                </button>
                            </div>
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
                                <option>New</option>
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


    <div id="entry-modal"
        class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/50 px-2 py-4 md:px-4 md:py-8 overflow-y-auto">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl my-auto" id="entry-modal-box">

            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-sm uppercase tracking-widest text-gray-800">Add Entry</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">Select a project, choose a mode, then fill in the
                        details.</p>
                </div>
                <button onclick="closeEntryModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-4 py-4 space-y-4">

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
                    <div class="flex gap-2">
                        <button type="button" class="mode-btn" id="mode-billing" onclick="setMode('billing')">
                            <i class="fa-solid fa-file-invoice-dollar text-orange-400"></i>
                            Collection
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

                <!-- Step 3: Fields -->
                <div id="entry-fields" class="hidden space-y-4">
                    <!-- Hybrid = stacked on mobile, side by side on md+ -->
                    <div id="hybrid-grid" class="grid grid-cols-1 gap-4">

                        <!-- BILLING FIELDS -->
                        <div id="billing-fields" class="hidden">
                            <span class="section-tag billing">Billed &amp; Paid by Client</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                <div class="sm:col-span-2">
                                    <label
                                        class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Particulars</label>
                                    <input id="b-particulars" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                        placeholder="e.g. Progress Billing #1">
                                </div>
                                <div>
                                    <label
                                        class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Amount</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold">₱</span>
                                        <input id="b-amount" type="text" inputmode="decimal"
                                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm outline-none focus:border-orange-400 font-mono"
                                            placeholder="0.00" oninput="formatAmountInput(this)">
                                    </div>
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
                                <div class="sm:col-span-2">
                                    <label
                                        class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Remarks</label>
                                    <input id="b-remarks" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400"
                                        placeholder="Optional notes">
                                </div>
                            </div>
                        </div>

                        <!-- EXPENSE FIELDS -->
                        <div id="expense-fields" class="hidden">
                            <span class="section-tag expense">Costs / Expenses</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
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
                                <div class="sm:col-span-2">
                                    <label
                                        class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Particulars</label>
                                    <input id="e-particulars" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-slate-400"
                                        placeholder="e.g. Purchase of cement bags">
                                </div>
                                <div>
                                    <label
                                        class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Amount</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold">₱</span>
                                        <input id="e-amount" type="text" inputmode="decimal"
                                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm outline-none focus:border-slate-400 font-mono"
                                            placeholder="0.00" oninput="formatAmountInput(this)">
                                    </div>
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

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex items-center justify-between gap-3 px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                <p id="entry-mode-hint" class="text-[10px] text-gray-400 italic hidden sm:block">Select a mode above to
                    continue.</p>
                <div class="flex items-center gap-2 ml-auto">
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


    <!-- Project Name Manager Modal -->
    <div id="projectname-manager-modal"
        class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[80vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h3 class="font-bold text-sm uppercase tracking-widest text-gray-700">
                    <i class="fa-solid fa-gear mr-2 text-orange-500"></i>Manage Project Names
                </h3>
                <button onclick="closeProjectNameManager()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-4 py-3 border-b border-gray-100 flex gap-2 flex-shrink-0">
                <input type="text" id="new-projectname-input" placeholder="New project name..."
                    onkeydown="if(event.key==='Enter') addNewProjectName()"
                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition uppercase">
                <button onclick="addNewProjectName()"
                    class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add
                </button>
            </div>
            <div id="projectname-list" class="overflow-y-auto flex-1 px-2 py-2"></div>
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex-shrink-0 text-right">
                <button onclick="closeProjectNameManager()"
                    class="text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 px-5 py-2 rounded-lg transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <script>
        let allProjects = [];
        let currentMode = null; // 'billing' | 'expense' | 'hybrid'
        // ── PROJECT NAME MANAGER ───────────────────────────────────────────────────────

        function openProjectNameManager() {
            document.getElementById('projectname-manager-modal').classList.remove('hidden');
            renderProjectNameList();
        }

        function closeProjectNameManager() {
            document.getElementById('projectname-manager-modal').classList.add('hidden');
            document.getElementById('new-projectname-input').value = '';
            // Reload dropdown + preserve current selection
            const currentVal = document.getElementById('f-project-name').value;
            fetch('<?= BASE_URL ?>/fetchprojectnames')
                .then(r => r.json())
                .then(names => {
                    const sel = document.getElementById('f-project-name');
                    sel.innerHTML = '<option value="">— Select —</option>';
                    names.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.name;
                        opt.textContent = p.name;
                        sel.appendChild(opt);
                    });
                    if (currentVal) sel.value = currentVal;
                });
        }

        function renderProjectNameList() {
            fetch('<?= BASE_URL ?>/fetchprojectnames')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('projectname-list');
                    list.innerHTML = data.length ? data.map(p => `
                <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 group" id="pn-row-${p.id}">
                    <span id="pn-text-${p.id}" class="text-sm text-gray-700 flex-1">${p.name}</span>
                    <input id="pn-edit-${p.id}" type="text" value="${p.name}"
                        class="hidden flex-1 border border-orange-300 rounded px-2 py-1 text-sm outline-none focus:ring-1 focus:ring-orange-200 uppercase">
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition" id="pn-actions-${p.id}">
                        <button onclick="startEditPN(${p.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-orange-100 text-gray-400 hover:text-orange-500 transition">
                            <i class="fa-solid fa-pen text-[9px]"></i>
                        </button>
                        <button onclick="deletePN(${p.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-red-100 text-gray-400 hover:text-red-500 transition">
                            <i class="fa-solid fa-trash text-[9px]"></i>
                        </button>
                    </div>
                    <div class="hidden gap-1" id="pn-save-actions-${p.id}">
                        <button onclick="saveEditPN(${p.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-green-100 hover:bg-green-200 text-green-600 transition">
                            <i class="fa-solid fa-check text-[9px]"></i>
                        </button>
                        <button onclick="cancelEditPN(${p.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
                            <i class="fa-solid fa-xmark text-[9px]"></i>
                        </button>
                    </div>
                </div>`).join('')
                        : '<p class="text-xs text-gray-400 text-center py-4">No project names yet.</p>';
                });
        }

        function startEditPN(id) {
            document.getElementById('pn-text-' + id).classList.add('hidden');
            document.getElementById('pn-edit-' + id).classList.remove('hidden');
            document.getElementById('pn-actions-' + id).classList.add('hidden');
            document.getElementById('pn-save-actions-' + id).classList.remove('hidden');
            document.getElementById('pn-save-actions-' + id).classList.add('flex');
            document.getElementById('pn-edit-' + id).focus();
        }
        function cancelEditPN(id) {
            document.getElementById('pn-text-' + id).classList.remove('hidden');
            document.getElementById('pn-edit-' + id).classList.add('hidden');
            document.getElementById('pn-actions-' + id).classList.remove('hidden');
            document.getElementById('pn-save-actions-' + id).classList.add('hidden');
            document.getElementById('pn-save-actions-' + id).classList.remove('flex');
        }
        function saveEditPN(id) {
            const val = document.getElementById('pn-edit-' + id).value.trim();
            if (!val) return;
            fetch('<?= BASE_URL ?>/saveprojectname', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name: val })
            }).then(r => r.json()).then(d => { if (d.success) renderProjectNameList(); });
        }
        function deletePN(id) {
            if (!confirm('Delete this project name?')) return;
            fetch('<?= BASE_URL ?>/deleteprojectname', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            }).then(r => r.json()).then(d => { if (d.success) renderProjectNameList(); });
        }
        function addNewProjectName() {
            const val = document.getElementById('new-projectname-input').value.trim();
            if (!val) return;
            fetch('<?= BASE_URL ?>/saveprojectname', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: val })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('new-projectname-input').value = '';
                    renderProjectNameList();
                    showToast('Project name added!');
                } else {
                    alert(d.message ?? 'Something went wrong.');
                }
            });
        }

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
            const cards = document.getElementById('projects-cards');

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No projects yet.</td></tr>`;
                cards.innerHTML = `<div class="px-4 py-8 text-center text-gray-400 text-sm">No projects yet.</div>`;
                return;
            }

            const statusColors = {
                'New': 'bg-purple-100 text-purple-700',
                'Ongoing': 'bg-blue-100 text-blue-700',
                'Completed': 'bg-green-100 text-green-700',
                'On Hold': 'bg-yellow-100 text-yellow-700',
                'Cancelled': 'bg-red-100 text-red-700',
            };

            // ── Desktop rows (unchanged) ──
            tbody.innerHTML = data.map(row => {
                const statusCls = statusColors[row.status] ?? 'bg-gray-100 text-gray-500';
                const date = row.created_at
                    ? new Date(row.created_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                    : '—';
                const amount = row.contract_amount
                    ? '₱ ' + parseFloat(row.contract_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                    : '—';

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

            // ── Mobile cards ──
            cards.innerHTML = data.map(row => {
                const statusCls = statusColors[row.status] ?? 'bg-gray-100 text-gray-500';
                const date = row.created_at
                    ? new Date(row.created_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                    : '—';
                const contract = parseFloat(row.contract_amount) || 0;
                const credited = parseFloat(row.total_credited) || 0;
                const pct = contract > 0 ? Math.min((credited / contract) * 100, 100) : 0;
                const fillClass = pct >= 100 ? 'full' : pct >= 75 ? 'high' : pct >= 40 ? 'mid' : 'low';
                const pctColor = pct >= 100 ? 'text-green-600' : pct >= 75 ? 'text-green-500' : pct >= 40 ? 'text-orange-500' : 'text-red-500';
                const amountFmt = contract > 0 ? '₱ ' + contract.toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '—';
                const creditedFmt = '₱ ' + credited.toLocaleString('en-PH', { minimumFractionDigits: 2 });

                return `
<div class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 hover:bg-orange-50 active:bg-orange-100 transition-colors cursor-pointer"
    onclick="window.location='<?= BASE_URL ?>/projectdetail?id=${row.id}'">

    <!-- Left color bar based on status -->
    <div class="w-1 self-stretch rounded-full flex-shrink-0 mt-1
        ${row.status === 'Completed' ? 'bg-green-400' :
                        row.status === 'Ongoing' ? 'bg-blue-400' :
                            row.status === 'On Hold' ? 'bg-yellow-400' :
                                row.status === 'Cancelled' ? 'bg-red-400' : 'bg-purple-400'}"></div>

    <!-- Main content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between gap-2 mb-0.5">
            <span class="font-mono text-[10px] font-bold text-orange-500 truncate">${row.reference_no ?? '—'}</span>
            <span class="${statusCls} text-[9px] font-semibold px-2 py-0.5 rounded-full uppercase flex-shrink-0">${row.status ?? '—'}</span>
        </div>
        <div class="text-sm font-semibold text-gray-800 truncate">${row.project_name}</div>
        <div class="text-[11px] text-gray-400 truncate">${row.client_name ?? '—'} ${row.sales_person ? '· ' + row.sales_person : ''}</div>

        ${contract > 0 ? `
        <div class="mt-1.5">
            <div class="flex items-center justify-between text-[10px] mb-0.5">
                <span class="text-gray-400">${amountFmt}</span>
                <span class="font-bold ${pctColor}">${pct.toFixed(1)}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill ${fillClass}" style="width:${pct}%"></div>
            </div>
            <div class="text-[10px] text-gray-400 mt-0.5">${creditedFmt} credited</div>
        </div>` : `<div class="text-[10px] text-gray-300 mt-1">No contract amount set</div>`}

        <div class="text-[10px] text-gray-300 mt-1">${date}</div>
    </div>

    <!-- Edit button -->
    <button onclick="event.stopPropagation(); openAddModal(${row.id})"
        class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 transition mt-1">
        <i class="fa-solid fa-pen text-xs"></i>
    </button>
</div>`;
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


            // Fetch project names and populate dropdown
            fetch('<?= BASE_URL ?>/fetchprojectnames')
                .then(res => res.json())
                .then(names => {
                    const sel = document.getElementById('f-project-name');
                    const currentVal = sel.value;
                    sel.innerHTML = '<option value="">— Select —</option>';
                    names.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.name;
                        opt.textContent = p.name;
                        sel.appendChild(opt);
                    });
                    if (currentVal) sel.value = currentVal;
                });


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
            const modalOpen = !document.getElementById('entry-modal').classList.contains('hidden');
            const savedVal = sel.value;

            sel.innerHTML = '<option value="">— Choose a project —</option>';
            projects.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.project_name + (p.reference_no ? '  [' + p.reference_no + ']' : '');
                sel.appendChild(opt);
            });

            // Never wipe the user's selection if the modal is open
            if (modalOpen && savedVal) {
                sel.value = savedVal;
            }
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

            const modalBox = document.querySelector('#entry-modal > div');
            const hybridGrid = document.getElementById('hybrid-grid');
            modalBox.classList.remove('max-w-5xl');
            modalBox.classList.add('max-w-2xl');
            hybridGrid.classList.remove('grid-cols-2');
            hybridGrid.classList.add('grid-cols-1');
        }

        function setMode(mode) {
            currentMode = mode;


            // ── Update mode button styles ──────────────────────────
            const modeClass = {
                billing: 'mode-btn active-billing',
                expense: 'mode-btn active-expense',
                hybrid: 'mode-btn active-hybrid'
            };
            ['billing', 'expense', 'hybrid'].forEach(m => {
                document.getElementById('mode-' + m).className = m === mode ? modeClass[m] : 'mode-btn';
            });

            // ── Show/hide field sections (fields keep their values) ─
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

            const modalBox = document.querySelector('#entry-modal > div');
            const hybridGrid = document.getElementById('hybrid-grid');

            if (mode === 'hybrid') {
                modalBox.classList.remove('max-w-2xl');
                modalBox.classList.add('md:max-w-5xl');
                hybridGrid.classList.remove('grid-cols-1');
                hybridGrid.classList.add('md:grid-cols-2');
            } else {
                modalBox.classList.remove('md:max-w-5xl');
                modalBox.classList.add('max-w-2xl');
                hybridGrid.classList.remove('md:grid-cols-2');
                hybridGrid.classList.add('grid-cols-1');
            }

            saveBtn.disabled = false;
        }

        // ── AMOUNT FORMATTING ──────────────────────────────────────────────────────────

        function formatAmountInput(el) {
            // Strip everything except digits and one decimal point
            let raw = el.value.replace(/[^0-9.]/g, '');

            // Allow only one decimal point
            const parts = raw.split('.');
            if (parts.length > 2) raw = parts[0] + '.' + parts.slice(1).join('');

            // Limit decimal to 2 places
            if (parts[1] !== undefined) {
                raw = parts[0] + '.' + parts[1].slice(0, 2);
            }

            // Format integer part with commas
            const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            el.value = parts[1] !== undefined ? intPart + '.' + (parts[1] || '') : intPart;
        }

        function getRawAmount(id) {
            // Strip commas → parse as float → send clean number to backend
            const val = document.getElementById(id).value.replace(/,/g, '');
            return val === '' ? '' : parseFloat(val);
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
                    amount: getRawAmount('b-amount'),
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
                    amount: getRawAmount('e-amount'),
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