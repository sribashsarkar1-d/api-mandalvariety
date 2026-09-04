<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_employee();

// Check if user is admin (optional, user said "admin jodi chay", but we can allow all or just admin. I will allow admin check if required, but user role check might be needed. Let's allow same as receive-payment but we can add a check)
if (get_user_role() !== 'admin') {
    die("Only admin can manually add dues.");
}

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

$page_title = 'Add Manual Due';
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
    </div>

    <form id="addDueForm">
        <input type="hidden" id="customerId" value="<?= $customer_id ?>">
        
        <div class="mb-3">
            <label class="form-label fw-bold">Add New Due Amount (₹)</label>
            <input type="number" class="form-control form-control-lg fw-bold" id="dueAmount" step="0.01" min="1" placeholder="e.g. 500" required>
            <small class="text-muted">This amount will be added to the current due.</small>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold">Notes (Reason for adding due)</label>
            <textarea class="form-control" id="notes" rows="2" placeholder="e.g. Offline purchase, forgot to bill earlier..." required></textarea>
        </div>
        
        <button type="submit" class="btn btn-warning w-100 py-3 fw-bold fs-5 shadow-sm" id="submitBtn">
            <i class="bi bi-plus-circle me-1"></i> Add Due
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addDueForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('submitBtn');
        const origText = btn.innerHTML;
        const amount = parseFloat(document.getElementById('dueAmount').value);
        
        if (amount <= 0 || isNaN(amount)) {
            alert('Please enter a valid amount.');
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = 'Processing...';
        
        const payload = {
            customer_id: document.getElementById('customerId').value,
            amount: amount,
            notes: document.getElementById('notes').value
        };
        
        try {
            const response = await fetch('../api/credit/add-due.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.success) {
                alert(`₹${amount} due added successfully.`);
                window.location.href = `customer-ledger.php?id=${payload.customer_id}`;
            } else {
                alert('Failed: ' + result.message);
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
