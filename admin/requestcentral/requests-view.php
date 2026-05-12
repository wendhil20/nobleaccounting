<?php
// Included sa bawat department main page — hindi standalone
// admin/requestcentral/requests-view.php
?>
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-800">Budget Requests</h1>
    <p class="text-sm text-gray-400 mt-1">Requests sent to you for approval</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <span class="text-sm font-semibold text-gray-700">Incoming Requests</span>
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
                    <th class="px-5 py-3 text-left">Items</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody id="requests-tbody">
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- View Request Modal -->
<div id="view-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-8 overflow-y-auto">
    <div class="bg-white w-full max-w-5xl rounded-sm shadow-xl border border-gray-300 my-auto">

        <!-- Header -->
        <div class="grid grid-cols-[1fr_auto] border-b-2 border-gray-800">

            <!-- Brand + Stamp -->
            <div class="flex items-center gap-4 px-3 py-3 border-r-2 border-gray-800">
                <div class="w-14 h-14 shrink-0">
                    <img src="<?= BASE_URL ?>/icon/logo.png" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div class="w-px h-12 bg-gray-400"></div>
                <div class="flex-1">
                    <h1 class="font-bold text-sm uppercase tracking-wide leading-tight">Noblehome Construction
                        Corporation</h1>
                    <p class="text-[10px] text-gray-500 mt-1 leading-relaxed">
                        1181 MC Premiere Bldg., EDSA Bldg., EDSA Balintawak Quezon City<br>
                        noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell. No. 0968-591-6544
                    </p>
                </div>

            </div>
            <!-- Title + Control -->
            <div class="flex flex-col">
                <div class="flex items-center justify-between px-4 py-2 border-b-2 border-gray-800 gap-4">
                    <h2 class="font-bold text-sm uppercase tracking-widest whitespace-nowrap">Budget Request Form</h2>
                    <div class="flex items-center gap-2 shrink-0">
                        <div id="view-download-btn" class="flex items-center gap-2"></div>
                        <button onclick="closeViewModal()"
                            class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
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
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Requestor
                    Name:</span>
                <p id="view-requestor" class="text-sm text-gray-800"></p>
            </div>
            <div class="flex items-center gap-2 px-6 py-3">
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Purpose of
                    Request:</span>
                <p id="view-purpose" class="text-sm text-gray-800"></p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto max-h-[220px] overflow-y-auto">
            <table class="w-full text-sm border-collapse">
                <thead class="sticky top-0 z-1">

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

        <!-- Status lang — walang action buttons -->
        <div class="flex flex-col px-6 py-4 border-t-2 border-gray-800 gap-2">

            <div id="view-reject-comment"></div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 ">
            <div class="px-5 ">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-regular fa-circle-user text-gray-400 text-lg"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Approved By:</span>
                    <div id="view-status-badge"></div>
                </div>
                <div id="view-approved-by" class="text-center"></div>
                <div class="border-b-2 border-gray-400 mb-1"></div>
                <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">Head</p>
            </div>

            <div class="px-8 py-1">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fa-regular fa-circle-user text-gray-400 text-lg"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Received By:</span>
                </div>
                <!-- Name kapag may nagreceive na -->
                <div id="view-received-by" class="text-center"></div>
                <!-- Button kapag approved pero hindi pa nareceive -->
                <div id="view-receive-btn"></div>
                <div class="border-b-2 border-gray-400 mb-1"></div>
                <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">&nbsp;</p>
            </div>
        </div>

    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-sm uppercase tracking-widest text-red-600">
                <i class="fa-solid fa-xmark mr-2"></i>Reject Request
            </h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <p class="text-sm text-gray-500">Please provide a reason for rejecting this request.</p>
            <textarea id="reject-comment" rows="4" placeholder="Enter reason for rejection..."
                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-800 outline-none focus:border-red-400 focus:ring-1 focus:ring-red-200 resize-none transition-all"></textarea>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
            <button onclick="closeRejectModal()"
                class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all">
                Cancel
            </button>
            <button onclick="confirmReject()"
                class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-5 py-2 rounded transition-all">
                <i class="fa-solid fa-xmark text-xs"></i>
                Confirm Reject
            </button>
        </div>
    </div>
</div>

<!-- Ping Modal -->
<div id="ping-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-sm uppercase tracking-widest text-blue-600">
                <i class="fa-solid fa-bell mr-2"></i>Ping Staff
            </h3>
            <button onclick="closePingModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <p class="text-sm text-gray-500">Select staff to notify about this request:</p>
            <div id="ping-staff-list"
                class="space-y-1 max-h-[200px] overflow-y-auto border border-gray-100 rounded-lg p-2">
                <p class="text-xs text-gray-400">Loading...</p>
            </div>
            <textarea id="ping-message" rows="3" placeholder="Optional message to staff..."
                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-800 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-200 resize-none transition-all"></textarea>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
            <button onclick="closePingModal()" class="text-sm text-gray-500 font-medium px-4 py-2 rounded">
                Cancel
            </button>
            <button onclick="confirmPing()"
                class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-5 py-2 rounded transition-all">
                <i class="fa-solid fa-bell text-xs"></i>Send Ping
            </button>
        </div>
    </div>
</div>

<script>
    let previousCount = 0;

    let rejectTargetId = null;

    let pingTargetRequestId = null;

    function openPingModal(id) {
        pingTargetRequestId = id;
        document.getElementById('ping-message').value = '';
        document.getElementById('ping-staff-list').innerHTML =
            '<p class="text-xs text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Loading staff...</p>';
        document.getElementById('ping-modal').classList.remove('hidden');

        fetch('<?= BASE_URL ?>/pingrequest')
            .then(res => res.json())
            .then(staff => {
                document.getElementById('ping-staff-list').innerHTML = staff.length
                    ? staff.map(s => `
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" value="${s.id}" class="ping-staff-check w-4 h-4 accent-blue-500">
                        <div>
                            <p class="text-sm font-medium text-gray-800">${s.name}</p>
                            <p class="text-[10px] text-gray-400">${s.email ?? ''}</p>
                        </div>
                    </label>`).join('')
                    : '<p class="text-xs text-gray-400">No staff found.</p>';
            });
    }

    function closePingModal() {
        document.getElementById('ping-modal').classList.add('hidden');
        pingTargetRequestId = null;
    }

    function confirmPing() {
        const checked = [...document.querySelectorAll('.ping-staff-check:checked')];
        if (!checked.length) {
            alert('Please select at least one staff to ping.');
            return;
        }

        const staff_ids = checked.map(c => parseInt(c.value));
        const message = document.getElementById('ping-message').value.trim();

        fetch('<?= BASE_URL ?>/pingrequest', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: pingTargetRequestId, staff_ids, message })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closePingModal();
                    showToast('Staff pinged successfully!', 'approved');
                }
            });
    }

    function statusBadge(status) {
        const map = {
            pending: 'bg-yellow-100 text-yellow-700',
            approved: 'bg-green-300 text-green-900',
            rejected: 'bg-red-100 text-red-700',
        };
        return `<span class="${map[status] ?? 'bg-gray-100 text-gray-500'} text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">${status}</span>`;
    }

    function fetchRequests() {
        fetch('<?= BASE_URL ?>/fetchrequests')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('requests-tbody');

                if (!data.length) {
                    tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No requests yet.</td></tr>`;
                    previousCount = 0;
                    return;
                }

                if (data.length === previousCount) return;
                previousCount = data.length;

                tbody.innerHTML = data.map(row => {
                    const isApproved = row.status === 'approved';
                    const isRejected = row.status === 'rejected';

                    const rowClass = isApproved
                        ? 'bg-green-50 hover:bg-green-100'
                        : isRejected
                            ? 'bg-red-50 hover:bg-red-100'
                            : 'hover:bg-gray-50';

                    return `
    <tr class="border-t border-gray-100 transition-colors ${rowClass}">
       <td class="px-5 py-3 font-mono text-xs text-blue-500 underline cursor-pointer" 
           onclick="viewRequest(${JSON.stringify(row).replace(/"/g, '&quot;')})">
               ${row.control_no}
        </td>
        <td class="px-5 py-3">
            <p class="font-medium text-gray-800">${row.requestor_name}</p>
            <p class="text-[10px] text-gray-400">${row.sender_email ?? ''}</p>
        </td>
        <td class="px-5 py-3 text-gray-600">${row.purpose}</td>
        <td class="px-5 py-3 text-xs text-gray-400 font-mono">${row.date_requested}</td>
        <td class="px-5 py-3 text-xs text-gray-500">${row.items?.filter(i => i.description?.trim()).length ?? 0} item(s)</td>

        <td class="px-5 py-3">${statusBadge(row.status)}</td>
        <td class="px-5 py-3">${actionButtons(row.id, row.status, row)}</td>
    </tr>`;
                }).join('');

                document.getElementById('last-updated').textContent =
                    'Updated ' + new Date().toLocaleTimeString('en-PH');
            })
            .catch(err => console.error('Fetch error:', err));
    }

    function doAction(id, action, comment = '') {
        fetch('<?= BASE_URL ?>/actionrequest', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action, comment })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(action === 'approved' ? 'Request approved successfully!' : 'Request rejected.', action);
                    previousCount = 0;
                    fetchRequests();
                }
            });
    }

    function showToast(message, type = 'approved') {
        const existing = document.getElementById('toast-notif');
        if (existing) existing.remove();

        const color = type === 'approved' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'approved' ? 'fa-circle-check' : 'fa-xmark';

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

    function actionButtons(id, status, row) {
        const viewBtn = `<button onclick='viewRequest(${JSON.stringify(row)})' 
        class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
        <i class="fa-solid fa-eye mr-1"></i>View
    </button>`;

        const pingBtn = `<button onclick="openPingModal(${id})"
        class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
        <i class="fa-solid fa-bell mr-1"></i>Ping
    </button>`;

        // ← Pending — walang ping
        if (status === 'pending') return `
        <div class="flex items-center gap-2">
            ${viewBtn}
            <button onclick="doAction(${id}, 'approved')"
                class="bg-green-500 hover:bg-green-600 text-white text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                <i class="fa-solid fa-check mr-1"></i>Approve
            </button>
            <button onclick="openRejectModal(${id})"
                class="bg-red-500 hover:bg-red-600 text-white text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                <i class="fa-solid fa-xmark mr-1"></i>Reject
            </button>
        </div>`;

        // ← Approved — may ping
        if (status === 'approved') return `
        <div class="flex items-center gap-2">
            ${viewBtn}
            ${pingBtn}
        </div>`;

        // ← Rejected — view lang
        return viewBtn;
    }

    function openRejectModal(id) {
        rejectTargetId = id;
        document.getElementById('reject-comment').value = '';
        document.getElementById('reject-modal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('reject-modal').classList.add('hidden');
        rejectTargetId = null;
    }

    function confirmReject() {
        const comment = document.getElementById('reject-comment').value.trim();
        if (!comment) {
            document.getElementById('reject-comment').classList.add('border-red-400');
            document.getElementById('reject-comment').placeholder = 'Reason is required!';
            return;
        }
        document.getElementById('reject-comment').classList.remove('border-red-400');

        const idToReject = rejectTargetId; // ← i-save muna bago i-close
        closeRejectModal();
        doAction(idToReject, 'rejected', comment); // ← gamitin ang saved id
    }

    function downloadPDF(id) {
        window.open('<?= BASE_URL ?>/download-pdfbudgetrequest?id=' + id, '_blank');
    }

    function viewRequest(row) {
        // Fill modal data — tama na ang mapping
        document.getElementById('view-control-no').textContent = row.control_no;
        document.getElementById('view-date').textContent = row.date_requested;
        document.getElementById('view-requestor').textContent = row.requestor_name;
        document.getElementById('view-purpose').textContent = row.purpose;

        // Items
        const items = row.items ?? [];
        let total = 0;
        let rowNum = 0;
        document.getElementById('view-items-tbody').innerHTML = items
            .filter(item => item.description && item.description.trim() !== '')  // ← skip empty
            .map((item) => {
                rowNum++;
                const amount = parseFloat(item.amount) || 0;
                total += amount;
                return `
        <tr class="border-t border-gray-200">
            <td class="px-3 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-200 w-10">${rowNum}</td>
            <td class="px-4 py-2 border-r border-gray-200 min-w-[180px]">${item.description || ''}</td>
            <td class="px-4 py-2 border-r border-gray-200 min-w-[150px]">${item.purpose || ''}</td>
            <td class="px-4 py-2 border-r border-gray-200 text-center w-24">${item.quantity || 0}</td>
            <td class="px-4 py-2 border-r border-gray-200 text-right font-mono w-32">${parseFloat(item.unit_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
            <td class="px-4 py-2 border-r border-gray-200 text-right font-mono w-32">₱ ${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
            <td class="px-4 py-2 min-w-[120px]">${item.notes || ''}</td>
        </tr>`;
            }).join('');

        document.getElementById('view-total').textContent = '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });

        // Status lang — walang accept/reject buttons
        document.getElementById('view-status-badge').innerHTML = statusBadge(row.status);

        // ← DITO ilagay — bago mag-show ng modal
        const approverName = row.approver_name ?? '';
        const approvedAt = row.approved_at
            ? new Date(row.approved_at).toLocaleDateString('en-PH', {
                year: 'numeric', month: 'long', day: 'numeric'
            }) + ' ' + new Date(row.approved_at).toLocaleTimeString('en-PH', {
                hour: 'numeric', minute: '2-digit', hour12: true
            })
            : '';

        // Pagkatapos ng view-status-badge
        const rejectComment = row.reject_comment ?? '';
        const rejectDiv = document.getElementById('view-reject-comment');
        if (rejectDiv) {
            rejectDiv.innerHTML = rejectComment && row.status === 'rejected'
                ? `<div class="mt-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
               <p class="text-[10px] font-bold uppercase tracking-widest text-red-500 mb-1">Reason for Rejection:</p>
               <p class="text-sm text-red-700">${rejectComment}</p>
           </div>`
                : '';
        }

        // Received By
        const receiverName = row.receiver_name ?? '';
        const receivedAt = row.received_at
            ? new Date(row.received_at).toLocaleDateString('en-PH', {
                year: 'numeric', month: 'long', day: 'numeric'
            }) + ' ' + new Date(row.received_at).toLocaleTimeString('en-PH', {
                hour: 'numeric', minute: '2-digit', hour12: true
            })
            : '';

        document.getElementById('view-received-by').innerHTML = receiverName
            ? `<p class="text-sm font-semibold text-gray-800">${receiverName}</p>
       <p class="text-[10px] text-gray-400">${receivedAt}</p>`
            : '';

        // Download PDF button — approved + received na
        const downloadBtn = document.getElementById('view-download-btn');
        const pdfBtn = (row.status === 'approved' && row.receiver_name)
            ? `<button onclick="downloadPDF(${row.id})"
            class="flex items-center gap-1.5 bg-gray-800 hover:bg-gray-900 text-white text-[11px] font-semibold px-3 py-1.5 rounded-md transition-all whitespace-nowrap">
            <i class="fa-solid fa-file-pdf text-[10px]"></i>Download PDF
        </button>`
            : '';

        downloadBtn.innerHTML = `
    ${pdfBtn}
    <button onclick="printRequest(${row.id})"
        class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-[11px] font-semibold px-3 py-1.5 rounded-md transition-all whitespace-nowrap">
        <i class="fa-solid fa-print text-[10px]"></i>Print
    </button>`;

        document.getElementById('view-approved-by').innerHTML = approverName
            ? `<p class="text-sm font-semibold text-gray-800">${approverName}</p>
       <p class="text-[10px] text-gray-400">${approvedAt}</p>`
            : '';
        document.getElementById('view-modal').classList.remove('hidden');
    }

    function printRequest(id) {
        // Hanapin yung row data mula sa current na nakita
        fetch('<?= BASE_URL ?>/fetchrequests')
            .then(res => res.json())
            .then(data => {
                const row = data.find(r => r.id == id);
                if (!row) return;

                const items = row.items ?? [];
                let total = 0;
                let itemRows = '';
                let filledCount = 0;

                items.forEach((item, i) => {
                    if (!item.description) return;
                    filledCount++;
                    const amount = parseFloat(item.amount || 0);
                    total += amount;
                    itemRows += `
                <tr>
                    <td style="text-align:center;border:1px solid #ccc;padding:5px;">${filledCount}</td>
                    <td style="border:1px solid #ccc;padding:5px;">${item.description || ''}</td>
                    <td style="border:1px solid #ccc;padding:5px;">${item.purpose || ''}</td>
                    <td style="text-align:center;border:1px solid #ccc;padding:5px;">${item.quantity || 0}</td>
                    <td style="text-align:right;border:1px solid #ccc;padding:5px;font-family:monospace;">${parseFloat(item.unit_price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                    <td style="text-align:right;border:1px solid #ccc;padding:5px;font-family:monospace;">₱ ${amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                    <td style="border:1px solid #ccc;padding:5px;">${item.notes || ''}</td>
                </tr>`;
                });

                // Empty rows hanggang 5
                for (let e = filledCount; e < 5; e++) {
                    itemRows += `
                <tr>
                    <td style="text-align:center;border:1px solid #ccc;padding:5px;color:#ccc;">${e + 1}</td>
                    <td style="border:1px solid #ccc;padding:5px;height:22px;"></td>
                    <td style="border:1px solid #ccc;padding:5px;"></td>
                    <td style="text-align:center;border:1px solid #ccc;padding:5px;">0</td>
                    <td style="text-align:right;border:1px solid #ccc;padding:5px;font-family:monospace;">0.00</td>
                    <td style="text-align:right;border:1px solid #ccc;padding:5px;font-family:monospace;">₱ 0.00</td>
                    <td style="border:1px solid #ccc;padding:5px;"></td>
                </tr>`;
                }

                const approverName = row.approver_name ?? '';
                const approvedAt = row.approved_at
                    ? new Date(row.approved_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
                    + ' ' + new Date(row.approved_at).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true })
                    : '';
                const receiverName = row.receiver_name ?? '';
                const receivedAt = row.received_at
                    ? new Date(row.received_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
                    + ' ' + new Date(row.received_at).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true })
                    : '';

                const html = `
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Budget Request - ${row.control_no}</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 15px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f97316; color: white; padding: 6px 8px; font-size: 10px; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #555; }
    .control-box { background: #f97316; color: white; font-weight: bold; text-align: center; padding: 4px 8px; font-size: 9px; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sig-line { border-top: 1px solid #999; margin-top: 40px; }
    @page { size: A4 landscape; margin: 1cm; }
</style>
</head>
<body>
<div style="border:1px solid #111;">

    <!-- Header -->
    <table style="border-bottom:1px solid #111;">
        <tr>
            <td style="width:60%;border-right:1px solid #111;padding:10px;">
                <table>
                    <tr>
                        <td style="width:60px;">
                            <img src="<?= BASE_URL ?>/icon/logo.png" style="width:50px;height:50px;object-fit:contain;">
                        </td>
                        <td style="border-left:1px solid #aaa;padding-left:10px;">
                            <strong style="font-size:11px;text-transform:uppercase;">Noblehome Construction Corporation</strong><br>
                            <span style="font-size:9px;color:#666;">
                                1181 MC Premiere Bldg., EDSA Balintawak Quezon City<br>
                                noblehomeconsl.ph@gmail.com | Tel. No. 02-88221295 | Cell. No. 0968-591-6544
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:40%;padding:0;vertical-align:top;">
                <div style="font-weight:bold;font-size:12px;text-transform:uppercase;letter-spacing:1px;padding:8px;border-bottom:1px solid #111;">
                    Budget Request Form
                </div>
                <table style="width:100%;">
                    <tr>
                        <td style="width:50%;border-right:1px solid #111;padding:0;vertical-align:top;">
                            <div class="control-box">Control No.</div>
                            <div style="text-align:center;font-family:monospace;font-size:10px;padding:6px;background:#f9fafb;">
                                ${row.control_no}
                            </div>
                        </td>
                        <td style="width:50%;padding:0;vertical-align:top;">
                            <div class="control-box">Date</div>
                            <div style="text-align:center;font-family:monospace;font-size:10px;padding:6px;background:#f9fafb;">
                                ${row.date_requested}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Requestor + Purpose -->
    <table style="border-bottom:1px solid #111;">
        <tr>
            <td style="width:50%;border-right:1px solid #111;padding:8px;">
                <span class="label">Requestor Name:</span>
                <span style="font-size:12px;margin-left:5px;">${row.requestor_name}</span>
            </td>
            <td style="width:50%;padding:8px;">
                <span class="label">Purpose of Request:</span>
                <span style="font-size:12px;margin-left:5px;">${row.purpose}</span>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table style="border-collapse:collapse;width:100%;border-bottom:1px solid #111;">
        <thead>
            <tr>
                <th style="width:5%;border:1px solid #ea6c00;">No.</th>
                <th style="border:1px solid #ea6c00;text-align:left;">Items / Description</th>
                <th style="border:1px solid #ea6c00;text-align:left;">Purpose</th>
                <th style="width:8%;border:1px solid #ea6c00;">Qty</th>
                <th style="width:13%;border:1px solid #ea6c00;">Unit Price</th>
                <th style="width:13%;border:1px solid #ea6c00;">Amount</th>
                <th style="border:1px solid #ea6c00;text-align:left;">Notes</th>
            </tr>
        </thead>
        <tbody>${itemRows}</tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right;font-weight:bold;padding:6px;border:1px solid #ccc;font-size:10px;text-transform:uppercase;">Total:</td>
                <td style="text-align:right;font-weight:bold;font-family:monospace;border:1px solid #ccc;padding:6px;">₱ ${total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td style="border:1px solid #ccc;"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Signatures -->
    <table style="width:100%;">
        <tr>
            <td style="width:50%;border-right:1px solid #111;padding:15px 20px;vertical-align:bottom;">
                <span class="label">Approved By:</span>
                <div style="margin-top:20px;text-align:center;">
                    <strong style="font-size:12px;">${approverName}</strong><br>
                    <span style="font-size:9px;color:#888;">${approvedAt}</span>
                </div>
                <div class="sig-line"></div>
                <div style="text-align:center;font-size:9px;text-transform:uppercase;color:#888;margin-top:3px;">Head</div>
            </td>
            <td style="width:50%;padding:15px 20px;vertical-align:bottom;">
                <span class="label">Received By:</span>
                <div style="margin-top:20px;text-align:center;">
                    <strong style="font-size:12px;">${receiverName}</strong><br>
                    <span style="font-size:9px;color:#888;">${receivedAt}</span>
                </div>
                <div class="sig-line"></div>
                <div style="text-align:center;font-size:9px;color:#888;margin-top:3px;">&nbsp;</div>
            </td>
        </tr>
    </table>

</div>
<script>
    window.onload = function() {
        window.print();
        window.onafterprint = function() { window.close(); };
    };
<\/script>
</body>
</html>`;

                const printWindow = window.open('', '_blank');
                printWindow.document.write(html);
                printWindow.document.close();
            });
    }

    function closeViewModal() {
        document.getElementById('view-modal').classList.add('hidden');
    }

    fetchRequests();

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            previousCount = 0;
            fetchRequests();
        }
    });
</script>