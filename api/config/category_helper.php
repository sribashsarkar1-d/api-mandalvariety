<?php
/**
 * Category Helper Proxy/Bridge for API
 */
if (file_exists(__DIR__ . '/../../admin/includes/category_helper.php')) {
    require_once __DIR__ . '/../../admin/includes/category_helper.php';
} else {
    // Standalone fallback if admin folder is separated in production
    if (!function_exists('getCategoryMappings')) {
        function getCategoryMappings(PDO $conn) {
            static $cache = null;
            if ($cache !== null) return $cache;

            $parentToLegacy = [1 => 52, 2 => 53, 3 => 54, 4 => 55];
            $legacyToParent = [52 => 1, 53 => 2, 54 => 3, 55 => 4];

            try {
                $parents = $conn->query("SELECT id, name, slug FROM parent_categories")->fetchAll(PDO::FETCH_ASSOC);
                $categories = $conn->query("SELECT id, name, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC);

                $catBySlug = [];
                $catByName = [];
                foreach ($categories as $c) {
                    if (!empty($c['slug'])) $catBySlug[strtolower(trim($c['slug']))] = (int)$c['id'];
                    if (!empty($c['name'])) $catByName[trim($c['name'])] = (int)$c['id'];
                }

                foreach ($parents as $p) {
                    $pId = (int)$p['id'];
                    if (isset($parentToLegacy[$pId])) continue;

                    $pSlug = strtolower(trim($p['slug'] ?? ''));
                    $pName = trim($p['name'] ?? '');

                    $matched = null;
                    if ($pSlug !== '' && isset($catBySlug[$pSlug])) {
                        $matched = $catBySlug[$pSlug];
                    } elseif ($pName !== '' && isset($catByName[$pName])) {
                        $matched = $catByName[$pName];
                    }

                    if ($matched !== null) {
                        $parentToLegacy[$pId] = $matched;
                        $legacyToParent[$matched] = $pId;
                    }
                }
            } catch (Exception $e) {}

            $cache = [$parentToLegacy, $legacyToParent];
            return $cache;
        }
    }

    if (!function_exists('getLegacyCategoryId')) {
        function getLegacyCategoryId(PDO $conn, int $parentCategoryId): int {
            if ($parentCategoryId <= 0) return 0;
            list($parentToLegacy) = getCategoryMappings($conn);
            if (isset($parentToLegacy[$parentCategoryId])) return $parentToLegacy[$parentCategoryId];

            try {
                $stmt = $conn->prepare("SELECT name, slug FROM parent_categories WHERE id = ?");
                $stmt->execute([$parentCategoryId]);
                $parent = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($parent) {
                    $checkStmt = $conn->prepare("SELECT id FROM categories WHERE (slug != '' AND slug = ?) OR name = ? LIMIT 1");
                    $checkStmt->execute([$parent['slug'], $parent['name']]);
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    if ($existing) return (int)$existing['id'];
                }
            } catch (Exception $e) {}

            return 0;
        }
    }

    if (!function_exists('getOrCreateLegacyCategoryId')) {
        function getOrCreateLegacyCategoryId(PDO $conn, int $parentCategoryId): int {
            if ($parentCategoryId <= 0) return 0;
            $legacyId = getLegacyCategoryId($conn, $parentCategoryId);
            if ($legacyId > 0) return $legacyId;

            try {
                $stmt = $conn->prepare("SELECT * FROM parent_categories WHERE id = ?");
                $stmt->execute([$parentCategoryId]);
                $parent = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$parent) return 0;

                $checkStmt = $conn->prepare("SELECT id FROM categories WHERE (slug != '' AND slug = ?) OR name = ? LIMIT 1");
                $checkStmt->execute([$parent['slug'], $parent['name']]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) return (int)$existing['id'];

                $base = strtolower(trim(preg_replace('/[^a-z0-9\-\s]/', '', $parent['slug'] ?: $parent['name'])));
                $base = trim(preg_replace('/[\s\-]+/', '-', $base), '-');
                if (empty($base)) $base = 'category';

                $newSlug = $base;
                $i = 1;
                while (true) {
                    $slugStmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
                    $slugStmt->execute([$newSlug]);
                    if (!$slugStmt->fetch()) break;
                    $newSlug = $base . '-' . $i++;
                }

                $insStmt = $conn->prepare("INSERT INTO categories (name, slug, description, image, is_active) VALUES (?, ?, ?, ?, ?)");
                $insStmt->execute([
                    $parent['name'],
                    $newSlug,
                    $parent['description'] ?? null,
                    $parent['image'] ?? null,
                    $parent['is_active'] ?? 1
                ]);

                return (int)$conn->lastInsertId();
            } catch (Exception $e) {
                return 0;
            }
        }
    }

    if (!function_exists('getParentCategoryFromLegacy')) {
        function getParentCategoryFromLegacy(PDO $conn, int $legacyCategoryId): ?int {
            if ($legacyCategoryId <= 0) return null;
            list($parentToLegacy, $legacyToParent) = getCategoryMappings($conn);
            if (isset($legacyToParent[$legacyCategoryId])) return $legacyToParent[$legacyCategoryId];

            try {
                $stmt = $conn->prepare("SELECT name, slug FROM categories WHERE id = ?");
                $stmt->execute([$legacyCategoryId]);
                $cat = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$cat) return null;

                $pStmt = $conn->prepare("SELECT id FROM parent_categories WHERE (slug != '' AND slug = ?) OR name = ? LIMIT 1");
                $pStmt->execute([$cat['slug'], $cat['name']]);
                $parent = $pStmt->fetch(PDO::FETCH_ASSOC);
                return $parent ? (int)$parent['id'] : null;
            } catch (Exception $e) {
                return null;
            }
        }
    }
}
