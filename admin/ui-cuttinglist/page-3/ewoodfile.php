<?php
//  ewoodfile.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_CUTTING];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$cutListAjaxUrl = BASE_URL . '/cuttinglistajax';
$cutProgAjaxUrl = BASE_URL . '/crmewoodajax';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphic Design Dashboard</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main class="ml-56 min-h-screen p-8 overflow-x-hidden">

        <div class="max-w-7xl mx-auto">

            <div class="mb-5">
                <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase mb-0.5">Cutting Department</p>
                <h1 class="text-gray-900 text-xl font-semibold">Cutting Files &amp; Progress</h1>
                <p class="text-gray-400 text-xs mt-0.5">Upload cutting archives (.zip / .rar) and reference photos per approved submission. Only records with Notice to Proceed can be uploaded to.</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-left text-[10px] uppercase tracking-wide text-gray-500">
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Control No.</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Client</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Branch</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Project Type</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">Latest Progress</th>
                                <th class="px-4 py-2.5 font-semibold whitespace-nowrap">NTP Status</th>
                                <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody id="ewoodTbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

            <p id="ewoodCount" class="text-[11px] text-gray-400 mt-2.5"></p>
        </div>

        <!-- Upload / history modal -->
        <div id="ewoodModal" class="hidden fixed inset-0 bg-black/40 z-[999] flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 id="ewoodModalTitle" class="text-sm font-semibold text-gray-800">Cutting Progress</h2>
                    <button type="button" id="ewoodModalClose" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>

                <form id="ewoodUploadForm" class="px-5 py-4 space-y-3">
                    <input type="hidden" name="quotation_id" id="ewoodQuotationId">

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Archive (.zip / .rar)</label>
                        <input type="file" name="archive" accept=".zip,.rar"
                            class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 file:mr-3 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-amber-50 file:text-amber-700 file:text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Photos (auto-converted to WebP)</label>
                        <input type="file" name="images[]" multiple accept="image/*"
                            class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 file:mr-3 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-amber-50 file:text-amber-700 file:text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Status</label>
                        <select name="status" class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Remarks</label>
                        <textarea name="remarks" rows="2" class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2 text-xs font-semibold text-white bg-amber-700 rounded-lg hover:bg-amber-800 transition-colors">
                        Save Update
                    </button>
                </form>

                <div class="px-5 pb-5">
                    <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">History</h3>
                    <div id="ewoodHistory" class="space-y-3 text-xs"></div>
                </div>
            </div>
        </div>

        <div id="crmToastContainer" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0"></div>

    </main>

    <script>
        function crmShowToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('crmToastContainer');
            const palette = type === 'success'
                ? { wrap: 'bg-green-50 border-green-200 text-green-700', icon: 'bg-green-200 text-green-700', symbol: '✓' }
                : { wrap: 'bg-red-50 border-red-200 text-red-700', icon: 'bg-red-200 text-red-700', symbol: '!' };
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-2.5 border rounded-lg shadow-lg px-4 py-3 text-sm ${palette.wrap} translate-x-6 opacity-0 scale-95 transition-all duration-300 ease-out`;
            toast.innerHTML = `<span class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold ${palette.icon}">${palette.symbol}</span>
                <span class="flex-1 leading-relaxed">${message}</span>
                <button type="button" class="shrink-0 text-current opacity-50 hover:opacity-100 text-base leading-none">&times;</button>`;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-x-6', 'opacity-0', 'scale-95'));
            const remove = () => { toast.classList.add('translate-x-6', 'opacity-0', 'scale-95'); setTimeout(() => toast.remove(), 300); };
            toast.querySelector('button').addEventListener('click', remove);
            if (duration > 0) setTimeout(remove, duration);
        }

        const CUT_LIST_AJAX_URL = <?= json_encode($cutListAjaxUrl) ?>;
        const CUT_PROG_AJAX_URL = <?= json_encode($cutProgAjaxUrl) ?>;

        function ewoodEscapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function ewoodFormatDate(value) {
            if (!value) return '—';
            const dt = new Date(value.replace(' ', 'T'));
            if (isNaN(dt.getTime())) return value;
            return dt.toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
        }

        // NTP badge — same convention as cuttinglist main table.
        function ewoodNtpBadge(row) {
            const isNtp = row.deposit_status === 'Notice to Proceed';
            const cls = isNtp
                ? 'bg-green-50 text-green-700 border-green-200'
                : 'bg-amber-50 text-amber-700 border-amber-200';
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold border whitespace-nowrap ${cls}">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 ${isNtp ? 'bg-green-600' : 'bg-amber-600'}"></span>
                        ${isNtp ? 'NTP' : 'Hold'}
                    </span>`;
        }

        async function ewoodFetchList() {
            const tbody = document.getElementById('ewoodTbody');
            try {
                const res = await fetch(`${CUT_LIST_AJAX_URL}?action=list&filter=all`);
                const data = await res.json();
                if (!data.success) { crmShowToast('Failed to load submissions.', 'error'); return; }

                if (data.rows.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-gray-400 py-8">No approved submissions found.</td></tr>`;
                    document.getElementById('ewoodCount').textContent = '';
                    return;
                }

                tbody.innerHTML = data.rows.map(row => {
                    const isNtp = row.deposit_status === 'Notice to Proceed';
                    return `
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-4 py-2.5"><span class="font-mono text-[11px] font-semibold text-amber-700">${ewoodEscapeHtml(row.control_no)}</span></td>
                        <td class="px-4 py-2.5 text-gray-800 font-medium">${ewoodEscapeHtml(row.client_name)}</td>
                        <td class="px-4 py-2.5 text-gray-600">${ewoodEscapeHtml(row.branch)}</td>
                        <td class="px-4 py-2.5 text-gray-600">${ewoodEscapeHtml(row.project_type ?? '—')}</td>
                        <td class="px-4 py-2.5 text-gray-400" id="ewoodLatest_${row.id}">—</td>
                        <td class="px-4 py-2.5">${ewoodNtpBadge(row)}</td>
                        <td class="px-4 py-2.5 text-right">
                            ${isNtp
                            ? `<button type="button" onclick="ewoodOpenModal(${row.id}, '${ewoodEscapeHtml(row.control_no)}')"
                                    class="px-2.5 py-1.5 text-[11px] font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    Upload
                                </button>`
                            : `<button type="button" disabled title="Waiting for Notice to Proceed from Accounting"
                                    class="px-2.5 py-1.5 text-[11px] font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                    Upload
                                </button>`}
                        </td>
                    </tr>
                `;
                }).join('');

                document.getElementById('ewoodCount').textContent = `${data.count} submission${data.count === 1 ? '' : 's'} found`;

                // Load latest-progress label per row, NTP or not — history is
                // just informational so it's fine to always fetch it.
                data.rows.forEach(row => ewoodLoadLatest(row.id));

            } catch (e) {
                console.error(e);
                crmShowToast('Connection error while fetching submissions.', 'error');
            }
        }

        function ewoodRenderHistory(entries) {
            const box = document.getElementById('ewoodHistory');
            if (entries.length === 0) {
                box.innerHTML = `<p class="text-gray-400">No updates yet.</p>`;
                return;
            }
            box.innerHTML = entries.map(e => `
                <div class="border border-gray-100 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-semibold text-gray-700">${e.status}</span>
                        <span class="text-gray-400 text-[10px]">${ewoodFormatDate(e.created_at)}</span>
                    </div>
                    ${e.remarks ? `<p class="text-gray-600 mb-1.5">${ewoodEscapeHtml(e.remarks)}</p>` : ''}
                    ${e.archive_url ? `<a href="${e.archive_url}" target="_blank" class="inline-flex items-center gap-1 text-amber-700 hover:underline mb-1.5"><i class="fa-solid fa-file-zipper"></i> ${ewoodEscapeHtml(e.archive_original_name || 'Download archive')}</a>` : ''}
                    ${e.photos.length ? `<div class="flex flex-wrap gap-1.5 mt-1">${e.photos.map(p => `<a href="${p}" target="_blank"><img src="${p}" class="w-14 h-14 object-cover rounded border border-gray-200"></a>`).join('')}</div>` : ''}
                    <p class="text-gray-400 text-[10px] mt-1.5">by ${ewoodEscapeHtml(e.uploaded_by_name)}</p>
                </div>
            `).join('');
        }

        // Only updates the "Latest Progress" cell — used on initial list load.
        async function ewoodLoadLatest(quotationId) {
            try {
                const res = await fetch(`${CUT_PROG_AJAX_URL}?action=history&quotation_id=${quotationId}`);
                const data = await res.json();
                if (!data.success) return;
                const latestCell = document.getElementById(`ewoodLatest_${quotationId}`);
                if (latestCell && data.entries.length) {
                    latestCell.textContent = `${data.entries[0].status} · ${ewoodFormatDate(data.entries[0].created_at)}`;
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function ewoodLoadHistory(quotationId) {
            try {
                const res = await fetch(`${CUT_PROG_AJAX_URL}?action=history&quotation_id=${quotationId}`);
                const data = await res.json();
                if (!data.success) return;
                ewoodRenderHistory(data.entries);
                const latestCell = document.getElementById(`ewoodLatest_${quotationId}`);
                if (latestCell && data.entries.length) {
                    latestCell.textContent = `${data.entries[0].status} · ${ewoodFormatDate(data.entries[0].created_at)}`;
                }
            } catch (e) {
                console.error(e);
            }
        }

        function ewoodOpenModal(quotationId, controlNo) {
            document.getElementById('ewoodQuotationId').value = quotationId;
            document.getElementById('ewoodModalTitle').textContent = `Cutting Progress — ${controlNo}`;
            document.getElementById('ewoodUploadForm').reset();
            document.getElementById('ewoodQuotationId').value = quotationId;
            document.getElementById('ewoodModal').classList.remove('hidden');
            ewoodLoadHistory(quotationId);
        }

        document.getElementById('ewoodModalClose').addEventListener('click', () => {
            document.getElementById('ewoodModal').classList.add('hidden');
        });

        document.getElementById('ewoodUploadForm').addEventListener('submit', async function (ev) {
            ev.preventDefault();
            const formData = new FormData(this);
            const quotationId = document.getElementById('ewoodQuotationId').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';

            try {
                const res = await fetch(`${CUT_PROG_AJAX_URL}?action=upload`, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    crmShowToast(data.message, 'success');
                    this.reset();
                    document.getElementById('ewoodQuotationId').value = quotationId;
                    ewoodLoadHistory(quotationId);
                } else {
                    crmShowToast(data.message, 'error');
                }
            } catch (e) {
                console.error(e);
                crmShowToast('Connection error while saving.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Update';
            }
        });

        ewoodFetchList();
    </script>
</body>

</html>