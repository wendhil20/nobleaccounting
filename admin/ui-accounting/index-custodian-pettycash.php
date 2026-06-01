<?php
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
    <title>Petty Cash General Sheet</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main id="main-content" class="ml-56 min-h-screen p-8 transition-all duration-300">

        <!-- Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Petty Cash General Sheet</h1>
                <p class="text-sm text-gray-400 mt-1">Cash inflows and transaction records</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Sheet Tabs -->
                <!-- Sheet Tabs -->
                <div class="flex items-center bg-white border border-gray-200 rounded-lg p-1 shadow-sm gap-1">
                    <span class="text-xs font-bold text-white bg-orange-500 px-3 py-1.5 rounded-md">General Sheet</span>
                    <a id="tab-custodian" href="<?= BASE_URL ?>/accountingcustodianpettycashtwo"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 transition flex items-center gap-1.5">
                        General Sheet Two <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                    <a id="tab-department" href="<?= BASE_URL ?>/pettycashdepartment"
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
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Beginning Balance</p>
                <p id="card-beginning" class="text-xl font-bold text-gray-800">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Cash Inflows</p>
                <p id="card-inflows" class="text-xl font-bold text-green-600">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Actual (Expenses)
                </p>
                <p id="card-actual" class="text-xl font-bold text-red-500">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 border-l-4 border-l-blue-400">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Remaining Balance</p>
                <p id="card-balance" class="text-xl font-bold text-blue-600">₱ 0.00</p>
                <p class="text-[9px] text-gray-400 mt-1">→ Becomes next month's beginning</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse min-w-[1200px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest">
                            <th class="px-3 py-3 text-center border-r border-orange-400 w-10">No.</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">Date</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-32">Cash Inflows</th>
                            <th class="px-4 py-3 text-center border-r border-orange-400 w-24">Voucher No.</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-36">Account Title</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400">Particulars</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-32">Department</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">In-Charge</th>
                            <th class="px-4 py-3 text-right border-r border-orange-400 w-28">Actual</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">Remarks</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">Reference</th>
                            <th class="px-4 py-3 text-left border-r border-orange-400 w-28">Added By</th>
                            <th class="px-4 py-3 text-center w-20">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-tbody">
                        <tr>
                            <td colspan="12" class="px-5 py-10 text-center text-gray-400 text-sm">
                                <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries yet
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="2"
                                class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-500 border-r border-gray-200">
                                Total</td>
                            <td id="foot-inflows"
                                class="px-4 py-3 text-right font-bold font-mono text-green-600 border-r border-gray-200 text-xs">
                            </td>
                            <td colspan="5" class="border-r border-gray-200"></td>
                            <td id="foot-actual"
                                class="px-4 py-3 text-right font-bold font-mono text-red-500 border-r border-gray-200 text-xs">
                            </td>
                            <td colspan="3"></td>
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
                — This will be the <strong>Beginning Balance</strong> for the next month.
            </p>
        </div>

    </main>

    <!-- Add/Edit Modal -->
    <div id="entry-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 id="modal-title" class="font-bold text-sm uppercase tracking-widest text-gray-700">
                    <i class="fa-solid fa-plus mr-2 text-orange-500"></i>Add Entry
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-6 py-5 grid grid-cols-2 gap-4">
                <input type="hidden" id="entry-id">

                <!-- Entry Type -->
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Entry Type
                        <span class="text-red-400">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="entry-type" id="type-received" value="received" checked
                                class="accent-orange-500">
                            <span class="text-sm text-gray-700 font-medium">Received</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="entry-type" id="type-beginning" value="beginning"
                                class="accent-orange-500">
                            <span class="text-sm text-gray-700 font-medium">Beginning Balance</span>
                        </label>
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Date <span
                            class="text-red-400">*</span></label>
                    <input type="date" id="entry-date" readonly
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-gray-50 cursor-not-allowed outline-none">
                </div>

                <!-- Cash Inflows -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Cash
                        Inflows</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-cash-inflows" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                    </div>
                </div>

                <!-- Voucher No -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Voucher
                        No.</label>
                    <input type="text" id="entry-voucher-no" placeholder="e.g. 0001"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Account Title -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Account
                        Title</label>
                    <input type="text" id="entry-account-title" placeholder="e.g. Cash on Hand"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Particulars -->
                <div class="col-span-2">
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Particulars
                        <span class="text-red-400">*</span></label>
                    <input type="text" id="entry-particulars" placeholder="e.g. Petty Cash Beginning"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Department -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Department</label>
                    <input type="text" id="entry-department" placeholder="e.g. Accounting"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- In-Charge -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">In-Charge</label>
                    <input type="text" id="entry-in-charge" placeholder="e.g. Juan Dela Cruz"
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

                <!-- Remarks -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Remarks</label>
                    <input type="text" id="entry-remarks" placeholder="Optional"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Reference -->
                <div class="col-span-2">
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Reference</label>
                    <input type="text" id="entry-reference" placeholder="Optional reference"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
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

    <script>
        let allEntries = [];
        let deleteTargetId = null;
        let editMode = false;

        const BASE_URL_JS = '<?= BASE_URL ?>';

        // ── Fetch ──────────────────────────────────────────────────
        function fetchEntries() {
            const month = document.getElementById('filter-month').value;
            fetch(`${BASE_URL_JS}/fetchgeneralsheet?month=${month}`)
                .then(res => res.json())
                .then(data => {
                    allEntries = data;
                    renderTable(data);
                    renderSummary(data, month);
                })
                .catch(err => console.error(err));
        }

        // ── Render Table ───────────────────────────────────────────
        function renderTable(data) {
            const tbody = document.getElementById('sheet-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="12" class="px-5 py-10 text-center text-gray-400 text-sm">
                <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries yet</td></tr>`;
                return;
            }

            let rowNum = 0;
            tbody.innerHTML = data.map(row => {
                rowNum++;
                const isBeginning = row.entry_type === 'beginning';
                const rowBg = isBeginning ? 'bg-orange-50' : 'hover:bg-gray-50';
                const badge = isBeginning
                    ? '<span class="inline-block text-[9px] font-bold uppercase bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded mr-1">Beginning</span>'
                    : '<span class="inline-block text-[9px] font-bold uppercase bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded mr-1">Received</span>';

                return `
            <tr class="border-t border-gray-100 transition ${rowBg}">
                <td class="px-3 py-3 text-center text-xs text-gray-400 font-mono border-r border-gray-100">${rowNum}</td>
                <td class="px-4 py-3 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.date ?? ''}</td>
                <td class="px-4 py-3 text-xs font-mono text-right text-green-700 border-r border-gray-100 font-semibold">
                    ${row.cash_inflows ? '₱ ' + parseFloat(row.cash_inflows).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : ''}
                </td>
                <td class="px-4 py-3 text-xs text-center font-mono text-gray-600 border-r border-gray-100">${row.voucher_no ?? ''}</td>
                <td class="px-4 py-3 text-xs text-gray-700 border-r border-gray-100">${row.account_title ?? ''}</td>
                <td class="px-4 py-3 text-xs text-gray-700 border-r border-gray-100">
                    ${badge}${row.particulars ?? ''}
                </td>
                <td class="px-4 py-3 text-xs text-gray-600 border-r border-gray-100">${row.department ?? ''}</td>
                <td class="px-4 py-3 text-xs text-gray-600 border-r border-gray-100">${row.in_charge ?? ''}</td>
                
                <td class="px-4 py-3 text-xs font-mono text-right text-red-600 border-r border-gray-100">
                    ${row.actual ? '₱ ' + parseFloat(row.actual).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : ''}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 border-r border-gray-100">${row.remarks ?? ''}</td>
                <td class="px-4 py-3 text-xs text-gray-500 border-r border-gray-100">${row.reference ?? ''}</td>
                <td class="px-4 py-3 text-xs text-gray-500 border-r border-gray-100">${row.inserted_by ?? ''}</td>
                <td class="px-4 py-3 text-center">
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
            let beginning = 0, inflows = 0, actual = 0;

            data.forEach(row => {
                if (row.entry_type === 'beginning') {
                    beginning = parseFloat(row.cash_inflows || 0);
                } else {
                    inflows += parseFloat(row.cash_inflows || 0);
                }
                actual += parseFloat(row.actual || 0);
            });

            // Remaining Balance = Beginning + Cash Inflows - Total Actual
            const remaining = beginning + inflows - actual;
            const fmt = v => '₱ ' + v.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            document.getElementById('card-beginning').textContent = fmt(beginning);
            document.getElementById('card-inflows').textContent = fmt(inflows);
            document.getElementById('card-actual').textContent = fmt(actual);
            document.getElementById('card-balance').textContent = fmt(remaining);

            const totalInflows = beginning + inflows;
            document.getElementById('foot-inflows').textContent = fmt(totalInflows);
            document.getElementById('foot-actual').textContent = fmt(actual);

            // Carryover bar
            if (data.length > 0) {
                const [yr, mo] = month.split('-').map(Number);
                const nextDate = new Date(yr, mo, 1); // JS months are 0-indexed so mo = next month
                const nextMonth = nextDate.toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
                const currentMonthLabel = new Date(yr, mo - 1, 1).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });

                document.getElementById('carryover-month').textContent = currentMonthLabel;
                document.getElementById('carryover-amount').textContent = fmt(remaining);
                document.getElementById('carryover-bar').classList.remove('hidden');
            } else {
                document.getElementById('carryover-bar').classList.add('hidden');
            }
        }

        // ── Modal ──────────────────────────────────────────────────
        function openAddModal() {
            editMode = false;
            document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-plus mr-2 text-orange-500"></i>Add Entry';
            document.getElementById('entry-id').value = '';

            const today = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
            document.getElementById('entry-date').value = today;
            document.getElementById('entry-date').readOnly = true;

            document.getElementById('entry-cash-inflows').value = '';
            document.getElementById('entry-voucher-no').value = '';
            document.getElementById('entry-account-title').value = '';
            document.getElementById('entry-particulars').value = '';
            document.getElementById('entry-department').value = '';
            document.getElementById('entry-in-charge').value = '';
            document.getElementById('entry-actual').value = '';
            document.getElementById('entry-remarks').value = '';
            document.getElementById('entry-reference').value = '';
            document.querySelector('input[name="entry-type"][value="received"]').checked = true;
            document.getElementById('entry-modal').classList.remove('hidden');
        }

        function editEntry(id) {
            const row = allEntries.find(r => r.id == id);
            if (!row) return;
            editMode = true;
            document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-pen mr-2 text-orange-500"></i>Edit Entry';
            document.getElementById('entry-id').value = row.id;
            document.getElementById('entry-date').value = row.date ?? '';
            document.getElementById('entry-date').readOnly = false;
            document.getElementById('entry-cash-inflows').value = row.cash_inflows ?? '';
            document.getElementById('entry-voucher-no').value = row.voucher_no ?? '';
            document.getElementById('entry-account-title').value = row.account_title ?? '';
            document.getElementById('entry-particulars').value = row.particulars ?? '';
            document.getElementById('entry-department').value = row.department ?? '';
            document.getElementById('entry-in-charge').value = row.in_charge ?? '';
            document.getElementById('entry-actual').value = row.actual ?? '';
            document.getElementById('entry-remarks').value = row.remarks ?? '';
            document.getElementById('entry-reference').value = row.reference ?? '';
            const typeVal = row.entry_type === 'beginning' ? 'beginning' : 'received';
            document.querySelector(`input[name="entry-type"][value="${typeVal}"]`).checked = true;
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
                entry_type: document.querySelector('input[name="entry-type"]:checked').value,
                date,
                cash_inflows: document.getElementById('entry-cash-inflows').value || 0,
                voucher_no: document.getElementById('entry-voucher-no').value.trim(),
                account_title: document.getElementById('entry-account-title').value.trim(),
                particulars,
                department: document.getElementById('entry-department').value.trim(),
                in_charge: document.getElementById('entry-in-charge').value.trim(),
                actual: document.getElementById('entry-actual').value || 0,
                remarks: document.getElementById('entry-remarks').value.trim(),
                reference: document.getElementById('entry-reference').value.trim(),
            };

            fetch(`${BASE_URL_JS}/savegeneralsheet`, {
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
            fetch(`${BASE_URL_JS}/deletegeneralsheet`, {
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
            const existing = document.getElementById('gs-toast');
            if (existing) existing.remove();
            const color = type === 'delete' ? 'bg-red-500' : 'bg-green-500';
            const icon = type === 'delete' ? 'fa-trash' : 'fa-circle-check';
            const toast = document.createElement('div');
            toast.id = 'gs-toast';
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
            document.getElementById('tab-custodian').href = `${BASE_URL_JS}/accountingcustodianpettycashtwo?month=${month}`;
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