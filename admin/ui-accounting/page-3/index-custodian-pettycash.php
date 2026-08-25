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
      <main id="main-content"
        class="md:ml-56 pt-20 md:pt-5 min-h-screen p-4 md:p-8 transition-all duration-300">

       <!-- Header -->
<div class="mb-4 flex items-start justify-between flex-wrap gap-2">
    <div>
        <h1 class="text-base font-bold text-gray-800">Petty Cash General Sheet</h1>
        <p class="text-[11px] text-gray-400 mt-0.5">Cash inflows and transaction records</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Sheet Tabs -->
        <div class="flex items-center bg-white border border-gray-200 rounded-lg p-0.5 shadow-sm gap-0.5">
            <span class="text-[10px] font-bold text-white bg-orange-500 px-2 py-1 rounded-md">
                <span class="hidden sm:inline">General Sheet</span>
                <span class="sm:hidden">General</span>
            </span>
            <a id="tab-custodian" href="<?= BASE_URL ?>/accountingcustodianpettycashtwo"
                class="text-[10px] font-semibold text-gray-500 hover:text-gray-700 px-2 py-1 rounded-md hover:bg-gray-100 transition flex items-center gap-1">
                <span class="hidden sm:inline">General Sheet Two</span>
                <span class="sm:hidden">Sheet Two</span>
                <i class="fa-solid fa-arrow-right text-[8px]"></i>
            </a>
            <a id="tab-department" href="<?= BASE_URL ?>/pettycashdepartment"
                class="text-[10px] font-semibold text-gray-500 hover:text-gray-700 px-2 py-1 rounded-md hover:bg-gray-100 transition flex items-center gap-1">
                <span class="hidden sm:inline">Department Sheet</span>
                <span class="sm:hidden">Dept.</span>
                <i class="fa-solid fa-arrow-right text-[8px]"></i>
            </a>
        </div>
        <!-- Month Filter -->
        <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-2 py-1 shadow-sm">
            <i class="fa-solid fa-calendar text-orange-400 text-[10px]"></i>
            <input type="month" id="filter-month"
                class="text-[10px] font-semibold text-gray-600 outline-none border-none bg-transparent"
                value="<?= date('Y-m') ?>">
        </div>
        <!-- Add Entry Button -->
        <button onclick="openAddModal()"
            class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-semibold px-3 py-1.5 rounded-lg transition shadow-sm">
            <i class="fa-solid fa-plus text-[9px]"></i>
            <span class="hidden sm:inline">Add Entry</span>
            <span class="sm:hidden">Add</span>
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-3 flex items-center justify-between sm:block">
        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-0.5 sm:mb-1">Beginning Balance</p>
        <p id="card-beginning" class="text-sm font-bold text-gray-800">₱ 0.00</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-3 flex items-center justify-between sm:block">
        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-0.5 sm:mb-1">Total Cash Inflows</p>
        <p id="card-inflows" class="text-sm font-bold text-green-600">₱ 0.00</p>
    </div>
</div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse min-w-[900px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest">
                            <th class="px-2 py-2 text-center border-r border-orange-400 w-10">No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-28">Date</th>
                            <th class="px-1 py-1 text-right border-r border-orange-400 w-32">Cash Inflows</th>
                            <th class="px-1 py-1 text-center border-r border-orange-400 w-24">Voucher No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-36">Account Title</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400">Particulars</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-28">Source</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-28">Reference</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-28">Added By</th>
                            <th class="px-1 py-1 text-center w-20">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-tbody">
                        <tr>
                            <td colspan="10" class="px-5 py-10 text-center text-gray-400 text-sm">
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
                            <td colspan="7"></td>
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
                            <span class="text-sm text-gray-700 font-medium">Receipts</span>
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
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Particulars
                        <span class="text-red-400">*</span></label>
                    <input type="text" id="entry-particulars" placeholder="e.g. Petty Cash Beginning"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Source (was In-Charge) -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Source</label>
                    <input type="text" id="entry-in-charge" placeholder="e.g. Juan Dela Cruz"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Reference -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Reference</label>
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
                tbody.innerHTML = `<tr><td colspan="10" class="px-5 py-10 text-center text-gray-400 text-sm">
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
                <td class="px-2 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-100">${rowNum}</td>
                <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.date ?? ''}</td>
                <td class="px-1 py-1 text-xs font-mono text-right text-green-700 border-r border-gray-100 font-semibold">
                    ${row.cash_inflows ? '₱ ' + parseFloat(row.cash_inflows).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : ''}
                </td>
                <td class="px-1 py-1 text-xs text-center font-mono text-gray-600 border-r border-gray-100">${row.voucher_no ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100">${row.account_title ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100">
                    ${badge}${row.particulars ?? ''}
                </td>
                <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100">${row.in_charge ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-500 border-r border-gray-100">${row.reference ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-500 border-r border-gray-100">${row.inserted_by ?? ''}</td>
                <td class="px-1 py-1 text-center">
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
            let beginning = 0, inflows = 0;

            data.forEach(row => {
                if (row.entry_type === 'beginning') {
                    beginning = parseFloat(row.cash_inflows || 0);
                } else {
                    inflows += parseFloat(row.cash_inflows || 0);
                }
            });

            const remaining = beginning + inflows;
            const fmt = v => '₱ ' + v.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            document.getElementById('card-beginning').textContent = fmt(beginning);
            document.getElementById('card-inflows').textContent = fmt(inflows);
            document.getElementById('foot-inflows').textContent = fmt(beginning + inflows);

            if (data.length > 0) {
                const [yr, mo] = month.split('-').map(Number);
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
            document.getElementById('entry-in-charge').value = '';
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
            document.getElementById('entry-in-charge').value = row.in_charge ?? '';
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
                in_charge: document.getElementById('entry-in-charge').value.trim(),
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