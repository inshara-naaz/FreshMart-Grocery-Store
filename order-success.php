<?php
// ============================================
// order-success.php (Database Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ GET FORM DATA ============
$fullName = trim($_POST['full_name'] ?? $_SESSION['user_name'] ?? '');
$email = trim($_POST['email'] ?? $_SESSION['user_email'] ?? '');
$phone = trim($_POST['phone'] ?? $_SESSION['user_phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? 'cod';
$orderNotes = trim($_POST['notes'] ?? '');

// ============ VALIDATION ============
if (empty($fullName) || empty($email) || empty($phone) || empty($address) || empty($city)) {
    header('Location: checkout.php');
    exit;
}

// ============ GET CART ITEMS ============
$cartItems = getCartItems($pdo);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// ============ CALCULATE TOTALS ============
$subtotal = 0;
foreach ($cartItems as &$item) {
    $item['line_total'] = $item['unit_price'] * $item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);

$deliveryFee = $subtotal > 1500 ? 0 : 150;
$discount = 0;
$couponApplied = $_SESSION['coupon'] ?? '';

if ($couponApplied === 'FRESH30') {
    $discount = round($subtotal * 0.30);
    $deliveryFee = 0;
}

$grandTotal = $subtotal + $deliveryFee - $discount;

// ============ PAYMENT LABEL ============
$paymentLabels = [
    'cod' => 'Cash on Delivery',
    'card' => 'Debit / Credit Card',
    'bank' => 'Bank Transfer',
];
$paymentLabel = $paymentLabels[$paymentMethod] ?? 'Cash on Delivery';

// ============ GENERATE ORDER NUMBER ============
$orderNumber = 'FM-' . date('Ymd') . '-' . rand(1000, 9999);

// ============ USER ID ============
$userId = $_SESSION['user_id'] ?? null;

// ============ SAVE ORDER TO DATABASE ============
try {
    $pdo->beginTransaction();
    
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, customer_name, customer_email, customer_phone,
            customer_address, customer_city, customer_postal, delivery_notes,
            subtotal, delivery_fee, discount, coupon_code, total, payment_method, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $orderNumber,
        $userId,
        $fullName,
        $email,
        $phone,
        $address,
        $city,
        $postalCode,
        $orderNotes,
        $subtotal,
        $deliveryFee,
        $discount,
        $couponApplied ?: null,
        $grandTotal,
        $paymentMethod,
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_name, product_image,
            unit_label, unit_price, quantity, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cartItems as $item) {
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['name'],
            $item['image'],
            $item['unit_label'],
            $item['unit_price'],
            $item['quantity'],
            $item['line_total'],
        ]);
    }
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    die('Order failed: ' . $e->getMessage());
}

// ============ CLEAR CART ============
clearCart($pdo);
unset($_SESSION['coupon']);

// ============ FETCH SAVED ORDER ITEMS ============
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$savedItems = $stmt->fetchAll();

// ============ PAGE TITLE ============
$pageTitle = 'Order Confirmed - FreshMart';
$cartCount = 0;
$userName = $fullName;

include 'includes/header.php';
?>

<!-- ================= SUCCESS HERO ================= -->
<section class="success-hero">
    <div class="container">
        <div class="success-hero-content" data-aos="zoom-in">
            <div class="success-check">
                <i class="fas fa-check"></i>
                <div class="success-ring"></div>
                <div class="success-ring delay-1"></div>
                <div class="success-ring delay-2"></div>
            </div>
            <h1>Order Confirmed! 🎉</h1>
            <p>Thank you <strong><?php echo htmlspecialchars($fullName); ?></strong>! Your order has been placed successfully.</p>
            <div class="order-id-badge">
                <span>Order ID:</span>
                <strong><?php echo $orderNumber; ?></strong>
            </div>
        </div>
    </div>
</section>

<!-- ================= ORDER DETAILS ================= -->
<section class="success-details-section">
    <div class="container">
        <div class="row g-4">
            
            <!-- LEFT: Order Items -->
            <div class="col-lg-8">
                <div class="success-card" data-aos="fade-up">
                    <div class="success-card-header">
                        <i class="fas fa-box-open"></i>
                        <h3>Your Order</h3>
                    </div>
                    <div class="success-items">
                        <?php foreach ($savedItems as $item): ?>
                        <div class="success-item">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <div class="success-item-info">
                                <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                <span><?php echo htmlspecialchars($item['unit_label']); ?> × <?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="success-item-price">Rs. <?php echo number_format($item['line_total']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="success-totals">
                        <div class="suc-row"><span>Subtotal</span><strong>Rs. <?php echo number_format($subtotal); ?></strong></div>
                        <div class="suc-row"><span>Delivery</span><strong><?php echo $deliveryFee === 0 ? 'FREE' : 'Rs. ' . $deliveryFee; ?></strong></div>
                        <?php if ($discount > 0): ?>
                        <div class="suc-row discount"><span>Discount</span><strong>- Rs. <?php echo number_format($discount); ?></strong></div>
                        <?php endif; ?>
                        <div class="suc-divider"></div>
                        <div class="suc-row total"><span>Total Paid</span><strong>Rs. <?php echo number_format($grandTotal); ?></strong></div>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT: Delivery + Payment -->
            <div class="col-lg-4">
                
                <div class="success-card info-card" data-aos="fade-left">
                    <div class="success-card-header">
                        <i class="fas fa-truck"></i>
                        <h3>Delivery Details</h3>
                    </div>
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <div><strong><?php echo htmlspecialchars($fullName); ?></strong></div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div><?php echo htmlspecialchars($phone); ?></div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div><?php echo htmlspecialchars($email); ?></div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div><?php echo htmlspecialchars($address); ?><br><?php echo htmlspecialchars($city); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="success-card info-card" data-aos="fade-left" data-aos-delay="100">
                    <div class="success-card-header">
                        <i class="fas fa-credit-card"></i>
                        <h3>Payment</h3>
                    </div>
                    <div class="payment-badge">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $paymentLabel; ?></span>
                    </div>
                    <p class="payment-note">You will pay <strong>Rs. <?php echo number_format($grandTotal); ?></strong> upon delivery.</p>
                </div>
                
                <div class="success-card info-card estimate-card" data-aos="fade-left" data-aos-delay="200">
                    <div class="success-card-header">
                        <i class="fas fa-clock"></i>
                        <h3>Estimated Delivery</h3>
                    </div>
                    <div class="estimate-time">
                        <h2>60 Minutes</h2>
                        <p>Your order will arrive within 60 minutes</p>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Actions -->
        <div class="success-actions" data-aos="fade-up">
            <a href="track-order.php?order_id=<?php echo $orderNumber; ?>" class="btn-primary-custom">
                <i class="fas fa-truck"></i> Track This Order
            </a>
            <a href="shop.php" class="btn-outline-custom">
                <i class="fas fa-shopping-basket"></i> Continue Shopping
            </a>
            <a href="index.php" class="btn-outline-custom">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>