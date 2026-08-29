<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin(); // admins can view customers

$page_title = 'Customers';

// Fetch customers with their current due
$stmt = $pdo->query("
    SELECT c.*, 
    (
        SELECT new_due 
        FROM employee_customer_ledger l 
        WHERE l.customer_id = c.id 
        ORDER BY l.created_at DESC, l.id DESC 
        LIMIT 1
    ) as current_due
    FROM employee_customers c 
    ORDER BY c.name ASC
");
$customers = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden flex-grow-1 me-3">
        <span class="input-group-text bg-white border-end-0 border-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="customerSearchInput" class="form-control border-start-0 border-0 bg-white shadow-none" placeholder="Search by name or mobile number...">
    </div>
    <button class="btn btn-primary btn-lg shadow-sm rounded-4 px-4" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="bi bi-person-plus-fill me-2"></i>Add Customer
    </button>
</div>

<div class="row g-2">
    <?php if (empty($customers)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No customers found</p>
        </div>
    <?php else: ?>
        <?php foreach ($customers as $c): ?>
        <div class="col-12 col-md-6 col-lg-4 customer-card-wrapper" data-name="<?= strtolower(htmlspecialchars($c['name'])) ?>" data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>">
            <div class="custom-card p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($c['name']) ?></h6>
                        <div class="text-muted small">📞 <?= htmlspecialchars($c['phone'] ?? 'N/A') ?></div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                    <div>
                        <div class="text-muted small">Current Baki</div>
                        <?php 
                        $due = (float)($c['current_due'] ?? 0);
                        if ($due > 0): ?>
                            <div class="fw-bold text-danger fs-5"><i class="bi bi-exclamation-circle-fill me-1"></i> <?= format_currency($due) ?></div>
                        <?php else: ?>
                            <div class="fw-bold text-success fs-5"><i class="bi bi-check-circle-fill me-1"></i> CLEAR</div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end text-muted small">
                        <div>Purchase: <?= format_currency($c['total_purchase']) ?></div>
                        <div><?= $c['total_bills'] ?> Bills</div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="../employee/pos.php?customer_id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1"><i class="bi bi-cart-plus me-1"></i>New Bill</a>
                    <a href="../employee/customer-ledger.php?id=<?= $c['id'] ?>" class="btn btn-outline-secondary btn-sm flex-grow-1"><i class="bi bi-journal-text me-1"></i>Ledger</a>
                    <a href="../employee/receive-payment.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-cash-coin me-1"></i>Pay</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="newCustName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" class="form-control" id="newCustPhone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address (Optional)</label>
                        <textarea class="form-control" id="newCustAddress" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opening Due ₹ (Optional)</label>
                        <input type="number" class="form-control" id="newCustOpeningDue" step="0.01" min="0">
                        <small class="text-muted">Previous balance before using the system.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.customer-card-wrapper');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const phone = card.getAttribute('data-phone');
                if (name.includes(term) || phone.includes(term)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    const addCustomerForm = document.getElementById('addCustomerForm');
    if (addCustomerForm) {
        addCustomerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Saving...';
            
            const name = document.getElementById('newCustName').value;
            const phone = document.getElementById('newCustPhone').value;
            const address = document.getElementById('newCustAddress').value;
            const opening_due = parseFloat(document.getElementById('newCustOpeningDue').value) || 0;
            
            try {
                const response = await fetch('../api/customers/create.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ name, phone, address, opening_due })
                });
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to add customer');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (err) {
                alert('An error occurred');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }
});
</script>
<?php $extra_js = ob_get_clean(); ?>

<?php require_once '../includes/footer.php'; ?>
