<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Categories';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize_input($_POST['name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO employee_categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = "Category added successfully.";
        } catch (PDOException $e) {
            $error = "Error: Category name might already exist.";
        }
    }
}

// Fetch categories
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM employee_categories c 
    LEFT JOIN employee_products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

$header_action_html = '<button class="btn btn-sm btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-lg"></i> Add</button>';

require_once '../includes/header.php';
?>

<div class="row g-3">
    <?php if (isset($success)): ?>
        <div class="col-12"><div class="alert alert-success"><?= $success ?></div></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="col-12"><div class="alert alert-danger"><?= $error ?></div></div>
    <?php endif; ?>

    <?php foreach ($categories as $c): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="custom-card p-3 d-flex align-items-center">
            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                <i class="bi bi-tags"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($c['name']) ?></h6>
                <div class="text-muted small"><?= $c['product_count'] ?> Products</div>
            </div>
            <div>
                <span class="badge <?= $c['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($c['status']) ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-0">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Category Name *</label>
                        <input type="text" name="name" class="form-control" required>
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
