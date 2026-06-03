<?php
// index-custodian-pettycash-department.php
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
    <title>Petty Cash — Department Sheet</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main id="main-content" class="ml-56 min-h-screen p-8 transition-all duration-300">

        <!-- Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Petty Cash — Department Sheet</h1>
                <p class="text-sm text-gray-400 mt-1">Entries from Sheet Two, filtered by department</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Sheet Tabs -->
                <div class="flex items-center bg-white border border-gray-200 rounded-lg p-1 shadow-sm gap-1">
                    <a id="tab-general" href="<?= BASE_URL ?>/accountingcustodianpettycash"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[9px]"></i> General Sheet
                    </a>
                    <a id="tab-custodian" href="<?= BASE_URL ?>/accountingcustodianpettycashtwo"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 transition flex items-center gap-1.5">
                        General Sheet Two <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                    <span class="text-xs font-bold text-white bg-orange-500 px-3 py-1.5 rounded-md">Department Sheet</span>
                </div>
                <!-- Month Filter -->
                <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-1.5 shadow-sm">
                    <i class="fa-solid fa-calendar text-orange-400 text-xs"></i>
                    <input type="month" id="filter-month"
                        class="text-xs font-semibold text-gray-600 outline-none border-none bg-transparent"
                        value="<?= date('Y-m') ?>">
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Department</p>
                <p id="card-dept" class="text-lg font-bold text-orange-500 truncate">—</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Actual</p>
                <p id="card-actual" class="text-xl font-bold text-red-500">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total VATable</p>
                <p id="card-vatable" class="text-xl font-bold text-purple-600">₱ 0.00</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total Entries</p>
                <p id="card-entries" class="text-xl font-bold text-blue-600">0</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <!-- Filters row: Department dropdown + Account Title filter -->
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 flex-wrap">

                <!-- Department Dropdown -->
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-building text-orange-400 text-xs"></i>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Department</span>
                    <select id="filter-department"
                        class="text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 outline-none cursor-pointer hover:border-orange-300 transition">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <div class="w-px h-5 bg-gray-200"></div>

                <!-- Account Title Dropdown -->
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-tag text-orange-400 text-xs"></i>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Account Title</span>
                    <select id="filter-account-title"
                        class="text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 outline-none cursor-pointer hover:border-orange-300 transition">
                        <option value="">All Account Titles</option>
                    </select>
                </div>

                <!-- Active account title badge -->
                <span id="filter-badge" class="hidden items-center gap-1 text-[10px] font-bold text-orange-600 bg-orange-50 border border-orange-200 rounded-full px-2.5 py-0.5">
                    <span id="filter-badge-text"></span>
                    <button onclick="clearAccountFilter()" class="ml-1 hover:text-orange-800 transition">
                        <i class="fa-solid fa-xmark text-[9px]"></i>
                    </button>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse" style="min-width: 1800px;">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest">
                            <th class="px-1 py-1 text-center border-r border-orange-400 w-10">No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-24">Date</th>
                            <th class="px-1 py-1 text-center border-r border-orange-400 w-24">Ref No.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-36">Account Title</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400">Particulars</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-28">Dept.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-24">In-Charge</th>
                            <th class="px-1 py-1 text-right border-r border-orange-400 w-28">Actual</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-32">Supplier (Corp)</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-32">Supplier (Indiv)</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-28">Address</th>
                            <th class="px-1 py-1 text-center border-r border-orange-400 w-24">TIN</th>
                            <th class="px-1 py-1 text-right border-r border-orange-400 w-24">VATable</th>
                            <th class="px-1 py-1 text-right border-r border-orange-400 w-20">VAT</th>
                            <th class="px-1 py-1 text-right border-r border-orange-400 w-24">Total</th>
                            <th class="px-1 py-1 text-right border-r border-orange-400 w-24">Non-VAT</th>
                            <th class="px-1 py-1 text-center border-r border-orange-400 w-28">No Sales Inv.</th>
                            <th class="px-1 py-1 text-left border-r border-orange-400 w-24">Added By</th>
                            <th class="px-1 py-1 text-right w-24">VAT Exempt</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-tbody">
                        <tr>
                            <td colspan="19" class="px-5 py-10 text-center text-gray-400 text-sm">
                                <i class="fa-solid fa-building text-2xl mb-2 block"></i>Select a department to view entries
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="7"
                                class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-gray-500 border-r border-gray-200">
                                Total</td>
                            <td id="foot-actual"
                                class="px-4 py-3 text-right font-bold font-mono text-red-500 border-r border-gray-200 text-xs whitespace-nowrap"></td>
                            <td colspan="4" class="border-r border-gray-200"></td>
                            <td id="foot-vatable"
                                class="px-4 py-3 text-right font-bold font-mono text-purple-600 border-r border-gray-200 text-xs whitespace-nowrap"></td>
                            <td id="foot-vat"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-600 border-r border-gray-200 text-xs whitespace-nowrap"></td>
                            <td id="foot-total"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-800 border-r border-gray-200 text-xs whitespace-nowrap"></td>
                            <td id="foot-nonvat"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-600 border-r border-gray-200 text-xs whitespace-nowrap"></td>
                            <td class="border-r border-gray-200"></td>
                            <td id="foot-vatexempt"
                                class="px-4 py-3 text-right font-bold font-mono text-gray-600 text-xs whitespace-nowrap"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </main>

    <script>
        let activeDept = null;
        let allDepartments = [];

        const BASE_URL_JS = '<?= BASE_URL ?>';
        const fmt = v => '₱ ' + parseFloat(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });

        // ── Load Department Dropdown ───────────────────────────────
        function loadDepartmentTabs() {
            fetch(`${BASE_URL_JS}/fetchpettycashdepartment`)
                .then(r => r.json())
                .then(data => {
                    allDepartments = data;
                    const sel = document.getElementById('filter-department');

                    if (!data.length) {
                        sel.innerHTML = '<option value="">No departments found</option>';
                        document.getElementById('sheet-tbody').innerHTML =
                            '<tr><td colspan="19" class="px-5 py-10 text-center text-gray-400 text-sm"><i class="fa-solid fa-building text-2xl mb-2 block"></i>No departments yet.</td></tr>';
                        return;
                    }

                    sel.innerHTML = data.map(d =>
                        `<option value="${d.id}" data-name="${d.name}">${d.name}</option>`
                    ).join('');

                    // Auto-select from URL or first dept
                    const urlParams = new URLSearchParams(window.location.search);
                    const deptParam = urlParams.get('dept');
                    const match = data.find(d => d.name === deptParam);

                    if (match) {
                        sel.value = match.id;
                        switchDept(match);
                    } else {
                        sel.value = data[0].id;
                        switchDept(data[0]);
                    }
                });
        }

        function switchDept(dept) {
            activeDept = dept;
            document.getElementById('card-dept').textContent = dept.name;
            resetAccountFilter();
            fetchEntries();

            const url = new URL(window.location);
            url.searchParams.set('dept', dept.name);
            url.searchParams.set('month', document.getElementById('filter-month').value);
            window.history.replaceState({}, '', url);
        }

        // ── Account Title Filter Helpers ───────────────────────────
        function populateAccountTitles(data) {
            const select = document.getElementById('filter-account-title');
            const current = select.value;
            const titles = [...new Set(data.map(r => (r.account_title ?? '').trim()).filter(Boolean))].sort();
            select.innerHTML = '<option value="">All Account Titles</option>' +
                titles.map(t => `<option value="${t}"${t === current ? ' selected' : ''}>${t}</option>`).join('');
        }

        function resetAccountFilter() {
            document.getElementById('filter-account-title').innerHTML = '<option value="">All Account Titles</option>';
            document.getElementById('filter-badge').classList.add('hidden');
            document.getElementById('filter-badge').classList.remove('flex');
        }

        function clearAccountFilter() {
            document.getElementById('filter-account-title').value = '';
            updateFilterBadge('');
            fetchEntries();
        }

        function updateFilterBadge(val) {
            const badge = document.getElementById('filter-badge');
            if (val) {
                document.getElementById('filter-badge-text').textContent = val;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        // ── Fetch Entries ──────────────────────────────────────────
        function fetchEntries() {
            if (!activeDept) return;
            const month = document.getElementById('filter-month').value;

            fetch(`${BASE_URL_JS}/fetchcustodiansheetpettycashtwo?month=${month}`)
                .then(r => r.json())
                .then(data => {
                    const filtered = data.filter(row =>
                        (row.project_department ?? '').trim().toLowerCase() === activeDept.name.trim().toLowerCase()
                    );

                    populateAccountTitles(filtered);

                    const acctFilter = document.getElementById('filter-account-title').value;
                    const display = acctFilter
                        ? filtered.filter(row => (row.account_title ?? '').trim() === acctFilter)
                        : filtered;

                    renderTable(display);
                    renderSummary(display);
                })
                .catch(err => console.error(err));
        }

        // ── Render Table ───────────────────────────────────────────
        function renderTable(data) {
            const tbody = document.getElementById('sheet-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="19" class="px-5 py-10 text-center text-gray-400 text-sm">
                    <i class="fa-solid fa-table text-2xl mb-2 block"></i>No entries for <strong>${activeDept ? activeDept.name : 'this department'}</strong> this month.</td></tr>`;
                return;
            }
            const money = val => val && parseFloat(val) !== 0
                ? '₱ ' + parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                : '';
            tbody.innerHTML = data.map((row, idx) => `
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-1 py-1 text-center text-xs text-gray-400 font-mono border-r border-gray-100">${idx + 1}</td>
                    <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100 whitespace-nowrap">${row.date ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-center font-mono text-gray-600 border-r border-gray-100">${row.reference_no ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100">${row.account_title ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-700 border-r border-gray-100">${row.particulars ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100">${row.project_department ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100">${row.in_charge ?? ''}</td>
                    <td class="px-1 py-1 text-xs font-mono text-right text-red-600 border-r border-gray-100 font-semibold whitespace-nowrap">${money(row.actual)}</td>
                    <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100">${row.supplier_name_corp ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100">${row.supplier_name_indiv ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-500 border-r border-gray-100">${row.address ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-center font-mono text-gray-600 border-r border-gray-100">${row.tin ?? ''}</td>
                    <td class="px-1 py-1 text-xs font-mono text-right text-purple-600 border-r border-gray-100 whitespace-nowrap">${money(row.vatable_amount)}</td>
                    <td class="px-1 py-1 text-xs font-mono text-right text-gray-600 border-r border-gray-100 whitespace-nowrap">${money(row.vat)}</td>
                    <td class="px-1 py-1 text-xs font-mono text-right text-gray-800 font-semibold border-r border-gray-100 whitespace-nowrap">${money(row.total)}</td>
                    <td class="px-1 py-1 text-xs font-mono text-right text-gray-600 border-r border-gray-100 whitespace-nowrap">${money(row.non_vat)}</td>
                    <td class="px-1 py-1 text-xs text-center text-gray-600 border-r border-gray-100">${row.no_sales_invoice ?? ''}</td>
                    <td class="px-1 py-1 text-xs text-gray-600 border-r border-gray-100">${row.inserted_by ?? '—'}</td>
                    <td class="px-1 py-1 text-xs font-mono text-right text-gray-600 whitespace-nowrap">${money(row.vat_exempt)}</td>
                </tr>`).join('');
        }

        // ── Summary ────────────────────────────────────────────────
        function renderSummary(data) {
            let actual = 0, vatable = 0, vat = 0, total = 0, nonvat = 0, vatexempt = 0;
            data.forEach(row => {
                actual    += parseFloat(row.actual || 0);
                vatable   += parseFloat(row.vatable_amount || 0);
                vat       += parseFloat(row.vat || 0);
                total     += parseFloat(row.total || 0);
                nonvat    += parseFloat(row.non_vat || 0);
                vatexempt += parseFloat(row.vat_exempt || 0);
            });
            document.getElementById('card-actual').textContent    = fmt(actual);
            document.getElementById('card-vatable').textContent   = fmt(vatable);
            document.getElementById('card-entries').textContent   = data.length;
            document.getElementById('foot-actual').textContent    = fmt(actual);
            document.getElementById('foot-vatable').textContent   = fmt(vatable);
            document.getElementById('foot-vat').textContent       = fmt(vat);
            document.getElementById('foot-total').textContent     = fmt(total);
            document.getElementById('foot-nonvat').textContent    = fmt(nonvat);
            document.getElementById('foot-vatexempt').textContent = fmt(vatexempt);
        }

        // ── Init ───────────────────────────────────────────────────
        const urlParams = new URLSearchParams(window.location.search);
        const monthParam = urlParams.get('month');
        if (monthParam) document.getElementById('filter-month').value = monthParam;

        document.getElementById('filter-month').addEventListener('change', () => {
            resetAccountFilter();
            fetchEntries();
            const url = new URL(window.location);
            url.searchParams.set('month', document.getElementById('filter-month').value);
            window.history.replaceState({}, '', url);
        });

        document.getElementById('filter-department').addEventListener('change', function () {
            const selected = allDepartments.find(d => d.id == this.value);
            if (selected) switchDept(selected);
        });

        document.getElementById('filter-account-title').addEventListener('change', function () {
            updateFilterBadge(this.value);
            fetchEntries();
        });

        loadDepartmentTabs();
    </script>

</body>
</html>