<?php
// index-accounting-generalsheet.php
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
    <title>Noble General Sheet</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">

    <main id="main-content" class="md:ml-56 pt-20 md:pt-5 min-h-screen p-4 md:p-8 transition-all duration-300">

        <!-- Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Noble General Sheet</h1>
                <p class="text-sm text-gray-400 mt-1">Collection and payment records</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
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
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Entries</p>
                <p id="card-total-entries" class="text-xl font-bold text-gray-800">0</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Amount</p>
                <p id="card-total-amount" class="text-xl font-bold text-green-600">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Particulars Amount
                </p>
                <p id="card-particulars-amount" class="text-xl font-bold text-blue-600">₱ 0.00</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse min-w-[1200px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest">
                            <th class="px-1 py-1 text-center border-r border-orange-500 w-10">No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-28 whitespace-nowrap">Payment
                                Date</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-36 whitespace-nowrap">Collection
                                Receipt No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-28 whitespace-nowrap">Type of
                                Sale</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-36 whitespace-nowrap">Deposit
                                Reference No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-32 whitespace-nowrap">Payee</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-32 whitespace-nowrap">Payor</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 whitespace-nowrap">Particulars
                            </th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-28 whitespace-nowrap">Sales
                                Person</th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-36 whitespace-nowrap">Department
                            </th>
                            <th class="px-1 py-1 text-right border-r border-orange-500 w-32 whitespace-nowrap">Amount
                            </th>
                            <th class="px-1 py-1 text-left border-r border-orange-500 w-28 whitespace-nowrap">Added By
                            </th>
                            <th class="px-1 py-1 text-center w-20 whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-tbody">
                        <tr>
                            <td colspan="13" class="px-5 py-10 text-center text-gray-400 text-sm">
                                <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries yet
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-orange-500 text-white">
                            <td colspan="10"
                                class="px-1 py-1 text-right text-[10px] font-bold uppercase tracking-widest border-r border-orange-500 whitespace-nowrap">
                                Total</td>
                            <td id="foot-amount"
                                class="px-1 py-1 text-right font-bold font-mono text-white border-r border-orange-500 text-xs whitespace-nowrap">
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </main>

    <!-- Add/Edit Modal -->
    <div id="entry-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h3 id="modal-title" class="font-bold text-sm uppercase tracking-widest text-gray-700">
                    <i class="fa-solid fa-plus mr-2 text-orange-500"></i>Add Entry
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-6 py-5 grid grid-cols-2 gap-4 overflow-y-auto">
                <input type="hidden" id="entry-id">

                <div id="date-field-wrap">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                        Payment Date <span class="text-red-400">*</span>
                    </label>
                    <input type="date" id="entry-payment-date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Department -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Department</label>
                    <select id="entry-department"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition bg-white">
                        <option value="">— Select —</option>
                    </select>
                </div>

                <!-- Reference Mode Toggle -->
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                        Reference Mode
                        <span class="text-[9px] normal-case text-gray-400 font-normal ml-1">(piliin kung alin ang
                            papasok sa reference)</span>
                    </label>
                    <div class="flex gap-2">
                        <button type="button" id="mode-cr" onclick="setRefMode('collection')"
                            class="flex-1 flex items-center justify-center gap-2 border-2 border-orange-500 bg-orange-50 text-orange-700 text-[10px] font-semibold py-2 px-3 rounded-lg transition">
                            <i class="fa-solid fa-receipt text-xs"></i>
                            Use Collection Receipt No.
                        </button>
                        <button type="button" id="mode-dep" onclick="setRefMode('deposit')"
                            class="flex-1 flex items-center justify-center gap-2 border border-gray-200 bg-gray-50 text-gray-400 text-[10px] font-semibold py-2 px-3 rounded-lg transition hover:border-gray-400">
                            <i class="fa-solid fa-building-columns text-xs"></i>
                            Use Deposit Reference No.
                        </button>
                    </div>
                </div>

                <!-- Collection Receipt No. -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                        Collection Receipt No.
                        <span id="badge-cr"
                            class="text-[8px] bg-orange-100 text-orange-600 font-bold px-1.5 py-0.5 rounded ml-1">→
                            REFERENCE</span>
                    </label>
                    <input type="text" id="entry-collection-receipt-no" placeholder="e.g. CR-0001"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Deposit Reference No. -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                        Deposit Reference No.
                        <span id="badge-dep"
                            class="hidden text-[8px] bg-orange-100 text-orange-600 font-bold px-1.5 py-0.5 rounded ml-1">→
                            REFERENCE</span>
                    </label>
                    <input type="text" id="entry-deposit-reference-no" placeholder="e.g. DEP-0001"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Type of Sale -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Type of
                        Sale</label>
                    <input type="text" id="entry-type-of-sale" placeholder="e.g. Cash / Credit"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Payee -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Payee</label>
                    <input type="text" id="entry-payee" placeholder="e.g. Noble Corp"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Payor -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Payor</label>
                    <input type="text" id="entry-payor" placeholder="e.g. Juan Dela Cruz"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Particulars -->
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                        Particulars <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="entry-particulars" placeholder="e.g. Payment for services"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Sales Person -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Sales
                        Person</label>
                    <input type="text" id="entry-sales-person" placeholder="e.g. Maria Santos"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 transition">
                </div>

                <!-- Amount -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-widests text-gray-500 mb-1.5">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                        <input type="number" id="entry-amount" step="0.01" min="0" placeholder="0.00"
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

    <script>
        let allEntries = [];
        let deleteTargetId = null;
        let editMode = false;
        let currentRefMode = 'collection';

        const BASE_URL_JS = '<?= BASE_URL ?>';

        function setRefMode(mode) {
            currentRefMode = mode;
            const crBtn = document.getElementById('mode-cr');
            const depBtn = document.getElementById('mode-dep');
            const badgeCr = document.getElementById('badge-cr');
            const badgeDep = document.getElementById('badge-dep');

            if (mode === 'collection') {
                crBtn.className = 'flex-1 flex items-center justify-center gap-2 border-2 border-orange-500 bg-orange-50 text-orange-700 text-[10px] font-semibold py-2 px-3 rounded-lg transition';
                depBtn.className = 'flex-1 flex items-center justify-center gap-2 border border-gray-200 bg-gray-50 text-gray-400 text-[10px] font-semibold py-2 px-3 rounded-lg transition hover:border-gray-400';
                badgeCr.classList.remove('hidden');
                badgeDep.classList.add('hidden');
            } else {
                depBtn.className = 'flex-1 flex items-center justify-center gap-2 border-2 border-gray-700 bg-gray-100 text-gray-700 text-[10px] font-semibold py-2 px-3 rounded-lg transition';
                crBtn.className = 'flex-1 flex items-center justify-center gap-2 border border-gray-200 bg-gray-50 text-gray-400 text-[10px] font-semibold py-2 px-3 rounded-lg transition hover:border-gray-400';
                badgeDep.classList.remove('hidden');
                badgeCr.classList.add('hidden');
            }
        }

        function loadDepartments(selectedValue = '') {
            fetch(`${BASE_URL_JS}/fetchnoblepettycashdepartment`)
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('entry-department');
                    sel.innerHTML = '<option value="">— Select —</option>' +
                        data.map(d => `<option value="${d.name}" ${d.name === selectedValue ? 'selected' : ''}>${d.name}</option>`).join('');
                });
        }

        function fetchEntries() {
            const month = document.getElementById('filter-month').value;
            fetch(`${BASE_URL_JS}/fetchnoblegeneralsheet?month=${month}`)
                .then(res => res.json())
                .then(data => {
                    allEntries = data;
                    renderTable(data);
                    renderSummary(data);
                })
                .catch(err => console.error(err));
        }

        function renderTable(data) {
            const tbody = document.getElementById('sheet-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="13" class="px-5 py-10 text-center text-gray-400 text-sm">
                <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries yet</td></tr>`;
                return;
            }

            let rowNum = 0;
            tbody.innerHTML = data.map(row => {
                rowNum++;
                return `
            <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                <td class="px-1 py-1 text-center text-xs text-gray-400 font-mono border-r border-gray-100 whitespace-nowrap">${rowNum}</td>
                <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.payment_date ?? ''}</td>
                <td class="px-1 py-1 text-xs font-mono text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.collection_receipt_no ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.type_of_sale ?? ''}</td>
                <td class="px-1 py-1 text-xs font-mono text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.deposit_reference_no ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100 whitespace-nowrap">${row.payee ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100 whitespace-nowrap">${row.payor ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100 whitespace-nowrap">${row.particulars ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.sales_person ?? ''}</td>
                <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.department ?? ''}</td>
                <td class="px-1 py-1 text-xs font-mono text-right font-semibold text-green-700 border-r border-gray-100 whitespace-nowrap">
                    ${row.amount ? '₱ ' + parseFloat(row.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : ''}
                </td>
                <td class="px-1 py-1 text-xs text-gray-500 border-r border-gray-100 whitespace-nowrap">${row.inserted_by ?? ''}</td>
                <td class="px-1 py-1 text-center whitespace-nowrap">
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

        function renderSummary(data) {
            let totalAmount = 0;
            let particularsAmount = 0;
            data.forEach(row => {
                totalAmount += parseFloat(row.amount || 0);
                particularsAmount += parseFloat(row.amount || 0);
            });
            const fmt = v => '₱ ' + v.toLocaleString('en-PH', { minimumFractionDigits: 2 });
            document.getElementById('card-total-entries').textContent = data.length;
            document.getElementById('card-total-amount').textContent = fmt(totalAmount);
            document.getElementById('card-particulars-amount').textContent = fmt(particularsAmount);
            document.getElementById('foot-amount').textContent = fmt(totalAmount);
        }

        function openAddModal() {
            editMode = false;
            document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-plus mr-2 text-orange-500"></i>Add Entry';
            document.getElementById('entry-id').value = '';

            const today = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
            document.getElementById('date-field-wrap').innerHTML = `
    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">
        Payment Date <span class="text-red-400">*</span>
    </label>
    <div class="w-full border border-gray-100 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400 select-none">${today}</div>
    <input type="hidden" id="entry-payment-date" value="${today}">
`;

            document.getElementById('entry-collection-receipt-no').value = '';
            document.getElementById('entry-deposit-reference-no').value = '';
            document.getElementById('entry-type-of-sale').value = '';
            document.getElementById('entry-payee').value = '';
            document.getElementById('entry-payor').value = '';
            document.getElementById('entry-particulars').value = '';
            document.getElementById('entry-sales-person').value = '';
            document.getElementById('entry-amount').value = '';
            currentRefMode = 'collection';
            setRefMode('collection');
            loadDepartments();
            document.getElementById('entry-modal').classList.remove('hidden');
        }

        function editEntry(id) {
            const row = allEntries.find(r => r.id == id);
            if (!row) return;
            editMode = true;
            document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-pen mr-2 text-orange-500"></i>Edit Entry';
            document.getElementById('entry-id').value = row.id;

            // Date field — locked, plain text display
            document.getElementById('date-field-wrap').innerHTML = `
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Payment Date</label>
        <div class="w-full border border-gray-100 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400 select-none">${row.payment_date ?? ''}</div>
        <input type="hidden" id="entry-payment-date" value="${row.payment_date ?? ''}">
    `;

            document.getElementById('entry-collection-receipt-no').value = row.collection_receipt_no ?? '';
            document.getElementById('entry-deposit-reference-no').value = row.deposit_reference_no ?? '';
            document.getElementById('entry-type-of-sale').value = row.type_of_sale ?? '';
            document.getElementById('entry-payee').value = row.payee ?? '';
            document.getElementById('entry-payor').value = row.payor ?? '';
            document.getElementById('entry-particulars').value = row.particulars ?? '';
            document.getElementById('entry-sales-person').value = row.sales_person ?? '';
            document.getElementById('entry-amount').value = row.amount ?? '';
            currentRefMode = row.reference_mode ?? 'collection';
            setRefMode(currentRefMode);
            loadDepartments(row.department ?? '');
            document.getElementById('entry-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('entry-modal').classList.add('hidden');
        }

        function saveEntry() {
            const payment_date = document.getElementById('entry-payment-date').value;
            const particulars = document.getElementById('entry-particulars').value.trim();
            if (!payment_date || !particulars) {
                alert('Payment Date and Particulars are required.');
                return;
            }

            const reference = currentRefMode === 'collection'
                ? document.getElementById('entry-collection-receipt-no').value.trim()
                : document.getElementById('entry-deposit-reference-no').value.trim();

            const payload = {
                id: document.getElementById('entry-id').value || null,
                payment_date,
                collection_receipt_no: document.getElementById('entry-collection-receipt-no').value.trim(),
                deposit_reference_no: document.getElementById('entry-deposit-reference-no').value.trim(),
                reference_mode: currentRefMode,
                reference,
                department: document.getElementById('entry-department').value.trim(),
                type_of_sale: document.getElementById('entry-type-of-sale').value.trim(),
                payee: document.getElementById('entry-payee').value.trim(),
                payor: document.getElementById('entry-payor').value.trim(),
                particulars,
                sales_person: document.getElementById('entry-sales-person').value.trim(),
                amount: document.getElementById('entry-amount').value || 0,
            };

            fetch(`${BASE_URL_JS}/savenoblegeneralsheet`, {
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

        function openDeleteModal(id) {
            deleteTargetId = id;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            deleteTargetId = null;
        }
        function confirmDelete() {
            fetch(`${BASE_URL_JS}/deletenoblegeneralsheet`, {
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

        function showToast(message, type = 'success') {
            const existing = document.getElementById('ngs-toast');
            if (existing) existing.remove();
            const color = type === 'delete' ? 'bg-red-500' : 'bg-green-500';
            const icon = type === 'delete' ? 'fa-trash' : 'fa-circle-check';
            const toast = document.createElement('div');
            toast.id = 'ngs-toast';
            toast.className = `fixed bottom-6 right-6 z-[999] flex items-center gap-3 ${color} text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg transition-all duration-300 opacity-0`;
            toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.replace('opacity-0', 'opacity-100'), 10);
            setTimeout(() => {
                toast.classList.replace('opacity-100', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        const urlParams = new URLSearchParams(window.location.search);
        const monthParam = urlParams.get('month');
        if (monthParam) document.getElementById('filter-month').value = monthParam;

        document.getElementById('filter-month').addEventListener('change', fetchEntries);
        fetchEntries();
    </script>

</body>

</html>