<?php
// ============================================
// admin/product-add.php - Add New Product
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ FETCH CATEGORIES ============
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// ============ HANDLE FORM SUBMIT ============
$errors = [];
$success = false;

$oldData = [
    'name' => '',
    'sku' => '',
    'category_id' => '',
    'description' => '',
    'price' => '',
    'old_price' => '',
    'unit' => 'kg',
    'stock' => '',
    'badge' => '',
    'image' => '',
    'features' => [''],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ============ GET DATA ============
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
    
    // ============ SAVE OLD DATA ============
    $oldData = [
        'name' => $name,
        'sku' => $sku,
        'category_id' => $categoryId,
        'description' => $description,
        'price' => $price,
        'old_price' => $oldPrice,
        'unit' => $unit,
        'stock' => $stock,
        'badge' => $badge,
        'image' => $image,
        'features' => $features,
    ];
    
    // ============ VALIDATION ============
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
    
    // Check SKU unique
    if (empty($errors['sku'])) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetch()) {
            $errors['sku'] = 'This SKU already exists. Please use a unique SKU.';
        }
    }
    
    // ============ INSERT ============
    if (empty($errors)) {
        // Filter features (remove empty)
        $features = array_filter($features, fn($f) => trim($f) !== '');
        $features = array_values($features);
        $featuresJson = !empty($features) ? json_encode($features) : null;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, sku, category_id, description, price, old_price, unit, stock, badge, image, features, rating) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0)
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
            ]);
            
            $_SESSION['admin_flash'] = [
                'type' => 'success',
                'msg' => 'Product "' . $name . '" added successfully!'
            ];
            header('Location: products.php');
            exit;
            
        } catch (Exception $e) {
            $errors['general'] = 'Failed to add product: ' . $e->getMessage();
        }
    }
}

// ============ PAGE VARIABLES ============
$pageTitle = 'Add New Product';
$pageSubtitle = 'Create a new product for your store';

include 'includes/admin-header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Add New Product</h1>
        <p>Fill in the details below to add a new product</p>
    </div>
    <div class="page-header-actions">
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
<form method="POST" action="product-add.php" id="productForm">
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
                            <input type="text" name="name" placeholder="e.g. Organic Tomatoes" value="<?php echo htmlspecialchars($oldData['name']); ?>" required>
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
                            <input type="text" name="sku" placeholder="e.g. VEG-TOM-001" value="<?php echo htmlspecialchars($oldData['sku']); ?>" required>
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
                                <option value="<?php echo $cat['id']; ?>" <?php echo $oldData['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
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
                                <option value="kg" <?php echo $oldData['unit'] === 'kg' ? 'selected' : ''; ?>>Per KG</option>
                                <option value="litre" <?php echo $oldData['unit'] === 'litre' ? 'selected' : ''; ?>>Per Litre</option>
                                <option value="pack" <?php echo $oldData['unit'] === 'pack' ? 'selected' : ''; ?>>Per Pack</option>
                                <option value="piece" <?php echo $oldData['unit'] === 'piece' ? 'selected' : ''; ?>>Per Piece</option>
                                <option value="dozen" <?php echo $oldData['unit'] === 'dozen' ? 'selected' : ''; ?>>Per Dozen</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="admin-form-group">
                            <label>Description</label>
                            <textarea name="description" rows="4" placeholder="Describe your product..."><?php echo htmlspecialchars($oldData['description']); ?></textarea>
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
                            <input type="number" name="price" placeholder="120" value="<?php echo $oldData['price']; ?>" min="0" step="0.01" required>
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
                            <input type="number" name="old_price" placeholder="180" value="<?php echo $oldData['old_price']; ?>" min="0" step="0.01">
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
                            <input type="number" name="stock" placeholder="50" value="<?php echo $oldData['stock']; ?>" min="0" required>
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
                    <?php foreach ($oldData['features'] as $i => $feature): ?>
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
                
                <small style="color:var(--gray); font-size:11px; display:block; margin-top:10px;">
                    <i class="fas fa-info-circle"></i> Add key features like "100% Organic", "No Preservatives", etc.
                </small>
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
                    <input type="url" name="image" id="imageInput" placeholder="https://images.unsplash.com/..." value="<?php echo htmlspecialchars($oldData['image']); ?>" required>
                    <?php if (isset($errors['image'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['image']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Image Preview -->
                <div style="margin-top:15px;">
                    <label style="font-size:12px; font-weight:600; color:var(--gray); display:block; margin-bottom:8px;">Preview:</label>
                    <div style="border:2px dashed var(--border); border-radius:12px; padding:15px; text-align:center; background:var(--cream); min-height:180px; display:flex; align-items:center; justify-content:center;">
                        <img id="imagePreview" 
                             src="<?php echo !empty($oldData['image']) ? htmlspecialchars($oldData['image']) : 'https://via.placeholder.com/200x200?text=No+Image'; ?>" 
                             alt="Preview" 
                             style="max-width:100%; max-height:200px; border-radius:10px; object-fit:cover;">
                    </div>
                </div>
            </div>
            
            <!-- Badge Card -->
            <div class="admin-card" data-aos="fade-up" data-aos-delay="200">
                <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-tag" style="color:var(--gold);"></i> Badge
                </h3>
                
                <div class="admin-form-group">
                    <label>Product Badge</label>
                    <select name="badge">
                        <option value="" <?php echo $oldData['badge'] === '' ? 'selected' : ''; ?>>No Badge</option>
                        <option value="Sale" <?php echo $oldData['badge'] === 'Sale' ? 'selected' : ''; ?>>Sale</option>
                        <option value="Hot" <?php echo $oldData['badge'] === 'Hot' ? 'selected' : ''; ?>>Hot</option>
                        <option value="New" <?php echo $oldData['badge'] === 'New' ? 'selected' : ''; ?>>New</option>
                    </select>
                    <small style="color:var(--gray); font-size:11px; margin-top:6px; display:block;">
                        Badge helps highlight the product on store
                    </small>
                </div>
                
                <!-- Action Buttons -->
                <div style="margin-top:25px; padding-top:20px; border-top:1px dashed var(--border);">
                    <button type="submit" class="btn-admin btn-primary-admin" style="width:100%; justify-content:center; padding:14px; font-size:14px;">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                    <a href="products.php" class="btn-admin btn-outline-admin" style="width:100%; justify-content:center; margin-top:10px; padding:12px;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
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
    } else {
        preview.src = 'https://via.placeholder.com/200x200?text=No+Image';
    }
    
    preview.onerror = function() {
        this.src = 'https://via.placeholder.com/200x200?text=Invalid+URL';
    };
});
</script>

<?php include 'includes/admin-footer.php'; ?>