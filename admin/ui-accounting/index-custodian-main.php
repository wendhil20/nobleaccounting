<?php
// index-custodian-main.php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Dashboard</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">Cash Vouchers</h1>
            <p class="text-sm text-gray-400 mt-1">Completed and received budget requests</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                            <tr class="bg-gray-50 text-[11px] font-semibold text-black uppercase tracking-widest">
                                <th class="px-5 py-3 text-left">Voucher No.</th>
                                <th class="px-5 py-3 text-left">Budget Request No.</th>
                                <th class="px-5 py-3 text-left">Payee</th>
                                <th class="px-5 py-3 text-left">Payment For</th>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Total Amount</th>
                                <th class="px-5 py-3 text-left">Approved By</th>
                                <th class="px-5 py-3 text-left">Received By</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody id="voucher-tbody">
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-gray-400 text-sm">
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
                            <input id="v-payee"
                                class="flex-1 border-b border-gray-400 text-sm pb-0.5 outline-none bg-transparent" />
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Payment
                                For</span>
                            <span class="text-gray-400">:</span>
                            <input id="v-purpose"
                                class="flex-1 border-b border-gray-400 text-sm pb-0.5 outline-none bg-transparent ml-2" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 w-28">Address</span>
                            <span class="text-gray-400 mr-2">:</span>
                            <input id="v-address"
                                class="flex-1 border-b border-gray-400 text-sm pb-0.5 outline-none bg-transparent" />
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Amount
                                in Words</span>
                            <span class="text-gray-400">:</span>
                            <input id="v-amount-words" readonly
                                class="flex-1 border-b border-gray-400 text-sm pb-0.5 outline-none bg-transparent italic text-gray-700 ml-2" />
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
                                                Method No:</span>
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
                    <!-- Prepared By — blank, manual -->
                    <div class="px-4 py-4 border-r border-gray-200">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-prepared"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-prepared-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                    <!-- Certified Thru Correct — from certified_by -->
                    <div class="px-4 py-4 border-r border-gray-200">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-certified"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-certified-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                    <!-- Approved By -->
                    <div class="px-4 py-4 border-r border-gray-200">
                        <p class="text-[10px] text-gray-500">Name: <span id="v-approver"
                                class="text-gray-800 font-semibold text-xs"></span></p>
                        <p class="text-[10px] text-gray-500 mt-3">Date: <span id="v-approved-at"
                                class="text-gray-600 text-xs"></span></p>
                    </div>
                    <!-- Received By -->
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

    <!-- Title Modal -->
    <div id="voucher-title-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center">
                        <i class="fa-solid fa-tag text-orange-500 text-xs"></i>
                    </div>
                    <h3 class="font-bold text-sm text-gray-800">Voucher Details</h3>
                </div>
                <button onclick="document.getElementById('voucher-title-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <!-- Title -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Voucher Title <span
                            class="text-red-400">*</span></label>
                    <input type="text" id="voucher-title-input" placeholder="e.g. Materials for Site A — May 2026"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-orange-400 transition-all">
                </div>

                <!-- Second No. -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Second No. <span
                            class="text-gray-300">(optional)</span></label>
                    <input type="text" id="voucher-second-no-input" placeholder="e.g. 9808971"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-orange-400 transition-all">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                <button onclick="document.getElementById('voucher-title-modal').classList.add('hidden')"
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all">
                    Cancel
                </button>
                <button onclick="submitWithTitle()"
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Submit Voucher
                </button>
            </div>
        </div>
    </div>

    <script>
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
            const cls = map[status] ?? 'bg-gray-100 text-gray-500';
            const lbl = label[status] ?? status;
            return `<span class="${cls} text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">${lbl}</span>`;
        }

        function renderTable(data) {
            const tbody = document.getElementById('voucher-tbody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="px-5 py-8 text-center text-gray-400">No completed vouchers yet.</td></tr>`;
                return;
            }
        tbody.innerHTML = data.map(row => {
    const items = row.items ?? [];
    const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
    
const isComplete = row.approver_name && (row.receiver_name || row.manual_receiver_name) && row.voucher_status === 'released';
const isPending = !row.approver_name || (!row.receiver_name && !row.manual_receiver_name);
    
    const rowClass = isComplete
        ? 'bg-green-50 hover:bg-green-100'
        : isPending
            ? 'bg-red-100 hover:bg-red-200'
            : 'hover:bg-gray-50';

    return `
<tr class="border-t border-gray-100 transition-colors ${rowClass}">
               <td class="px-5 py-3 font-mono text-xs text-blue-500 cursor-pointer underline"
    onclick="viewVoucher(${JSON.stringify(row).replace(/"/g, '&quot;')})">
    ${row.voucher_control_no ?? '—'}
</td>
<td class="px-5 py-3 font-mono text-xs text-gray-500">
    ${row.budget_control_no}
</td>
                <td class="px-5 py-3 text-gray-800">${row.voucher_payee ?? '—'}</td>
                <td class="px-5 py-3 text-gray-600">${row.purpose}</td>
                <td class="px-5 py-3 text-xs text-gray-400 font-mono">${row.date_requested}</td>
                <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
                    PhP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                </td>
                <td class="px-5 py-3 text-sm text-gray-700">${row.approver_name ?? '—'}</td>
                <td class="px-5 py-3 text-sm text-gray-700">${row.manual_receiver_name || row.receiver_name || '—'}</td>
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

            

            // Header
            document.getElementById('v-control-no').textContent = row.control_no;
            document.getElementById('v-date').textContent = row.date_requested;
            // Sa header area — ipakita ang second_no kung meron
            document.getElementById('v-second-no').textContent = row.voucher_second_no ?? '—';

            document.getElementById('v-title').textContent = row.voucher_title ?? '';

            // Inputs — kung may voucher na, i-fill; kung wala, blank
            document.getElementById('v-payee').value = row.voucher_payee ?? '';
            document.getElementById('v-address').value = row.voucher_address ?? '';
            document.getElementById('v-purpose').value = row.voucher_purpose ?? '';
            document.getElementById('v-amount-words').value = numberToWords(total);

            // Total
            document.getElementById('v-total').textContent = 'PhP ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            // Prepared
            document.getElementById('v-prepared').textContent = row.prepared_name ?? '';
            document.getElementById('v-prepared-at').textContent = row.prepared_at
                ? new Date(row.prepared_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
            // Signatures
            document.getElementById('v-certified').textContent = row.certified_name ?? '';
            document.getElementById('v-certified-at').textContent = row.certified_name && row.certified_at
                ? new Date(row.certified_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            document.getElementById('v-approver').textContent = row.approver_name ?? '';
            document.getElementById('v-approved-at').textContent = row.approver_name && row.approved_at
                ? new Date(row.approved_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            document.getElementById('v-receiver').textContent = row.receiver_name ?? '';
            document.getElementById('v-received-at').textContent = row.receiver_name && row.received_at
                ? new Date(row.received_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            // Received By — manual o system
const receiverName = row.manual_receiver_name || row.receiver_name || '';
const receivedAt = row.manual_receiver_date 
    ? new Date(row.manual_receiver_date).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' })
    : row.received_at 
        ? new Date(row.received_at.replace(' ','T')).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' })
        : '';

document.getElementById('v-receiver').textContent = receiverName;
document.getElementById('v-received-at').textContent = receivedAt;


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


            // Footer buttons
const footerBtns = document.getElementById('v-footer-btns');
const closeBtn = `<button onclick="closeVoucherModal()" class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all border border-gray-200">Close</button>`;

if (!row.budget_received_by) {
    footerBtns.innerHTML = closeBtn + `
        <span class="flex items-center gap-2 text-xs text-gray-400 px-4 py-2 font-medium bg-gray-50 rounded-lg border border-gray-200">
            <i class="fa-solid fa-lock text-gray-300"></i>
            Waiting for staff to mark as received
        </span>`;
} else if (!row.voucher_status) {
    footerBtns.innerHTML = closeBtn + `
        <button onclick="confirmSubmit()"
            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
            <i class="fa-solid fa-paper-plane mr-1"></i>Submit Voucher
        </button>`;
} else if (row.voucher_status === 'ready_to_release') {
    footerBtns.innerHTML = closeBtn + `
        <div class="flex items-center gap-2">
            <input type="text" id="manual-receiver-name" placeholder="Receiver name (optional)"
                class="border border-gray-200 rounded px-3 py-1.5 text-xs outline-none focus:border-orange-400 w-44">
            <input type="date" id="manual-receiver-date"
                class="border border-gray-200 rounded px-3 py-1.5 text-xs outline-none focus:border-orange-400 w-36">
            <button onclick="releaseVoucher(${row.voucher_id})"
                class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all whitespace-nowrap">
                <i class="fa-solid fa-check mr-1"></i>Release Cash Voucher
            </button>
        </div>`;
} else {
    footerBtns.innerHTML = closeBtn + `
        <span class="text-xs text-gray-400 px-4 py-2 font-medium">
            ${row.voucher_status === 'released'
                ? '<i class="fa-solid fa-check text-green-500 mr-1"></i>Released'
                : '<i class="fa-solid fa-clock text-yellow-500 mr-1"></i>Waiting for approval'}
        </span>`;
}

            document.getElementById('voucher-modal').classList.remove('hidden');
        }

        function closeVoucherModal() {
            document.getElementById('voucher-modal').classList.add('hidden');
            currentRow = null;
        }

        function confirmSubmit() {
            const payee = document.getElementById('v-payee').value.trim();
            const address = document.getElementById('v-address').value.trim();
            const purpose = document.getElementById('v-purpose').value.trim();

            if (!payee) {
                document.getElementById('v-payee').classList.add('border-red-400');
                return;
            }
            document.getElementById('v-payee').classList.remove('border-red-400');

            // Show title modal first
            document.getElementById('voucher-title-modal').classList.remove('hidden');
        }

        function submitWithTitle() {
            const title = document.getElementById('voucher-title-input').value.trim();
            const secondNo = document.getElementById('voucher-second-no-input').value.trim();

            if (!title) {
                document.getElementById('voucher-title-input').classList.add('border-red-400');
                return;
            }
            document.getElementById('voucher-title-input').classList.remove('border-red-400');
            document.getElementById('voucher-title-modal').classList.add('hidden');

            const payee = document.getElementById('v-payee').value.trim();
            const address = document.getElementById('v-address').value.trim();
            const purpose = document.getElementById('v-purpose').value.trim();

            fetch('<?= BASE_URL ?>/submitrequestvoucher', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_id: currentRow.id, payee, address, purpose, title, second_no: secondNo })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeVoucherModal();
                        allData = [];
                        fetchVouchers();
                    } else {
                        alert(data.error ?? 'Failed to submit.');
                    }
                });
        }

function releaseVoucher(voucherId) {
    const manualName = document.getElementById('manual-receiver-name')?.value.trim() ?? '';
    const manualDate = document.getElementById('manual-receiver-date')?.value ?? '';

    fetch('<?= BASE_URL ?>/releasevoucher', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ voucher_id: voucherId, manual_name: manualName, manual_date: manualDate })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeVoucherModal();
                showToast('Cash voucher released!');
                allData = [];
                fetchVouchers();
            } else {
                showToast(data.error ?? 'Failed to release.', 'error');
            }
        });
}
        function fetchVouchers() {
            fetch('<?= BASE_URL ?>/fetchreceived')
                .then(res => res.json())
                .then(data => {
                    allData = data;
                    renderTable(data);
                    document.getElementById('last-updated').textContent =
                        'Updated ' + new Date().toLocaleTimeString('en-PH');
                });
        }

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

        fetchVouchers();
        setInterval(fetchVouchers, 5000);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') fetchVouchers();
        });
    </script>
</body>

</html>