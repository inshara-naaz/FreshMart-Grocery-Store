<?php
// ============================================
// admin/products.php - Products Management
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
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$deleteId]);
    $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Product deleted successfully'];
    header('Location: products.php');
    exit;
}

// ============ FILTERS ============
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$stockFilter    = isset($_GET['stock']) ? $_GET['stock'] : '';
$sortBy         = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// ============ PAGINATION — 100% SAFE INT ============
$perPage = 10;

// Page number get karo — ONLY if not empty
$currentPage = 1;
if (isset($_GET['page']) && $_GET['page'] !== '' && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
    if ($currentPage < 1) {
        $currentPage = 1;
    }
}

// ============ FETCH CATEGORIES ============
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// ============ BUILD WHERE ============
$where = "WHERE 1=1";
$params = [];

if ($searchQuery !== '') {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

if ($categoryFilter > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($stockFilter === 'low') {
    $where .= " AND p.stock > 0 AND p.stock <= 15";
} elseif ($stockFilter === 'out') {
    $where .= " AND p.stock = 0";
} elseif ($stockFilter === 'in') {
    $where .= " AND p.stock > 15";
}

// ============ SORT ============
$orderBy = "ORDER BY p.id DESC";
if ($sortBy === 'name') {
    $orderBy = "ORDER BY p.name ASC";
} elseif ($sortBy === 'price_low') {
    $orderBy = "ORDER BY p.price ASC";
} elseif ($sortBy === 'price_high') {
    $orderBy = "ORDER BY p.price DESC";
} elseif ($sortBy === 'stock_low') {
    $orderBy = "ORDER BY p.stock ASC";
} elseif ($sortBy === 'oldest') {
    $orderBy = "ORDER BY p.id ASC";
}

// ============ COUNT ============
$countSql = "SELECT COUNT(*) AS cnt FROM products p $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$countRow = $stmt->fetch();
$totalProducts = $countRow ? (int)$countRow['cnt'] : 0;

// ============ CALCULATE PAGES ============
$totalPages = (int)ceil($totalProducts / $perPage);
if ($totalPages < 1) {
    $totalPages = 1;
}

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;

// ============ FETCH PRODUCTS ============
$perPageInt = (int)$perPage;
$offsetInt = (int)$offset;

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where 
        $orderBy 
        LIMIT $perPageInt OFFSET $offsetInt";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ============ MINI STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products");
$statTotal = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products WHERE stock > 0 AND stock <= 15");
$statLow = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products WHERE stock = 0");
$statOut = (int)$stmt->fetch()['cnt'];

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ PAGE VARIABLES ============
$pageTitle = 'Products';
$pageSubtitle = 'Manage your store inventory';

include 'includes/admin-header.php';
?>

<?php if ($flash): ?>
<div style="background:#d4edda; color:#155724; padding:14px 20px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:10px; border-left:4px solid #28a745;">
    <i class="fas fa-check-circle"></i>
    <?php echo htmlspecialchars($flash['msg']); ?>
</div>
<?php endif; ?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Products Management</h1>
        <p>Total <?php echo (int)$totalProducts; ?> product<?php echo $totalProducts != 1 ? 's' : ''; ?> found</p>
    </div>
    <div class="page-header-actions">
        <a href="product-add.php" class="btn-admin btn-primary-admin">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>
</div>

<!-- ================= MINI STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
    <div class="stat-admin-card stat-green">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-box"></i></div></div>
        <div class="stat-admin-value"><?php echo (int)$statTotal; ?></div>
        <div class="stat-admin-label">Total Products</div>
    </div>
    <div class="stat-admin-card stat-orange">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
        <div class="stat-admin-value"><?php echo (int)$statLow; ?></div>
        <div class="stat-admin-label">Low Stock (≤15)</div>
    </div>
    <div class="stat-admin-card stat-red">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-times-circle"></i></div></div>
        <div class="stat-admin-value"><?php echo (int)$statOut; ?></div>
        <div class="stat-admin-label">Out of Stock</div>
    </div>
</div>

<!-- ================= FILTER BAR ================= -->
<div class="admin-card" style="padding: 20px; margin-bottom: 25px;">
    <form method="GET" action="products.php" style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:12px; align-items:end;">

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Search</label>
            <input type="text" name="search" placeholder="Search by name or SKU..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Category</label>
            <select name="cat">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Stock</label>
            <select name="stock">
                <option value="">All</option>
                <option value="in" <?php echo $stockFilter === 'in' ? 'selected' : ''; ?>>In Stock</option>
                <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Low</option>
                <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Out</option>
            </select>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Sort</label>
            <select name="sort">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price ↑</option>
                <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price ↓</option>
                <option value="stock_low" <?php echo $sortBy === 'stock_low' ? 'selected' : ''; ?>>Stock ↑</option>
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-admin btn-primary-admin" style="padding:12px 18px;">
                <i class="fas fa-filter"></i>
            </button>
            <a href="products.php" class="btn-admin btn-outline-admin" style="padding:12px 18px;">
                <i class="fas fa-redo"></i>
            </a>
        </div>

    </form>
</div>

<!-- ================= PRODUCTS TABLE ================= -->
<div class="admin-table-wrap">

    <div class="admin-table-header">
        <h3><i class="fas fa-list"></i> All Products</h3>
        <div style="font-size:12px; color:var(--gray);">
            Showing <strong><?php echo count($products); ?></strong> of <strong><?php echo (int)$totalProducts; ?></strong>
        </div>
    </div>

    <?php if (empty($products)): ?>

        <div class="admin-empty">
            <i class="fas fa-box-open"></i>
            <h3>No products found</h3>
            <p>Try adjusting filters</p>
        </div>

    <?php else: ?>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Badge</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $stockColor = $p['stock'] == 0 ? '#E53935' : ($p['stock'] <= 15 ? '#FFB300' : '#43A047');
                    ?>
                    <tr>
                        <td>
                            <div class="table-product">
                                <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                <div class="table-product-info">
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <span>SKU: <?php echo htmlspecialchars($p['sku']); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="background:#FAF8F3; color:#212121; padding:4px 12px; border-radius:50px; font-size:11px; font-weight:600;">
                                <?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td><strong style="color:#1B5E20;">Rs. <?php echo number_format($p['price']); ?></strong></td>
                        <td>
                            <span style="background:<?php echo $stockColor; ?>; color:white; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:700;">
                                <?php echo (int)$p['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($p['badge'])): ?>
                                <span class="table-badge badge-<?php echo strtolower($p['badge']); ?>"><?php echo htmlspecialchars($p['badge']); ?></span>
                            <?php else: ?>
                                <span style="color:#616161;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end;">
                                <a href="../product.php?id=<?php echo (int)$p['id']; ?>" target="_blank" class="table-action-btn view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="product-edit.php?id=<?php echo (int)$p['id']; ?>" class="table-action-btn edit" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="confirmDeleteProduct(<?php echo (int)$p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>')" class="table-action-btn delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ================= PAGINATION ============ -->
        <?php 
        $cp = (int)$currentPage;
        $pp = (int)$perPage;
        $tp = (int)$totalProducts;
        $tpg = (int)$totalPages;
        
        if ($tpg > 1): 
            $startNum = (($cp - 1) * $pp) + 1;
            $endNum = min($cp * $pp, $tp);
        ?>
        <div style="padding: 22px 25px; border-top: 1px solid #E8E8E8; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background: #FAF8F3;">

            <div style="font-size: 13px; color: #616161;">
                Page <strong style="color:#1B5E20;"><?php echo $cp; ?></strong> 
                of <strong style="color:#1B5E20;"><?php echo $tpg; ?></strong>
                <span style="margin-left:10px; color:#999;">|</span>
                <span style="margin-left:10px;">
                    Showing <strong><?php echo $startNum; ?>-<?php echo $endNum; ?></strong> of <strong><?php echo $tp; ?></strong>
                </span>
            </div>

            <div style="display: flex; gap: 6px; flex-wrap:wrap;">

                <?php if ($cp > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $cp - 1])); ?>" 
                   style="min-width:38px; height:38px; padding: 0 12px; border-radius:10px; background:#FFF; border:2px solid #E8E8E8; display:flex; align-items:center; justify-content:center; color:#212121; text-decoration:none; font-size: 13px; font-weight: 600;">
                    <i class="fas fa-chevron-left"></i> Prev
                </a>
                <?php endif; ?>

                <?php 
                $startPage = max(1, $cp - 2);
                $endPage = min($tpg, $cp + 2);
                for ($i = $startPage; $i <= $endPage; $i++): 
                ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                   style="min-width:38px; height:38px; padding: 0 12px; border-radius:10px; background:<?php echo $i === $cp ? '#2E7D32' : '#FFF'; ?>; border:2px solid <?php echo $i === $cp ? '#2E7D32' : '#E8E8E8'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $i === $cp ? '#FFF' : '#212121'; ?>; text-decoration:none; font-size:13px; font-weight:700;">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($cp < $tpg): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $cp + 1])); ?>" 
                   style="min-width:38px; height:38px; padding: 0 12px; border-radius:10px; background:#FFF; border:2px solid #E8E8E8; display:flex; align-items:center; justify-content:center; color:#212121; text-decoration:none; font-size: 13px; font-weight: 600;">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>
<!-- End admin-table-wrap -->

<script>
function confirmDeleteProduct(id, name) {
    if (confirm('Are you sure you want to delete "' + name + '"?')) {
        window.location.href = 'products.php?delete=' + id;
    }
}

// ============ AUTO SUBMIT ON FILTER CHANGE ============
document.addEventListener('DOMContentLoaded', function() {
    // Product filter form dhundo
    const filterForm = document.querySelector('form[action="products.php"]');
    
    if (filterForm) {
        // Saare select dropdowns pe change event lagao
        filterForm.querySelectorAll('select').forEach(function(select) {
            select.addEventListener('change', function() {
                // Form submit karo (auto filter)
                filterForm.submit();
            });
        });
        
        // Search box pe Enter key
        const searchInput = filterForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    filterForm.submit();
                }
            });
        }
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>