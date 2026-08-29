// assets/js/cart.js
const Cart = {
    items: [],
    customerId: null,
    customerName: 'Walk-in Customer',
    previousDue: 0,
    billTotal: 0,
    
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
            const price = parseFloat(item.selling_price) || 0;
            const discount = parseFloat(item.discount) || 0;
            const gstPercent = parseFloat(item.gst_percent) || 0;
            const qty = parseFloat(item.qty) || 0;
            
            const priceAfterDiscount = price - discount;
            const gstAmountPerUnit = (priceAfterDiscount * gstPercent) / 100;
            
            subtotal += (price * qty);
            totalDiscount += (discount * qty);
            totalGst += (gstAmountPerUnit * qty);
            
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
                        <div class="fw-bold">${App.formatCurrency((priceAfterDiscount + gstAmountPerUnit) * qty)}</div>
                    </div>
                </div>
            </div>
            `;
        });
        
        listContainer.innerHTML = html;
        checkoutBtn.disabled = false;
        
        const total = subtotal - totalDiscount + totalGst;
        this.updateSummary(subtotal, totalDiscount, totalGst, total);
    },
    
    updateSummary: function(subtotal, discount, gst, total) {
        this.billTotal = total;
        document.getElementById('summarySubtotal').textContent = App.formatCurrency(subtotal);
        document.getElementById('summaryDiscount').textContent = App.formatCurrency(discount);
        document.getElementById('summaryGST').textContent = App.formatCurrency(gst);
        document.getElementById('summaryTotal').textContent = App.formatCurrency(total);
        
        const totalPayable = total + this.previousDue;
        
        if (this.previousDue > 0) {
            document.getElementById('rowPreviousDue').style.display = 'flex';
            document.getElementById('rowTotalPayable').style.display = 'flex';
            document.getElementById('summaryPreviousDue').textContent = App.formatCurrency(this.previousDue);
            document.getElementById('summaryTotalPayable').textContent = App.formatCurrency(totalPayable);
            
            // Also update modal
            const mPrevDue = document.getElementById('modalPreviousDue');
            if (mPrevDue) mPrevDue.textContent = App.formatCurrency(this.previousDue);
            const mTotalPay = document.getElementById('modalTotalPayable');
            if (mTotalPay) mTotalPay.textContent = App.formatCurrency(totalPayable);
        } else {
            document.getElementById('rowPreviousDue').style.display = 'none';
            document.getElementById('rowTotalPayable').style.display = 'none';
            
            const mPrevDue = document.getElementById('modalPreviousDue');
            if (mPrevDue) mPrevDue.textContent = App.formatCurrency(0);
            const mTotalPay = document.getElementById('modalTotalPayable');
            if (mTotalPay) mTotalPay.textContent = App.formatCurrency(total);
        }
        
        // Modal bill display
        const mTodayBill = document.getElementById('modalTodayBill');
        if(mTodayBill) mTodayBill.textContent = App.formatCurrency(total);
        
        const upiDisplay = document.getElementById('upiAmountDisplay');
        if(upiDisplay) upiDisplay.textContent = App.formatCurrency(totalPayable);
        
        const paidInput = document.getElementById('paidAmountInput');
        if(paidInput) {
            paidInput.value = totalPayable;
            this.updateRemainingDue();
        }
    },
    
    updateRemainingDue: function() {
        const totalPayable = this.billTotal + this.previousDue;
        const paidInput = document.getElementById('paidAmountInput');
        let paidAmount = parseFloat(paidInput.value);
        if(isNaN(paidAmount)) paidAmount = 0;
        
        let remaining = totalPayable - paidAmount;
        if(remaining < 0) remaining = 0;
        
        document.getElementById('remainingDueDisplay').textContent = App.formatCurrency(remaining);
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
            const confirmBtn = document.getElementById('confirmPaymentBtn');
            if(confirmBtn) confirmBtn.disabled = false;
            
            const totalPayable = self.billTotal + self.previousDue;
            document.getElementById('paidAmountInput').value = totalPayable;
            self.updateRemainingDue();
            
            const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
        });
        
        const paidInput = document.getElementById('paidAmountInput');
        if(paidInput) {
            paidInput.addEventListener('input', function() {
                self.updateRemainingDue();
            });
        }
        
        // Payment received checkbox toggle
        const paymentCheckbox = document.getElementById('paymentReceivedCheckbox');
        if(paymentCheckbox) {
            paymentCheckbox.addEventListener('change', function() {
                const confirmBtn = document.getElementById('confirmPaymentBtn');
                confirmBtn.disabled = !this.checked;
            });
        }
        
        // Payment method toggle (show/hide transaction ID and QR code)
        document.querySelectorAll('.payment-method').forEach(radio => {
            radio.addEventListener('change', function() {
                const transactionWrapper = document.getElementById('transactionIdWrapper');
                const qrContainer = document.getElementById('upiQrContainer');
                
                // Toggle Transaction ID
                if (['upi', 'card'].includes(this.value)) {
                    transactionWrapper.classList.remove('d-none');
                } else {
                    transactionWrapper.classList.add('d-none');
                    document.getElementById('transactionId').value = '';
                }
                
                // Toggle QR Code
                if (this.value === 'upi') {
                    qrContainer.classList.remove('d-none');
                } else {
                    qrContainer.classList.add('d-none');
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
            let paidAmount = parseFloat(document.getElementById('paidAmountInput').value);
            if(isNaN(paidAmount)) paidAmount = 0;
            
            const totalPayable = self.billTotal + self.previousDue;
            if(paidAmount > totalPayable) {
                alert("Payment amount cannot exceed total payable amount.");
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i> Confirm Bill';
                return;
            }
            
            const payload = {
                customer_id: self.customerId, // null means walk-in
                items: self.items.map(item => ({ id: item.id, qty: item.qty })),
                payment_method: paymentMethod,
                transaction_id: transactionId,
                paid_amount: paidAmount
            };
            
            try {
                const response = await fetch('../api/sales/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                
                if (result.success) {
                    // Clear cart
                    localStorage.removeItem('pos_cart');
                    self.items = [];
                    self.previousDue = 0;
                    self.customerId = null;
                    
                    // Hide payment modal
                    const modalEl = document.getElementById('checkoutModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if(modal) modal.hide();
                    
                    // Show Success Screen
                    self.showSuccessScreen(result.data);
                } else {
                    alert(result.message || 'Failed to create bill');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i> Confirm Bill';
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred during checkout.');
                btn.disabled = false;
                btn.textContent = 'Confirm Bill';
            }
        });
        
        // Search customer
        const customerSearch = document.getElementById('customerSearch');
        if(customerSearch) {
            customerSearch.addEventListener('input', function() {
                self.searchCustomers(this.value);
            });
            // Initial load
            self.searchCustomers('');
        }
        
        // Select customer from list
        document.getElementById('customerList').addEventListener('click', function(e) {
            const item = e.target.closest('.list-group-item');
            if(item) {
                // Update active state
                document.querySelectorAll('#customerList .list-group-item').forEach(el => el.classList.remove('active-customer', 'text-primary'));
                item.classList.add('active-customer', 'text-primary');
                
                // Update self
                self.customerId = item.dataset.id || null;
                self.customerName = item.dataset.name;
                
                // Update UI display
                document.querySelector('#selectedCustomerDisplay .customer-name').textContent = item.dataset.name;
                document.querySelector('#selectedCustomerDisplay .customer-phone').textContent = item.dataset.phone || 'No phone provided';
                
                // Close modal
                const modalEl = document.getElementById('customerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if(modal) modal.hide();
                
                self.fetchCustomerDue();
            }
        });
        
        // Add new customer
        const newCustomerForm = document.getElementById('newCustomerForm');
        if(newCustomerForm) {
            newCustomerForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const name = document.getElementById('newCustName').value;
                const phone = document.getElementById('newCustPhone').value;
                const address = document.getElementById('newCustAddress') ? document.getElementById('newCustAddress').value : '';
                const opening_due = 0; // Employees cannot set opening due
                
                const btn = this.querySelector('button[type="submit"]');
                const origText = btn.textContent;
                btn.disabled = true;
                btn.innerHTML = 'Saving...';
                
                try {
                    const response = await fetch('../api/customers/create.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ name, phone, address, opening_due })
                    });
                    const result = await response.json();
                    
                    if(result.success) {
                        // Set active
                        self.customerId = result.data.id;
                        self.customerName = result.data.name;
                        
                        document.querySelector('#selectedCustomerDisplay .customer-name').textContent = result.data.name;
                        document.querySelector('#selectedCustomerDisplay .customer-phone').textContent = result.data.phone;
                        
                        // Close modal
                        const modalEl = document.getElementById('customerModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if(modal) modal.hide();
                        
                        // Reload list
                        self.searchCustomers('');
                        newCustomerForm.reset();
                    } else {
                        alert(result.message);
                    }
                } catch(error) {
                    console.error(error);
                    alert("Failed to create customer");
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            });
        }
    },
    
    searchCustomers: async function(query) {
        const list = document.getElementById('customerList');
        try {
            const response = await fetch(`../api/customers/search.php?q=${encodeURIComponent(query)}`);
            const result = await response.json();
            
            if(result.success) {
                let html = `
                    <button class="list-group-item list-group-item-action fw-bold ${!this.customerId ? 'active-customer text-primary' : ''}" data-id="" data-name="Walk-in Customer" data-phone="No phone provided">
                        Walk-in Customer
                    </button>
                `;
                
                result.data.forEach(c => {
                    const isActive = this.customerId == c.id;
                    html += `
                        <button class="list-group-item list-group-item-action fw-bold ${isActive ? 'active-customer text-primary' : ''}" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone}">
                            ${c.name} <small class="text-muted d-block fw-normal">${c.phone}</small>
                        </button>
                    `;
                });
                
                list.innerHTML = html;
            }
        } catch(error) {
            console.error(error);
        }
    },
    
    fetchCustomerDue: async function() {
        if (!this.customerId) {
            this.previousDue = 0;
            this.renderCart();
            return;
        }
        
        try {
            const response = await fetch(`../api/credit/customer-due.php?customer_id=${this.customerId}`);
            const result = await response.json();
            
            if (result.success) {
                this.previousDue = parseFloat(result.data.current_due);
            } else {
                this.previousDue = 0;
            }
        } catch (error) {
            console.error("Error fetching due:", error);
            this.previousDue = 0;
        }
        
        this.renderCart();
    },
    
    showSuccessScreen: function(data) {
        document.getElementById('successTodayBill').textContent = App.formatCurrency(data.new_bill_total);
        document.getElementById('successPreviousBaki').textContent = App.formatCurrency(data.previous_due);
        document.getElementById('successPaidToday').textContent = App.formatCurrency(data.paid_today);
        document.getElementById('successRemainingBaki').textContent = App.formatCurrency(data.remaining_due);
        document.getElementById('successPaymentMethod').textContent = data.payment_method.toUpperCase();
        
        const prevBakiRow = document.getElementById('successRowPreviousBaki');
        const paidRow = document.getElementById('successRowPaidToday');
        const remainingBakiRow = document.getElementById('successRowRemainingBaki');
        
        // Hide baki related info if it was a normal sale with no credit involved
        if (data.previous_due == 0 && data.remaining_due == 0 && data.paid_today == data.new_bill_total) {
            if (prevBakiRow) prevBakiRow.style.display = 'none';
            if (remainingBakiRow) remainingBakiRow.style.display = 'none';
            if (paidRow) paidRow.style.display = 'none';
        } else {
            if (prevBakiRow) prevBakiRow.style.display = 'flex';
            if (remainingBakiRow) remainingBakiRow.style.display = 'flex';
            if (paidRow) paidRow.style.display = 'flex';
        }
        
        // Setup buttons
        const printBtn = document.getElementById('successPrintBtn');
        const viewBtn = document.getElementById('successViewBtn');
        const newBtn = document.getElementById('successNewBtn');
        
        printBtn.onclick = () => window.location.href = `invoice.php?id=${data.sale_id}&auto_print=1`;
        viewBtn.onclick = () => window.location.href = `invoice.php?id=${data.sale_id}`;
        newBtn.onclick = () => {
            const modalEl = document.getElementById('successModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if(modal) modal.hide();
            window.location.reload();
        };
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('successModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
});
