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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
