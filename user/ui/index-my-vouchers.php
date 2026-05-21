<?php

include ROOT_PATH . '/network/connect.php';
if (empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vouchers — NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/user/navigation/top.php'; ?>
</head>

<body class="min-h-screen px-4 py-12 relative"
    style="background-image: url('<?= BASE_URL ?>/icon/building2.png'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 bg-black/50 z-0"></div>
    <div class="max-w-5xl mx-auto relative z-10 pt-16">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-white">My Vouchers</h1>
            <p class="text-sm text-white/60 mt-1">Released cash vouchers for your acceptance</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Voucher Records</span>
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                            <th class="px-5 py-3 text-left">Voucher No.</th>
                            <th class="px-5 py-3 text-left">Purpose</th>
                            <th class="px-5 py-3 text-left">Total Amount</th>
                            <th class="px-5 py-3 text-left">Released</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="voucher-tbody">
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">
                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="view-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 overflow-y-auto">
        <div class="bg-white w-full max-w-4xl rounded-sm shadow-xl border border-gray-300 my-auto">
            <div class="grid grid-cols-[auto_1fr_auto] border-b-2 border-orange-400 items-center px-4 py-3 gap-4">
                <div class="flex items-center gap-3">
                    <img src="<?= BASE_URL ?>/icon/logo.png" alt="Logo" class="w-12 h-12 object-contain">
                    <div>
                        <p class="font-bold text-sm uppercase">Noblehome Construction Corporation</p>
                        <p class="text-[10px] text-gray-500">1181 MC Premiere Bldg., EDSA Bldg., EDSA Balintawak Quezon
                            City</p>
                        <p class="text-[10px] text-gray-500">noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell.
                            No. 0968-591-6544</p>
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

            <div class="px-6 py-3 border-b border-gray-300 space-y-2">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600 w-28">Payee</span>
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
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600 w-28">Address</span>
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
                                        <span class="font-bold text-xs uppercase tracking-widest text-gray-700">Total
                                            Amount:</span>
                                        <span id="v-total" class="font-bold font-mono text-sm"></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

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
                <div class="bg-orange-500 text-white text-[10px] font-bold uppercase tracking-wider text-center py-1.5">
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

            <div class="flex items-center justify-between px-6 py-3">
                <p class="text-[10px] italic text-gray-500">Received the above stated amount in full settlement.</p>
                <div class="flex gap-2" id="v-footer-btns"></div>
            </div>
        </div>
    </div>

    <script>
        let allData = [];
        let currentRow = null;

        function numberToWords(amount) {
            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            if (amount === 0) return 'Zero Pesos Only';
            function convertHundreds(n) {
                let r = '';
                if (n >= 100) { r += ones[Math.floor(n / 100)] + ' Hundred '; n %= 100; }
                if (n >= 20) { r += tens[Math.floor(n / 10)] + ' '; n %= 10; }
                if (n > 0) { r += ones[n] + ' '; }
                return r;
            }
            let intPart = Math.floor(amount), decPart = Math.round((amount - intPart) * 100), result = '';
            if (intPart >= 1000000) { result += convertHundreds(Math.floor(intPart / 1000000)) + 'Million '; intPart %= 1000000; }
            if (intPart >= 1000) { result += convertHundreds(Math.floor(intPart / 1000)) + 'Thousand '; intPart %= 1000; }
            if (intPart > 0) { result += convertHundreds(intPart); }
            result += 'Pesos';
            if (decPart > 0) result += ' and ' + decPart + '/100';
            return result.trim() + ' Only';
        }

        function fetchVouchers() {
            fetch('<?= BASE_URL ?>/fetchmyvouchers')
                .then(res => res.json())
                .then(data => {
                    allData = data;
                    const tbody = document.getElementById('voucher-tbody');
                    if (!data.length) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No released vouchers yet.</td></tr>`;
                        return;
                    }
                    tbody.innerHTML = data.map(row => {
                        const items = row.items ?? [];
                        const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
                        const isReceived = !!row.receiver_name || !!row.manual_receiver_name;
                        return `
                        <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-blue-500 cursor-pointer underline"
                                onclick="viewVoucher(${JSON.stringify(row).replace(/"/g, '&quot;')})">
                                ${row.control_no}
                            </td>
                            <td class="px-5 py-3 text-gray-600">${row.purpose}</td>
                            <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
                                PhP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-400">
                                ${row.released_at ? new Date(row.released_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'}
                            </td>
                            <td class="px-5 py-3">
                                ${isReceived
                                ? '<span class="bg-green-100 text-green-700 text-[10px] font-semibold px-2 py-1 rounded-full uppercase">Received</span>'
                                : '<span class="bg-orange-100 text-orange-700 text-[10px] font-semibold px-2 py-1 rounded-full uppercase">Pending Acceptance</span>'}
                            </td>
                            <td class="px-5 py-3">
                                <button onclick='viewVoucher(${JSON.stringify(row).replace(/"/g, "&quot;")})'
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                                    <i class="fa-solid fa-eye mr-1"></i>View
                                </button>
                            </td>
                        </tr>`;
                    }).join('');
                });
        }

        function viewVoucher(row) {
            currentRow = row;
            const items = row.items ?? [];
            const total = items.reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);

            document.getElementById('v-control-no').textContent = row.control_no;
            document.getElementById('v-date').textContent = row.date_requested;
            document.getElementById('v-title').textContent = row.title ?? '';
            document.getElementById('v-second-no').textContent = row.second_no ?? '—';
            document.getElementById('v-payee').textContent = row.payee ?? '';
            document.getElementById('v-address').textContent = row.address ?? '';
            document.getElementById('v-purpose').textContent = row.purpose ?? '';
            document.getElementById('v-amount-words').textContent = numberToWords(total);
            document.getElementById('v-total').textContent = 'PhP ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            document.getElementById('v-prepared').textContent = row.prepared_name ?? '';
            document.getElementById('v-prepared-at').textContent = row.prepared_at
                ? new Date(row.prepared_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
            document.getElementById('v-certified').textContent = row.certified_name ?? '';
            document.getElementById('v-certified-at').textContent = row.certified_at
                ? new Date(row.certified_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
            document.getElementById('v-approver').textContent = row.approver_name ?? '';
            document.getElementById('v-approved-at').textContent = row.approved_at
                ? new Date(row.approved_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
            document.getElementById('v-receiver').textContent = row.receiver_name ?? '';
            document.getElementById('v-received-at').textContent = row.received_at
                ? new Date(row.received_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '';

            let rows = '', filled = 0;
            items.forEach(item => {
                if (!item.description) return;
                filled++;
                const amt = parseFloat(item.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                rows += `<tr class="border-t border-gray-200">
                    <td class="px-3 py-2 text-center text-xs text-gray-400 border-r border-gray-200">${filled}</td>
                    <td class="px-4 py-2 border-r border-gray-200 text-sm">${item.description}${item.purpose ? ' — ' + item.purpose : ''}</td>
                    <td class="px-4 py-2 text-right font-mono text-sm">${amt}</td>
                </tr>`;
            });
            for (let e = filled; e < 5; e++) {
                rows += `<tr class="border-t border-gray-200">
                    <td class="px-3 py-2 text-center text-xs text-gray-300 border-r border-gray-200">${e + 1}</td>
                    <td class="px-4 py-2 border-r border-gray-200" style="height:28px;"></td>
                    <td class="px-4 py-2"></td>
                </tr>`;
            }
            document.getElementById('v-items-tbody').innerHTML = rows;

            const footerBtns = document.getElementById('v-footer-btns');
            const closeBtn = `<button onclick="closeViewModal()" class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all border border-gray-200">Close</button>`;

            if (row.manual_receiver_name) {
                footerBtns.innerHTML = closeBtn + `
        <span class="flex items-center gap-2 text-xs text-green-600 font-semibold px-4 py-2">
            <i class="fa-solid fa-circle-check"></i> Received by ${row.manual_receiver_name}
        </span>`;
            } else if (!row.receiver_name) {
                footerBtns.innerHTML = closeBtn + `
        <button onclick="acceptVoucher(${row.id})"
            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
            <i class="fa-solid fa-check mr-1"></i>Accept & Claim Voucher
        </button>`;
            } else {
                footerBtns.innerHTML = closeBtn + `
        <span class="flex items-center gap-2 text-xs text-green-600 font-semibold px-4 py-2">
            <i class="fa-solid fa-circle-check"></i> Already Received
        </span>`;
            }

            document.getElementById('view-modal').classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('view-modal').classList.add('hidden');
        }

        function acceptVoucher(voucherId) {
            fetch('<?= BASE_URL ?>/acceptvoucher', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ voucher_id: voucherId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeViewModal();
                        showToast('Voucher accepted successfully!');
                        fetchVouchers();
                    } else {
                        showToast('Failed to accept voucher.', 'error');
                    }
                });
        }

        function showToast(message, type = 'success') {
            const colors = { success: 'bg-green-500', error: 'bg-red-500' };
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 z-[999] flex items-center gap-3 ${colors[type]} text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg transition-all duration-300 opacity-0 translate-y-2`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${message}`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('opacity-0', 'translate-y-2'));
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        fetchVouchers();
        setInterval(fetchVouchers, 5000);
    </script>
</body>

</html>