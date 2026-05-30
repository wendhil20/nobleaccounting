<?php
$currentUrl = $_SERVER['REQUEST_URI'];
$role = $_SESSION['role'] ?? '';
$position = $_SESSION['position'] ?? POSITION_STAFF;
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

$user_id = intval($_SESSION['account_id'] ?? 0);
$onlineResult = $conn->query("
    SELECT last_active FROM noblerole WHERE id = $user_id LIMIT 1
");
$onlineRow = $onlineResult->fetch_assoc();
$isOnline = $onlineRow && $onlineRow['last_active'] &&
    (time() - strtotime($onlineRow['last_active'])) < 60;
?>

<style>
    /* Sidebar transition */
    #sidebar {
        transition: width 0.25s ease;
        overflow: hidden;
    }

    #sidebar.collapsed {
        width: 56px !important;
    }

    /* Hide text labels and section headers when collapsed */
    #sidebar.collapsed .sidebar-label,
    #sidebar.collapsed .sidebar-section-label,
    #sidebar.collapsed .sidebar-logo-text,
    #sidebar.collapsed .sidebar-divider,
    #sidebar.collapsed .user-text,
    #sidebar.collapsed #sidebar-notif-badge-wrap {
        display: none !important;
    }

    /* Center icons when collapsed */
    #sidebar.collapsed nav a,
    #sidebar.collapsed nav button {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    #sidebar.collapsed nav a .w-4,
    #sidebar.collapsed nav button .w-4 {
        width: 100%;
        text-align: center;
    }

    /* Logo area collapsed */
    #sidebar.collapsed #sidebar-logo-area {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    #sidebar.collapsed #sidebar-logo-area .sidebar-divider-v {
        display: none;
    }

    /* User profile collapsed */
    #sidebar.collapsed #sidebar-user-block {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    #sidebar.collapsed #sidebar-user-block .logout-btn {
        display: none;
    }

    /* Tooltip for collapsed icons */
    #sidebar.collapsed nav a,
    #sidebar.collapsed nav button {
        position: relative;
    }

    #sidebar.collapsed nav a:hover::after,
    #sidebar.collapsed nav button:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: calc(100% + 8px);
        top: 50%;
        transform: translateY(-50%);
        background: #1f2937;
        color: #fff;
        font-size: 11px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 6px;
        white-space: nowrap;
        pointer-events: none;
        z-index: 999;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
    }

    #sidebar.collapsed nav a:hover::before,
    #sidebar.collapsed nav button:hover::before {
        content: '';
        position: absolute;
        left: calc(100% + 3px);
        top: 50%;
        transform: translateY(-50%);
        border: 5px solid transparent;
        border-right-color: #1f2937;
        pointer-events: none;
        z-index: 999;
    }

    /* Toggle button */
    #sidebar-toggle {
        transition: transform 0.25s ease;
    }

    #sidebar.collapsed #sidebar-toggle {
        transform: rotate(180deg);
    }

    /* Logo area collapsed: center everything */
    #sidebar.collapsed #sidebar-logo-area {
        padding-left: 0;
        padding-right: 0;
        justify-content: center;
    }
</style>

<aside id="sidebar"
    class="fixed top-0 left-0 h-screen w-56 bg-white border-r border-gray-100 flex flex-col z-50 shadow-sm">

    <!-- Logo -->
    <div id="sidebar-logo-area" class="px-4 py-4 border-b border-gray-100 flex items-center gap-2">

        <!-- Left: logo icon + toggle stacked -->
        <div class="flex flex-col items-center gap-1 flex-shrink-0">
            <img src="<?= BASE_URL ?>/icon/logo.png" alt="NobleHome Logo" class="h-8 w-auto object-contain">

            <!-- Toggle Button sits below the logo icon -->
            <button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar"
                class="w-5 h-5 bg-gray-100 border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-200 hover:text-gray-700 transition-all">
                <i class="fa-solid fa-chevron-left text-[8px]"></i>
            </button>
        </div>

        <!-- Right: brand text (hidden when collapsed) -->
        <div class="sidebar-divider-v w-px h-10 bg-gray-300 sidebar-logo-text"></div>
        <span
            class="sidebar-logo-text text-base font-bold text-gray-800 tracking-tight whitespace-nowrap leading-tight">
            Noble<span class="text-amber-500">Home</span><br>
            <span class="text-gray-400 font-normal text-xs">Department</span>
        </span>
    </div>

    <!-- Nav Links -->
    <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto overflow-x-hidden">

        <p class="sidebar-section-label text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">
            Main</p>

        <?php $role = $_SESSION['role'] ?? ''; ?>

        <?php if ($role === ROLE_ACCOUNTING): ?>
            <a href="<?= BASE_URL ?>/generalannouncement" data-tooltip="General Announce"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/generalannouncement') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">General Announce</span>
            </a>
        <?php endif; ?>

        <?php if ($role === ROLE_ACCOUNTING): ?>
            <?php if ($isHead): ?>
                <a href="<?= BASE_URL ?>/accountinggraph" data-tooltip="Dashboard"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountinggraph') ?>">
                    <i class="fa-solid fa-chart-line w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Dashboard</span>
                </a>

                <a href="<?= BASE_URL ?>/accounting" data-tooltip="Requests List"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accounting') ?>">
                    <i class="fa-solid fa-list-check w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Requests List</span>
                </a>

                <a href="<?= BASE_URL ?>/accountinggeneralsheet" data-tooltip="General Sheet"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountinggeneralsheet') ?>">
                    <i class="fa-solid fa-clipboard-list w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">General Sheet</span>
                </a>

                <a href="<?= BASE_URL ?>/accountingpettycash" data-tooltip="Petty Cash"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingpettycash') ?>">
                    <i class="fa-solid fa-coins w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Petty Cash</span>
                </a>
            <?php endif; ?>

            <?php if ($isStaff): ?>
                <a href="<?= BASE_URL ?>/accountingstaffdashboard" data-tooltip="Dashboard Records"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingstaffdashboard') ?>">
                    <i class="fa-solid fa-chart-line w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Dashboard Records</span>
                </a>

                <a href="<?= BASE_URL ?>/accountingstaff" data-tooltip="Acknowledge Request"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingstaff') ?>">
                    <i class="fa-solid fa-list w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Acknowledge Request</span>
                </a>
            <?php endif; ?>

            <?php if ($isCustodian): ?>
                <a href="<?= BASE_URL ?>/accountingcustodiandashboard" data-tooltip="Dashboard Records"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodiandashboard') ?>">
                    <i class="fa-solid fa-chart-line w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Dashboard Records</span>
                </a>

                <a href="<?= BASE_URL ?>/accountingcustodian" data-tooltip="Cash Voucher Request"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodian') ?>">
                    <i class="fa-solid fa-list w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Cash Voucher Request</span>
                </a>

                <a href="<?= BASE_URL ?>/accountinggeneralsheet" data-tooltip="General Sheet"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountinggeneralsheet') ?>">
                    <i class="fa-solid fa-clipboard-list w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">General Sheet</span>
                </a>

                <a href="<?= BASE_URL ?>/accountingcustodianpettycash" data-tooltip="Petty Cash"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodianpettycash') ?>">
                    <i class="fa-solid fa-coins w-4 text-center text-sm flex-shrink-0"></i>
                    <span class="sidebar-label">Petty Cash</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (in_array($role, [ROLE_HR])): ?>
            <a href="<?= BASE_URL ?>/humanresource" data-tooltip="Dashboard"
                class="flex items-center gap-3 px-3 py-2 rounded-lg font-semibold text-sm transition-all <?= isActive('/humanresource') ?>">
                <i class="fa-solid fa-chart-line w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Dashboard</span>
            </a>

            <a href="<?= BASE_URL ?>/humanresourcerequest" data-tooltip="Request"
                class="flex items-center gap-3 px-3 py-2 rounded-lg font-semibold text-sm transition-all <?= isActive('/humanresourcerequest') ?>">
                <i class="fa-solid fa-address-book w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Request</span>
            </a>
        <?php endif; ?>

        <?php if (in_array($role, [ROLE_IT])): ?>
            <a href="<?= BASE_URL ?>/it" data-tooltip="Dashboard"
                class="flex items-center gap-3 px-3 py-2 rounded-lg font-semibold text-sm group transition-all <?= isActive('/informationtech') ?>">
                <i class="fa-sharp fa-solid fa-chart-bar w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Dashboard</span>
            </a>
        <?php endif; ?>

        <div class="sidebar-section-label pt-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Manage</p>
        </div>

        <?php if ($isStaff): ?>
            <a href="<?= BASE_URL ?>/accountingstaffannouncement" data-tooltip="Insert Announcement"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingstaffannouncement') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Insert-Announcement</span>
            </a>
        <?php endif; ?>

        <?php if ($isCustooAssistant): ?>
            <a href="<?= BASE_URL ?>/accountingcustodianassistant" data-tooltip="Insert Announcement"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingcustodianassistant') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Insert-Announcement</span>
            </a>
        <?php endif; ?>

        <?php if ($isCustodian): ?>
            <a href="<?= BASE_URL ?>/announcementcustodian" data-tooltip="Insert Announcement"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/announcementcustodian') ?>">
                <i class="fa-solid fa-bullhorn w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Insert-Announcement</span>
            </a>

            <a href="<?= BASE_URL ?>/projectmonitor" data-tooltip="Project Monitor"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/projectmonitor') ?>">
                <i class="fa-solid fa-file-circle-check w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Project Monitor</span>
            </a>
        <?php endif; ?>

        <?php if ($role === ROLE_ACCOUNTING && in_array($position, [POSITION_HEAD])): ?>
            <a href="<?= BASE_URL ?>/announcementdashboard" data-tooltip="Announce List"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/announcementdashboard') ?>">
                <i class="fa-solid fa-sign-hanging w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Announce List</span>
            </a>

            <a href="<?= BASE_URL ?>/accountingmonitoring" data-tooltip="Monitoring Project"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingmonitoring') ?>">
                <i class="fa-solid fa-file-circle-check w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Monitoring Project</span>
            </a>

            <a href="<?= BASE_URL ?>/accountingtracking" data-tooltip="Accounting Tracking"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingtracking') ?>">
                <i class="fa-solid fa-timeline w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Tracking Req & Vouch</span>
            </a>
        <?php endif; ?>

        <?php if ($role === ROLE_ACCOUNTING && in_array($position, [POSITION_HEAD, POSITION_CUSTODIAN, POSITION_CUSTOASSISTANT])): ?>
            <a href="<?= BASE_URL ?>/cashvoucherdashboard" data-tooltip="Approval Cash Voucher"
                class="flex items-center gap-3 px-2 py-2 rounded-lg text-sm transition-all <?= isActive('/cashvoucherdashboard') ?>">
                <i class="fa-solid fa-ticket-simple w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Approval Cash Voucher</span>
            </a>
        <?php endif; ?>

        <?php if (in_array($role, [ROLE_HR])): ?>
            <a href="<?= BASE_URL ?>/superad" data-tooltip="Manage Accounts"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 hover:text-gray-800 font-medium text-sm group transition-all <?= isActive('/superad') ?>">
                <i class="fa-solid fa-user-plus w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Manage Accounts</span>
            </a>
        <?php endif; ?>

        <?php if (in_array($role, [ROLE_IT])): ?>
            <a href="#" data-tooltip="Maintenance"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 font-medium text-sm group transition-all">
                <i class="fa-solid fa-wrench w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Maintenance</span>
            </a>
        <?php endif; ?>

        <div class="sidebar-section-label pt-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Notifications</p>
        </div>

        <button onclick="toggleSidebarNotif()" data-tooltip="Notifications"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 hover:text-gray-800 text-gray-500 font-medium text-sm transition-all">
            <div class="relative w-4 flex items-center justify-center flex-shrink-0">
                <i class="fa-regular fa-bell text-sm"></i>
                <span id="sidebar-notif-badge"
                    class="hidden absolute -top-1.5 -right-1.5 min-w-[14px] h-3.5 bg-red-500 rounded-full text-[8px] font-bold text-white flex items-center justify-center px-0.5">
                </span>
            </div>
            <span id="sidebar-notif-badge-wrap" class="sidebar-label flex-1 text-left flex items-center gap-2">
                Notifications
            </span>
            <i class="fa-solid fa-chevron-right text-[9px] opacity-40 transition-transform duration-200 sidebar-label"
                id="sidebar-notif-chevron"></i>
        </button>

        <div class="sidebar-section-label pt-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Settings</p>
        </div>

        <?php if ($role === ROLE_ACCOUNTING && in_array($position, [POSITION_HEAD, POSITION_CUSTODIAN, POSITION_CUSTOASSISTANT, POSITION_STAFF])): ?>
            <a href="<?= BASE_URL ?>/accountingsignatured" data-tooltip="Signatured Request"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accountingsignatured') ?>">
                <i class="fa-solid fa-signature w-4 text-center text-sm flex-shrink-0"></i>
                <span class="sidebar-label">Signatured Request</span>
            </a>
        <?php endif; ?>

    </nav>

    <!-- User Profile -->
    <div class="px-3 py-4 border-t border-gray-100">
        <div id="sidebar-user-block"
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
            <div class="user-text flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">
                    <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>
                </p>
                <p class="text-[10px] text-gray-400 truncate">
                    <?= isset($_SESSION['position']) ? htmlspecialchars(ucfirst($_SESSION['position'])) . '  ' : '' ?>
                </p>
            </div>

            <!-- Logout -->
            <a href="<?= BASE_URL ?>/logout"
                class="logout-btn flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors" title="Logout">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Overlay -->
    <div id="notif-overlay" onclick="toggleSidebarNotif()" class="hidden fixed inset-0 z-40 bg-black/20"></div>

    <!-- Slide-out Notification Panel -->
    <div id="notif-panel" class="fixed top-0 z-50 h-screen w-72 bg-white border-r border-gray-100 shadow-xl flex flex-col
           transition-all duration-300 ease-in-out"
        style="right: -288px; left: auto; border-r: none; border-left: 1px solid #f3f4f6;">

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

        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 bg-gray-50">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Recent</span>
            <button onclick="sidebarMarkAllRead()"
                class="text-[10px] text-orange-500 hover:text-orange-600 font-semibold transition-colors">
                Mark all read
            </button>
        </div>

        <div id="sidebar-notif-list" class="overflow-y-auto divide-y divide-gray-100"
            style="max-height: calc(100vh - 120px);">
            <div class="px-4 py-8 text-center text-gray-400 text-[11px]">
                <i class="fa-regular fa-bell mb-2 block text-2xl"></i>
                No notifications
            </div>
        </div>
    </div>
    <script>
        var CURRENT_USER_ID = <?= intval($_SESSION['account_id'] ?? 0) ?>;
    </script>
    <!-- Global Toast Notifications -->
    <script src="<?= BASE_URL ?>/js/global-notif.js"></script>

    <script>

        const BASE_URL = '<?= BASE_URL ?>';
        // ── Sidebar Collapse ──────────────────────────────────────────
        const SIDEBAR_KEY = 'sidebar_collapsed';
        let sidebarCollapsed = localStorage.getItem(SIDEBAR_KEY) === 'true';

        function applySidebarState() {
            const sidebar = document.getElementById('sidebar');
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
            // Push main content if it exists
            const main = document.getElementById('main-content') || document.querySelector('main') || document.querySelector('.main-content');
            if (main) {
                main.style.marginLeft = sidebarCollapsed ? '56px' : '224px';
                main.style.transition = 'margin-left 0.25s ease';
            }
        }

        function toggleSidebar() {
            sidebarCollapsed = !sidebarCollapsed;
            localStorage.setItem(SIDEBAR_KEY, sidebarCollapsed);
            applySidebarState();
        }

        // Apply on load
        applySidebarState();

        // ── Heartbeat ─────────────────────────────────────────────────
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

        // ── Notifications ─────────────────────────────────────────────
        function toggleSidebarNotif() {
            notifPanelOpen = !notifPanelOpen;
            const panel = document.getElementById('notif-panel');
            const overlay = document.getElementById('notif-overlay');
            const chevron = document.getElementById('sidebar-notif-chevron');

            if (notifPanelOpen) {
                panel.style.left = '0';
                overlay.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-90');
            } else {
                panel.style.left = '-288px';
                overlay.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-90');
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
                        let message = n.message;
                        if (n.control_no) {
                            message = message.replace(
                                n.control_no,
                                `<span style="background:#fff3e0; color:#c2410c; font-weight:600; padding:1px 5px; border-radius:4px; font-size:10px;">${n.control_no}</span>`
                            );
                        }
                        const requestDate = n.date_requested ?? '';
                        return `
<div onclick="sidebarClickNotif(${n.id}, '${n.link ?? ''}', ${n.request_id ?? 0}, '${requestDate}')"
     class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors hover:bg-gray-50 ${n.is_read == 0
                                ? (isPing ? 'bg-red-50 border-l-[3px] border-red-400' : 'bg-orange-50 border-l-[3px] border-orange-400')
                                : 'border-l-[3px] border-transparent'}">
    <div class="w-8 h-8 rounded-full ${n.is_read == 0 ? (isPing ? 'bg-red-100' : 'bg-orange-100') : 'bg-gray-100'} flex items-center justify-center shrink-0 mt-0.5">
        <i class="fa-solid ${isPing ? 'fa-bell' : 'fa-file-invoice'} ${n.is_read == 0 ? (isPing ? 'text-red-500' : 'text-orange-500') : 'text-gray-400'} text-xs"></i>
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