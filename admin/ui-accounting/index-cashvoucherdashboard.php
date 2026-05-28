<?php
// index-cashvoucherdashboard.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
$allowedPositions = [POSITION_CUSTOASSISTANT, POSITION_CUSTODIAN, POSITION_HEAD];

include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$position = $_SESSION['position'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Voucher Dashboard</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">Cash Voucher Dashboard</h1>
            <p class="text-sm text-gray-400 mt-1">Review and process cash vouchers</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Voucher Records</span>
                <div class="flex items-center gap-3">
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

            <div class="overflow-x-auto">
                <div class="max-h-[500px] overflow-y-auto scrollbar-thin">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-gray-50 text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                                <th class="px-5 py-3 text-left">Voucher No.</th>
                                <th class="px-5 py-3 text-left">Title</th>
                                <th class="px-5 py-3 text-left">Payee</th>
                                <th class="px-5 py-3 text-left">Payment For</th>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Total Amount</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody id="voucher-tbody">
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cash Voucher Modal -->
        <div id="voucher-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 overflow-y-auto">
            <div class="bg-white w-full max-w-4xl rounded-sm shadow-xl border border-gray-300 my-auto">

                <!-- Header -->
                <div class="grid grid-cols-[auto_1fr_auto] border-b-2 border-orange-400 items-center px-4 py-3 gap-4">
                    <div class="flex items-center gap-3">
                        <img src="<?= BASE_URL ?>/icon/logo.png" alt="Logo" class="w-12 h-12 object-contain">
                        <div>
                            <p class="font-bold text-sm uppercase">Noblehome Construction Corporation</p>
                            <p class="text-[10px] text-gray-500">1181 MC Premiere Bldg., EDSA Bldg., EDSA Balintawak
                                Quezon City</p>
                            <p class="text-[10px] text-gray-500">noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 |
                                Cell. No. 0968-591-6544</p>
                        </div>
                    </div>
                    <div></div>
                    <div class="text-right">
                        <p class="font-bold text-lg uppercase tracking-widest text-gray-800">Cash Voucher</p>
                        <p id="v-title" class="text-[10px] text-gray-500 uppercase tracking-wider mb-1"></p>
                        <div class="grid grid-cols-2 mt-1">
                            <div
                                class="bg-orange-500 text-white text-[10px] font-bold px-3 py-1 uppercase tracking-wider text-center border-r border-orange-400">
                                Voucher No.</div>
                            <div
                                class="bg-orange-500 text-white text-[10px] font-bold px-3 py-1 uppercase tracking-wider text-center">
                                Date:</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div id="v-control-no"
                                class="border border-gray-300 text-xs font-mono px-3 py-1 text-center bg-gray-50 border-r-0">
                            </div>
                            <div id="v-date"
                                class="border border-gray-300 text-xs font-mono px-3 py-1 text-center bg-gray-50"></div>
                        </div>
                    </div>
                </div>

                <!-- Payee / Address / Payment For / Amount in Words -->
                <div class="px-6 py-3 border-b border-gray-300 space-y-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 w-28">Payee</span>
                            <span class="text-gray-400 mr-2">:</span>
                            <span id="v-payee" class="text-sm text-gray-800"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Payment
                                For</span>
                            <span class="text-gray-400">:</span>
                            <span id="v-purpose" class="text-sm text-gray-800 ml-2"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 w-28">Address</span>
                            <span class="text-gray-400 mr-2">:</span>
                            <span id="v-address" class="text-sm text-gray-800"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Amount
                                in Words</span>
                            <span class="text-gray-400">:</span>
                            <span id="v-amount-words" class="text-sm italic text-gray-700 ml-2"></span>
                        </div>
                    </div>
                </div>

                <!-- Particulars Table -->
                <div class="relative">

                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-orange-500 text-white">
                                <th
                                    class="w-12 px-3 py-2 border border-orange-400 text-center text-[11px] uppercase tracking-wider">
                                    No.</th>
                                <th
                                    class="px-4 py-2 border border-orange-400 text-center text-[11px] uppercase tracking-widest">
                                    P A R T I C U L A R S</th>
                                <th
                                    class="w-36 px-4 py-2 border border-orange-400 text-center text-[11px] uppercase tracking-wider">
                                    Amount (P)</th>
                            </tr>
                        </thead>
                        <tbody id="v-items-tbody"></tbody>
                        <tfoot>
                            <tr class="border-t-2 border-orange-400 bg-orange-50">
                                <td colspan="3" class="border border-gray-300 p-0">
                                    <div class="flex items-center justify-between px-4 py-2">
                                        <div>
                                            <span
                                                class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Payment
                                                Method No.:</span>
                                            <span id="v-second-no" class="font-mono text-xs ml-2 text-gray-700"></span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span
                                                class="font-bold text-xs uppercase tracking-widest text-gray-700">Total
                                                Amount:</span>
                                            <span id="v-total" class="font-bold font-mono text-sm"></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Signatures -->
                <div class="grid grid-cols-4 border-t-2 border-orange-400">
                    <div
                        class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-wider text-center py-1.5 border-r border-orange-400">
                        Prepared By</div>
                    <div
                        class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-wider text-center py-1.5 border-r border-orange-400">
                        Certified Thru Correct</div>
                    <div
                        class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-wider text-center py-1.5 border-r border-orange-400">
                        Approved By</div>
                    <div
                        class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-wider text-center py-1.5">
                        Received By</div>
                </div>
                <div class="grid grid-cols-4 border-b border-gray-200">
                    <div class="px-4 py-4 border-r border-gray-200">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-prepared"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-prepared-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                    <div class="px-4 py-4 border-r border-gray-200">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-certified"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-certified-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                    <div class="px-4 py-4 border-r border-gray-200">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-approver"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-approved-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                    <div class="px-4 py-4">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-receiver"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-received-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between px-6 py-3">
                    <p class="text-[10px] italic text-gray-500">Received the above stated amount in full settlement.</p>
                    <div class="flex gap-2" id="v-footer-btns"></div>
                </div>
            </div>
        </div>

    </main>

    <style>
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

    <script>
        const POSITION = '<?= htmlspecialchars($position) ?>';
        let allData = [];
        let currentRow = null;

        function numberToWords(amount) {
            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                'Seventeen', 'Eighteen', 'Nineteen'];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            if (amount === 0) return 'Zero Pesos Only';
            function convertHundreds(n) {
                let r = '';
                if (n >= 100) { r += ones[Math.floor(n / 100)] + ' Hundred '; n %= 100; }
                if (n >= 20) { r += tens[Math.floor(n / 10)] + ' '; n %= 10; }
                if (n > 0) { r += ones[n] + ' '; }
                return r;
            }
            let intPart = Math.floor(amount);
            let decPart = Math.round((amount - intPart) * 100);
            let result = '';
            if (intPart >= 1000000) { result += convertHundreds(Math.floor(intPart / 1000000)) + 'Million '; intPart %= 1000000; }
            if (intPart >= 1000) { result += convertHundreds(Math.floor(intPart / 1000)) + 'Thousand '; intPart %= 1000; }
            if (intPart > 0) { result += convertHundreds(intPart); }
            result += 'Pesos';
            if (decPart > 0) result += ' and ' + decPart + '/100';
            return result.trim() + ' Only';
        }

        function statusBadge(status) {
            const map = {
                'voucher_approval': 'bg-yellow-100 text-yellow-700',
                'ready_to_release': 'bg-blue-100 text-blue-700',
                'released': 'bg-green-100 text-green-700',
            };
            const label = {
                'voucher_approval': 'For Approval',
                'ready_to_release': 'Ready to Release',
                'released': 'Released',
            };
            return `<span class="${map[status] ?? 'bg-gray-100 text-gray-500'} text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">${label[status] ?? status}</span>`;
        }

        function highlight(text, q) {
            if (!text) return '';
            if (!q) return text;
            const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return String(text).replace(new RegExp(`(${escaped})`, 'gi'),
                '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
        }

        function renderTable(data, q = '') {
            const tbody = document.getElementById('voucher-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No vouchers yet.</td></tr>`;
                return;
            }
            tbody.innerHTML = data.map(row => {
                const items = row.items ?? [];
                const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
                return `
    <tr data-id="${row.id}" class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
        <td class="px-5 py-3 font-mono text-xs text-blue-500 cursor-pointer underline"
            onclick="viewVoucher(${JSON.stringify(row).replace(/"/g, '&quot;')})">
            ${highlight(row.control_no, q)}
        </td>
        <td class="px-5 py-3 text-xs text-gray-700">${highlight(row.voucher_title ?? '—', q)}</td>
        <td class="px-5 py-3 text-gray-800">${highlight(row.voucher_payee ?? '—', q)}</td>
        <td class="px-5 py-3 text-gray-600">${highlight(row.purpose, q)}</td>
        <td class="px-5 py-3 text-xs text-gray-400 font-mono">${row.date_requested}</td>
        <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
            PHP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
        </td>
        <td class="px-5 py-3">${row.voucher_status ? statusBadge(row.voucher_status) : '<span class="text-[10px] text-gray-400">Not submitted</span>'}</td>
        <td class="px-5 py-3">
            <button onclick="viewVoucher(${JSON.stringify(row).replace(/"/g, '&quot;')})"
                class="bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                <i class="fa-solid fa-receipt mr-1"></i>View
            </button>
        </td>
    </tr>`;
            }).join('');
        }

        function viewVoucher(row) {
            currentRow = row;
            const items = row.items ?? [];
            const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);

            document.getElementById('v-control-no').textContent = row.control_no;
            document.getElementById('v-date').textContent = row.date_requested;
            document.getElementById('v-title').textContent = row.voucher_title ?? '';
            document.getElementById('v-second-no').textContent = row.voucher_second_no ?? '—';
            document.getElementById('v-payee').textContent = row.voucher_payee ?? '';
            document.getElementById('v-address').textContent = row.voucher_address ?? '';
            document.getElementById('v-purpose').textContent = row.voucher_purpose ?? '';
            document.getElementById('v-amount-words').textContent = numberToWords(total);
            document.getElementById('v-total').textContent = 'PhP ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            // Prepared — may signature
            const prepSig = row.prepared_signature ?? '';
            document.getElementById('v-prepared').innerHTML = prepSig
                ? `<span class="relative inline-block">
        <img src="${prepSig}" 
             style="position:absolute; bottom:-50px; left:80px; transform:translateX(-50%); 
                    height:90px; max-width:160px; object-fit:contain; z-index:10; pointer-events:none;">
        ${row.prepared_name ?? ''}
       </span>`
                : (row.prepared_name ?? '');
            document.getElementById('v-prepared-at').textContent = row.prepared_at
                ? new Date(row.prepared_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            // Certified — may signature (galing sa certified_signature column)
            const certSig = row.certified_signature ?? '';
            document.getElementById('v-certified').innerHTML = certSig
                ? `<span class="relative inline-block">
        <img src="${certSig}" 
             style="position:absolute; bottom:-50px; left:80px; transform:translateX(-50%); 
                    height:90px; max-width:160px; object-fit:contain; z-index:10; pointer-events:none;">
        ${row.certified_name ?? ''}
       </span>`
                : (row.certified_name ?? '');
            document.getElementById('v-certified-at').textContent = row.certified_name && row.certified_at
                ? new Date(row.certified_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            // Approver — may signature
            const apprSig = row.approver_signature ?? '';
            document.getElementById('v-approver').innerHTML = apprSig
                ? `<span class="relative inline-block">
        <img src="${apprSig}" 
             style="position:absolute; bottom:-50px; left:80px; transform:translateX(-50%); 
                    height:90px; max-width:160px; object-fit:contain; z-index:10; pointer-events:none;">
        ${row.approver_name ?? ''}
       </span>`
                : (row.approver_name ?? '');
            document.getElementById('v-approved-at').textContent = row.approver_name && row.approved_at
                ? new Date(row.approved_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            document.getElementById('v-receiver').textContent = row.receiver_name ?? '';
            document.getElementById('v-received-at').textContent = row.receiver_name && row.received_at
                ? new Date(row.received_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            // Items
            let rows = '';
            let filled = 0;
            items.forEach(item => {
                if (!item.description) return;
                filled++;
                const amt = parseFloat(item.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                rows += `
            <tr class="border-t border-gray-200">
                <td class="px-3 py-2 text-center text-xs text-gray-400 border-r border-gray-200">${filled}</td>
                <td class="px-4 py-2 border-r border-gray-200 text-sm">${item.description}${item.purpose ? ' — ' + item.purpose : ''}</td>
                <td class="px-4 py-2 text-right font-mono text-sm">${amt}</td>
            </tr>`;
            });
            for (let e = filled; e < 5; e++) {
                rows += `
            <tr class="border-t border-gray-200">
                <td class="px-3 py-2 text-center text-xs text-gray-300 border-r border-gray-200">${e + 1}</td>
                <td class="px-4 py-2 border-r border-gray-200" style="height:28px;"></td>
                <td class="px-4 py-2"></td>
            </tr>`;
            }

            document.getElementById('v-items-tbody').innerHTML = rows;

            // Footer buttons — based on position
            const footerBtns = document.getElementById('v-footer-btns');
            const closeBtn = `<button onclick="closeVoucherModal()" class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all border border-gray-200">Close</button>`;

            let actionBtn = '';

            if (POSITION === '<?= POSITION_CUSTOASSISTANT ?>') {
                if (!row.prepared_by) {
                    actionBtn = `
        <button onclick="markPrepared(${row.voucher_id})"
            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
            <i class="fa-solid fa-pen-to-square mr-1"></i>Mark as Prepared
        </button>`;
                }
            } else if (POSITION === '<?= POSITION_HEAD ?>') {
                if (row.voucher_status === 'voucher_approval' && !row.approved_by) {
                    if (!row.prepared_by) {
                        actionBtn = `
                <span class="flex items-center gap-2 text-xs text-red-500 font-semibold px-4 py-2 bg-red-50 rounded-lg border border-red-200">
                    <i class="fa-solid fa-lock"></i>Waiting for to be prepared
                </span>`;
                    } else {
                        actionBtn = `
                <button onclick="markApproved(${row.voucher_id})"
                    class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                    <i class="fa-solid fa-check mr-1"></i>Approve Voucher
                </button>`;
                    }
                }
            }

           footerBtns.innerHTML = closeBtn + (actionBtn || `
<span class="text-xs text-gray-400 px-4 py-2 font-medium flex items-center gap-2">
    ${row.voucher_status === 'released'
        ? `<button onclick="printVoucher(${JSON.stringify(row).replace(/"/g, '&quot;')})"
               class="flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
               <i class="fa-solid fa-print mr-1"></i>Print Voucher
           </button>`
        : row.voucher_status === 'ready_to_release'
            ? '<i class="fa-solid fa-box text-blue-500 mr-1"></i>Ready to Release'
            : '<i class="fa-solid fa-clock text-yellow-500 mr-1"></i>Waiting for approval'}
</span>`);

            document.getElementById('voucher-modal').classList.remove('hidden');
        }

        function closeVoucherModal() {
            document.getElementById('voucher-modal').classList.add('hidden');
            currentRow = null;
        }

        function markPrepared(voucherId) {
            fetch('<?= BASE_URL ?>/cashvoucherprepared', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ voucher_id: voucherId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeVoucherModal();
                        showToast('Voucher marked as prepared!');
                        previousCount = 0;
                        fetchVouchers();
                    } else {
                        showToast(data.error ?? 'Failed to mark as prepared.', 'error');
                    }
                });
        }

        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
            };
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 z-[999] flex items-center gap-3 ${colors[type]} text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg transition-all duration-300 opacity-0 translate-y-2`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${message}`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
            });
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function markApproved(voucherId) {
            fetch('<?= BASE_URL ?>/cashvoucherapproved', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ voucher_id: voucherId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeVoucherModal();
                        showToast('Voucher approved successfully!');
                        previousCount = 0;
                        fetchVouchers();
                    } else {
                        showToast(data.error ?? 'Failed.', 'error');
                    }
                });
        }

          function printVoucher(row) {
            const items = row.items ?? [];
            let total = 0, itemRows = '', filled = 0;
            items.forEach(item => {
                if (!item.description) return;
                filled++;
                const amt = parseFloat(item.amount || 0);
                total += amt;
                itemRows += `<tr>
            <td style="text-align:center;border:1px solid #ccc;padding:5px;font-size:11px;">${filled}</td>
            <td style="border:1px solid #ccc;padding:5px;font-size:11px;">${item.description}${item.purpose ? ' — ' + item.purpose : ''}</td>
            <td style="text-align:right;border:1px solid #ccc;padding:5px;font-family:monospace;font-size:11px;">${amt.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
        </tr>`;
            });
            for (let e = filled; e < 5; e++) {
                itemRows += `<tr>
            <td style="text-align:center;border:1px solid #ccc;padding:5px;color:#ccc;font-size:11px;">${e + 1}</td>
            <td style="border:1px solid #ccc;padding:5px;height:24px;"></td>
            <td style="border:1px solid #ccc;padding:5px;"></td>
        </tr>`;
            }

            const prepSig = row.prepared_signature ? `<img src="${row.prepared_signature}"   style="position:absolute;top:0;left:50%;transform:translateX(-50%);height:60px;max-width:140px;object-fit:contain;">` : '';
            const certSig = row.certified_signature ? `<img src="${row.certified_signature}"  style="position:absolute;top:0;left:50%;transform:translateX(-50%);height:60px;max-width:140px;object-fit:contain;">` : '';
            const apprSig = row.approver_signature ? `<img src="${row.approver_signature}"   style="position:absolute;top:0;left:50%;transform:translateX(-50%);height:60px;max-width:140px;object-fit:contain;">` : '';

            const preparedAt = row.prepared_at ? new Date(row.prepared_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
            const certifiedAt = row.certified_at ? new Date(row.certified_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
            const approvedAt = row.approved_at ? new Date(row.approved_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
            const receiverName = row.manual_receiver_name || row.receiver_name || '';
            const receivedAt = row.manual_receiver_date
                ? new Date(row.manual_receiver_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                : row.received_at
                    ? new Date(row.received_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                    : '';

            const html = `<!DOCTYPE html><html><head><meta charset="UTF-8">
    <title>Cash Voucher - ${row.voucher_control_no ?? row.control_no}</title>
    <style>
        *{box-sizing:border-box;}
        body{font-family:Arial,sans-serif;font-size:11px;margin:0;padding:15px;}
        table{width:100%;border-collapse:collapse;}
        .orange-bg{background:#f97316;color:white;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
       .sig-cell{padding:12px 8px;width:25%;border-right:1px solid #e5e7eb;box-sizing:border-box;vertical-align:top;}
.sig-inner{height:70px;display:flex;align-items:center;justify-content:center;}
.sig-line{border-top:1px solid #999;margin-top:6px;padding-top:4px;text-align:center;}
        @page{size:A4 portrait;margin:1cm;}
    </style></head><body>
    <div style="border:2px solid #f97316;border-radius:2px;">

        <!-- Header -->
        <div style="display:grid;grid-template-columns:1fr auto;border-bottom:2px solid #f97316;">
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;">
                <img src="<?= BASE_URL ?>/icon/logo.png" style="width:48px;height:48px;object-fit:contain;">
                <div>
                    <div style="font-weight:bold;font-size:12px;text-transform:uppercase;">Noblehome Construction Corporation</div>
                    <div style="font-size:9px;color:#666;margin-top:2px;">1181 MC Premiere Bldg., EDSA Bldg., EDSA Balintawak Quezon City</div>
                    <div style="font-size:9px;color:#666;">noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell. No. 0968-591-6544</div>
                </div>
            </div>
            <div style="text-align:right;padding:10px 14px;border-left:1px solid #f97316;">
                <div style="font-weight:bold;font-size:16px;text-transform:uppercase;letter-spacing:2px;">Cash Voucher</div>
                <div style="font-size:9px;color:#888;text-transform:uppercase;margin-bottom:6px;">${row.voucher_title ?? ''}</div>
                <table style="width:auto;margin-left:auto;">
                    <tr>
                        <td class="orange-bg" style="font-size:9px;font-weight:bold;padding:3px 10px;text-align:center;border-right:1px solid #ea6c00;">Voucher No.</td>
                        <td class="orange-bg" style="font-size:9px;font-weight:bold;padding:3px 10px;text-align:center;">Date:</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #d1d5db;font-family:monospace;font-size:10px;padding:4px 10px;text-align:center;background:#f9fafb;border-right:0;">${row.voucher_control_no ?? row.control_no}</td>
                        <td style="border:1px solid #d1d5db;font-family:monospace;font-size:10px;padding:4px 10px;text-align:center;background:#f9fafb;">${row.date_requested}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payee / Address -->
        <div style="padding:10px 14px;border-bottom:1px solid #e5e7eb;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:6px;">
                <div style="display:flex;gap:6px;align-items:center;">
                    <span style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;color:#555;width:90px;">Payee</span>
                    <span style="color:#aaa;margin-right:4px;">:</span>
                    <span style="border-bottom:1px solid #aaa;flex:1;font-size:11px;">${row.voucher_payee ?? ''}</span>
                </div>
                <div style="display:flex;gap:6px;align-items:center;">
                    <span style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;color:#555;white-space:nowrap;">Payment For</span>
                    <span style="color:#aaa;margin:0 4px;">:</span>
                    <span style="border-bottom:1px solid #aaa;flex:1;font-size:11px;">${row.voucher_purpose ?? ''}</span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div style="display:flex;gap:6px;align-items:center;">
                    <span style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;color:#555;width:90px;">Address</span>
                    <span style="color:#aaa;margin-right:4px;">:</span>
                    <span style="border-bottom:1px solid #aaa;flex:1;font-size:11px;">${row.voucher_address ?? ''}</span>
                </div>
                <div style="display:flex;gap:6px;align-items:center;">
                    <span style="font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;color:#555;white-space:nowrap;">Amount in Words</span>
                    <span style="color:#aaa;margin:0 4px;">:</span>
                    <span style="border-bottom:1px solid #aaa;flex:1;font-size:11px;font-style:italic;">${(() => {
                    // numberToWords inline
                    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
                    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
                    let amount = total; if (amount === 0) return 'Zero Pesos Only';
                    function cvt(n) { let r = ''; if (n >= 100) { r += ones[Math.floor(n / 100)] + ' Hundred '; n %= 100; } if (n >= 20) { r += tens[Math.floor(n / 10)] + ' '; n %= 10; } if (n > 0) { r += ones[n] + ' '; } return r; }
                    let i = Math.floor(amount), d = Math.round((amount - i) * 100), res = '';
                    if (i >= 1000000) { res += cvt(Math.floor(i / 1000000)) + 'Million '; i %= 1000000; }
                    if (i >= 1000) { res += cvt(Math.floor(i / 1000)) + 'Thousand '; i %= 1000; }
                    if (i > 0) { res += cvt(i); } res += 'Pesos'; if (d > 0) res += ' and ' + d + '/100';
                    return res.trim() + ' Only';
                })()}</span>
                </div>
            </div>
        </div>

        <!-- Particulars -->
        <table style="border-collapse:collapse;">
            <thead>
                <tr class="orange-bg">
                    <th style="width:40px;padding:6px;border:1px solid #ea6c00;text-align:center;font-size:10px;">No.</th>
                    <th style="padding:6px;border:1px solid #ea6c00;text-align:center;font-size:10px;letter-spacing:3px;">P A R T I C U L A R S</th>
                    <th style="width:130px;padding:6px;border:1px solid #ea6c00;text-align:center;font-size:10px;">Amount (P)</th>
                </tr>
            </thead>
            <tbody>${itemRows}</tbody>
            <tfoot>
                <tr style="background:#fff7ed;border-top:2px solid #f97316;">
                    <td colspan="3" style="border:1px solid #e5e7eb;padding:0;">
                        <div style="display:flex;justify-content:space-between;padding:6px 12px;">
                            <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#888;">
                                Payment Method No.: <span style="font-family:monospace;font-size:10px;color:#374151;">${row.voucher_second_no ?? '—'}</span>
                            </div>
                            <div style="font-weight:bold;font-size:11px;">
                                Total Amount: <span style="font-family:monospace;">${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature Headers -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;border-top:2px solid #f97316;">
            <div class="orange-bg" style="font-size:9px;font-weight:bold;text-align:center;padding:5px;border-right:1px solid #ea6c00;">Prepared By</div>
            <div class="orange-bg" style="font-size:9px;font-weight:bold;text-align:center;padding:5px;border-right:1px solid #ea6c00;">Certified Thru Correct</div>
            <div class="orange-bg" style="font-size:9px;font-weight:bold;text-align:center;padding:5px;border-right:1px solid #ea6c00;">Approved By</div>
            <div class="orange-bg" style="font-size:9px;font-weight:bold;text-align:center;padding:5px;">Received By</div>
        </div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;border-bottom:1px solid #e5e7eb;">
    <div style="padding:10px 8px;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;">
        <div style="height:65px;display:flex;align-items:flex-end;justify-content:center;margin-bottom:4px;">
            ${row.prepared_signature ? `<img src="${row.prepared_signature}" style="max-height:65px;max-width:120px;object-fit:contain;">` : ''}
        </div>
        <div style="border-top:1px solid #999;padding-top:4px;text-align:center;">
            <div style="font-size:10px;font-weight:600;">${row.prepared_name ?? ''}</div>
            <div style="font-size:9px;color:#888;">${preparedAt}</div>
        </div>
    </div>
    <div style="padding:10px 8px;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;">
        <div style="height:65px;display:flex;align-items:flex-end;justify-content:center;margin-bottom:4px;">
            ${row.certified_signature ? `<img src="${row.certified_signature}" style="max-height:65px;max-width:120px;object-fit:contain;">` : ''}
        </div>
        <div style="border-top:1px solid #999;padding-top:4px;text-align:center;">
            <div style="font-size:10px;font-weight:600;">${row.certified_name ?? ''}</div>
            <div style="font-size:9px;color:#888;">${certifiedAt}</div>
        </div>
    </div>
    <div style="padding:10px 8px;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;">
        <div style="height:65px;display:flex;align-items:flex-end;justify-content:center;margin-bottom:4px;">
            ${row.approver_signature ? `<img src="${row.approver_signature}" style="max-height:65px;max-width:120px;object-fit:contain;">` : ''}
        </div>
        <div style="border-top:1px solid #999;padding-top:4px;text-align:center;">
            <div style="font-size:10px;font-weight:600;">${row.approver_name ?? ''}</div>
            <div style="font-size:9px;color:#888;">${approvedAt}</div>
        </div>
    </div>
    <div style="padding:10px 8px;display:flex;flex-direction:column;">
        <div style="height:65px;display:flex;align-items:flex-end;justify-content:center;margin-bottom:4px;">
            <!-- no signature for receiver -->
        </div>
        <div style="border-top:1px solid #999;padding-top:4px;text-align:center;">
            <div style="font-size:10px;font-weight:600;">${receiverName}</div>
            <div style="font-size:9px;color:#888;">${receivedAt}</div>
        </div>
    </div>
</div>

        <!-- Footer note -->
        <div style="padding:8px 14px;">
            <span style="font-size:9px;font-style:italic;color:#888;">Received the above stated amount in full settlement.</span>
        </div>

    </div>
    <script>window.onload=function(){window.print();window.onafterprint=function(){window.close();};};<\/script>
    </body></html>`;

          const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
        }



        let previousCount = 0;

        function fetchVouchers() {
            fetch('<?= BASE_URL ?>/cashvoucherfetchall')
                .then(res => res.json())
                .then(data => {
                    if (data.length === previousCount) return;
                    previousCount = data.length;
                    allData = data;
                    const q = document.getElementById('search-input').value.toLowerCase();
                    const filtered = q ? allData.filter(row =>
                        row.control_no?.toLowerCase().includes(q) ||
                        row.voucher_title?.toLowerCase().includes(q) ||
                        row.voucher_payee?.toLowerCase().includes(q) ||
                        row.purpose?.toLowerCase().includes(q)
                    ) : allData;
                    renderTable(filtered, q);
                    document.getElementById('last-updated').textContent =
                        'Updated ' + new Date().toLocaleTimeString('en-PH');
                })
                .catch(err => console.error('Fetch error:', err));
        }
        document.getElementById('search-input').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            const filtered = allData.filter(row =>
                row.control_no?.toLowerCase().includes(q) ||
                row.voucher_title?.toLowerCase().includes(q) ||
                row.voucher_payee?.toLowerCase().includes(q) ||
                row.purpose?.toLowerCase().includes(q)
            );
            renderTable(filtered, q);
        });

        // CSS — isang beses lang
        const style = document.createElement('style');
        style.textContent = `
    @keyframes badgePulse {
        0%   { transform: scale(1);   opacity: 1; }
        50%  { transform: scale(1.4); opacity: 0.5; }
        100% { transform: scale(1);   opacity: 1; }
    }
    .highlight-row td:first-child {
        position: relative;
    }
    .highlight-badge {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #ef4444;
        border-radius: 50%;
        animation: badgePulse 0.8s ease-in-out 6;
        margin-right: 6px;
        vertical-align: middle;
        flex-shrink: 0;
    }
`;
        document.head.appendChild(style);

        // Highlight logic — run after fetchRequests
        function checkHighlight() {
            const params = new URLSearchParams(window.location.search);
            const highlightId = params.get('highlight');
            if (!highlightId) return;

            // Ulit-ulitin hanggang lumabas yung row (kasi async ang fetch)
            const interval = setInterval(() => {
                const row = document.querySelector(`tr[data-id="${highlightId}"]`);
                if (row) {
                    clearInterval(interval);
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.classList.add('highlight-row');

                    // Dagdag badge sa first td
                    const firstTd = row.querySelector('td:first-child');
                    const badge = document.createElement('span');
                    badge.className = 'highlight-badge';
                    firstTd.prepend(badge);

                    // Tanggalin badge after animation
                    setTimeout(() => badge.remove(), 5000);
                }
            }, 200);

            // Stop after 5 seconds kung hindi pa rin makita
            setTimeout(() => clearInterval(interval), 5000);
        }

        fetchVouchers();
        setTimeout(checkHighlight, 500); // slight delay para matapos muna mag-render

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                previousCount = 0;
                fetchVouchers();
            }
        });

    </script>

</body>

</html>