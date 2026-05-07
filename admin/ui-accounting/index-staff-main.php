<?php
session_name('nobleadmin');
session_start();

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
</body>

<script>
    let prevCount = 0;

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
                    const items  = row.items ?? [];
                    const total  = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);

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
                            <p class="text-[10px] text-gray-400">${row.approved_at ?? ''}</p>
                        </td>
                        <td class="px-5 py-3">
                            <button onclick="markReceived(${row.id})"
                                class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                                <i class="fa-solid fa-check mr-1"></i>Mark as Received
                            </button>
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
                prevCount = 0;
                fetchApproved();
            }
        });
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
</html>