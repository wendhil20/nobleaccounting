<?php
// user/ui/index-main-page-1.php
session_name('noblehome');
session_start();

include ROOT_PATH . '/network/connect.php';

// Redirect to login if not logged in
if (empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

function generateControlNo($conn)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $rand = '';
        for ($i = 0; $i < 8; $i++) {
            $rand .= $chars[rand(0, strlen($chars) - 1)];
        }
        $control = 'NHREQUEST-' . $rand;
        $stmt = $conn->prepare("SELECT id FROM noblebudgetrequest WHERE control_no = ?");
        $stmt->bind_param("s", $control);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $control;
}

$control_no = generateControlNo($conn);
$today = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Request Form NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/user/navigation/top.php'; ?>
</head>

<body class="min-h-screen flex items-center justify-center px-4 py-12 relative"
    style="background-image: url('<?= BASE_URL ?>/icon/building2.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <div class="max-w-5xl mx-auto relative z-10">
        <div class="bg-white border border-gray-300 shadow-md rounded-sm overflow-hidden">

            <!-- ── Header ── -->
            <div class="grid grid-cols-[1fr_auto] border-b-2 border-gray-800">

                <!-- Brand -->
                <div class="flex items-center gap-4 px-6 py-4 border-r-2 border-gray-800">
                    <div class="w-14 h-14 shrink-0">
                        <img src="<?= BASE_URL ?>/icon/logo.png" alt="Noblehome Logo"
                            class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="font-bold text-sm uppercase tracking-wide leading-tight">Noblehome Construction
                            Corporation</h1>
                        <p class="text-[10px] text-gray-500 mt-1 leading-relaxed">
                            1181 MC Premiere Bldg., EDSA Bldg., EDSA Balintawak Quezon City<br>
                            noblehomeconsl.ph@gmail.com &nbsp;|&nbsp; Tel. No. 02-88221295 &nbsp;|&nbsp; Cell. No.
                            0968-591-6544
                        </p>
                    </div>
                </div>

                <!-- Title + Control No (2nd grid column) -->
                <div class="flex flex-col       ">
                    <!-- Title row -->
                    <div class="flex items-center justify-center px-6 py-2 border-b-2 border-gray-800">
                        <h2 class="font-bold text-sm uppercase tracking-widest whitespace-nowrap">Budget Request Form
                        </h2>
                    </div>
                    <!-- Control No. + Date row -->
                    <div class="flex flex-row flex-1 text-[10px]">
                        <!-- Control No. Cell -->
                        <div class="flex flex-col border-r-2 border-gray-800 flex-1">
                            <span
                                class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Control
                                No.</span>
                            <input type="text" value="<?= htmlspecialchars($control_no) ?>" readonly
                                class="flex-1 px-4 py-1 font-mono text-xs outline-none bg-gray-50 text-center cursor-not-allowed">
                        </div>
                        <!-- Date Cell -->
                        <div class="flex flex-col flex-1">
                            <span
                                class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Date:</span>
                            <input type="date" value="<?= $today ?>" readonly
                                class="flex-1 px-4 py-1 font-mono text-xs outline-none bg-gray-50 text-center cursor-not-allowed">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Requestor + Purpose ── -->
            <div class="grid grid-cols-2 border-b-2 border-gray-800">
                <div class="flex items-center gap-2 px-6 py-3 border-r-2 border-gray-800">
                    <span
                        class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Requestor
                        Name:</span>
                    <input type="text" id="requestor-name-input"
                        class="flex-1 border-b-2 border-gray-400 outline-none text-sm py-0.5 focus:border-orange-500 bg-transparent transition-colors">
                </div>
                <div class="flex items-center gap-2 px-6 py-3">
                    <span
                        class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Purpose
                        of Request:</span>
                    <input type="text" id="purpose-input"
                        class="flex-1 border-b-2 border-gray-400 outline-none text-sm py-0.5 focus:border-orange-500 bg-transparent transition-colors">
                </div>
            </div>

            <!-- ── Table ── -->
            <div class="overflow-x-auto max-h-[220px] overflow-y-auto">
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
                    <tbody id="item-rows">
                        <!-- Rows injected by JS -->
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-800 bg-gray-50">
                            <td colspan="5"
                                class="px-4 py-2 font-bold text-xs uppercase tracking-widest text-right border-r border-gray-300">
                                Total:</td>
                            <td class="px-4 py-2 font-bold font-mono text-right" id="grand-total">₱ 0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- ── Add Row Button ── -->
            <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                <button onclick="addRow()"
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 active:scale-95 text-white text-xs font-semibold px-4 py-2 rounded transition-all">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    Add Item
                </button>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-gray-400 font-mono">20260425-BRF-v1</span>
                    <button onclick="openSubmitModal()"
                        class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 active:scale-95 text-white text-xs font-semibold px-4 py-2 rounded transition-all">
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        Submit Request
                    </button>
                </div>
            </div>

            <!-- ── Footer Signatures ── -->
            <div class="grid grid-cols-2 border-t-2 border-gray-800">
                <div class="px-8 py-5 border-r-2 border-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-regular fa-circle-user text-gray-400 text-lg"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Approved By:</span>
                    </div>
                    <div class="border-b-2 border-gray-400 mt-8 mb-1"></div>
                    <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">Head</p>
                </div>
                <div class="px-8 py-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-regular fa-circle-user text-gray-400 text-lg"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Received By:</span>
                    </div>
                    <div class="border-b-2 border-gray-400 mt-8 mb-1"></div>
                    <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">&nbsp;</p>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Submit Modal ── -->
    <div id="submit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-sm uppercase tracking-widest text-gray-800">Submit Request</h3>
                <button onclick="closeSubmitModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-500">Select the head you want to send this request to:</p>

                <!-- Head Selection -->
                <div id="heads-list" class="space-y-2 max-h-60 overflow-y-auto">
                    <div class="text-center text-gray-400 text-sm py-4">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                    </div>
                </div>

                <!-- Selected Head Display -->
                <div id="selected-head-display"
                    class="hidden bg-orange-50 border border-orange-200 rounded-lg px-4 py-3">
                    <p class="text-xs text-orange-600 font-semibold uppercase tracking-wider mb-1">Sending to:</p>
                    <p id="selected-head-name" class="text-sm font-bold text-gray-800"></p>
                    <p id="selected-head-role" class="text-[10px] text-gray-500 uppercase tracking-wide"></p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                <button onclick="closeSubmitModal()"
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all">
                    Cancel
                </button>
                <button id="confirm-submit-btn" onclick="confirmSubmit()" disabled
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded transition-all">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Confirm & Submit
                </button>
            </div>
        </div>
    </div>

    <script>
        let rowCount = 0;
        let selectedHeadId = null;

        function openSubmitModal() {
            document.getElementById('submit-modal').classList.remove('hidden');
            selectedHeadId = null;
            document.getElementById('confirm-submit-btn').disabled = true;
            document.getElementById('selected-head-display').classList.add('hidden');
            loadHeads();
        }

        function closeSubmitModal() {
            document.getElementById('submit-modal').classList.add('hidden');
        }

        function loadHeads() {
            fetch('<?= BASE_URL ?>/fetchheads')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('heads-list');

                    if (!data.length) {
                        list.innerHTML = `<p class="text-center text-gray-400 text-sm py-4">No heads available.</p>`;
                        return;
                    }

                    list.innerHTML = data.map(head => `
                <div onclick="selectHead(${head.id}, '${head.name}', '${head.role}')"
                    id="head-option-${head.id}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg border-2 border-transparent hover:border-orange-300 hover:bg-orange-50 cursor-pointer transition-all">
                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user text-orange-500 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">${head.name}</p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">${head.role}</p>
                    </div>
                </div>
            `).join('');
                });
        }

        function selectHead(id, name, role) {
            selectedHeadId = id;

            // Highlight selected
            document.querySelectorAll('[id^="head-option-"]').forEach(el => {
                el.classList.remove('border-orange-500', 'bg-orange-50');
                el.classList.add('border-transparent');
            });
            document.getElementById('head-option-' + id).classList.add('border-orange-500', 'bg-orange-50');
            document.getElementById('head-option-' + id).classList.remove('border-transparent');

            // Show selected display
            document.getElementById('selected-head-name').textContent = name;
            document.getElementById('selected-head-role').textContent = role;
            document.getElementById('selected-head-display').classList.remove('hidden');

            // Enable confirm button
            document.getElementById('confirm-submit-btn').disabled = false;
        }

        function confirmSubmit() {
    if (!selectedHeadId) return;

    // Kunin ang form values
    const controlNo      = document.querySelector('input[value^="NHREQUEST"]').value;
    const requestorName = document.getElementById('requestor-name-input').value;
    const purpose       = document.getElementById('purpose-input').value;
    const dateRequested  = document.querySelector('input[type="date"]').value;

    // Kunin ang items
    const items = [];
    document.querySelectorAll('#item-rows tr').forEach(tr => {
        const inputs = tr.querySelectorAll('input');
        const amount = tr.querySelector('.row-amount')?.textContent.replace('₱', '').replace(/,/g, '').trim();
        items.push({
            description : inputs[0]?.value || '',
            purpose     : inputs[1]?.value || '',
            quantity    : inputs[2]?.value || 0,
            unit_price  : inputs[3]?.value || 0,
            amount      : amount || '0.00',
            notes       : inputs[4]?.value || ''
        });
    });

    // Validate
    if (!requestorName || !purpose) {
        alert('Please fill in Requestor Name and Purpose before submitting.');
        return;
    }

    const btn = document.getElementById('confirm-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Submitting...';

    fetch('<?= BASE_URL ?>/submitrequest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            control_no      : controlNo,
            requestor_name  : requestorName,
            purpose         : purpose,
            date_requested  : dateRequested,
            sent_to         : selectedHeadId,
            items           : items
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeSubmitModal();
            // Success notification
            const notif = document.createElement('div');
            notif.className = 'fixed top-5 right-5 z-50 bg-green-500 text-white text-sm font-semibold px-5 py-3 rounded-lg shadow-lg flex items-center gap-2';
            notif.innerHTML = '<i class="fa-solid fa-check"></i> Request submitted successfully!';
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        } else {
            alert('Failed: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> Confirm & Submit';
        }
    })
    .catch(() => {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> Confirm & Submit';
    });
}

        function addRow() {
            rowCount++;
            const tbody = document.getElementById('item-rows');

            const tr = document.createElement('tr');
            tr.className = 'border-t border-gray-200 hover:bg-orange-50 transition-colors group';
            tr.dataset.row = rowCount;
            tr.innerHTML = `
        <td class="px-3 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-200">${rowCount}</td>
        <td class="px-2 py-1 border-r border-gray-200">
            <input type="text" placeholder="Description..." class="w-full outline-none text-sm py-1 px-1 bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
        </td>
        <td class="px-2 py-1 border-r border-gray-200">
            <input type="text" placeholder="Purpose..." class="w-full outline-none text-sm py-1 px-1 bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
        </td>
        <td class="px-2 py-1 border-r border-gray-200">
            <input type="number" min="0" placeholder="0" oninput="calcRow(this)" data-type="qty"
                class="w-full outline-none text-sm py-1 px-1 text-center bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
        </td>
        <td class="px-2 py-1 border-r border-gray-200">
            <input type="number" min="0" step="0.01" placeholder="0.00" oninput="calcRow(this)" data-type="price"
                class="w-full outline-none text-sm py-1 px-1 text-right bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
        </td>
        <td class="px-3 py-2 border-r border-gray-200 text-right font-mono text-sm text-gray-700 row-amount">₱ 0.00</td>
        <td class="px-2 py-1">
            <div class="flex items-center gap-1">
                <input type="text" placeholder="Notes..." class="flex-1 outline-none text-sm py-1 px-1 bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
                <button onclick="removeRow(this)" title="Remove"
                    class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all ml-1 shrink-0">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </td>
    `;
            tbody.appendChild(tr);
            tr.querySelector('input').focus();
        }

        function calcRow(input) {
            const tr = input.closest('tr');
            const qty = parseFloat(tr.querySelector('[data-type="qty"]').value) || 0;
            const price = parseFloat(tr.querySelector('[data-type="price"]').value) || 0;
            tr.querySelector('.row-amount').textContent = '₱ ' + (qty * price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            calcTotal();
        }

        function calcTotal() {
            let total = 0;
            document.querySelectorAll('.row-amount').forEach(cell => {
                total += parseFloat(cell.textContent.replace('₱', '').replace(/,/g, '')) || 0;
            });
            document.getElementById('grand-total').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function removeRow(btn) {
            const tr = btn.closest('tr');
            tr.style.opacity = '0';
            tr.style.transition = 'opacity 0.2s';
            setTimeout(() => {
                tr.remove();
                document.querySelectorAll('#item-rows tr').forEach((row, i) => {
                    row.cells[0].textContent = i + 1;
                });
                rowCount = document.querySelectorAll('#item-rows tr').length;
                calcTotal();
            }, 200);
        }

        // Start with 5 rows like the original form
        for (let i = 0; i < 5; i++) addRow();
    </script>

</body>

</html>