<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_employee();

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customer_id <= 0) {
    die("Invalid customer.");
}

$stmtCust = $pdo->prepare("SELECT * FROM employee_customers WHERE id = ?");
$stmtCust->execute([$customer_id]);
$customer = $stmtCust->fetch();
if (!$customer) die("Customer not found.");

$stmtDue = $pdo->prepare("
    SELECT new_due 
    FROM employee_customer_ledger 
    WHERE customer_id = ? 
    ORDER BY created_at DESC, id DESC 
    LIMIT 1
");
$stmtDue->execute([$customer_id]);
$ledger = $stmtDue->fetch();
$current_due = $ledger ? (float)$ledger['new_due'] : 0.00;

if ($current_due <= 0) {
    echo "<script>alert('Customer has no outstanding due.'); window.location.href='customers.php';</script>";
    exit;
}

$page_title = 'Receive Payment';
$show_back_btn = true;
require_once '../includes/header.php';
?>

<div class="custom-card p-4">
    <div class="text-center mb-4">
        <h5 class="fw-bold"><?= htmlspecialchars($customer['name']) ?></h5>
        <div class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></div>
        
        <div class="mt-3 p-3 bg-light rounded text-center">
            <div class="text-muted small">Current Baki / Due</div>
            <h2 class="text-danger fw-bold mb-0">₹<span id="displayDue"><?= number_format($current_due, 2) ?></span></h2>
        </div>
        
        <?php if (get_user_role() === 'admin'): ?>
        <div class="mt-2 text-end">
            <a href="add-due.php?id=<?= $customer_id ?>" class="btn btn-sm btn-outline-warning text-dark border-2 fw-bold"><i class="bi bi-plus-circle me-1"></i> Add Manual Baki</a>
        </div>
        <?php endif; ?>
    </div>

    <form id="receivePaymentForm">
        <input type="hidden" id="customerId" value="<?= $customer_id ?>">
        <input type="hidden" id="maxDue" value="<?= $current_due ?>">
        
        <div class="mb-3">
            <label class="form-label fw-bold">Payment Amount (₹)</label>
            <input type="number" class="form-control form-control-lg fw-bold" id="paymentAmount" step="0.01" max="<?= $current_due ?>" value="<?= $current_due ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Payment Method</label>
            <div class="row g-2">
                <div class="col-4">
                    <input type="radio" class="btn-check" name="paymentMethod" id="payCash" value="cash" checked>
                    <label class="btn btn-outline-primary w-100" for="payCash">Cash</label>
                </div>
                <div class="col-4">
                    <input type="radio" class="btn-check" name="paymentMethod" id="payUpi" value="upi">
                    <label class="btn btn-outline-primary w-100" for="payUpi">UPI</label>
                </div>
                <div class="col-4">
                    <input type="radio" class="btn-check" name="paymentMethod" id="payCard" value="card">
                    <label class="btn btn-outline-primary w-100" for="payCard">Card</label>
                </div>
            </div>
        </div>
        
        <div class="mb-3 d-none" id="txWrapper">
            <label class="form-label fw-bold">Transaction ID (Optional)</label>
            <input type="text" class="form-control" id="transactionId" placeholder="e.g. UTR123456789">
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold">Notes (Optional)</label>
            <textarea class="form-control" id="notes" rows="2" placeholder="Any additional notes..."></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold fs-5 shadow-sm" id="submitBtn">
            <i class="bi bi-check2-circle me-1"></i> Confirm Payment
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const txWrapper = document.getElementById('txWrapper');
            if (this.value === 'upi' || this.value === 'card') {
                txWrapper.classList.remove('d-none');
            } else {
                txWrapper.classList.add('d-none');
                document.getElementById('transactionId').value = '';
            }
        });
    });

    document.getElementById('receivePaymentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('submitBtn');
        const origText = btn.innerHTML;
        const maxDue = parseFloat(document.getElementById('maxDue').value);
        const amount = parseFloat(document.getElementById('paymentAmount').value);
        
        if (amount <= 0 || isNaN(amount)) {
            alert('Please enter a valid amount.');
            return;
        }
        
        if (amount > maxDue) {
            alert('Payment cannot exceed current due of ₹' + maxDue);
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = 'Processing...';
        
        const payload = {
            customer_id: document.getElementById('customerId').value,
            payment_amount: amount,
            payment_method: document.querySelector('input[name="paymentMethod"]:checked').value,
            transaction_id: document.getElementById('transactionId').value,
            notes: document.getElementById('notes').value
        };
        
        try {
            const response = await fetch('../api/credit/receive-payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.success) {
                alert(`₹${amount} payment received successfully.`);
                window.location.href = `customer-ledger.php?id=${payload.customer_id}`;
            } else {
                alert('Payment failed: ' + result.message);
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        } catch(error) {
            console.error(error);
            alert('An error occurred.');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
