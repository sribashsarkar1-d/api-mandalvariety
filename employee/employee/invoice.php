<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    die("Invalid Invoice ID");
}
$sale_id = (int)$_GET['id'];

// Fetch Sale
$stmt = $pdo->prepare("
    SELECT s.*, c.name as customer_name, c.phone as customer_phone, u.name as cashier_name
    FROM employee_sales s
    LEFT JOIN employee_customers c ON s.customer_id = c.id
    LEFT JOIN employee_users u ON s.employee_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch();

if (!$sale) {
    die("Invoice not found.");
}

// Fetch Items
$stmtItem = $pdo->prepare("SELECT * FROM employee_sale_items WHERE sale_id = ?");
$stmtItem->execute([$sale_id]);
$items = $stmtItem->fetchAll();

// Fetch Payments
$stmtPayment = $pdo->prepare("SELECT * FROM employee_payments WHERE sale_id = ? ORDER BY id ASC");
$stmtPayment->execute([$sale_id]);
$payments = $stmtPayment->fetchAll();

// Calculate exact paid today from payments linked to sale
$paid_today = 0;
foreach ($payments as $pay) {
    $paid_today += (float)$pay['amount'];
}

// Fetch exact Ledger records for this sale
$stmtLedger = $pdo->prepare("SELECT * FROM employee_customer_ledger WHERE sale_id = ? ORDER BY id ASC");
$stmtLedger->execute([$sale_id]);
$ledger_records = $stmtLedger->fetchAll();

$previous_due = 0.00;
$total_payable = (float)$sale['grand_total'];
$remaining_due = 0.00;
$has_ledger = false;

if (!empty($ledger_records)) {
    $has_ledger = true;
    foreach ($ledger_records as $rec) {
        if ($rec['transaction_type'] === 'sale_credit') {
            $previous_due = (float)$rec['previous_due'];
            $total_payable = $previous_due + (float)$sale['grand_total'];
            $remaining_due = (float)$rec['new_due']; // Base remaining due before payment
        }
        if ($rec['transaction_type'] === 'payment') {
            $remaining_due = (float)$rec['new_due']; // Final remaining due after payment
        }
    }
} else {
    // Standard sale with no baki interaction
    $remaining_due = $total_payable - $paid_today;
}

$page_title = 'Invoice';
$show_back_btn = true;
$header_action_html = '<i class="bi bi-printer" onclick="window.print()" style="cursor:pointer;" title="Print Invoice"></i>';

require_once '../includes/header.php';
?>
<style>
    body { background-color: var(--bg-color); }
    /* .bottom-nav { display: none !important; } */
    
    .invoice-card {
        background: white;
        padding: 2rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
    }
    .invoice-header {
        text-align: center;
        border-bottom: 1px dashed var(--border-color);
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .invoice-header h4 {
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .invoice-header p {
        color: var(--text-muted);
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    .invoice-details {
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }
    .invoice-details table {
        width: 100%;
    }
    .invoice-details td {
        padding: 0.25rem 0;
    }
    
    .invoice-items th {
        background-color: var(--bg-color);
        font-size: 0.85rem;
        text-transform: uppercase;
        color: var(--text-muted);
    }
    .invoice-items td {
        font-size: 0.95rem;
    }
    
    .invoice-totals {
        margin-top: 1rem;
        border-top: 1px dashed var(--border-color);
        padding-top: 1rem;
    }
    .invoice-totals .row {
        margin-bottom: 0.25rem;
    }
    .invoice-totals .grand-total {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--primary-blue);
        margin-top: 0.5rem;
        border-top: 1px solid var(--border-color);
        padding-top: 0.5rem;
    }
    
    .invoice-footer {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    
    @media print {
        @page { 
            margin: 0; 
            size: 58mm auto; 
        }
        body { 
            margin: 0; 
            padding: 2mm; 
            background: white; 
            color: black !important;
            font-family: monospace;
        }
        
        .invoice-card { 
            box-shadow: none; 
            border: none; 
            padding: 0; 
            margin: 0; 
            width: 100%;
        }
        
        .app-header, .no-print, .bottom-nav { display: none !important; }
        
        .main-wrapper, .main-content { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
        }

        .invoice-header {
            border-bottom: 1px dashed black;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .invoice-header h4 { font-size: 16px; font-weight: bold; margin-bottom: 2px; color: black !important; }
        .invoice-header p { font-size: 11px; margin-bottom: 0; color: black !important; }
        
        .invoice-details { font-size: 11px; margin-bottom: 10px; color: black !important; }
        .invoice-details td { padding: 1px 0; }
        
        .invoice-items { font-size: 11px; width: 100%; margin-bottom: 10px; color: black !important; }
        .invoice-items th { font-size: 10px; border-bottom: 1px dashed black; padding-bottom: 2px; color: black !important; background: transparent; }
        .invoice-items td { padding: 2px 0; border: none; }
        
        .invoice-totals { border-top: 1px dashed black !important; margin-top: 5px; padding-top: 5px; }
        .invoice-totals .d-flex { margin-bottom: 2px; }
        .invoice-totals span { font-size: 11px; color: black !important; }
        .invoice-totals .fw-bold span { font-size: 12px; }
        
        .dashed, .border-top { border-top: 1px dashed black !important; }
        
        .grand-total { border-top: 1px solid black !important; padding-top: 3px; }
        .grand-total span { font-size: 13px !important; font-weight: bold; color: black !important; }
        
        .text-muted, .text-danger, .text-success, .text-primary, .text-warning, .text-dark { 
            color: black !important; 
        }
        
        .invoice-footer { margin-top: 10px; font-size: 10px; }
        .invoice-footer p { margin-bottom: 2px; }
    }
</style>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="invoice-card">
            
            <div class="invoice-header">
                <h4><?= SITE_NAME ?></h4>
                <p>Balarampu , sarayer par</p>
                <p>Phone: +91 8967136033</p>
            </div>
            
            <div class="invoice-details">
                <table>
                    <tr>
                        <td class="fw-bold" width="35%">Invoice #</td>
                        <td>: <?= htmlspecialchars($sale['invoice_number']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Date</td>
                        <td>: <?= format_date($sale['created_at']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Cashier</td>
                        <td>: <?= htmlspecialchars($sale['cashier_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Customer</td>
                        <td>: <?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></td>
                    </tr>
                    <?php if ($sale['customer_phone']): ?>
                    <tr>
                        <td class="fw-bold">Phone</td>
                        <td>: <?= htmlspecialchars($sale['customer_phone']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <table class="table table-borderless invoice-items mb-0">
                <thead>
                    <tr>
                        <th width="45%">Item</th>
                        <th class="text-center" width="15%">Qty</th>
                        <th class="text-end" width="20%">Price</th>
                        <th class="text-end" width="20%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($item['product_name']) ?></div>
                            <?php if ($item['discount'] > 0): ?>
                                <small class="text-success">Disc: <?= format_currency($item['discount']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= (float)$item['quantity'] ?></td>
                        <td class="text-end"><?= format_currency($item['unit_price']) ?></td>
                        <td class="text-end fw-bold"><?= format_currency($item['total_price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="invoice-totals">
                <div class="d-flex justify-content-between text-muted small">
                    <span>Subtotal</span>
                    <span><?= format_currency($sale['subtotal']) ?></span>
                </div>
                <?php if ($sale['discount'] > 0): ?>
                <div class="d-flex justify-content-between text-muted small">
                    <span>Discount</span>
                    <span><?= format_currency($sale['discount']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($sale['gst_amount'] > 0): ?>
                <div class="d-flex justify-content-between text-muted small">
                    <span>GST</span>
                    <span><?= format_currency($sale['gst_amount']) ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between fw-bold mt-1 text-dark">
                    <span>Today's Bill</span>
                    <span><?= format_currency($sale['grand_total']) ?></span>
                </div>
            </div>
            
            <?php if ($has_ledger): ?>
            <div class="mt-3 pt-3 border-top dashed">
                <h6 class="fw-bold mb-2 text-muted" style="font-size: 0.8rem; text-transform: uppercase;">Customer Credit</h6>
                <div class="d-flex justify-content-between text-muted">
                    <span>Previous Baki</span>
                    <span><?= format_currency($previous_due) ?></span>
                </div>
                <div class="d-flex justify-content-between text-muted">
                    <span>Today's Bill</span>
                    <span><?= format_currency($sale['grand_total']) ?></span>
                </div>
                <div class="d-flex justify-content-between grand-total">
                    <span>Total Payable</span>
                    <span><?= format_currency($total_payable) ?></span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2 text-success">
                    <span>Paid Today</span>
                    <span><?= format_currency($paid_today) ?></span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-1 text-danger">
                    <span>Remaining Baki</span>
                    <span><?= format_currency($remaining_due) ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mt-3 pt-3 border-top dashed">
                <h6 class="fw-bold mb-2 text-muted" style="font-size: 0.8rem; text-transform: uppercase;">Payment Details</h6>
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-bold">Payment Method:</span>
                    <span class="text-capitalize"><?= htmlspecialchars($sale['payment_method']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-bold">Payment Status:</span>
                    <?php 
                    $statusColor = 'text-success';
                    if ($sale['payment_status'] === 'pending') $statusColor = 'text-danger';
                    if ($sale['payment_status'] === 'partial') $statusColor = 'text-warning';
                    ?>
                    <span class="text-capitalize fw-bold <?= $statusColor ?>"><?= htmlspecialchars($sale['payment_status']) ?></span>
                </div>
            </div>
            
            <div class="invoice-footer">
                <p class="mb-1 fw-bold">Thank you for shopping with us!</p>
                <p class="mb-0">Visit Again <span class="text-danger">❤</span></p>
            </div>
            
        </div>
        
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-primary w-50" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print</button>
            <a href="pos.php" class="btn btn-primary w-50"><i class="bi bi-cart-plus me-2"></i>New Bill</a>
        </div>
        
    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
?>
<?php if (isset($_GET['auto_print']) && $_GET['auto_print'] == '1'): ?>
<script>
window.addEventListener('load', function() {
    window.print();
});
</script>
<?php endif; ?>
