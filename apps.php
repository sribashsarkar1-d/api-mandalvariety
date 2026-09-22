<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Download Apps - Mandal Variety</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- AOS CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
  
  <style>
    .app-card {
      background: #ffffff;
      border-radius: 24px;
      padding: 40px 30px;
      text-align: center;
      box-shadow: var(--card-shadow);
      border: 1px solid rgba(0, 0, 0, 0.05);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .app-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(100, 122, 103, 0.15);
      border-color: var(--primary-color);
    }
    .app-icon-wrapper {
      width: 90px;
      height: 90px;
      background: var(--gradient-bg);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 25px auto;
      color: var(--tea-green);
      font-size: 2.5rem;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    .app-card:hover .app-icon-wrapper {
      transform: scale(1.1) rotate(5deg);
    }
    .app-title {
      font-weight: 800;
      font-size: 1.4rem;
      color: var(--text-main);
      margin-bottom: 15px;
    }
    .app-desc {
      color: var(--text-muted);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 30px;
      flex-grow: 1;
    }
    .download-btn {
      background: var(--primary-color);
      color: #ffffff;
      font-weight: 700;
      padding: 12px 30px;
      border-radius: 30px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all 0.3s ease;
      text-decoration: none;
      border: 2px solid transparent;
      width: 100%;
    }
    .download-btn:hover {
      background: var(--carbon-black);
      color: var(--tea-green);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .download-btn i {
      font-size: 1.2rem;
    }
    .page-header {
      padding: 100px 0 60px 0;
      background: linear-gradient(180deg, var(--bg-light) 0%, #ffffff 100%);
      text-align: center;
    }
    .page-title {
      font-size: 3.5rem;
      font-weight: 800;
      color: var(--text-main);
      margin-bottom: 20px;
    }
    .page-subtitle {
      font-size: 1.2rem;
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.6;
    }
  </style>
</head>

<body>

  <!-- TOP BAR -->
  <div class="topbar text-center py-2">
    Ordering is available only through the Mandal Variety mobile application.
  </div>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top custom-navbar">
    <div class="container">

      <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
        <div class="logo-box">
          MV
        </div>

        <div>
          <h5 class="mb-0 fw-bold">Mandal Variety</h5>
          <small>Fast Local Delivery</small>
        </div>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">

        <ul class="navbar-nav mx-auto">

          <li class="nav-item">
            <a class="nav-link" href="index.php#highlights">Highlights</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="index.php#delivery">Delivery Area</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="index.php#categories">Categories</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="index.php#how-to-use">How to Use</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="index.php#contact">Contact</a>
          </li>

        </ul>

        <a href="mandal-variety-official-web/mandal-variety.apk" class="btn btn-dark rounded-pill px-4">
          Download App
        </a>

      </div>
    </div>
  </nav>

  <!-- PAGE HEADER -->
  <header class="page-header">
    <div class="container">
      <h1 class="page-title" data-aos="fade-up">Download Our Apps</h1>
      <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
        Get access to all our dedicated applications designed for customers, delivery partners, and store management.
      </p>
    </div>
  </header>

  <!-- APPS SECTION -->
  <section class="section-padding">
    <div class="container">
      <div class="row g-5 justify-content-center">

        <!-- Customer App -->
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
          <div class="app-card">
            <div>
              <div class="app-icon-wrapper">
                <i class="bi bi-shop"></i>
              </div>
              <h3 class="app-title">Mandal Variety Shop</h3>
              <p class="app-desc">
                The official customer app. Order fresh groceries, snacks, and daily essentials with fast local delivery within 1 hour.
              </p>
            </div>
            <a href="shop-app/app/build/outputs/apk/debug/shop.apk" class="download-btn">
              <i class="bi bi-download"></i> Download APK
            </a>
          </div>
        </div>

        <!-- Delivery Boy App -->
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="app-card">
            <div>
              <div class="app-icon-wrapper">
                <i class="bi bi-bicycle"></i>
              </div>
              <h3 class="app-title">Delivery Partner App</h3>
              <p class="app-desc">
                Dedicated app for our delivery heroes. Manage orders, navigate routes, and deliver smiles across Balarampur.
              </p>
            </div>
            <!-- The actual APK download link can be added here when available -->
            <a href="delivery-partner-app/app/build/outputs/apk/debug/delivery-boy.apk" class="download-btn">
              <i class="bi bi-download"></i> Download APK
            </a>
          </div>
        </div>

        <!-- Inventory App -->
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
          <div class="app-card">
            <div>
              <div class="app-icon-wrapper">
                <i class="bi bi-box-seam"></i>
              </div>
              <h3 class="app-title">Mandal Inventory</h3>
              <p class="app-desc">
                Internal management application for store owners to track stock, manage products, and monitor sales efficiently.
              </p>
            </div>
            <!-- The actual APK download link can be added here when available -->
            <a href="inventory-app/app/build/outputs/apk/debug/inventory.apk" class="download-btn">
              <i class="bi bi-download"></i> Download APK
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-4">
          <h4>Mandal Variety</h4>
          <p>
            Fast Local Delivery Within 1 Hour
          </p>
        </div>
        <div class="col-lg-4">
          <h5>Quick Links</h5>
          <ul class="footer-links">
            <li><a href="index.php#highlights">Highlights</a></li>
            <li><a href="index.php#delivery">Delivery</a></li>
            <li><a href="index.php#categories">Categories</a></li>
            <li><a href="index.php#how-to-use">How to Use</a></li>
            <li><a href="index.php#contact">Contact</a></li>
            <li><a href="apps.php">Shop All App Download</a></li>
          </ul>
        </div>
        <div class="col-lg-4">
          <h5>Policies</h5>
          <ul class="footer-links">
            <li><a href="#">Terms & Conditions</a></li>
            <li><a href="#">Privacy Policy</a></li>
          </ul>
        </div>
      </div>
      <hr>
      <div class="text-center">
        Copyright © 2026 Mandal Variety
      </div>
    </div>
  </footer>

  <!-- FLOATING WHATSAPP -->
  <a href="https://wa.me/918967136033" class="whatsapp-btn">
    <i class="bi bi-whatsapp"></i>
  </a>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <!-- AOS JS -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({
      once: true,
      duration: 800,
      offset: 100
    });
  </script>
</body>
</html>
