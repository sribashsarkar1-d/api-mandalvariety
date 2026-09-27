<?php
require_once 'includes/config.php';
require_once '../admin/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['delivery_id'])) {
    header('Location: index.php');
    exit;
}

// Redirect to splash screen on first visit
if (!isset($_SESSION['splash_seen'])) {
    header('Location: splash.php');
    exit;
}

$error = '';
$step = isset($_SESSION['login_step']) ? (int)$_SESSION['login_step'] : 1;

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
                $error = 'Your account is inactive. Contact admin.';
            } else {
                $otp = sprintf("%06d", mt_rand(1, 999999));
                $_SESSION['login_otp'] = $otp;
                $_SESSION['login_email'] = $email;
                $_SESSION['login_delivery_boy_id'] = $delivery_boy['id'];
                $_SESSION['login_delivery_boy_name'] = $delivery_boy['name'];

                $mailBody = "
                <div style='font-family:Arial,sans-serif;padding:20px;border:1px solid #ddd;border-radius:10px;max-width:500px;margin:0 auto;'>
                    <h2 style='color:#07A158;text-align:center;'>Delivery Partner Login</h2>
                    <p>Dear {$delivery_boy['name']},</p>
                    <p>Your OTP to login to the Delivery Boy App is:</p>
                    <div style='text-align:center;margin:20px 0;'>
                        <strong style='font-size:30px;background:#e8f7f0;padding:10px 20px;border-radius:8px;color:#07A158;letter-spacing:5px;'>{$otp}</strong>
                    </div>
                    <p>If you didn't request this, you can ignore this email.</p>
                </div>";

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sribashsarkarblp@gmail.com'; // Admin's gmail
                    $mail->Password   = 'mjkl wzow ycsq jnps';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('sribashsarkarblp@gmail.com', 'Mandal Variety Delivery');
                    $mail->addAddress($email, $delivery_boy['name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your Login OTP - Mandal Variety Delivery Partner';
                    $mail->Body    = $mailBody;
                    
                    $mail->send();
                    $_SESSION['login_step'] = 2;
                    $step = 2;
                } catch (Exception $ex) {
                    $error = 'Failed to send OTP: ' . $mail->ErrorInfo;
                }
            }
        }
    } elseif (isset($_POST['verify_otp'])) {
        $otp_entered = '';
        for ($i = 1; $i <= 6; $i++) {
            $otp_entered .= $_POST['otp'.$i] ?? '';
        }

        if ($otp_entered === (string)$_SESSION['login_otp']) {
            session_regenerate_id(true);
            $_SESSION['delivery_id'] = $_SESSION['login_delivery_boy_id'];
            $_SESSION['delivery_name'] = $_SESSION['login_delivery_boy_name'];
            
            unset($_SESSION['login_otp'], $_SESSION['login_email'], $_SESSION['login_step'], $_SESSION['login_delivery_boy_id'], $_SESSION['login_delivery_boy_name']);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid OTP. Please try again.';
        }
    } elseif (isset($_POST['change_email'])) {
        unset($_SESSION['login_otp'], $_SESSION['login_email'], $_SESSION['login_step']);
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
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        /* Faint vegetable background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M50 20 Q60 10 70 20 T80 40 Q70 60 50 60 Q30 60 20 40 T30 20 Q40 10 50 20 Z" fill="none" stroke="%2307A158" stroke-width="0.3" stroke-opacity="0.1"/></svg>');
            background-size: 150px;
            opacity: 0.6;
            z-index: -1;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 30px;
        }
        .logo-container i {
            font-size: 80px;
            color: var(--mandal-green);
        }
        .logo-text {
            font-size: 32px;
            font-weight: 800;
            color: var(--mandal-green-dark);
            line-height: 1;
            margin-top: 10px;
            letter-spacing: -1px;
        }
        .logo-text-sub {
            font-size: 32px;
            font-weight: 800;
            color: var(--mandal-green-dark);
            line-height: 1;
            letter-spacing: -1px;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 15px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }
        .form-control-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 16px;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s;
            text-align: center;
        }
        .form-control-custom:focus {
            outline: none;
            border-color: var(--mandal-green);
            box-shadow: 0 0 0 4px rgba(7, 161, 88, 0.1);
        }
        .btn-green {
            background: var(--mandal-green);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(7, 161, 88, 0.2);
            transition: transform 0.2s;
        }
        .btn-green:active {
            transform: scale(0.98);
        }
        .terms {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 30px;
        }
        .terms a {
            color: #2563eb;
            text-decoration: none;
        }
        
        /* OTP Boxes */
        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 30px;
        }
        .otp-box {
            width: 48px;
            height: 56px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            color: var(--text-dark);
            background: #ffffff;
            transition: all 0.2s;
        }
        .otp-box:focus {
            outline: none;
            border-color: var(--mandal-green);
            box-shadow: 0 0 0 3px rgba(7, 161, 88, 0.15);
        }
        .resend-text {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        .resend-text span {
            color: var(--text-dark);
            font-weight: 600;
        }
        .change-email {
            background: none;
            border: none;
            color: var(--mandal-green);
            font-weight: 600;
            font-size: 14px;
            margin-top: 15px;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <?php if ($step === 1): ?>
        <div class="logo-container">
            <!-- Simulated Logo -->
            <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 30 L80 30 L70 70 L30 70 Z" stroke="#07A158" stroke-width="6" stroke-linejoin="round"/>
                <path d="M10 20 L20 30" stroke="#07A158" stroke-width="6" stroke-linecap="round"/>
                <circle cx="40" cy="85" r="8" stroke="#07A158" stroke-width="5"/>
                <circle cx="60" cy="85" r="8" stroke="#07A158" stroke-width="5"/>
                <path d="M35 55 Q50 70 65 55 Q60 40 50 40 Q40 40 35 55 Z" fill="#07A158" opacity="0.8"/>
                <path d="M50 40 C 45 20, 60 10, 70 15 C 60 25, 60 30, 50 40" fill="#07A158"/>
            </svg>
            <div class="logo-text">Mandal</div>
            <div class="logo-text-sub">Variety</div>
        </div>

        <h1 class="title">Delivery Partner Login</h1>
        <p class="subtitle">Enter your email address to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="font-size:14px;border-radius:10px;padding:10px;margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" class="form-control-custom" placeholder="partner@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <button type="submit" name="send_otp" class="btn-green">Send OTP</button>
        </form>

        <div class="terms">
            By continuing, you agree to our<br>
            <a href="#">Terms & Conditions</a>
        </div>
    <?php else: ?>
        <h1 class="title">OTP Verification</h1>
        <p class="subtitle">We have sent a 6-digit OTP to<br><strong style="color:var(--text-dark);"><?= htmlspecialchars($_SESSION['login_email']) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="font-size:14px;border-radius:10px;padding:10px;margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="otpForm">
            <div class="otp-container">
                <input type="text" name="otp1" class="otp-box" maxlength="1" pattern="\d" required autofocus>
                <input type="text" name="otp2" class="otp-box" maxlength="1" pattern="\d" required>
                <input type="text" name="otp3" class="otp-box" maxlength="1" pattern="\d" required>
                <input type="text" name="otp4" class="otp-box" maxlength="1" pattern="\d" required>
                <input type="text" name="otp5" class="otp-box" maxlength="1" pattern="\d" required>
                <input type="text" name="otp6" class="otp-box" maxlength="1" pattern="\d" required>
            </div>
            
            <div class="resend-text">
                Resend OTP in <span id="timer">00:30</span>
            </div>

            <button type="submit" name="verify_otp" class="btn-green">Verify</button>
        </form>
        
        <form method="POST">
            <button type="submit" name="change_email" class="change-email">Change Email</button>
        </form>
        
        <script>
            // OTP Input focus logic
            const inputs = document.querySelectorAll('.otp-box');
            inputs.forEach((input, index) => {
                input.addEventListener('keyup', (e) => {
                    if (e.key >= 0 && e.key <= 9) {
                        if (index < inputs.length - 1) inputs[index + 1].focus();
                    } else if (e.key === 'Backspace') {
                        if (index > 0) inputs[index - 1].focus();
                    }
                });
                
                // Allow paste
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = e.clipboardData.getData('text').slice(0, 6);
                    if (/^\d+$/.test(text)) {
                        text.split('').forEach((char, i) => {
                            if (inputs[i]) {
                                inputs[i].value = char;
                                if (i < inputs.length - 1) inputs[i + 1].focus();
                            }
                        });
                    }
                });
            });

            // Timer
            let timeLeft = 30;
            const timerEl = document.getElementById('timer');
            const timerInterval = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerEl.innerHTML = '<a href="#" style="color:#07A158;text-decoration:none;" onclick="location.reload()">Resend Now</a>';
                } else {
                    timerEl.innerText = '00:' + (timeLeft < 10 ? '0' : '') + timeLeft;
                }
            }, 1000);
        </script>
    <?php endif; ?>
</div>

</body>
</html>
