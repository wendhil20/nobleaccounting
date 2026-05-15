<?php
// user/authentication/index-login.php
session_name('noblehome');
session_start();

include ROOT_PATH . '/network/connect.php';

// Already logged in
if (!empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/userhome');
    exit;
}

// Generate CSRF state token
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Build Google OAuth 2.0 Authorization URL
$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => BASE_URL . '/callback',  // adjust to your actual callback route
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account',
    'state'         => $state,
]);

$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <style>

        .noise-bg::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        .card-glow {
            box-shadow:
                0 0 0 1px rgba(217, 119, 6, 0.08),
                0 20px 60px -10px rgba(0, 0, 0, 0.6),
                0 0 80px -20px rgba(217, 119, 6, 0.05);
        }

        .google-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .google-btn:hover { background-color: #f5f5f5; }
        .google-btn:active { transform: scale(0.99); }

        .divider-line {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .fade-up  { animation: fadeUp 0.5s ease forwards; }
        .delay-1  { animation-delay: 0.05s; opacity: 0; }
        .delay-2  { animation-delay: 0.15s; opacity: 0; }
        .delay-3  { animation-delay: 0.25s; opacity: 0; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 0.7s linear infinite; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-12 relative"
      style="background-image: url('<?= BASE_URL ?>/icon/building2.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <div class="w-full max-w-sm relative z-10">

          <!-- Brand -->
        <div class="flex items-center justify-center gap-4 mb-8">
            <div class="flex items-center justify-center w-14 h-14 rounded-lg shrink-0">
                <img src="<?= BASE_URL ?>/icon/logo.png" class="object-contain bg-white rounded-md p-1" alt="error">
            </div>
            <div class="w-px h-12 bg-white"></div>
            <div class="">
                <h1 class="text-xl font-bold tracking-wide text-white uppercase leading-tight">
                    Noble<span class="text-yellow-500">Home</span> Accounting
                </h1>
                <p class="text-xs text-white tracking-widest uppercase mt-0.5">Management System</p>
            </div>
        </div>


        <!-- Card -->
        <div class="card-glow bg-neutral-900/80 backdrop-blur-sm border border-white/[0.06] rounded-2xl px-7 py-8 fade-up delay-2">

            <div class="mb-6">
                <h2 class="text-white text-base font-semibold mb-1">Welcome</h2>
                <p class="text-gray-400 text-sm leading-relaxed">Sign in with your organization Google account to continue.</p>
            </div>

            <!-- Error messages from callback redirect -->
            <?php if (!empty($_GET['error'])): ?>
            <div class="mb-5 flex items-start gap-2.5 bg-red-500/8 border border-red-500/15 text-red-400 text-sm rounded-xl px-4 py-3">
                <i class="fa-solid fa-circle-exclamation shrink-0 mt-0.5 text-xs"></i>
                <span class="leading-relaxed">
                    <?php
                    $errors = [
                        'state_mismatch'  => 'Security check failed. Please try again.',
                        'token_failed'    => 'Could not verify your Google account. Please try again.',
                        'domain_mismatch' => 'Access denied. Only @noble.com accounts are allowed.',
                        'not_registered'  => 'Your account is not registered in the system.',
                        'access_denied'   => 'Sign-in was cancelled. Please try again.',
                    ];
                    echo htmlspecialchars($errors[$_GET['error']] ?? 'An error occurred. Please try again.');
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- Divider -->
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 h-px divider-line"></div>
                <span class="text-gray-400 text-xs font-medium tracking-wider uppercase">Continue with</span>
                <div class="flex-1 h-px divider-line"></div>
            </div>

            <!-- Google Button — plain anchor, no AJAX needed -->
            <a href="<?= htmlspecialchars($googleAuthUrl) ?>"
               id="googleBtn"
               onclick="handleClick(this)"
               class="google-btn w-full flex items-center justify-center gap-3 bg-white text-neutral-800 text-sm font-semibold py-3 px-4 rounded-xl shadow-sm">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" class="shrink-0">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                    <path d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                <span id="btnText">Sign in with Google</span>
                <svg id="btnSpinner" class="hidden spinner w-4 h-4 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </a>
            
        <p class="text-center text-white text-xs mt-6 fade-up delay-3">
            Don't have an account?
            <a href="<?= BASE_URL ?>/register" class="text-yellow-500 hover:text-amber-400 transition-colors ml-1">Register</a>
        </p>

        

        </div>

        <!-- Footer -->
        <p class="text-center text-white text-xs mt-6 fade-up delay-3">
            &copy; <?= date('Y') ?> Noble Accounting. All rights reserved.
        </p>

    </div>

    <script>
        function handleClick(el) {
            document.getElementById('btnText').textContent = 'Redirecting\u2026';
            document.getElementById('btnSpinner').classList.remove('hidden');
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.7';
        }
    </script>

</body>
</html>