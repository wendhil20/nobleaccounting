<?php
session_name('nobleadmin');
session_start();

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_HR];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">

    <main class="ml-56 min-h-screen p-8">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">Manage Accounts</h1>
            <p class="text-sm text-gray-400 mt-1">All registered department accounts</p>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Account List</span>
                <div class="flex items-center gap-2">
                    <span id="last-updated" class="text-[10px] text-gray-400"></span>
                    <div id="pulse" class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-[11px] font-semibold text-black uppercase">
                            <th class="px-5 py-3 text-left">ID</th>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role / Department</th>
                            <th class="px-5 py-3 text-left">Position</th>
                            <th class="px-5 py-3 text-left">Created At</th>
                        </tr>
                    </thead>
                    <tbody id="accounts-tbody">
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        let previousCount = 0;

        function renderAccounts(data) {
            const tbody = document.getElementById('accounts-tbody');

            tbody.innerHTML = data.map((row) => `
        <tr class="border-t border-gray-100 hover:bg-amber-50 transition-colors">
            <td class="px-5 py-3 text-gray-400 font-mono text-xs">${row.id}</td>
            <td class="px-5 py-3 font-medium text-gray-800">${row.name}</td>
            <td class="px-5 py-3 text-gray-500">${row.email}</td>
            <td class="px-5 py-3">
                <span class="bg-amber-100 text-amber-700 text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">
                    ${row.role}
                </span>
            </td>
            <td class="px-5 py-3">
                <select onchange="changePosition(${row.id}, this.value)"
                    class="${row.position === 'head' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'} 
                    text-[10px] font-semibold px-3 py-1 rounded-full uppercase tracking-wide border-none outline-none cursor-pointer transition-all">
                    <option value="staff" ${row.position === 'staff' ? 'selected' : ''}>Staff</option>
                    <option value="head" ${row.position === 'head' ? 'selected' : ''}>Head</option>
                     <option value="custodian" ${row.position === 'custodian' ? 'selected' : ''}>Custodian</option>
                     <option value="custoassistant" ${row.position === 'custoassistant' ? 'selected' : ''}>Custodian Assistant</option>
                </select>
            </td>
            <td class="px-5 py-3 text-gray-400 text-xs font-mono">${row.created_at}</td>
        </tr>
    `).join('');

            const now = new Date();
            document.getElementById('last-updated').textContent =
                'Updated ' + now.toLocaleTimeString('en-PH');
        }

        function fetchAccounts(forceRender = false) {
            fetch('<?= BASE_URL ?>/hrfetch')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('accounts-tbody');

                    if (!data.length) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No accounts found.</td></tr>`;
                        previousCount = 0;
                        return;
                    }

                    // Mag-render lang kung may bagong account o force render
                    if (forceRender || data.length !== previousCount) {
                        previousCount = data.length;
                        renderAccounts(data);
                    }
                })
                .catch(() => {
                    document.getElementById('accounts-tbody').innerHTML =
                        `<tr><td colspan="6" class="px-5 py-8 text-center text-red-400">Failed to load data.</td></tr>`;
                });
        }

        function changePosition(id, newPosition) {
            fetch('<?= BASE_URL ?>/hrposition', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, position: newPosition })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Wala nang fetchAccounts — update lang ang dropdown color
                        const select = document.querySelector(`select[onchange="changePosition(${id}, this.value)"]`);
                        if (select) {
                            select.className = `${newPosition === 'head' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'} 
                    text-[10px] font-semibold px-3 py-1 rounded-full uppercase tracking-wide border-none outline-none cursor-pointer transition-all`;
                        }
                    }
                });
        }

        // Initial load lang — isang beses
        fetchAccounts(true);

        // Mag-check ng bago kapag nag-focus ulit ang tab (bumalik sa tab)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                fetchAccounts();
            }
        });
    </script>

</body>

</html>