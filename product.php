<?php
// ============================================
// product.php - Single Product Detail (DB Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ GET PRODUCT ID ============
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// ============ FETCH PRODUCT FROM DB ============
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS cat_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: shop.php');
    exit;
}

// ============ FEATURES (JSON se array) ============
$product['features'] = !empty($product['features']) ? json_decode($product['features'], true) : [];
if (!is_array($product['features'])) {
    $product['features'] = [];
}

// ============ GALLERY IMAGES ============
$galleryImages = [
    $product['image'],
    str_replace('w=500', 'w=800', $product['image']),
];

// ============ REVIEWS COUNT (demo) ============
$reviewsCount = rand(50, 250);

// ============ QUANTITY OPTIONS ============
$quantityOptions = [];
$basePrice = $product['price'];

if ($product['unit'] === 'kg' || $product['unit'] === 'litre') {
    $quantityOptions = [
        ['label' => '250 g', 'value' => 0.25, 'price' => round($basePrice * 0.25)],
        ['label' => '500 g', 'value' => 0.5,  'price' => round($basePrice * 0.5)],
        ['label' => '1 ' . $product['unit'], 'value' => 1, 'price' => $basePrice, 'selected' => true],
        ['label' => '2 ' . $product['unit'], 'value' => 2, 'price' => $basePrice * 2],
        ['label' => '5 ' . $product['unit'], 'value' => 5, 'price' => $basePrice * 5],
    ];
} elseif ($product['unit'] === 'pack' || $product['unit'] === 'piece') {
    $quantityOptions = [
        ['label' => '1 ' . $product['unit'], 'value' => 1, 'price' => $basePrice, 'selected' => true],
        ['label' => '2 ' . $product['unit'] . 's', 'value' => 2, 'price' => $basePrice * 2],
        ['label' => '3 ' . $product['unit'] . 's', 'value' => 3, 'price' => $basePrice * 3],
        ['label' => '5 ' . $product['unit'] . 's', 'value' => 5, 'price' => $basePrice * 5],
    ];
} elseif ($product['unit'] === 'dozen') {
    $quantityOptions = [
        ['label' => 'Half Dozen', 'value' => 0.5, 'price' => round($basePrice * 0.5)],
        ['label' => '1 Dozen', 'value' => 1, 'price' => $basePrice, 'selected' => true],
        ['label' => '2 Dozen', 'value' => 2, 'price' => $basePrice * 2],
    ];
} else {
    $quantityOptions = [
        ['label' => '1 ' . $product['unit'], 'value' => 1, 'price' => $basePrice, 'selected' => true],
    ];
}

// ============ FAKE REVIEWS ============
$reviews = [
    ['name' => 'Ayesha Khan',  'date' => '2 days ago',  'rating' => 5, 'text' => 'Bohat fresh products hain! Bilkul farm se aaye jaise. Quality zabardast hai. Highly recommended!'],
    ['name' => 'Ahmed Ali',    'date' => '1 week ago',  'rating' => 5, 'text' => 'Excellent quality. Delivery bhi time pe hui. Packing bilkul professional thi. Will order again.'],
    ['name' => 'Fatima Noor',  'date' => '2 weeks ago', 'rating' => 4, 'text' => 'Good quality. Thora sa price zyada hai but quality ke hisaab se theek hai.'],
];

// ============ PAGE TITLE ============
$pageTitle = $product['name'] . ' - FreshMart';
$cartCount = getCartCount($pdo);
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= BREADCRUMB ================= -->
<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb-nav">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <a href="shop.php">Shop</a>
            <i class="fas fa-angle-right separator"></i>
            <a href="shop.php?cat=<?php echo $product['cat_slug']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>
</section>

<!-- ================= PRODUCT DETAIL ================= -->
<section class="product-detail-section">
    <div class="container">
        <div class="row g-5">

            <!-- PRODUCT GALLERY -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="product-gallery">
                    <div class="main-image" id="mainImage">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="mainImg">
                        <?php if ($product['badge']): ?>
                            <span class="product-badge badge-<?php echo strtolower($product['badge']); ?>"><?php echo $product['badge']; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (count($galleryImages) > 1): ?>
                        <div class="thumbnail-list">
                            <?php foreach ($galleryImages as $i => $img): ?>
                                <div class="thumb <?php echo $i === 0 ? 'active' : ''; ?>" data-img="<?php echo htmlspecialchars($img); ?>">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Thumbnail <?php echo $i + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PRODUCT INFO -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="product-detail-info">

                    <span class="product-cat-badge">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>

                    <h1 class="product-detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <!-- Rating -->
                    <div class="product-detail-rating">
                        <div class="stars">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i < $product['rating'] ? 'filled' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text"><?php echo $product['rating']; ?>.0</span>
                        <span class="reviews-link">(<?php echo $reviewsCount; ?> customer reviews)</span>
                        <span class="stock-status"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)</span>
                    </div>

                    <!-- Price -->
                    <div class="product-detail-price">
                        <span class="current-price">Rs. <?php echo $product['price']; ?></span>
                        <span class="per-unit">/ <?php echo $product['unit']; ?></span>
                        <?php if (!empty($product['old_price']) && $product['old_price'] > 0): ?>
                            <span class="old-price">Rs. <?php echo $product['old_price']; ?></span>
                            <span class="discount-badge">
                                Save <?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <p class="product-short-desc"><?php echo htmlspecialchars($product['description']); ?></p>

                    <!-- Quantity Selector -->
                    <div class="quantity-section">
                        <label class="quantity-label">Select Quantity:</label>
                        <div class="quantity-options">
                            <?php foreach ($quantityOptions as $opt): ?>
                                <label class="qty-option <?php echo isset($opt['selected']) ? 'active' : ''; ?>">
                                    <input type="radio" name="quantity" value="<?php echo $opt['value']; ?>" data-price="<?php echo $opt['price']; ?>" <?php echo isset($opt['selected']) ? 'checked' : ''; ?>>
                                    <span class="qty-label"><?php echo $opt['label']; ?></span>
                                    <span class="qty-price">Rs. <?php echo $opt['price']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="product-actions-row">
                        <div class="qty-input">
                            <button type="button" class="qty-btn" id="qtyMinus"><i class="fas fa-minus"></i></button>
                            <input type="number" id="qtyInput" value="1" min="1" max="10">
                            <button type="button" class="qty-btn" id="qtyPlus"><i class="fas fa-plus"></i></button>
                        </div>
                        <button class="btn-add-cart-large" id="addToCartBtn" data-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                    </div>

                    <!-- Secondary Actions -->
                    <div class="secondary-actions">
                        <button class="btn-secondary-action" id="wishlistBtn">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </button>
                        <button class="btn-secondary-action" id="compareBtn">
                            <i class="fas fa-exchange-alt"></i> Compare
                        </button>
                    </div>

                    <!-- Meta Info -->
                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">SKU:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['sku']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Delivery:</span>
                            <span class="meta-value"><i class="fas fa-truck"></i> Free above Rs. 1500</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Returns:</span>
                            <span class="meta-value"><i class="fas fa-rotate-left"></i> 7 days easy return</span>
                        </div>
                    </div>

                    <!-- Share -->
                    <div class="product-share">
                        <span>Share:</span>
                        <a href="#" class="share-icon share-fb"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="share-icon share-tw"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="share-icon share-wa"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="share-icon share-pt"><i class="fab fa-pinterest"></i></a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= TABS ================= -->
<section class="product-tabs-section">
    <div class="container">
        <div class="tabs-wrapper">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="description"><i class="fas fa-align-left"></i> Description</button>
                <button class="tab-btn" data-tab="features"><i class="fas fa-list-check"></i> Features</button>
                <button class="tab-btn" data-tab="reviews"><i class="fas fa-star"></i> Reviews (<?php echo $reviewsCount; ?>)</button>
            </div>

            <!-- Description -->
            <div class="tab-content active" id="tab-description">
                <h3>About <?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p>Our team brings fresh products directly from farms every day, so you get the freshest and highest quality groceries. Every product passes a quality check before reaching you.</p>
            </div>

            <!-- Features -->
            <div class="tab-content" id="tab-features">
                <h3>Key Features</h3>
                <?php if (!empty($product['features'])): ?>
                <ul class="features-list">
                    <?php foreach ($product['features'] as $feat): ?>
                        <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($feat); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>No features listed for this product.</p>
                <?php endif; ?>
            </div>

            <!-- Reviews -->
            <div class="tab-content" id="tab-reviews">
                <div class="reviews-wrapper">
                    <div class="reviews-summary">
                        <div class="summary-score">
                            <h2><?php echo $product['rating']; ?>.0</h2>
                            <div class="stars">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i < $product['rating'] ? 'filled' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p>Based on <?php echo $reviewsCount; ?> reviews</p>
                        </div>
                        <div class="summary-bars">
                            <?php
                            $bars = [5 => 75, 4 => 15, 3 => 6, 2 => 3, 1 => 1];
                            foreach ($bars as $star => $percent):
                            ?>
                                <div class="bar-row">
                                    <span class="bar-label"><?php echo $star; ?> <i class="fas fa-star"></i></span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                    <span class="bar-percent"><?php echo $percent; ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="reviews-list">
                        <h3>Customer Reviews</h3>

                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-card">
                                <div class="review-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="review-body">
                                    <div class="review-header">
                                        <div>
                                            <h5><?php echo $rev['name']; ?></h5>
                                            <span class="review-date"><?php echo $rev['date']; ?></span>
                                        </div>
                                        <div class="review-stars">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i < $rev['rating'] ? 'filled' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="review-text"><?php echo $rev['text']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="review-form-wrap">
                            <h4>Write a Review</h4>
                            <form class="review-form" onsubmit="event.preventDefault(); alert('Thank you! Your review will be published after approval.');">
                                <div class="rating-input">
                                    <span>Your Rating:</span>
                                    <div class="stars-input">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="far fa-star" data-value="<?php echo $i; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" placeholder="Your Name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" placeholder="Your Email" required>
                                    </div>
                                    <div class="col-12">
                                        <textarea rows="4" placeholder="Write your review..." required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-primary-custom">
                                            <i class="fas fa-paper-plane"></i> Submit Review
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= RELATED PRODUCTS ================= -->
<section class="related-products-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">You May Also Like</span>
            <h2>Related Products</h2>
            <p>Similar products from our store</p>
        </div>
        <div class="row g-4">
            <?php
            // Same category ke products (current product hata ke)
            $stmt = $pdo->prepare("
                SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = ? AND p.id != ?
                ORDER BY RAND()
                LIMIT 4
            ");
            $stmt->execute([$product['category_id'], $product['id']]);
            $relatedProducts = $stmt->fetchAll();
            
            // Agar 4 se kam hain to random products se pura karo
            if (count($relatedProducts) < 4) {
                $needed = 4 - count($relatedProducts);
                $existingIds = array_column($relatedProducts, 'id');
                $existingIds[] = $product['id'];
                $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
                
                $stmt = $pdo->prepare("
                    SELECT p.*, c.name AS category_name
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.id NOT IN ($placeholders)
                    ORDER BY RAND()
                    LIMIT $needed
                ");
                $stmt->execute($existingIds);
                $moreProducts = $stmt->fetchAll();
                $relatedProducts = array_merge($relatedProducts, $moreProducts);
            }
            
            foreach ($relatedProducts as $i => $p):
            ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="product-card">
                        <div class="product-img">
                            <a href="product.php?id=<?php echo $p['id']; ?>">
                                <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            </a>
                            <?php if ($p['badge']): ?>
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
                                <?php for ($s = 0; $s < 5; $s++): ?>
                                    <i class="fas fa-star <?php echo $s < $p['rating'] ? 'filled' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span>(<?php echo rand(20, 200); ?>)</span>
                            </div>
                            <div class="product-bottom">
                                <div class="product-price">
                                    <span class="new-price">Rs. <?php echo $p['price']; ?></span>
                                    <span class="per-unit-small">/<?php echo $p['unit']; ?></span>
                                    <?php if (!empty($p['old_price']) && $p['old_price'] > 0): ?>
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
    </div>
</section>

<?php
include 'includes/footer.php';
?>