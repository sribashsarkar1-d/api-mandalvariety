<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

$id = $_GET['id'] ?? null;

$data = json_decode(file_get_contents("php://input"), true);

    $parentToLegacy = [1 => 52, 2 => 53, 3 => 54, 4 => 55];
    $legacyToParent = [52 => 1, 53 => 2, 54 => 3, 55 => 4];

    function validateSubcategory($pdo, $subcategory_id, $parent_id) {
        if (!$subcategory_id) return true;
        if (!$parent_id) return false;
        
        $checkChild = $pdo->prepare("SELECT id FROM child_categories WHERE id = ? AND parent_category_id = ?");
        $checkChild->execute([$subcategory_id, $parent_id]);
        return (bool)$checkChild->fetch();
    }

if ($method === 'POST') {
    // Create
    $name = trim($data['name'] ?? '');
    $price = $data['price'] ?? 0;
    $sku = $data['sku'] ?? 'SKU-' . time();
    $slug = strtolower(str_replace(' ', '-', $name)) . '-' . time();
    $stock_quantity = $data['stock_quantity'] ?? 0;

    $category_id = $data['category_id'] ?? 1; // Default fallback
    $parent_id = null;
    
    if (isset($data['parent_category_id'])) {
        $parent_id = (int)$data['parent_category_id'];
        if (isset($parentToLegacy[$parent_id])) {
            $category_id = $parentToLegacy[$parent_id];
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parent_category_id']);
            exit;
        }
    } else {
        $parent_id = $legacyToParent[$category_id] ?? null;
    }

    $subcategory_id = !empty($data['subcategory_id']) ? (int)$data['subcategory_id'] : null;

    if ($subcategory_id && !validateSubcategory($pdo, $subcategory_id, $parent_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid subcategory_id for the given parent category']);
        exit;
    }

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, slug, price, sku, category_id, subcategory_id, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $slug, $price, $sku, $category_id, $subcategory_id, $stock_quantity])) {
        echo json_encode(['success' => true, 'message' => 'Product saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create product']);
    }
} elseif ($method === 'PUT') {
    // Update
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    $name = trim($data['name'] ?? '');
    $price = $data['price'] ?? null;
    $stock_quantity = $data['stock_quantity'] ?? null;

    $updateFields = [];
    $params = [];

    if (!empty($name)) {
        $updateFields[] = "name = ?";
        $params[] = $name;
    }
    if ($price !== null) {
        $updateFields[] = "price = ?";
        $params[] = $price;
    }
    if ($stock_quantity !== null) {
        $updateFields[] = "stock_quantity = ?";
        $params[] = $stock_quantity;
    }

    $hasCatUpdate = false;
    $category_id = null;
    $parent_id = null;

    if (array_key_exists('parent_category_id', $data)) {
        if ($data['parent_category_id'] !== null) {
            $parent_id = (int)$data['parent_category_id'];
            if (isset($parentToLegacy[$parent_id])) {
                $category_id = $parentToLegacy[$parent_id];
                $updateFields[] = "category_id = ?";
                $params[] = $category_id;
                $hasCatUpdate = true;
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid parent_category_id']);
                exit;
            }
        }
    } elseif (array_key_exists('category_id', $data)) {
        $category_id = (int)$data['category_id'];
        $parent_id = $legacyToParent[$category_id] ?? null;
        $updateFields[] = "category_id = ?";
        $params[] = $category_id;
        $hasCatUpdate = true;
    }

    if (array_key_exists('subcategory_id', $data)) {
        $subcategory_id = !empty($data['subcategory_id']) ? (int)$data['subcategory_id'] : null;
        
        // If we are updating subcategory_id, we must ensure it matches the final parent_category.
        // If parent wasn't updated in this request, we need to fetch the existing category_id to validate.
        if ($subcategory_id) {
            $validateParentId = $parent_id;
            if (!$hasCatUpdate) {
                $fetchStmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
                $fetchStmt->execute([$id]);
                $existingProd = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                if ($existingProd) {
                    $validateParentId = $legacyToParent[$existingProd['category_id']] ?? null;
                }
            }
            
            if (!validateSubcategory($pdo, $subcategory_id, $validateParentId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid subcategory_id for the product\'s parent category']);
                exit;
            }
        }
        
        $updateFields[] = "subcategory_id = ?";
        $params[] = $subcategory_id;
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }

    $params[] = $id;

    $sql = "UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
