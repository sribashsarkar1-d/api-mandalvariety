<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mandal Variety</title>

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Bootstrap Icons -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
  />

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
</head>

<body>

  <!-- TOP BAR -->
  <div class="topbar text-center py-2">
    Welcome to Mandal Variety! Fast local delivery within 1 hour.
  </div>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top custom-navbar">
    <div class="container">

      <a class="navbar-brand d-flex align-items-center gap-3" href="#">
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
            <a class="nav-link" href="#highlights">Highlights</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#delivery">Delivery Area</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#categories">Categories</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>

        </ul>

        <div class="d-flex align-items-center gap-3">
          <a href="#" class="text-dark position-relative" data-bs-toggle="modal" data-bs-target="#wishlistModal" title="Wishlist">
            <i class="bi bi-heart fs-5"></i>
            <span id="wishlist-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; display: none;">0</span>
          </a>
          <a href="#" class="text-dark position-relative" data-bs-toggle="modal" data-bs-target="#cartModal" title="Cart">
            <i class="bi bi-cart fs-5"></i>
            <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; display: none;">0</span>
          </a>
          <a href="mandal-variety-official-web/mandal-variety.apk" class="btn btn-dark rounded-pill px-4 ms-2 d-none d-lg-block">
            Download App
          </a>
        </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-section">

    <div class="container">

      <div class="row align-items-center g-5">

        <div class="col-lg-6">

          <span class="badge custom-badge mb-4">
            Delivery only in Balarampur, Cooch Behar
          </span>

          <h1 class="hero-title">
            Mandal Variety
          </h1>

          <h2 class="hero-gradient-text">
            Fast Local Delivery Within 1 Hour
          </h2>

          <p class="hero-text">
            Fresh groceries, snacks, beverages, daily essentials and more delivered quickly in Balarampur, Cooch Behar.
          </p>

          <div class="hero-buttons d-flex flex-wrap gap-3">

            <a href="mandal-variety-official-web/mandal-variety.apk" class="btn btn-dark btn-lg rounded-pill px-4">
              Download App
            </a>

            <a href="#contact" class="btn btn-light btn-lg rounded-pill px-4 border">
              Contact Us
            </a>

          </div>

          <div class="row mt-5 g-4">

            <div class="col-md-4">
              <div class="info-card">
                <h4>1 Hour</h4>
                <p>Fast delivery promise</p>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-card">
                <h4>₹10 Only</h4>
                <p>Flat delivery charge</p>
              </div>
            </div>

            <div class="col-md-4">
              <div class="info-card">
                <h4>Local & Fresh</h4>
                <p>Daily essentials nearby</p>
              </div>
            </div>

          </div>

        </div>

        <div class="col-lg-6">

          <div class="phone-card">

            <div class="delivery-box">

              <small>Delivery Zone</small>

              <h3>Balarampur</h3>

              <span class="app-badge">
                App Only
              </span>

            </div>

            <div class="service-box">

              <h3>Within 1 Hour</h3>

              <p>
                Groceries, snacks, drinks, dairy, and household essentials on demand.
              </p>

              <div class="tags d-flex gap-2 flex-wrap">

                <span>₹10 Delivery</span>

                <span>Fast Support</span>

              </div>

            </div>

            <div class="row g-3 mt-2">

              <div class="col-6">
                <div class="mini-category">
                  <i class="bi bi-basket2-fill"></i>
                  <p>Groceries</p>
                </div>
              </div>

              <div class="col-6">
                <div class="mini-category">
                  <i class="bi bi-cup-straw"></i>
                  <p>Cold Drinks</p>
                </div>
              </div>

              <div class="col-6">
                <div class="mini-category">
                  <i class="bi bi-bag-fill"></i>
                  <p>Snacks</p>
                </div>
              </div>

              <div class="col-6">
                <div class="mini-category">
                  <i class="bi bi-house-fill"></i>
                  <p>Daily Needs</p>
                </div>
              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </section>

  <!-- HIGHLIGHTS -->
  <section class="section-padding" id="highlights">

    <div class="container">

      <div class="section-title text-center">

        <h2>Service Highlights</h2>

        <p>
          Premium local delivery designed around convenience
        </p>

      </div>

      <div class="row g-4">

        <div class="col-lg-4 col-md-6">

          <div class="service-card">

            <i class="bi bi-clock-fill"></i>

            <h4>1 Hour Delivery</h4>

            <p>
              Receive essentials quickly within one hour.
            </p>

          </div>

        </div>

        <div class="col-lg-4 col-md-6">

          <div class="service-card">

            <i class="bi bi-currency-rupee"></i>

            <h4>₹10 Flat Charge</h4>

            <p>
              Affordable and simple pricing system.
            </p>

          </div>

        </div>

        <div class="col-lg-4 col-md-6">

          <div class="service-card">

            <i class="bi bi-shop"></i>

            <h4>Trusted Local Store</h4>

            <p>
              Reliable neighborhood delivery partner.
            </p>

          </div>

        </div>

      </div>

    </div>

  </section>

  <!-- DELIVERY -->
  <section class="section-padding bg-light" id="delivery">

    <div class="container">

      <div class="row g-5 align-items-center">

        <div class="col-lg-6">

          <h2 class="fw-bold mb-4">
            Delivery Area
          </h2>

          <p class="text-muted">
            We currently deliver only in:
          </p>

          <div class="delivery-area-box">
            Balarampur, Cooch Behar, West Bengal
          </div>

          <div class="row mt-4 g-4">

            <div class="col-md-6">
              <div class="small-box">
                <h4>1 Hour</h4>
                <p>Fast Delivery</p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="small-box">
                <h4>₹10</h4>
                <p>Flat Charge</p>
              </div>
            </div>

          </div>

        </div>

        <div class="col-lg-6">

          <div class="map-card">

            <div class="circle-main">
              MV
            </div>

          </div>

        </div>

      </div>

    </div>

  </section>

  <!-- CATEGORIES -->
  <section class="section-padding" id="categories">

    <div class="container">

      <div class="section-title text-center">

        <h2>Categories</h2>

        <p>
          Daily essentials for every customer
        </p>

      </div>

      <div class="row g-4">

        <div class="col-lg-3 col-md-6">
          <div class="category-card">
            <i class="bi bi-basket2-fill"></i>
            <h5>Groceries</h5>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="category-card">
            <i class="bi bi-cup-straw"></i>
            <h5>Cold Drinks</h5>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="category-card">
            <i class="bi bi-bag-fill"></i>
            <h5>Snacks</h5>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="category-card">
            <i class="bi bi-house-fill"></i>
            <h5>Daily Needs</h5>
          </div>
        </div>

      </div>

    </div>

  </section>

  <!-- PRODUCTS -->
  <section class="section-padding bg-light" id="products">
    <div class="container">
      <div class="section-title text-center">
        <h2>Our Products</h2>
        <p>Fresh items available for immediate delivery</p>
      </div>
      <div class="row g-4" id="product-list">
        <!-- Products will be injected here via JS -->
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section class="section-padding bg-light" id="about">

    <div class="container">

      <div class="row align-items-center g-5">

        <div class="col-lg-6">

          <h2 class="fw-bold mb-4">
            About Mandal Variety
          </h2>

          <p class="text-muted">
            Trusted local delivery service dedicated to providing daily essentials quickly and affordably.
          </p>

          <div class="row mt-4 g-4">

            <div class="col-6">
              <div class="info-card">
                <h4>2500+</h4>
                <p>Happy Customers</p>
              </div>
            </div>

            <div class="col-6">
              <div class="info-card">
                <h4>12000+</h4>
                <p>Deliveries</p>
              </div>
            </div>

          </div>

        </div>

        <div class="col-lg-6">

          <div class="about-box">

            <h4>Why Choose Us?</h4>

            <ul>

              <li>Fast Delivery</li>

              <li>Trusted Local Store</li>

              <li>Affordable Charge</li>

              <li>Fresh Products</li>

              <li>Friendly Support</li>

            </ul>

          </div>

        </div>

      </div>

    </div>

  </section>

  <!-- CONTACT -->
  <section class="section-padding" id="contact">

    <div class="container">

      <div class="section-title text-center">

        <h2>Contact Us</h2>

        <p>
          Reach the Mandal Variety support team
        </p>

      </div>

      <div class="row g-4">

        <div class="col-lg-4">

          <div class="contact-card">

            <i class="bi bi-telephone-fill"></i>

            <h5>Phone</h5>

            <p>+91 89671 36033</p>

          </div>

        </div>

        <div class="col-lg-4">

          <div class="contact-card">

            <i class="bi bi-envelope-fill"></i>

            <h5>Email</h5>

            <p>mandalvarietycustomersupport@gmail.com</p>

          </div>

        </div>

        <div class="col-lg-4">

          <div class="contact-card">

            <i class="bi bi-geo-alt-fill"></i>

            <h5>Address</h5>

            <p>Balarampur, Cooch Behar</p>

          </div>

        </div>

      </div>

    </div>

  </section>

  <!-- DOWNLOAD -->
  <section class="download-section" id="download">

    <div class="container text-center">

      <h2>
        Download The Mandal Variety App
      </h2>

      <p>
        Ordering is available only through the official mobile application.
      </p>

      <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">

        <a href="#" class="btn btn-light btn-lg rounded-pill px-4">
          Android App
        </a>

        <a href="#" class="btn btn-outline-light btn-lg rounded-pill px-4">
          iOS App
        </a>

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

            <li><a href="#">Highlights</a></li>

            <li><a href="#">Delivery</a></li>

            <li><a href="#">Categories</a></li>

            <li><a href="#">Contact</a></li>

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
  <a href="https://wa.me/91 89671 36033" class="whatsapp-btn">
    <i class="bi bi-whatsapp"></i>
  </a>

  <!-- CART MODAL -->
  <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel">Shopping Cart</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="cart-items">
          <p class="text-center text-muted">Your cart is empty.</p>
        </div>
        <div class="modal-footer d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Total: ₹<span id="cart-total">0</span></h5>
          <button type="button" class="btn btn-dark rounded-pill px-4" onclick="placeOrder()">Place Order</button>
        </div>
      </div>
    </div>
  </div>

  <!-- WISHLIST MODAL -->
  <div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="wishlistModalLabel">Your Wishlist</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="wishlist-items">
          <p class="text-center text-muted">Your wishlist is empty.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <!-- CUSTOM SCRIPT -->
  <script>
    const products = [
      { id: 1, name: 'Premium Fresh Dates', price: 250, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_37_32 PM.png' },
      { id: 2, name: 'Organic Almonds', price: 400, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_37_44 PM.png' },
      { id: 3, name: 'Cashew Nuts', price: 350, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_37_49 PM.png' },
      { id: 4, name: 'Mixed Dry Fruits', price: 500, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_37_55 PM.png' },
      { id: 5, name: 'Green Raisins', price: 150, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_38_00 PM.png' },
      { id: 6, name: 'Walnut Kernels', price: 450, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_38_05 PM.png' },
      { id: 7, name: 'Pistachios (Salted)', price: 480, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_38_12 PM.png' },
      { id: 8, name: 'Dried Figs', price: 300, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_38_19 PM.png' },
      { id: 9, name: 'Black Raisins', price: 180, image: 'mandal-variety-official-web/images/ChatGPT Image Jul 4, 2026, 03_38_25 PM.png' }
    ];

    let cart = JSON.parse(localStorage.getItem('mv_cart')) || [];
    let wishlist = JSON.parse(localStorage.getItem('mv_wishlist')) || [];

    function saveState() {
      localStorage.setItem('mv_cart', JSON.stringify(cart));
      localStorage.setItem('mv_wishlist', JSON.stringify(wishlist));
      updateBadges();
      renderCart();
      renderWishlist();
    }

    function updateBadges() {
      const cartCount = cart.reduce((acc, item) => acc + item.quantity, 0);
      const wishlistCount = wishlist.length;

      const cartBadge = document.getElementById('cart-badge');
      const wishlistBadge = document.getElementById('wishlist-badge');

      if (cartCount > 0) {
        cartBadge.textContent = cartCount;
        cartBadge.style.display = 'block';
      } else {
        cartBadge.style.display = 'none';
      }

      if (wishlistCount > 0) {
        wishlistBadge.textContent = wishlistCount;
        wishlistBadge.style.display = 'block';
      } else {
        wishlistBadge.style.display = 'none';
      }
    }

    function renderProducts() {
      const container = document.getElementById('product-list');
      if (!container) return;
      container.innerHTML = '';

      products.forEach(p => {
        const isWished = wishlist.some(w => w.id === p.id);
        const wishIcon = isWished ? 'bi-heart-fill text-danger' : 'bi-heart';

        container.innerHTML += `
          <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden product-card">
              <img src="${p.image}" class="card-img-top" alt="${p.name}" style="height: 250px; object-fit: cover;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h5 class="card-title mb-0 fw-bold">${p.name}</h5>
                  <button class="btn btn-light btn-sm rounded-circle shadow-sm" onclick="toggleWishlist(${p.id})" title="Wishlist">
                    <i class="bi ${wishIcon}"></i>
                  </button>
                </div>
                <p class="card-text text-success fw-bold fs-5 mb-3">₹${p.price}</p>
                <button class="btn btn-dark w-100 rounded-pill" onclick="addToCart(${p.id})">
                  <i class="bi bi-cart-plus me-2"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
        `;
      });
    }

    function addToCart(id) {
      const product = products.find(p => p.id === id);
      const existing = cart.find(item => item.id === id);
      if (existing) {
        existing.quantity += 1;
      } else {
        cart.push({ ...product, quantity: 1 });
      }
      saveState();
      
      const btn = event.currentTarget;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-check2"></i> Added';
      btn.classList.replace('btn-dark', 'btn-success');
      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.replace('btn-success', 'btn-dark');
      }, 1000);
    }

    function removeFromCart(id) {
      cart = cart.filter(item => item.id !== id);
      saveState();
    }

    function updateQuantity(id, delta) {
      const item = cart.find(i => i.id === id);
      if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) removeFromCart(id);
        else saveState();
      }
    }

    function toggleWishlist(id) {
      const existingIndex = wishlist.findIndex(item => item.id === id);
      if (existingIndex > -1) {
        wishlist.splice(existingIndex, 1);
      } else {
        const product = products.find(p => p.id === id);
        wishlist.push(product);
      }
      saveState();
      renderProducts();
    }

    function renderCart() {
      const container = document.getElementById('cart-items');
      const totalEl = document.getElementById('cart-total');
      
      if (cart.length === 0) {
        container.innerHTML = '<p class="text-center text-muted my-4">Your cart is empty.</p>';
        totalEl.textContent = '0';
        return;
      }

      let html = '';
      let total = 0;

      cart.forEach(item => {
        total += item.price * item.quantity;
        html += `
          <div class="d-flex align-items-center mb-3 border-bottom pb-3">
            <img src="${item.image}" alt="${item.name}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
            <div class="ms-3 flex-grow-1">
              <h6 class="mb-0">${item.name}</h6>
              <small class="text-muted">₹${item.price}</small>
            </div>
            <div class="d-flex align-items-center me-3">
              <button class="btn btn-sm btn-outline-secondary rounded-circle px-2 py-0" onclick="updateQuantity(${item.id}, -1)">-</button>
              <span class="mx-2">${item.quantity}</span>
              <button class="btn btn-sm btn-outline-secondary rounded-circle px-2 py-0" onclick="updateQuantity(${item.id}, 1)">+</button>
            </div>
            <button class="btn btn-sm btn-light text-danger rounded-circle" onclick="removeFromCart(${item.id})">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        `;
      });

      container.innerHTML = html;
      totalEl.textContent = total;
    }

    function renderWishlist() {
      const container = document.getElementById('wishlist-items');
      
      if (wishlist.length === 0) {
        container.innerHTML = '<p class="text-center text-muted my-4">Your wishlist is empty.</p>';
        return;
      }

      let html = '';
      wishlist.forEach(item => {
        html += `
          <div class="d-flex align-items-center mb-3 border-bottom pb-3">
            <img src="${item.image}" alt="${item.name}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
            <div class="ms-3 flex-grow-1">
              <h6 class="mb-0">${item.name}</h6>
              <small class="text-muted">₹${item.price}</small>
            </div>
            <button class="btn btn-sm btn-dark rounded-pill me-2" onclick="addToCart(${item.id}); toggleWishlist(${item.id});">
              Move to Cart
            </button>
            <button class="btn btn-sm btn-light text-danger rounded-circle" onclick="toggleWishlist(${item.id})">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        `;
      });

      container.innerHTML = html;
    }

    function placeOrder() {
      if (cart.length === 0) return;
      
      let message = "Hello Mandal Variety, I would like to place an order:\n\n";
      let total = 0;
      
      cart.forEach((item, index) => {
        message += `${index + 1}. ${item.name} (x${item.quantity}) - ₹${item.price * item.quantity}\n`;
        total += item.price * item.quantity;
      });
      
      message += `\n*Total: ₹${total}*`;
      message += `\nDelivery Area: Balarampur`;
      
      const whatsappUrl = `https://wa.me/918967136033?text=${encodeURIComponent(message)}`;
      window.open(whatsappUrl, '_blank');
    }

    document.addEventListener('DOMContentLoaded', () => {
      renderProducts();
      saveState();
    });
  </script>
</body>
</html>