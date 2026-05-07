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
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
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
</body>

<style>
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

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
                ? new Date(row.received_at).toLocaleDateString('en-PH', {
                    year: 'numeric', month: 'short', day: 'numeric'
                  }) + ' ' + new Date(row.received_at).toLocaleTimeString('en-PH', {
                    hour: 'numeric', minute: '2-digit', hour12: true
                  })
                : '—';

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
                <td class="px-5 py-3 text-sm text-gray-700">${row.approver_name ?? '—'}</td>
                <td class="px-5 py-3 text-sm text-gray-700">${row.receiver_name ?? '—'}</td>
                <td class="px-5 py-3 text-xs text-gray-400">${receivedAt}</td>
            </tr>`;
        }).join('');
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
</html>