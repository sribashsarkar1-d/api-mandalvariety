<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    :root{
        --st-primary:#0d6efd;
        --st-success:#198754;
        --st-danger:#dc3545;
        --st-warning:#f59e0b;
        --st-bg:#f4f7fb;
        --st-card:#ffffff;
        --st-text:#1f2937;
        --st-muted:#6b7280;
        --st-border:#e5e7eb;
        --st-shadow:0 8px 24px rgba(15,23,42,.06);
        --st-radius:18px;
    }

    body{
        background:var(--st-bg);
    }

    .settings-page{
        padding:24px;
    }

    .settings-header{
        margin-bottom:24px;
    }

    .settings-title{
        font-size:26px;
        font-weight:800;
        color:var(--st-text);
        margin-bottom:4px;
    }

    .settings-subtitle{
        font-size:14px;
        color:var(--st-muted);
        margin:0;
    }

    .settings-card,
    .settings-nav-card,
    .settings-summary-card{
        background:var(--st-card);
        border:1px solid var(--st-border);
        border-radius:var(--st-radius);
        box-shadow:var(--st-shadow);
    }

    .settings-summary-card{
        padding:18px;
        height:100%;
    }

    .settings-summary-label{
        display:block;
        font-size:13px;
        color:var(--st-muted);
        margin-bottom:8px;
    }

    .settings-summary-value{
        font-size:24px;
        font-weight:800;
        color:var(--st-text);
        margin:0;
    }

    .settings-nav-card{
        padding:16px;
        position:sticky;
        top:20px;
    }

    .settings-nav-title{
        font-size:14px;
        font-weight:800;
        color:var(--st-text);
        margin-bottom:12px;
    }

    .settings-nav{
        display:flex;
        flex-direction:column;
        gap:8px;
    }

    .settings-nav a{
        text-decoration:none;
        color:var(--st-muted);
        background:#f8fafc;
        border:1px solid #eef2f7;
        border-radius:12px;
        padding:10px 12px;
        font-size:14px;
        font-weight:600;
        transition:.2s ease;
    }

    .settings-nav a:hover{
        color:var(--st-primary);
        background:rgba(13,110,253,.06);
        border-color:rgba(13,110,253,.18);
    }

    .settings-card{
        margin-bottom:20px;
        overflow:hidden;
    }

    .settings-card-header{
        padding:18px 22px;
        border-bottom:1px solid var(--st-border);
        background:#fcfdff;
    }

    .settings-card-title{
        margin:0;
        font-size:17px;
        font-weight:800;
        color:var(--st-text);
    }

    .settings-card-body{
        padding:22px;
    }

    .form-label{
        font-size:13px;
        font-weight:700;
        color:var(--st-text);
        margin-bottom:8px;
    }

    .form-control,
    .form-select,
    textarea.form-control{
        border:1px solid var(--st-border);
        border-radius:12px;
        min-height:46px;
        box-shadow:none;
        font-size:14px;
    }

    textarea.form-control{
        min-height:110px;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:var(--st-primary);
        box-shadow:0 0 0 4px rgba(13,110,253,.10);
    }

    .form-text{
        font-size:12px;
        color:var(--st-muted);
    }

    .settings-switch{
        border:1px solid var(--st-border);
        border-radius:14px;
        padding:14px 16px;
        background:#fbfcfe;
        height:100%;
    }

    .settings-switch-title{
        font-size:14px;
        font-weight:700;
        color:var(--st-text);
        margin-bottom:4px;
    }

    .settings-switch-text{
        font-size:12px;
        color:var(--st-muted);
        margin:0;
    }

    .settings-footer-bar{
        position:sticky;
        bottom:14px;
        z-index:5;
        margin-top:20px;
    }

    .settings-footer-inner{
        background:#ffffff;
        border:1px solid var(--st-border);
        box-shadow:var(--st-shadow);
        border-radius:16px;
        padding:14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .settings-footer-note{
        font-size:13px;
        color:var(--st-muted);
    }

    .btn-settings{
        min-height:46px;
        border-radius:12px;
        font-weight:700;
        padding:0 18px;
    }

    .settings-badge{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        background:rgba(13,110,253,.08);
        color:var(--st-primary);
        font-size:12px;
        font-weight:700;
    }

    .alert{
        border:none;
        border-radius:14px;
        box-shadow:var(--st-shadow);
    }

    .logo-preview{
        width:72px;
        height:72px;
        border:1px solid var(--st-border);
        border-radius:14px;
        object-fit:cover;
        background:#fff;
        display:block;
    }

    .section-divider{
        border-top:1px dashed #e5e7eb;
        margin:20px 0;
    }

    @media (max-width:991.98px){
        .settings-nav-card{
            position:static;
        }
    }

    @media (max-width:767.98px){
        .settings-page{
            padding:16px;
        }

        .settings-title{
            font-size:22px;
        }

        .settings-card-body{
            padding:16px;
        }
    }
</style>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$success = '';

$defaultSettings = [
    'store_name' => 'My E-commerce Store',
    'store_email' => 'admin@example.com',
    'store_phone' => '',
    'store_whatsapp' => '',
    'store_address' => '',
    'store_city' => '',
    'store_state' => '',
    'store_country' => 'India',
    'store_pincode' => '',
    'store_currency' => 'INR',
    'currency_symbol' => '₹',
    'timezone' => 'Asia/Kolkata',
    'tax_percent' => '0',
    'shipping_charge' => '0',
    'free_shipping_min_amount' => '0',
    'cod_enabled' => '1',
    'razorpay_enabled' => '0',
    'razorpay_key_id' => '',
    'razorpay_key_secret' => '',
    'paypal_enabled' => '0',
    'paypal_client_id' => '',
    'paypal_secret' => '',
    'order_auto_confirm' => '0',
    'allow_guest_checkout' => '1',
    'low_stock_limit' => '5',
    'maintenance_mode' => '0',
    'maintenance_message' => 'We are improving our store. Please check back soon.',
    'seo_meta_title' => '',
    'seo_meta_keywords' => '',
    'seo_meta_description' => '',
    'seo_og_image' => '',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'email_notifications' => '1',
    'order_notifications' => '1',
    'review_notifications' => '1',
    'new_user_notifications' => '1',
    'invoice_prefix' => 'INV',
    'footer_text' => '© All rights reserved.',
    'facebook_url' => '',
    'instagram_url' => '',
    'twitter_url' => '',
    'youtube_url' => '',
    'logo' => '',
    'favicon' => '',
    'admin_per_page' => '10',
    'admin_theme' => 'light',
];

$settings = $defaultSettings;

function getSetting(PDO $conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return ($value !== false) ? $value : $default;
}

function saveSetting(PDO $conn, $key, $value) {
    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    return $stmt->execute([$key, $value]);
}

try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
    $dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    if (!empty($dbSettings)) {
        foreach ($dbSettings as $key => $value) {
            $settings[$key] = $value;
        }
    }
} catch (Exception $ex) {
    $errors[] = 'Settings table not found or could not load settings.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $storeLogo = $settings['logo'] ?? '';
        $storeFavicon = $settings['favicon'] ?? '';

        if (!empty($_FILES['logo']['name'])) {
            if (!is_dir('../uploads/settings')) {
                mkdir('../uploads/settings', 0777, true);
            }

            $logoName = time() . '_logo_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']);
            $logoPath = '../uploads/settings/' . $logoName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
                $storeLogo = 'settings/' . $logoName;
            }
        }

        if (!empty($_FILES['favicon']['name'])) {
            if (!is_dir('../uploads/settings')) {
                mkdir('../uploads/settings', 0777, true);
            }

            $favName = time() . '_favicon_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['favicon']['name']);
            $favPath = '../uploads/settings/' . $favName;

            if (move_uploaded_file($_FILES['favicon']['tmp_name'], $favPath)) {
                $storeFavicon = 'settings/' . $favName;
            }
        }

        $postSettings = [
            'store_name','store_email','store_phone','store_whatsapp','store_address','store_city','store_state','store_country',
            'store_pincode','store_currency','currency_symbol','timezone','tax_percent','shipping_charge',
            'free_shipping_min_amount','razorpay_key_id','razorpay_key_secret','paypal_client_id','paypal_secret',
            'low_stock_limit','maintenance_message','seo_meta_title','seo_meta_keywords','seo_meta_description',
            'seo_og_image','smtp_host','smtp_port','smtp_username','smtp_password','smtp_encryption','invoice_prefix',
            'footer_text','facebook_url','instagram_url','twitter_url','youtube_url','admin_per_page','admin_theme'
        ];

        foreach ($postSettings as $key) {
            saveSetting($conn, $key, trim($_POST[$key] ?? ''));
            $settings[$key] = trim($_POST[$key] ?? '');
        }

        $toggleSettings = [
            'cod_enabled','razorpay_enabled','paypal_enabled','order_auto_confirm',
            'allow_guest_checkout','maintenance_mode','email_notifications',
            'order_notifications','review_notifications','new_user_notifications'
        ];

        foreach ($toggleSettings as $key) {
            $value = isset($_POST[$key]) ? '1' : '0';
            saveSetting($conn, $key, $value);
            $settings[$key] = $value;
        }

        saveSetting($conn, 'logo', $storeLogo);
        saveSetting($conn, 'favicon', $storeFavicon);
        $settings['logo'] = $storeLogo;
        $settings['favicon'] = $storeFavicon;

        $success = 'Settings updated successfully.';
    } catch (Exception $ex) {
        $errors[] = 'Failed to save settings: ' . $ex->getMessage();
    }
}

$totalSettingsSaved = 0;
try {
    $totalSettingsSaved = (int)$conn->query("SELECT COUNT(*) FROM settings")->fetchColumn();
} catch (Exception $ex) {
    $totalSettingsSaved = 0;
}

$logoPreview = !empty($settings['logo']) ? '../uploads/' . $settings['logo'] : 'https://via.placeholder.com/72?text=Logo';
$faviconPreview = !empty($settings['favicon']) ? '../uploads/' . $settings['favicon'] : 'https://via.placeholder.com/72?text=Icon';
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid settings-page">
        <div class="settings-header">
            <h4 class="settings-title">⚙️ Store Settings</h4>
            <p class="settings-subtitle">Configure store identity, checkout, payments, shipping, SEO, email, security, and operational preferences.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="settings-summary-card">
                    <span class="settings-summary-label">Saved Settings</span>
                    <h3 class="settings-summary-value"><?= (int)$totalSettingsSaved ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="settings-summary-card">
                    <span class="settings-summary-label">Currency</span>
                    <h3 class="settings-summary-value"><?= e($settings['store_currency']) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="settings-summary-card">
                    <span class="settings-summary-label">Maintenance Mode</span>
                    <h3 class="settings-summary-value"><?= !empty($settings['maintenance_mode']) ? 'ON' : 'OFF' ?></h3>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="settings-nav-card">
                        <div class="settings-nav-title">Settings Sections</div>
                        <div class="settings-nav">
                            <a href="#store-info">Store Info</a>
                            <a href="#location">Location</a>
                            <a href="#branding">Branding</a>
                            <a href="#order-checkout">Order & Checkout</a>
                            <a href="#payment">Payment</a>
                            <a href="#shipping-tax">Shipping & Tax</a>
                            <a href="#email-smtp">Email SMTP</a>
                            <a href="#notifications">Notifications</a>
                            <a href="#seo">SEO & Social</a>
                            <a href="#admin-pref">Admin Preferences</a>
                            <a href="#maintenance">Maintenance & Security</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div id="store-info" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Store Information</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Store Name</label>
                                    <input type="text" name="store_name" class="form-control" value="<?= e($settings['store_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Store Email</label>
                                    <input type="email" name="store_email" class="form-control" value="<?= e($settings['store_email']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Store Phone</label>
                                    <input type="text" name="store_phone" class="form-control" value="<?= e($settings['store_phone']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">WhatsApp Number</label>
                                    <input type="text" name="store_whatsapp" class="form-control" value="<?= e($settings['store_whatsapp']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Footer Text</label>
                                    <input type="text" name="footer_text" class="form-control" value="<?= e($settings['footer_text']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="location" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Store Address & Region</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="store_address" class="form-control"><?= e($settings['store_address']) ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" name="store_city" class="form-control" value="<?= e($settings['store_city']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" name="store_state" class="form-control" value="<?= e($settings['store_state']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" name="store_pincode" class="form-control" value="<?= e($settings['store_pincode']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="store_country" class="form-control" value="<?= e($settings['store_country']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Timezone</label>
                                    <input type="text" name="timezone" class="form-control" value="<?= e($settings['timezone']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="branding" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Branding</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Logo</label>
                                    <input type="file" name="logo" class="form-control">
                                    <div class="form-text mt-2">Upload store logo.</div>
                                    <div class="mt-3">
                                        <img src="<?= e($logoPreview) ?>" alt="Logo Preview" class="logo-preview">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Favicon</label>
                                    <input type="file" name="favicon" class="form-control">
                                    <div class="form-text mt-2">Upload browser icon.</div>
                                    <div class="mt-3">
                                        <img src="<?= e($faviconPreview) ?>" alt="Favicon Preview" class="logo-preview">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="order-checkout" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Order & Checkout</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Invoice Prefix</label>
                                    <input type="text" name="invoice_prefix" class="form-control" value="<?= e($settings['invoice_prefix']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Low Stock Limit</label>
                                    <input type="number" name="low_stock_limit" class="form-control" value="<?= e($settings['low_stock_limit']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Items Per Page (Admin)</label>
                                    <input type="number" name="admin_per_page" class="form-control" value="<?= e($settings['admin_per_page']) ?>">
                                </div>

                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="allow_guest_checkout" id="allow_guest_checkout" <?= !empty($settings['allow_guest_checkout']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="allow_guest_checkout">Allow Guest Checkout</label>
                                        </div>
                                        <p class="settings-switch-text">Customers can place orders without login.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="order_auto_confirm" id="order_auto_confirm" <?= !empty($settings['order_auto_confirm']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="order_auto_confirm">Auto Confirm Orders</label>
                                        </div>
                                        <p class="settings-switch-text">Automatically mark newly placed orders as confirmed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="payment" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Payment Settings</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Currency Code</label>
                                    <input type="text" name="store_currency" class="form-control" value="<?= e($settings['store_currency']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Currency Symbol</label>
                                    <input type="text" name="currency_symbol" class="form-control" value="<?= e($settings['currency_symbol']) ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Admin Theme</label>
                                    <select name="admin_theme" class="form-select">
                                        <option value="light" <?= $settings['admin_theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                                        <option value="dark" <?= $settings['admin_theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="cod_enabled" id="cod_enabled" <?= !empty($settings['cod_enabled']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="cod_enabled">Cash on Delivery</label>
                                        </div>
                                        <p class="settings-switch-text">Enable COD for checkout.</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="razorpay_enabled" id="razorpay_enabled" <?= !empty($settings['razorpay_enabled']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="razorpay_enabled">Razorpay</label>
                                        </div>
                                        <p class="settings-switch-text">Enable Razorpay payment gateway.</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="paypal_enabled" id="paypal_enabled" <?= !empty($settings['paypal_enabled']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="paypal_enabled">PayPal</label>
                                        </div>
                                        <p class="settings-switch-text">Enable PayPal payments.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Razorpay Key ID</label>
                                    <input type="text" name="razorpay_key_id" class="form-control" value="<?= e($settings['razorpay_key_id']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Razorpay Key Secret</label>
                                    <input type="text" name="razorpay_key_secret" class="form-control" value="<?= e($settings['razorpay_key_secret']) ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">PayPal Client ID</label>
                                    <input type="text" name="paypal_client_id" class="form-control" value="<?= e($settings['paypal_client_id']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PayPal Secret</label>
                                    <input type="text" name="paypal_secret" class="form-control" value="<?= e($settings['paypal_secret']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="shipping-tax" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Shipping & Tax</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tax Percent</label>
                                    <input type="number" step="0.01" name="tax_percent" class="form-control" value="<?= e($settings['tax_percent']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Shipping Charge</label>
                                    <input type="number" step="0.01" name="shipping_charge" class="form-control" value="<?= e($settings['shipping_charge']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Free Shipping Minimum</label>
                                    <input type="number" step="0.01" name="free_shipping_min_amount" class="form-control" value="<?= e($settings['free_shipping_min_amount']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="email-smtp" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Email SMTP Configuration</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" name="smtp_host" class="form-control" value="<?= e($settings['smtp_host']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="text" name="smtp_port" class="form-control" value="<?= e($settings['smtp_port']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Encryption</label>
                                    <select name="smtp_encryption" class="form-select">
                                        <option value="tls" <?= $settings['smtp_encryption'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= $settings['smtp_encryption'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= $settings['smtp_encryption'] === 'none' ? 'selected' : '' ?>>None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" name="smtp_username" class="form-control" value="<?= e($settings['smtp_username']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="text" name="smtp_password" class="form-control" value="<?= e($settings['smtp_password']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="notifications" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Notifications</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" <?= !empty($settings['email_notifications']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="email_notifications">General Email Notifications</label>
                                        </div>
                                        <p class="settings-switch-text">Enable system email alerts.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="order_notifications" id="order_notifications" <?= !empty($settings['order_notifications']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="order_notifications">Order Notifications</label>
                                        </div>
                                        <p class="settings-switch-text">Get alerts for new or updated orders.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="review_notifications" id="review_notifications" <?= !empty($settings['review_notifications']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="review_notifications">Review Notifications</label>
                                        </div>
                                        <p class="settings-switch-text">Get alerts for new customer reviews.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="new_user_notifications" id="new_user_notifications" <?= !empty($settings['new_user_notifications']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="new_user_notifications">New User Notifications</label>
                                        </div>
                                        <p class="settings-switch-text">Get alerts when a new customer registers.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="seo" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">SEO & Social Media</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="seo_meta_title" class="form-control" value="<?= e($settings['seo_meta_title']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" name="seo_meta_keywords" class="form-control" value="<?= e($settings['seo_meta_keywords']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="seo_meta_description" class="form-control"><?= e($settings['seo_meta_description']) ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Open Graph Image URL</label>
                                    <input type="text" name="seo_og_image" class="form-control" value="<?= e($settings['seo_og_image']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Facebook URL</label>
                                    <input type="text" name="facebook_url" class="form-control" value="<?= e($settings['facebook_url']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Instagram URL</label>
                                    <input type="text" name="instagram_url" class="form-control" value="<?= e($settings['instagram_url']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Twitter URL</label>
                                    <input type="text" name="twitter_url" class="form-control" value="<?= e($settings['twitter_url']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">YouTube URL</label>
                                    <input type="text" name="youtube_url" class="form-control" value="<?= e($settings['youtube_url']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="admin-pref" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Admin Preferences</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Admin Theme Preference</label>
                                    <select name="admin_theme" class="form-select">
                                        <option value="light" <?= $settings['admin_theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                                        <option value="dark" <?= $settings['admin_theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Admin List Per Page</label>
                                    <input type="number" name="admin_per_page" class="form-control" value="<?= e($settings['admin_per_page']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="maintenance" class="settings-card">
                        <div class="settings-card-header">
                            <h5 class="settings-card-title">Maintenance & Security</h5>
                        </div>
                        <div class="settings-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
                                            <label class="form-check-label settings-switch-title" for="maintenance_mode">Maintenance Mode</label>
                                        </div>
                                        <p class="settings-switch-text">Temporarily disable storefront access for visitors.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-switch">
                                        <div class="settings-switch-title">Security Model</div>
                                        <p class="settings-switch-text">This settings page uses persistent key-value storage, toggle-based operational controls, and grouped sections for safer configuration management.</p>
                                        <span class="settings-badge mt-2">Logical Multi-Section Model</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Maintenance Message</label>
                                    <textarea name="maintenance_message" class="form-control"><?= e($settings['maintenance_message']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-footer-bar">
                        <div class="settings-footer-inner">
                            <div class="settings-footer-note">
                                Save all configuration changes carefully. Store operations, checkout, and notifications can be affected immediately.
                            </div>
                            <div class="d-flex gap-2">
                                <a href="index.php" class="btn btn-light btn-settings">Reset</a>
                                <button type="submit" class="btn btn-primary btn-settings">Save Settings</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>