<?php
// index.php
define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/vendor/autoload.php';  

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
    : $_ENV['APP_URL']
);


// ─── Routing ──────────────────────────────────────────────────────────────────
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = trim($request, '/');
$request = preg_replace('#^nobleaccounting/?#', '', $request);
$request = trim($request, '/');

if ($request === '' || $request === 'home') {
    $request = 'home';
}

// ─── Define Admin Routes ──────────────────────────────────────────────────────
$adminRoutes = [

     // it
    'it',                               

    // accounting
    'accounting',                       
    'download-pdfbudgetrequest',        
    'accountingdashboard',              
    'announcementdashboard',            
    'announcement',                     

    //staffaccounting
    'accountingstaff',                  
    'accountingstaffdashboard',         
    'fetchapproved',                    
    'markreceived',                     
    'fetchacknowledged',                
    'accountingstaffannouncement',      

    // custodian
    'accountingcustodian',              
    'accountingcustodiandashboard',     
    'fetchreceived',                    
    'submitrequestvoucher',             
    'accountingcustodianassistant',     
    'announcementcustodian',            

    // custiodiansublink
    'projectmonitor',                   
    'projectdetail',                    
    'fetchprojects',                    
    'saveproject',                      
    'fetchprojectbilling',              
    'saveprojectbilling',               
    'deleteprojectbilling',             
    'fetchprojectexpense',              
    'saveprojectexpense',               
    'deleteprojectexpense',             
    'exportprojectexcel',               
    'saveincomeloss',                   

    //cashvoucher
    'cashvoucherdashboard',             
    'cashvoucherapproved',              
    'cashvoucherprepared',              
    'cashvoucherfetchall',              
    'releasevoucher',                   

    // hr
    'humanresource',                    
    'superad',                          
    'department',                       
    'hrfetch',                          
    'hrposition',                       
    'humanresourcerequest',             

    // operation
    'operation',                        

    // sales & market
    'salesmarket',                      
    // graphic design
    'graphicdesign',                 
    // designer
    'designer',                         
    // cutting list
    'cuttinglist',           
    
    'unauthorized',                    
    'fetchrequests',                   
    'actionrequest',                   
    'fetchnotifications',              
    'marknotificationsread',           
    'notificationstream',              
    'pingrequest',                     
    'fetchstaff',                      
    'heartbeat',                       


];

if (in_array($request, $adminRoutes)) {
    session_name("nobleadmin");
} else {
    session_name("nobleuser");
}

session_start();

$routes = [
    'home'                             => 'user/authentication/index-login.php',
    'logout'                           => 'admin/authentication/index-logout.php',
    'loginadmin'                       => 'admin/authentication/index-login.php',
    'loginuser'                        => 'user/authentication/index-login.php',
    'callback'                         => 'user/authentication/index-callback.php',
    'userhome'                         => 'user/ui/index-main-page-1.php',
    'register'                         => 'user/authentication/index-register.php',
    'verify'                           => 'user/authentication/index-verify.php',
    'logoutuser'                       => 'user/authentication/index-logout.php',
    'fetchannouncements'               => 'user/navigation/backend/backend-announcement/fetchannouncements.php',
    'saveannouncement'                 => 'admin/announcementcentral/save-announcement.php',
    'deleteannouncement'               => 'admin/announcementcentral/deleteannouncement.php',
    'fetchannouncementsadmin'          => 'admin/announcementcentral/fetchannouncementsadmin.php',
    'generalannouncement'              => 'admin/announcementcentral/announcement-view.php',

    //user
    'myvouchers'                       => 'user/ui/index-my-vouchers.php',
    'fetchmyvouchers'                  => 'user/ui/backend/index-fetch-my-vouchers.php',
    'acceptvoucher'                    => 'user/ui/backend/index-accept-voucher.php',
    'fetchheads'                       => 'user/ui/backend/index-fetch-heads.php',
    'submitrequest'                    => 'user/ui/backend/index-submit-request.php',
    'fetchmyrequests'                  => 'user/ui/backend/index-fetch-my-requests.php',
    'requesthistory'                   => 'user/ui/index-my-request-history.php',
    'dashboard'                        => 'user/ui/index-main-dashboard.php',

    // it
    'it'                               => 'admin/ui-informationtech/index-it-main.php',

    // accounting
    'accounting'                       => 'admin/ui-accounting/index-accounting-main.php',
    'download-pdfbudgetrequest'        => 'admin/ui-accounting/backend/backend-accounting/download-pdfbudgetrequest.php',
    'accountingdashboard'              => 'admin/ui-accounting/index-accounting-dashboard.php',
    'announcementdashboard'            => 'admin/ui-accounting/index-accounting-dashboard.php',
    'announcement'                     => 'admin/ui-accounting/index-accounting-announcement.php',

    //staffaccounting
    'accountingstaff'                  => 'admin/ui-accounting/index-staff-main.php',
    'accountingstaffdashboard'         => 'admin/ui-accounting/index-staff-dashboard.php',
    'fetchapproved'                    => 'admin/ui-accounting/backend/backend-staff/index-staff-fetch-approved.php',
    'markreceived'                     => 'admin/ui-accounting/backend/backend-staff/index-staff-mark-received.php',
    'fetchacknowledged'                => 'admin/ui-accounting/backend/backend-staff/index-staff-acknowledge.php',
    'accountingstaffannouncement'      => 'admin/ui-accounting/index-staff-announcement.php',

    // custodian
    'accountingcustodian'              => 'admin/ui-accounting/index-custodian-main.php',
    'accountingcustodiandashboard'     => 'admin/ui-accounting/index-custodian-dashboard.php',
    'fetchreceived'                    => 'admin/ui-accounting/backend/backend-custodian/index-custodian-fetch-received.php',
    'submitrequestvoucher'             => 'admin/ui-accounting/backend/backend-custodian/index-custodian-submit-voucher.php',
    'accountingcustodianassistant'     => 'admin/ui-accounting/index-custodianassist-main.php',
    'announcementcustodian'            => 'admin/ui-accounting/index-custodian-dashboard.php',

    // custiodiansublink
    'projectmonitor'                   => 'admin/ui-accounting/index-projectmonitor-main.php',
    'projectdetail'                    => 'admin/ui-accounting/index-projectmonitor-details.php',
    'fetchprojects'                    => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-fetchprojects.php',
    'saveproject'                      => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-saveproject.php',
    'fetchprojectbilling'              => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-fetchprojectbilling.php',
    'saveprojectbilling'               => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-saveprojectbilling.php',
    'deleteprojectbilling'             => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-deleteprojectbilling.php',
    'fetchprojectexpense'              => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-fetchprojectexpense.php',
    'saveprojectexpense'               => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-saveprojectexpense.php',
    'deleteprojectexpense'             => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-deleteprojectexpense.php',
    'exportprojectexcel'               => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-exportprojectexcel.php',
    'saveincomeloss'                   => 'admin/ui-accounting/backend/backend-custodian/index-projectmonitor-saveincomeloss.php',

    //cashvoucher
    'cashvoucherdashboard'             => 'admin/ui-accounting/index-cashvoucherdashboard.php',
    'cashvoucherapproved'              => 'admin/ui-accounting/backend/backend-cashvoucher/index-cashvoucherapproved.php',
    'cashvoucherprepared'              => 'admin/ui-accounting/backend/backend-cashvoucher/index-cashvoucherprepared.php',
    'cashvoucherfetchall'              => 'admin/ui-accounting/backend/backend-cashvoucher/index-cashvoucherfetchall.php',
    'releasevoucher'                   => 'admin/ui-accounting/backend/backend-cashvoucher/index-cashreleasevoucher.php',

    // hr
    'humanresource'                    => 'admin/ui-humanresource/humanresource-main.php',
    'superad'                          => 'admin/ui-humanresource/humanresource-registration-account.php',
    'department'                       => 'admin/ui-humanresource/humanresource-registration-department.php',
    'hrfetch'                          => 'admin/ui-humanresource/humanresource-hrfetch.php',
    'hrposition'                       => 'admin/ui-humanresource/humanresource-hrposition.php',
    'humanresourcerequest'             => 'admin/ui-humanresource/humanresource-request.php',

    // operation
    'operation'                        => 'admin/ui-operation/index-operation-main.php',

    // sales & market
    'salesmarket'                      => 'admin/ui-salesmarket/index-sales-main.php',

    // graphic design
    'graphicdesign'                    => 'admin/ui-graphicdesign/index-graphic-main.php',

    // designer
    'designer'                         => 'admin/ui-designer/index-designer-main.php',

    // cutting list
    'cuttinglist'                      => 'admin/ui-cuttinglist/index-cuttinglist-main.php',

    'unauthorized'                     => 'admin/authentication/index-unauthorized.php',
    'fetchrequests'                    => 'admin/requestcentral/fetch-requests.php',
    'actionrequest'                    => 'admin/requestcentral/action-request.php',
    'fetchnotifications'               => 'admin/navigation/sidebar-fetch-notifications.php',
    'marknotificationsread'            => 'admin/navigation/sidebar-mark-notifications-read.php',
    'notificationstream'               => 'admin/navigation/sidebar-notification-stream.php',
    'pingrequest'                      => 'admin/requestcentral/ping-request.php',
    'fetchstaff'                       => 'admin/requestcentral/fetch-staff.php',
    'heartbeat'                        => 'admin/authentication/index-heartbeat.php',

    //user
    'fetchnotificationsuser'           => 'user/navigation/backend/backend-notification/fetchnotifications.php',
    'readnotificationuser'             => 'user/navigation/backend/backend-notification/readnotification.php',
    'notificationsuser'                => 'user/ui/index-notifications.php',
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