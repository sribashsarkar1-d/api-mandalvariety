<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_login();

$page_title = 'Cart';
$show_back_btn = true;

// Header Action HTML (Delete/Clear Cart)
$header_action_html = '<i class="bi bi-trash" id="clearCartBtn" style="cursor:pointer;" title="Clear Cart"></i>';

require_once '../includes/header.php';
?>
<style>
    body { background-color: var(--bg-color); }
    .bottom-nav { display: none !important; }
    
    .cart-summary {
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-top: 1.5rem;
    }
    
    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        color: var(--text-muted);
    }
    .cart-summary-row.total {
        color: var(--text-main);
        font-weight: 700;
        font-size: 1.2rem;
        border-top: 1px solid var(--border-color);
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }
    
    .customer-selector {
        background: white;
        padding: 1rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .customer-selector i.bi-person-circle {
        font-size: 2.5rem;
        color: var(--primary-blue);
        margin-right: 1rem;
    }
    .customer-info {
        flex-grow: 1;
    }
    .customer-name {
        font-weight: 600;
        margin-bottom: 0;
    }
    .customer-phone {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
</style>

<!-- Customer Selector -->
<div class="customer-selector" data-bs-toggle="modal" data-bs-target="#customerModal">
    <i class="bi bi-person-circle"></i>
    <div class="customer-info" id="selectedCustomerDisplay">
        <p class="customer-name">Walk-in Customer</p>
        <p class="customer-phone">No phone provided</p>
    </div>
    <i class="bi bi-pencil ms-auto text-muted"></i>
</div>

<div class="custom-card p-3">
    <div id="cartItemsList">
        <!-- Cart items rendered by JS -->
    </div>
</div>

<div class="cart-summary">
    <div class="cart-summary-row">
        <span>Subtotal</span>
        <span id="summarySubtotal">₹0.00</span>
    </div>
    <div class="cart-summary-row">
        <span>Discount</span>
        <span id="summaryDiscount">₹0.00</span>
    </div>
    <div class="cart-summary-row">
        <span>GST</span>
        <span id="summaryGST">₹0.00</span>
    </div>
    <div class="cart-summary-row total">
        <span>Total</span>
        <span id="summaryTotal" class="text-primary">₹0.00</span>
    </div>
    
    <button class="btn btn-primary w-100 py-3 mt-4 fw-bold fs-5 rounded-3 shadow-sm" id="checkoutBtn" disabled>
        Checkout
    </button>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Select Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="search-box mb-3 position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control ps-5" id="customerSearch" placeholder="Search by name or phone">
                </div>
                
                <div class="list-group list-group-flush mb-3" id="customerList" style="max-height: 200px; overflow-y: auto;">
                    <button class="list-group-item list-group-item-action fw-bold text-primary active-customer" data-id="" data-name="Walk-in Customer" data-phone="No phone provided">
                        Walk-in Customer
                    </button>
                    <!-- Fetched customers -->
                </div>
                
                <hr>
                <p class="text-center text-muted small mb-2">Or add new customer</p>
                <form id="newCustomerForm">
                    <input type="text" class="form-control mb-2" id="newCustName" placeholder="Full Name" required>
                    <input type="text" class="form-control mb-3" id="newCustPhone" placeholder="Phone Number" required>
                    <button type="submit" class="btn btn-outline-primary w-100">Save & Select Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal (Payment) -->
<div class="modal fade" id="checkoutModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content" style="border-radius: var(--radius-lg);">
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold">Complete Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="text-center mb-4 pt-2">
                    <p class="text-muted mb-1">Amount to Pay</p>
                    <h2 class="fw-bold text-primary mb-0" id="paymentAmountDisplay">₹0.00</h2>
                </div>
                
                <h6 class="fw-bold mb-3 px-1">Payment Method</h6>
                <div class="row g-2 mb-4 px-1">
                    <div class="col-6">
                        <input type="radio" class="btn-check payment-method" name="payment_method" id="payCash" value="cash" checked>
                        <label class="btn btn-outline-primary w-100 py-3" for="payCash"><i class="bi bi-cash fs-4 d-block mb-1"></i> Cash</label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check payment-method" name="payment_method" id="payUpi" value="upi">
                        <label class="btn btn-outline-primary w-100 py-3" for="payUpi"><i class="bi bi-qr-code-scan fs-4 d-block mb-1"></i> UPI</label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check payment-method" name="payment_method" id="payCard" value="card">
                        <label class="btn btn-outline-primary w-100 py-3" for="payCard"><i class="bi bi-credit-card fs-4 d-block mb-1"></i> Card</label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check payment-method" name="payment_method" id="payCredit" value="credit">
                        <label class="btn btn-outline-warning w-100 py-3" for="payCredit"><i class="bi bi-journal-text fs-4 d-block mb-1"></i> Credit (Udhar)</label>
                    </div>
                </div>
                
                <div class="mb-3 px-1 d-none" id="transactionIdWrapper">
                    <label class="form-label text-muted small">Transaction ID (Optional)</label>
                    <input type="text" class="form-control" id="transactionId" placeholder="e.g. UTR123456789">
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 pt-3 pb-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="confirmPaymentBtn">Confirm Bill</button>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = '<script src="' . BASE_URL . '/assets/js/cart.js"></script>';
require_once '../includes/footer.php'; 
?>
