<?php
// index-my-request-history.php

include ROOT_PATH . '/network/connect.php';
if (empty($_SESSION['logged_in'])) { header('Location: ' . BASE_URL . '/'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests — NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/user/navigation/top.php'; ?>
</head>
<body class="min-h-screen px-4 py-12 relative"
    style="background-image: url('<?= BASE_URL ?>/icon/building2.png'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 bg-black/50 z-0"></div>
    <div class="max-w-5xl mx-auto relative z-10 pt-16">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-white">My Requests</h1>
            <p class="text-sm text-white/60 mt-1">All budget requests you have submitted</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Request History</span>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="search-input" placeholder="Search..."
                            class="pl-8 pr-4 py-1.5 text-xs border border-gray-200 rounded-full outline-none focus:border-amber-400 transition-all w-48">
                    </div>
                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                            <th class="px-5 py-3 text-left">Control No.</th>
                            <th class="px-5 py-3 text-left">Purpose</th>
                            <th class="px-5 py-3 text-left">Total Amount</th>
                            <th class="px-5 py-3 text-left">Date Submitted</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody">
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="view-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 overflow-y-auto">
        <div class="bg-white w-full max-w-4xl rounded-sm shadow-xl border border-gray-300 my-auto">

            <div class="grid grid-cols-[1fr_auto] border-b-2 border-gray-800">
                <div class="flex items-center gap-4 px-3 py-3 border-r-2 border-gray-800">
                    <img src="<?= BASE_URL ?>/icon/logo.png" alt="Logo" class="w-12 h-12 object-contain">
                    <div class="w-px h-12 bg-gray-400"></div>
                    <div>
                        <p class="font-bold text-sm uppercase tracking-wide">Noblehome Construction Corporation</p>
                        <p class="text-[10px] text-gray-500 mt-1 leading-relaxed">
                            1181 MC Premiere Bldg., EDSA Balintawak Quezon City<br>
                            noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell. No. 0968-591-6544
                        </p>
                    </div>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center justify-between px-4 py-2 border-b-2 border-gray-800 gap-4">
                        <h2 class="font-bold text-sm uppercase tracking-widest whitespace-nowrap">Budget Request Form</h2>
                        <button onclick="closeViewModal()" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="flex flex-row flex-1 text-[10px]">
                        <div class="flex flex-col border-r-2 border-gray-800 flex-1">
                            <span class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Control No.</span>
                            <p id="v-control-no" class="flex-1 px-4 py-1 font-mono text-xs text-center bg-gray-50 min-w-[180px]"></p>
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Date:</span>
                            <p id="v-date" class="flex-1 px-4 py-1 font-mono text-xs text-center bg-gray-50 min-w-[150px]"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 border-b-2 border-gray-800">
                <div class="flex items-center gap-2 px-6 py-3 border-r-2 border-gray-800">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Requestor Name:</span>
                    <p id="v-requestor" class="text-sm text-gray-800"></p>
                </div>
                <div class="flex items-center gap-2 px-6 py-3">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Purpose of Request:</span>
                    <p id="v-purpose" class="text-sm text-gray-800"></p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-orange-500 text-white text-[11px] font-bold uppercase tracking-wider">
                            <th class="w-10 px-3 py-2 border-r border-orange-400 text-center">No.</th>
                            <th class="px-4 py-2 border-r border-orange-400 text-left">Items / Description</th>
                            <th class="px-4 py-2 border-r border-orange-400 text-left">Purpose</th>
                            <th class="w-24 px-4 py-2 border-r border-orange-400 text-center">Quantity</th>
                            <th class="w-28 px-4 py-2 border-r border-orange-400 text-center">Unit Price</th>
                            <th class="w-28 px-4 py-2 border-r border-orange-400 text-center">Amount</th>
                            <th class="px-4 py-2 text-left">Notes</th>
                        </tr>
                    </thead>
                    <tbody id="v-items-tbody"></tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-800 bg-gray-50">
                            <td colspan="5" class="px-4 py-2 font-bold text-xs uppercase tracking-widest text-right border-r border-gray-300">Total:</td>
                            <td id="v-total" class="px-4 py-2 font-bold font-mono text-right"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="grid grid-cols-2 border-t-2 border-gray-800">
                <div class="px-5 py-4 border-r-2 border-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Approved By:</span>
                        <span id="v-status-badge"></span>
                    </div>
                    <div id="v-approved-by" class="text-center"></div>
                    <div class="border-b-2 border-gray-400 mb-1 mt-6"></div>
                    <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">Head</p>
                </div>
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Received By:</span>
                    </div>
                    <div id="v-received-by" class="text-center"></div>
                    <div class="border-b-2 border-gray-400 mb-1 mt-6"></div>
                    <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">&nbsp;</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allData = [];

        const statusMap = {
            pending:  { label: 'Pending',  cls: 'bg-yellow-100 text-yellow-700' },
            approved: { label: 'Approved', cls: 'bg-green-100 text-green-700' },
            rejected: { label: 'Rejected', cls: 'bg-red-100 text-red-700' },
        };

        function statusBadge(status) {
            const s = statusMap[status] ?? { label: status, cls: 'bg-gray-100 text-gray-500' };
            return `<span class="${s.cls} text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">${s.label}</span>`;
        }

        function fetchHistory() {
            fetch('<?= BASE_URL ?>/fetchmyrequests')
                .then(res => res.json())
                .then(data => {
                    allData = data;
                    renderTable(data);
                });
        }

        function renderTable(data) {
            const tbody = document.getElementById('history-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No requests submitted yet.</td></tr>`;
                return;
            }
            tbody.innerHTML = data.map(row => {
                const items = row.items ?? [];
                const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
                const date = row.date_requested
                    ? new Date(row.date_requested).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                    : '—';
                return `
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-mono text-xs text-blue-500 cursor-pointer underline"
                        onclick='viewRequest(${JSON.stringify(row).replace(/"/g, "&quot;")})'>${row.control_no}</td>
                    <td class="px-5 py-3 text-gray-600">${row.purpose}</td>
                    <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
                        PhP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">${date}</td>
                    <td class="px-5 py-3">${statusBadge(row.status)}</td>
                    <td class="px-5 py-3">
                        <button onclick='viewRequest(${JSON.stringify(row).replace(/"/g, "&quot;")})'
                            class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                            <i class="fa-solid fa-eye mr-1"></i>View
                        </button>
                    </td>
                </tr>`;
            }).join('');
        }

        function viewRequest(row) {
            const items = row.items ?? [];
            let total = 0;
            document.getElementById('v-control-no').textContent = row.control_no;
            document.getElementById('v-date').textContent       = row.date_requested;
            document.getElementById('v-requestor').textContent  = row.requestor_name;
            document.getElementById('v-purpose').textContent    = row.purpose;
            document.getElementById('v-status-badge').innerHTML = statusBadge(row.status);

            document.getElementById('v-items-tbody').innerHTML = items.map((item, i) => {
                const amount = parseFloat(item.amount) || 0;
                total += amount;
                return `<tr class="border-t border-gray-200">
                    <td class="px-3 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-200">${i + 1}</td>
                    <td class="px-4 py-2 border-r border-gray-200">${item.description || ''}</td>
                    <td class="px-4 py-2 border-r border-gray-200">${item.purpose || ''}</td>
                    <td class="px-4 py-2 border-r border-gray-200 text-center">${item.quantity || 0}</td>
                    <td class="px-4 py-2 border-r border-gray-200 text-right font-mono">${parseFloat(item.unit_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                    <td class="px-4 py-2 border-r border-gray-200 text-right font-mono">₱ ${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                    <td class="px-4 py-2">${item.notes || ''}</td>
                </tr>`;
            }).join('');

            document.getElementById('v-total').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            const fmtDate = str => str
                ? new Date(str.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
                  + ' ' + new Date(str.replace(' ', 'T')).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true })
                : '';

            document.getElementById('v-approved-by').innerHTML = row.approver_name
                ? `<p class="text-sm font-semibold text-gray-800">${row.approver_name}</p>
                   <p class="text-[10px] text-gray-400">${fmtDate(row.approved_at)}</p>`
                : '<p class="text-xs text-gray-300 italic">Not yet approved</p>';

            document.getElementById('v-received-by').innerHTML = row.receiver_name
                ? `<p class="text-sm font-semibold text-gray-800">${row.receiver_name}</p>
                   <p class="text-[10px] text-gray-400">${fmtDate(row.received_at)}</p>`
                : '<p class="text-xs text-gray-300 italic">Not yet received</p>';

            document.getElementById('view-modal').classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('view-modal').classList.add('hidden');
        }

        // Search
        document.getElementById('search-input').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            renderTable(allData.filter(row =>
                row.control_no?.toLowerCase().includes(q) ||
                row.purpose?.toLowerCase().includes(q) ||
                row.status?.toLowerCase().includes(q)
            ));
        });

        fetchHistory();
        setInterval(fetchHistory, 10000);
    </script>
</body>
</html>