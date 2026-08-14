<?php
class SearchService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function normalize($string) {
        if (!$string) return '';
        $string = strtolower(trim($string));
        return preg_replace('/[^a-z0-9\s-]/', '', $string);
    }

    public function logSearch($query, $normalized, $count, $type, $user_id = null) {
        if (empty($normalized)) return;
        $stmt = $this->pdo->prepare("INSERT INTO search_logs (user_id, query, normalized_query, result_count, search_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $query, $normalized, $count, $type]);
    }

    public function globalSearch($q, $limit = 20, $offset = 0, $filters = [], $sort = 'relevance') {
        $where = ["p.is_active = 1"];
        $params = [];
        
        if (!empty($q)) {
            $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR p.sku LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }
        if (isset($filters['in_stock']) && $filters['in_stock'] !== '') {
            $where[] = $filters['in_stock'] == 1 ? "p.stock_quantity > 0" : "p.stock_quantity = 0";
        }

        $whereSql = implode(" AND ", $where);
        
        $orderSql = "ORDER BY p.id DESC";
        if (!empty($q) && $sort === 'relevance') {
            $orderSql = "ORDER BY 
                CASE 
                    WHEN p.name = ? THEN 1
                    WHEN p.name LIKE ? THEN 2
                    WHEN p.name LIKE ? THEN 3
                    WHEN c.name LIKE ? THEN 4
                    ELSE 5 
                END ASC";
            array_push($params, $q, "$q%", "%$q%", "%$q%");
        } elseif ($sort === 'latest') {
            $orderSql = "ORDER BY p.created_at DESC";
        } elseif ($sort === 'price_low_high') {
            $orderSql = "ORDER BY p.price ASC";
        } elseif ($sort === 'price_high_low') {
            $orderSql = "ORDER BY p.price DESC";
        }

        array_push($params, (int)$limit, (int)$offset);

        $sql = "SELECT DISTINCT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE $whereSql 
                $orderSql 
                LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $i => $val) {
            $stmt->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $this->formatProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function searchCategories($q, $limit = 10) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE name LIKE ? ORDER BY name ASC LIMIT ?");
        $stmt->bindValue(1, "%$q%");
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPopularSearches($limit = 10) {
        $stmt = $this->pdo->query("SELECT normalized_query, COUNT(*) as count FROM search_logs WHERE result_count > 0 GROUP BY normalized_query ORDER BY count DESC LIMIT " . (int)$limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClosestMatch($q) {
        $stmt = $this->pdo->query("SELECT name FROM products WHERE is_active = 1 LIMIT 5000");
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $closest = null;
        $shortest = -1;

        foreach ($names as $name) {
            $lev = levenshtein(strtolower($q), strtolower($name));
            if ($lev == 0) return $name;
            if ($lev <= $shortest || $shortest < 0) {
                $closest  = $name;
                $shortest = $lev;
            }
        }
        
        if ($shortest <= 3) return $closest;
        
        $stmt = $this->pdo->prepare("SELECT name FROM products WHERE SOUNDEX(name) = SOUNDEX(?) LIMIT 1");
        $stmt->execute([$q]);
        $soundexMatch = $stmt->fetchColumn();
        if ($soundexMatch) return $soundexMatch;

        return null;
    }

    public function getRelatedProductsByCategoryFallback($limit = 5) {
        $stmt = $this->pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY RAND() LIMIT " . (int)$limit);
        return $this->formatProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function formatProducts(array $products) {
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
        return $products;
    }
}
