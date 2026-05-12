<?php
// index-staff-main.php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';
?>
<!DOCTYPE html>

<body lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accounting Dashboard</title>
        <?php include ROOT_PATH . '/link/top.php'; ?>
        <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
    </head>

    <body class="bg-slate-100">
        <main class="ml-56 min-h-screen p-8">

            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-800">For Receiving</h1>
                <p class="text-sm text-gray-400 mt-1">Approved requests pending for receive</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <span class="text-sm font-semibold text-gray-700">Approved Requests</span>
                    <div class="flex items-center gap-2">
                        <span id="last-updated" class="text-[10px] text-gray-400"></span>
                        <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                                <th class="px-5 py-3 text-left">Control No.</th>
                                <th class="px-5 py-3 text-left">Requestor</th>
                                <th class="px-5 py-3 text-left">Purpose</th>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Total</th>
                                <th class="px-5 py-3 text-left">Approved By</th>
                                <th class="px-5 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody id="accounting-tbody">
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
        <!-- View Modal (read-only) -->
        <div id="view-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 overflow-y-auto">
            <div class="bg-white w-full max-w-5xl rounded-sm shadow-xl border border-gray-300 my-auto">

                <!-- Header -->
                <div class="grid grid-cols-[1fr_auto] border-b-2 border-gray-800">
                    <div class="flex items-center gap-4 px-3 py-3 border-r-2 border-gray-800">
                        <div class="w-14 h-14 shrink-0">
                            <img src="<?= BASE_URL ?>/icon/logo.png" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <div class="w-px h-12 bg-gray-400"></div>
                        <div class="flex-1">
                            <h1 class="font-bold text-sm uppercase tracking-wide leading-tight">Noblehome Construction
                                Corporation</h1>
                            <p class="text-[10px] text-gray-500 mt-1 leading-relaxed">
                                1181 MC Premiere Bldg., EDSA Balintawak Quezon City<br>
                                noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell. No. 0968-591-6544
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center justify-between px-4 py-2 border-b-2 border-gray-800 gap-4">
                            <h2 class="font-bold text-sm uppercase tracking-widest whitespace-nowrap">Budget Request
                                Form</h2>
                            <button onclick="closeViewModal()"
                                class="text-gray-400 hover:text-red-500 transition-colors p-1">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="flex flex-row flex-1 text-[10px]">
                            <div class="flex flex-col border-r-2 border-gray-800 flex-1">
                                <span
                                    class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Control
                                    No.</span>
                                <p id="view-control-no"
                                    class="flex-1 px-4 py-1 font-mono text-xs text-center bg-gray-50 min-w-[180px]"></p>
                            </div>
                            <div class="flex flex-col flex-1">
                                <span
                                    class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Date:</span>
                                <p id="view-date"
                                    class="flex-1 px-4 py-1 font-mono text-xs text-center bg-gray-50 min-w-[150px]"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requestor + Purpose -->
                <div class="grid grid-cols-2 border-b-2 border-gray-800">
                    <div class="flex items-center gap-2 px-6 py-3 border-r-2 border-gray-800">
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Requestor
                            Name:</span>
                        <p id="view-requestor" class="text-sm text-gray-800"></p>
                    </div>
                    <div class="flex items-center gap-2 px-6 py-3">
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Purpose
                            of Request:</span>
                        <p id="view-purpose" class="text-sm text-gray-800"></p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto max-h-[280px] overflow-y-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead class="sticky top-0 z-10">
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
                        <tbody id="view-items-tbody"></tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-800 bg-gray-50">
                                <td colspan="5"
                                    class="px-4 py-2 font-bold text-xs uppercase tracking-widest text-right border-r border-gray-300">
                                    Total:</td>
                                <td id="view-total" class="px-4 py-2 font-bold font-mono text-right"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Signatures -->
                <div class="grid grid-cols-2 border-t-2 border-gray-800">
                    <div class="px-5 py-4 border-r-2 border-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Approved
                                By:</span>
                            <div id="view-status-badge"></div>
                        </div>
                        <div id="view-approved-by" class="text-center"></div>
                        <div class="border-b-2 border-gray-400 mb-1 mt-6"></div>
                        <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">Head</p>
                    </div>
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Received
                                By:</span>
                        </div>
                        <div id="view-received-by" class="text-center"></div>
                        <div class="border-b-2 border-gray-400 mb-1 mt-6"></div>
                        <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">&nbsp;</p>
                    </div>
                </div>

            </div>
        </div>

        <script>
            let prevCount = 0;

            function viewRequest(row) {
                document.getElementById('view-control-no').textContent = row.control_no;
                document.getElementById('view-date').textContent = row.date_requested;
                document.getElementById('view-requestor').textContent = row.requestor_name;
                document.getElementById('view-purpose').textContent = row.purpose;

                const items = row.items ?? [];
                let total = 0;
                document.getElementById('view-items-tbody').innerHTML = items.map((item, i) => {
                    const amount = parseFloat(item.amount) || 0;
                    total += amount;
                    return `
            <tr class="border-t border-gray-200">
                <td class="px-3 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-200 w-10">${i + 1}</td>
                <td class="px-4 py-2 border-r border-gray-200 min-w-[180px]">${item.description || ''}</td>
                <td class="px-4 py-2 border-r border-gray-200 min-w-[150px]">${item.purpose || ''}</td>
                <td class="px-4 py-2 border-r border-gray-200 text-center w-24">${item.quantity || 0}</td>
                <td class="px-4 py-2 border-r border-gray-200 text-right font-mono w-32">${parseFloat(item.unit_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-2 border-r border-gray-200 text-right font-mono w-32">₱ ${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-2 min-w-[120px]">${item.notes || ''}</td>
            </tr>`;
                }).join('');

                document.getElementById('view-total').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

                function statusBadge(status) {
                    const map = {
                        pending: 'bg-yellow-100 text-yellow-700',
                        approved: 'bg-green-300 text-green-900',
                        rejected: 'bg-red-100 text-red-700',
                    };
                    return `<span class="${map[status] ?? 'bg-gray-100 text-gray-500'} text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">${status}</span>`;
                }

                document.getElementById('view-status-badge').innerHTML = statusBadge(row.status);

                const approverName = row.approver_name ?? '';
                const approvedAt = row.approved_at
                    ? new Date(row.approved_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
                    + ' ' + new Date(row.approved_at).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true })
                    : '';

                document.getElementById('view-approved-by').innerHTML = approverName
                    ? `<p class="text-sm font-semibold text-gray-800">${approverName}</p>
               <p class="text-[10px] text-gray-400">${approvedAt}</p>`
                    : '';

                const receiverName = row.receiver_name ?? '';
                const receivedAt = row.received_at
                    ? new Date(row.received_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
                    + ' ' + new Date(row.received_at).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true })
                    : '';

                document.getElementById('view-received-by').innerHTML = receiverName
                    ? `<p class="text-sm font-semibold text-gray-800">${receiverName}</p>
               <p class="text-[10px] text-gray-400">${receivedAt}</p>`
                    : '';

                document.getElementById('view-modal').classList.remove('hidden');
            }

            function closeViewModal() {
                document.getElementById('view-modal').classList.add('hidden');
            }



            function fetchApproved() {
                fetch('<?= BASE_URL ?>/fetchapproved')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById('accounting-tbody');

                        if (!data.length) {
                            tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No pending requests for receiving.</td></tr>`;
                            prevCount = 0;
                            return;
                        }

                        if (data.length === prevCount) return;
                        prevCount = data.length;

                        tbody.innerHTML = data.map(row => {
                            // Compute total
                            const items = row.items ?? [];
                            const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);

                            return `
                    <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-blue-500">${row.control_no}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">${row.requestor_name}</p>
                            <p class="text-[10px] text-gray-400">${row.sender_email ?? ''}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600">${row.purpose}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 font-mono">${row.date_requested}</td>
                        <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
                            ₱ ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-sm text-gray-700">${row.approver_name ?? ''}</p>
                            <p class="text-[10px] text-gray-400">${row.approved_at
                                    ? new Date(row.approved_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                                    + ' ' + new Date(row.approved_at.replace(' ', 'T')).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true })
                                    : ''}</p>
                        </td>
                       <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <button onclick='viewRequest(${JSON.stringify(row).replace(/"/g, "&quot;")})' class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                                    <i class="fa-solid fa-eye mr-1"></i>View
                                </button>
                                <button onclick="markReceived(${row.id})" class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                                    <i class="fa-solid fa-check mr-1"></i>Mark as Received
                                </button>
                            </div>
                        </td>
                    </tr>`;
                        }).join('');

                        document.getElementById('last-updated').textContent =
                            'Updated ' + new Date().toLocaleTimeString('en-PH');
                    });
            }

            function markReceived(id) {
                fetch('<?= BASE_URL ?>/markreceived', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Request marked as received!', 'received');
                            prevCount = 0;
                            fetchApproved();
                        }
                    });
            }

            function showToast(message, type = 'received') {
                const existing = document.getElementById('toast-notif');
                if (existing) existing.remove();

                const color = type === 'received' ? 'bg-blue-500' : 'bg-green-500';
                const icon = type === 'received' ? 'fa-circle-check' : 'fa-check';

                const toast = document.createElement('div');
                toast.id = 'toast-notif';
                toast.className = `fixed bottom-6 right-6 z-[999] flex items-center gap-3 ${color} text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg transition-all duration-300 opacity-0`;
                toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
                document.body.appendChild(toast);

                setTimeout(() => toast.classList.replace('opacity-0', 'opacity-100'), 10);
                setTimeout(() => {
                    toast.classList.replace('opacity-100', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            fetchApproved();

            // Realtime — every 5 seconds mag-refresh
            setInterval(fetchApproved, 5000);

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    prevCount = 0;
                    fetchApproved();
                }
            });
        </script>
    </body>

    </html>