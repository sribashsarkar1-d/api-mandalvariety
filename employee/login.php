<?php
require_once 'config/constants.php';
require_once 'config/auth.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mandal Variety Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-hero">
        <div class="auth-hero-icon">
            <i class="bi bi-cart3"></i>
        </div>
        <div class="auth-hero-title">Mandal Variety Store</div>
        <div class="auth-hero-subtitle">Store Management System</div>
    </div>

    <div class="auth-card">
        <div class="auth-header">
            <h4>Welcome Back! </h4>
            <p>Sign in to continue to your account</p>
        </div>

        <div id="alertBox" class="alert d-none"></div>

        <div class="auth-tabs">
            <div class="auth-tab active" data-role="admin">
                <i class="bi bi-person-badge"></i> Admin Login
            </div>
            <div class="auth-tab" data-role="employee">
                <i class="bi bi-shop"></i> Employee Login
            </div>
        </div>

        <form id="loginForm">
            <!-- Hidden role input for backend logic, if needed by backend, though existing login might just check email -->
            <!-- Wait, the existing login.php didn't have a role select, it just took email & password -->
            <!-- I'll keep the UI tabs but they won't affect the form data since existing login didn't send role -->
            
            <div class="auth-form-group">
                <i class="bi bi-person auth-form-icon"></i>
                <input type="email" class="auth-form-control" id="email" name="email" placeholder="Email or Phone Number" autocomplete="username" required>
            </div>
            
            <div class="auth-form-group">
                <i class="bi bi-lock auth-form-icon"></i>
                <input type="password" class="auth-form-control" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
                <button type="button" class="auth-password-toggle" data-target="password">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            
            <div class="auth-footer-link">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            
            <button class="auth-btn" type="submit" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
            
            <div class="auth-security-card">
                <div class="auth-security-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="auth-security-text">
                    <h6>Secure & Trusted</h6>
                    <p>Your data is protected with enterprise-grade security</p>
                </div>
            </div>
            
            <div class="auth-bottom-text">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/auth-ui.js"></script>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const alertBox = document.getElementById('alertBox');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/auth/login.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.data.redirect;
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'An error occurred. Please try again.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
    }
});
</script>
</body>
</html>
