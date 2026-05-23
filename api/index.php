<?php
declare(strict_types=1);

session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

date_default_timezone_set('Asia/Kolkata');

function wantsJson(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return (isset($_GET['format']) && $_GET['format'] === 'json')
        || str_contains($accept, 'application/json')
        || str_contains($accept, 'text/json');
}

function responseJson(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function apiError(string $message, int $status = 404, array $extra = []): void
{
    responseJson([
        'success' => false,
        'message' => $message,
        'code' => $status,
        'debug' => array_merge([
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'uri' => $_SERVER['REQUEST_URI'] ?? ''
        ], $extra)
    ], $status);
}

function appBaseUrl(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $scheme . '://' . $host . $dir . '/';
}

function currentPath(): string
{
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $path = preg_replace('#^' . preg_quote($scriptDir, '#') . '/?#', '', $requestUri);
    return trim((string)$path, '/');
}

function routeSegments(): array
{
    $path = currentPath();
    if ($path === '') {
        return [];
    }
    return array_values(array_filter(explode('/', strtolower($path))));
}

function sampleDocs(): array
{
    $base = appBaseUrl();

    return [
        'System' => [
            [
                'method' => 'GET',
                'path' => '',
                'title' => 'API Home / Docs',
                'description' => 'Shows browser documentation for all APIs.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'status' => 'PRODUCTION READY']
            ],
            [
                'method' => 'GET',
                'path' => 'health',
                'title' => 'Health Check',
                'description' => 'Returns API uptime/basic health information.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'message' => 'API is running']
            ],
        ],
        'Categories' => [
            [
                'method' => 'GET',
                'path' => 'categories/list.php',
                'title' => 'All Categories',
                'description' => 'Get all active categories.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'data' => [['id' => 1, 'name' => 'Category Name']]]
            ],
        ],
        'Products' => [
            [
                'method' => 'GET',
                'path' => 'products/list.php',
                'title' => 'All Products',
                'description' => 'Get all products.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'data' => [['id' => 1, 'name' => 'Product Name', 'price' => 999]]]
            ],
            [
                'method' => 'GET',
                'path' => 'products/detail.php/1',
                'title' => 'Product Detail',
                'description' => 'Get one product by id.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'data' => ['id' => 1, 'name' => 'Product Name', 'price' => 999]]
            ],
            [
                'method' => 'POST|PUT',
                'path' => 'products/manage.php/1',
                'title' => 'Manage Product',
                'description' => 'Create or update a product.',
                'query' => [],
                'body' => ['name' => 'Demo Product', 'price' => 999, 'stock_quantity' => 10],
                'response' => ['success' => true, 'message' => 'Product saved successfully']
            ],
        ],
        'Cart' => [
            [
                'method' => 'GET',
                'path' => 'cart.php?user_id=1',
                'title' => 'Get Cart',
                'description' => 'Get cart items of a user.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'data' => ['user_id' => 1, 'items' => []]]
            ],
            [
                'method' => 'POST',
                'path' => 'cart/add.php?user_id=1',
                'title' => 'Add to Cart',
                'description' => 'Add product to cart.',
                'query' => ['user_id' => 1],
                'body' => ['product_id' => 1, 'quantity' => 2],
                'response' => ['success' => true, 'message' => 'Added to cart!']
            ],
            [
                'method' => 'PUT',
                'path' => 'cart/update.php?user_id=1',
                'title' => 'Update Cart',
                'description' => 'Update cart quantity.',
                'query' => ['user_id' => 1],
                'body' => ['cart_item_id' => 1, 'quantity' => 3],
                'response' => ['success' => true, 'message' => 'Cart updated']
            ],
            [
                'method' => 'DELETE',
                'path' => 'cart/remove.php/1?user_id=1',
                'title' => 'Remove Cart Item',
                'description' => 'Remove one item from cart.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'message' => 'Item removed']
            ],
            [
                'method' => 'DELETE',
                'path' => 'cart/clear.php?user_id=1',
                'title' => 'Clear Cart',
                'description' => 'Remove all cart items.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'message' => 'Cart cleared']
            ],
        ],
        'Wishlist' => [
            [
                'method' => 'GET',
                'path' => 'wishlist.php?user_id=1',
                'title' => 'Get Wishlist',
                'description' => 'Get wishlist data.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'data' => []]
            ],
            [
                'method' => 'POST',
                'path' => 'wishlist/add.php?user_id=1',
                'title' => 'Add Wishlist Item',
                'description' => 'Add product to wishlist.',
                'query' => ['user_id' => 1],
                'body' => ['product_id' => 1],
                'response' => ['success' => true, 'message' => 'Added to wishlist']
            ],
            [
                'method' => 'DELETE',
                'path' => 'wishlist/remove.php/1?user_id=1',
                'title' => 'Remove Wishlist Item',
                'description' => 'Remove product from wishlist.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'message' => 'Removed from wishlist']
            ],
        ],
        'Orders' => [
            [
                'method' => 'GET',
                'path' => 'orders.php?user_id=1',
                'title' => 'List Orders',
                'description' => 'Get all orders for one user.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'data' => []]
            ],
            [
                'method' => 'GET',
                'path' => 'orders/detail.php/1?user_id=1',
                'title' => 'Order Detail',
                'description' => 'Get one order detail.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'data' => ['id' => 1]]
            ],
            [
                'method' => 'POST',
                'path' => 'orders/create.php?user_id=1',
                'title' => 'Create Order',
                'description' => 'Create order from cart.',
                'query' => ['user_id' => 1],
                'body' => ['address' => 'Siliguri', 'payment_method' => 'cod'],
                'response' => ['success' => true, 'message' => 'Order created']
            ],
            [
                'method' => 'POST',
                'path' => 'orders/cancel.php/1?user_id=1',
                'title' => 'Cancel Order',
                'description' => 'Cancel pending order.',
                'query' => ['user_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'message' => 'Order cancelled']
            ],
        ],
        'Auth' => [
            [
                'method' => 'POST',
                'path' => 'auth/register.php',
                'title' => 'Register',
                'description' => 'Register customer account.',
                'query' => [],
                'body' => ['name' => 'Sribash', 'email' => 'Sribash@example.com', 'phone' => '9876543210', 'password' => '123456'],
                'response' => ['success' => true, 'message' => 'OTP sent successfully']
            ],
            [
                'method' => 'POST',
                'path' => 'auth/verify.php',
                'title' => 'Verify OTP',
                'description' => 'Verify OTP and complete registration.',
                'query' => [],
                'body' => ['otp' => '123456'],
                'response' => ['success' => true, 'message' => 'Registration successful']
            ],
            [
                'method' => 'POST',
                'path' => 'auth/login.php',
                'title' => 'Login',
                'description' => 'Login with email and password.',
                'query' => [],
                'body' => ['email' => 'Sribash@example.com', 'password' => '123456'],
                'response' => ['success' => true, 'data' => ['id' => 1, 'name' => 'Sribash']]
            ],
            [
                'method' => 'POST',
                'path' => 'auth/logout.php',
                'title' => 'Logout',
                'description' => 'Logout current user.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'message' => 'Logged out successfully']
            ],
            [
                'method' => 'GET',
                'path' => 'auth/profile.php',
                'title' => 'Profile',
                'description' => 'Get logged in user profile.',
                'query' => [],
                'body' => null,
                'response' => ['success' => true, 'data' => ['id' => 1, 'name' => 'Sribash']]
            ],
        ],
        'Reviews' => [
            [
                'method' => 'GET',
                'path' => 'reviews/list.php',
                'title' => 'List Reviews',
                'description' => 'Get reviews for a product or user.',
                'query' => ['product_id' => 1],
                'body' => null,
                'response' => ['success' => true, 'data' => [['id' => 1, 'rating' => 5, 'comment' => 'Great!']]]
            ],
            [
                'method' => 'POST',
                'path' => 'reviews/add.php',
                'title' => 'Add Review',
                'description' => 'Submit a new review.',
                'query' => ['user_id' => 1],
                'body' => ['product_id' => 1, 'rating' => 5, 'title' => 'Awesome', 'comment' => 'Great product!'],
                'response' => ['success' => true, 'message' => 'Review submitted successfully and is pending approval']
            ],
        ],
        'Coupons' => [
            ['method' => 'GET', 'path' => 'coupons/list.php', 'title' => 'List Coupons', 'description' => 'Get all coupons', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'POST|PUT|DELETE', 'path' => 'coupons/manage.php', 'title' => 'Manage Coupons', 'description' => 'Create/Update/Delete coupons', 'query' => [], 'body' => ['code' => 'DISCOUNT10', 'discount' => 10], 'response' => ['success' => true, 'message' => 'Coupon created successfully']]
        ],
        'Offers' => [
            ['method' => 'GET', 'path' => 'offers/list.php', 'title' => 'List Offers', 'description' => 'Get all offers', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'POST|PUT|DELETE', 'path' => 'offers/manage.php', 'title' => 'Manage Offers', 'description' => 'Create/Update/Delete offers', 'query' => [], 'body' => ['offer_name' => 'Summer Sale', 'offer_value' => 20], 'response' => ['success' => true, 'message' => 'Offer created successfully']]
        ],
        'Settings' => [
            ['method' => 'GET', 'path' => 'settings/get.php', 'title' => 'Get Settings', 'description' => 'Get all settings', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => ['site_name' => 'Store']]],
            ['method' => 'POST', 'path' => 'settings/update.php', 'title' => 'Update Settings', 'description' => 'Update site settings', 'query' => [], 'body' => ['site_name' => 'My New Store'], 'response' => ['success' => true, 'message' => 'Settings updated successfully']]
        ],
        'Policies' => [
            ['method' => 'GET', 'path' => 'policies/list.php', 'title' => 'List Policies', 'description' => 'Get all policies', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'GET', 'path' => 'policies/detail.php?slug=privacy-policy', 'title' => 'Policy Detail', 'description' => 'Get single policy', 'query' => ['slug' => 'privacy-policy'], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'POST|PUT|DELETE', 'path' => 'policies/manage.php', 'title' => 'Manage Policies', 'description' => 'Create/Update/Delete policies', 'query' => [], 'body' => ['title' => 'Privacy', 'content' => 'Data...'], 'response' => ['success' => true, 'message' => 'Policy created successfully']]
        ],
        'Age Verifications' => [
            ['method' => 'GET', 'path' => 'age_verifications/list.php', 'title' => 'List Verifications', 'description' => 'Get verifications', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'POST|PUT', 'path' => 'age_verifications/manage', 'title' => 'Manage Verifications', 'description' => 'Submit/Review verifications', 'query' => [], 'body' => ['user_id' => 1, 'full_name' => 'John Doe'], 'response' => ['success' => true, 'message' => 'Verification submitted successfully']]
        ],
        'Advanced Search' => [
            ['method' => 'GET', 'path' => 'search/global.php', 'title' => 'Global Search', 'description' => 'Search across products and categories with typo handling', 'query' => ['q' => 'milk'], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'GET', 'path' => 'search/products.php', 'title' => 'Product Search', 'description' => 'Search only products', 'query' => ['q' => 'milk'], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'GET', 'path' => 'search/categories.php', 'title' => 'Category Search', 'description' => 'Search only categories', 'query' => ['q' => 'milk'], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'GET', 'path' => 'search/suggestions.php', 'title' => 'Suggestions', 'description' => 'Get autocomplete suggestions', 'query' => ['q' => 'sh'], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'GET', 'path' => 'search/related.php', 'title' => 'Related Results', 'description' => 'Fallback related products', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'GET', 'path' => 'search/popular.php', 'title' => 'Popular Searches', 'description' => 'Get popular search terms', 'query' => [], 'body' => null, 'response' => ['success' => true, 'data' => []]],
            ['method' => 'POST', 'path' => 'search/voice.php', 'title' => 'Voice Search', 'description' => 'Process voice query', 'query' => [], 'body' => ['query' => 'milk shake'], 'response' => ['success' => true, 'data' => []]]
        ],
    ];
}

function routeTable(): array
{
    return [
        'health' => [
            'methods' => ['GET'],
            'type' => 'callable',
            'handler' => function (): void {
                responseJson([
                    'success' => true,
                    'message' => 'API is running',
                    'version' => '2.0.0',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'base_url' => appBaseUrl()
                ]);
            }
        ],

        'categories/list' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/categories/list.php'
        ],

        'products/list' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/products/list.php'
        ],

        'products/detail' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/products/detail.php',
            'before' => function (?string $id): void {
                $_GET['id'] = $id ?: ($_GET['id'] ?? null);
            }
        ],

        'products/manage' => [
            'methods' => ['POST', 'PUT'],
            'type' => 'file',
            'handler' => __DIR__ . '/products/manage.php',
            'before' => function (?string $id): void {
                $_GET['id'] = $id ?: ($_GET['id'] ?? null);
            }
        ],

        'cart' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/cart/list.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'cart/list' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/cart/list.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'cart/add' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/cart/add.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'cart/update' => [
            'methods' => ['PUT'],
            'type' => 'file',
            'handler' => __DIR__ . '/cart/update.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'cart/remove' => [
            'methods' => ['DELETE'],
            'type' => 'file',
            'handler' => __DIR__ . '/cart/remove.php',
            'before' => function (?string $id): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
                $_GET['cart_item_id'] = $id ?: ($_GET['cart_item_id'] ?? null);
            }
        ],

        'cart/clear' => [
            'methods' => ['DELETE'],
            'type' => 'file',
            'handler' => __DIR__ . '/cart/clear.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'wishlist' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/wishlist/list.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'wishlist/list' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/wishlist/list.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'wishlist/add' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/wishlist/add.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'wishlist/remove' => [
            'methods' => ['DELETE'],
            'type' => 'file',
            'handler' => __DIR__ . '/wishlist/remove.php',
            'before' => function (?string $id): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
                $_GET['product_id'] = $id ?: ($_GET['product_id'] ?? null);
            }
        ],

        'orders' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/orders/list.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'orders/list' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/orders/list.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'orders/detail' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/orders/detail.php',
            'before' => function (?string $id): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
                $_GET['order_id'] = $id ?: ($_GET['order_id'] ?? null);
            }
        ],

        'orders/create' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/orders/create.php',
            'before' => function (): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
            }
        ],

        'orders/cancel' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/orders/cancel.php',
            'before' => function (?string $id): void {
                $_GET['user_id'] = $_GET['user_id'] ?? 1;
                $_GET['order_id'] = $id ?: ($_GET['order_id'] ?? null);
            }
        ],

        'auth/register' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/auth/register.php'
        ],

        'auth/verify' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/auth/verify.php'
        ],

        'auth/login' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/auth/login.php'
        ],

        'auth/logout' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/auth/logout.php'
        ],

        'auth/profile' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/auth/profile.php'
        ],

        'reviews/list.php' => [
            'methods' => ['GET'],
            'type' => 'file',
            'handler' => __DIR__ . '/reviews/list.php'
        ],

        'reviews/add' => [
            'methods' => ['POST'],
            'type' => 'file',
            'handler' => __DIR__ . '/reviews/add.php'
        ],

        'coupons/list' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/coupons/list.php'],
        'coupons/manage' => ['methods' => ['POST', 'PUT', 'DELETE'], 'type' => 'file', 'handler' => __DIR__ . '/coupons/manage.php', 'before' => function(?string $id) { $_GET['id'] = $id ?: ($_GET['id'] ?? null); }],
        
        'offers/list' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/offers/list.php'],
        'offers/manage' => ['methods' => ['POST', 'PUT', 'DELETE'], 'type' => 'file', 'handler' => __DIR__ . '/offers/manage.php', 'before' => function(?string $id) { $_GET['id'] = $id ?: ($_GET['id'] ?? null); }],
        
        'settings/get' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/settings/get.php'],
        'settings/update' => ['methods' => ['POST', 'PUT'], 'type' => 'file', 'handler' => __DIR__ . '/settings/update.php'],
        
        'policies/list' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/policies/list.php'],
        'policies/detail' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/policies/detail.php', 'before' => function(?string $id) { $_GET['id'] = $id ?: ($_GET['id'] ?? null); }],
        'policies/manage' => ['methods' => ['POST', 'PUT', 'DELETE'], 'type' => 'file', 'handler' => __DIR__ . '/policies/manage.php', 'before' => function(?string $id) { $_GET['id'] = $id ?: ($_GET['id'] ?? null); }],
        
        'age_verifications/list' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/age_verifications/list.php'],
        'age_verifications/manage' => ['methods' => ['POST', 'PUT'], 'type' => 'file', 'handler' => __DIR__ . '/age_verifications/manage.php', 'before' => function(?string $id) { $_GET['id'] = $id ?: ($_GET['id'] ?? null); }],
        
        'search' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/global.php'],
        'search/global' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/global.php'],
        'search/products' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/products.php'],
        'search/categories' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/categories.php'],
        'search/suggestions' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/suggestions.php'],
        'search/related' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/related.php'],
        'search/popular' => ['methods' => ['GET'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/popular.php'],
        'search/voice' => ['methods' => ['POST'], 'type' => 'file', 'handler' => __DIR__ . '/search_module/voice.php'],
    ];
}

function buildCurl(string $method, string $url, ?array $body = null): string
{
    $curl = "curl -X {$method} \"{$url}\"";
    if (in_array($method, ['POST', 'PUT'], true)) {
        $curl .= " \\\n  -H \"Content-Type: application/json\"";
        if ($body) {
            $curl .= " \\\n  -d '" . json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "'";
        }
    }
    return $curl;
}

function buildFlutterDio(string $method, string $url, ?array $body = null): string
{
    $methodLower = strtolower(explode('|', $method)[0]);
    $bodyCode = $body ? json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'null';

    $options = "Options(headers: {'Content-Type': 'application/json'})";

    if ($methodLower === 'get' || $methodLower === 'delete') {
        return "final dio = Dio();\n\ntry {\n  final response = await dio.{$methodLower}(\n    '{$url}',\n    options: {$options},\n  );\n\n  print(response.data);\n} catch (e) {\n  print(e);\n}";
    }

    return "final dio = Dio();\nfinal data = {$bodyCode};\n\ntry {\n  final response = await dio.{$methodLower}(\n    '{$url}',\n    data: data,\n    options: {$options},\n  );\n\n  print(response.data);\n} catch (e) {\n  print(e);\n}";
}

function renderDocs(): void
{
    $base = appBaseUrl();
    $groups = sampleDocs();
    $total = 0;

    foreach ($groups as $items) {
        $total += count($items);
    }

    if (wantsJson()) {
        responseJson([
            'success' => true,
            'version' => '2.0.0',
            'name' => 'Mandal variety E-Commerce API - Sribash Sarkar',
            'base_url' => $base,
            'total_endpoints' => $total,
            'status' => 'PRODUCTION READY',
            'groups' => $groups
        ]);
    }

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandal variety E-Commerce API Docs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --bg:#0b1220;
            --bg2:#111827;
            --card:rgba(17,24,39,.84);
            --line:rgba(255,255,255,.08);
            --text:#e5e7eb;
            --muted:#94a3b8;
            --primary:#3b82f6;
            --success:#10b981;
            --warning:#f59e0b;
            --danger:#ef4444;
        }
       body{
    background:linear-gradient(135deg,#0b1220,#111827,#1e293b);
    color:#ffffff !important;
}

.glass{
    background:#111827 !important;
    border:1px solid rgba(255,255,255,.14) !important;
}

.muted,
.text-muted,
small,
label,
.form-label,
.accordion-body p{
    color:#e5e7eb !important;
}

.endpoint-url{
    background:#0f172a !important;
    border:1px solid rgba(255,255,255,.18) !important;
    border-radius:12px;
    padding:10px 12px;
    color:#ffffff !important;
    font-weight:600;
    opacity:1 !important;
}

.code-box{
    background:#0f172a !important;
    border:1px solid rgba(255,255,255,.18) !important;
    border-radius:14px;
    padding:16px;
    position:relative;
    color:#ffffff !important;
    opacity:1 !important;
}

.code-box *,
.endpoint-url *,
.mono,
pre,
code{
    color:#ffffff !important;
    opacity:1 !important;
}

.accordion-button{
    background:rgba(255,255,255,.06) !important;
    color:#ffffff !important;
}

.accordion-button:not(.collapsed){
    background:rgba(59,130,246,.18) !important;
    color:#ffffff !important;
}

.accordion-item{
    background:#111827 !important;
    border:1px solid rgba(255,255,255,.12) !important;
}

h1,h2,h3,h4,h5,h6,p,div,span{
    color:inherit;
}

.btn-outline-light{
    border-color:#ffffff !important;
    color:#ffffff !important;
}

.btn-outline-light:hover{
    background:#ffffff !important;
    color:#0f172a !important;
}
    </style>
</head>
<body>
<div class="container py-4 py-md-5">
    <div class="glass p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle mb-3">API v2.0.0</span>
                <h1 class="hero-title display-5 mb-2">Mandal variety E-Commerce API</h1>
                <p class="muted mb-3">Professional API router + live browser docs + Flutter integration examples.</p>
                <div class="endpoint-url mono"><?= htmlspecialchars($base) ?></div>
            </div>
            <div class="text-lg-end">
                <div class="fs-1 fw-bold"><?= $total ?></div>
                <div class="muted">Total endpoints</div>
                <div class="d-flex gap-2 justify-content-lg-end mt-3 flex-wrap">
                    <a class="btn btn-primary" href="?format=json">JSON Docs</a>
                    <a class="btn btn-outline-light" href="<?= htmlspecialchars($base . 'health') ?>" target="_blank" rel="noopener noreferrer">Health Check</a>
                </div>
            </div>
        </div>
    </div>

    <div class="glass p-4 mb-4">
        <h2 class="h4 mb-3">How to use this API</h2>
        <div class="row g-3">
            <div class="col-md-3"><div class="step-card p-3"><strong>Step 1</strong><p class="muted mb-0 mt-2">Open the docs page and choose any endpoint group.</p></div></div>
            <div class="col-md-3"><div class="step-card p-3"><strong>Step 2</strong><p class="muted mb-0 mt-2">Click an endpoint card to see URL, body, cURL, and Flutter code.</p></div></div>
            <div class="col-md-3"><div class="step-card p-3"><strong>Step 3</strong><p class="muted mb-0 mt-2">Use GET API button to test live browser-accessible endpoints instantly.</p></div></div>
            <div class="col-md-3"><div class="step-card p-3"><strong>Step 4</strong><p class="muted mb-0 mt-2">Use the shown Dio code in Flutter and replace sample values with real data.</p></div></div>
        </div>
    </div>

    <?php $accordionId = 0; foreach ($groups as $groupName => $items): ?>
        <div class="glass p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="h4 mb-0"><?= htmlspecialchars($groupName) ?></h2>
                <span class="badge text-bg-light"><?= count($items) ?> endpoint(s)</span>
            </div>

            <div class="accordion" id="accordion-<?= $accordionId ?>">
                <?php foreach ($items as $index => $item): 
                    $itemId = 'item-' . $accordionId . '-' . $index;
                    $fullUrl = $base . ltrim($item['path'], '/');
                    $curl = buildCurl($item['method'], $fullUrl, $item['body']);
                    $flutter = buildFlutterDio($item['method'], $fullUrl, $item['body']);
                    $methodClass = 'bg-secondary';
                    if (str_contains($item['method'], 'GET')) $methodClass = 'bg-success';
                    if ($item['method'] === 'POST') $methodClass = 'bg-primary';
                    if ($item['method'] === 'PUT') $methodClass = 'bg-warning text-dark';
                    if ($item['method'] === 'DELETE') $methodClass = 'bg-danger';
                    if ($item['method'] === 'POST|PUT') $methodClass = 'bg-info text-dark';
                ?>
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading-<?= $itemId ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $itemId ?>" aria-expanded="false">
                                <div class="w-100 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge <?= $methodClass ?> method-badge"><?= htmlspecialchars($item['method']) ?></span>
                                        <strong><?= htmlspecialchars($item['title']) ?></strong>
                                    </div>
                                    <small class="muted mono"><?= htmlspecialchars($item['path']) ?></small>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse-<?= $itemId ?>" class="accordion-collapse collapse" data-bs-parent="#accordion-<?= $accordionId ?>">
                            <div class="accordion-body">
                                <p class="muted"><?= htmlspecialchars($item['description']) ?></p>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Endpoint URL</label>
                                    <div class="endpoint-url mono"><?= htmlspecialchars($fullUrl) ?></div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mb-4">
                                    <?php if (str_contains($item['method'], 'GET')): ?>
                                        <a class="btn btn-success btn-sm" href="<?= htmlspecialchars($fullUrl) ?>" target="_blank" rel="noopener noreferrer">GET API</a>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-light btn-sm" onclick="copyText(`<?= htmlspecialchars(addslashes($fullUrl)) ?>`)">Copy URL</button>
                                </div>

                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <h6>Query Parameters</h6>
                                        <div class="code-box mono small">
                                            <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyFrom(this)">Copy</button><?= htmlspecialchars(json_encode($item['query'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6>Request Body</h6>
                                        <div class="code-box mono small">
                                            <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyFrom(this)">Copy</button><?= htmlspecialchars($item['body'] ? json_encode($item['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'No body required') ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6>Sample Response</h6>
                                        <div class="code-box mono small">
                                            <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyFrom(this)">Copy</button><?= htmlspecialchars(json_encode($item['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6>cURL Example</h6>
                                        <div class="code-box mono small">
                                            <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyFrom(this)">Copy</button><?= htmlspecialchars($curl) ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h6>Flutter Dio Example</h6>
                                        <div class="code-box mono small">
                                            <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyFrom(this)">Copy</button><?= htmlspecialchars($flutter) ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h6>Flutter Flow</h6>
                                        <div class="code-box mono small">
1. Add dio package in pubspec.yaml
2. Set your baseUrl = "<?= htmlspecialchars($base) ?>"
3. Create Dio instance in ApiService
4. Call endpoint using GET/POST/PUT/DELETE
5. Parse response.data in model
6. Show success/error message in Flutter UI
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php $accordionId++; endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => alert('Copied'));
}
function copyFrom(button) {
    const text = button.parentElement.innerText.replace('Copy', '').trim();
    navigator.clipboard.writeText(text).then(() => {
        const old = button.innerText;
        button.innerText = 'Copied';
        setTimeout(() => button.innerText = old, 1200);
    });
}
</script>
</body>
</html>
<?php
    exit;
}

$segments = routeSegments();

if (empty($segments)) {
    renderDocs();
}

$module = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? null;

$routeKey = $module;
if ($action !== '') {
    $routeKey .= '/' . $action;
}

$routes = routeTable();

if (!isset($routes[$routeKey])) {
    apiError('Endpoint not found', 404, ['route' => $routeKey]);
}

$route = $routes[$routeKey];
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (!in_array($method, $route['methods'], true)) {
    apiError('Method not allowed', 405, [
        'allowed_methods' => $route['methods'],
        'received_method' => $method
    ]);
}

if (isset($route['before']) && is_callable($route['before'])) {
    $route['before']($id);
}

if ($route['type'] === 'callable' && is_callable($route['handler'])) {
    $route['handler']();
}

if ($route['type'] === 'file') {
    $file = $route['handler'];

    if (!file_exists($file)) {
        apiError('Handler file not found', 500, ['file' => $file]);
    }

    require_once $file;
    exit;
}

apiError('Invalid route configuration', 500);