<?php
// ============================================
// offers.php - Offers & Deals (DB Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ FETCH PRODUCTS BY BADGE ============
$stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.badge = 'Sale'");
$saleProducts = $stmt->fetchAll();

$stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.badge = 'Hot'");
$hotProducts = $stmt->fetchAll();

$stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.badge = 'New'");
$newProducts = $stmt->fetchAll();

// Deal of the day
$dealOfDay = $saleProducts[0] ?? ($hotProducts[0] ?? null);
if (!$dealOfDay) {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id LIMIT 1");
    $dealOfDay = $stmt->fetch();
}

// ============ FETCH COUPONS FROM DB ============
$stmt = $pdo->query("SELECT * FROM coupons WHERE is_active = 1 ORDER BY id ASC");
$coupons = $stmt->fetchAll();

// Coupon color/icon mapping (design ke liye)
$couponStyles = [
    'FRESH30' => ['color' => 'orange', 'icon' => 'fa-gift'],
    'SAVE20' => ['color' => 'green', 'icon' => 'fa-percent'],
    'FREESHIP' => ['color' => 'blue', 'icon' => 'fa-truck'],
    'MEGA50' => ['color' => 'red', 'icon' => 'fa-fire'],
];

// ============ FETCH CATEGORIES (for category offers) ============
$stmt = $pdo->query("SELECT * FROM categories LIMIT 4");
$catOffers = $stmt->fetchAll();

$catDiscounts = ['vegetables' => 30, 'fruits' => 25, 'dairy' => 20, 'bakery' => 15];
$catColors = ['vegetables' => 'green', 'fruits' => 'orange', 'dairy' => 'blue', 'bakery' => 'red'];

$pageTitle = 'Offers & Deals - FreshMart';
$cartCount = getCartCount($pdo);
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= OFFERS HERO ================= -->
<section class="offers-hero">
    <div class="container">
        <nav class="breadcrumb-nav breadcrumb-light">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">Offers & Deals</span>
        </nav>
        <div class="offers-hero-content" data-aos="fade-up">
            <span class="offers-hero-tag"><i class="fas fa-fire"></i> Mega Sale is Live</span>
            <h1>Up To <span class="highlight">50% OFF</span> <br>On Everything You Love</h1>
            <p>Fresh groceries, unbeatable prices — limited time only!</p>
            <div class="offers-hero-btns">
                <a href="#deals" class="btn-white-custom"><i class="fas fa-bolt"></i> Shop Deals</a>
                <a href="#coupons" class="btn-outline-white-custom"><i class="fas fa-ticket-alt"></i> Get Coupons</a>
            </div>
        </div>

        <div class="hero-countdown" data-aos="zoom-in">
            <p class="countdown-label"><i class="fas fa-hourglass-half"></i> Sale Ends In:</p>
            <div class="countdown-timer" id="heroCountdown">
                <div class="countdown-box"><span class="countdown-num" id="cd-days">02</span><span class="countdown-txt">Days</span></div>
                <div class="countdown-sep">:</div>
                <div class="countdown-box"><span class="countdown-num" id="cd-hours">18</span><span class="countdown-txt">Hours</span></div>
                <div class="countdown-sep">:</div>
                <div class="countdown-box"><span class="countdown-num" id="cd-mins">45</span><span class="countdown-txt">Mins</span></div>
                <div class="countdown-sep">:</div>
                <div class="countdown-box"><span class="countdown-num" id="cd-secs">30</span><span class="countdown-txt">Secs</span></div>
            </div>
        </div>
    </div>
</section>

<!-- ================= DEAL OF THE DAY ================= -->
<?php if ($dealOfDay): ?>
    <section class="deal-of-day-section" id="deals">
        <div class="container">
            <div class="deal-wrapper" data-aos="fade-up">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5">
                        <div class="deal-img">
                            <img src="<?php echo htmlspecialchars($dealOfDay['image']); ?>" alt="<?php echo htmlspecialchars($dealOfDay['name']); ?>">
                            <span class="deal-badge"><i class="fas fa-fire"></i> DEAL OF THE DAY</span>
                            <div class="deal-save">
                                <span>SAVE</span>
                                <strong><?php echo (!empty($dealOfDay['old_price']) && $dealOfDay['old_price'] > 0) ? round((($dealOfDay['old_price'] - $dealOfDay['price']) / $dealOfDay['old_price']) * 100) : 30; ?>%</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="deal-info">
                            <span class="deal-tag"><i class="fas fa-clock"></i> Limited Time Offer</span>
                            <h2><?php echo htmlspecialchars($dealOfDay['name']); ?></h2>
                            <div class="deal-rating">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i < $dealOfDay['rating'] ? 'filled' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span><?php echo $dealOfDay['rating']; ?>.0 (<?php echo rand(50, 200); ?> reviews)</span>
                            </div>
                            <p class="deal-desc"><?php echo htmlspecialchars($dealOfDay['description']); ?></p>

                            <div class="deal-price-row">
                                <div class="deal-price">
                                    <span class="deal-currency">Rs.</span>
                                    <span class="deal-new"><?php echo $dealOfDay['price']; ?></span>
                                    <?php if (!empty($dealOfDay['old_price']) && $dealOfDay['old_price'] > 0): ?>
                                        <span class="deal-old">Rs. <?php echo $dealOfDay['old_price']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="deal-per-unit">per <?php echo $dealOfDay['unit']; ?></div>
                            </div>

                            <div class="deal-stock">
                                <div class="stock-info">
                                    <span><i class="fas fa-box"></i> Only <strong><?php echo rand(5, 15); ?></strong> left in stock!</span>
                                    <span>Sold: <?php echo rand(60, 90); ?>%</span>
                                </div>
                                <div class="stock-bar">
                                    <div class="stock-fill" style="width: <?php echo rand(60, 90); ?>%"></div>
                                </div>
                            </div>

                            <div class="deal-actions">
                                <div class="deal-qty">
                                    <button type="button" class="deal-qty-btn">-</button>
                                    <input type="number" value="1" min="1" max="10">
                                    <button type="button" class="deal-qty-btn">+</button>
                                </div>
                                <button class="btn-add-deal-cart add-cart-btn" data-id="<?php echo $dealOfDay['id']; ?>">
                                    <i class="fas fa-shopping-bag"></i> Add to Cart
                                </button>
                                <a href="product.php?id=<?php echo $dealOfDay['id']; ?>" class="btn-deal-view">
                                    <i class="far fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ================= FLASH SALE ================= -->
<?php if (!empty($saleProducts)): ?>
    <section class="flash-sale-section">
        <div class="container">
            <div class="flash-header" data-aos="fade-up">
                <div class="flash-title-wrap">
                    <div class="flash-icon"><i class="fas fa-bolt"></i></div>
                    <div>
                        <h2>Flash Sale</h2>
                        <p>Hurry! Stock is limited</p>
                    </div>
                </div>
                <div class="flash-timer">
                    <i class="fas fa-clock"></i>
                    <span id="flashTimer">Ends in 04:23:15</span>
                </div>
            </div>

            <div class="row g-4">
                <?php
                $flashProducts = array_slice($saleProducts, 0, 4);
                foreach ($flashProducts as $i => $p):
                ?>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                        <div class="flash-card">
                            <div class="flash-img">
                                <a href="product.php?id=<?php echo $p['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                </a>
                                <span class="flash-discount">
                                    -<?php echo (!empty($p['old_price']) && $p['old_price'] > 0) ? round((($p['old_price'] - $p['price']) / $p['old_price']) * 100) : 20; ?>%
                                </span>
                                <div class="flash-stock-bar">
                                    <div class="flash-stock-fill" style="width: <?php echo rand(30, 80); ?>%"></div>
                                </div>
                            </div>
                            <div class="flash-info">
                                <span class="flash-cat"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                <h5><a href="product.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a></h5>
                                <div class="flash-rating">
                                    <?php for ($s = 0; $s < 5; $s++): ?>
                                        <i class="fas fa-star <?php echo $s < $p['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="flash-price-row">
                                    <div>
                                        <span class="flash-new">Rs. <?php echo $p['price']; ?></span>
                                        <?php if (!empty($p['old_price']) && $p['old_price'] > 0): ?>
                                            <span class="flash-old">Rs. <?php echo $p['old_price']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="flash-cart-btn add-cart-btn" data-id="<?php echo $p['id']; ?>">
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
<?php endif; ?>

<!-- ================= CATEGORY OFFERS ================= -->
<section class="category-offers-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Shop By Category</span>
            <h2>Category-Wise Offers</h2>
            <p>Biggest discounts on your favorite categories</p>
        </div>
        <div class="row g-4">
            <?php foreach ($catOffers as $i => $c):
                $discount = $catDiscounts[$c['slug']] ?? 20;
                $color = $catColors[$c['slug']] ?? 'green';
            ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                    <a href="shop.php?cat=<?php echo $c['slug']; ?>" class="cat-offer-card cat-offer-<?php echo $color; ?>">
                        <div class="cat-offer-img">
                            <img src="<?php echo htmlspecialchars($c['image']); ?>" alt="<?php echo htmlspecialchars($c['name']); ?>">
                            <div class="cat-offer-overlay"></div>
                        </div>
                        <div class="cat-offer-discount">
                            <span>UP TO</span>
                            <strong><?php echo $discount; ?>%</strong>
                            <span>OFF</span>
                        </div>
                        <div class="cat-offer-info">
                            <div class="cat-offer-icon"><i class="fas <?php echo $c['icon']; ?>"></i></div>
                            <h4><?php echo htmlspecialchars($c['name']); ?></h4>
                            <span class="cat-offer-link">Shop Now <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= COUPON CODES ================= -->
<?php if (!empty($coupons)): ?>
    <section class="coupons-section" id="coupons">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">Save More</span>
                <h2>Exclusive Coupon Codes</h2>
                <p>Copy these codes and save big on your order</p>
            </div>
            <div class="row g-4">
                <?php foreach ($coupons as $i => $c):
                    $style = $couponStyles[$c['code']] ?? ['color' => 'green', 'icon' => 'fa-ticket-alt'];
                    $discountText = $c['discount_type'] === 'percent' ? $c['discount_value'] . '% OFF' : 'Rs. ' . $c['discount_value'] . ' OFF';
                    $minText = $c['min_order'] > 0 ? 'Min. Rs. ' . $c['min_order'] : 'No minimum';
                    $expiryText = $c['expiry_date'] ? date('d M Y', strtotime($c['expiry_date'])) : 'Limited time';
                ?>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
                        <div class="coupon-card coupon-<?php echo $style['color']; ?>">
                            <div class="coupon-left">
                                <div class="coupon-icon"><i class="fas <?php echo $style['icon']; ?>"></i></div>
                                <span class="coupon-discount"><?php echo $discountText; ?></span>
                                <p class="coupon-desc"><?php echo $c['discount_type'] === 'percent' ? 'On your order' : 'Flat discount'; ?></p>
                                <span class="coupon-min"><?php echo $minText; ?></span>
                            </div>
                            <div class="coupon-divider"><span></span><span></span><span></span></div>
                            <div class="coupon-right">
                                <div class="coupon-code">
                                    <span class="coupon-code-label">CODE</span>
                                    <strong class="coupon-code-value" data-code="<?php echo $c['code']; ?>"><?php echo $c['code']; ?></strong>
                                </div>
                                <button class="coupon-copy-btn" onclick="copyCoupon(this, '<?php echo $c['code']; ?>')">
                                    <i class="fas fa-copy"></i> Copy Code
                                </button>
                                <div class="coupon-expiry">
                                    <i class="fas fa-calendar-alt"></i> Expires: <?php echo $expiryText; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ================= BUNDLE DEALS ================= -->
<section class="bundles-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Save Even More</span>
            <h2>Combo & Bundle Deals</h2>
            <p>Buy more, save more with our curated bundles</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="bundle-card bundle-large">
                    <div class="bundle-content">
                        <span class="bundle-tag"><i class="fas fa-star"></i> Best Seller</span>
                        <h3>Weekly Grocery Bundle</h3>
                        <p>All your essentials in one box — vegetables, fruits, dairy, and bakery items.</p>
                        <ul class="bundle-list">
                            <li><i class="fas fa-check"></i> 5kg Vegetables</li>
                            <li><i class="fas fa-check"></i> 3kg Fresh Fruits</li>
                            <li><i class="fas fa-check"></i> 2L Milk + Eggs</li>
                            <li><i class="fas fa-check"></i> Fresh Bread</li>
                        </ul>
                        <div class="bundle-price">
                            <span class="bundle-old">Rs. 3500</span>
                            <span class="bundle-new">Rs. 2500</span>
                            <span class="bundle-save">Save 28%</span>
                        </div>
                        <button class="btn-add-bundle">
                            <i class="fas fa-shopping-bag"></i> Add Bundle to Cart
                        </button>
                    </div>
                    <div class="bundle-img">
                        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&q=80" alt="Weekly Bundle">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-4">
                    <div class="col-12" data-aos="fade-left">
                        <div class="bundle-card bundle-small bundle-fruits">
                            <div class="bundle-small-content">
                                <span class="bundle-tag small-tag">Fresh Fruits Combo</span>
                                <h4>Fruit Lovers Pack</h4>
                                <p>Apple + Banana + Strawberry + Orange</p>
                                <div class="bundle-price-row">
                                    <div>
                                        <span class="bundle-old">Rs. 1400</span>
                                        <span class="bundle-new">Rs. 999</span>
                                    </div>
                                    <button class="btn-add-small"><i class="fas fa-shopping-bag"></i></button>
                                </div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=300&q=80" alt="Fruit Pack">
                        </div>
                    </div>
                    <div class="col-12" data-aos="fade-left" data-aos-delay="100">
                        <div class="bundle-card bundle-small bundle-veggies">
                            <div class="bundle-small-content">
                                <span class="bundle-tag small-tag">Veggie Combo</span>
                                <h4>Green Fresh Pack</h4>
                                <p>Tomatoes + Carrots + Lettuce + Broccoli</p>
                                <div class="bundle-price-row">
                                    <div>
                                        <span class="bundle-old">Rs. 800</span>
                                        <span class="bundle-new">Rs. 599</span>
                                    </div>
                                    <button class="btn-add-small"><i class="fas fa-shopping-bag"></i></button>
                                </div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300&q=80" alt="Veggie Pack">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= HOT DEALS GRID ================= -->
<section class="hot-deals-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Trending Now</span>
            <h2>Hot Deals</h2>
            <p>Most popular offers this week</p>
        </div>
        <div class="row g-4">
            <?php
            $hotDeals = array_slice(array_merge($hotProducts, $newProducts), 0, 4);
            foreach ($hotDeals as $i => $p):
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

<!-- ================= OFFERS NEWSLETTER ================= -->
<section class="offers-newsletter">
    <div class="container">
        <div class="offers-newsletter-inner" data-aos="fade-up">
            <div class="newsletter-icon-big"><i class="fas fa-bell"></i></div>
            <h2>Never Miss An Offer Again!</h2>
            <p>Subscribe to get exclusive deals, coupons, and sale alerts straight to your inbox.</p>
            <form class="offers-newsletter-form" onsubmit="event.preventDefault(); showToast('Subscribed successfully! 🎉'); this.reset();">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit"><i class="fas fa-paper-plane"></i> Subscribe</button>
            </form>
            <div class="newsletter-perks">
                <span><i class="fas fa-check-circle"></i> Exclusive deals</span>
                <span><i class="fas fa-check-circle"></i> Early access</span>
                <span><i class="fas fa-check-circle"></i> No spam</span>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>