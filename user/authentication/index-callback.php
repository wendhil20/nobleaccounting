<?php
// user/authentication/callback.php
session_name('noblehome');
session_start();

include ROOT_PATH . '/network/connect.php';

$ALLOWED  = 'gmail.com';
$loginUrl = BASE_URL . '/loginuser';

// ─── Helper: redirect to login with error code ───────────────────────────────
function fail(string $code): never {
    global $loginUrl;
    header('Location: ' . $loginUrl . '?error=' . $code);
    exit;
}

// ─── 1. User cancelled on Google's side ──────────────────────────────────────
if (!empty($_GET['error'])) {
    fail('access_denied');
}

// ─── 2. Expect both `code` and `state` params ────────────────────────────────
$code  = trim($_GET['code']  ?? '');
$state = trim($_GET['state'] ?? '');

if (empty($code) || empty($state)) {
    fail('access_denied');
}

// ─── 3. Validate CSRF state ───────────────────────────────────────────────────
if (!hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
    fail('state_mismatch');
}
unset($_SESSION['oauth_state']); // one-time use

// ─── 4. Exchange authorization code for tokens ───────────────────────────────
$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false,
    stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'code'          => $code,
                'client_id'     => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri'  => BASE_URL . '/callback',  // must match exactly
                'grant_type'    => 'authorization_code',
            ]),
        ],
    ])
);

if (!$tokenResponse) {
    fail('token_failed');
}

$tokens = json_decode($tokenResponse, true);

if (empty($tokens['id_token'])) {
    fail('token_failed');
}

// ─── 5. Verify the id_token with Google ──────────────────────────────────────
$verify  = file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($tokens['id_token']));
$payload = json_decode($verify, true);

if (!$verify || isset($payload['error'])) {
    fail('token_failed');
}

if (($payload['aud'] ?? '') !== GOOGLE_CLIENT_ID) {
    fail('token_failed');
}

// ─── 6. Enforce allowed domain ───────────────────────────────────────────────
$email = strtolower(trim($payload['email'] ?? ''));

if (empty($email) || !str_ends_with($email, '@' . $ALLOWED)) {
    fail('domain_mismatch');
}

// ─── 7. Check if account exists in nobleaccount ──────────────────────────────
$stmt = $conn->prepare("SELECT * FROM nobleaccount WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    fail('not_registered');
}

// ─── 8. Regenerate session ID (prevents session fixation) ────────────────────
session_regenerate_id(true);

// ─── 9. Set session and redirect ─────────────────────────────────────────────
$_SESSION['account_id'] = $account['id'];
$_SESSION['username']   = $account['name'];
$_SESSION['email']      = $account['email'];
$_SESSION['picture']    = $payload['picture'] ?? ''; // ← dagdag ito
$_SESSION['role']       = $account['role'];
$_SESSION['logged_in']  = true;

header('Location: ' . BASE_URL . '/userhome');
exit;