<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Partner Portal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --mandal-green: #07A158;
            --mandal-green-light: #e8f7f0;
            --mandal-green-gradient: linear-gradient(135deg, #07A158 0%, #058547 100%);
            --secondary-bg: #f9fafb;
            --card-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            --text-dark: #111827;
            --text-muted: #6b7280;
        }

        body {
            background-color: var(--secondary-bg);
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text-dark);
            padding-bottom: 90px; /* Space for bottom nav */
            -webkit-tap-highlight-color: transparent;
        }

        /* App Container for Desktop constraints */
        .app-container {
            max-width: 480px;
            margin: 0 auto;
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.05);
            padding-bottom: 80px;
        }

        /* Responsive specific resets */
        @media (max-width: 480px) {
            .app-container {
                box-shadow: none;
                width: 100%;
            }
        }

        .premium-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.02);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .btn-premium {
            background: var(--mandal-green);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 16px 20px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s ease;
        }

        .btn-premium:hover, .btn-premium:active {
            transform: scale(0.98);
            background: #068a4b;
            color: white;
        }

        /* Remove default link styling */
        a { text-decoration: none; }
    </style>
</head>
<body>

<div class="app-container">
<!-- Note: Top navbar removed to be replaced by page-specific native headers -->
