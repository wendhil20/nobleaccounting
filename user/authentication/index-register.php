<?php
// user/authentication/index-register.php

include ROOT_PATH . '/network/connect.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require ROOT_PATH . '/vendor/autoload.php';

// Already logged in
if (!empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/userhome');
    exit;
}

$isLocalhost = str_contains($_SERVER['HTTP_HOST'], 'localhost') ||
    str_contains($_SERVER['HTTP_HOST'], '127.0.0.1');
$ALLOWED_DOMAIN = 'gmail.com';

$error = '';
$success = '';

// ─── Send verification email via PHPMailer ────────────────────────────────────
function sendVerificationEmail(string $toEmail, string $toName, string $verifyUrl): bool
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_FROM'];
        $mail->Password = $_ENV['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender & recipient
        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_NAME'] ?? 'NobleHome Accounting');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify your NobleHome account';
        $mail->Body = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#e8e8e8;font-family:\'DM Sans\',Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#e8e8e8;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="480" cellpadding="0" cellspacing="0" style="background:#141414;border:1px solid rgba(255,255,255,0.06);border-radius:16px;overflow:hidden;max-width:480px;width:100%;">

          <!-- Header -->
          <tr>
            <td style="padding:32px 40px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.05);">
              <div style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:-0.3px;">
                Noble<span style="color:#f59e0b;">Home</span>
              </div>
              <div style="color:#e8e8e8;font-size:10px;letter-spacing:0.15em;text-transform:uppercase;margin-top:4px;">
                Accounting System
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:32px 40px;">
              <p style="color:#e8e8e8;font-size:14px;margin:0 0 8px;">Hi <strong style="color:#ffffff;">' . htmlspecialchars($toName) . '</strong>,</p>
              <p style="color:#e8e8e8;font-size:14px;line-height:1.6;margin:0 0 28px;">
                You\'re almost there! Click the button below to verify your email address and activate your NobleHome account.
              </p>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <a href="' . $verifyUrl . '"
                       style="display:inline-block;background:linear-gradient(135deg,#d97706,#b45309);color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:10px;letter-spacing:0.01em;">
                      Verify my account →
                    </a>
                  </td>
                </tr>
              </table>

              <p style="color:#e8e8e8;font-size:12px;line-height:1.6;margin:28px 0 0;text-align:center;">
                This link expires in <strong style="color:#525252;">24 hours</strong>.<br>
                If you didn\'t create an account, you can safely ignore this email.
              </p>
            </td>
          </tr>

          <!-- Link fallback -->
          <tr>
            <td style="padding:0 40px 24px;">
              <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;padding:12px 16px;">
                <p style="color:#e8e8e8;font-size:11px;margin:0 0 4px;">Or copy this link:</p>
                <p style="color:#d97706;font-size:11px;margin:0;word-break:break-all;">' . $verifyUrl . '</p>
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
              <p style="color:#e8e8e8;font-size:11px;margin:0;">
                &copy; ' . date('Y') . ' Noble Accounting. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

        $mail->AltBody = "Hi {$toName},\n\nVerify your NobleHome account:\n\n{$verifyUrl}\n\nThis link expires in 24 hours.\n\n© " . date('Y') . " Noble Accounting.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

// ─── Handle POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($name) || empty($email)) {
        $error = 'Please fill in all fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif (!$isLocalhost && !str_ends_with($email, '@' . $ALLOWED_DOMAIN)) {
        $error = 'Only @' . $ALLOWED_DOMAIN . ' email accounts are allowed.';

    } else {
        // Check if already registered
        $stmt = $conn->prepare("SELECT id, verified FROM nobleaccount WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing && $existing['verified']) {
            $error = 'This email is already registered. Please sign in.';

        } else {
            // ⬅ DITO ILAGAY — bago mag-generate ng token
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM nobleaccount WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($result['cnt'] >= 1) {
                $error = 'Please wait 1 minute before requesting another verification email.';
            } else {

                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                if ($existing && !$existing['verified']) {
                    // Resend — update token
                    $stmt = $conn->prepare("UPDATE nobleaccount SET name = ?, token = ?, token_expires = ? WHERE email = ?");
                    $stmt->bind_param("ssss", $name, $token, $expires, $email);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // New account
                    $stmt = $conn->prepare("INSERT INTO nobleaccount (name, email, token, token_expires, verified, role) VALUES (?, ?, ?, ?, 0, 'user')");
                    $stmt->bind_param("ssss", $name, $email, $token, $expires);
                    $stmt->execute();
                    $stmt->close();
                }

                $verifyUrl = BASE_URL . '/verify?token=' . $token;

                $sent = sendVerificationEmail($email, $name, $verifyUrl);
                $success = $sent ? 'sent' : 'mail_error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <style>
        .card-glow {
            box-shadow:
                0 0 0 1px rgba(217, 119, 6, 0.08),
                0 20px 60px -10px rgba(0, 0, 0, 0.6),
                0 0 80px -20px rgba(217, 119, 6, 0.05);
        }

        .input-field {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: border-color 0.2s, background 0.2s;
        }

        .input-field:focus {
            outline: none;
            border-color: rgba(217, 119, 6, 0.5);
            background: rgba(255, 255, 255, 0.06);
        }

        .input-field::placeholder {
            color: #525252;
        }

        .submit-btn {
            background: linear-gradient(135deg, #d97706, #b45309);
            transition: opacity 0.2s, transform 0.15s;
        }

        .submit-btn:hover {
            opacity: 0.92;
        }

        .submit-btn:active {
            transform: scale(0.99);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .divider-line {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.07), transparent);
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

        .delay-3 {
            animation-delay: 0.25s;
            opacity: 0;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            animation: spin 0.7s linear infinite;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(217, 119, 6, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(217, 119, 6, 0);
            }
        }

        .pulse {
            animation: pulse-ring 2s infinite;
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
            class="card-glow bg-neutral-900/80 backdrop-blur-sm border border-white/[0.06] rounded-2xl px-7 py-8 fade-up delay-2">

            <?php if ($success === 'sent'): ?>

                <!-- Email sent -->
                <div class="flex flex-col items-center text-center py-4">
                    <div
                        class="w-14 h-14 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-4 pulse">
                        <i class="fa-solid fa-envelope text-amber-400 text-xl"></i>
                    </div>
                    <h2 class="text-white text-base font-semibold mb-2">Check your email</h2>
                    <p class="text-gray-400 text-sm leading-relaxed mb-4">
                        A verification link was sent to<br>
                        <span class="text-amber-400 font-medium"><?= htmlspecialchars($_POST['email']) ?></span>
                    </p>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        Link expires in 24 hours. Check your spam folder if you don't see it.
                    </p>
                </div>

            <?php elseif ($success === 'mail_error'): ?>

                <!-- Mail failed -->
                <div class="flex flex-col items-center text-center py-4">
                    <div
                        class="w-14 h-14 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-xl"></i>
                    </div>
                    <h2 class="text-white text-base font-semibold mb-2">Email could not be sent</h2>
                    <p class="text-neutral-500 text-sm leading-relaxed mb-5">
                        Your account was created but we couldn't send the verification email. Please contact your
                        administrator.
                    </p>
                    <a href="<?= BASE_URL ?>/register"
                        class="text-amber-500 text-sm hover:text-amber-400 transition-colors">
                        ← Try again
                    </a>
                </div>

            <?php elseif (str_starts_with($success, 'dev:')): ?>

                <!-- Dev mode / localhost -->
                <?php $devLink = substr($success, 4); ?>
                <div class="flex flex-col items-center text-center py-2">
                    <div
                        class="w-14 h-14 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-flask text-emerald-400 text-xl"></i>
                    </div>
                    <h2 class="text-white text-base font-semibold mb-1">Localhost — dev mode</h2>
                    <p class="text-neutral-500 text-sm leading-relaxed mb-4">PHPMailer skipped on localhost. Click below to
                        verify:</p>
                    <a href="<?= htmlspecialchars($devLink) ?>"
                        class="w-full text-center bg-emerald-500/10 hover:bg-emerald-500/15 border border-emerald-500/20 text-emerald-400 text-xs font-medium py-3 px-4 rounded-xl transition-colors break-all leading-relaxed">
                        Verify account →
                    </a>
                </div>

            <?php else: ?>

                <!-- Form -->
                <div class="mb-6">
                    <h2 class="text-white text-base font-semibold mb-1">Create an account</h2>

                </div>

                <?php if ($error): ?>
                    <div
                        class="mb-5 flex items-start gap-2.5 bg-red-500/8 border border-red-500/15 text-red-400 text-sm rounded-xl px-4 py-3">
                        <i class="fa-solid fa-circle-exclamation shrink-0 mt-0.5 text-xs"></i>
                        <span class="leading-relaxed"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <div class="space-y-3 mb-5">
                    <div>
                        <label class="block text-white text-xs font-medium mb-1.5 tracking-wide uppercase">
                            <i class="fa-solid fa-user pr-1"></i>
                            Full Name</label>
                        <input type="text" name="name" id="inp-name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            placeholder="Juan dela Cruz" class="input-field w-full text-white text-sm rounded-xl px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-white text-xs font-medium mb-1.5 tracking-wide uppercase">
                            <i class="fa-solid fa-envelope pr-1"></i>
                            Email Address</label>
                        <input type="email" name="email" id="inp-email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            placeholder="<?= $isLocalhost ? 'you@example.com' : 'you@' . $ALLOWED_DOMAIN ?>"
                            class="input-field w-full text-white text-sm rounded-xl px-4 py-3">
                    </div>
                </div>

                <button onclick="handleSubmit()" id="submitBtn"
                    class="submit-btn w-full flex items-center justify-center gap-2 text-white text-sm font-semibold py-3 px-4 rounded-xl">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span id="btnText">Send Verification Link</span>
                    <svg id="btnSpinner" class="hidden spinner w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </button>

            <?php endif; ?>

        </div>

        <p class="text-center text-white text-xs mt-6 fade-up delay-3">
            Already have an account?
            <a href="<?= BASE_URL ?>/loginuser" class="text-yellow-500 hover:text-amber-400 transition-colors ml-1">Sign
                in</a>
        </p>

    </div>

    <script>
        function handleSubmit() {
            const name = document.getElementById('inp-name')?.value.trim();
            const email = document.getElementById('inp-email')?.value.trim();
            if (!name || !email) return;

            const btn = document.getElementById('submitBtn');
            document.getElementById('btnText').textContent = 'Sending\u2026';
            document.getElementById('btnSpinner').classList.remove('hidden');
            btn.disabled = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;

            [['name', name], ['email', email]].forEach(([k, v]) => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = k; inp.value = v;
                form.appendChild(inp);
            });

            document.body.appendChild(form);
            form.submit();
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Enter') handleSubmit();
        });
    </script>

</body>

</html>