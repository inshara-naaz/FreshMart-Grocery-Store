<?php
// ============================================
// cart.php - Shopping Cart (Database Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ HANDLE ACTIONS ============
// Remove item
if (isset($_GET['remove'])) {
    $cartId = (int)$_GET['remove'];
    removeFromCart($pdo, $cartId);
    $_SESSION['flash_msg'] = 'Item removed from cart';
    header('Location: cart.php');
    exit;
}

// Clear cart
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    clearCart($pdo);
    $_SESSION['flash_msg'] = 'Cart cleared successfully';
    header('Location: cart.php');
    exit;
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $cartId = (int)$_POST['cart_id'];
    $qty = (int)$_POST['qty'];
    updateCartQuantity($pdo, $cartId, $qty);
    header('Location: cart.php');
    exit;
}

// ============ GET CART ITEMS ============
$cartItems = getCartItems($pdo);

// ============ BUILD ITEMS WITH PRODUCT DATA ============
$subtotal = 0;
foreach ($cartItems as &$item) {
    $item['line_total'] = $item['unit_price'] * $item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);

// ============ CART TOTALS ============
$deliveryFee = $subtotal > 1500 ? 0 : 150;
$discount = 0;
$couponApplied = '';

if (isset($_GET['coupon']) && strtoupper($_GET['coupon']) === 'FRESH30') {
    $discount = round($subtotal * 0.30);
    $couponApplied = 'FRESH30';
    $deliveryFee = 0;
    $_SESSION['coupon'] = 'FRESH30';
} elseif (isset($_GET['coupon']) && empty($_GET['coupon'])) {
    unset($_SESSION['coupon']);
} elseif (isset($_SESSION['coupon']) && $_SESSION['coupon'] === 'FRESH30') {
    $discount = round($subtotal * 0.30);
    $couponApplied = 'FRESH30';
    $deliveryFee = 0;
}

$grandTotal = $subtotal + $deliveryFee - $discount;

// ============ CART COUNT ============
$cartCount = getCartCount($pdo);

// ============ PAGE TITLE ============
$pageTitle = 'Shopping Cart - FreshMart';
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
            <i class="fas fa-angle-right separator"></i>
            <span class="current">Shopping Cart</span>
        </nav>
        <div class="shop-header-content">
            <h1><i class="fas fa-shopping-bag"></i> Shopping Cart</h1>
            <p>You have <strong><?php echo $cartCount; ?></strong> item<?php echo $cartCount != 1 ? 's' : ''; ?> in your cart</p>
        </div>
    </div>
</section>

<!-- ================= CART SECTION ================= -->
<section class="cart-section">
    <div class="container">
        <?php if (count($cartItems) === 0): ?>
        <!-- ========== EMPTY CART ========== -->
        <div class="empty-cart" data-aos="fade-up">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="shop.php" class="btn-primary-custom">
                <i class="fas fa-shopping-basket"></i> Start Shopping
            </a>
        </div>
        <?php else: ?>
        <!-- ========== CART WITH ITEMS ========== -->
        <div class="row g-4">
            
            <!-- LEFT: Cart Items -->
            <div class="col-lg-8">
                <div class="cart-items-wrap" data-aos="fade-up">
                    <div class="cart-header-row">
                        <span>Product</span>
                        <span>Price</span>
                        <span>Quantity</span>
                        <span>Subtotal</span>
                        <span></span>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <div class="cart-product">
                            <a href="product.php?id=<?php echo $item['product_id']; ?>" class="cart-product-img">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </a>
                            <div class="cart-product-info">
                                <span class="cart-product-cat">Fresh</span>
                                <h4><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h4>
                                <span class="cart-product-unit">Unit: <?php echo htmlspecialchars($item['unit_label']); ?></span>
                            </div>
                        </div>
                        
                        <div class="cart-price" data-label="Price">
                            <span class="new-price">Rs. <?php echo number_format($item['unit_price']); ?></span>
                        </div>
                        
                        <div class="cart-qty" data-label="Quantity">
                            <form method="POST" action="cart.php" class="qty-form">
                                <input type="hidden" name="update_qty" value="1">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <div class="qty-control">
                                    <button type="button" class="qty-minus"><i class="fas fa-minus"></i></button>
                                    <input type="number" name="qty" class="qty-value" value="<?php echo $item['quantity']; ?>" min="1" max="20" data-price="<?php echo $item['unit_price']; ?>">
                                    <button type="button" class="qty-plus"><i class="fas fa-plus"></i></button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="cart-subtotal" data-label="Subtotal">
                            <span class="subtotal-value">Rs. <?php echo number_format($item['line_total']); ?></span>
                        </div>
                        
                        <div class="cart-remove">
                            <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn" title="Remove" onclick="return confirm('Remove this item from cart?');">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Cart Actions -->
                    <div class="cart-actions">
                        <a href="shop.php" class="btn-continue-shopping">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <a href="cart.php?clear=1" class="btn-clear-cart" onclick="return confirm('Are you sure you want to clear your entire cart?');">
                            <i class="fas fa-trash-alt"></i> Clear Cart
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT: Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-summary-wrap" data-aos="fade-left">
                    
                    <!-- Coupon -->
                    <div class="coupon-box">
                        <h4><i class="fas fa-ticket-alt"></i> Have a Coupon?</h4>
                        <form class="coupon-form" method="GET" action="cart.php">
                            <input type="text" name="coupon" placeholder="Enter coupon code" value="<?php echo $couponApplied; ?>">
                            <button type="submit"><i class="fas fa-check"></i></button>
                        </form>
                        <p class="coupon-hint">Try: <strong>FRESH30</strong> for 30% off + Free Delivery</p>
                    </div>
                    
                    <!-- Summary -->
                    <div class="summary-box">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                            <strong>Rs. <?php echo number_format($subtotal); ?></strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <?php if ($deliveryFee === 0): ?>
                                <strong class="free-ship">FREE <i class="fas fa-truck"></i></strong>
                            <?php else: ?>
                                <strong>Rs. <?php echo $deliveryFee; ?></strong>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div class="summary-row discount-row">
                            <span>Discount (<?php echo $couponApplied; ?>)</span>
                            <strong>- Rs. <?php echo number_format($discount); ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <strong>Rs. <?php echo number_format($grandTotal); ?></strong>
                        </div>
                        
                        <?php if ($deliveryFee > 0): ?>
                        <div class="free-ship-hint">
                            <i class="fas fa-info-circle"></i>
                            Add <strong>Rs. <?php echo number_format(1500 - $subtotal); ?></strong> more for FREE delivery
                        </div>
                        <?php endif; ?>
                        
                        <a href="checkout.php" class="btn-checkout">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        
                        <div class="secure-payment">
                            <i class="fas fa-shield-alt"></i>
                            <span>100% Secure Payment</span>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="payment-methods">
                        <p>We Accept:</p>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-paypal"></i>
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================= RECOMMENDED PRODUCTS ================= -->
<?php if (count($cartItems) > 0): ?>
<section class="related-products-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">You May Also Like</span>
            <h2>Recommended For You</h2>
            <p>Based on your cart, you might like these</p>
        </div>
        <div class="row g-4">
            <?php
            // Featured products DB se
            $stmt = $pdo->query("SELECT * FROM products WHERE badge IN ('Sale', 'Hot', 'New') ORDER BY RAND() LIMIT 4");
            $recommended = $stmt->fetchAll();
            foreach($recommended as $i=>$p):
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i*100; ?>">
                <div class="product-card">
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
                        <span class="product-cat">Fresh</span>
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
<?php endif; ?>

<?php include 'includes/footer.php'; ?>