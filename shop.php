<?php
// ============================================
// shop.php - Shop (Database Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ GET FILTERS ============
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1500;
$ratingFilter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'grid';

// ============ CATEGORIES ============
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll();

// ============ BUILD WHERE CLAUSE ============
$whereClause = "WHERE 1=1";
$params = [];

if ($searchQuery) {
    $whereClause .= " AND p.name LIKE ?";
    $params[] = "%$searchQuery%";
}

if ($categoryFilter) {
    $whereClause .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

if ($minPrice > 0) {
    $whereClause .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice < 1500) {
    $whereClause .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

if ($ratingFilter > 0) {
    $whereClause .= " AND p.rating >= ?";
    $params[] = $ratingFilter;
}

// ============ COUNT QUERY ============
$countSql = "SELECT COUNT(*) AS cnt FROM products p JOIN categories c ON p.category_id = c.id $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalProducts = (int)$stmt->fetch()['cnt'];

// ============ PAGINATION ============
$perPage = 12;
$totalPages = max(1, ceil($totalProducts / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;

// ============ SORT ============
$orderBy = "ORDER BY p.id ASC";
switch ($sortBy) {
    case 'price_low':  $orderBy = "ORDER BY p.price ASC"; break;
    case 'price_high': $orderBy = "ORDER BY p.price DESC"; break;
    case 'rating':     $orderBy = "ORDER BY p.rating DESC"; break;
    case 'name':       $orderBy = "ORDER BY p.name ASC"; break;
}

// ============ MAIN QUERY ============
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        $whereClause
        $orderBy
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pagedProducts = $stmt->fetchAll();

// ============ CART COUNT ============
$cartCount = getCartCount($pdo);

// ============ PAGE TITLE ============
$categoryName = '';
foreach ($categories as $c) {
    if ($c['slug'] === $categoryFilter) $categoryName = $c['name'];
}
$pageTitle = ($categoryName ? $categoryName : 'Shop') . ' - FreshMart';
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<section class="shop-page-header">
    <div class="container">
        <nav class="breadcrumb-nav">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <a href="shop.php">Shop</a>
            <?php if($categoryName): ?>
            <i class="fas fa-angle-right separator"></i>
            <span class="current"><?php echo htmlspecialchars($categoryName); ?></span>
            <?php endif; ?>
        </nav>
        <div class="shop-header-content">
            <h1><?php echo $categoryName ?: 'All Products'; ?></h1>
            <p>Showing <strong><?php echo $totalProducts; ?></strong> products<?php echo $searchQuery ? ' for "' . htmlspecialchars($searchQuery) . '"' : ''; ?></p>
        </div>
    </div>
</section>

<!-- ================= SHOP SECTION ================= -->
<section class="shop-section">
    <div class="container">
        <div class="row g-4">
            
            <!-- SIDEBAR FILTERS -->
            <div class="col-lg-3">
                <button class="filter-toggle-mobile" id="filterToggle">
                    <i class="fas fa-filter"></i> Show Filters
                </button>
                
                <aside class="shop-sidebar" id="shopSidebar">
                    <div class="sidebar-header">
                        <h3><i class="fas fa-filter"></i> Filters</h3>
                        <a href="shop.php" class="clear-filters">Clear All</a>
                    </div>
                    
                    <!-- Categories -->
                    <div class="filter-group">
                        <h4 class="filter-title">Categories</h4>
                        <ul class="filter-list">
                            <li>
                                <a href="shop.php" class="<?php echo !$categoryFilter ? 'active' : ''; ?>">
                                    <i class="fas fa-th-large"></i> All Categories
                                </a>
                            </li>
                            <?php foreach($categories as $cat): ?>
                            <li>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['cat' => $cat['slug'], 'page' => 1])); ?>" class="<?php echo $categoryFilter === $cat['slug'] ? 'active' : ''; ?>">
                                    <i class="fas <?php echo $cat['icon']; ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Price -->
                    <div class="filter-group">
                        <h4 class="filter-title">Price Range</h4>
                        <form method="GET" action="shop.php">
                            <?php foreach($_GET as $k => $v): if(!in_array($k, ['min_price','max_price','page'])): ?>
                            <input type="hidden" name="<?php echo $k; ?>" value="<?php echo htmlspecialchars($v); ?>">
                            <?php endif; endforeach; ?>
                            <div class="price-inputs">
                                <div class="price-input">
                                    <label>Min (Rs.)</label>
                                    <input type="number" name="min_price" value="<?php echo $minPrice; ?>" min="0" max="2000">
                                </div>
                                <div class="price-input">
                                    <label>Max (Rs.)</label>
                                    <input type="number" name="max_price" value="<?php echo $maxPrice; ?>" min="0" max="2000">
                                </div>
                            </div>
                            <button type="submit" class="btn-apply-filter">Apply</button>
                        </form>
                    </div>
                    
                    <!-- Rating -->
                    <div class="filter-group">
                        <h4 class="filter-title">Rating</h4>
                        <ul class="filter-list rating-filter">
                            <?php for($r=4; $r>=1; $r--): ?>
                            <li>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['rating' => $r, 'page' => 1])); ?>" class="<?php echo $ratingFilter === $r ? 'active' : ''; ?>">
                                    <span class="stars">
                                        <?php for($s=1; $s<=5; $s++): ?>
                                            <i class="fas fa-star <?php echo $s <= $r ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="rating-text">& up</span>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                </aside>
            </div>
            
            <!-- PRODUCTS -->
            <div class="col-lg-9">
                
                <!-- Toolbar -->
                <div class="shop-toolbar">
                    <div class="toolbar-left">
                        <span class="results-count">
                            Showing <strong><?php echo count($pagedProducts); ?></strong> of <strong><?php echo $totalProducts; ?></strong> products
                        </span>
                    </div>
                    <div class="toolbar-right">
                        <div class="view-toggle">
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['view'=>'grid'])); ?>" class="view-btn <?php echo $viewMode==='grid'?'active':''; ?>"><i class="fas fa-th"></i></a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['view'=>'list'])); ?>" class="view-btn <?php echo $viewMode==='list'?'active':''; ?>"><i class="fas fa-list"></i></a>
                        </div>
                        <div class="sort-box">
                            <label>Sort By:</label>
                            <select onchange="window.location.href = this.value;">
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'default','page'=>1])); ?>" <?php echo $sortBy==='default'?'selected':''; ?>>Default</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'price_low','page'=>1])); ?>" <?php echo $sortBy==='price_low'?'selected':''; ?>>Price: Low to High</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'price_high','page'=>1])); ?>" <?php echo $sortBy==='price_high'?'selected':''; ?>>Price: High to Low</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'rating','page'=>1])); ?>" <?php echo $sortBy==='rating'?'selected':''; ?>>Top Rated</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'name','page'=>1])); ?>" <?php echo $sortBy==='name'?'selected':''; ?>>Name: A-Z</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if(count($pagedProducts) === 0): ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>Try different filters or search keywords</p>
                    <a href="shop.php" class="btn-primary-custom"><i class="fas fa-rotate-left"></i> Reset Filters</a>
                </div>
                <?php else: ?>
                
                <div class="row g-4 <?php echo $viewMode==='list'?'list-view':''; ?>">
                    <?php foreach($pagedProducts as $i=>$p): ?>
                    <div class="<?php echo $viewMode==='list'?'col-12':'col-lg-4 col-md-6'; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($i%3)*100; ?>">
                        <div class="product-card <?php echo $viewMode==='list'?'product-card-list':''; ?>">
                            <div class="product-img">
                                <a href="product.php?id=<?php echo $p['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                </a>
                                <?php if($p['badge']): ?>
                                <span class="product-badge badge-<?php echo strtolower($p['badge']); ?>"><?php echo $p['badge']; ?></span>
                                <?php endif; ?>
                                <div class="product-actions">
                                    <button class="action-icon" title="Wishlist"><i class="far fa-heart"></i></button>
                                    <a href="product.php?id=<?php echo $p['id']; ?>" class="action-icon" title="Quick View"><i class="far fa-eye"></i></a>
                                    <button class="action-icon" title="Compare"><i class="fas fa-exchange-alt"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <span class="product-cat"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                <h5 class="product-name"><a href="product.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a></h5>
                                <div class="product-rating">
                                    <?php for($s=0;$s<5;$s++): ?>
                                        <i class="fas fa-star <?php echo $s<$p['rating']?'filled':''; ?>"></i>
                                    <?php endfor; ?>
                                    <span>(<?php echo rand(20,200); ?>)</span>
                                </div>
                                <div class="product-bottom">
                                    <div class="product-price">
                                        <span class="new-price">Rs. <?php echo $p['price']; ?></span>
                                        <span class="per-unit-small">/<?php echo $p['unit']; ?></span>
                                        <?php if(!empty($p['old_price']) && $p['old_price'] > 0): ?>
                                        <span class="old-price">Rs. <?php echo $p['old_price']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="add-cart-btn" title="Add to Cart" data-id="<?php echo $p['id']; ?>">
                                        <i class="fas fa-shopping-bag"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <div class="pagination-wrap">
                    <ul class="pagination">
                        <?php if($currentPage > 1): ?>
                        <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$currentPage-1])); ?>"><i class="fas fa-angle-left"></i></a></li>
                        <?php endif; ?>
                        <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$i])); ?>" class="<?php echo $i===$currentPage?'active':''; ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <?php if($currentPage < $totalPages): ?>
                        <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$currentPage+1])); ?>"><i class="fas fa-angle-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>