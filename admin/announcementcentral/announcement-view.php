<?php
// announcement-view.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-slate-100">
    <main id="main-content" class="md:ml-56 pt-20 md:pt-5 min-h-screen p-4 md:p-8 transition-all duration-300">

        <!-- Page Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Announcements</h1>
                <p class="text-sm text-slate-500 mt-1">All active announcements</p>
            </div>
        </div>

        <!-- Announcements Container -->
        <div id="announcements-section" class="space-y-4">
            <p class="text-sm text-slate-400">Loading announcements...</p>
        </div>

    </main>

    <script>
        // Track all active countdown timers so we can clear them on re-render
        let countdownTimers = [];

        function fetchAnnouncements() {
            fetch('<?= BASE_URL ?>/fetchannouncements')
                .then(res => res.json())
                .then(data => {
                    const section = document.getElementById('announcements-section');

                    if (!data.length) {
                        section.innerHTML = `
                            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                                <p class="text-slate-400 text-sm">No announcements found.</p>
                            </div>`;
                        return;
                    }

                    const newHTML = data.map(row => buildAnnHTML(row)).join('');
                    if (section.innerHTML !== newHTML) {
                        // Clear old countdown timers before re-rendering
                        countdownTimers.forEach(t => clearInterval(t));
                        countdownTimers = [];

                        section.innerHTML = newHTML;

                        // Start countdowns for each announcement
                        data.forEach(row => {
                            if (row.expires_at) {
                                startCountdown(row.id, row.expires_at);
                            }
                        });
                    }
                })
                .catch(() => {
                    document.getElementById('announcements-section').innerHTML =
                        '<p class="text-sm text-red-400">Failed to load announcements.</p>';
                });
        }

        function startCountdown(id, expiresAt) {
            const el = document.getElementById('countdown-' + id);
            if (!el) return;

            const expireDate = new Date(expiresAt.replace(' ', 'T'));

            function tick() {
                const now  = new Date();
                const diff = expireDate - now;

                if (diff <= 0) {
                    el.innerHTML = `<span class="text-red-500 font-semibold">Expired</span>`;
                    return;
                }

                const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                let parts = [];
                if (days > 0)    parts.push(`${days}d`);
                if (hours > 0)   parts.push(`${hours}h`);
                if (minutes > 0) parts.push(`${minutes}m`);
                parts.push(`${seconds}s`);

                // Change color based on urgency
                const colorClass = diff < 3600000
                    ? 'text-red-500'       // less than 1 hour — red
                    : diff < 86400000
                        ? 'text-orange-500'  // less than 1 day — orange
                        : 'text-gray-400';   // more than 1 day — gray

                el.innerHTML = `
                    <i class="fa-solid fa-clock text-[9px] mr-0.5"></i>
                    <span class="${colorClass} font-semibold tabular-nums">
                        Expires in ${parts.join(' ')}
                    </span>`;
            }

            tick(); // run immediately
            const timer = setInterval(tick, 1000);
            countdownTimers.push(timer);
        }

        function buildAnnHTML(row) {
            const rawDate = row.created_at.replace(' ', 'T');
            const dateObj = new Date(rawDate);

            const date  = dateObj.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
            const day   = dateObj.getDate();
            const month = dateObj.toLocaleString('en-PH', { month: 'short' }).toUpperCase();
            const year  = dateObj.getFullYear();

            // Countdown placeholder — filled by startCountdown()
            const countdownEl = row.expires_at
                ? `<span id="countdown-${row.id}" class="flex items-center gap-1 text-[10px] text-gray-400"></span>`
                : '';

            const templates = {
                1: `
                    <div class="overflow-hidden shadow-sm rounded-xl">
                        <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 bg-orange-500 rounded-t-xl">
                            <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-calendar text-orange-400 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-white uppercase tracking-widest">General Announcement</p>
                                <h2 class="text-sm font-bold text-white truncate">${row.title}</h2>
                            </div>
                            <span class="text-[10px] text-white shrink-0">${date}</span>
                        </div>
                        <div class="px-5 py-4 bg-white rounded-b-xl">
                            <p class="text-sm text-gray-700 leading-relaxed">${row.body}</p>
                            <div class="mt-3">${countdownEl}</div>
                        </div>
                    </div>`,

                2: `
                    <div class="rounded-xl overflow-hidden shadow-sm">
                        <div style="background:#ef4444;" class="px-5 py-3 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-white text-xs"></i>
                            <p class="text-[10px] text-white/80 uppercase tracking-widest">Urgent Alert</p>
                        </div>
                        <div class="px-5 py-4 bg-white border-l-4 border-red-500">
                            <h3 class="text-sm font-bold text-gray-800 mb-1">${row.title}</h3>
                            <p class="text-sm text-gray-600">${row.body}</p>
                            <div class="flex items-center gap-3 mt-3 flex-wrap">
                                <span class="bg-red-100 text-red-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Urgent</span>
                                <span class="text-[10px] text-gray-400">${date}</span>
                                ${countdownEl}
                            </div>
                        </div>
                    </div>`,

                3: `
                    <div class="rounded-xl overflow-hidden shadow-sm">
                        <div class="grid" style="grid-template-columns:80px 1fr;">
                            <div style="background:#1e293b;" class="flex flex-col items-center justify-center py-4 gap-0.5">
                                <span class="text-2xl font-bold text-orange-400 leading-none">${day}</span>
                                <span class="text-[10px] text-slate-400 uppercase">${month}</span>
                                <span class="text-[10px] text-slate-500">${year}</span>
                            </div>
                            <div class="px-5 py-4 bg-white">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest">Event / Holiday</p>
                                <h3 class="text-sm font-bold text-gray-800 mt-1 mb-1">${row.title}</h3>
                                <p class="text-sm text-gray-600">${row.body}</p>
                                <div class="mt-2">${countdownEl}</div>
                            </div>
                        </div>
                    </div>`
            };

            return templates[row.template] ?? `
                <div class="bg-white rounded-xl border border-slate-200 px-5 py-4 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-800 mb-1">${row.title}</h3>
                    <p class="text-sm text-gray-600">${row.body}</p>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-[10px] text-gray-400">${date}</p>
                        ${countdownEl}
                    </div>
                </div>`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            fetchAnnouncements();
            setInterval(fetchAnnouncements, 5000);
        });
    </script>
</body>

</html>