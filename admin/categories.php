<?php
// ============================================
// admin/categories.php - Categories Management
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ HANDLE DELETE ============
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM products WHERE category_id = ?");
    $stmt->execute([$deleteId]);
    $productCount = (int)$stmt->fetch()['cnt'];
    
    if ($productCount > 0) {
        $_SESSION['admin_flash'] = [
            'type' => 'error',
            'msg' => "Cannot delete! This category has {$productCount} products."
        ];
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$deleteId]);
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Category deleted successfully'];
    }
    
    header('Location: categories.php');
    exit;
}

// ============ HANDLE ADD/EDIT ============
$errors = [];
$editMode = false;
$editCategory = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'icon' => 'fa-tag',
    'image' => '',
];

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
    
    if ($editCategory) {
        $editMode = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catId = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    $icon  = trim($_POST['icon'] ?? 'fa-tag');
    $image = trim($_POST['image'] ?? '');
    
    if (empty($slug) && !empty($name)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
    
    if (empty($name) || strlen($name) < 2) {
        $errors['name'] = 'Category name must be at least 2 characters';
    }
    if (empty($slug)) {
        $errors['slug'] = 'Slug is required';
    }
    if (empty($image)) {
        $errors['image'] = 'Category image URL is required';
    }
    
    if (empty($errors['name'])) {
        if ($catId > 0) {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $catId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
        }
        if ($stmt->fetch()) {
            $errors['name'] = 'This category name already exists';
        }
    }
    
    if (empty($errors['slug'])) {
        if ($catId > 0) {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $catId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        if ($stmt->fetch()) {
            $errors['slug'] = 'This slug already exists';
        }
    }
    
    if (empty($errors)) {
        try {
            if ($catId > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ?, image = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $icon, $image, $catId]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Category updated successfully!'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $icon, $image]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Category added successfully!'];
            }
            header('Location: categories.php');
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Failed: ' . $e->getMessage();
        }
    }
    
    $editCategory = [
        'id' => $catId,
        'name' => $name,
        'slug' => $slug,
        'icon' => $icon,
        'image' => $image,
    ];
    $editMode = ($catId > 0);
}

$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
    FROM categories c 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$pageTitle = 'Categories';
$pageSubtitle = 'Manage product categories';

include 'includes/admin-header.php';
?>

<!-- Flash Message -->
<?php if ($flash): ?>
<div style="background:<?php echo $flash['type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo $flash['type'] === 'success' ? '#155724' : '#721c24'; ?>; padding:14px 20px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:10px; border-left:4px solid <?php echo $flash['type'] === 'success' ? '#28a745' : '#dc3545'; ?>;">
    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($flash['msg']); ?>
</div>
<?php endif; ?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Categories Management</h1>
        <p>Total <?php echo count($categories); ?> categor<?php echo count($categories) != 1 ? 'ies' : 'y'; ?></p>
    </div>
</div>

<div class="row g-4">
    
    <!-- ================= LEFT: FORM ================= -->
    <div class="col-lg-4">
        <div class="admin-card" style="position:sticky; top:90px;">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                <i class="fas fa-<?php echo $editMode ? 'pen' : 'plus-circle'; ?>" style="color:var(--primary);"></i>
                <?php echo $editMode ? 'Edit Category' : 'Add New Category'; ?>
            </h3>
            
            <?php if (isset($errors['general'])): ?>
            <div style="background:#f8d7da; color:#721c24; padding:12px 16px; border-radius:10px; margin-bottom:15px; font-size:13px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="categories.php<?php echo $editMode ? '?edit=' . $editCategory['id'] : ''; ?>">
                <input type="hidden" name="id" value="<?php echo (int)$editCategory['id']; ?>">
                
                <div class="admin-form-group">
                    <label>Category Name <span class="req">*</span></label>
                    <input type="text" name="name" id="catName" placeholder="e.g. Fresh Vegetables" value="<?php echo htmlspecialchars($editCategory['name']); ?>" required>
                    <?php if (isset($errors['name'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['name']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="admin-form-group">
                    <label>Slug <span class="req">*</span></label>
                    <input type="text" name="slug" id="catSlug" placeholder="fresh-vegetables" value="<?php echo htmlspecialchars($editCategory['slug']); ?>" required>
                    <?php if (isset($errors['slug'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['slug']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="admin-form-group">
                    <label>Font Awesome Icon</label>
                    <input type="text" name="icon" id="catIcon" placeholder="fa-carrot" value="<?php echo htmlspecialchars($editCategory['icon']); ?>">
                </div>
                
                <div class="admin-form-group">
                    <label>Image URL <span class="req">*</span></label>
                    <input type="url" name="image" id="catImage" placeholder="https://images.unsplash.com/..." value="<?php echo htmlspecialchars($editCategory['image']); ?>" required>
                    <?php if (isset($errors['image'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['image']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Preview -->
                <div style="margin-bottom:15px;">
                    <label style="font-size:12px; font-weight:600; color:var(--gray); display:block; margin-bottom:8px;">Live Preview:</label>
                    <div style="border:2px dashed var(--border); border-radius:12px; padding:12px; background:var(--cream); display:flex; align-items:center; gap:12px;">
                        <div style="width:50px; height:50px; background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:white; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                            <i class="fas <?php echo htmlspecialchars($editCategory['icon']); ?>" id="iconPreviewI"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <strong style="display:block; font-size:13px;" id="namePreview"><?php echo htmlspecialchars($editCategory['name'] ?: 'Category Name'); ?></strong>
                            <span style="font-size:11px; color:var(--gray);" id="slugPreview"><?php echo htmlspecialchars($editCategory['slug'] ?: 'slug-here'); ?></span>
                        </div>
                        <div style="width:50px; height:50px; border-radius:10px; overflow:hidden; background:#EEE; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            <img id="imagePreview" 
                                 src="<?php echo htmlspecialchars($editCategory['image']); ?>" 
                                 alt="Preview" 
                                 style="width:100%; height:100%; object-fit:cover;">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-admin btn-primary-admin" style="width:100%; justify-content:center; padding:14px;">
                    <i class="fas fa-<?php echo $editMode ? 'save' : 'plus'; ?>"></i>
                    <?php echo $editMode ? 'Update Category' : 'Add Category'; ?>
                </button>
                
                <?php if ($editMode): ?>
                <a href="categories.php" class="btn-admin btn-outline-admin" style="width:100%; justify-content:center; margin-top:10px;">
                    <i class="fas fa-times"></i> Cancel Edit
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- ================= RIGHT: TABLE ================= -->
    <div class="col-lg-8">
        <div class="admin-table-wrap">
            
            <div class="admin-table-header">
                <h3><i class="fas fa-th-large"></i> All Categories</h3>
                <div style="font-size:12px; color:var(--gray);">
                    <?php echo count($categories); ?> categor<?php echo count($categories) != 1 ? 'ies' : 'y'; ?>
                </div>
            </div>
            
            <?php if (empty($categories)): ?>
            
            <div class="admin-empty">
                <i class="fas fa-th-large"></i>
                <h3>No categories yet</h3>
                <p>Add your first category</p>
            </div>
            
            <?php else: ?>
            
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <div class="table-product">
                                    <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                                    <div class="table-product-info">
                                        <strong>
                                            <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>" style="color:var(--primary); margin-right:6px;"></i>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="font-size:11px; background:var(--cream); padding:3px 8px; border-radius:5px;">
                                    <?php echo htmlspecialchars($cat['slug']); ?>
                                </code>
                            </td>
                            <td>
                                <?php if ($cat['product_count'] > 0): ?>
                                <a href="products.php?cat=<?php echo (int)$cat['id']; ?>" style="background:rgba(46,125,50,0.1); color:var(--primary-dark); padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700; text-decoration:none;">
                                    <?php echo (int)$cat['product_count']; ?> products
                                </a>
                                <?php else: ?>
                                <span style="background:rgba(97,97,97,0.1); color:var(--gray); padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700;">
                                    0 products
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="../shop.php?cat=<?php echo urlencode($cat['slug']); ?>" target="_blank" class="table-action-btn view" title="View on Store">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="categories.php?edit=<?php echo (int)$cat['id']; ?>" class="table-action-btn edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDeleteCat(<?php echo (int)$cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>', <?php echo (int)$cat['product_count']; ?>)" class="table-action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php endif; ?>
            
        </div>
    </div>
    
</div>

<script>
// ============ LIVE PREVIEW ============
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('catName');
    const slugInput = document.getElementById('catSlug');
    const iconInput = document.getElementById('catIcon');
    const imageInput = document.getElementById('catImage');
    
    const namePreview = document.getElementById('namePreview');
    const slugPreview = document.getElementById('slugPreview');
    const iconPreviewI = document.getElementById('iconPreviewI');
    const imagePreview = document.getElementById('imagePreview');
    
    // Name input
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            const name = this.value || 'Category Name';
            if (namePreview) namePreview.textContent = name;
            
            if (slugInput && !slugInput.dataset.manual) {
                const autoSlug = name.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');
                slugInput.value = autoSlug;
                if (slugPreview) slugPreview.textContent = autoSlug || 'slug-here';
            }
        });
    }
    
    // Slug input
    if (slugInput) {
        slugInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
            if (slugPreview) slugPreview.textContent = this.value || 'slug-here';
        });
    }
    
    // Icon input
    if (iconInput && iconPreviewI) {
        iconInput.addEventListener('input', function() {
            const icon = this.value || 'fa-tag';
            iconPreviewI.className = 'fas ' + icon;
        });
    }
    
    // Image input — Live preview
    if (imageInput && imagePreview) {
        imageInput.addEventListener('input', function() {
            const url = this.value.trim();
            if (url) {
                imagePreview.src = url;
            }
        });
        
        // Paste event (instant update)
        imageInput.addEventListener('paste', function() {
            const input = this;
            setTimeout(function() {
                const url = input.value.trim();
                if (url) {
                    imagePreview.src = url;
                }
            }, 50);
        });
    }
});

// ============ DELETE CONFIRM ============
function confirmDeleteCat(id, name, productCount) {
    if (productCount > 0) {
        alert('⚠️ Cannot delete "' + name + '"\n\nThis category has ' + productCount + ' products.\n\nPlease move or delete these products first.');
        return;
    }
    if (confirm('Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone.')) {
        window.location.href = 'categories.php?delete=' + id;
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>