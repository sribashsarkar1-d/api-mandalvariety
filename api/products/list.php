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
    list($parentToLegacy, $legacyToParent) = getCategoryMappings($pdo);

    $legacy_category_id = $_GET['category_id'] ?? null;
    $parent_category_id = $_GET['parent_category_id'] ?? null;
    $subcategory_id = $_GET['subcategory_id'] ?? $_GET['child_category_id'] ?? null;

    if ($parent_category_id !== null) {
        $search_category_id = getLegacyCategoryId($pdo, (int)$parent_category_id) ?: (int)$parent_category_id;
    } else {
        $search_category_id = $legacy_category_id ? (int)$legacy_category_id : null;
    }

    $whereClauses = ["p.is_active = 1"];
    $whereParams = [];

    if ($search_category_id) {
        $whereClauses[] = "p.category_id = ?";
        $whereParams[] = $search_category_id;
    }
    if ($subcategory_id !== null && is_numeric($subcategory_id) && (int)$subcategory_id > 0) {
        $whereClauses[] = "p.subcategory_id = ?";
        $whereParams[] = (int)$subcategory_id;
    }

    $where = "WHERE " . implode(" AND ", $whereClauses);
    
    // Select all fields (p.*) to ensure frontend models map correctly just like in detail.php
    $sql = "SELECT p.*, c.name as category_name, cc.name as child_category_name,
            (SELECT COALESCE(AVG(rating), 0) FROM reviews r WHERE r.product_id = p.id AND r.status = 'approved') as rating,
            (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.status = 'approved') as reviewCount
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN child_categories cc ON p.subcategory_id = cc.id 
            $where";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($whereParams);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Safely determine project base path
    if (strpos($host, 'api.mandal-variety.com') !== false) {
        $uploads_url = "https://mandal-variety.com/admin/uploads/";
    } else {
        $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $project_path = rtrim(preg_replace('/\/api\/.*$/i', '', $script_path), '/');
        $uploads_url = $protocol . "://" . $host . $project_path . "/admin/uploads/";
    }

    foreach ($products as &$product) {
        $product['parent_category_id'] = isset($product['category_id']) ? getParentCategoryFromLegacy($pdo, (int)$product['category_id']) : null;
        $product['parent_category_name'] = $product['category_name'] ?? null;
        $product['subcategory_id'] = $product['subcategory_id'] ? (int)$product['subcategory_id'] : null;
        $product['child_category_name'] = $product['child_category_name'] ?? null;

        $images = json_decode($product['images'] ?? '[]', true);
        if (!is_array($images)) $images = [];
        
        $full_images = [];
        foreach ($images as $img) {
            $img = trim($img);
            if (empty($img)) continue;
            // If it's already a full URL, keep it. Otherwise prepend uploads_url
            if (filter_var($img, FILTER_VALIDATE_URL) || strpos($img, 'http') === 0) {
                $full_images[] = $img;
            } else {
                $full_images[] = $uploads_url . ltrim($img, '/');
            }
        }
        $product['images'] = $full_images;
            
        // Ensure proper types and add camelCase fallback fields to match detail.php
        $product['id'] = (int)$product['id'];
        $product['price'] = number_format((float)($product['price'] ?? 0), 2, '.', '');
        $product['discount_price'] = isset($product['discount_price']) ? number_format((float)$product['discount_price'], 2, '.', '') : null;
        
        $stockQty = max(0, (int)($product['stock_quantity'] ?? 0));
        $product['stock_quantity'] = $stockQty;
        $product['stock'] = $stockQty;
        $product['available_stock'] = $stockQty;
        
        $product['shortDescription'] = $product['short_description'] ?? 'High quality product available at best price.';
        $product['brand'] = $product['brand'] ?? 'Mandal Variety';
        $product['unitLabel'] = $product['unit_label'] ?? $product['unit'] ?? 'pcs';
        $product['couponApplicable'] = isset($product['coupon_applicable']) ? (bool)$product['coupon_applicable'] : true;
        
        $price = (float)$product['price'];
        $discountPrice = (float)($product['discount_price'] ?? 0);
        $discountPercentage = 0;
        if ($price > 0 && $discountPrice > 0 && $discountPrice < $price) {
            $discountPercentage = round((($price - $discountPrice) / $price) * 100);
        }
        $product['discountPercentage'] = isset($product['discount_percentage']) ? (int)$product['discount_percentage'] : $discountPercentage;
        
        $isActive = ((int)($product['is_active'] ?? 1) === 1) && (($product['status'] ?? 'published') !== 'inactive');
        $product['isInStock'] = $isActive && ($stockQty > 0);
        $product['is_in_stock'] = $product['isInStock'];
        
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
        
        $product['rating'] = isset($product['rating']) ? round((float)$product['rating'], 1) : 0;
        $product['reviewCount'] = isset($product['reviewCount']) ? (int)$product['reviewCount'] : 0;
    }

    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
