<?php
// index-custodian-pettycashtwo.php
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
    <title>Petty Cash Custodian Sheet</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main id="main-content" class="ml-56 min-h-screen p-8 transition-all duration-300">

        <!-- Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Petty Cash Custodian Sheet</h1>
                <p class="text-sm text-gray-400 mt-1">Expense disbursement records</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Sheet Tabs -->
                <div class="flex items-center bg-white border border-gray-200 rounded-lg p-1 shadow-sm gap-1">
                    <a id="tab-general" href="<?= BASE_URL ?>/accountingcustodianpettycash"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[9px]"></i> General Sheet
                    </a>
                    <span class="text-xs font-bold text-white bg-orange-500 px-3 py-1.5 rounded-md">General Sheet
                        Two</span>
                    <a href="<?= BASE_URL ?>/pettycashdepartment"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 transition flex items-center gap-1.5">
                        Department Sheet <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>
                <!-- Month Filter -->
                <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-1.5 shadow-sm">
                    <i class="fa-solid fa-calendar text-orange-400 text-xs"></i>
                    <input type="month" id="filter-month"
                        class="text-xs font-semibold text-gray-600 outline-none border-none bg-transparent"
                        value="<?= date('Y-m') ?>">
                </div>
                <button onclick="openAddModal()"
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i>Add Entry
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Cash Inflows (This Month)
                </p>
                <p id="card-inflows" class="text-xl font-bold text-green-600">₱ 0.00</p>
                <p class="text-[9px] text-gray-400 mt-1">From General Sheet</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Actual (Expenses)
                </p>
                <p id="card-actual" class="text-xl font-bold text-red-500">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total VATable</p>
                <p id="card-vatable" class="text-xl font-bold text-purple-600">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 border-l-4 border-l-blue-400">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Remaining Balance</p>
                <p id="card-balance" class="text-xl font-bold text-blue-600">₱ 0.00</p>
                <p class="text-[9px] text-gray-400 mt-1">→ Next month's beginning</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse" style="min-width: 1800px;">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest">
                            <th class="px-3 py-3 text-center border-r border-orange-400 w-10">No.</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-24">Date</th>
                            <th class="px-4 py-3 text-center border-r border-orange-400 w-24">Ref No.</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-36">Account Title</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400">Particulars</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">Dept.</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-24">In-Charge</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-24">Actual</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-32">Supplier (Corp)</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-32">Supplier (Indiv)</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">Address</th>
                            <th class="px-4 py-3 text-center border-r border-orange-400 w-24">TIN</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-24">VATable</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-20">VAT</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-24">Total</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-24">Non-VAT</th>
                            <th class="px-4 py-3 text-center border-r border-orange-400 w-28">No Sales Inv.</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-24">VAT Exempt</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-24">Added By</th>
                            <th class="px-4 py-3 text-center w-16">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-tbody">
                        <tr>
                            <td colspan="19" class="px-5 py-10 text-center text-gray-400 text-sm">
                                <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries yet
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="7"
                                class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-500 border-r border-gray-200">
                                Total</td>
                            <td id="foot-actual"
                                class="px-4 py-3 text-right font-bold font-mono text-red-500 border-r border-gray-200 text-xs">
                            </td>
                            <td colspan="4" class="border-r border-gray-200"></td>
                            <td id="foot-vatable"
                                class="px-4 py-3 text-right font-bold font-mono text-purple-600 border-r border-gray-200 text-xs">
                            </td>
                            <td id="foot-vat"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-600 border-r border-gray-200 text-xs">
                            </td>
                            <td id="foot-total"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-800 border-r border-gray-200 text-xs">
                            </td>
                            <td id="foot-nonvat"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-600 border-r border-gray-200 text-xs">
                            </td>
                            <td class="border-r border-gray-200"></td>
                            <td id="foot-vatexempt"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-600 border-r border-gray-200 text-xs">
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Carry-over info -->
        <div id="carryover-bar"
            class="hidden mt-4 bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 flex items-center gap-3">
            <i class="fa-solid fa-arrow-right-long text-blue-400"></i>
            <p class="text-xs text-blue-700 font-medium">
                Remaining Balance of <span id="carryover-month" class="font-bold"></span>:
                <span id="carryover-amount" class="font-bold text-blue-800"></span>
                — This will be the <strong>Beginning Balance</strong> for next month's General Sheet.
            </p>
        </div>

    </main>

    <!-- Add/Edit Modal -->
    <div id="entry-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h3 id="modal-title" class="font-bold text-sm uppercase tracking-widest text-gray-700">
                    <i class="fa-solid fa-plus mr-2 text-orange-500"></i>Add Entry
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-6 py-5 grid grid-cols-3 gap-4 overflow-y-auto">
                <input type="hidden" id="entry-id">


                <!-- Date -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Date</label>
                    <input type="date" id="entry-date" readonly
                        class="w-full border border-gray-100 rounded-lg px-3 py-2 text-sm text-gray-500 bg-gray-50 cursor-not-allowed select-none">
                </div>

                <!-- Reference No -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Reference
                        No.</label>
                    <input type="text" id="entry-reference-no" placeholder="e.g. 040001"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Account Title -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Account
                        Title</label>
                    <div class="flex gap-2">
                        <select id="entry-account-title"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition bg-white">
                            <option value="">— Select —</option>
                        </select>
                        <button type="button" onclick="openTitleManager()"
                            class="shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 text-gray-400 hover:text-orange-500 transition"
                            title="Manage titles">
                            <i class="fa-solid fa-gear text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Particulars -->
                <div class="col-span-3">
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Particulars
                        <span class="text-red-400">*</span></label>
                    <input type="text" id="entry-particulars" placeholder="e.g. Transportation Expense of Workers"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>


                <!-- Department -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Department</label>
                    <div class="flex gap-2">
                        <select id="entry-department"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition bg-white">
                            <option value="">— Select —</option>
                        </select>
                        <button type="button" onclick="openDeptManager()"
                            class="shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 text-gray-400 hover:text-orange-500 transition"
                            title="Manage departments">
                            <i class="fa-solid fa-gear text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- In-Charge -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">In-Charge</label>
                    <input type="text" id="entry-in-charge" placeholder="e.g. ERMA"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Actual -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Actual</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-actual" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

                <!-- Supplier Corp -->
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Supplier
                        Name (Corp)</label>
                    <input type="text" id="entry-supplier-corp" placeholder="Company name"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Supplier Individual -->
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Supplier
                        Name (Individual)</label>
                    <input type="text" id="entry-supplier-indiv" placeholder="Individual name"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Address -->
                <div class="col-span-1">
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Address</label>
                    <input type="text" id="entry-address" placeholder="Address"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- TIN -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">TIN</label>
                    <input type="text" id="entry-tin" placeholder="000-000-000"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- VATable Amount -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">VATable
                        Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-vatable" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

                <!-- VAT -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">VAT</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-vat" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

                <!-- Total -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Total</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-total" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

                <!-- Non-VAT -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Non-VAT</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-nonvat" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

                <!-- No Sales Invoice -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">No Sales
                        Invoice</label>
                    <input type="text" id="entry-no-sales-invoice" placeholder="Invoice no."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- VAT Exempt -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">VAT
                        Exempt</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-vat-exempt" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

            </div>
            <div
                class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                <button onclick="closeModal()"
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition">Cancel</button>
                <button onclick="saveEntry()"
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>Save Entry
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="px-6 py-5 text-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-trash text-red-500"></i>
                </div>
                <h3 class="font-bold text-sm text-gray-800 mb-1">Delete Entry?</h3>
                <p class="text-xs text-gray-500">This action cannot be undone.</p>
            </div>
            <div class="flex border-t border-gray-100">
                <button onclick="closeDeleteModal()"
                    class="flex-1 py-3 text-sm text-gray-500 hover:bg-gray-50 transition font-medium border-r border-gray-100">Cancel</button>
                <button onclick="confirmDelete()"
                    class="flex-1 py-3 text-sm text-red-500 hover:bg-red-50 transition font-semibold">Delete</button>
            </div>
        </div>
    </div>

    <!-- Title Manager Modal -->
    <div id="title-manager-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[80vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h3 class="font-bold text-sm uppercase tracking-widest text-gray-700">
                    <i class="fa-solid fa-gear mr-2 text-orange-500"></i>Manage Account Titles
                </h3>
                <button onclick="closeTitleManager()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <!-- Add new -->
            <div class="px-4 py-3 border-b border-gray-100 flex gap-2 flex-shrink-0">
                <input type="text" id="new-title-input" placeholder="New account title..."
                    onkeydown="if(event.key==='Enter') addNewTitle()"
                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition uppercase">
                <button onclick="addNewTitle()"
                    class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add
                </button>
            </div>
            <!-- List -->
            <div id="title-list" class="overflow-y-auto flex-1 px-2 py-2"></div>
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex-shrink-0 text-right">
                <button onclick="closeTitleManager()"
                    class="text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 px-5 py-2 rounded-lg transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Department Manager Modal -->
    <div id="dept-manager-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[80vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h3 class="font-bold text-sm uppercase tracking-widest text-gray-700">
                    <i class="fa-solid fa-gear mr-2 text-orange-500"></i>Manage Departments
                </h3>
                <button onclick="closeDeptManager()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-4 py-3 border-b border-gray-100 flex gap-2 flex-shrink-0">
                <input type="text" id="new-dept-input" placeholder="New department..."
                    onkeydown="if(event.key==='Enter') addNewDept()"
                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition uppercase">
                <button onclick="addNewDept()"
                    class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add
                </button>
            </div>
            <div id="dept-list" class="overflow-y-auto flex-1 px-2 py-2"></div>
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex-shrink-0 text-right">
                <button onclick="closeDeptManager()"
                    class="text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 px-5 py-2 rounded-lg transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <script>
        let allEntries = [];
        let deleteTargetId = null;
        let editMode = false;
        let generalSheetInflows = 0; // fetched from general sheet

        const BASE_URL_JS = '<?= BASE_URL ?>';
        const fmt = v => '₱ ' + parseFloat(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });

        // ── Fetch ──────────────────────────────────────────────────
        function fetchEntries() {
            const month = document.getElementById('filter-month').value;

            // Also fetch general sheet inflows for this month to compute remaining balance
            Promise.all([
                fetch(`${BASE_URL_JS}/fetchcustodiansheetpettycashtwo?month=${month}`).then(r => r.json()),
                fetch(`${BASE_URL_JS}/fetchgeneralsheet?month=${month}`).then(r => r.json())
            ]).then(([custodianData, generalData]) => {
                allEntries = custodianData;

                // Compute total cash inflows from general sheet (beginning + received)
                let beginning = 0, received = 0;
                generalData.forEach(row => {
                    if (row.entry_type === 'beginning') beginning = parseFloat(row.cash_inflows || 0);
                    else received += parseFloat(row.cash_inflows || 0);
                });
                generalSheetInflows = beginning + received;

                renderTable(custodianData);
                renderSummary(custodianData, month);
            }).catch(err => console.error(err));
        }

        // ── Departments ────────────────────────────────────────
        function loadDepartments(selectedValue = '') {
            fetch(`${BASE_URL_JS}/fetchpettycashdepartment`)
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('entry-department');
                    sel.innerHTML = '<option value="">— Select —</option>' +
                        data.map(d => `<option value="${d.name}" ${d.name === selectedValue ? 'selected' : ''}>${d.name}</option>`).join('');
                });
        }

        function openDeptManager() {
            document.getElementById('dept-manager-modal').classList.remove('hidden');
            renderDeptList();
        }
        function closeDeptManager() {
            document.getElementById('dept-manager-modal').classList.add('hidden');
            document.getElementById('new-dept-input').value = '';
            loadDepartments(document.getElementById('entry-department').value);
        }

        function renderDeptList() {
            fetch(`${BASE_URL_JS}/fetchpettycashdepartment`)
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('dept-list');
                    list.innerHTML = data.length ? data.map(d => `
                <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 group" id="dept-row-${d.id}">
                    <span id="dept-text-${d.id}" class="text-sm text-gray-700 flex-1">${d.name}</span>
                    <input id="dept-edit-${d.id}" type="text" value="${d.name}"
                        class="hidden flex-1 border border-orange-300 rounded px-2 py-1 text-sm outline-none focus:ring-1 focus:ring-orange-200">
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition" id="dept-actions-${d.id}">
                        <button onclick="startEditDept(${d.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-orange-100 text-gray-400 hover:text-orange-500 transition">
                            <i class="fa-solid fa-pen text-[9px]"></i>
                        </button>
                        <button onclick="deleteDeptItem(${d.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-red-100 text-gray-400 hover:text-red-500 transition">
                            <i class="fa-solid fa-trash text-[9px]"></i>
                        </button>
                    </div>
                    <div class="hidden gap-1" id="dept-save-actions-${d.id}">
                        <button onclick="saveEditDept(${d.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-green-100 hover:bg-green-200 text-green-600 transition">
                            <i class="fa-solid fa-check text-[9px]"></i>
                        </button>
                        <button onclick="cancelEditDept(${d.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
                            <i class="fa-solid fa-xmark text-[9px]"></i>
                        </button>
                    </div>
                </div>`).join('')
                        : '<p class="text-xs text-gray-400 text-center py-4">No departments yet.</p>';
                });
        }

        function startEditDept(id) {
            document.getElementById('dept-text-' + id).classList.add('hidden');
            document.getElementById('dept-edit-' + id).classList.remove('hidden');
            document.getElementById('dept-actions-' + id).classList.add('hidden');
            document.getElementById('dept-save-actions-' + id).classList.remove('hidden');
            document.getElementById('dept-save-actions-' + id).classList.add('flex');
            document.getElementById('dept-edit-' + id).focus();
        }
        function cancelEditDept(id) {
            document.getElementById('dept-text-' + id).classList.remove('hidden');
            document.getElementById('dept-edit-' + id).classList.add('hidden');
            document.getElementById('dept-actions-' + id).classList.remove('hidden');
            document.getElementById('dept-save-actions-' + id).classList.add('hidden');
        }
        function saveEditDept(id) {
            const val = document.getElementById('dept-edit-' + id).value.trim();
            if (!val) return;
            fetch(`${BASE_URL_JS}/savepettycashdepartment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name: val })
            }).then(r => r.json()).then(d => { if (d.success) renderDeptList(); });
        }
        function deleteDeptItem(id) {
            if (!confirm('Delete this department?')) return;
            fetch(`${BASE_URL_JS}/deletepettycashdepartment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            }).then(r => r.json()).then(d => { if (d.success) renderDeptList(); });
        }
        function addNewDept() {
            const val = document.getElementById('new-dept-input').value.trim();
            if (!val) return;
            fetch(`${BASE_URL_JS}/savepettycashdepartment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: val })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('new-dept-input').value = '';
                    renderDeptList();
                }
            });
        }

        // ── Account Titles ─────────────────────────────────────
        let allTitles = [];

        function loadAccountTitles(selectedValue = '') {
            fetch(`${BASE_URL_JS}/fetchpettycashaccounttitles`)
                .then(r => r.json())
                .then(data => {
                    allTitles = data;
                    const sel = document.getElementById('entry-account-title');
                    sel.innerHTML = '<option value="">— Select —</option>' +
                        data.map(t => `<option value="${t.title}" ${t.title === selectedValue ? 'selected' : ''}>${t.title}</option>`).join('');
                });
        }

        // ── Title Manager Modal ────────────────────────────────
        function openTitleManager() {
            document.getElementById('title-manager-modal').classList.remove('hidden');
            renderTitleList();
        }
        function closeTitleManager() {
            document.getElementById('title-manager-modal').classList.add('hidden');
            document.getElementById('new-title-input').value = '';
            loadAccountTitles(document.getElementById('entry-account-title').value);
        }

        function renderTitleList() {
            fetch(`${BASE_URL_JS}/fetchpettycashaccounttitles`)
                .then(r => r.json())
                .then(data => {
                    allTitles = data;
                    const list = document.getElementById('title-list');
                    list.innerHTML = data.length ? data.map(t => `
                <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 group" id="title-row-${t.id}">
                    <span id="title-text-${t.id}" class="text-sm text-gray-700 flex-1">${t.title}</span>
                    <input id="title-edit-${t.id}" type="text" value="${t.title}"
                        class="hidden flex-1 border border-orange-300 rounded px-2 py-1 text-sm outline-none focus:ring-1 focus:ring-orange-200">
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition" id="title-actions-${t.id}">
                        <button onclick="startEditTitle(${t.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-orange-100 text-gray-400 hover:text-orange-500 transition">
                            <i class="fa-solid fa-pen text-[9px]"></i>
                        </button>
                        <button onclick="deleteTitleItem(${t.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-red-100 text-gray-400 hover:text-red-500 transition">
                            <i class="fa-solid fa-trash text-[9px]"></i>
                        </button>
                    </div>
                    <div class="hidden gap-1" id="title-save-actions-${t.id}">
                        <button onclick="saveEditTitle(${t.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-green-100 hover:bg-green-200 text-green-600 transition">
                            <i class="fa-solid fa-check text-[9px]"></i>
                        </button>
                        <button onclick="cancelEditTitle(${t.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
                            <i class="fa-solid fa-xmark text-[9px]"></i>
                        </button>
                    </div>
                </div>`).join('')
                        : '<p class="text-xs text-gray-400 text-center py-4">No titles yet.</p>';
                });
        }

        function startEditTitle(id) {
            document.getElementById('title-text-' + id).classList.add('hidden');
            document.getElementById('title-edit-' + id).classList.remove('hidden');
            document.getElementById('title-actions-' + id).classList.add('hidden');
            document.getElementById('title-save-actions-' + id).classList.remove('hidden');
            document.getElementById('title-save-actions-' + id).classList.add('flex');
            document.getElementById('title-edit-' + id).focus();
        }
        function cancelEditTitle(id) {
            document.getElementById('title-text-' + id).classList.remove('hidden');
            document.getElementById('title-edit-' + id).classList.add('hidden');
            document.getElementById('title-actions-' + id).classList.remove('hidden');
            document.getElementById('title-save-actions-' + id).classList.add('hidden');
        }
        function saveEditTitle(id) {
            const val = document.getElementById('title-edit-' + id).value.trim();
            if (!val) return;
            fetch(`${BASE_URL_JS}/savepettycashaccounttitle`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, title: val })
            }).then(r => r.json()).then(d => { if (d.success) renderTitleList(); });
        }
        function deleteTitleItem(id) {
            if (!confirm('Delete this title?')) return;
            fetch(`${BASE_URL_JS}/deletepettycashaccounttitle`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            }).then(r => r.json()).then(d => { if (d.success) renderTitleList(); });
        }
        function addNewTitle() {
            const val = document.getElementById('new-title-input').value.trim();
            if (!val) return;
            fetch(`${BASE_URL_JS}/savepettycashaccounttitle`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ title: val })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('new-title-input').value = '';
                    renderTitleList();
                }
            });
        }

        // ── Render Table ───────────────────────────────────────────
        function renderTable(data) {
            const tbody = document.getElementById('sheet-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="19" class="px-5 py-10 text-center text-gray-400 text-sm">
                <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries yet</td></tr>`;
                return;
            }

            let rowNum = 0;
            tbody.innerHTML = data.map(row => {
                rowNum++;
                const money = (val) => val ? '₱ ' + parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '';
                return `
            <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                <td class="px-3 py-2.5 text-center text-xs text-gray-400 font-mono border-r border-gray-100">${rowNum}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.date ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-center font-mono text-gray-600 border-r border-gray-100">${row.reference_no ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-gray-700 border-r border-gray-100">${row.account_title ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-gray-700 border-r border-gray-100">${row.particulars ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 border-r border-gray-100">${row.project_department ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 border-r border-gray-100">${row.in_charge ?? ''}</td>
                <td class="px-4 py-2.5 text-xs font-mono text-right text-red-600 border-r border-gray-100 font-semibold whitespace-nowrap">${money(row.actual)}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 border-r border-gray-100">${row.supplier_name_corp ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 border-r border-gray-100">${row.supplier_name_indiv ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500 border-r border-gray-100">${row.address ?? ''}</td>
                <td class="px-4 py-2.5 text-xs text-center font-mono text-gray-600 border-r border-gray-100">${row.tin ?? ''}</td>
                <td class="px-4 py-2.5 text-xs font-mono text-right text-purple-600 border-r border-gray-100 whitespace-nowrap">${money(row.vatable_amount)}</td>
                <td class="px-4 py-2.5 text-xs font-mono text-right text-gray-600 border-r border-gray-100 whitespace-nowrap">${money(row.vat)}</td>
                <td class="px-4 py-2.5 text-xs font-mono text-right text-gray-800 font-semibold border-r border-gray-100 whitespace-nowrap">${money(row.total)}</td>
                <td class="px-4 py-2.5 text-xs font-mono text-right text-gray-600 border-r border-gray-100 whitespace-nowrap">${money(row.non_vat)}</td>
                <td class="px-4 py-2.5 text-xs text-center text-gray-600 border-r border-gray-100">${row.no_sales_invoice ?? ''}</td>
                <td class="px-4 py-2.5 text-xs font-mono text-right text-gray-600 border-r border-gray-100 whitespace-nowrap">${money(row.vat_exempt)}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 border-r border-gray-100">${row.inserted_by ?? '—'}</td>
                <td class="px-4 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button onclick="editEntry(${row.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-orange-100 text-gray-500 hover:text-orange-500 transition">
                            <i class="fa-solid fa-pen text-[9px]"></i>
                        </button>
                        <button onclick="openDeleteModal(${row.id})"
                            class="w-6 h-6 flex items-center justify-center rounded bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-500 transition">
                            <i class="fa-solid fa-trash text-[9px]"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
            }).join('');
        }

        // ── Summary ────────────────────────────────────────────────
        function renderSummary(data, month) {
            let actual = 0, vatable = 0, vat = 0, total = 0, nonvat = 0, vatexempt = 0;

            data.forEach(row => {
                actual += parseFloat(row.actual || 0);
                vatable += parseFloat(row.vatable_amount || 0);
                vat += parseFloat(row.vat || 0);
                total += parseFloat(row.total || 0);
                nonvat += parseFloat(row.non_vat || 0);
                vatexempt += parseFloat(row.vat_exempt || 0);
            });

            // Remaining = Total Cash Inflows (from General Sheet) - Total Actual
            const remaining = generalSheetInflows - actual;

            document.getElementById('card-inflows').textContent = fmt(generalSheetInflows);
            document.getElementById('card-actual').textContent = fmt(actual);
            document.getElementById('card-vatable').textContent = fmt(vatable);
            document.getElementById('card-balance').textContent = fmt(remaining);

            document.getElementById('foot-actual').textContent = fmt(actual);
            document.getElementById('foot-vatable').textContent = fmt(vatable);
            document.getElementById('foot-vat').textContent = fmt(vat);
            document.getElementById('foot-total').textContent = fmt(total);
            document.getElementById('foot-nonvat').textContent = fmt(nonvat);
            document.getElementById('foot-vatexempt').textContent = fmt(vatexempt);

            // Carryover bar
            const [yr, mo] = month.split('-').map(Number);
            const currentMonthLabel = new Date(yr, mo - 1, 1).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
            document.getElementById('carryover-month').textContent = currentMonthLabel;
            document.getElementById('carryover-amount').textContent = fmt(remaining);
            document.getElementById('carryover-bar').classList.remove('hidden');
        }

        // ── Modal ──────────────────────────────────────────────────
        function openAddModal() {
            editMode = false;
            document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-plus mr-2 text-orange-500"></i>Add Entry';
            document.getElementById('entry-id').value = '';
            const today = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
            document.getElementById('entry-date').value = today;
            ['reference-no', 'particulars', 'department', 'in-charge', 'actual',
                'supplier-corp', 'supplier-indiv', 'address', 'tin', 'vatable', 'vat', 'total', 'nonvat',
                'no-sales-invoice', 'vat-exempt'].forEach(f => {
                    const el = document.getElementById('entry-' + f);
                    if (el) el.value = '';
                });
            loadAccountTitles(); // ← dropdown reset
            loadDepartments();
            document.getElementById('entry-modal').classList.remove('hidden');
        }

        function editEntry(id) {
            const row = allEntries.find(r => r.id == id);
            if (!row) return;
            editMode = true;
            document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-pen mr-2 text-orange-500"></i>Edit Entry';
            document.getElementById('entry-id').value = row.id;
            document.getElementById('entry-date').value = row.date ?? '';
            document.getElementById('entry-reference-no').value = row.reference_no ?? '';
            loadAccountTitles(row.account_title ?? ''); // ← load dropdown + pre-select
            document.getElementById('entry-particulars').value = row.particulars ?? '';
            loadDepartments(row.project_department ?? '');
            document.getElementById('entry-in-charge').value = row.in_charge ?? '';
            document.getElementById('entry-actual').value = row.actual ?? '';
            document.getElementById('entry-supplier-corp').value = row.supplier_name_corp ?? '';
            document.getElementById('entry-supplier-indiv').value = row.supplier_name_indiv ?? '';
            document.getElementById('entry-address').value = row.address ?? '';
            document.getElementById('entry-tin').value = row.tin ?? '';
            document.getElementById('entry-vatable').value = row.vatable_amount ?? '';
            document.getElementById('entry-vat').value = row.vat ?? '';
            document.getElementById('entry-total').value = row.total ?? '';
            document.getElementById('entry-nonvat').value = row.non_vat ?? '';
            document.getElementById('entry-no-sales-invoice').value = row.no_sales_invoice ?? '';
            document.getElementById('entry-vat-exempt').value = row.vat_exempt ?? '';
            document.getElementById('entry-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('entry-modal').classList.add('hidden');
        }

        // ── Save ───────────────────────────────────────────────────
        function saveEntry() {
            const date = document.getElementById('entry-date').value;
            const particulars = document.getElementById('entry-particulars').value.trim();
            if (!date || !particulars) {
                alert('Date and Particulars are required.');
                return;
            }

            const payload = {
                id: document.getElementById('entry-id').value || null,
                date,
                reference_no: document.getElementById('entry-reference-no').value.trim(),
                account_title: document.getElementById('entry-account-title').value.trim(),
                particulars,
                project_department: document.getElementById('entry-department').value.trim(),
                in_charge: document.getElementById('entry-in-charge').value.trim(),
                actual: document.getElementById('entry-actual').value || 0,
                supplier_name_corp: document.getElementById('entry-supplier-corp').value.trim(),
                supplier_name_indiv: document.getElementById('entry-supplier-indiv').value.trim(),
                address: document.getElementById('entry-address').value.trim(),
                tin: document.getElementById('entry-tin').value.trim(),
                vatable_amount: document.getElementById('entry-vatable').value || 0,
                vat: document.getElementById('entry-vat').value || 0,
                total: document.getElementById('entry-total').value || 0,
                non_vat: document.getElementById('entry-nonvat').value || 0,
                no_sales_invoice: document.getElementById('entry-no-sales-invoice').value.trim(),
                vat_exempt: document.getElementById('entry-vat-exempt').value || 0,
            };

            fetch(`${BASE_URL_JS}/savecustodiansheetpettycashtwo`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        fetchEntries();
                        showToast(editMode ? 'Entry updated!' : 'Entry added!');
                    } else {
                        alert(data.message ?? 'Something went wrong.');
                    }
                });
        }

        // ── Delete ─────────────────────────────────────────────────
        function openDeleteModal(id) {
            deleteTargetId = id;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            deleteTargetId = null;
        }
        function confirmDelete() {
            fetch(`${BASE_URL_JS}/deletecustodiansheetpettycashtwo`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: deleteTargetId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeDeleteModal();
                        fetchEntries();
                        showToast('Entry deleted.', 'delete');
                    }
                });
        }

        // ── Toast ──────────────────────────────────────────────────
        function showToast(message, type = 'success') {
            const existing = document.getElementById('cs-toast');
            if (existing) existing.remove();
            const color = type === 'delete' ? 'bg-red-500' : 'bg-green-500';
            const icon = type === 'delete' ? 'fa-trash' : 'fa-circle-check';
            const toast = document.createElement('div');
            toast.id = 'cs-toast';
            toast.className = `fixed bottom-6 right-6 z-[999] flex items-center gap-3 ${color} text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg transition-all duration-300 opacity-0`;
            toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.replace('opacity-0', 'opacity-100'), 10);
            setTimeout(() => {
                toast.classList.replace('opacity-100', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }


        // ── Init ───────────────────────────────────────────────────
        const urlParams = new URLSearchParams(window.location.search);
        const monthParam = urlParams.get('month');
        if (monthParam) {
            document.getElementById('filter-month').value = monthParam;
        }

        function updateTabLinks() {
            const month = document.getElementById('filter-month').value;
            document.getElementById('tab-general').href = `${BASE_URL_JS}/accountingcustodianpettycash?month=${month}`;
        }

        document.getElementById('filter-month').addEventListener('change', () => {
            fetchEntries();
            updateTabLinks();
        });

        fetchEntries();
        updateTabLinks();
    </script>

</body>

</html>