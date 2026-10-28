<?php
// ============================================
// admin/product-edit.php - Edit Product
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ GET PRODUCT ID ============
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'msg' => 'Invalid product ID'];
    header('Location: products.php');
    exit;
}

// ============ FETCH PRODUCT ============
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'msg' => 'Product not found'];
    header('Location: products.php');
    exit;
}

// ============ FETCH CATEGORIES ============
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// ============ DECODE FEATURES ============
$features = [];
if (!empty($product['features'])) {
    $decoded = json_decode($product['features'], true);
    if (is_array($decoded)) {
        $features = $decoded;
    }
}
if (empty($features)) {
    $features = [''];
}

// ============ HANDLE FORM SUBMIT ============
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data
    $name        = trim($_POST['name'] ?? '');
    $sku         = trim($_POST['sku'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $oldPrice    = (float)($_POST['old_price'] ?? 0);
    $unit        = trim($_POST['unit'] ?? 'kg');
    $stock       = (int)($_POST['stock'] ?? 0);
    $badge       = trim($_POST['badge'] ?? '');
    $image       = trim($_POST['image'] ?? '');
    $features    = $_POST['features'] ?? [''];
    
    // Validation
    if (empty($name) || strlen($name) < 3) {
        $errors['name'] = 'Product name must be at least 3 characters';
    }
    if (empty($sku)) {
        $errors['sku'] = 'SKU is required';
    }
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a category';
    }
    if ($price <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }
    if ($oldPrice > 0 && $oldPrice <= $price) {
        $errors['old_price'] = 'Old price must be greater than current price';
    }
    if ($stock < 0) {
        $errors['stock'] = 'Stock cannot be negative';
    }
    if (empty($image)) {
        $errors['image'] = 'Product image URL is required';
    }
    
    // Check SKU unique (except this product)
    if (empty($errors['sku'])) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $stmt->execute([$sku, $productId]);
        if ($stmt->fetch()) {
            $errors['sku'] = 'This SKU already exists. Please use a unique SKU.';
        }
    }
    
    // Update
    if (empty($errors)) {
        $features = array_filter($features, fn($f) => trim($f) !== '');
        $features = array_values($features);
        $featuresJson = !empty($features) ? json_encode($features) : null;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, sku = ?, category_id = ?, description = ?, 
                    price = ?, old_price = ?, unit = ?, stock = ?, 
                    badge = ?, image = ?, features = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $name,
                $sku,
                $categoryId,
                $description,
                $price,
                $oldPrice,
                $unit,
                $stock,
                $badge,
                $image,
                $featuresJson,
                $productId,
            ]);
            
            $_SESSION['admin_flash'] = [
                'type' => 'success',
                'msg' => 'Product "' . $name . '" updated successfully!'
            ];
            header('Location: products.php');
            exit;
            
        } catch (Exception $e) {
            $errors['general'] = 'Failed to update product: ' . $e->getMessage();
        }
    }
    
    // Agar error hai, to form mein old values dikhane ke liye
    $product['name'] = $name;
    $product['sku'] = $sku;
    $product['category_id'] = $categoryId;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['old_price'] = $oldPrice;
    $product['unit'] = $unit;
    $product['stock'] = $stock;
    $product['badge'] = $badge;
    $product['image'] = $image;
}

// ============ PAGE VARIABLES ============
$pageTitle = 'Edit Product';
$pageSubtitle = 'Editing: ' . htmlspecialchars($product['name']);

include 'includes/admin-header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Product</h1>
        <p>Editing <strong style="color:var(--primary);"><?php echo htmlspecialchars($product['name']); ?></strong> · SKU: <?php echo htmlspecialchars($product['sku']); ?></p>
    </div>
    <div class="page-header-actions">
        <a href="../product.php?id=<?php echo $productId; ?>" target="_blank" class="btn-admin btn-outline-admin">
            <i class="fas fa-eye"></i> View on Store
        </a>
        <a href="products.php" class="btn-admin btn-outline-admin">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<!-- ================= GENERAL ERROR ================= -->
<?php if (isset($errors['general'])): ?>
<div style="background:#f8d7da; color:#721c24; padding:14px 20px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:10px; border-left:4px solid #dc3545;" data-aos="fade-down">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo htmlspecialchars($errors['general']); ?>
</div>
<?php endif; ?>

<!-- ================= FORM ================= -->
<form method="POST" action="product-edit.php?id=<?php echo $productId; ?>" id="productForm">
    <div class="row g-4">
        
        <!-- ================= LEFT COLUMN ================= -->
        <div class="col-lg-8">
            
            <!-- Basic Info Card -->
            <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-info-circle" style="color:var(--primary);"></i> Basic Information
                </h3>
                
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="admin-form-group">
                            <label>Product Name <span class="req">*</span></label>
                            <input type="text" name="name" placeholder="e.g. Organic Tomatoes" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                            <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['name']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>SKU <span class="req">*</span></label>
                            <input type="text" name="sku" placeholder="e.g. VEG-TOM-001" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                            <?php if (isset($errors['sku'])): ?>
                            <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['sku']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Category <span class="req">*</span></label>
                            <select name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                            <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['category_id']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Unit <span class="req">*</span></label>
                            <select name="unit" required>
                                <option value="kg" <?php echo $product['unit'] === 'kg' ? 'selected' : ''; ?>>Per KG</option>
                                <option value="litre" <?php echo $product['unit'] === 'litre' ? 'selected' : ''; ?>>Per Litre</option>
                                <option value="pack" <?php echo $product['unit'] === 'pack' ? 'selected' : ''; ?>>Per Pack</option>
                                <option value="piece" <?php echo $product['unit'] === 'piece' ? 'selected' : ''; ?>>Per Piece</option>
                                <option value="dozen" <?php echo $product['unit'] === 'dozen' ? 'selected' : ''; ?>>Per Dozen</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="admin-form-group">
                            <label>Description</label>
                            <textarea name="description" rows="4" placeholder="Describe your product..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pricing Card -->
            <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-money-bill-wave" style="color:var(--accent);"></i> Pricing & Stock
                </h3>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>Current Price (Rs.) <span class="req">*</span></label>
                            <input type="number" name="price" placeholder="120" value="<?php echo $product['price']; ?>" min="0" step="0.01" required>
                            <?php if (isset($errors['price'])): ?>
                            <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['price']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>Old Price (Rs.)</label>
                            <input type="number" name="old_price" placeholder="180" value="<?php echo $product['old_price']; ?>" min="0" step="0.01">
                            <small style="color:var(--gray); font-size:11px; margin-top:4px; display:block;">For discount display (optional)</small>
                            <?php if (isset($errors['old_price'])): ?>
                            <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['old_price']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>Stock Quantity <span class="req">*</span></label>
                            <input type="number" name="stock" placeholder="50" value="<?php echo $product['stock']; ?>" min="0" required>
                            <?php if (isset($errors['stock'])): ?>
                            <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['stock']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Features Card -->
            <div class="admin-card" data-aos="fade-up">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-list-check" style="color:var(--info);"></i> Product Features
                    </span>
                    <button type="button" onclick="addFeature()" class="btn-admin btn-outline-admin" style="padding:6px 12px; font-size:12px;">
                        <i class="fas fa-plus"></i> Add Feature
                    </button>
                </h3>
                
                <div id="featuresContainer">
                    <?php foreach ($features as $i => $feature): ?>
                    <div class="feature-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                        <div style="flex:1;">
                            <input type="text" name="features[]" placeholder="e.g. 100% Organic" value="<?php echo htmlspecialchars($feature); ?>">
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="table-action-btn delete" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
        
        <!-- ================= RIGHT COLUMN ================= -->
        <div class="col-lg-4">
            
            <!-- Image Card -->
            <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up" data-aos-delay="100">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-image" style="color:var(--primary);"></i> Product Image
                </h3>
                
                <div class="admin-form-group">
                    <label>Image URL <span class="req">*</span></label>
                    <input type="url" name="image" id="imageInput" placeholder="https://images.unsplash.com/..." value="<?php echo htmlspecialchars($product['image']); ?>" required>
                    <?php if (isset($errors['image'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['image']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top:15px;">
                    <label style="font-size:12px; font-weight:600; color:var(--gray); display:block; margin-bottom:8px;">Preview:</label>
                    <div style="border:2px dashed var(--border); border-radius:12px; padding:15px; text-align:center; background:var(--cream); min-height:180px; display:flex; align-items:center; justify-content:center;">
                        <img id="imagePreview" 
                             src="<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="Preview" 
                             style="max-width:100%; max-height:200px; border-radius:10px; object-fit:cover;">
                    </div>
                </div>
            </div>
            
            <!-- Badge Card -->
            <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up" data-aos-delay="200">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-tag" style="color:var(--gold);"></i> Badge
                </h3>
                
                <div class="admin-form-group">
                    <label>Product Badge</label>
                    <select name="badge">
                        <option value="" <?php echo $product['badge'] === '' ? 'selected' : ''; ?>>No Badge</option>
                        <option value="Sale" <?php echo $product['badge'] === 'Sale' ? 'selected' : ''; ?>>Sale</option>
                        <option value="Hot" <?php echo $product['badge'] === 'Hot' ? 'selected' : ''; ?>>Hot</option>
                        <option value="New" <?php echo $product['badge'] === 'New' ? 'selected' : ''; ?>>New</option>
                    </select>
                </div>
            </div>
            
            <!-- Info Card -->
            <div class="admin-card" style="margin-bottom: 20px; background:var(--cream);" data-aos="fade-up" data-aos-delay="300">
                <h3 style="font-size:14px; font-weight:700; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-info-circle" style="color:var(--info);"></i> Product Info
                </h3>
                <div style="font-size:12px; color:var(--gray); line-height:2;">
                    <div><strong style="color:var(--dark);">ID:</strong> #<?php echo $product['id']; ?></div>
                    <div><strong style="color:var(--dark);">Rating:</strong> ⭐ <?php echo $product['rating']; ?></div>
                    <div><strong style="color:var(--dark);">Created:</strong> <?php echo date('d M Y', strtotime($product['created_at'])); ?></div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="admin-card" data-aos="fade-up" data-aos-delay="400">
                <button type="submit" class="btn-admin btn-primary-admin" style="width:100%; justify-content:center; padding:14px; font-size:14px;">
                    <i class="fas fa-save"></i> Update Product
                </button>
                <a href="products.php" class="btn-admin btn-outline-admin" style="width:100%; justify-content:center; margin-top:10px; padding:12px;">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="button" onclick="confirmDelete()" class="btn-admin btn-danger-admin" style="width:100%; justify-content:center; margin-top:10px; padding:12px;">
                    <i class="fas fa-trash"></i> Delete Product
                </button>
            </div>
            
        </div>
        
    </div>
</form>

<script>
// ============ ADD FEATURE ============
function addFeature() {
    const container = document.getElementById('featuresContainer');
    const row = document.createElement('div');
    row.className = 'feature-row';
    row.style.cssText = 'display:flex; gap:10px; margin-bottom:10px; align-items:center;';
    row.innerHTML = `
        <div style="flex:1;">
            <input type="text" name="features[]" placeholder="e.g. 100% Organic">
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="table-action-btn delete" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(row);
}

// ============ IMAGE PREVIEW ============
document.getElementById('imageInput').addEventListener('input', function() {
    const url = this.value.trim();
    const preview = document.getElementById('imagePreview');
    
    if (url) {
        preview.src = url;
    }
    
    preview.onerror = function() {
        this.src = 'https://via.placeholder.com/200x200?text=Invalid+URL';
    };
});

// ============ DELETE CONFIRM ============
function confirmDelete() {
    if (confirm('Are you sure you want to DELETE "<?php echo htmlspecialchars(addslashes($product['name'])); ?>"?\n\nThis action cannot be undone!')) {
        window.location.href = 'products.php?delete=<?php echo $productId; ?>';
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>