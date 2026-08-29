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
    /* .bottom-nav { display: none !important; } */
    
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
    <!-- Cart Summary -->
    <div class="cart-summary-row mt-3">
        <span>Subtotal</span>
        <span id="summarySubtotal">₹0.00</span>
    </div>
    <div class="cart-summary-row text-success">
        <span>Discount</span>
        <span id="summaryDiscount">₹0.00</span>
    </div>
    <div class="cart-summary-row">
        <span>GST</span>
        <span id="summaryGST">₹0.00</span>
    </div>
    <div class="cart-summary-row mt-2 pt-2 border-top">
        <span>Today's Bill</span>
        <span id="summaryTotal" class="text-primary fw-bold fs-5">₹0.00</span>
    </div>
    
    <div class="cart-summary-row mt-2 text-danger" id="rowPreviousDue" style="display: none;">
        <span>Previous Baki</span>
        <span id="summaryPreviousDue">₹0.00</span>
    </div>
    
    <div class="cart-summary-row total mt-2 pt-2 border-top" id="rowTotalPayable" style="display: none;">
        <span>Total Payable</span>
        <span id="summaryTotalPayable" class="text-danger fw-bold fs-4">₹0.00</span>
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
                    <input type="text" class="form-control mb-2" id="newCustPhone" placeholder="Phone Number" required>
                    <textarea class="form-control mb-2" id="newCustAddress" placeholder="Address (Optional)" rows="2"></textarea>
                    <input type="number" class="form-control mb-3" id="newCustOpeningDue" placeholder="Opening Due ₹ (Optional)" step="0.01" min="0">
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
                <div class="bg-white p-3 rounded mb-3 shadow-sm text-center">
                    <p class="text-muted fw-bold mb-1">COMPLETE PAYMENT</p>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Today's Bill</span>
                        <span class="fw-bold" id="modalTodayBill">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Previous Baki</span>
                        <span class="fw-bold text-danger" id="modalPreviousDue">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <span class="fw-bold">Total Payable</span>
                        <span class="fw-bold text-primary fs-5" id="modalTotalPayable">₹0.00</span>
                    </div>
                </div>
                
                <div class="mb-3 px-1">
                    <label class="form-label text-muted small fw-bold">Paid Today (₹)</label>
                    <input type="number" step="0.01" class="form-control form-control-lg fw-bold" id="paidAmountInput" placeholder="Enter amount paid">
                    <div class="d-flex justify-content-between mt-2 p-2 bg-white rounded border">
                        <span class="fw-bold">Remaining Baki</span>
                        <span id="remainingDueDisplay" class="text-danger fw-bold fs-5">₹0.00</span>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3 px-1">Payment Method</h6>
                <div class="row g-2 mb-2 px-1">
                    <div class="col-6">
                        <input type="radio" class="btn-check payment-method" name="payment_method" id="payCash" value="cash" checked>
                        <label class="btn btn-outline-primary w-100 py-3" for="payCash"><i class="bi bi-cash fs-4 d-block mb-1"></i> Cash</label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check payment-method" name="payment_method" id="payUpi" value="upi">
                        <label class="btn btn-outline-primary w-100 py-3" for="payUpi"><i class="bi bi-qr-code-scan fs-4 d-block mb-1"></i> UPI</label>
                    </div>
                </div>
                
                <div class="text-center mb-3 d-none" id="upiQrContainer">
                    <p class="text-muted small mb-2">Scan to Pay <span id="upiAmountDisplay" class="fw-bold text-dark"></span></p>
                    <div class="bg-white p-2 rounded d-inline-block border shadow-sm">
                        <img src="../assets/images/image.png" alt="UPI QR Code" style="width: 200px; height: 200px; object-fit: contain;">
                    </div>
                    <p class="small text-muted mt-2">Wait for successful payment confirmation before proceeding.</p>
                </div>
                
                <div class="mb-2 px-1 d-none" id="txWrapper">
                    <input type="text" class="form-control" id="transactionId" placeholder="Transaction ID (Optional)">
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-primary w-100 py-3 fw-bold fs-5 rounded-3 shadow-sm" id="confirmPaymentBtn">
                    <i class="bi bi-check2-circle me-2"></i> CONFIRM BILL
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg text-success" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-4">BILL CREATED SUCCESSFULLY</h4>
                
                <div class="bg-light p-3 rounded text-start mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Today's Bill</span>
                        <span class="fw-bold" id="successTodayBill">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" id="successRowPreviousBaki">
                        <span class="text-muted">Previous Baki</span>
                        <span class="fw-bold text-danger" id="successPreviousBaki">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom" id="successRowPaidToday">
                        <span class="text-muted">Paid Today</span>
                        <span class="fw-bold text-success" id="successPaidToday">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3" id="successRowRemainingBaki">
                        <span class="fw-bold">Remaining Baki</span>
                        <span class="fw-bold text-danger fs-5" id="successRemainingBaki">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Payment Method</span>
                        <span class="fw-bold" id="successPaymentMethod">CASH</span>
                    </div>
                </div>
                
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-primary py-2 fw-bold" id="successViewBtn">
                        <i class="bi bi-file-earmark-text me-2"></i> VIEW INVOICE
                    </button>
                    <button class="btn btn-outline-primary py-2 fw-bold" id="successPrintBtn">
                        <i class="bi bi-printer me-2"></i> PRINT INVOICE
                    </button>
                    <button class="btn btn-light py-2 fw-bold border" id="successNewBtn">
                        <i class="bi bi-plus-circle me-2"></i> NEW BILL
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = '<script src="' . BASE_URL . '/assets/js/cart.js"></script>';
require_once '../includes/footer.php'; 
?>
