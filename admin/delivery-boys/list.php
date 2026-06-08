<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$success = '';
$error = '';

// Handle activation/deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $delivery_id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action === 'toggle_active') {
        $stmt = $conn->prepare("UPDATE delivery_boys SET is_active = NOT is_active WHERE id = ?");
        if ($stmt->execute([$delivery_id])) {
            $success = 'Delivery Boy status updated successfully.';
        } else {
            $error = 'Failed to update status.';
        }
    }
}

// Fetch all delivery boys
$stmt = $conn->query("
    SELECT * FROM delivery_boys 
    ORDER BY created_at DESC
");
$delivery_boys = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="w-100 orders-page">
    <?php include '../includes/topbar.php'; ?>

    <style>
        .orders-page {
            background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding-bottom: 30px;
        }

        .page-header-premium {
            background: #ffffff;
            border-radius: 20px;
            padding: 22px 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            border: 1px solid #eaf0f6;
            margin-bottom: 25px;
        }

        .page-title {
            font-weight: 800;
            font-size: 1.8rem;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: #64748b;
            margin: 0;
            font-size: .95rem;
        }

        .table-card {
            border: 1px solid #e9eef5;
            border-radius: 20px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
            background: #fff;
            overflow: hidden;
        }

        .table-card-header {
            padding: 20px;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .table-title {
            font-weight: 800;
            color: #0f172a;
            font-size: 1.1rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #0f172a !important;
            color: #fff !important;
            border: none !important;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 14px 12px;
            border-top: 1px solid #eef2f7;
        }
        
        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>

    <div class="container-fluid mt-4">
        
        <div class="page-header-premium">
            <h4 class="page-title">Delivery Partners</h4>
            <p class="page-subtitle">Manage delivery personnel, verify vehicles, and update access.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success rounded-3 small"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger rounded-3 small"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-card-header">
                <div class="table-title">Registered Partners (<?= count($delivery_boys) ?>)</div>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Partner Info</th>
                            <th>Contact</th>
                            <th>Vehicle</th>
                            <th>Availability</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($delivery_boys)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No delivery partners found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($delivery_boys as $boy): ?>
                                <tr>
                                    <td>#<?= e($boy['id']) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($boy['name']) ?></div>
                                        <div class="small text-muted">Joined <?= date('M d, Y', strtotime($boy['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <div><i class="fa-solid fa-envelope text-muted me-1"></i> <?= e($boy['email']) ?></div>
                                        <div><i class="fa-solid fa-phone text-muted me-1"></i> <?= e($boy['phone']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($boy['vehicle_number']) ?></div>
                                        <div class="small text-muted"><?= e($boy['vehicle_type']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ((int)$boy['is_available'] === 1): ?>
                                            <span class="badge-status bg-success bg-opacity-10 text-success">Online</span>
                                        <?php else: ?>
                                            <span class="badge-status bg-secondary bg-opacity-10 text-secondary">Offline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)$boy['is_active'] === 1): ?>
                                            <span class="badge-status text-bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge-status text-bg-danger">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?= e($boy['id']) ?>">
                                            <input type="hidden" name="action" value="toggle_active">
                                            <?php if ((int)$boy['is_active'] === 1): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold rounded-pill" onclick="return confirm('Are you sure you want to suspend this partner?');">Suspend</button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success fw-bold rounded-pill">Activate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>
