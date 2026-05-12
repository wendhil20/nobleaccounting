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
</head>
<body class="bg-slate-100">
<main class="ml-56 min-h-screen p-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Project Monitoring</h1>
            <p class="text-sm text-gray-400 mt-1">Accounting Report</p>
        </div>
        <button onclick="openAddModal()"
            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
            <i class="fa-solid fa-plus"></i> New Project
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-700">All Projects</span>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
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
                        <th class="px-5 py-3 text-left">Contract Amount</th>
                        <th class="px-5 py-3 text-left">Sales Person</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody id="projects-tbody">
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add Project Modal -->
<div id="add-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 overflow-y-auto">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl my-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-sm uppercase tracking-widest text-gray-800">New Project</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="px-6 py-5">
            <!-- Basic Info -->
            <div class="mb-4">
                <div class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded mb-3 inline-block">
                    Basic Information
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Project Name</label>
                        <input id="f-project-name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Job Order</label>
                        <input id="f-job-order" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Project Scope</label>
                        <input id="f-project-scope" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Purchase Order</label>
                        <input id="f-purchase-order" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Client Name</label>
                        <input id="f-client-name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Notice to Proceed</label>
                        <input id="f-notice-to-proceed" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Contract Amount</label>
                        <input id="f-contract-amount" type="number" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">(1) Billing Order #</label>
                        <input id="f-billing-order-1" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Sales Person</label>
                        <input id="f-sales-person" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">(2) Billing Order #</label>
                        <input id="f-billing-order-2" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Address</label>
                        <input id="f-address" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1 block">Status</label>
                        <select id="f-status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-400">
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
            <button onclick="closeAddModal()" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded border border-gray-200">Cancel</button>
            <button onclick="saveProject()"
                class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Save Project
            </button>
        </div>
    </div>
</div>

<script>
    let allProjects = [];

    function fetchProjects() {
        fetch('<?= BASE_URL ?>/fetchprojects')
            .then(res => res.json())
            .then(data => {
                allProjects = data;
                renderProjects(data);
            });
    }

    function renderProjects(data) {
        const tbody = document.getElementById('projects-tbody');
        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No projects yet.</td></tr>`;
            return;
        }

        const statusColors = {
            'Ongoing':   'bg-blue-100 text-blue-700',
            'Completed': 'bg-green-100 text-green-700',
            'On Hold':   'bg-yellow-100 text-yellow-700',
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

            return `
            <tr class="border-t border-gray-100 hover:bg-orange-50 transition-colors cursor-pointer"
                onclick="window.location='<?= BASE_URL ?>/projectdetail?id=${row.id}'">
                <td class="px-5 py-3 font-mono text-xs text-orange-500">${row.reference_no ?? '—'}</td>
                <td class="px-5 py-3 font-medium text-gray-800">${row.project_name}</td>
                <td class="px-5 py-3 text-gray-600">${row.client_name ?? '—'}</td>
                <td class="px-5 py-3 font-mono text-xs text-gray-700">${amount}</td>
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

    function openAddModal(id = null) {
        // Clear fields
        ['project-name','job-order','project-scope','purchase-order','client-name',
         'notice-to-proceed','contract-amount','billing-order-1','sales-person',
         'billing-order-2','address','status'].forEach(f => {
            document.getElementById('f-' + f).value = '';
        });

        if (id) {
            const row = allProjects.find(p => p.id == id);
            if (row) {
                document.getElementById('f-project-name').value      = row.project_name ?? '';
                document.getElementById('f-job-order').value         = row.job_order ?? '';
                document.getElementById('f-project-scope').value     = row.project_scope ?? '';
                document.getElementById('f-purchase-order').value    = row.purchase_order ?? '';
                document.getElementById('f-client-name').value       = row.client_name ?? '';
                document.getElementById('f-notice-to-proceed').value = row.notice_to_proceed ?? '';
                document.getElementById('f-contract-amount').value   = row.contract_amount ?? '';
                document.getElementById('f-billing-order-1').value   = row.billing_order_1 ?? '';
                document.getElementById('f-sales-person').value      = row.sales_person ?? '';
                document.getElementById('f-billing-order-2').value   = row.billing_order_2 ?? '';
                document.getElementById('f-address').value           = row.address ?? '';
                document.getElementById('f-status').value            = row.status ?? '';
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
            id:                editId ? parseInt(editId) : null,
            project_name:      document.getElementById('f-project-name').value,
            job_order:         document.getElementById('f-job-order').value,
            project_scope:     document.getElementById('f-project-scope').value,
            purchase_order:    document.getElementById('f-purchase-order').value,
            client_name:       document.getElementById('f-client-name').value,
            notice_to_proceed: document.getElementById('f-notice-to-proceed').value,
            contract_amount:   document.getElementById('f-contract-amount').value,
            billing_order_1:   document.getElementById('f-billing-order-1').value,
            sales_person:      document.getElementById('f-sales-person').value,
            billing_order_2:   document.getElementById('f-billing-order-2').value,
            address:           document.getElementById('f-address').value,
            status:            document.getElementById('f-status').value,
        };

        if (!payload.project_name) {
            alert('Project Name is required.');
            return;
        }

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

    fetchProjects();
    setInterval(fetchProjects, 15000);
</script>
</body>
</html>