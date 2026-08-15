<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../includes/functions.php';

// Must be logged in
if (!is_logged_in()) {
    json_response(false, 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    json_response(false, 'Cart is empty');
}

$employee_id = $_SESSION['user_id'];
$customer_id = !empty($input['customer_id']) ? $input['customer_id'] : null;
$payment_method = sanitize_input($input['payment_method'] ?? 'cash');
$transaction_id = sanitize_input($input['transaction_id'] ?? '');

$valid_payments = ['cash', 'upi', 'card', 'mixed', 'credit'];
if (!in_array($payment_method, $valid_payments)) {
    $payment_method = 'cash';
}

$items = $input['items'];
$invoice_number = generate_invoice_number($pdo);

try {
    $pdo->beginTransaction();

    $subtotal = 0;
    $total_discount = 0;
    $total_gst_amount = 0;
    
    // Arrays to hold prepared data for inserts
    $sale_items_data = [];
    $stock_updates = [];
    
    // 1. Validate and calculate items against DB (Ignore frontend prices)
    foreach ($items as $item) {
        $product_id = (int)$item['id'];
        $requested_qty = (float)$item['qty'];
        
        if ($requested_qty <= 0) continue;
        
        // Fetch product and stock
        $stmt = $pdo->prepare("
            SELECT p.name, p.unit, p.selling_price, p.discount, p.gst_percent, p.status, p.minimum_stock,
                   s.quantity as current_stock
            FROM employee_products p
            LEFT JOIN employee_product_stock s ON p.id = s.product_id
            WHERE p.id = ? FOR UPDATE
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product || $product['status'] !== 'active') {
            throw new Exception("Product ID {$product_id} is unavailable.");
        }
        
        if ($product['current_stock'] < $requested_qty) {
            throw new Exception("Insufficient stock for {$product['name']}. Available: {$product['current_stock']} {$product['unit']}");
        }
        
        // Calculations
        $unit_price = (float)$product['selling_price'];
        $item_discount = (float)$product['discount'];
        $gst_percent = (float)$product['gst_percent'];
        
        $price_after_discount = $unit_price - $item_discount;
        $gst_amount_per_unit = ($price_after_discount * $gst_percent) / 100;
        
        $line_total = ($price_after_discount + $gst_amount_per_unit) * $requested_qty;
        
        $subtotal += ($unit_price * $requested_qty);
        $total_discount += ($item_discount * $requested_qty);
        $total_gst_amount += ($gst_amount_per_unit * $requested_qty);
        
        $sale_items_data[] = [
            'product_id' => $product_id,
            'product_name' => $product['name'],
            'quantity' => $requested_qty,
            'unit' => $product['unit'],
            'unit_price' => $unit_price,
            'discount' => $item_discount * $requested_qty,
            'gst_percent' => $gst_percent,
            'gst_amount' => $gst_amount_per_unit * $requested_qty,
            'total_price' => $line_total
        ];
        
        $stock_updates[] = [
            'product_id' => $product_id,
            'deduct_qty' => $requested_qty,
            'new_stock' => $product['current_stock'] - $requested_qty,
            'min_stock' => $product['minimum_stock'] ?? 0
        ];
    }
    
    $grand_total = $subtotal - $total_discount + $total_gst_amount;
    
    // 2. Insert Sale
    $stmt = $pdo->prepare("
        INSERT INTO employee_sales 
        (invoice_number, customer_id, employee_id, subtotal, discount, gst_amount, grand_total, payment_method, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $payment_status = ($payment_method === 'credit') ? 'pending' : 'paid';
    
    $stmt->execute([
        $invoice_number, 
        $customer_id, 
        $employee_id, 
        $subtotal, 
        $total_discount, 
        $total_gst_amount, 
        $grand_total, 
        $payment_method, 
        $payment_status
    ]);
    
    $sale_id = $pdo->lastInsertId();
    
    // 3. Insert Sale Items
    $stmtItem = $pdo->prepare("
        INSERT INTO employee_sale_items
        (sale_id, product_id, product_name, quantity, unit, unit_price, discount, gst_percent, gst_amount, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($sale_items_data as $si) {
        $stmtItem->execute([
            $sale_id,
            $si['product_id'],
            $si['product_name'],
            $si['quantity'],
            $si['unit'],
            $si['unit_price'],
            $si['discount'],
            $si['gst_percent'],
            $si['gst_amount'],
            $si['total_price']
        ]);
    }
    
    // 4. Insert Payment if not credit
    if ($payment_method !== 'credit') {
        $stmtPayment = $pdo->prepare("
            INSERT INTO employee_payments (sale_id, amount, payment_method, transaction_id, payment_status)
            VALUES (?, ?, ?, ?, 'success')
        ");
        $stmtPayment->execute([$sale_id, $grand_total, $payment_method, $transaction_id]);
    }
    
    // 5. Update Stock and Record Movement
    $stmtStockUpdate = $pdo->prepare("UPDATE employee_product_stock SET quantity = ?, stock_status = ? WHERE product_id = ?");
    $stmtMovement = $pdo->prepare("
        INSERT INTO employee_stock_movements (product_id, employee_id, movement_type, quantity, reference_id, note)
        VALUES (?, ?, 'sale', ?, ?, ?)
    ");
    
    foreach ($stock_updates as $su) {
        // Determine stock status
        $status = 'in_stock';
        if ($su['new_stock'] <= 0) {
            $status = 'out_of_stock';
        } elseif ($su['new_stock'] <= $su['min_stock']) {
            $status = 'low_stock';
        }
        
        $stmtStockUpdate->execute([$su['new_stock'], $status, $su['product_id']]);
        
        $note = "Sale Invoice: " . $invoice_number;
        $stmtMovement->execute([
            $su['product_id'],
            $employee_id,
            $su['deduct_qty'], // quantity moved (out)
            $sale_id,
            $note
        ]);
    }
    
    // 6. Update Customer Stats if customer exists
    if ($customer_id) {
        $pdo->prepare("
            UPDATE employee_customers 
            SET total_purchase = total_purchase + ?, total_bills = total_bills + 1
            WHERE id = ?
        ")->execute([$grand_total, $customer_id]);
    }

    $pdo->commit();
    
    json_response(true, 'Checkout successful', ['sale_id' => $sale_id, 'invoice_number' => $invoice_number]);

} catch (Exception $e) {
    $pdo->rollBack();
    json_response(false, $e->getMessage());
}
?>
