<?php
// ============================================
// checkout.php - Checkout (Database Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ CHECK CART ============
$cartItems = getCartItems($pdo);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// ============ BUILD ITEMS ============
$subtotal = 0;
foreach ($cartItems as &$item) {
    $item['line_total'] = $item['unit_price'] * $item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);

// ============ TOTALS ============
$deliveryFee = $subtotal > 1500 ? 0 : 150;
$discount = 0;
$couponApplied = $_SESSION['coupon'] ?? '';

if ($couponApplied === 'FRESH30') {
    $discount = round($subtotal * 0.30);
    $deliveryFee = 0;
}

$grandTotal = $subtotal + $deliveryFee - $discount;

// ============ CART COUNT ============
$cartCount = getCartCount($pdo);

// ============ PAGE TITLE ============
$pageTitle = 'Checkout - FreshMart';
$userName = $_SESSION['user_name'] ?? null;

// Pre-fill from user session
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';

include 'includes/header.php';
?>

<!-- ================= CHECKOUT HEADER ================= -->
<section class="checkout-header-section">
    <div class="container">
        <nav class="breadcrumb-nav">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <a href="cart.php">Cart</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">Checkout</span>
        </nav>
        
        <!-- Progress Bar -->
        <div class="checkout-progress" data-aos="fade-up">
            <div class="progress-step completed">
                <div class="step-icon"><i class="fas fa-check"></i></div>
                <span class="step-label">Cart</span>
            </div>
            <div class="progress-line active"></div>
            <div class="progress-step active">
                <div class="step-icon">2</div>
                <span class="step-label">Checkout</span>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <div class="step-icon">3</div>
                <span class="step-label">Payment</span>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <div class="step-icon">4</div>
                <span class="step-label">Done</span>
            </div>
        </div>
    </div>
</section>

<!-- ================= CHECKOUT MAIN ================= -->
<section class="checkout-section">
    <div class="container">
        <form method="POST" action="order-success.php" id="checkoutForm">
            <div class="row g-4">
                
                <!-- LEFT: Forms -->
                <div class="col-lg-7">
                    
                    <!-- Billing Info -->
                    <div class="checkout-card" data-aos="fade-up">
                        <div class="checkout-card-header">
                            <div class="cc-icon"><i class="fas fa-user"></i></div>
                            <div>
                                <h3>Billing Information</h3>
                                <p>Enter your details for delivery</p>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name <span class="req">*</span></label>
                                    <input type="text" name="full_name" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($userName ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address <span class="req">*</span></label>
                                    <input type="email" name="email" placeholder="example@email.com" required value="<?php echo htmlspecialchars($userEmail); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number <span class="req">*</span></label>
                                    <input type="tel" name="phone" placeholder="+92 300 1234567" required value="<?php echo htmlspecialchars($userPhone); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>City <span class="req">*</span></label>
                                    <select name="city" required>
                                        <option value="">Select city</option>
                                        <option>Lahore</option>
                                        <option>Karachi</option>
                                        <option>Islamabad</option>
                                        <option>Rawalpindi</option>
                                        <option>Faisalabad</option>
                                        <option>Multan</option>
                                        <option>Peshawar</option>
                                        <option>Quetta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Street Address <span class="req">*</span></label>
                                    <input type="text" name="address" placeholder="House #, Street, Area" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Postal Code</label>
                                    <input type="text" name="postal_code" placeholder="54000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Delivery Notes (Optional)</label>
                                    <input type="text" name="notes" placeholder="Any special instructions">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-card" data-aos="fade-up">
                        <div class="checkout-card-header">
                            <div class="cc-icon cc-green"><i class="fas fa-credit-card"></i></div>
                            <div>
                                <h3>Payment Method</h3>
                                <p>Choose how you want to pay</p>
                            </div>
                        </div>
                        
                        <div class="payment-options">
                            <label class="payment-option active">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="po-content">
                                    <div class="po-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="po-info">
                                        <strong>Cash on Delivery</strong>
                                        <span>Pay when your order arrives</span>
                                    </div>
                                    <div class="po-check"><i class="fas fa-check"></i></div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="card">
                                <div class="po-content">
                                    <div class="po-icon po-blue"><i class="fas fa-credit-card"></i></div>
                                    <div class="po-info">
                                        <strong>Debit / Credit Card</strong>
                                        <span>Visa, Mastercard, and more</span>
                                    </div>
                                    <div class="po-check"><i class="fas fa-check"></i></div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="bank">
                                <div class="po-content">
                                    <div class="po-icon po-purple"><i class="fas fa-university"></i></div>
                                    <div class="po-info">
                                        <strong>Bank Transfer</strong>
                                        <span>Direct bank transfer</span>
                                    </div>
                                    <div class="po-check"><i class="fas fa-check"></i></div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="card-details" id="cardDetails" style="display:none;">
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Card Number</label>
                                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Expiry Date</label>
                                        <input type="text" placeholder="MM/YY" maxlength="5">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>CVV</label>
                                        <input type="text" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- RIGHT: Order Summary -->
                <div class="col-lg-5">
                    <div class="order-summary-wrap" data-aos="fade-left">
                        <div class="order-summary-box">
                            <div class="os-header">
                                <h3><i class="fas fa-shopping-bag"></i> Order Summary</h3>
                                <a href="cart.php" class="edit-cart"><i class="fas fa-edit"></i> Edit</a>
                            </div>
                            
                            <!-- Cart Items -->
                            <div class="os-items">
                                <?php foreach ($cartItems as $item): ?>
                                <div class="os-item">
                                    <div class="os-item-img">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <span class="os-item-qty"><?php echo $item['quantity']; ?>x</span>
                                    </div>
                                    <div class="os-item-info">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <span><?php echo htmlspecialchars($item['unit_label']); ?></span>
                                    </div>
                                    <div class="os-item-price">Rs. <?php echo number_format($item['line_total']); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Totals -->
                            <div class="os-totals">
                                <div class="os-row">
                                    <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                                    <strong>Rs. <?php echo number_format($subtotal); ?></strong>
                                </div>
                                <div class="os-row">
                                    <span>Delivery Fee</span>
                                    <?php if ($deliveryFee === 0): ?>
                                        <strong class="os-free">FREE <i class="fas fa-truck"></i></strong>
                                    <?php else: ?>
                                        <strong>Rs. <?php echo $deliveryFee; ?></strong>
                                    <?php endif; ?>
                                </div>
                                <?php if ($discount > 0): ?>
                                <div class="os-row os-discount">
                                    <span>Discount (<?php echo $couponApplied; ?>)</span>
                                    <strong>- Rs. <?php echo number_format($discount); ?></strong>
                                </div>
                                <?php endif; ?>
                                
                                <div class="os-divider"></div>
                                
                                <div class="os-row os-total">
                                    <span>Total</span>
                                    <strong>Rs. <?php echo number_format($grandTotal); ?></strong>
                                </div>
                            </div>
                            
                            <?php if ($couponApplied): ?>
                            <div class="os-coupon-applied">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Coupon <strong><?php echo $couponApplied; ?></strong> applied</span>
                            </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn-place-order">
                                <i class="fas fa-lock"></i> Place Order — Rs. <?php echo number_format($grandTotal); ?>
                            </button>
                            
                            <div class="os-trust">
                                <div class="trust-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Secure</span>
                                </div>
                                <div class="trust-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Fast Delivery</span>
                                </div>
                                <div class="trust-item">
                                    <i class="fas fa-rotate-left"></i>
                                    <span>Easy Returns</span>
                                </div>
                            </div>
                            
                            <div class="os-payments">
                                <p>Secure Payment:</p>
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
                
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>