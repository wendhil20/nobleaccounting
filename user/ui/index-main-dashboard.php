<?php

include ROOT_PATH . '/network/connect.php';
if (empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vouchers — NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/user/navigation/top.php'; ?>
</head>

<body class="min-h-screen px-4 py-12 relative"
    style="background-image: url('<?= BASE_URL ?>/icon/building2.png'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <div class="max-w-5xl mx-auto relative z-10 pt-16">

        <!-- Announcements Section -->
        <div id="announcements-section" class="mb-6 space-y-3"></div>

        <script>
            function fetchUserAnnouncements() {
                fetch('<?= BASE_URL ?>/fetchannouncements')
                    .then(res => res.json())
                    .then(data => {
                        const section = document.getElementById('announcements-section');
                        if (!data.length) { section.innerHTML = ''; return; }

                        section.innerHTML = data.map(row => buildUserAnnHTML(row)).join('');
                    });
            }

            function buildUserAnnHTML(row) {
                const rawDate = row.created_at.replace(' ', 'T');
                const dateObj = new Date(rawDate);

                const date = dateObj.toLocaleDateString('en-PH', {
                    year: 'numeric', month: 'long', day: 'numeric'
                });
                const day = dateObj.getDate();
                const month = dateObj.toLocaleString('en-PH', { month: 'short' }).toUpperCase();


                const templates = {
                    1: `<div class=" overflow-hidden shadow-sm " style="font-family: 'Inter', sans-serif;">
    <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 bg-orange-500 rounded-full">
        <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-calendar text-orange-400 text-xs"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[10px] text-white uppercase tracking-widest" style="font-family:'Inter',sans-serif;letter-spacing:0.1em;">General Announcement</p>
            <h2 class="text-sm font-bold text-white truncate" style="font-weight:600;">${row.title}</h2>
        </div>
        <span class="text-[10px] text-white shrink-0">${date}</span>
    </div>
    <div class="px-5 py-4">
        <p class="text-sm text-white leading-relaxed" style="font-weight:400;line-height:1.7;">${row.body}</p>
        <p class="text-[10px] text-red-500 mt-3" style="font-weight:500;">Posted by ${row.posted_by_name ?? ''}</p>
    </div>
</div>`,

                    2: `<div class="rounded-xl overflow-hidden shadow-sm ">
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

                    3: `<div class="rounded-xl overflow-hidden shadow-sm">
    <div class="grid" style="grid-template-columns:80px 1fr;">
        <div style="background:#1e293b;" class="flex flex-col items-center justify-center py-4 gap-0.5">
            <span class="text-2xl font-bold text-orange-400 leading-none">${day}</span>
            <span class="text-[10px] text-slate-400 uppercase">${month}</span>
            <span class="text-[10px] text-slate-500">${dateObj.getFullYear()}</span>
        </div>
        <div class="px-5 py-4 bg-white">
            <p class="text-[10px] text-gray-400 uppercase tracking-widest">Event / Holiday</p>
            <h3 class="text-sm font-bold text-gray-800 mt-1 mb-1">${row.title}</h3>
            <p class="text-sm text-gray-600">${row.body}</p>
        </div>
    </div>
</div>`
                };

                return templates[row.template] ?? '';
            }

            document.addEventListener('DOMContentLoaded', function () {
                fetchUserAnnouncements();
                setInterval(fetchUserAnnouncements, 5000);
            });
        </script>
    </div>

</body>

</html>