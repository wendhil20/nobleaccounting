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
    <main class="ml-56 min-h-screen p-8">

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

                    section.innerHTML = data.map(row => buildAnnHTML(row)).join('');
                })
                .catch(() => {
                    document.getElementById('announcements-section').innerHTML =
                        '<p class="text-sm text-red-400">Failed to load announcements.</p>';
                });
        }

        function buildAnnHTML(row) {
            const rawDate = row.created_at.replace(' ', 'T');
            const dateObj = new Date(rawDate);

            const date = dateObj.toLocaleDateString('en-PH', {
                year: 'numeric', month: 'long', day: 'numeric'
            });
            const day = dateObj.getDate();
            const month = dateObj.toLocaleString('en-PH', { month: 'short' }).toUpperCase();
            const year = dateObj.getFullYear();

            const templates = {
                1: `
                    <div class="overflow-hidden shadow-sm rounded-xl" style="font-family: 'Inter', sans-serif;">
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
                            <div class="flex items-center gap-2 mt-3">
                                <span class="bg-red-100 text-red-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Urgent</span>
                                <span class="text-[10px] text-gray-400">${date}</span>
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
                            </div>
                        </div>
                    </div>`
            };

            return templates[row.template] ?? `
                <div class="bg-white rounded-xl border border-slate-200 px-5 py-4 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-800 mb-1">${row.title}</h3>
                    <p class="text-sm text-gray-600">${row.body}</p>
                    <p class="text-[10px] text-gray-400 mt-2">${date}</p>
                </div>`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            fetchAnnouncements();
            setInterval(fetchAnnouncements, 5000);
        });
    </script>
</body>

</html>