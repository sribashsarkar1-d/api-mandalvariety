<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Mandal Variety Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-hero" style="padding: 2rem 1.5rem 4rem 1.5rem;">
        <a href="login.php" class="auth-back-btn"><i class="bi bi-arrow-left"></i></a>
        <div class="auth-hero-icon" style="width:60px; height:60px; font-size:2rem;">
            <i class="bi bi-shield-lock"></i>
        </div>
        <div class="auth-hero-title">Forgot Password</div>
        <div class="auth-hero-subtitle">Reset your account access</div>
    </div>

    <div class="auth-card">
        <div class="auth-header text-center mb-4">
            <h5 class="mt-2 fw-bold">Enter Email Address</h5>
            <p>We'll send you an OTP to reset your password.</p>
        </div>

        <div id="alertBox" class="alert d-none"></div>

        <form id="forgotForm">
            <div class="auth-form-group">
                <i class="bi bi-envelope auth-form-icon"></i>
                <input type="email" class="auth-form-control" id="email" name="email" placeholder="Email Address" required>
            </div>
            
            <button class="auth-btn mt-4" type="submit" id="submitBtn">
                <i class="bi bi-send"></i> Send OTP
            </button>
            
            <div class="auth-bottom-text mt-4">
                Remember your password? <a href="login.php">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alertBox');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/auth/forgot_password.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                window.location.href = 'reset_password.php?email=' + encodeURIComponent(document.getElementById('email').value);
            }, 1500);
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i> Send OTP';
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'An error occurred. Please try again.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send"></i> Send OTP';
    }
});
</script>
</body>
</html>
