<?php
// index-accounting-graph.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_ACCOUNTING];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

$currentYear  = (int)date('Y');
$currentMonth = (int)date('n');

$selectedYear  = isset($_GET['year'])  ? (int)$_GET['year']  : $currentYear;
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : 0;

$yearsResult = $conn->query("SELECT DISTINCT YEAR(date_requested) AS yr FROM noblebudgetrequest ORDER BY yr DESC");
$availableYears = [];
while ($row = $yearsResult->fetch_assoc()) {
    $availableYears[] = (int)$row['yr'];
}
if (empty($availableYears)) $availableYears[] = $currentYear;

$monthNames = [
    1=>'January',2=>'February',3=>'March',4=>'April',
    5=>'May',6=>'June',7=>'July',8=>'August',
    9=>'September',10=>'October',11=>'November',12=>'December'
];
$monthShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

if ($selectedMonth > 0) {
    $sql = "SELECT requestor_name, DAY(date_requested) AS day, COUNT(*) AS request_count
            FROM noblebudgetrequest
            WHERE YEAR(date_requested) = ? AND MONTH(date_requested) = ?
            GROUP BY requestor_name, DAY(date_requested)
            ORDER BY requestor_name, day";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $selectedYear, $selectedMonth);
} else {
    $sql = "SELECT requestor_name, MONTH(date_requested) AS month, COUNT(*) AS request_count
            FROM noblebudgetrequest
            WHERE YEAR(date_requested) = ?
            GROUP BY requestor_name, MONTH(date_requested)
            ORDER BY requestor_name, month";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $selectedYear);
}

$stmt->execute();
$result = $stmt->get_result();
$chartData = [];

if ($selectedMonth > 0) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
    $labels = array_map(fn($d) => (string)$d, range(1, $daysInMonth));
    while ($row = $result->fetch_assoc()) {
        $name = $row['requestor_name'];
        $dayIndex = (int)$row['day'] - 1;
        if (!isset($chartData[$name])) $chartData[$name] = array_fill(0, $daysInMonth, 0);
        $chartData[$name][$dayIndex] = (int)$row['request_count'];
    }
} else {
    $labels = $monthShort;
    while ($row = $result->fetch_assoc()) {
        $name = $row['requestor_name'];
        $monthIndex = (int)$row['month'] - 1;
        if (!isset($chartData[$name])) $chartData[$name] = array_fill(0, 12, 0);
        $chartData[$name][$monthIndex] = (int)$row['request_count'];
    }
}
$stmt->close();

$totalRequests = 0; $topUser = ''; $topUserCount = 0;
foreach ($chartData as $user => $counts) {
    $userTotal = array_sum($counts);
    $totalRequests += $userTotal;
    if ($userTotal > $topUserCount) { $topUserCount = $userTotal; $topUser = $user; }
}
$totalUsers = count($chartData);

$colors = [
    ['bg'=>'rgba(37,99,235,0.85)',  'border'=>'#2563eb'],
    ['bg'=>'rgba(16,185,129,0.85)','border'=>'#10b981'],
    ['bg'=>'rgba(245,158,11,0.85)','border'=>'#f59e0b'],
    ['bg'=>'rgba(239,68,68,0.85)', 'border'=>'#ef4444'],
    ['bg'=>'rgba(139,92,246,0.85)','border'=>'#8b5cf6'],
    ['bg'=>'rgba(236,72,153,0.85)','border'=>'#ec4899'],
    ['bg'=>'rgba(20,184,166,0.85)','border'=>'#14b8a6'],
    ['bg'=>'rgba(249,115,22,0.85)','border'=>'#f97316'],
];

$datasets = [];
$i = 0;
foreach ($chartData as $user => $counts) {
    $color = $colors[$i % count($colors)];
    $datasets[] = [
        'label'           => $user,
        'data'            => array_values($counts),
        'backgroundColor' => $color['bg'],
        'borderColor'     => $color['border'],
        'borderWidth'     => 2,
        'borderRadius'    => 6,
    ];
    $i++;
}

$chartJson   = json_encode(['labels' => $labels, 'datasets' => $datasets]);
$periodLabel = $selectedMonth > 0 ? $monthNames[$selectedMonth] . ' ' . $selectedYear : 'Year ' . $selectedYear;
$topUserLabel = $selectedMonth > 0 ? 'requests this month' : 'requests this year';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Request Graph</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="bg-slate-100">
    <main id="main-content" class="md:ml-56 pt-20 md:pt-5 min-h-screen p-4 md:p-8 transition-all duration-300">

        <!-- Page Header -->
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Monthly Budget Requests</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= $selectedMonth > 0
                        ? 'Daily breakdown for ' . $monthNames[$selectedMonth] . ' ' . $selectedYear
                        : 'Budget requests submitted per user, per month' ?>
                </p>
            </div>

            <!-- Filters — stacks on mobile -->
            <form method="GET" class="flex items-center gap-2 flex-wrap">
                <select name="year" onchange="this.form.submit()"
                    class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 shadow-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <?php foreach ($availableYears as $yr): ?>
                        <option value="<?= $yr ?>" <?= $yr == $selectedYear ? 'selected' : '' ?>><?= $yr ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="month" onchange="this.form.submit()"
                    class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 shadow-sm outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <option value="0" <?= $selectedMonth === 0 ? 'selected' : '' ?>>All Months</option>
                    <?php foreach ($monthNames as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Summary Cards — 3 cols on sm+, stacked on mobile -->
        <div class="grid grid-cols-3 gap-3 mb-5">
            <!-- Total Requests -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 truncate">Total Requests</p>
                <p class="text-2xl font-bold text-slate-800"><?= number_format($totalRequests) ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5 truncate">In <?= $periodLabel ?></p>
            </div>
            <!-- Total Requestors -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 truncate">Requestors</p>
                <p class="text-2xl font-bold text-slate-800"><?= $totalUsers ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5">Unique users</p>
            </div>
            <!-- Top Requestor -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 truncate">Top Requestor</p>
                <p class="text-sm font-bold text-slate-800 truncate leading-tight"><?= $topUser ?: '—' ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5"><?= $topUserCount ?> <?= $topUserLabel ?></p>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 md:p-6">
            <?php if (empty($chartData)): ?>
                <div class="flex flex-col items-center justify-center h-48 text-slate-400">
                    <svg class="w-10 h-10 mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5V19a1 1 0 001 1h4v-5h4v5h4v-5h4v5a1 1 0 001-1v-5.5M3 13.5L12 4l9 9.5" />
                    </svg>
                    <p class="text-sm font-medium">No data for <?= $periodLabel ?></p>
                </div>
            <?php else: ?>

                <!-- Chart topbar -->
                <div class="flex items-center justify-between mb-4 gap-2">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest hidden sm:block">Stacked Bar Chart</p>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest sm:hidden"><?= $periodLabel ?></p>

                    <!-- Requestors Dropdown -->
                    <div class="relative flex-shrink-0" id="requestorsWrap">
                        <button onclick="toggleDropdown(event)"
                            class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg px-3 py-1.5 transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6-4a3 3 0 11-6 0 3 3 0 016 0zM3 8a3 3 0 116 0A3 3 0 013 8z"/>
                            </svg>
                            <span class="hidden sm:inline">Requestors</span>
                            <span class="bg-blue-600 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 leading-none"><?= $totalUsers ?></span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown panel -->
                        <div id="requestorsDropdown"
                            class="hidden absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Requestors</p>
                                <span class="text-[10px] text-slate-400">Tap to toggle</span>
                            </div>
                            <ul class="py-1 max-h-64 overflow-y-auto" id="requestorList"></ul>
                        </div>
                    </div>
                </div>

                <!-- Chart — scrollable on mobile so bars don't get squished -->
                <div class="overflow-x-auto -mx-4 md:mx-0 px-4 md:px-0">
                    <div style="min-width: <?= $selectedMonth > 0 ? max(600, $daysInMonth * 18) : 420 ?>px; height: 340px; position: relative;">
                        <canvas id="budgetChart"></canvas>
                    </div>
                </div>

                <!-- Mobile legend (below chart) -->
                <div class="mt-4 flex flex-wrap gap-2 sm:hidden" id="mobileLegend"></div>

            <?php endif; ?>
        </div>

        <!-- Mobile: per-user breakdown cards (only on small screen) -->
        <?php if (!empty($chartData)): ?>
        <div class="mt-4 sm:hidden space-y-2" id="mobileBreakdown"></div>
        <?php endif; ?>

    </main>

    <?php if (!empty($chartData)): ?>
    <script>
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const chartData = <?= $chartJson ?>;

        // ── Requestors dropdown ──
        function buildRequestorList(chart) {
            const ul = document.getElementById('requestorList');
            ul.innerHTML = '';
            chart.data.datasets.forEach((ds, index) => {
                const total = ds.data.reduce((a, b) => a + b, 0);
                const li = document.createElement('li');
                li.id = 'req-item-' + index;
                li.className = 'flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 active:bg-slate-100 cursor-pointer transition select-none';
                li.innerHTML = `
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:${ds.borderColor}"></span>
                    <span class="text-sm text-slate-700 flex-1 truncate font-medium">${ds.label}</span>
                    <span class="text-xs font-semibold text-slate-400 bg-slate-100 rounded-full px-2 py-0.5">${total}</span>
                `;
                li.onclick = () => {
                    const meta = chart.getDatasetMeta(index);
                    meta.hidden = !meta.hidden;
                    chart.update();
                    li.classList.toggle('opacity-40', meta.hidden);
                };
                ul.appendChild(li);
            });
        }

        // ── Mobile legend ──
        function buildMobileLegend() {
            const wrap = document.getElementById('mobileLegend');
            if (!wrap) return;
            chartData.datasets.forEach(ds => {
                const span = document.createElement('span');
                span.className = 'flex items-center gap-1.5 text-[11px] font-medium text-slate-600';
                span.innerHTML = `<span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${ds.borderColor}"></span>${ds.label}`;
                wrap.appendChild(span);
            });
        }

        // ── Mobile per-user breakdown cards ──
        function buildMobileBreakdown() {
            const wrap = document.getElementById('mobileBreakdown');
            if (!wrap) return;
            chartData.datasets.forEach(ds => {
                const total = ds.data.reduce((a, b) => a + b, 0);
                if (!total) return;
                const card = document.createElement('div');
                card.className = 'bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3 flex items-center gap-3';
                card.innerHTML = `
                    <span class="w-3 h-10 rounded-full flex-shrink-0" style="background:${ds.borderColor}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">${ds.label}</p>
                        <p class="text-[10px] text-slate-400">${total} request${total !== 1 ? 's' : ''}</p>
                    </div>
                    <span class="text-xl font-bold" style="color:${ds.borderColor}">${total}</span>
                `;
                wrap.appendChild(card);
            });
        }

        function toggleDropdown(e) {
            e.stopPropagation();
            document.getElementById('requestorsDropdown').classList.toggle('hidden');
        }

        document.addEventListener('click', () => {
            document.getElementById('requestorsDropdown')?.classList.add('hidden');
        });

        const budgetChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} request${ctx.parsed.y !== 1 ? 's' : ''}`,
                            footer: items => {
                                const total = items.reduce((sum, i) => sum + i.parsed.y, 0);
                                return `Total: ${total} request${total !== 1 ? 's' : ''}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#64748b' }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: { size: 10 }, color: '#64748b' },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });

        buildRequestorList(budgetChart);
        buildMobileLegend();
        buildMobileBreakdown();
    </script>
    <?php endif; ?>
</body>
</html>