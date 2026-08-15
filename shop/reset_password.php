<?php
$email = $_GET['email'] ?? '';
if (empty($email)) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Mandal Variety Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-hero" style="padding: 2rem 1.5rem 4rem 1.5rem;">
        <div class="auth-hero-icon" style="width:60px; height:60px; font-size:2rem;">
            <i class="bi bi-key"></i>
        </div>
        <div class="auth-hero-title">Reset Password</div>
        <div class="auth-hero-subtitle">Create a new password</div>
    </div>

    <div class="auth-card">
        <div class="auth-header text-center mb-4">
            <h5 class="mt-2 fw-bold">Set New Password</h5>
            <p>Enter the 6-digit OTP sent to your email and your new password.</p>
        </div>

        <div id="alertBox" class="alert d-none"></div>

        <form id="resetForm">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="auth-form-group">
                <i class="bi bi-shield-lock auth-form-icon"></i>
                <input type="text" class="auth-form-control" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
            </div>
            
            <div class="auth-form-group">
                <i class="bi bi-lock auth-form-icon"></i>
                <input type="password" class="auth-form-control" id="password" name="password" placeholder="New Password" required>
                <button type="button" class="auth-password-toggle" data-target="password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            
            <button class="auth-btn mt-4" type="submit" id="submitBtn">
                <i class="bi bi-check-circle"></i> Reset Password
            </button>
        </form>
    </div>
</div>

<script src="assets/js/auth-ui.js"></script>

<script>
document.getElementById('resetForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alertBox');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Resetting...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/auth/reset_password.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Reset Password';
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'An error occurred. Please try again.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Reset Password';
    }
});
</script>
</body>
</html>
