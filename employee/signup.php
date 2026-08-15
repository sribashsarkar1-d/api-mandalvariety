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
    <title>Create Account - Mandal Variety Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-hero" style="padding: 2rem 1.5rem 4rem 1.5rem;">
        <a href="login.php" class="auth-back-btn"><i class="bi bi-arrow-left"></i></a>
        <div class="auth-hero-icon" style="width:60px; height:60px; font-size:2rem;">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="auth-hero-title">Create Account</div>
        <div class="auth-hero-subtitle">Sign up to get started</div>
    </div>

    <div class="auth-card">
        <div id="alertBox" class="alert d-none"></div>

        <form id="signupForm">
            <div id="step1">
                <div class="auth-tabs">
                    <div class="auth-tab active" data-role="admin">
                        <i class="bi bi-person-badge"></i> Admin Sign Up
                    </div>
                    <div class="auth-tab" data-role="employee">
                        <i class="bi bi-shop"></i> Employee Sign Up
                    </div>
                </div>
                <input type="hidden" id="role" name="role" value="admin">

                <div class="auth-form-group">
                    <i class="bi bi-person auth-form-icon"></i>
                    <input type="text" class="auth-form-control" id="name" name="name" placeholder="Full Name" required>
                </div>
                
                <div class="auth-form-group">
                    <i class="bi bi-envelope auth-form-icon"></i>
                    <input type="email" class="auth-form-control" id="email" name="email" placeholder="Email Address" required>
                </div>
                
                <div class="auth-form-group">
                    <i class="bi bi-lock auth-form-icon"></i>
                    <input type="password" class="auth-form-control" id="password" name="password" placeholder="Password" autocomplete="new-password" required>
                    <button type="button" class="auth-password-toggle" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                
                <button class="auth-btn mt-4" type="submit" id="signupBtn">
                    <i class="bi bi-person-plus"></i> Sign Up
                </button>
                
                <div class="auth-bottom-text mt-4">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
            
            <div id="step2" class="d-none">
                <div class="auth-header text-center mb-4">
                    <i class="bi bi-envelope-check text-primary" style="font-size:3rem;"></i>
                    <h5 class="mt-2 fw-bold">Verify Your Email</h5>
                    <p>Please enter the 6-digit OTP sent to your email.</p>
                </div>

                <div class="auth-form-group">
                    <i class="bi bi-shield-lock auth-form-icon"></i>
                    <input type="text" class="auth-form-control" id="otp" name="otp" placeholder="Enter OTP" maxlength="6">
                </div>
                
                <button class="auth-btn mb-3" type="button" id="verifyBtn">
                    <i class="bi bi-check2-circle"></i> Verify OTP & Sign up
                </button>
                <button class="btn btn-light w-100 py-2 text-muted fw-bold border" type="button" id="backBtn">
                    Back
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/auth-ui.js"></script>

<script>
document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('signupBtn');
    const alertBox = document.getElementById('alertBox');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending OTP...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/auth/send_signup_otp.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            
            // Show step 2
            document.getElementById('step1').classList.add('d-none');
            document.getElementById('step2').classList.remove('d-none');
            
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'An error occurred. Please try again.';
        alertBox.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-person-plus"></i> Sign Up';
    }
});

document.getElementById('backBtn').addEventListener('click', function() {
    document.getElementById('step2').classList.add('d-none');
    document.getElementById('step1').classList.remove('d-none');
    document.getElementById('alertBox').classList.add('d-none');
});

document.getElementById('verifyBtn').addEventListener('click', async function() {
    const btn = this;
    const alertBox = document.getElementById('alertBox');
    const otp = document.getElementById('otp').value;
    
    if (!otp) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'Please enter OTP';
        alertBox.classList.remove('d-none');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
    
    const formData = new FormData();
    formData.append('otp', otp);
    
    try {
        const response = await fetch('api/auth/signup.php', {
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
            }, 1500);
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = result.message;
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Verify OTP & Sign up';
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'An error occurred. Please try again.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Verify OTP & Sign up';
    }
});
</script>
</body>
</html>
