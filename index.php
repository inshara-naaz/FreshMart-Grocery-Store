<?php
// ============================================
// index.php - FreshMart Grocery Shop Homepage
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// Page title
$pageTitle = 'FreshMart — Fresh Groceries Delivered';

// Cart count DB se
$cartCount = getCartCount($pdo);

// User name
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= HERO SECTION ================= -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content" data-aos="fade-right">
                <span class="hero-badge"><i class="fas fa-leaf"></i> 100% Organic & Fresh</span>
                <h1 class="hero-title">
                    Fresh Groceries <br>
                    Delivered <span class="highlight">To Your Door</span>
                </h1>
                <p class="hero-text">
                    Shop from a wide range of farm-fresh vegetables, fruits, dairy and daily essentials.
                    Quality you can trust, prices you'll love.
                </p>
                <div class="hero-buttons">
                    <a href="shop.php" class="btn-primary-custom"><i class="fas fa-shopping-basket"></i> Shop Now</a>
                    <a href="offers.php" class="btn-outline-custom"><i class="fas fa-tag"></i> View Offers</a>
                </div>
                <div class="hero-stats">
                    <div class="stat"><h3>5000+</h3><p>Happy Customers</p></div>
                    <div class="stat"><h3>1500+</h3><p>Products</p></div>
                    <div class="stat"><h3>24/7</h3><p>Support</p></div>
                </div>
            </div>
            <div class="col-lg-6 hero-image" data-aos="fade-left">
                <div class="hero-img-wrap">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80" alt="Fresh Groceries">
                    <div class="floating-card card-1">
                        <i class="fas fa-truck"></i>
                        <div><strong>Free Delivery</strong><small>Orders above Rs. 1500</small></div>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-shield-alt"></i>
                        <div><strong>100% Fresh</strong><small>Quality Guaranteed</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FEATURES STRIP ================= -->
<section class="features-strip">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-truck-fast"></i></div>
                    <div><h5>Free Delivery</h5><p>On orders above Rs. 1500</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-rotate-left"></i></div>
                    <div><h5>Easy Returns</h5><p>7 days return policy</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <div><h5>24/7 Support</h5><p>Always here to help</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <div><h5>Secure Payment</h5><p>100% safe checkout</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CATEGORIES ================= -->
<section class="categories-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Shop By Category</span>
            <h2>Browse Our Categories</h2>
            <p>Everything you need, organized just the way you like</p>
        </div>
        <div class="row g-4">
            <?php
            // Categories DB se
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC LIMIT 6");
            $categories = $stmt->fetchAll();
            foreach ($categories as $i => $cat):
            ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <a href="shop.php?cat=<?php echo $cat['slug']; ?>" class="category-card">
                        <div class="cat-img">
                            <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                            <div class="cat-overlay"></div>
                        </div>
                        <div class="cat-info">
                            <div class="cat-icon"><i class="fas <?php echo $cat['icon']; ?>"></i></div>
                            <h4><?php echo htmlspecialchars($cat['name']); ?></h4>
                            <span>Shop Now</span>
                            <div class="cat-link">Explore <i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= FEATURED PRODUCTS ================= -->
<section class="products-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Best Sellers</span>
            <h2>Featured Products</h2>
            <p>Hand-picked fresh products just for you</p>
        </div>
        <div class="row g-4">
            <?php
            // Featured products DB se — category name bhi sath laao
            $stmt = $pdo->query("
                SELECT p.*, c.name AS category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.badge IN ('Sale', 'Hot', 'New') 
                ORDER BY RAND() 
                LIMIT 8
            ");
            $products = $stmt->fetchAll();
            foreach ($products as $i => $p):
            ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($i % 4) * 100; ?>">
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
                            <span class="product-cat"><?php echo htmlspecialchars($p['category_name'] ?? 'Fresh'); ?></span>
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
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="shop.php" class="btn-primary-custom">View All Products <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ================= OFFER BANNER ================= -->
<section class="offer-banner">
    <div class="container">
        <div class="offer-inner" data-aos="zoom-in">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="offer-tag"><i class="fas fa-gift"></i> Special Offer</span>
                    <h2>Get <span>30% OFF</span> On Your First Order</h2>
                    <p>Use code <strong>FRESH30</strong> at checkout. Fresh groceries delivered to your door in 60 minutes.</p>
                    <a href="shop.php" class="btn-white-custom">Grab The Deal <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="https://images.unsplash.com/photo-1610348725531-843dff563e2c?w=500&q=80" alt="Offer">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE US ================= -->
<section class="why-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Why FreshMart</span>
            <h2>Why Choose Us?</h2>
            <p>We're committed to bringing you the best</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-seedling"></i></div>
                    <h4>100% Organic</h4>
                    <p>Directly sourced from trusted local farms</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-clock"></i></div>
                    <h4>60-Min Delivery</h4>
                    <p>Fastest delivery in your city, guaranteed</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <h4>Best Prices</h4>
                    <p>Unbeatable prices with daily discounts</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-award"></i></div>
                    <h4>Premium Quality</h4>
                    <p>Every product passes strict quality checks</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= NEWSLETTER ================= -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-box" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3><i class="fas fa-envelope-open-text"></i> Subscribe to Our Newsletter</h3>
                    <p>Get the latest offers and updates directly in your inbox</p>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Subscribed successfully!');">
                        <input type="email" placeholder="Enter your email address" required>
                        <button type="submit">Subscribe <i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>