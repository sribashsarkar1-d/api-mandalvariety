<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Get ID from ANY source - super flexible
$id = $_GET['id'] ?? $_REQUEST['id'] ?? 1; // Default to 1 for testing

// Basic validation - NO strict checks
if (!is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Bad ID: ' . $id]);
    exit;
}

try {
    // Simple query for your mondal-vr schema
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Fix images JSON and prepend full URL
        $images = json_decode($product['images'] ?? '[]', true);
        if (!is_array($images)) $images = [];
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_dir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base_dir = rtrim(str_replace('\\', '/', $base_dir), '/');
        $uploads_url = $protocol . "://" . $host . $base_dir . "/uploads/";

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
        
        // 1. shortDescription
        $product['shortDescription'] = $product['short_description'] ?? 'High quality product available at best price.';
        
        // 2. brand
        $product['brand'] = $product['brand'] ?? 'Mandal Variety';
        
        // 3. unitLabel
        $product['unitLabel'] = $product['unit_label'] ?? $product['unit'] ?? 'pcs';
        
        // 4. couponApplicable
        $product['couponApplicable'] = isset($product['coupon_applicable']) ? (bool)$product['coupon_applicable'] : true;
        
        // 5. discountPercentage
        $price = (float)($product['price'] ?? 0);
        $discountPrice = (float)($product['discount_price'] ?? 0);
        $discountPercentage = 0;
        if ($price > 0 && $discountPrice > 0 && $discountPrice < $price) {
            $discountPercentage = round((($price - $discountPrice) / $price) * 100);
        }
        $product['discountPercentage'] = isset($product['discount_percentage']) ? (int)$product['discount_percentage'] : $discountPercentage;
        
        // 6. isInStock
        $stock = (int)($product['stock_quantity'] ?? 0);
        $product['isInStock'] = isset($product['is_in_stock']) ? (bool)$product['is_in_stock'] : ($stock > 0);
        
        // 7. maxOrderQuantity
        $product['maxOrderQuantity'] = isset($product['max_order_quantity']) ? (int)$product['max_order_quantity'] : 10;
        
        // 8. minOrderQuantity
        $product['minOrderQuantity'] = isset($product['min_order_quantity']) ? (int)$product['min_order_quantity'] : 1;
        
        // 9. estimatedDeliveryTime
        $product['estimatedDeliveryTime'] = $product['estimated_delivery_time'] ?? '30-45 minutes';
        
        // 10. expiryDate
        $product['expiryDate'] = $product['expiry_date'] ?? null;
        
        // 11. manufacturingDate
        $product['manufacturingDate'] = $product['manufacturing_date'] ?? null;
        
        // 12. countryOfOrigin
        $product['countryOfOrigin'] = $product['country_of_origin'] ?? 'India';
        
        // 13. deliveryType
        $product['deliveryType'] = $product['delivery_type'] ?? 'instant';
        
        // 14. deliveryCharge
        $deliveryCharge = isset($product['delivery_charge']) ? (float)$product['delivery_charge'] : 10.00;
        $product['deliveryCharge'] = $deliveryCharge;
        
        // 15. freeDelivery
        $product['freeDelivery'] = isset($product['free_delivery']) ? (bool)$product['free_delivery'] : ($deliveryCharge == 0);

        echo json_encode(['success' => true, 'data' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No product ID: ' . $id]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
