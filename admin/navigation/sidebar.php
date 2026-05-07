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

        <?php $role = $_SESSION['role'] ?? ''; ?>

        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Main</p>

        <!-- For Accounting Department -->
        <?php if ($role === ROLE_ACCOUNTING): ?>
            <?php if ($isHead): ?>
                <a href="<?= BASE_URL ?>/accounting/dashboard"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= isActive('/accounting/dashboard') ?>">
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

    </nav>

    <!-- User Profile -->
    <div class="px-3 py-4 border-t border-gray-100">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-all">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                <i class="fa-sharp fa-solid fa-user-tie text-black text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">
                    <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>
                </p>
                <p class="text-[10px] text-gray-400 truncate">
                    <?= isset($_SESSION['position']) ? htmlspecialchars(ucfirst($_SESSION['position'])) . ' · ' : '' ?>
                    <?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Administrator' ?>
                </p>    
            </div>
            <a href="<?= BASE_URL ?>/logout" class="text-gray-400 hover:text-red-500 transition-colors ml-auto"
                title="Logout">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>

</aside>