<?php
require_once 'includes/config.php';
require_once '../admin/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['delivery_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['splash_shown'])) {
    $_SESSION['splash_shown'] = true;
    header("Location: splash.php");
    exit;
}

$error = '';
$success = '';
$step = isset($_SESSION['login_step']) ? (int)$_SESSION['login_step'] : 1;

/**
 * Helper to send OTP email
 */
function sendDeliveryBoyOtpEmail($email, $name, $otp) {
    $mailBody = "
    <div style='font-family:Arial,sans-serif;padding:24px;border:1px solid #e2e8f0;border-radius:12px;max-width:500px;margin:0 auto;background:#ffffff;'>
        <div style='text-align:center;margin-bottom:20px;'>
            <h2 style='color:#07A158;margin:0;font-size:24px;'>Mandal Variety Delivery</h2>
            <p style='color:#64748b;font-size:14px;margin-top:4px;'>Delivery Partner Verification</p>
        </div>
        <p style='color:#1e293b;font-size:15px;'>Dear <strong>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p style='color:#475569;font-size:14px;line-height:1.5;'>Your 6-digit verification code to log in to the Delivery Boy Portal is:</p>
        <div style='text-align:center;margin:28px 0;'>
            <span style='font-size:34px;background:#e8f7f0;padding:12px 28px;border-radius:10px;color:#07A158;letter-spacing:8px;font-weight:800;border:1px dashed #07A158;display:inline-block;'>{$otp}</span>
        </div>
        <p style='color:#64748b;font-size:13px;'>This code is valid for 10 minutes. If you did not request this login, please ignore this email.</p>
        <hr style='border:none;border-top:1px solid #f1f5f9;margin:20px 0;'>
        <p style='color:#94a3b8;font-size:12px;text-align:center;margin:0;'>&copy; " . date('Y') . " Mandal Variety. All rights reserved.</p>
    </div>";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sribashsarkarblp@gmail.com';
        $mail->Password   = 'mjkl wzow ycsq jnps';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('sribashsarkarblp@gmail.com', 'Mandal Variety Delivery');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP - Mandal Variety Delivery Partner';
        $mail->Body    = $mailBody;
        $mail->AltBody = "Your Delivery Partner Login OTP is: {$otp}";
        
        $mail->send();
        return true;
    } catch (Exception $ex) {
        return false;
    }
}

// Handle AJAX Resend Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_resend_otp'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['login_email']) || !isset($_SESSION['login_delivery_boy_id'])) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please enter email again.']);
        exit;
    }
    
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $_SESSION['login_otp'] = $otp;
    $sent = sendDeliveryBoyOtpEmail($_SESSION['login_email'], $_SESSION['login_delivery_boy_name'], $otp);
    
    if ($sent) {
        echo json_encode(['success' => true, 'message' => 'A new 6-digit OTP has been sent to your email.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please check your connection.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_otp'])) {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $error = 'Please enter your email address.';
        } else {
            $stmt = $conn->prepare("SELECT id, name, is_active FROM delivery_boys WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $delivery_boy = $stmt->fetch();

            if (!$delivery_boy) {
                $error = 'No delivery partner found with this email.';
            } elseif ((int)$delivery_boy['is_active'] !== 1) {
                $error = 'Your account is inactive. Please contact the administrator.';
            } else {
                $otp = sprintf("%06d", mt_rand(1, 999999));
                $_SESSION['login_otp'] = $otp;
                $_SESSION['login_email'] = $email;
                $_SESSION['login_delivery_boy_id'] = $delivery_boy['id'];
                $_SESSION['login_delivery_boy_name'] = $delivery_boy['name'];

                $sent = sendDeliveryBoyOtpEmail($email, $delivery_boy['name'], $otp);
                if ($sent) {
                    $_SESSION['login_step'] = 2;
                    $step = 2;
                } else {
                    $error = 'Failed to send OTP email. Please try again.';
                }
            }
        }
    } elseif (isset($_POST['resend_otp'])) {
        if (!isset($_SESSION['login_email']) || !isset($_SESSION['login_delivery_boy_id'])) {
            $step = 1;
        } else {
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $_SESSION['login_otp'] = $otp;
            $sent = sendDeliveryBoyOtpEmail($_SESSION['login_email'], $_SESSION['login_delivery_boy_name'], $otp);
            if ($sent) {
                $success = 'A new 6-digit OTP has been sent to your email.';
            } else {
                $error = 'Failed to resend OTP. Please try again later.';
            }
            $step = 2;
        }
    } elseif (isset($_POST['verify_otp'])) {
        // Collect OTP either from individual otp1..otp6 or combined otp
        $otp_entered = '';
        if (!empty($_POST['full_otp'])) {
            $otp_entered = trim($_POST['full_otp']);
        } else {
            for ($i = 1; $i <= 6; $i++) {
                $otp_entered .= trim($_POST['otp'.$i] ?? '');
            }
        }

        if (strlen($otp_entered) !== 6 || !ctype_digit($otp_entered)) {
            $error = 'Please enter a complete 6-digit numeric OTP.';
        } elseif (!isset($_SESSION['login_otp']) || $otp_entered !== (string)$_SESSION['login_otp']) {
            $error = 'Invalid OTP. Please check the code and try again.';
        } else {
            session_regenerate_id(true);
            $_SESSION['delivery_id'] = $_SESSION['login_delivery_boy_id'];
            $_SESSION['delivery_name'] = $_SESSION['login_delivery_boy_name'];
            
            unset($_SESSION['login_otp'], $_SESSION['login_email'], $_SESSION['login_step'], $_SESSION['login_delivery_boy_id'], $_SESSION['login_delivery_boy_name']);

            header('Location: index.php');
            exit;
        }
    } elseif (isset($_POST['change_email'])) {
        unset($_SESSION['login_otp'], $_SESSION['login_email'], $_SESSION['login_step'], $_SESSION['login_delivery_boy_id'], $_SESSION['login_delivery_boy_name']);
        $step = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Partner Login - Mandal Variety</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --mandal-green: #07A158;
            --mandal-green-dark: #058547;
            --mandal-green-light: #e8f7f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }
        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        /* Faint subtle background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(#07A158 0.75px, transparent 0.75px);
            background-size: 24px 24px;
            opacity: 0.12;
            z-index: 0;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 36px 28px;
            text-align: center;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            z-index: 1;
            margin: 20px;
        }
        .logo-container {
            margin-bottom: 24px;
        }
        .logo-icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: var(--mandal-green-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: var(--mandal-green);
            font-size: 32px;
            box-shadow: 0 4px 14px rgba(7, 161, 88, 0.15);
        }
        .logo-text {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.1;
            letter-spacing: -0.5px;
        }
        .logo-text span {
            color: var(--mandal-green);
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .form-control-custom {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            font-size: 15px;
            width: 100%;
            box-shadow: none;
            transition: all 0.2s ease;
            text-align: center;
            color: var(--text-dark);
            font-weight: 500;
        }
        .form-control-custom:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--mandal-green);
            box-shadow: 0 0 0 4px rgba(7, 161, 88, 0.15);
        }
        .btn-green {
            background: linear-gradient(135deg, #07A158 0%, #058547 100%);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            width: 100%;
            margin-top: 18px;
            box-shadow: 0 6px 20px rgba(7, 161, 88, 0.25);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .btn-green:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            color: white;
        }
        .btn-green:active {
            transform: translateY(1px);
        }
        .btn-green:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .terms {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 24px;
        }
        .terms a {
            color: var(--mandal-green);
            text-decoration: none;
            font-weight: 600;
        }

        /* Six-Digit OTP Input Grid */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0 24px 0;
            position: relative;
        }
        .otp-box {
            width: 48px;
            height: 56px;
            border: 2px solid #cbd5e1;
            border-radius: 14px;
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            color: var(--text-dark);
            background: #ffffff;
            transition: all 0.2s ease;
            caret-color: var(--mandal-green);
            padding: 0;
            -webkit-user-select: text;
            user-select: text;
        }
        @media (max-width: 380px) {
            .otp-container {
                gap: 6px;
            }
            .otp-box {
                width: 42px;
                height: 52px;
                font-size: 20px;
                border-radius: 10px;
            }
        }
        .otp-box:focus {
            outline: none;
            border-color: var(--mandal-green);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(7, 161, 88, 0.2);
            transform: translateY(-2px);
        }
        .otp-box.filled {
            border-color: var(--mandal-green);
            background: #f0fdf4;
            color: var(--mandal-green-dark);
        }
        .resend-text {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        .resend-text span {
            color: var(--text-dark);
            font-weight: 700;
        }
        .resend-btn {
            background: none;
            border: none;
            color: var(--mandal-green);
            font-weight: 700;
            font-size: 14px;
            padding: 0;
            cursor: pointer;
            text-decoration: underline;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .resend-btn:hover {
            color: var(--mandal-green-dark);
        }
        .change-email {
            background: none;
            border: none;
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
            margin-top: 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .change-email:hover {
            color: var(--text-dark);
        }
        .alert-custom {
            font-size: 13px;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="login-card">
    <?php if ($step === 1): ?>
        <div class="logo-container">
            <div class="logo-icon-wrap">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div class="logo-text">Mandal <span>Variety</span></div>
        </div>

        <h1 class="title">Delivery Partner Login</h1>
        <p class="subtitle">Enter your registered email address to receive a 6-digit login verification code</p>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom border-0 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-danger fs-6"></i>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="emailForm" onsubmit="handleEmailSubmit(this)">
            <input type="email" name="email" class="form-control-custom" placeholder="name@mandal-variety.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus autocomplete="email">
            <button type="submit" name="send_otp" id="sendOtpBtn" class="btn-green">
                <span>Send OTP Code</span>
                <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="terms">
            By logging in, you agree to Mandal Variety<br>
            <a href="#">Partner Terms of Service</a> &bull; <a href="#">Privacy Policy</a>
        </div>
        <script>
            function handleEmailSubmit(form) {
                const btn = document.getElementById('sendOtpBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending OTP...';
            }
        </script>
    <?php else: ?>
        <div class="logo-container" style="margin-bottom: 16px;">
            <div class="logo-icon-wrap" style="width: 60px; height: 60px; font-size: 26px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

        <h1 class="title">OTP Verification</h1>
        <p class="subtitle">
            Enter the 6-digit code sent to<br>
            <strong style="color:var(--text-dark);"><?= htmlspecialchars($_SESSION['login_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom border-0 shadow-sm" id="errorAlert">
                <i class="fa-solid fa-circle-exclamation text-danger fs-6"></i>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <div class="alert alert-success alert-custom border-0 shadow-sm d-none" id="successAlert">
            <i class="fa-solid fa-circle-check text-success fs-6"></i>
            <span id="successMsg"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const el = document.getElementById('successAlert');
                    el.classList.remove('d-none');
                });
            </script>
        <?php endif; ?>

        <form method="POST" id="otpForm" onsubmit="return handleOtpSubmit(event)">
            <!-- Hidden field for combined OTP if needed -->
            <input type="hidden" name="full_otp" id="fullOtpInput">

            <div class="otp-container" id="otpContainer">
                <input type="tel" name="otp1" id="otp-0" class="otp-box" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" required autofocus>
                <input type="tel" name="otp2" id="otp-1" class="otp-box" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" required>
                <input type="tel" name="otp3" id="otp-2" class="otp-box" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" required>
                <input type="tel" name="otp4" id="otp-3" class="otp-box" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" required>
                <input type="tel" name="otp5" id="otp-4" class="otp-box" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" required>
                <input type="tel" name="otp6" id="otp-5" class="otp-box" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="off" required>
            </div>
            
            <div class="resend-text">
                <span id="timerWrapper">Resend OTP in <span id="timer">00:30</span></span>
                <button type="button" class="resend-btn d-none" id="btnResend" onclick="resendOtpAjax()">
                    <i class="fa-solid fa-rotate-right"></i> Resend OTP
                </button>
            </div>

            <button type="submit" name="verify_otp" id="verifyBtn" class="btn-green">
                <i class="fa-solid fa-circle-check me-2"></i>
                <span id="btnVerifyText">Verify & Continue</span>
            </button>
        </form>
        
        <form method="POST" id="changeEmailForm">
            <input type="hidden" name="change_email" value="1">
            <button type="submit" class="change-email">
                <i class="fa-solid fa-pen-to-square"></i> Change Email Address
            </button>
        </form>
        
        <script>
            const inputs = Array.from(document.querySelectorAll('.otp-box'));
            const fullOtpInput = document.getElementById('fullOtpInput');
            const verifyBtn = document.getElementById('verifyBtn');
            const btnVerifyText = document.getElementById('btnVerifyText');
            let isSubmitting = false;

            // Auto-focus first input on load
            window.addEventListener('DOMContentLoaded', () => {
                if (inputs.length > 0) {
                    setTimeout(() => {
                        inputs[0].focus();
                        inputs[0].select();
                    }, 120);
                }
            });

            // Update box styling and get current OTP string
            function getOtpValue() {
                return inputs.map(i => i.value.trim()).join('');
            }

            function syncBoxesState() {
                inputs.forEach(input => {
                    if (input.value.trim()) {
                        input.classList.add('filled');
                    } else {
                        input.classList.remove('filled');
                    }
                });
                const otp = getOtpValue();
                if (fullOtpInput) fullOtpInput.value = otp;
            }

            // Trigger submit when 6 digits are complete
            function checkAndSubmitIfComplete() {
                const otp = getOtpValue();
                if (otp.length === 6 && /^\d{6}$/.test(otp) && !isSubmitting) {
                    inputs[5].blur(); // dismiss keyboard on Android WebView
                    document.getElementById('otpForm').requestSubmit();
                }
            }

            // Distribute a string of digits across inputs starting at a given index
            function distributeDigits(str, startIndex = 0) {
                const digits = str.replace(/\D/g, '');
                if (!digits) return;
                
                let writeIndex = startIndex;
                for (let i = 0; i < digits.length && writeIndex < inputs.length; i++) {
                    inputs[writeIndex].value = digits[i];
                    writeIndex++;
                }
                syncBoxesState();

                // Focus the next empty box or the last box
                const nextEmpty = inputs.findIndex(input => !input.value.trim());
                if (nextEmpty !== -1) {
                    inputs[nextEmpty].focus();
                    inputs[nextEmpty].select();
                } else {
                    inputs[inputs.length - 1].focus();
                    inputs[inputs.length - 1].select();
                    checkAndSubmitIfComplete();
                }
            }

            // Input handlers for each box
            inputs.forEach((input, index) => {
                // When focused, auto-select text so typing overwrites
                input.addEventListener('focus', () => {
                    input.select();
                });

                // Listen to 'input' event (handles mobile virtual keyboard & SMS autofill reliably)
                input.addEventListener('input', (e) => {
                    const rawVal = input.value;

                    // If multiple characters (autofill, paste, fast typing)
                    if (rawVal.length > 1) {
                        distributeDigits(rawVal, index);
                        return;
                    }

                    // Single character handling: accept only digits 0-9
                    const cleanVal = rawVal.replace(/\D/g, '');
                    input.value = cleanVal;
                    syncBoxesState();

                    if (cleanVal) {
                        // Move to next field
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                            inputs[index + 1].select();
                        } else {
                            // Last field filled
                            checkAndSubmitIfComplete();
                        }
                    }
                });

                // Handle backspace, delete, and arrow keys
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' || e.keyCode === 8) {
                        if (!input.value) {
                            // If current box is already empty, move to previous box and clear it
                            if (index > 0) {
                                e.preventDefault();
                                inputs[index - 1].value = '';
                                syncBoxesState();
                                inputs[index - 1].focus();
                            }
                        } else {
                            // Current box has a digit, let native backspace clear it
                            // After clearing, keep focus on this box
                        }
                    } else if (e.key === 'ArrowLeft' && index > 0) {
                        e.preventDefault();
                        inputs[index - 1].focus();
                        inputs[index - 1].select();
                    } else if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                        e.preventDefault();
                        inputs[index + 1].focus();
                        inputs[index + 1].select();
                    }
                });

                // Clipboard paste handling on individual box
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasteText = (e.clipboardData || window.clipboardData).getData('text');
                    distributeDigits(pasteText, index === 0 ? 0 : index);
                });
            });

            // Handle submission and prevent duplicate submissions
            function handleOtpSubmit(e) {
                const otp = getOtpValue();
                if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
                    e.preventDefault();
                    alert('Please enter a valid 6-digit OTP code.');
                    const emptyIndex = inputs.findIndex(i => !i.value.trim());
                    if (emptyIndex !== -1) {
                        inputs[emptyIndex].focus();
                    }
                    return false;
                }

                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                isSubmitting = true;
                if (fullOtpInput) fullOtpInput.value = otp;
                verifyBtn.disabled = true;
                btnVerifyText.innerText = 'Verifying...';
                verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...';
                return true;
            }

            // 30s Countdown timer & AJAX Resend
            let timeLeft = 30;
            let timerInterval = null;
            const timerEl = document.getElementById('timer');
            const timerWrapper = document.getElementById('timerWrapper');
            const btnResend = document.getElementById('btnResend');
            const successAlert = document.getElementById('successAlert');
            const successMsg = document.getElementById('successMsg');
            const errorAlert = document.getElementById('errorAlert');

            function startTimer() {
                timeLeft = 30;
                timerWrapper.classList.remove('d-none');
                btnResend.classList.add('d-none');
                btnResend.disabled = false;
                btnResend.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Resend OTP';

                clearInterval(timerInterval);
                timerInterval = setInterval(() => {
                    timeLeft--;
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        timerWrapper.classList.add('d-none');
                        btnResend.classList.remove('d-none');
                    } else {
                        timerEl.innerText = '00:' + (timeLeft < 10 ? '0' : '') + timeLeft;
                    }
                }, 1000);
            }

            startTimer();

            function resendOtpAjax() {
                btnResend.disabled = true;
                btnResend.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Sending...';

                const formData = new FormData();
                formData.append('ajax_resend_otp', '1');

                fetch('login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Clear inputs
                        inputs.forEach(i => { i.value = ''; i.classList.remove('filled'); });
                        inputs[0].focus();
                        syncBoxesState();

                        // Show success alert
                        if (errorAlert) errorAlert.remove();
                        successMsg.innerText = data.message || 'A new 6-digit OTP has been sent.';
                        successAlert.classList.remove('d-none');

                        // Restart timer
                        startTimer();
                    } else {
                        alert(data.message || 'Failed to resend OTP. Please try again.');
                        btnResend.disabled = false;
                        btnResend.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Resend OTP';
                    }
                })
                .catch(() => {
                    alert('Network error while resending OTP. Please try again.');
                    btnResend.disabled = false;
                    btnResend.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Resend OTP';
                });
            }
        </script>
    <?php endif; ?>
</div>

</body>
</html>
