<?php
session_start();
$_SESSION['splash_shown'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mandal Variety - Delivery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --mandal-green: #07A158;
            --mandal-green-dark: #058547;
            --mandal-light: #e8f7f0;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--mandal-light);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* Dynamic Background */
        .splash-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 0%, #ffffff 0%, var(--mandal-light) 100%);
            z-index: 1;
            animation: bgPulse 3s ease-in-out infinite alternate;
        }

        @keyframes bgPulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.1); opacity: 1; }
        }

        .decoration {
            position: absolute;
            background: var(--mandal-green);
            border-radius: 50%;
            filter: blur(50px);
            opacity: 0.1;
            z-index: 1;
            animation: floatShape 6s ease-in-out infinite alternate;
        }
        .dec-1 { width: 300px; height: 300px; top: -100px; left: -100px; animation-delay: 0s; }
        .dec-2 { width: 400px; height: 400px; bottom: -150px; right: -150px; animation-delay: 1s; }

        @keyframes floatShape {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 30px) scale(1.1); }
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Logo Animation */
        .logo-box {
            width: 110px;
            height: 110px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(7, 161, 88, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            transform: scale(0);
            opacity: 0;
            animation: popIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            animation-delay: 0.2s;
        }

        @keyframes popIn {
            0% { transform: scale(0.5) translateY(20px); opacity: 0; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }

        /* SVG internal animations */
        .cart-path {
            stroke-dasharray: 200;
            stroke-dashoffset: 200;
            animation: drawPath 1s ease-out forwards;
            animation-delay: 0.5s;
        }
        .leaf-path {
            transform: scale(0);
            transform-origin: center;
            animation: leafGrow 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            animation-delay: 1s;
        }
        .wheel-path {
            opacity: 0;
            transform: translateY(-10px);
            animation: wheelDrop 0.5s bounce forwards;
            animation-delay: 0.8s;
        }

        @keyframes drawPath {
            to { stroke-dashoffset: 0; }
        }
        @keyframes leafGrow {
            0% { transform: scale(0) rotate(-20deg); opacity: 0; }
            100% { transform: scale(1) rotate(0); opacity: 0.9; }
        }
        @keyframes wheelDrop {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Text Animations */
        .text-line {
            overflow: hidden;
            display: block;
        }
        
        .logo-text {
            font-size: 38px;
            font-weight: 800;
            color: var(--mandal-green-dark);
            line-height: 1.1;
            letter-spacing: -1px;
            margin-bottom: 15px;
        }

        .text-mandal, .text-variety {
            display: inline-block;
            transform: translateY(100%);
            opacity: 0;
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .text-mandal { animation-delay: 0.7s; }
        .text-variety { animation-delay: 0.8s; }

        @keyframes slideUpFade {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        /* App Title Ribbon */
        .app-title-wrapper {
            position: relative;
            margin-bottom: 25px;
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
            animation-delay: 1.1s;
        }

        .app-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--mandal-green);
            letter-spacing: 4px;
            text-transform: uppercase;
            position: relative;
            display: inline-block;
            padding: 0 15px;
        }

        .app-title::before, .app-title::after {
            content: '';
            position: absolute;
            top: 50%;
            height: 1.5px;
            background: var(--mandal-green);
            opacity: 0.4;
            width: 0;
            animation: lineExpand 0.6s ease forwards;
            animation-delay: 1.3s;
        }
        .app-title::before { left: -30px; }
        .app-title::after { right: -30px; }

        @keyframes lineExpand {
            to { width: 30px; }
        }

        /* Tagline */
        .tagline {
            font-size: 16px;
            color: #475569;
            font-weight: 500;
            opacity: 0;
            transform: translateY(10px);
            animation: gentleFadeUp 0.8s ease forwards;
            animation-delay: 1.4s;
        }

        @keyframes gentleFadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Professional Loading Dots */
        .loading-dots {
            display: flex;
            gap: 8px;
            margin-top: 40px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            animation-delay: 1.8s;
        }

        .dot {
            width: 10px;
            height: 10px;
            background: var(--mandal-green);
            border-radius: 50%;
            animation: dotBounce 1.4s infinite ease-in-out both;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes dotBounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="splash-bg"></div>
<div class="decoration dec-1"></div>
<div class="decoration dec-2"></div>

<div class="content-wrapper">
    <!-- Premium App Icon Style Box -->
    <div class="logo-box">
        <svg width="70" height="70" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path class="cart-path" d="M15 25 L85 25 L75 65 L25 65 Z" stroke="#07A158" stroke-width="7" stroke-linejoin="round"/>
            <path class="cart-path" d="M5 15 L15 25" stroke="#07A158" stroke-width="7" stroke-linecap="round"/>
            <circle class="wheel-path" cx="35" cy="82" r="8" fill="#058547"/>
            <circle class="wheel-path" cx="65" cy="82" r="8" fill="#058547"/>
            <!-- Center Leaf -->
            <path class="leaf-path" d="M35 50 Q50 70 65 50 Q60 30 50 30 Q40 30 35 50 Z" fill="#07A158"/>
            <path class="leaf-path" d="M50 30 C 45 10, 60 5, 70 10 C 60 20, 60 25, 50 30" fill="#07A158"/>
        </svg>
    </div>
    
    <div class="logo-text">
        <div class="text-line"><span class="text-mandal">Mandal</span></div>
        <div class="text-line"><span class="text-variety">Variety</span></div>
    </div>
    
    <div class="app-title-wrapper">
        <div class="app-title">Delivery Partner</div>
    </div>
    
    <div class="tagline">Delivering Goodness<br>To Your Home</div>
    
    <div class="loading-dots">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
</div>

<script>
    // Smooth transition out before redirect
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.4s ease';
        document.body.style.opacity = '0';
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 400);
    }, 2800); // 2.8s total duration for all animations to play out
</script>

</body>
</html>
