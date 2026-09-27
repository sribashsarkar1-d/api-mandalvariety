<?php
session_start();
$_SESSION['splash_seen'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mandal Variety - Delivery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --mandal-green: #07A158;
            --mandal-green-dark: #058547;
        }
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: #eefdf4;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }
        .splash-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* A subtle gradient mimicking a natural outdoor blur */
            background: linear-gradient(180deg, rgba(238,253,244,1) 0%, rgba(255,255,255,1) 100%);
            z-index: 1;
        }
        
        .content {
            position: relative;
            z-index: 2;
            animation: fadeIn 1s ease-out;
        }

        .logo-wrapper {
            margin-bottom: 20px;
            animation: bounceIn 1.2s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .logo-text {
            font-size: 42px;
            font-weight: 800;
            color: var(--mandal-green-dark);
            line-height: 1.1;
            letter-spacing: -1px;
            margin-top: 15px;
        }

        .app-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--mandal-green);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 15px;
            position: relative;
            display: inline-block;
        }

        .app-title::before, .app-title::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30px;
            height: 1px;
            background: var(--mandal-green);
        }
        .app-title::before { left: -40px; }
        .app-title::after { right: -40px; }

        .tagline {
            font-size: 18px;
            color: #4b5563;
            margin-top: 25px;
            font-weight: 500;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        /* Decorative leaves/blur in background to match image 1 feeling */
        .decoration {
            position: absolute;
            background: var(--mandal-green);
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.15;
            z-index: 1;
        }
        .dec-1 { width: 200px; height: 200px; top: -50px; left: -50px; }
        .dec-2 { width: 300px; height: 300px; bottom: -100px; right: -100px; }
        
        .loader {
            margin-top: 40px;
            width: 30px;
            height: 30px;
            border: 3px solid rgba(7, 161, 88, 0.2);
            border-radius: 50%;
            border-top-color: var(--mandal-green);
            animation: spin 1s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="splash-bg"></div>
<div class="decoration dec-1"></div>
<div class="decoration dec-2"></div>

<div class="content">
    <div class="logo-wrapper">
        <svg width="120" height="120" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 30 L80 30 L70 70 L30 70 Z" stroke="#07A158" stroke-width="6" stroke-linejoin="round"/>
            <path d="M10 20 L20 30" stroke="#07A158" stroke-width="6" stroke-linecap="round"/>
            <circle cx="40" cy="85" r="8" stroke="#07A158" stroke-width="5"/>
            <circle cx="60" cy="85" r="8" stroke="#07A158" stroke-width="5"/>
            <!-- Leaf inside cart -->
            <path d="M35 55 Q50 70 65 55 Q60 40 50 40 Q40 40 35 55 Z" fill="#07A158" opacity="0.8"/>
            <path d="M50 40 C 45 20, 60 10, 70 15 C 60 25, 60 30, 50 40" fill="#07A158"/>
        </svg>
    </div>
    
    <div class="logo-text">Mandal<br>Variety</div>
    
    <div class="app-title">DELIVERY BOY APP</div>
    
    <div class="tagline">Delivering Goodness<br>To Your Home</div>
    
    <div class="loader"></div>
</div>

<script>
    setTimeout(() => {
        window.location.href = 'index.php';
    }, 2500);
</script>

</body>
</html>
