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
        <!-- Nav Links -->
        <div class="hidden md:flex items-center gap-1">
            <a href="<?= BASE_URL ?>/dashboard"
                class="text-white hover:text-white hover:bg-white/10 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-lg transition-all">
                Dashboard
            </a>
            <a href="<?= BASE_URL ?>/requests"
                class="text-white hover:text-white hover:bg-white/10 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-lg transition-all">
                My Requests
            </a>
            <a href="<?= BASE_URL ?>/history"
                class="text-white hover:text-white hover:bg-white/10 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-lg transition-all">
                History
            </a>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">

            <!-- Notif Bell -->
            <button class="relative text-white/70 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10">
                <i class="fa-regular fa-bell text-sm"></i>
                <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-orange-500 rounded-full"></span>
            </button>

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
</script>