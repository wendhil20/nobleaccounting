<?php
// index-staff-dashboard.php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
$allowedPositions = [POSITION_STAFF];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Central Dashboard</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">Received Requests</h1>
            <p class="text-sm text-gray-400 mt-1">All requests that have been received</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Received List</span>
                <div class="flex items-center gap-3">
                    <!-- Search Bar -->
                    <div class="relative">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="search-input" placeholder="Search..."
                            class="pl-8 pr-4 py-1.5 text-xs border border-gray-200 rounded-full outline-none focus:border-amber-400 transition-all w-48">
                    </div>
                    <span id="last-updated" class="text-[10px] text-gray-400"></span>
                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                </div>
            </div>

            <!-- Scrollable Table -->
            <div class="overflow-x-auto">
                <div class="max-h-[600px] overflow-y-auto scrollbar-thin">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-gray-50 text-sm font-semibold text-gray-800 uppercase">
                                <th class="px-5 py-3 text-left">Control No.</th>
                                <th class="px-5 py-3 text-left">Requestor</th>
                                <th class="px-5 py-3 text-left">Purpose</th>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Total</th>
                                <th class="px-5 py-3 text-left">Approved By</th>
                                <th class="px-5 py-3 text-left">Received By</th>
                                <th class="px-5 py-3 text-left">Received At</th>
                                <th class="px-5 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="received-tbody">
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-gray-400 text-md">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <style>
        @media print {
            body>* {
                display: none !important;
            }

            #print-modal {
                display: block !important;
                position: fixed;
                inset: 0;
                background: white;
                z-index: 9999;
                overflow: visible;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            #printable-area {
                display: block !important;
                padding: 24px;
            }
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 999px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>


    <!-- Print Modal -->
    <div id="print-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 overflow-y-auto">
        <div class="bg-white w-full max-w-4xl rounded-xl shadow-xl my-auto">

            <!-- Modal Controls — hindi kasama sa print -->
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 no-print">
                <span class="text-sm font-semibold text-gray-700">Print Preview</span>
                <div class="flex items-center gap-2">
                    <button onclick="triggerPrint()"
                        class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                    <button onclick="closePrintModal()" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Printable Content -->
            <div id="printable-area" class="p-6">
                <div style="border:1px solid #111; border-radius:2px;">
                    <!-- Header -->
                    <div style="display:grid; grid-template-columns:1fr auto;">
                        <div
                            style="display:flex; align-items:center; gap:12px; padding:10px; border-right:1px solid #111;">
                            <img src="<?= BASE_URL ?>/icon/logo.png"
                                style="width:56px; height:56px; object-fit:contain;">
                            <div style="width:1px; height:48px; background:#9ca3af;"></div>
                            <div>
                                <div
                                    style="font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Noblehome Construction Corporation</div>
                                <div style="font-size:9px; color:#555; margin-top:4px; line-height:1.6;">
                                    1181 MC Premiere Bldg., EDSA Balintawak Quezon City<br>
                                    noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell. No. 0968-591-6544
                                </div>
                            </div>
                        </div>
                        <div
                            style="display:flex; flex-direction:column; min-width:240px; border-bottom:1px solid #111;">
                            <div
                                style="font-weight:700; font-size:15px; text-transform:uppercase; letter-spacing:3px; padding:8px 14px; border-bottom:2px solid #111; text-align:right;">
                                Budget Request Form</div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; flex:1;">
                                <div style="display:flex; flex-direction:column; border-right:1px solid #e5e7eb;">
                                    <div
                                        style="background:#f97316; color:white; font-weight:700; font-size:9px; text-align:center; padding:3px 6px; text-transform:uppercase; letter-spacing:1px;">
                                        Control No.</div>
                                    <div id="p-control-no"
                                        style="font-family:monospace; font-size:10px; text-align:center; padding:5px 6px; background:#f9fafb; flex:1; display:flex; align-items:center; justify-content:center;">
                                    </div>
                                </div>
                                <div style="display:flex; flex-direction:column;">
                                    <div
                                        style="background:#f97316; color:white; font-weight:700; font-size:9px; text-align:center; padding:3px 6px; text-transform:uppercase; letter-spacing:1px;">
                                        Date</div>
                                    <div id="p-date"
                                        style="font-family:monospace; font-size:10px; text-align:center; padding:5px 6px; background:#f9fafb; flex:1; display:flex; align-items:center; justify-content:center;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta: Requestor + Purpose -->
                    <div
                        style="display:grid; grid-template-columns:1fr 1fr; border-top:1px solid #111; border-bottom:1px solid #111;">
                        <div
                            style="display:flex; align-items:center; gap:6px; padding:7px 14px; border-right:1px solid #111;">
                            <span
                                style="font-weight:700; font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#374151; white-space:nowrap;">Requestor
                                Name</span>
                            <span style="color:#9ca3af; margin:0 4px;">:</span>
                            <span id="p-requestor" style="font-size:12px; color:#111;"></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; padding:7px 14px;">
                            <span
                                style="font-weight:700; font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#374151; white-space:nowrap;">Purpose
                                of Request</span>
                            <span style="color:#9ca3af; margin:0 4px;">:</span>
                            <span id="p-purpose" style="font-size:12px; color:#111;"></span>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f97316; color:white;">
                                <th
                                    style="width:40px; padding:6px 10px; font-size:10px; text-transform:uppercase; letter-spacing:1px; border:1px solid #ea6c00; text-align:center;">
                                    No.</th>
                                <th
                                    style="padding:6px 10px; font-size:10px; text-transform:uppercase; letter-spacing:2px; border:1px solid #ea6c00; text-align:center;">
                                    P A R T I C U L A R S</th>
                                <th
                                    style="width:140px; padding:6px 10px; font-size:10px; text-transform:uppercase; letter-spacing:1px; border:1px solid #ea6c00; text-align:center;">
                                    Amount (₱)</th>
                            </tr>
                        </thead>
                        <tbody id="p-items"></tbody>
                        <tfoot>
                            <tr style="border-top:2px solid #f97316; background:#fff7ed;">
                                <td colspan="2"
                                    style="padding:7px 12px; font-weight:700; font-size:10px; text-transform:uppercase; letter-spacing:1px; text-align:right; border:1px solid #e5e7eb; color:#374151;">
                                    Total Amount:</td>
                                <td id="p-total"
                                    style="padding:7px 12px; font-weight:700; font-family:monospace; font-size:13px; text-align:right; border:1px solid #e5e7eb;">
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Signatures -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; border-top:2px solid #f97316;">
                        <!-- Approved By header -->
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr;">
                        <div
                            style="background:#f97316; color:white; font-weight:700; font-size:10px; text-transform:uppercase; letter-spacing:1px; text-align:center; padding:5px; border-right:1px solid #ea6c00;">
                            Approved By</div>
                        <div
                            style="background:#f97316; color:white; font-weight:700; font-size:10px; text-transform:uppercase; letter-spacing:1px; text-align:center; padding:5px;">
                            Received By</div>
                    </div>
                    <div
                        style="display:grid; grid-template-columns:1fr 1fr; border:1px solid #e5e7eb; border-top:none;">
                        <div style="padding:20px 16px; border-right:1px solid #e5e7eb; text-align:center;">
                            <div style="font-size:9px; color:#6b7280; margin-bottom:4px;">Name: <span
                                    id="p-approver-name" style="font-weight:700; font-size:12px; color:#111;"></span>
                            </div>
                            <div style="font-size:9px; color:#6b7280; margin-top:10px;">Date: <span id="p-approved-at"
                                    style="color:#374151;"></span></div>
                        </div>
                        <div style="padding:20px 16px; text-align:center;">
                            <div style="font-size:9px; color:#6b7280; margin-bottom:4px;">Name: <span
                                    id="p-receiver-name" style="font-weight:700; font-size:12px; color:#111;"></span>
                            </div>
                            <div style="font-size:9px; color:#6b7280; margin-top:10px;">Date: <span id="p-received-at"
                                    style="color:#374151;"></span></div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 6px 0;">
                        <p style="font-size:9px; color:#9ca3af; font-style:italic;">Received the above stated amount in
                            full
                            settlement.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allData = [];

        function renderTable(data) {
            const tbody = document.getElementById('received-tbody');

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No received requests yet.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(row => {
                const items = row.items ?? [];
                const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
                const receivedAt = row.received_at
                    ? new Date(row.received_at.replace(' ', 'T')).toLocaleDateString('en-PH', {
                        year: 'numeric', month: 'short', day: 'numeric'
                    }) + ', ' + new Date(row.received_at.replace(' ', 'T')).toLocaleTimeString('en-PH', {
                        hour: 'numeric', minute: '2-digit', hour12: true
                    })
                    : '—';
                const isSuccess = row.approver_name && row.receiver_name; // ← add this
                return `
<tr class="border-t border-gray-100 transition-colors ${isSuccess ? 'bg-green-50 hover:bg-green-100' : 'hover:bg-gray-50'}">
    <td class="px-5 py-3 font-mono text-xs text-blue-500">${row.control_no}</td>
    <td class="px-5 py-3">
        <p class="font-medium text-gray-800">${row.requestor_name}</p>
        <p class="text-[10px] text-gray-400">${row.sender_email ?? ''}</p>
    </td>
    <td class="px-5 py-3 text-gray-600">${row.purpose}</td>
    <td class="px-5 py-3 text-xs text-gray-400 font-mono">${row.date_requested}</td>
    <td class="px-5 py-3 font-mono text-xs font-semibold ${isSuccess ? 'text-green-600' : 'text-gray-700'}">
        ₱ ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
    </td>
    <td class="px-5 py-3 text-sm text-gray-700">${row.approver_name ?? '—'}</td>
    <td class="px-5 py-3 text-sm text-gray-700">${row.receiver_name ?? '—'}</td>
    <td class="px-5 py-3 text-xs text-gray-400">${receivedAt}</td>
    <td class="px-5 py-3">
        <button onclick='printRequest(${JSON.stringify(row).replace(/'/g, "\\'")})'
            class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
            <i class="fa-solid fa-print text-[10px]"></i> Print
        </button>
    </td>
</tr>`;
            }).join('');
        }


        function printRequest(row) {
            const items = row.items ?? [];
            let total = 0;
            let rows = '';

            items.forEach((item, i) => {
                const amount = parseFloat(item.amount) || 0;
                total += amount;
                rows += `<tr>
            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:center; font-size:10px; color:#999;">${i + 1}</td>
            <td style="padding:5px 8px; border:1px solid #e5e7eb; font-size:11px;">${item.description || ''}${item.purpose ? ' — ' + item.purpose : ''}</td>
            <td style="padding:5px 8px; border:1px solid #e5e7eb; text-align:right; font-family:monospace; font-size:11px;">₱ ${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
        </tr>`;
            });

            for (let e = items.length; e < 5; e++) {
                rows += `<tr>
        <td style="padding:6px 10px; border:1px solid #e5e7eb; text-align:center; font-size:10px; color:#d1d5db;">${e + 1}</td>
        <td style="padding:6px 10px; border:1px solid #e5e7eb; height:28px;"></td>
        <td style="padding:6px 10px; border:1px solid #e5e7eb; text-align:right; font-family:monospace; font-size:11px; color:#d1d5db;">0.00</td>
    </tr>`;
            }

            const fmtDate = str => str
                ? new Date(str.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
                : '';

            document.getElementById('p-control-no').textContent = row.control_no;
            document.getElementById('p-date').textContent = row.date_requested;
            document.getElementById('p-requestor').textContent = row.requestor_name;
            document.getElementById('p-purpose').textContent = row.purpose;
            document.getElementById('p-items').innerHTML = rows;
            document.getElementById('p-total').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
            document.getElementById('p-approver-name').textContent = row.approver_name ?? '';
            document.getElementById('p-approved-at').textContent = fmtDate(row.approved_at);
            document.getElementById('p-receiver-name').textContent = row.receiver_name ?? '';
            document.getElementById('p-received-at').textContent = fmtDate(row.received_at);

            document.getElementById('print-modal').classList.remove('hidden');
        }

        function closePrintModal() {
            document.getElementById('print-modal').classList.add('hidden');
        }

        function triggerPrint() {
            window.print();
        }



        function fetchReceived() {
            fetch('<?= BASE_URL ?>/fetchacknowledged')
                .then(res => res.json())
                .then(data => {
                    allData = data;
                    renderTable(data);
                    document.getElementById('last-updated').textContent =
                        'Updated ' + new Date().toLocaleTimeString('en-PH');
                })
                .catch(err => console.error('Fetch error:', err));
        }

        // Search
        document.getElementById('search-input').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            const filtered = allData.filter(row =>
                row.control_no?.toLowerCase().includes(q) ||
                row.requestor_name?.toLowerCase().includes(q) ||
                row.purpose?.toLowerCase().includes(q) ||
                row.approver_name?.toLowerCase().includes(q) ||
                row.receiver_name?.toLowerCase().includes(q)
            );
            renderTable(filtered);
        });

        fetchReceived();
        setInterval(fetchReceived, 5000);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') fetchReceived();
        });
    </script>
</body>

</html>