// assets/js/pos.js
const POS = {
    cart: [],
    currentCategory: 'all',
    searchTimeout: null,
    
    init: function() {
        this.loadCart();
        this.bindEvents();
        this.fetchProducts();
        
        // Use a default image if no-image.png doesn't exist
        this.defaultImg = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2060%2060%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_1%20text%20%7B%20fill%3A%23999%3Bfont-weight%3Anormal%3Bfont-family%3AInter%2C%20Helvetica%2C%20sans-serif%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_1%22%3E%3Crect%20width%3D%2260%22%20height%3D%2260%22%20fill%3D%22%23eee%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2214%22%20y%3D%2234%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E';
    },
    
    loadCart: function() {
        const savedCart = localStorage.getItem('pos_cart');
        if (savedCart) {
            this.cart = JSON.parse(savedCart);
        }
        this.updateCartCount();
    },
    
    saveCart: function() {
        localStorage.setItem('pos_cart', JSON.stringify(this.cart));
        this.updateCartCount();
    },
    
    updateCartCount: function() {
        const count = this.cart.length;
        document.getElementById('cartCount').textContent = count;
    },
    
    addToCart: function(product) {
        // Check stock
        if (parseFloat(product.stock) <= 0) {
            alert('Product is out of stock!');
            return;
        }
        
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            if (existingItem.qty >= parseFloat(product.stock)) {
                alert('Cannot add more than available stock!');
                return;
            }
            existingItem.qty += 1;
        } else {
            this.cart.push({
                ...product,
                qty: 1
            });
        }
        
        this.saveCart();
        
        // Show brief visual feedback (optional toast)
        const btn = document.querySelector(`.add-to-cart[data-id="${product.id}"]`);
        if (btn) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i>';
            btn.classList.replace('btn-primary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.replace('btn-success', 'btn-primary');
            }, 500);
        }
    },
    
    bindEvents: function() {
        const self = this;
        
        // Search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(self.searchTimeout);
                self.searchTimeout = setTimeout(() => {
                    self.fetchProducts(this.value, self.currentCategory);
                }, 300);
            });
        }
        
        // Category Pills
        const pills = document.querySelectorAll('.category-pill');
        pills.forEach(pill => {
            pill.addEventListener('click', function() {
                pills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                self.currentCategory = this.dataset.id;
                self.fetchProducts(searchInput ? searchInput.value : '', self.currentCategory);
            });
        });
        
        // Add to cart delegation
        document.getElementById('productList').addEventListener('click', function(e) {
            const btn = e.target.closest('.add-to-cart');
            if (btn) {
                const productData = JSON.parse(btn.dataset.product);
                self.addToCart(productData);
            }
        });
    },
    
    fetchProducts: async function(search = '', category = 'all') {
        const listContainer = document.getElementById('productList');
        listContainer.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        try {
            // Adjust BASE_URL usage, assuming we are in /employee/pos.php
            const response = await fetch(`../api/products/list.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`);
            const result = await response.json();
            
            if (result.success) {
                this.renderProducts(result.data);
            } else {
                listContainer.innerHTML = `<div class="col-12"><div class="alert alert-danger">${result.message}</div></div>`;
            }
        } catch (error) {
            console.error(error);
            listContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load products</div></div>';
        }
    },
    
    renderProducts: function(products) {
        const listContainer = document.getElementById('productList');
        
        if (products.length === 0) {
            listContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No products found</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        products.forEach(p => {
            let stockBadge = '';
            if (parseFloat(p.stock) <= 0) {
                stockBadge = '<span class="text-danger small fw-bold">Out of Stock</span>';
            } else if (parseFloat(p.stock) <= 5) {
                stockBadge = `<span class="text-warning small fw-bold">Low Stock: ${p.stock} ${p.unit}</span>`;
            } else {
                stockBadge = `<span class="text-muted small">Stock: ${p.stock} ${p.unit}</span>`;
            }
            
            const productJson = JSON.stringify(p).replace(/"/g, '&quot;');
            
            // Note: I use self.defaultImg if image_url fails to load, but typically we handle it in CSS or backend.
            
            html += `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="product-card">
                    <img src="${p.image_url}" onerror="this.src='${this.defaultImg}'" class="product-image" alt="${p.name}">
                    <div class="product-details">
                        <div class="product-title">${p.name}</div>
                        <div class="product-meta mb-1">SKU: ${p.sku || 'N/A'}</div>
                        <div class="product-price">${p.formatted_price} <span class="text-muted small fw-normal">/ ${p.unit}</span></div>
                        <div class="d-flex justify-content-between align-items-end mt-1">
                            <div>
                                ${stockBadge}<br>
                                <span class="text-muted small">Expiry: ${p.formatted_expiry}</span>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-circle add-to-cart" style="width: 32px; height: 32px; padding: 0;" data-id="${p.id}" data-product="${productJson}" ${parseFloat(p.stock) <= 0 ? 'disabled' : ''}>
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `;
        });
        
        listContainer.innerHTML = html;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    POS.init();
});
