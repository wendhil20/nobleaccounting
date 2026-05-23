<?php
$currentUrl = $_SERVER['REQUEST_URI'];
$role = $_SESSION['role'] ?? '';
$position = $_SESSION['position'] ?? POSITION_STAFF; // default = staff (safer)
$isHead = $position === POSITION_HEAD;
$isStaff = $position === POSITION_STAFF;
$isCustodian = $position === POSITION_CUSTODIAN;
$isCustooAssistant = $position === POSITION_CUSTOASSISTANT;

function isActive(string $path): string
{
    global $currentUrl;
    $currentPath = rtrim(parse_url($currentUrl, PHP_URL_PATH), '/');
    $path = rtrim($path, '/');
    return $currentPath === $path || str_ends_with($currentPath, $path)
        ? 'bg-gray-300 text-black font-semibold'
        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800 font-small';
}

// Sa sidebar — check kung online
$user_id = intval($_SESSION['account_id'] ?? 0);
$onlineResult = $conn->query("
    SELECT last_active FROM noblerole WHERE id = $user_id LIMIT 1
");
$onlineRow = $onlineResult->fetch_assoc();
$isOnline = $onlineRow && $onlineRow['last_active'] &&
    (time() - strtotime($onlineRow['last_active'])) < 60;
?>

<aside id="sidebar"
    class="fixed top-0 left-0 h-screen w-56 bg-white border-r border-gray-100 flex flex-col z-50 shadow-sm transition-all duration-300">

    <!-- Logo -->
    <div class="px-5 py-5 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <img src="<?= BASE_URL ?>/icon/logo.png" alt="NobleHome Logo" class="h-8 w-auto object-contain">
            <div class="w-px h-12 bg-gray-400"></div>
            <span class="text-base font-bold text-gray-800 tracking-tight">Noble<span class="text-amber-500">Home</span>
                <span class="text-gray-400 font-normal text-sm">Department</span></span>
        </div>
    </div>

    <!-- Nav Links -->
    <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto">

        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Main</p>

        <?php $role = $_SESSION['role'] ?? ''; ?>

        <?php if ($role === ROLE_ACCOUNTING): ?>
            <a href="<?= BASE_URL ?>/generalannouncement"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/generalannouncement') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm"></i>
                <span>General Announce</span>
            </a>
        <?php endif; ?>

        <!-- For Accounting Department -->
        <?php if ($role === ROLE_ACCOUNTING): ?>
            <?php if ($isHead): ?>
                <a href="<?= BASE_URL ?>/accountinggraph"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountinggraph') ?>">
                    <i class="fa-solid fa-chart-line w-4 text-center text-sm"></i>
                    <span>Dashboard</span>
                </a>

                <a href="<?= BASE_URL ?>/accounting"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accounting') ?>">
                    <i class="fa-solid fa-list-check w-4 text-center text-sm"></i>
                    <span>Requests List</span>
                </a>
            <?php endif; ?>

            <!-- For Staff -->

            <?php if ($isStaff): ?>
                <a href="<?= BASE_URL ?>/accountingstaffdashboard"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingstaffdashboard') ?>">
                    <i class="fa-solid fa-chart-line w-4 text-center text-sm"></i>
                    <span>Dashboard Records</span>
                </a>

                <a href="<?= BASE_URL ?>/accountingstaff"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingstaff') ?>">
                    <i class="fa-solid fa-list w-4 text-center text-sm"></i>
                    <span>Acknowledge Request</span>
                </a>
            <?php endif; ?>


            <?php if ($isCustodian): ?>
                <a href="<?= BASE_URL ?>/accountingcustodiandashboard"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodiandashboard') ?>">
                    <i class="fa-solid fa-chart-line w-4 text-center text-sm"></i>
                    <span>Dashboard Records</span>
                </a>

                <a href="<?= BASE_URL ?>/accountingcustodian"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodian') ?>">
                    <i class="fa-solid fa-list w-4 text-center text-sm"></i>
                    <span>Cash Voucher Request</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- End For Accounting Department -->

        <!-- For HR Department -->
        <?php if (in_array($role, [ROLE_HR])): ?>
            <a href="<?= BASE_URL ?>/humanresource"
                class="flex items-center gap-3 px-3 py-2 rounded-lg font-semibold text-sm transition-all <?= isActive('/humanresource') ?>">
                <i class="fa-solid fa-chart-line w-4 text-center text-sm"></i>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <?php if (in_array($role, [ROLE_HR])): ?>
            <a href="<?= BASE_URL ?>/humanresourcerequest"
                class="flex items-center gap-3 px-3 py-2 rounded-lg font-semibold text-sm  transition-all <?= isActive('/humanresourcerequest') ?>">
                <i class="fa-solid fa-address-book w-4 text-center text-sm"></i>
                <span>Request</span>
            </a>
        <?php endif; ?>
        <!-- End For HR Department -->

        <!-- For IT Department -->
        <?php if (in_array($role, [ROLE_IT])): ?>
            <a href="<?= BASE_URL ?>/it"
                class="flex items-center gap-3 px-3 py-2 rounded-lg font-semibold text-sm group transition-all <?= isActive('/informationtech') ?>">
                <i class="fa-sharp fa-solid fa-chart-bar w-4 text-center text-sm"></i>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>
        <!-- End For IT Department -->

        <div class="pt-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Manage</p>
        </div>

        <?php if ($isStaff): ?>
            <a href="<?= BASE_URL ?>/accountingstaffannouncement"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingstaffannouncement') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm"></i>
                <span>Insert-Announcement</span>
            </a>
        <?php endif; ?>

        <?php if ($isCustooAssistant): ?>
            <a href="<?= BASE_URL ?>/accountingcustodianassistant"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodianassistant') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm"></i>
                <span>Insert-Announcement</span>
            </a>
        <?php endif; ?>

        <?php if ($isCustodian): ?>
            <a href="<?= BASE_URL ?>/announcementcustodian"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/announcementcustodian') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm"></i>
                <span>Insert-Announcement </span>
            </a>

            <a href="<?= BASE_URL ?>/projectmonitor"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/projectmonitor') ?>">
                <i class="fa-solid fa-file-circle-check w-4 text-center text-sm"></i>
                <span>Project Monitor</span>
            </a>
        <?php endif; ?>

       <?php if ($role === ROLE_ACCOUNTING && in_array($position, [POSITION_HEAD])): ?>
            <a href="<?= BASE_URL ?>/announcementdashboard"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/announcementdashboard') ?>">
                <i class="fa-solid fa-sign-hanging  w-4 text-center text-sm"></i>
                <span>Announce List</span>
            </a>

            <a href="<?= BASE_URL ?>/accountingmonitoring"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingmonitoring') ?>">
                <i class="fa-solid fa-file-circle-check w-4 text-center text-sm"></i>
                <span>Monitoring Project</span>
            </a>
        <?php endif; ?>

        <?php if ($role === ROLE_ACCOUNTING && in_array($position, [POSITION_HEAD, POSITION_CUSTODIAN, POSITION_CUSTOASSISTANT])): ?>
            <a href="<?= BASE_URL ?>/cashvoucherdashboard"
                class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-all <?= isActive('/cashvoucherdashboard') ?>">
                <i class="fa-solid fa-ticket-simple w-4 text-center text-sm"></i>
                <span>Approval Cash Voucher</span>
            </a>
        <?php endif; ?>

        <!-- For Hr Department -->
        <?php if (in_array($role, [ROLE_HR])): ?>
            <a href="<?= BASE_URL ?>/superad"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 hover:text-gray-800 font-medium text-sm group transition-all <?= isActive('/superad') ?>">
                <i class="fa-solid fa-user-plus w-4 text-center text-sm"></i>
                <span>Manage Accounts</span>
            </a>
        <?php endif; ?>
        <!-- End For HR Department -->


        <?php if (in_array($role, [ROLE_IT])): ?>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 font-medium text-sm group transition-all">
                <i class="fa-solid fa-wrench w-4 text-center text-sm"></i>
                <span>Maintenance</span>
            </a>
        <?php endif; ?>

        <div class="pt-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Notifications</p>
        </div>

        <!-- For Notification -->

        <button onclick="toggleSidebarNotif()"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 hover:text-gray-800 text-gray-500 font-medium text-sm transition-all">
            <div class="relative w-4 flex items-center justify-center">
                <i class="fa-regular fa-bell text-sm"></i>
                <span id="sidebar-notif-badge"
                    class="hidden absolute -top-1.5 -right-1.5 min-w-[14px] h-3.5 bg-red-500 rounded-full text-[8px] font-bold text-white flex items-center justify-center px-0.5">
                </span>
            </div>
            <span class="flex-1 text-left">Notifications</span>
            <i class="fa-solid fa-chevron-right text-[9px] opacity-40 transition-transform duration-200"
                id="sidebar-notif-chevron"></i>
        </button>

        <!-- End For Notification -->

    </nav>

    <!-- User Profile -->
    <div class="px-3 py-4 border-t border-gray-100">
        <div
            class="relative group flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-all">

            <!-- Hover Tooltip -->
            <div class="absolute bottom-full left-2 mb-2 hidden group-hover:block z-50">
                <div class="bg-gray-900 text-white rounded-lg px-3 py-2 shadow-lg min-w-max">
                    <div class="flex items-center gap-2 mb-1">
                        <span id="tooltip-dot"
                            class="w-2 h-2 rounded-full flex-shrink-0 <?= $isOnline ? 'bg-green-400' : 'bg-gray-400' ?>"></span>
                        <span id="tooltip-status"
                            class="text-xs font-semibold <?= $isOnline ? 'text-green-400' : 'text-gray-400' ?>">
                            <?= $isOnline ? 'Online' : 'Away' ?>
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-white">
                        <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>
                    </p>
                    <p class="text-[10px] text-gray-400 mt-0.5">
                        <?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Administrator' ?>
                    </p>
                    <!-- Tooltip arrow -->
                    <div class="absolute -bottom-1 left-4 w-2 h-2 bg-gray-900 rotate-45"></div>
                </div>
            </div>

            <!-- Avatar + Online Dot -->
            <div class="relative flex-shrink-0">
                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                    <i class="fa-sharp fa-solid fa-user-tie text-black text-xs"></i>
                </div>
                <span id="presence-dot"
                    class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 <?= $isOnline ? 'bg-green-400' : 'bg-gray-300' ?> border-2 border-white rounded-full"></span>
            </div>

            <!-- Text block -->
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">
                    <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>
                </p>
                <p class="text-[10px] text-gray-400 truncate">
                    <?= isset($_SESSION['position']) ? htmlspecialchars(ucfirst($_SESSION['position'])) . '  ' : '' ?>
                </p>
            </div>

            <!-- Logout -->
            <a href="<?= BASE_URL ?>/logout" class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors"
                title="Logout">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>

        </div>
    </div>

    <!-- Overlay -->
    <div id="notif-overlay" onclick="toggleSidebarNotif()" class="hidden fixed inset-0 z-40 bg-black/20"></div>

    <!-- Slide-out Panel -->
    <div id="notif-panel" class="fixed top-0 z-50 h-screen w-72 bg-white border-r border-gray-100 shadow-xl flex flex-col
           transition-all duration-300 ease-in-out"
        style="right: -288px; left: auto; border-r: none; border-left: 1px solid #f3f4f6;">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-bell text-gray-600 text-sm"></i>
                <span class="text-sm font-bold text-gray-800">Notifications</span>
                <span id="notif-panel-badge"
                    class="hidden bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                </span>
            </div>
            <button onclick="toggleSidebarNotif()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Mark all read -->
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 bg-gray-50">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Recent</span>
            <button onclick="sidebarMarkAllRead()"
                class="text-[10px] text-orange-500 hover:text-orange-600 font-semibold transition-colors">
                Mark all read
            </button>
        </div>

        <!-- List -->
        <div id="sidebar-notif-list" class="overflow-y-auto divide-y divide-gray-100"
            style="max-height: calc(100vh - 120px);">
            <div class="px-4 py-8 text-center text-gray-400 text-[11px]">
                <i class="fa-regular fa-bell mb-2 block text-2xl"></i>
                No notifications
            </div>
        </div>
    </div>

    <script>
        let notifPanelOpen = false;
        let sidebarLastUnread = -1;

        function sendHeartbeat() {
            fetch('<?= BASE_URL ?>/heartbeat', { method: 'POST' })
                .then(() => setDot(true))
                .catch(() => setDot(false));
        }
        function setDot(online) {
            const dot = document.getElementById('presence-dot');
            const tooltipDot = document.getElementById('tooltip-dot');
            const tooltipText = document.getElementById('tooltip-status');
            if (!dot) return;

            dot.className = `absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 ${online ? 'bg-green-400' : 'bg-gray-300'} border-2 border-white rounded-full`;

            if (tooltipDot) tooltipDot.className = `w-2 h-2 rounded-full flex-shrink-0 ${online ? 'bg-green-400' : 'bg-gray-400'}`;
            if (tooltipText) tooltipText.className = `text-xs font-semibold ${online ? 'text-green-400' : 'text-gray-400'}`;
            if (tooltipText) tooltipText.textContent = online ? 'Online' : 'Away';
        }
        sendHeartbeat();
        setInterval(sendHeartbeat, 10_000);


        function toggleSidebarNotif() {
            notifPanelOpen = !notifPanelOpen;
            const panel = document.getElementById('notif-panel');
            const overlay = document.getElementById('notif-overlay');
            const chevron = document.getElementById('sidebar-notif-chevron');

            if (notifPanelOpen) {
                panel.style.left = '0';
                overlay.classList.remove('hidden');
                chevron.classList.add('rotate-90');
            } else {
                panel.style.left = '-288px';
                overlay.classList.add('hidden');
                chevron.classList.remove('rotate-90');
                sidebarMarkAllRead();
            }
        }

        function sidebarFetchNotifications() {
            fetch('<?= BASE_URL ?>/fetchnotifications')
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('sidebar-notif-badge');
                    const panelBadge = document.getElementById('notif-panel-badge');
                    const list = document.getElementById('sidebar-notif-list');
                    const unread = data.filter(n => n.is_read == 0);

                    // Badges
                    if (unread.length > 0) {
                        const count = unread.length > 9 ? '9+' : unread.length;
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                        panelBadge.textContent = count;
                        panelBadge.classList.remove('hidden');

                        if (sidebarLastUnread !== -1 && unread.length > sidebarLastUnread) {
                            sidebarPlaySound();
                        }
                    } else {
                        badge.classList.add('hidden');
                        panelBadge.classList.add('hidden');
                    }
                    sidebarLastUnread = unread.length;

                    // List
                    if (!data.length) {
                        list.innerHTML = `
                    <div class="px-4 py-8 text-center text-black text-[11px]">
                        <i class="fa-regular fa-bell mb-2 block text-2xl"></i>
                        No notifications
                    </div>`;
                        return;
                    }

                    list.innerHTML = data.map(n => {
                        const isPing = n.message.includes('');

                        // I-highlight ang control_no sa loob ng message
                        let message = n.message;
                        if (n.control_no) {
                            message = message.replace(
                                n.control_no,
                                `<span style="background:#fff3e0; color:#c2410c; font-weight:600; padding:1px 5px; border-radius:4px; font-size:10px;">${n.control_no}</span>`
                            );
                        }

                        // ↓ UPDATED: dalawang bagong param — request_id at date_requested
                        const requestDate = n.date_requested ?? '';

                        return `
<div onclick="sidebarClickNotif(${n.id}, '${n.link ?? ''}', ${n.request_id ?? 0}, '${requestDate}')"
     class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors hover:bg-gray-50 ${n.is_read == 0
                                ? (isPing ? 'bg-red-50 border-l-[3px] border-red-400' : 'bg-orange-50 border-l-[3px] border-orange-400')
                                : 'border-l-[3px] border-transparent'}">
                                <div class="w-8 h-8 rounded-full ${n.is_read == 0
                                ? (isPing ? 'bg-red-100' : 'bg-orange-100')
                                : 'bg-gray-100'} flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid ${isPing ? 'fa-bell' : 'fa-file-invoice'} ${n.is_read == 0
                                ? (isPing ? 'text-red-500' : 'text-orange-500')
                                : 'text-gray-400'} text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold ${n.is_read == 0 ? 'text-gray-900' : 'text-gray-700'}">${n.control_no ?? ''}</p>
                                    ${n.sender_name ? `<p class="text-[10px] ${isPing ? 'text-red-400' : 'text-orange-400'} font-medium mt-0.5">${n.sender_name}</p>` : ''}
                                    <p class="text-[11px] ${n.is_read == 0 ? 'text-gray-600' : 'text-gray-400'} leading-snug mt-0.5">${message}</p>
                                    <p class="text-[10px] text-gray-400 mt-1">${sidebarTimeAgo(n.created_at)}</p>
                                </div>
                                ${n.is_read == 0 ? `<div class="w-2 h-2 ${isPing ? 'bg-red-500' : 'bg-orange-500'} rounded-full mt-1.5 shrink-0"></div>` : ''}
                            </div>`;
                    }).join('');
                })
                .catch(err => console.error('Notif error:', err));
        }

        // ↓ UPDATED: tumatanggap na ng requestDate param, idinagdag sa URL bilang &date=
        function sidebarClickNotif(id, link, requestId, requestDate) {
            fetch('<?= BASE_URL ?>/marknotificationsread', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            }).then(() => {
                if (link) {
                    let url = '<?= BASE_URL ?>' + link + '?highlight=' + requestId;
                    if (requestDate) url += '&date=' + requestDate;
                    window.location.href = url;
                } else {
                    sidebarFetchNotifications();
                }
            });
        }

        function sidebarMarkAllRead() {
            fetch('<?= BASE_URL ?>/marknotificationsread', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: 0 })
            }).then(() => sidebarFetchNotifications());
        }

        function sidebarTimeAgo(dateStr) {
            const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return Math.floor(diff / 86400) + 'd ago';
        }

        function sidebarPlaySound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) { }
        }

        sidebarFetchNotifications();
        setInterval(sidebarFetchNotifications, 5000);
    </script>
</aside>