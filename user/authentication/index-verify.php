<?php
// user/authentication/index-verify.php

include ROOT_PATH . '/network/connect.php';

$token = trim($_GET['token'] ?? '');
$status = 'invalid'; // default

if (!empty($token)) {

    // ── Look up token ──────────────────────────────────────────────────────────
    $stmt = $conn->prepare("SELECT * FROM nobleaccount WHERE token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$account) {
        $status = 'invalid'; // token not found

    } elseif ($account['verified']) {
        $status = 'already_verified';

    } elseif (strtotime($account['token_expires']) < time()) {
        $status = 'expired';

    } else {
        // ── Activate account ───────────────────────────────────────────────────
        $stmt = $conn->prepare("UPDATE nobleaccount SET verified = 1, token = NULL, token_expires = NULL WHERE id = ?");
        $stmt->bind_param("i", $account['id']);
        $stmt->execute();
        $stmt->close();

        $status = 'success';
    }
}

$messages = [
    'success' => [
        'icon' => 'fa-circle-check',
        'color' => 'emerald',
        'title' => 'Account verified!',
        'text' => 'Your account has been successfully verified. You can now sign in with Google.',
        'action' => true,
    ],
    'already_verified' => [
        'icon' => 'fa-circle-check',
        'color' => 'amber',
        'title' => 'Already verified',
        'text' => 'This account has already been verified. Please sign in.',
        'action' => true,
    ],
    'expired' => [
        'icon' => 'fa-clock',
        'color' => 'red',
        'title' => 'Link expired',
        'text' => 'This verification link has expired. Please register again to get a new link.',
        'action' => false,
    ],
    'invalid' => [
        'icon' => 'fa-triangle-exclamation',
        'color' => 'red',
        'title' => 'Invalid link',
        'text' => 'This verification link is invalid or has already been used.',
        'action' => false,
    ],
];

$msg = $messages[$status];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account — NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <style>
        .card-glow {
            box-shadow:
                0 0 0 1px rgba(217, 119, 6, 0.08),
                0 20px 60px -10px rgba(0, 0, 0, 0.6),
                0 0 80px -20px rgba(217, 119, 6, 0.05);
        }

        .btn-signin {
            background: linear-gradient(135deg, #d97706, #b45309);
            transition: opacity 0.2s, transform 0.15s;
        }

        .btn-signin:hover {
            opacity: 0.92;
        }

        .btn-signin:active {
            transform: scale(0.99);
        }

        .btn-register {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: background 0.2s;
        }

        .btn-register:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fadeUp 0.5s ease forwards;
        }

        .delay-1 {
            animation-delay: 0.05s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.15s;
            opacity: 0;
        }

        @keyframes pop {
            0% {
                transform: scale(0.7);
                opacity: 0;
            }

            70% {
                transform: scale(1.08);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .icon-pop {
            animation: pop 0.4s ease forwards 0.2s;
            opacity: 0;
        }
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
        <div
            class="card-glow bg-neutral-900/80 backdrop-blur-sm border border-white/[0.06] rounded-2xl px-7 py-10 fade-up delay-2">

            <div class="flex flex-col items-center text-center">

                <!-- Icon -->
                <?php
                $colorMap = [
                    'emerald' => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20', 'text' => 'text-emerald-400'],
                    'amber' => ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/20', 'text' => 'text-amber-400'],
                    'red' => ['bg' => 'bg-red-500/10', 'border' => 'border-red-500/20', 'text' => 'text-red-400'],
                ];
                $c = $colorMap[$msg['color']];
                ?>
                <div
                    class="icon-pop w-16 h-16 rounded-full <?= $c['bg'] ?> border <?= $c['border'] ?> flex items-center justify-center mb-5">
                    <i class="fa-solid <?= $msg['icon'] ?> <?= $c['text'] ?> text-2xl"></i>
                </div>

                <h2 class="text-white text-lg font-semibold mb-2"><?= htmlspecialchars($msg['title']) ?></h2>
                <p class="text-neutral-500 text-sm leading-relaxed mb-7"><?= htmlspecialchars($msg['text']) ?></p>

                <!-- Actions -->
                <?php if ($msg['action']): ?>
                    <a href="<?= BASE_URL ?>/loginuser"
                        class="btn-signin w-full flex items-center justify-center gap-2 text-white text-sm font-semibold py-3 px-4 rounded-xl mb-3">
                        <i class="fa-brands fa-google text-xs"></i>
                        Sign in with Google
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/register"
                        class="btn-register w-full flex items-center justify-center text-neutral-300 text-sm font-medium py-3 px-4 rounded-xl mb-3">
                        Register again
                    </a>
                <?php endif; ?>

            </div>

        </div>

        <p class="text-center text-white text-xs mt-6">
            &copy; <?= date('Y') ?> Noble Accounting. All rights reserved.
        </p>

    </div>

</body>

</html>