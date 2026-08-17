<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Add Product';
$show_back_btn = true;

// Fetch categories
$stmt = $pdo->query("SELECT id, name FROM employee_categories WHERE status = 'active'");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $category_id = $_POST['category_id'] ?: null;
    $sku = sanitize_input($_POST['sku']);
    $barcode = sanitize_input($_POST['barcode']);
    $brand = sanitize_input($_POST['brand']);
    $unit = $_POST['unit'];
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $selling_price = $_POST['selling_price'];
    $mrp = $_POST['mrp'] ?? 0;
    $gst = $_POST['gst_percent'] ?? 0;
    $discount = $_POST['discount'] ?: 0;
    $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $min_stock = $_POST['minimum_stock'] ?: 0;
    $quantity = $_POST['quantity'] ?: 0;
    $desc = sanitize_input($_POST['description']);
    
    // Handle image upload
    $image = null;
    $uploadDir = '../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . '-' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $filename;
        }
    } elseif (!empty($_POST['camera_image_base64'])) {
        $base64_string = $_POST['camera_image_base64'];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
            $data = substr($base64_string, strpos($base64_string, ',') + 1);
            $data = base64_decode(str_replace(' ', '+', $data));
            if ($data !== false) {
                $ext = strtolower($type[1]);
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $filename = uniqid() . '-camera.' . $ext;
                    $uploadFile = $uploadDir . $filename;
                    if (file_put_contents($uploadFile, $data)) {
                        $image = $filename;
                    }
                }
            }
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO employee_products 
            (category_id, name, sku, barcode, image, brand, unit, purchase_price, selling_price, mrp, gst_percent, discount, expiry_date, minimum_stock, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $category_id, $name, $sku, $barcode, $image, $brand, $unit, $purchase_price, $selling_price, $mrp, $gst, $discount, $expiry, $min_stock, $desc
        ]);
        
        $product_id = $pdo->lastInsertId();
        
        // Initialize stock
        $status = 'in_stock';
        if ($quantity <= 0) $status = 'out_of_stock';
        elseif ($quantity <= $min_stock) $status = 'low_stock';
        
        $stmtStock = $pdo->prepare("INSERT INTO employee_product_stock (product_id, quantity, stock_status) VALUES (?, ?, ?)");
        $stmtStock->execute([$product_id, $quantity, $status]);
        
        $pdo->commit();
        header("Location: products.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$auto_sku = 'SKU-' . strtoupper(substr(uniqid(), -6));
$auto_barcode = '890' . rand(100000000, 999999999);

require_once '../includes/header.php';
?>

<div class="custom-card p-3">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label text-muted small">Product Name *</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Product Image</label>
            <div class="d-flex gap-2 mb-2">
                <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                <button type="button" id="openCameraBtn" class="btn btn-secondary text-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera" viewBox="0 0 16 16">
                      <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/>
                      <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                      <path d="M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                    </svg>
                    Camera
                </button>
            </div>
            
            <div id="cameraSection" class="border rounded p-2 mb-2 bg-light text-center" style="display: none;">
                <video id="cameraVideo" style="width: 100%; max-width: 300px; display: none;" autoplay playsinline></video>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
                <img id="cameraPreview" style="width: 100%; max-width: 300px; display: none; border: 1px solid #ccc; border-radius: 4px;" />
                <input type="hidden" name="camera_image_base64" id="cameraImageBase64">
                
                <div class="mt-2">
                    <button type="button" id="startCameraBtn" class="btn btn-sm btn-primary">Start Camera</button>
                    <button type="button" id="capturePhotoBtn" class="btn btn-sm btn-success" style="display: none;">Take Photo</button>
                    <button type="button" id="retakePhotoBtn" class="btn btn-sm btn-warning" style="display: none;">Retake</button>
                    <button type="button" id="closeCameraBtn" class="btn btn-sm btn-danger">Close</button>
                </div>
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Select...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Unit *</label>
                <select name="unit" class="form-select" required>
                    <option value="piece">Piece</option>
                    <option value="kg">KG</option>
                    <option value="gram">Gram</option>
                    <option value="liter">Liter</option>
                    <option value="ml">ML</option>
                    <option value="packet">Packet</option>
                    <option value="box">Box</option>
                    <option value="dozen">Dozen</option>
                </select>
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">SKU</label>
                <input type="text" name="sku" class="form-control" value="<?= $auto_sku ?>">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Barcode</label>
                <input type="text" name="barcode" class="form-control" value="<?= $auto_barcode ?>">
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">Purchase Price</label>
                <input type="number" step="0.01" name="purchase_price" class="form-control" value="0">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">MRP</label>
                <input type="number" step="0.01" name="mrp" class="form-control" value="0">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">Selling Price *</label>
                <input type="number" step="0.01" name="selling_price" id="sellingPrice" class="form-control" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">Discount</label>
                <input type="number" step="0.01" name="discount" id="discountAmt" class="form-control" value="0">
            </div>
        </div>
        
        <div class="p-2 mb-3 bg-light rounded border d-flex justify-content-between align-items-center">
            <span class="text-muted small fw-bold">Final Discounted Price:</span>
            <span class="fs-5 fw-bold text-success" id="finalPriceDisplay">₹0.00</span>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Initial Stock Qty</label>
                <input type="number" step="0.001" name="quantity" class="form-control" value="0">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Min Stock</label>
                <input type="number" step="0.001" name="minimum_stock" class="form-control" value="5">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control">
        </div>
        
        <div class="mb-4">
            <label class="form-label text-muted small">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2">Save Product</button>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sellingPriceInput = document.getElementById('sellingPrice');
    const discountInput = document.getElementById('discountAmt');
    const finalPriceDisplay = document.getElementById('finalPriceDisplay');

    function calculateFinalPrice() {
        const sp = parseFloat(sellingPriceInput.value) || 0;
        const disc = parseFloat(discountInput.value) || 0;
        const final = Math.max(0, sp - disc);
        finalPriceDisplay.textContent = '₹' + final.toFixed(2);
    }

    sellingPriceInput.addEventListener('input', calculateFinalPrice);
    discountInput.addEventListener('input', calculateFinalPrice);

    // Camera functionality
    const openCameraBtn = document.getElementById('openCameraBtn');
    const cameraSection = document.getElementById('cameraSection');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraImageBase64 = document.getElementById('cameraImageBase64');
    
    const startCameraBtn = document.getElementById('startCameraBtn');
    const capturePhotoBtn = document.getElementById('capturePhotoBtn');
    const retakePhotoBtn = document.getElementById('retakePhotoBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const imageInput = document.getElementById('imageInput');
    
    let stream = null;

    openCameraBtn.addEventListener('click', function() {
        cameraSection.style.display = 'block';
        if (!stream && !cameraImageBase64.value) {
            startCamera();
        }
    });

    closeCameraBtn.addEventListener('click', function() {
        cameraSection.style.display = 'none';
        stopCamera();
    });

    startCameraBtn.addEventListener('click', startCamera);

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            cameraVideo.srcObject = stream;
            cameraVideo.style.display = 'inline-block';
            cameraPreview.style.display = 'none';
            startCameraBtn.style.display = 'none';
            capturePhotoBtn.style.display = 'inline-block';
            retakePhotoBtn.style.display = 'none';
        } catch (err) {
            console.error("Error accessing camera: ", err);
            alert("Could not access the camera. Please make sure you have granted permission.");
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraVideo.style.display = 'none';
        startCameraBtn.style.display = 'inline-block';
        capturePhotoBtn.style.display = 'none';
    }

    capturePhotoBtn.addEventListener('click', function() {
        const context = cameraCanvas.getContext('2d');
        cameraCanvas.width = cameraVideo.videoWidth || 300;
        cameraCanvas.height = cameraVideo.videoHeight || 300;
        context.drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);
        
        const dataUrl = cameraCanvas.toDataURL('image/png');
        cameraPreview.src = dataUrl;
        cameraImageBase64.value = dataUrl;
        
        // Clear file input when using camera
        imageInput.value = '';
        
        cameraVideo.style.display = 'none';
        cameraPreview.style.display = 'inline-block';
        capturePhotoBtn.style.display = 'none';
        retakePhotoBtn.style.display = 'inline-block';
        stopCamera(); // Stop stream after capture to save battery
    });

    retakePhotoBtn.addEventListener('click', function() {
        cameraImageBase64.value = '';
        startCamera();
    });
    
    // Clear camera data when a file is selected
    imageInput.addEventListener('change', function() {
        if (this.value) {
            cameraImageBase64.value = '';
            cameraPreview.src = '';
            cameraPreview.style.display = 'none';
            if (cameraSection.style.display === 'block') {
                cameraSection.style.display = 'none';
                stopCamera();
            }
        }
    });
});
</script>
<?php $extra_js = ob_get_clean(); ?>

<?php require_once '../includes/footer.php'; ?>
