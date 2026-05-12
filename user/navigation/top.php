<!-- top.php -->
<!-- Loading Overlay -->
<div id="nh-loader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/10 backdrop-blur-sm">
    <div class="flex items-center" id="loader-letters"></div>
</div>

<script>
    const words = [
        { letters: 'NOBLE', color: '#f97316', delay: 0 },
        { letters: 'HOME', color: null, delay: 5 },
        { letters: 'DEPOT', color: null, delay: 9 },
    ];

    const wrap = document.getElementById('loader-letters');

    words.forEach(({ letters, color, delay }) => {
        letters.split('').forEach((ch, i) => {
            const span = document.createElement('span');
            span.textContent = ch;
            span.style.cssText = `
    font-size: 2.5rem;
    font-weight: 600;
    letter-spacing: 2px;
    color: ${color ?? '#111'};
    opacity: 1;
    animation: nhblink 2s infinite;
    animation-delay: ${(delay + i) * 0.15}s;
`;
            wrap.appendChild(span);
        });
    });

    // Hide loader when page is ready
    setTimeout(() => {
        const loader = document.getElementById('nh-loader');
        loader.style.transition = 'opacity 0.4s ease';
        loader.style.opacity = '0';
        setTimeout(() => loader.remove(), 400);
    }, 2000);
</script>

<style>
    @keyframes nhblink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.1;
        }
    }
</style>

<nav class="fixed top-0 left-0 right-0 z-50">
    <div class="max-w-8xl mx-auto px-6 py-4 flex items-center justify-between">

        <!-- Logo + Brand -->
        <a href="<?= BASE_URL ?>/" class="flex items-center gap-3">
            <div class="w-9 h-9 shrink-0">
                <img src="<?= BASE_URL ?>/icon/logo.png" alt="Noblehome Logo"
                    class="w-full h-full object-contain bg-white rounded-full p-1">
            </div>
            <div class="w-px h-6 bg-white/40"></div>
            <span class="text-white font-bold text-sm uppercase tracking-widest drop-shadow">NobleHome <span
                    class="text-orange-400">Request</span></span>
        </a>
        <div class="hidden md:flex items-center gap-1">
            <?php
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Kung may subdirectory ang BASE_URL (e.g. /noblehomedepot), i-strip ito
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
            $relativePath = $basePath ? str_replace($basePath, '', $currentPath) : $currentPath;

            $navLinks = [
                '/dashboard' => ['label' => 'Announcement', 'badge' => true], // ← may badge
                '/requesthistory' => ['label' => 'My Requests', 'badge' => false],
                '/myvouchers' => ['label' => 'My Vouchers', 'badge' => false],
            ];

            foreach ($navLinks as $path => $item):
                $isActive = str_starts_with($relativePath, $path);
                $activeClass = $isActive
                    ? 'text-white border-b-2 border-white'
                    : 'text-white/70 hover:text-white hover:bg-white/10';
                ?>
                <a href="<?= BASE_URL . $path ?>"
                    class="<?= $activeClass ?> relative text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-lg transition-all">
                    <?= $item['label'] ?>
                    <?php if ($item['badge']): ?>
                        <span id="ann-nav-badge"
                            class="hidden absolute -top-1 -right-1 w-4 h-4 bg-orange-500 rounded-full text-[9px] text-white font-bold flex items-center justify-center">
                            0
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">

            <!-- Notif Bell -->
            <div class="relative" id="notif-wrapper">
                <button onclick="toggleNotif()"
                    class="relative text-white/70 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10 group">
                    <i class="fa-solid fa-bell text-md"></i>
                    <span id="notif-badge"
                        class="hidden absolute top-1 right-1 w-4 h-4 bg-orange-500 rounded-full text-[9px] text-white font-bold flex items-center justify-center">
                        0
                    </span>

                    <!-- Tooltip -->
                    <!-- Tooltip -->
                    <span class="absolute right-full top-1/2 -translate-y-1/2 ml-2 bg-gray-800 text-white text-[10px] font-medium px-2 py-1 rounded-md
    whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                        Notifications
                    </span>
                </button>

                <!-- Notif Dropdown -->
                <div id="notif-dropdown"
                    class="hidden absolute right-0 mt-1 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-widest">Notifications</span>
                        <button onclick="markAllRead()"
                            class="text-[10px] text-orange-500 hover:text-orange-600 font-semibold transition-colors">
                            Mark all as read
                        </button>
                    </div>

                    <!-- List -->
                    <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                        <p class="text-xs text-gray-400 text-center py-6">Loading...</p>
                    </div>

                </div>
            </div>
            <!-- User Dropdown -->
            <div class="relative" id="user-dropdown-wrapper">

                <button onclick="toggleDropdown()"
                    class="flex items-center gap-2 text-white/80 hover:text-white hover:bg-white/10 px-3 py-2 rounded-lg transition-all">
                    <div
                        class="w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center shrink-0 overflow-hidden">
                        <?php if (!empty($_SESSION['picture'])): ?>
                            <img src="<?= htmlspecialchars($_SESSION['picture']) ?>" alt="avatar"
                                class="w-full h-full object-cover" referrerpolicy="no-referrer">
                        <?php else: ?>
                            <i class="fa-solid fa-user text-white text-[10px]"></i>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-widest hidden sm:inline">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] opacity-60 transition-transform duration-200"
                        id="chevron-icon"></i>
                </button>

                <!-- Dropdown -->
                <div id="user-dropdown" class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden
                   opacity-0 invisible -translate-y-1 transition-all duration-200 z-50">

                    <!-- User Info -->
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <p class="text-xs font-bold text-gray-800 truncate">
                            <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                        </p>
                        <p class="text-[10px] text-gray-400 truncate mt-0.5">
                            <?= htmlspecialchars($_SESSION['email'] ?? '') ?>
                        </p>
                    </div>

                    <a href="<?= BASE_URL ?>/profile"
                        class="flex items-center gap-3 px-4 py-3 text-xs text-gray-700 hover:bg-orange-50 hover:text-orange-600 font-medium transition-colors">
                        <i class="fa-regular fa-circle-user w-4 text-center"></i>
                        Profile
                    </a>

                    <div class="border-t border-gray-100"></div>

                    <a href="<?= BASE_URL ?>/logoutuser"
                        class="flex items-center gap-3 px-4 py-3 text-xs text-red-500 hover:bg-red-50 font-medium transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>

    let notifOpen = false;

    function toggleDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        const chevron = document.getElementById('chevron-icon');
        const isOpen = !dropdown.classList.contains('invisible');

        if (isOpen) {
            dropdown.classList.add('opacity-0', 'invisible', '-translate-y-1');
            dropdown.classList.remove('opacity-100', 'translate-y-0');
            chevron.classList.remove('rotate-180');
        } else {
            dropdown.classList.remove('opacity-0', 'invisible', '-translate-y-1');
            dropdown.classList.add('opacity-100', 'translate-y-0');
            chevron.classList.add('rotate-180');
        }
    }

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('user-dropdown-wrapper');
        if (!wrapper.contains(e.target)) {
            const dropdown = document.getElementById('user-dropdown');
            const chevron = document.getElementById('chevron-icon');
            dropdown.classList.add('opacity-0', 'invisible', '-translate-y-1');
            dropdown.classList.remove('opacity-100', 'translate-y-0');
            chevron.classList.remove('rotate-180');
        }
    });

    function toggleNotif() {
        const dropdown = document.getElementById('notif-dropdown');
        notifOpen = !notifOpen;

        if (notifOpen) {
            dropdown.classList.remove('hidden');
            fetchNotifications(); // ← i-load kapag binuksan
        } else {
            dropdown.classList.add('hidden');
        }
    }

    // Close kapag nag-click sa labas
    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('notif-wrapper');
        if (!wrapper.contains(e.target)) {
            document.getElementById('notif-dropdown').classList.add('hidden');
            notifOpen = false;
        }
    });

    function fetchNotifications() {
        fetch('<?= BASE_URL ?>/fetchnotificationsuser')
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('notif-list');
                const badge = document.getElementById('notif-badge');
                const unread = data.filter(n => !n.is_read).length;

                // Badge
                if (unread > 0) {
                    badge.textContent = unread > 9 ? '9+' : unread;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }

                // List
                if (!data.length) {
                    list.innerHTML = '<p class="text-xs text-gray-400 text-center py-6">No notifications yet.</p>';
                    return;
                }

                list.innerHTML = data.slice(0, 10).map(n => `
                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer ${!n.is_read ? 'bg-orange-50' : ''}"
                         onclick="readNotif(${n.id})">
                        <div class="mt-0.5 w-7 h-7 rounded-full flex items-center justify-center shrink-0
                            ${!n.is_read ? 'bg-orange-100 text-orange-500' : 'bg-gray-100 text-gray-400'}">
                            <i class="fa-solid fa-bell text-[10px]"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 leading-snug ${!n.is_read ? 'font-semibold' : ''}">${n.message}</p>
                            <p class="text-[10px] text-gray-400 mt-1">${n.created_at}</p>
                        </div>
                        ${!n.is_read ? '<span class="w-1.5 h-1.5 bg-orange-400 rounded-full mt-1.5 shrink-0"></span>' : ''}
                    </div>`).join('');
            });
    }

    function readNotif(id) {
        fetch('<?= BASE_URL ?>/readnotificationuser', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(() => fetchNotifications());
    }

    function markAllRead() {
        fetch('<?= BASE_URL ?>/readnotificationuser', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: 'all' })
        }).then(() => fetchNotifications());
    }

    // ← I-add sa dulo ng existing <script> sa top.php
    function updateAnnBadge() {
        fetch('<?= BASE_URL ?>/fetchannouncements')
            .then(res => res.json())
            .then(data => {
                if (!data.length) return;
                const lastSeenId = parseInt(localStorage.getItem('last_seen_ann_id') ?? 0);
                const newCount = data.filter(r => r.id > lastSeenId).length;
                const badge = document.getElementById('ann-nav-badge');
                if (!badge) return;

                if (newCount > 0) {
                    badge.textContent = newCount > 9 ? '9+' : newCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            })
            .catch(() => { }); // ← silent fail
    }

    updateAnnBadge();
    setInterval(updateAnnBadge, 5000);

    // Mark as seen kapag nag-click sa Announcement
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('a[href$="/dashboard"]')?.addEventListener('click', function () {
            fetch('<?= BASE_URL ?>/fetchannouncements')
                .then(res => res.json())
                .then(data => {
                    if (data.length) {
                        localStorage.setItem('last_seen_ann_id', data[0].id);
                        const badge = document.getElementById('ann-nav-badge');
                        if (badge) badge.classList.add('hidden');
                    }
                });
        });
    });

    // Auto-poll every 30 seconds para updated ang badge
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
</script>