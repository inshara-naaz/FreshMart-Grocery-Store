<?php
// ============================================
// track-order.php - Order Tracking Page
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/products-data.php';

include 'includes/db.php';

// ============ GET ORDER ID FROM URL ============
$searchOrderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$foundOrder = null;
$searchAttempted = false;

if (!empty($searchOrderId)) {
    $searchAttempted = true;
    
    // DB se order dhundo
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([strtoupper($searchOrderId)]);
    $order = $stmt->fetch();
    
    if ($order) {
        // Order items lo
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();
        
        // Format data
        $foundOrder = [
            'id' => $order['order_number'],
            'date' => $order['created_at'],
            'status' => $order['status'],
            'subtotal' => $order['subtotal'],
            'delivery_fee' => $order['delivery_fee'],
            'discount' => $order['discount'],
            'total' => $order['total'],
            'payment_method' => $order['payment_method'],
            'customer' => [
                'name' => $order['customer_name'],
                'email' => $order['customer_email'],
                'phone' => $order['customer_phone'],
                'address' => $order['customer_address'],
                'city' => $order['customer_city'],
            ],
            'items' => [],
        ];
        
        foreach ($items as $item) {
            $foundOrder['items'][] = [
                'product' => [
                    'name' => $item['product_name'],
                    'img' => $item['product_image'],
                ],
                'qty' => $item['quantity'],
                'unit_label' => $item['unit_label'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
            ];
        }
    }
}

// ============ PAGE TITLE ============
$pageTitle = 'Track Order - FreshMart';
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= TRACK HERO ================= -->
<section class="track-hero">
    <div class="container">
        <nav class="breadcrumb-nav breadcrumb-light">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">Track Order</span>
        </nav>
        <div class="track-hero-content" data-aos="fade-up">
            <span class="track-hero-tag"><i class="fas fa-truck"></i> Order Tracking</span>
            <h1>Track Your <span class="highlight">Order</span></h1>
            <p>Enter your Order ID to see the latest status of your delivery</p>
        </div>
        
        <!-- Search Box -->
        <div class="track-search-box" data-aos="zoom-in">
            <form method="GET" action="track-order.php" class="track-form">
                <div class="track-input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="order_id" placeholder="Enter your Order ID (e.g., FM-20260921-3622)" value="<?php echo htmlspecialchars($searchOrderId); ?>" required>
                </div>
                <button type="submit" class="track-btn">
                    <i class="fas fa-search"></i> Track Order
                </button>
            </form>
            <p class="track-hint">
                <i class="fas fa-info-circle"></i>
                Your Order ID was provided after you placed your order
            </p>
        </div>
    </div>
</section>

<!-- ================= TRACK RESULT ================= -->
<section class="track-result-section">
    <div class="container">
        
        <?php if ($searchAttempted && $foundOrder): 
            $order = $foundOrder;
            
            $statusSteps = ['pending' => 1, 'confirmed' => 2, 'delivered' => 3];
            $currentStep = $statusSteps[$order['status']] ?? 1;
            
            $statusLabels = [
                'pending' => ['label' => 'Order Placed', 'icon' => 'fa-clock'],
                'confirmed' => ['label' => 'Order Confirmed', 'icon' => 'fa-box'],
                'delivered' => ['label' => 'Delivered', 'icon' => 'fa-home'],
            ];
            $stLabel = $statusLabels[$order['status']] ?? $statusLabels['pending'];
        ?>
        
        <!-- Order Found -->
        <div class="track-found" data-aos="fade-up">
            <div class="track-found-header">
                <div class="track-found-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3>Order Found!</h3>
                    <p>Order ID: <strong><?php echo $order['id']; ?></strong></p>
                </div>
                <div class="track-found-status status-<?php echo $order['status']; ?>">
                    <i class="fas <?php echo $stLabel['icon']; ?>"></i>
                    <?php echo $stLabel['label']; ?>
                </div>
            </div>
            
            <!-- Progress Tracker -->
            <div class="tracker-wrapper">
                <h4><i class="fas fa-route"></i> Delivery Progress</h4>
                <div class="tracker-progress">
                    <div class="tracker-step <?php echo $currentStep >= 1 ? 'completed' : ''; ?>">
                        <div class="tracker-dot"><i class="fas fa-check"></i></div>
                        <div class="tracker-info">
                            <strong>Order Placed</strong>
                            <span><?php echo date('d M Y, h:i A', strtotime($order['date'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="tracker-line <?php echo $currentStep >= 2 ? 'active' : ''; ?>"></div>
                    
                    <div class="tracker-step <?php echo $currentStep >= 2 ? 'completed' : ''; ?>">
                        <div class="tracker-dot"><i class="fas fa-box"></i></div>
                        <div class="tracker-info">
                            <strong>Order Confirmed</strong>
                            <span><?php echo $currentStep >= 2 ? 'Confirmed' : 'Processing...'; ?></span>
                        </div>
                    </div>
                    
                    <div class="tracker-line <?php echo $currentStep >= 3 ? 'active' : ''; ?>"></div>
                    
                    <div class="tracker-step <?php echo $currentStep >= 3 ? 'completed' : ''; ?>">
                        <div class="tracker-dot"><i class="fas fa-truck"></i></div>
                        <div class="tracker-info">
                            <strong>Out for Delivery</strong>
                            <span><?php echo $currentStep >= 3 ? 'On the way' : 'Waiting...'; ?></span>
                        </div>
                    </div>
                    
                    <div class="tracker-line <?php echo $currentStep >= 3 ? 'active' : ''; ?>"></div>
                    
                    <div class="tracker-step <?php echo $currentStep >= 3 ? 'completed' : ''; ?>">
                        <div class="tracker-dot"><i class="fas fa-home"></i></div>
                        <div class="tracker-info">
                            <strong>Delivered</strong>
                            <span><?php echo $currentStep >= 3 ? 'Completed' : 'Expected soon'; ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                <div class="tracker-estimate">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Estimated Delivery</strong>
                        <span>Within 60 minutes from confirmation</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Order Details -->
            <div class="track-details-grid">
                <div class="track-detail-card">
                    <h4><i class="fas fa-shopping-bag"></i> Items (<?php echo count($order['items']); ?>)</h4>
                    <div class="track-items">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="track-item">
                            <img src="<?php echo $item['product']['img']; ?>" alt="<?php echo $item['product']['name']; ?>">
                            <div>
                                <strong><?php echo $item['product']['name']; ?></strong>
                                <span><?php echo $item['unit_label']; ?> × <?php echo $item['qty']; ?></span>
                            </div>
                            <div class="track-item-price">Rs. <?php echo number_format($item['line_total']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="track-detail-card">
                    <h4><i class="fas fa-map-marker-alt"></i> Delivery Address</h4>
                    <div class="track-info">
                        <p><strong><?php echo htmlspecialchars($order['customer']['name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($order['customer']['address']); ?></p>
                        <p><?php echo htmlspecialchars($order['customer']['city']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['customer']['phone']); ?></p>
                    </div>
                    
                    <h4 style="margin-top:20px;"><i class="fas fa-credit-card"></i> Payment</h4>
                    <div class="track-info">
                        <p><strong>Method:</strong> <?php echo $order['payment_method']; ?></p>
                        <p><strong>Total:</strong> Rs. <?php echo number_format($order['total']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="track-actions">
                <a href="my-orders.php" class="btn-primary-custom">
                    <i class="fas fa-box"></i> View All Orders
                </a>
                <a href="contact.php" class="btn-outline-custom">
                    <i class="fas fa-headset"></i> Need Help?
                </a>
            </div>
        </div>
        
        <?php elseif ($searchAttempted && !$foundOrder): ?>
        
        <!-- Order Not Found -->
        <div class="track-not-found" data-aos="fade-up">
            <div class="not-found-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>Order Not Found</h3>
            <p>We couldn't find any order with ID: <strong><?php echo htmlspecialchars($searchOrderId); ?></strong></p>
            <p class="not-found-hint">
                <i class="fas fa-info-circle"></i>
                Please check your Order ID and try again. It usually looks like: <strong>FM-20260921-3622</strong>
            </p>
            <div class="not-found-actions">
                <a href="track-order.php" class="btn-primary-custom">
                    <i class="fas fa-redo"></i> Try Again
                </a>
                <a href="contact.php" class="btn-outline-custom">
                    <i class="fas fa-headset"></i> Contact Support
                </a>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- Info Cards -->
        <div class="track-info-cards" data-aos="fade-up">
            <div class="info-card-step">
                <div class="ics-icon"><i class="fas fa-receipt"></i></div>
                <h4>1. Get Your Order ID</h4>
                <p>"Your Order ID is provided after placing an order"</p>
            </div>
            <div class="info-card-step">
                <div class="ics-icon"><i class="fas fa-keyboard"></i></div>
                <h4>2. Enter Order ID</h4>
                <p>"Enter your Order ID and click the "Track Order" button to see your order status"</p>
            </div>
            <div class="info-card-step">
                <div class="ics-icon"><i class="fas fa-truck-fast"></i></div>
                <h4>3. See Live Status</h4>
                <p>"View the current status, delivery progress, and estimated time of your order"</p>
            </div>
        </div>
        
        <?php endif; ?>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>