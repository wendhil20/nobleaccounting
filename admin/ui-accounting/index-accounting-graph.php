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
    <main class="ml-56 min-h-screen p-8">

        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Monthly Budget Requests</h1>
                <p class="text-sm text-slate-500 mt-1">
                    <?= $selectedMonth > 0
                        ? 'Daily breakdown for ' . $monthNames[$selectedMonth] . ' ' . $selectedYear
                        : 'Number of budget requests submitted per user, per month' ?>
                </p>
            </div>

            <!-- Filters -->
            <form method="GET" class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600">Year:</label>
                    <select name="year" onchange="this.form.submit()"
                        class="text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($availableYears as $yr): ?>
                            <option value="<?= $yr ?>" <?= $yr == $selectedYear ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600">Month:</label>
                    <select name="month" onchange="this.form.submit()"
                        class="text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0" <?= $selectedMonth === 0 ? 'selected' : '' ?>>All Months</option>
                        <?php foreach ($monthNames as $num => $name): ?>
                            <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Total Requests</p>
                <p class="text-3xl font-bold text-slate-800"><?= number_format($totalRequests) ?></p>
                <p class="text-xs text-slate-400 mt-1">In <?= $periodLabel ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Total Requestors</p>
                <p class="text-3xl font-bold text-slate-800"><?= $totalUsers ?></p>
                <p class="text-xs text-slate-400 mt-1">Unique users</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Top Requestor</p>
                <p class="text-xl font-bold text-slate-800 truncate"><?= $topUser ?: '—' ?></p>
                <p class="text-xs text-slate-400 mt-1"><?= $topUserCount ?> <?= $topUserLabel ?></p>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <?php if (empty($chartData)): ?>
                <div class="flex flex-col items-center justify-center h-64 text-slate-400">
                    <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5V19a1 1 0 001 1h4v-5h4v5h4v-5h4v5a1 1 0 001-1v-5.5M3 13.5L12 4l9 9.5" />
                    </svg>
                    <p class="text-sm font-medium">No data available for <?= $periodLabel ?></p>
                </div>
            <?php else: ?>

                <!-- Chart top bar: label + requestors dropdown -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-widest">Stacked Bar Chart</p>

                    <!-- Requestors Dropdown -->
                    <div class="relative" id="requestorsWrap">
                        <button onclick="toggleDropdown(event)"
                            class="flex items-center gap-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg px-3 py-2 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6-4a3 3 0 11-6 0 3 3 0 016 0zM3 8a3 3 0 116 0A3 3 0 013 8z"/>
                            </svg>
                            Requestors
                            <span class="bg-blue-600 text-white text-xs font-bold rounded-full px-1.5 py-0.5 leading-none"><?= $totalUsers ?></span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown panel -->
                        <div id="requestorsDropdown"
                            class="hidden absolute right-0 mt-2 w-60 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Requestors</p>
                                <span class="text-xs text-slate-400">Click to toggle</span>
                            </div>
                            <ul class="py-1 max-h-72 overflow-y-auto" id="requestorList"></ul>
                        </div>
                    </div>
                </div>

                <div class="relative h-96">
                    <canvas id="budgetChart"></canvas>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php if (!empty($chartData)): ?>
    <script>
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const chartData = <?= $chartJson ?>;

        // Build dropdown list
        function buildRequestorList(chart) {
            const ul = document.getElementById('requestorList');
            ul.innerHTML = '';
            chart.data.datasets.forEach((ds, index) => {
                const total = ds.data.reduce((a, b) => a + b, 0);
                const li = document.createElement('li');
                li.id = 'req-item-' + index;
                li.className = 'flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer transition select-none';
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

        function toggleDropdown(e) {
            e.stopPropagation();
            document.getElementById('requestorsDropdown').classList.toggle('hidden');
        }

        document.addEventListener('click', () => {
            document.getElementById('requestorsDropdown').classList.add('hidden');
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
                        ticks: { font: { size: 12 }, color: '#64748b' }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: { size: 12 }, color: '#64748b' },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });

        buildRequestorList(budgetChart);
    </script>
    <?php endif; ?>
</body>
</html>