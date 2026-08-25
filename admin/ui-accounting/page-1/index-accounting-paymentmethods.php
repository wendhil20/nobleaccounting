<?php
// index-accounting-paymentmethods.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$pmAjaxUrl = BASE_URL . '/accountingpaymentmethodsajax';
$acctListUrl = BASE_URL . '/crmaccounting';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting — Payment Methods</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8 overflow-x-hidden">

        <div class="max-w-2xl mx-auto">

            <!-- Back link -->
            <a href="<?= htmlspecialchars($acctListUrl) ?>"
                class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-gray-500 hover:text-amber-700 mb-4 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Back to list
            </a>

            <div class="mb-5">
                <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">CRM Management</p>
                <h1 class="text-gray-900 text-xl font-semibold">Payment Methods</h1>
                <p class="text-gray-400 text-xs mt-0.5">Manage the options accounting can pick from when logging a deposit (e.g. GCash, Bank Transfer, Cash).</p>
            </div>

            <!-- Add form -->
            <form id="pmAddForm" class="flex items-center gap-2 mb-5">
                <input id="pmNewName" type="text" required placeholder="e.g. GCash, BDO Bank Transfer, Cash"
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-100 focus:border-amber-600 bg-white transition-colors">
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-amber-700 rounded-lg hover:bg-amber-800 transition-colors whitespace-nowrap">
                    + Add
                </button>
            </form>

            <!-- List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div id="pmList" class="divide-y divide-gray-100"></div>
            </div>

        </div>

        <!-- Toast container -->
        <div id="crmToastContainer"
            class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
        </div>

    </main>

    <script>
        function crmShowToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('crmToastContainer');
            const palette = type === 'success'
                ? { wrap: 'bg-green-50 border-green-200 text-green-700', icon: 'bg-green-200 text-green-700', symbol: '✓' }
                : { wrap: 'bg-red-50 border-red-200 text-red-700', icon: 'bg-red-200 text-red-700', symbol: '!' };
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-2.5 border rounded-lg shadow-lg px-4 py-3 text-sm
                ${palette.wrap}
                translate-x-6 opacity-0 scale-95 transition-all duration-300 ease-out`;
            toast.innerHTML = `
                <span class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold ${palette.icon}">${palette.symbol}</span>
                <span class="flex-1 leading-relaxed">${message}</span>
                <button type="button" class="shrink-0 text-current opacity-50 hover:opacity-100 text-base leading-none" aria-label="Close">&times;</button>
            `;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-x-6', 'opacity-0', 'scale-95'));
            const remove = () => {
                toast.classList.add('translate-x-6', 'opacity-0', 'scale-95');
                setTimeout(() => toast.remove(), 300);
            };
            toast.querySelector('button').addEventListener('click', remove);
            if (duration > 0) setTimeout(remove, duration);
        }

        const PM_AJAX_URL = <?= json_encode($pmAjaxUrl) ?>;

        function pmEscapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function pmEmptyState() {
            return `<div class="py-10 text-center text-gray-400 text-xs">No payment methods yet. Add one above.</div>`;
        }

        function pmRenderList(items) {
            const list = document.getElementById('pmList');
            if (items.length === 0) {
                list.innerHTML = pmEmptyState();
                return;
            }
            list.innerHTML = items.map(pm => `
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <span class="text-sm text-gray-800 font-medium">${pmEscapeHtml(pm.name)}</span>
                    <button type="button" onclick="pmDelete(${pm.id}, '${pmEscapeHtml(pm.name).replace(/'/g, "\\'")}')"
                        class="px-2.5 py-1 text-[11px] font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                        Delete
                    </button>
                </div>
            `).join('');
        }

        async function pmFetchList() {
            const list = document.getElementById('pmList');
            list.innerHTML = `<div class="p-4 space-y-2">${Array.from({ length: 3 }).map(() => `<div class="h-4 rounded bg-gray-100 animate-pulse"></div>`).join('')}</div>`;
            try {
                const res = await fetch(`${PM_AJAX_URL}?action=list`);
                const data = await res.json();
                if (!data.success) {
                    crmShowToast('Failed to load payment methods.', 'error');
                    return;
                }
                pmRenderList(data.items);
            } catch (e) {
                console.error('pmFetchList:', e);
                crmShowToast('Connection error while loading payment methods.', 'error');
            }
        }

        document.getElementById('pmAddForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const input = document.getElementById('pmNewName');
            const name = input.value.trim();
            if (!name) return;

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('name', name);

            try {
                const res = await fetch(PM_AJAX_URL, { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) {
                    crmShowToast(data.message || 'Failed to add.', 'error');
                    return;
                }
                input.value = '';
                crmShowToast('Payment method added.');
                pmFetchList();
            } catch (e) {
                console.error('pmAdd:', e);
                crmShowToast('Connection error. Please try again.', 'error');
            }
        });

        async function pmDelete(id, name) {
            if (!confirm(`Delete "${name}"? This won't affect deposits already logged with this method.`)) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            try {
                const res = await fetch(PM_AJAX_URL, { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) {
                    crmShowToast(data.message || 'Failed to delete.', 'error');
                    return;
                }
                crmShowToast('Payment method deleted.');
                pmFetchList();
            } catch (e) {
                console.error('pmDelete:', e);
                crmShowToast('Connection error. Please try again.', 'error');
            }
        }

        pmFetchList();
    </script>
</body>

</html>