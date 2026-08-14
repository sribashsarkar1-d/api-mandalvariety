<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config/database.php'; 

try {
    // You can pass either id or category_id
    $category_id = $_GET['id'] ?? $_GET['category_id'] ?? null;
    
    if (!$category_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID is required.']);
        exit;
    }

    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ? AND p.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    if (strpos($host, 'api.mandal-variety.com') !== false) {
        $uploads_url = "https://mandal-variety.com/admin/uploads/";
    } else {
        $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $project_path = rtrim(preg_replace('/\/api\/.*$/i', '', $script_path), '/');
        $uploads_url = $protocol . "://" . $host . $project_path . "/admin/uploads/";
    }

    foreach ($products as &$product) {
        $images = json_decode($product['images'] ?? '[]', true);
        if (!is_array($images)) $images = [];
        
        $full_images = [];
        foreach ($images as $img) {
            $img = trim($img);
            if (empty($img)) continue;
            if (filter_var($img, FILTER_VALIDATE_URL) || strpos($img, 'http') === 0) {
                $full_images[] = $img;
            } else {
                $full_images[] = $uploads_url . ltrim($img, '/');
            }
        }
        $product['images'] = $full_images;
        
        $product['id'] = (int)$product['id'];
        $product['price'] = number_format((float)($product['price'] ?? 0), 2, '.', '');
        $product['discount_price'] = isset($product['discount_price']) ? number_format((float)$product['discount_price'], 2, '.', '') : null;
        $product['stock_quantity'] = (int)($product['stock_quantity'] ?? 0);
        
        $product['shortDescription'] = $product['short_description'] ?? 'High quality product available at best price.';
        $product['brand'] = $product['brand'] ?? 'Mandal Variety';
        $product['unitLabel'] = $product['unit_label'] ?? $product['unit'] ?? 'pcs';
        $product['couponApplicable'] = isset($product['coupon_applicable']) ? (bool)$product['coupon_applicable'] : true;
        
        $price = $product['price'];
        $discountPrice = $product['discount_price'] ?? 0;
        $discountPercentage = 0;
        if ($price > 0 && $discountPrice > 0 && $discountPrice < $price) {
            $discountPercentage = round((($price - $discountPrice) / $price) * 100);
        }
        $product['discountPercentage'] = isset($product['discount_percentage']) ? (int)$product['discount_percentage'] : $discountPercentage;
        
        $stock = $product['stock_quantity'];
        $product['isInStock'] = isset($product['is_in_stock']) ? (bool)$product['is_in_stock'] : ($stock > 0);
        
        $product['maxOrderQuantity'] = isset($product['max_order_quantity']) ? (int)$product['max_order_quantity'] : 10;
        $product['minOrderQuantity'] = isset($product['min_order_quantity']) ? (int)$product['min_order_quantity'] : 1;
        $product['estimatedDeliveryTime'] = $product['estimated_delivery_time'] ?? '30-45 minutes';
        $product['expiryDate'] = $product['expiry_date'] ?? null;
        $product['manufacturingDate'] = $product['manufacturing_date'] ?? null;
        $product['countryOfOrigin'] = $product['country_of_origin'] ?? 'India';
        $product['deliveryType'] = $product['delivery_type'] ?? 'instant';
        
        $deliveryCharge = isset($product['delivery_charge']) ? (float)$product['delivery_charge'] : 10.00;
        $product['deliveryCharge'] = $deliveryCharge;
        $product['freeDelivery'] = isset($product['free_delivery']) ? (bool)$product['free_delivery'] : ($deliveryCharge == 0);
    }

    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
