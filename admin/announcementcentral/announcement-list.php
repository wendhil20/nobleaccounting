<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? '';
    $id = intval($body['id'] ?? 0);

    if ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE nobleannouncement SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE nobleannouncement SET is_active = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM nobleannouncement WHERE id = ?");
        $stmt->bind_param("i", $id);
    }

    $success = isset($stmt) ? $stmt->execute() : false;
    echo json_encode(['success' => $success]);
    exit;
}
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Announcements</h1>
        <p class="text-sm text-gray-400 mt-1">Manage all posted announcements</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
        <span class="text-sm font-semibold text-gray-700">All Announcements</span>
        <div class="flex items-center gap-2">
            <span id="ann-last-updated" class="text-[10px] text-gray-400"></span>
            <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
        </div>
    </div>

    <!-- Desktop Table (hidden on mobile) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                    <th class="px-5 py-3 text-left">Template</th>
                    <th class="px-5 py-3 text-left">Title</th>
                    <th class="px-5 py-3 text-left">Posted By</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody id="ann-tbody-desktop">
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>Loading...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card List (visible on mobile only) -->
    <div class="md:hidden divide-y divide-gray-100" id="ann-card-list">
        <div class="px-4 py-8 text-center text-gray-400 text-sm">
            <i class="fa-solid fa-spinner fa-spin mr-2"></i>Loading...
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="ann-preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <span class="text-sm font-bold text-gray-700 uppercase tracking-widest">Preview</span>
            <button onclick="closeAnnPreview()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div id="ann-preview-content" class="overflow-hidden rounded-b-xl"></div>
    </div>
</div>

<script>
    const templateLabel = {
        1: '<span class="bg-orange-100 text-orange-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">General</span>',
        2: '<span class="bg-red-100 text-red-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Urgent</span>',
        3: '<span class="bg-slate-100 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Event</span>',
    };

    function fetchAnnouncements() {
        fetch('<?= BASE_URL ?>/fetchannouncementsadmin')
            .then(res => res.json())
            .then(data => {
                renderDesktop(data);
                renderMobile(data);
                document.getElementById('ann-last-updated').textContent =
                    'Updated ' + new Date().toLocaleTimeString('en-PH');
            });
    }

    function renderDesktop(data) {
        const tbody = document.getElementById('ann-tbody-desktop');
        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No announcements yet.</td></tr>`;
            return;
        }
        tbody.innerHTML = data.map(row => `
            <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">${templateLabel[row.template] ?? row.template}</td>
                <td class="px-5 py-3 font-medium text-gray-800 max-w-[200px] truncate">${row.title}</td>
                <td class="px-5 py-3 text-gray-500">${row.posted_by_name ?? 'Unknown'}</td>
                <td class="px-5 py-3 text-xs text-gray-400 font-mono">${row.created_at}</td>
                <td class="px-5 py-3">${statusBadge(row.is_active)}</td>
                <td class="px-5 py-3">${actionButtons(row)}</td>
            </tr>`).join('');
    }

    function renderMobile(data) {
        const list = document.getElementById('ann-card-list');
        if (!data.length) {
            list.innerHTML = `<div class="px-4 py-8 text-center text-gray-400 text-sm">No announcements yet.</div>`;
            return;
        }
        list.innerHTML = data.map(row => `
            <div class="px-4 py-4 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">${row.title}</p>
                        <p class="text-xs text-gray-400 mt-0.5">${row.posted_by_name ?? 'Unknown'} · <span class="font-mono">${row.created_at}</span></p>
                    </div>
                    <div class="shrink-0 flex items-center gap-1.5">
                        ${templateLabel[row.template] ?? row.template}
                        ${statusBadge(row.is_active)}
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap pt-1">
                    <button onclick='previewAnn(${JSON.stringify(row)})'
                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                        <i class="fa-solid fa-eye mr-1"></i>View
                    </button>
                    ${row.is_active == 1
                        ? `<button onclick="toggleAnn(${row.id}, 'deactivate')"
                                class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                                <i class="fa-solid fa-eye-slash mr-1"></i>Hide
                            </button>`
                        : `<button onclick="toggleAnn(${row.id}, 'activate')"
                                class="bg-green-100 hover:bg-green-200 text-green-700 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                                <i class="fa-solid fa-eye mr-1"></i>Show
                            </button>`
                    }
                    <button onclick="deleteAnn(${row.id})"
                        class="bg-red-100 hover:bg-red-200 text-red-600 text-[10px] font-semibold px-3 py-1.5 rounded-full transition-all">
                        <i class="fa-solid fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>`).join('');
    }

    function statusBadge(is_active) {
        return is_active == 1
            ? '<span class="bg-green-100 text-green-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Active</span>'
            : '<span class="bg-gray-100 text-gray-500 text-[10px] font-semibold px-2 py-0.5 rounded-full">Inactive</span>';
    }

    function actionButtons(row) {
        return `<div class="flex items-center gap-2">
            <button onclick='previewAnn(${JSON.stringify(row)})'
                class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                <i class="fa-solid fa-eye mr-1"></i>View
            </button>
            ${row.is_active == 1
                ? `<button onclick="toggleAnn(${row.id}, 'deactivate')"
                        class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                        <i class="fa-solid fa-eye-slash mr-1"></i>Hide
                    </button>`
                : `<button onclick="toggleAnn(${row.id}, 'activate')"
                        class="bg-green-100 hover:bg-green-200 text-green-700 text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                        <i class="fa-solid fa-eye mr-1"></i>Show
                    </button>`
            }
            <button onclick="deleteAnn(${row.id})"
                class="bg-red-100 hover:bg-red-200 text-red-600 text-[10px] font-semibold px-3 py-1 rounded-full transition-all">
                <i class="fa-solid fa-trash mr-1"></i>Delete
            </button>
        </div>`;
    }

    function toggleAnn(id, action) {
        fetch('<?= BASE_URL ?>/deleteannouncement', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action })
        })
            .then(res => res.json())
            .then(data => { if (data.success) fetchAnnouncements(); });
    }

    function deleteAnn(id) {
        if (!confirm('Delete this announcement?')) return;
        fetch('<?= BASE_URL ?>/deleteannouncement', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action: 'delete' })
        })
            .then(res => res.json())
            .then(data => { if (data.success) fetchAnnouncements(); });
    }

    function previewAnn(row) {
        document.getElementById('ann-preview-content').innerHTML = buildPreviewHTML(row);
        document.getElementById('ann-preview-modal').classList.remove('hidden');
    }

    function closeAnnPreview() {
        document.getElementById('ann-preview-modal').classList.add('hidden');
    }

    function buildPreviewHTML(row) {
        const today = new Date(row.created_at).toLocaleDateString('en-PH', {
            year: 'numeric', month: 'long', day: 'numeric'
        });
        const day = new Date(row.created_at).getDate();
        const month = new Date(row.created_at).toLocaleString('en-PH', { month: 'short' }).toUpperCase();

        const previews = {
            1: `<div style="background:#f97316;padding:1.25rem 1.5rem;">
                    <p style="font-size:10px;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1px;margin:0;">General Announcement</p>
                    <h2 style="color:#fff;font-size:16px;font-weight:600;margin:4px 0 0;">${row.title}</h2>
                </div>
                <div style="padding:1.25rem 1.5rem;background:#fff;">
                    <p style="font-size:13px;color:#4b5563;margin:0 0 12px;">${row.body}</p>
                    <p style="font-size:10px;color:#9ca3af;margin:0;">${today} · ${row.posted_by_name ?? ''}</p>
                </div>`,

            2: `<div style="background:#ef4444;padding:0.75rem 1.5rem;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#fff;font-size:12px;"></i>
                    <p style="font-size:10px;color:rgba(255,255,255,0.8);text-transform:uppercase;letter-spacing:1px;margin:0;">Urgent Alert</p>
                </div>
                <div style="padding:1.25rem 1.5rem;background:#fff;border-left:4px solid #ef4444;">
                    <h3 style="font-size:14px;font-weight:600;color:#111827;margin:0 0 6px;">${row.title}</h3>
                    <p style="font-size:13px;color:#4b5563;margin:0 0 12px;">${row.body}</p>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#fef2f2;color:#991b1b;font-size:10px;font-weight:600;padding:2px 8px;border-radius:99px;">Urgent</span>
                        <span style="font-size:10px;color:#9ca3af;">${today}</span>
                    </div>
                </div>`,

            3: `<div style="display:grid;grid-template-columns:80px 1fr;">
                    <div style="background:#1e293b;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem 0.75rem;gap:2px;">
                        <span style="font-size:24px;font-weight:700;color:#f97316;line-height:1;">${day}</span>
                        <span style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">${month}</span>
                    </div>
                    <div style="padding:1.25rem 1.5rem;background:#fff;">
                        <p style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin:0;">Event / Holiday</p>
                        <h3 style="font-size:14px;font-weight:600;color:#111827;margin:6px 0 6px;">${row.title}</h3>
                        <p style="font-size:13px;color:#4b5563;margin:0;">${row.body}</p>
                    </div>
                </div>`
        };

        return previews[row.template] ?? '';
    }

    fetchAnnouncements();
    setInterval(fetchAnnouncements, 30000);
</script>