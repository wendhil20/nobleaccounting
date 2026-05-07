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
                <div id="view-stamp" class="hidden shrink-0 z-50">
                    <img src="<?= BASE_URL ?>/icon/stamp.png" alt="Approved Stamp"
                        class="absolute w-64 h-64 object-contain opacity-40 rotate-[-12deg] mt-[75px]">
                </div>
            </div>
            <!-- Title + Control -->
            <div class="flex flex-col">
                <div class="flex items-center justify-between px-4 py-2 border-b-2 border-gray-800">
                    <h2 class="font-bold text-sm uppercase tracking-widest whitespace-nowrap">Budget Request Form</h2>
                    <div class="flex items-center gap-2">
                        <!-- Download button — ipapakita lang via JS -->
                        <div id="view-download-btn"></div>
                        <button onclick="closeViewModal()"
                            class="text-gray-400 hover:text-red-500 ml-2 transition-colors">
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
        <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
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

<script>
    let previousCount = 0;

    let rejectTargetId = null;

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

                tbody.innerHTML = data.map(row => `
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
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
                    <td class="px-5 py-3 text-xs text-gray-500">${row.items?.length ?? 0} item(s)</td>
                    <td class="px-5 py-3">${statusBadge(row.status)}</td>
                   <td class="px-5 py-3">${actionButtons(row.id, row.status, row)}</td>
                </tr>
            `).join('');

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
                    previousCount = 0;
                    fetchRequests();
                }
            });
    }

    function actionButtons(id, status, row) {
        const viewBtn = `<button onclick='viewRequest(${JSON.stringify(row)})' 
        class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
        <i class="fa-solid fa-eye mr-1"></i>View
    </button>`;

        if (status !== 'pending') return viewBtn;
        return `
        <div class="flex items-center gap-2">
            ${viewBtn}
            <button onclick="doAction(${id}, 'approved')"
                class="bg-green-500 hover:bg-green-600 text-white text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                <i class="fa-solid fa-check mr-1"></i>Approved
            </button>
            <button onclick="openRejectModal(${id})"
    class="bg-red-500 hover:bg-red-600 text-white text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
    <i class="fa-solid fa-xmark mr-1"></i>Reject
</button>
        </div>`;
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
        document.getElementById('view-items-tbody').innerHTML = items.map((item, i) => {
            const amount = parseFloat(item.amount) || 0;
            total += amount;
            return `
            <tr class="border-t border-gray-200">
    <td class="px-3 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-200 w-10">${i + 1}</td>
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
        if (row.status === 'approved' && row.receiver_name) {
            downloadBtn.innerHTML = `
        <button onclick="downloadPDF(${row.id})"
            class="flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-all">
            <i class="fa-solid fa-file-pdf mr-1"></i>Download PDF
        </button>`;
        } else {
            downloadBtn.innerHTML = '';
        }

        const stampEl = document.getElementById('view-stamp');
        if (row.status === 'approved') {
            stampEl.classList.remove('hidden');
        } else {
            stampEl.classList.add('hidden');
        }

        document.getElementById('view-approved-by').innerHTML = approverName
            ? `<p class="text-sm font-semibold text-gray-800">${approverName}</p>
       <p class="text-[10px] text-gray-400">${approvedAt}</p>`
            : '';
        document.getElementById('view-modal').classList.remove('hidden');
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