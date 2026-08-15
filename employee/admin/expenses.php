<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Expenses';

// Handle Add Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize_input($_POST['title']);
    $amount = (float)$_POST['amount'];
    $cat = sanitize_input($_POST['expense_category']);
    $date = sanitize_input($_POST['expense_date']);
    $note = sanitize_input($_POST['note']);
    $emp = $_SESSION['user_id'];
    
    if (!empty($title) && $amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO employee_expenses (employee_id, title, amount, expense_category, expense_date, note) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$emp, $title, $amount, $cat, $date, $note]);
        $success = "Expense added successfully.";
    }
}

// Fetch expenses
$stmt = $pdo->query("SELECT * FROM employee_expenses ORDER BY expense_date DESC LIMIT 50");
$expenses = $stmt->fetchAll();

$header_action_html = '<button class="btn btn-sm btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="bi bi-plus-lg"></i> Add</button>';

require_once '../includes/header.php';
?>

<div class="row g-3">
    <?php if (isset($success)): ?>
        <div class="col-12"><div class="alert alert-success"><?= $success ?></div></div>
    <?php endif; ?>

    <?php if (empty($expenses)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-cash-stack text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No expenses found</p>
        </div>
    <?php else: ?>
        <?php foreach ($expenses as $e): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="custom-card p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-danger me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-arrow-down-right"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($e['title']) ?></h6>
                    <div class="text-muted small"><?= format_date($e['expense_date']) ?> • <?= htmlspecialchars(ucfirst($e['expense_category'])) ?></div>
                </div>
                <div class="text-end fw-bold text-danger">
                    <?= format_currency($e['amount']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-0">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Amount *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Category</label>
                            <select name="expense_category" class="form-select">
                                <option value="salary">Salary</option>
                                <option value="rent">Rent</option>
                                <option value="utility">Utility</option>
                                <option value="inventory">Inventory</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Date</label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
