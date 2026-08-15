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
    <title>Login - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: var(--primary-blue);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: var(--radius-lg);
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <h3 class="mb-4 text-primary fw-bold"><?= SITE_NAME ?></h3>
    <h5 class="mb-4">Welcome Back</h5>
    
    <div id="alertBox" class="alert d-none"></div>

    <form id="loginForm">
        <div class="form-floating mb-3 text-start">
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
            <label for="email">Email address</label>
        </div>
        <div class="form-floating mb-4 text-start">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        <button class="btn btn-primary w-100 py-2 mb-3" type="submit" id="loginBtn">Sign in</button>
        
        <p class="text-muted small">Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
    </form>
</div>

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
            btn.disabled = false;
            btn.textContent = 'Sign in';
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = 'An error occurred. Please try again.';
        btn.disabled = false;
        btn.textContent = 'Sign in';
    }
});
</script>
</body>
</html>
