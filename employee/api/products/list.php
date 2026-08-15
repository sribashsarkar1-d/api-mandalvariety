<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : 'all';

$query = "
    SELECT 
        p.id, p.name, p.sku, p.barcode, p.image, p.selling_price, p.discount, p.unit, p.expiry_date,
        COALESCE(s.quantity, 0) as stock, s.stock_status
    FROM employee_products p
    LEFT JOIN employee_product_stock s ON p.id = s.product_id
    WHERE p.status = 'active'
";

$params = [];

if ($category !== 'all' && !empty($category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY p.name ASC LIMIT 50";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Format data for frontend
    foreach ($products as &$p) {
        $discount = isset($p['discount']) ? (float)$p['discount'] : 0;
        $final_price = $p['selling_price'] - $discount;
        $p['formatted_price'] = format_currency($final_price);
        if ($p['expiry_date']) {
            $p['formatted_expiry'] = date('d-m-Y', strtotime($p['expiry_date']));
        } else {
            $p['formatted_expiry'] = 'N/A';
        }
        
        // Handle images
        if (empty($p['image'])) {
            $p['image_url'] = BASE_URL . '/assets/images/no-image.png';
        } else {
            $p['image_url'] = BASE_URL . '/uploads/products/' . $p['image'];
        }
    }
    
    json_response(true, 'Products loaded', $products);
} catch (PDOException $e) {
    json_response(false, 'Database error');
}
?>
