<?php
// index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);

// ─── Load .env ────────────────────────────────────────────────────────────────
$envFile = ROOT_PATH . '/.env';

if (!file_exists($envFile)) {
    die('.env file not found.');
}

foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#'))
        continue;  // skip comments/blanks
    if (!str_contains($line, '='))
        continue;                    // skip invalid lines

    [$key, $value] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($value);
}

// ─── Constants ────────────────────────────────────────────────────────────────
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');

// ─── Base URL ─────────────────────────────────────────────────────────────────
$isLocalhost = (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
);

define(
    'BASE_URL',
    $isLocalhost
    ? 'http://localhost/nobleaccounting'
    : 'https://www.noblehomedepot.com'
);
// ─── Routing ──────────────────────────────────────────────────────────────────
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = trim($request, '/');
$request = preg_replace('#^nobleaccounting/?#', '', $request);
$request = trim($request, '/');

if ($request === '' || $request === 'home') {
    $request = 'home';
}

$routes = [
    'home' => 'user/authentication/index-login.php',
    'logout' => 'admin/authentication/index-logout.php',
    'loginadmin' => 'admin/authentication/index-login.php',
    'loginuser' => 'user/authentication/index-login.php',
    'callback' => 'user/authentication/index-callback.php',
    'userhome' => 'user/ui/index-main-page-1.php',
    'register' => 'user/authentication/index-register.php',
    'verify' => 'user/authentication/index-verify.php',
    'logoutuser' => 'user/authentication/index-logout.php',
    // it
    'it' => 'admin/ui-informationtech/index-it-main.php',
    // accounting
    'accounting' => 'admin/ui-accounting/index-accounting-main.php',
    'accountingstaff' => 'admin/ui-accounting/index-staff-main.php',
    'accountingstaffdashboard' => 'admin/ui-accounting/index-staff-dashboard.php',
    'fetchapproved' => 'admin/ui-accounting/index-staff-fetch-approved.php',
    'markreceived' => 'admin/ui-accounting/index-staff-mark-received.php',
    'fetchacknowledged' => 'admin/ui-accounting/index-staff-acknowledge.php',
    'accountingcustodian' => 'admin/ui-accounting/index-custodian-main.php',
    'accountingcustodiandashboard' => 'admin/ui-accounting/index-custodian-dashboard.php',
    'download-pdfbudgetrequest' => 'admin/ui-accounting/download-pdfbudgetrequest.php',
    'fetchreceived' => 'admin/ui-accounting/index-custodian-fetch-received.php',
    'submitrequestvoucher' => 'admin/ui-accounting/index-custodian-submit-voucher.php',
    'cashvoucherdashboard' => 'admin/ui-accounting/index-cashvoucherdashboard.php',
    'cashvoucherapproved' => 'admin/ui-accounting/index-cashvoucherapproved.php',
    'cashvoucherprepared' => 'admin/ui-accounting/index-cashvoucherprepared.php',
    'cashvoucherfetchall' => 'admin/ui-accounting/index-cashvoucherfetchall.php',
    // hr
    'humanresource' => 'admin/ui-humanresource/humanresource-main.php',
    'superad' => 'admin/ui-humanresource/humanresource-registration-account.php',
    'department' => 'admin/ui-humanresource/humanresource-registration-department.php',
    'hrfetch' => 'admin/ui-humanresource/humanresource-hrfetch.php',
    'hrposition' => 'admin/ui-humanresource/humanresource-hrposition.php',
    'humanresourcerequest' => 'admin/ui-humanresource/humanresource-request.php',
    // operation
    'operation' => 'admin/ui-operation/index-operation-main.php',
    // sales & market
    'salesmarket' => 'admin/ui-salesmarket/index-sales-main.php',
    // graphic design
    'graphicdesign' => 'admin/ui-graphicdesign/index-graphic-main.php',
    // designer
    'designer' => 'admin/ui-designer/designer-main.php',
    // cutting list
    'cuttinglist' => 'admin/ui-cuttinglist/cuttinglist-main.php',

    'unauthorized' => 'admin/authentication/index-unauthorized.php',
    'fetchheads' => 'user/ui/index-fetch-heads.php',
    'submitrequest' => 'user/ui/index-submit-request.php',
    'fetchrequests' => 'admin/requestcentral/fetch-requests.php',
    'actionrequest' => 'admin/requestcentral/action-request.php',
];

$file = $routes[$request] ?? null;

if ($file === null) {
    http_response_code(404);
    include ROOT_PATH . '/404.php';
    exit;
}

$filepath = ROOT_PATH . '/' . $file;

if (file_exists($filepath)) {
    include $filepath;
} else {
    http_response_code(404);
    include ROOT_PATH . '/404.php';
    exit;
}