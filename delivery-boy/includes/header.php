<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#FFFFFF">
    <title>Mandal Variety - Delivery Partner</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary-green: #009B55;
            --dark-green: #008044;
            --light-green: #E8F7EF;
            --mandal-green: #009B55;
            --mandal-green-dark: #008044;
            --mandal-green-light: #E8F7EF;
            --text-dark: #111827;
            --text-muted: #64748B;
            --bg-white: #FFFFFF;
            --primary-text: #111827;
            --secondary-text: #64748B;
            --border-color: #E5E7EB;
            --divider-color: #F1F5F9;
            --card-shadow: 0 4px 16px rgba(17, 24, 39, 0.04);
            --card-radius: 18px;
            --pill-radius: 9999px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F8FAFC;
            color: var(--primary-text);
            line-height: 1.45;
            min-height: 100vh;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button, input, select, textarea {
            font-family: inherit;
        }

        /* Mobile First App Shell */
        .app-wrapper {
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
            background-color: var(--bg-white);
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.04);
            padding-bottom: 84px;
        }

        @media (max-width: 480px) {
            .app-wrapper {
                box-shadow: none;
            }
        }

        /* Reusable Common Utilities */
        .page-content {
            padding: 16px 18px 24px;
        }

        /* Card Styles */
        .mv-card {
            background: var(--bg-white);
            border-radius: var(--card-radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            padding: 16px;
            margin-bottom: 14px;
            transition: all 0.2s ease;
        }

        /* Green Button */
        .btn-green {
            background-color: var(--primary-green);
            color: #FFFFFF;
            border: none;
            border-radius: var(--pill-radius);
            padding: 9px 18px;
            font-size: 13.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            text-decoration: none;
        }
        .btn-green:hover {
            background-color: var(--dark-green);
            color: #FFFFFF;
        }
        .btn-green:active {
            transform: scale(0.98);
        }

        /* Outline Button */
        .btn-outline {
            background: transparent;
            color: var(--primary-text);
            border: 1px solid var(--border-color);
            border-radius: var(--pill-radius);
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-outline:hover {
            border-color: var(--secondary-text);
            background: #F8FAFC;
        }

        /* Light Green Status Badge */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--light-green);
            color: var(--primary-green);
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: var(--pill-radius);
            line-height: 1.2;
            white-space: nowrap;
        }
        .badge-status .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--primary-green);
        }

        /* Form Inputs */
        .input-pill {
            width: 100%;
            height: 44px;
            border-radius: var(--pill-radius);
            border: 1px solid var(--border-color);
            background: #FFFFFF;
            padding: 0 16px;
            font-size: 13.5px;
            color: var(--primary-text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-pill:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(0, 155, 85, 0.12);
        }
        .input-pill::placeholder {
            color: #94A3B8;
        }

        /* Toast Feedback */
        .mv-toast {
            position: fixed;
            bottom: 88px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #111827;
            color: #FFFFFF;
            padding: 10px 18px;
            border-radius: var(--pill-radius);
            font-size: 13.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            z-index: 1050;
            opacity: 0;
            pointer-events: none;
            transition: all 0.25s ease;
            max-width: 90%;
            white-space: nowrap;
        }
        .mv-toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }
        .mv-toast.toast-success {
            background: var(--primary-green);
        }
        .mv-toast.toast-error {
            background: #DC2626;
        }
    </style>
</head>
<body>

<div class="app-wrapper">
