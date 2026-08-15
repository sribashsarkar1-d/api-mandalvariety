// assets/js/cart.js
const Cart = {
    items: [],
    customerId: null,
    customerName: 'Walk-in Customer',
    
    init: function() {
        this.loadCart();
        this.bindEvents();
        
        // Use default image if no-image
        this.defaultImg = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2050%2050%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_2%20text%20%7B%20fill%3A%23999%3Bfont-weight%3Anormal%3Bfont-family%3AInter%2C%20Helvetica%2C%20sans-serif%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_2%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23eee%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%229%22%20y%3D%2229%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E';
    },
    
    loadCart: function() {
        const saved = localStorage.getItem('pos_cart');
        if (saved) {
            this.items = JSON.parse(saved);
        }
        this.renderCart();
    },
    
    saveCart: function() {
        localStorage.setItem('pos_cart', JSON.stringify(this.items));
        this.renderCart();
    },
    
    clearCart: function() {
        if (confirm('Are you sure you want to clear the cart?')) {
            this.items = [];
            this.saveCart();
        }
    },
    
    updateQty: function(id, delta) {
        const index = this.items.findIndex(item => item.id == id);
        if (index !== -1) {
            let newQty = this.items[index].qty + delta;
            
            // Check stock logic
            const maxStock = parseFloat(this.items[index].stock);
            
            if (newQty > maxStock) {
                alert(`Only ${maxStock} ${this.items[index].unit} available.`);
                newQty = maxStock;
            }
            
            if (newQty <= 0) {
                this.items.splice(index, 1);
            } else {
                this.items[index].qty = newQty;
            }
            this.saveCart();
        }
    },
    
    removeItem: function(id) {
        this.items = this.items.filter(item => item.id != id);
        this.saveCart();
    },
    
    renderCart: function() {
        const listContainer = document.getElementById('cartItemsList');
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        if (this.items.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Your cart is empty</p>
                    <a href="pos.php" class="btn btn-outline-primary mt-2">Go to Products</a>
                </div>
            `;
            checkoutBtn.disabled = true;
            this.updateSummary(0, 0, 0, 0);
            return;
        }
        
        let html = '';
        let subtotal = 0;
        let totalDiscount = 0;
        let totalGst = 0;
        
        this.items.forEach(item => {
            const price = parseFloat(item.selling_price);
            const qty = parseFloat(item.qty);
            const lineTotal = price * qty;
            
            subtotal += lineTotal;
            // Assuming no individual discount in UI for now, calculate GST if provided from DB
            // We will let backend do strict calc, but we show basic here.
            
            html += `
            <div class="cart-item">
                <img src="${item.image_url}" onerror="this.src='${this.defaultImg}'" class="cart-item-image rounded border">
                <div class="cart-item-details">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-bold text-dark">${item.name}</div>
                        <i class="bi bi-x-circle text-danger remove-item" style="cursor:pointer;" data-id="${item.id}"></i>
                    </div>
                    <div class="text-muted small mb-2">${App.formatCurrency(price)} / ${item.unit}</div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="d-flex align-items-center">
                            <button class="qty-btn minus-qty" data-id="${item.id}"><i class="bi bi-dash"></i></button>
                            <input type="text" class="qty-input mx-1" value="${qty}" readonly>
                            <button class="qty-btn plus-qty" data-id="${item.id}"><i class="bi bi-plus"></i></button>
                        </div>
                        <div class="fw-bold">${App.formatCurrency(lineTotal)}</div>
                    </div>
                </div>
            </div>
            `;
        });
        
        listContainer.innerHTML = html;
        checkoutBtn.disabled = false;
        
        // Update totals (simplified for UI, backend does real calc)
        const grandTotal = subtotal - totalDiscount + totalGst;
        this.updateSummary(subtotal, totalDiscount, totalGst, grandTotal);
    },
    
    updateSummary: function(subtotal, discount, gst, total) {
        document.getElementById('summarySubtotal').textContent = App.formatCurrency(subtotal);
        document.getElementById('summaryDiscount').textContent = App.formatCurrency(discount);
        document.getElementById('summaryGST').textContent = App.formatCurrency(gst);
        document.getElementById('summaryTotal').textContent = App.formatCurrency(total);
        
        // Also update modal payment amount
        document.getElementById('paymentAmountDisplay').textContent = App.formatCurrency(total);
    },
    
    bindEvents: function() {
        const self = this;
        
        // Cart List clicks (qty, remove)
        document.getElementById('cartItemsList').addEventListener('click', function(e) {
            const minusBtn = e.target.closest('.minus-qty');
            const plusBtn = e.target.closest('.plus-qty');
            const removeBtn = e.target.closest('.remove-item');
            
            if (minusBtn) self.updateQty(minusBtn.dataset.id, -1);
            if (plusBtn) self.updateQty(plusBtn.dataset.id, 1);
            if (removeBtn) self.removeItem(removeBtn.dataset.id);
        });
        
        // Clear Cart
        const clearBtn = document.getElementById('clearCartBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => self.clearCart());
        }
        
        // Checkout trigger
        document.getElementById('checkoutBtn').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
        });
        
        // Payment method toggle (show/hide transaction ID)
        document.querySelectorAll('.payment-method').forEach(radio => {
            radio.addEventListener('change', function() {
                const wrapper = document.getElementById('transactionIdWrapper');
                if (['upi', 'card'].includes(this.value)) {
                    wrapper.classList.remove('d-none');
                } else {
                    wrapper.classList.add('d-none');
                    document.getElementById('transactionId').value = '';
                }
            });
        });
        
        // Confirm Payment (Submit to Backend)
        document.getElementById('confirmPaymentBtn').addEventListener('click', async function() {
            if (self.items.length === 0) return;
            
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
            
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const transactionId = document.getElementById('transactionId').value;
            
            const payload = {
                customer_id: self.customerId, // null means walk-in
                items: self.items.map(item => ({ id: item.id, qty: item.qty })),
                payment_method: paymentMethod,
                transaction_id: transactionId
            };
            
            try {
                // Adjust BASE_URL assumption
                const response = await fetch('../api/sales/create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear cart
                    localStorage.removeItem('pos_cart');
                    self.items = [];
                    // Redirect to invoice
                    window.location.href = `invoice.php?id=${result.data.sale_id}`;
                } else {
                    alert('Checkout failed: ' + result.message);
                    btn.disabled = false;
                    btn.textContent = 'Confirm Bill';
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred during checkout.');
                btn.disabled = false;
                btn.textContent = 'Confirm Bill';
            }
        });
        
        // Customer Search & Creation logic could go here
        // (Skipping full AJAX implementation of customer fetch for brevity, 
        // using static Walk-in for now, but UI is prepared).
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
});
