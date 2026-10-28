<?php
// ============================================
// my-orders.php - My Orders (Database Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

// ============ USER LOGIN CHECK ============
// Agar user logged in nahi, to login page pe bhejo
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-orders.php');
    exit;
}

$userId = $_SESSION['user_id'];

// ============ FILTER ============
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$allowedStatuses = ['all', 'pending', 'confirmed', 'delivered', 'cancelled'];
if (!in_array($statusFilter, $allowedStatuses)) {
    $statusFilter = 'all';
}

// ============ FETCH ORDERS ============
if ($statusFilter === 'all') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY created_at DESC");
    $stmt->execute([$userId, $statusFilter]);
}
$orders = $stmt->fetchAll();

// ============ FETCH ORDER ITEMS FOR EACH ORDER ============
foreach ($orders as &$order) {
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll();
}
unset($order);

// ============ STATS ============
$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$totalOrders = $stmt->fetch()['cnt'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$pendingOrders = $stmt->fetch()['cnt'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND status = 'delivered'");
$stmt->execute([$userId]);
$deliveredOrders = $stmt->fetch()['cnt'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND status = 'cancelled'");
$stmt->execute([$userId]);
$cancelledOrders = $stmt->fetch()['cnt'];

$stats = [
    'total' => $totalOrders,
    'pending' => $pendingOrders,
    'delivered' => $deliveredOrders,
    'cancelled' => $cancelledOrders,
];

// ============ PAGE TITLE ============
$pageTitle = 'My Orders - FreshMart';
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<section class="shop-page-header">
    <div class="container">
        <nav class="breadcrumb-nav">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">My Orders</span>
        </nav>
        <div class="shop-header-content">
            <h1><i class="fas fa-box"></i> My Orders</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($userName); ?></strong>! Track and manage all your orders.</p>
        </div>
    </div>
</section>

<!-- ================= ORDERS SECTION ================= -->
<section class="myorders-section">
    <div class="container">
        
        <?php if (empty($stats['total'])): ?>
        <!-- ========== EMPTY STATE ========== -->
        <div class="empty-orders" data-aos="fade-up">
            <div class="empty-orders-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <h2>No Orders Yet</h2>
            <p>You haven't placed any orders. Start shopping now and your orders will appear here.</p>
            <a href="shop.php" class="btn-primary-custom">
                <i class="fas fa-shopping-basket"></i> Start Shopping
            </a>
        </div>
        <?php else: ?>
        
        <!-- ========== STATS ========== -->
        <div class="orders-stats" data-aos="fade-up">
            <div class="stat-card">
                <div class="stat-icon si-total"><i class="fas fa-shopping-bag"></i></div>
                <div>
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon si-pending"><i class="fas fa-clock"></i></div>
                <div>
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon si-delivered"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3><?php echo $stats['delivered']; ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon si-cancelled"><i class="fas fa-times-circle"></i></div>
                <div>
                    <h3><?php echo $stats['cancelled']; ?></h3>
                    <p>Cancelled</p>
                </div>
            </div>
        </div>
        
        <!-- ========== FILTER TABS ========== -->
        <div class="orders-filters" data-aos="fade-up">
            <a href="my-orders.php" class="filter-tab <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
                All Orders <span class="tab-count"><?php echo $stats['total']; ?></span>
            </a>
            <a href="my-orders.php?status=pending" class="filter-tab <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                Pending <span class="tab-count"><?php echo $stats['pending']; ?></span>
            </a>
            <a href="my-orders.php?status=delivered" class="filter-tab <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">
                Delivered <span class="tab-count"><?php echo $stats['delivered']; ?></span>
            </a>
            <a href="my-orders.php?status=cancelled" class="filter-tab <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
                Cancelled <span class="tab-count"><?php echo $stats['cancelled']; ?></span>
            </a>
        </div>
        
        <!-- ========== ORDERS LIST ========== -->
        <?php if (empty($orders)): ?>
        <div class="no-filter-orders" data-aos="fade-up">
            <i class="fas fa-search"></i>
            <h3>No <?php echo ucfirst($statusFilter); ?> Orders</h3>
            <p>You don't have any orders with this status.</p>
            <a href="my-orders.php" class="btn-outline-custom">
                <i class="fas fa-rotate-left"></i> View All Orders
            </a>
        </div>
        <?php else: ?>
        
        <div class="orders-list">
            <?php foreach ($orders as $i => $order): 
                $statusInfo = [
                    'pending' => ['label' => 'Pending', 'icon' => 'fa-clock', 'class' => 'status-pending'],
                    'confirmed' => ['label' => 'Confirmed', 'icon' => 'fa-check', 'class' => 'status-confirmed'],
                    'delivered' => ['label' => 'Delivered', 'icon' => 'fa-check-circle', 'class' => 'status-delivered'],
                    'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-times-circle', 'class' => 'status-cancelled'],
                ];
                $st = $statusInfo[$order['status']] ?? $statusInfo['pending'];
                
                // Total qty
                $totalQty = 0;
                foreach ($order['items'] as $it) $totalQty += $it['quantity'];
            ?>
            <div class="order-card" data-aos="fade-up" data-aos-delay="<?php echo min($i, 3) * 100; ?>">
                
                <!-- Order Header -->
                <div class="order-card-header">
                    <div class="order-head-left">
                        <div class="order-id-wrap">
                            <span class="order-label">Order ID</span>
                            <strong class="order-id"><?php echo $order['order_number']; ?></strong>
                        </div>
                        <div class="order-date-wrap">
                            <span class="order-label">Placed On</span>
                            <strong><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></strong>
                        </div>
                        <div class="order-total-wrap">
                            <span class="order-label">Total</span>
                            <strong class="order-total-price">Rs. <?php echo number_format($order['total']); ?></strong>
                        </div>
                    </div>
                    <div class="order-head-right">
                        <span class="order-status <?php echo $st['class']; ?>">
                            <i class="fas <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                        </span>
                        <button class="order-toggle" data-target="order-details-<?php echo $i; ?>">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Order Summary Row -->
                <div class="order-summary-row">
                    <div class="order-items-preview">
                        <?php 
                        $previewItems = array_slice($order['items'], 0, 4);
                        foreach ($previewItems as $item): 
                        ?>
                        <div class="preview-item">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($order['items']) > 4): ?>
                        <div class="preview-item preview-more">
                            +<?php echo count($order['items']) - 4; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="order-items-count">
                        <i class="fas fa-box"></i> <?php echo $totalQty; ?> item<?php echo $totalQty != 1 ? 's' : ''; ?>
                    </div>
                </div>
                
                <!-- Order Details (expandable) -->
                <div class="order-details" id="order-details-<?php echo $i; ?>" style="display:none;">
                    
                    <!-- Items -->
                    <div class="od-section">
                        <h4><i class="fas fa-shopping-bag"></i> Order Items</h4>
                        <div class="od-items">
                            <?php foreach ($order['items'] as $item): ?>
                            <div class="od-item">
                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <div class="od-item-info">
                                    <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                    <span><?php echo htmlspecialchars($item['unit_label']); ?> × <?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="od-item-price">Rs. <?php echo number_format($item['line_total']); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Info Grid -->
                    <div class="od-grid">
                        <div class="od-section">
                            <h4><i class="fas fa-map-marker-alt"></i> Delivery Address</h4>
                            <div class="od-info">
                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                <p><?php echo htmlspecialchars($order['customer_address']); ?></p>
                                <p><?php echo htmlspecialchars($order['customer_city']); ?></p>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            </div>
                        </div>
                        
                        <div class="od-section">
                            <h4><i class="fas fa-credit-card"></i> Payment</h4>
                            <div class="od-info">
                                <p><strong>Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                <p><strong>Subtotal:</strong> Rs. <?php echo number_format($order['subtotal']); ?></p>
                                <p><strong>Delivery:</strong> <?php echo $order['delivery_fee'] == 0 ? 'FREE' : 'Rs. ' . number_format($order['delivery_fee']); ?></p>
                                <?php if ($order['discount'] > 0): ?>
                                <p class="od-discount"><strong>Discount:</strong> - Rs. <?php echo number_format($order['discount']); ?> (<?php echo $order['coupon_code']; ?>)</p>
                                <?php endif; ?>
                                <p class="od-total"><strong>Total:</strong> Rs. <?php echo number_format($order['total']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mini Progress -->
                    <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                    <div class="od-section od-progress-section">
                        <h4><i class="fas fa-truck"></i> Order Status</h4>
                        <div class="mini-progress">
                            <div class="mini-step completed">
                                <div class="ms-dot"><i class="fas fa-check"></i></div>
                                <span>Placed</span>
                            </div>
                            <div class="mini-line <?php echo in_array($order['status'], ['confirmed', 'delivered']) ? 'active' : ''; ?>"></div>
                            <div class="mini-step <?php echo in_array($order['status'], ['confirmed', 'delivered']) ? 'completed' : ''; ?>">
                                <div class="ms-dot"><i class="fas fa-box"></i></div>
                                <span>Confirmed</span>
                            </div>
                            <div class="mini-line <?php echo $order['status'] === 'delivered' ? 'active' : ''; ?>"></div>
                            <div class="mini-step <?php echo $order['status'] === 'delivered' ? 'completed' : ''; ?>">
                                <div class="ms-dot"><i class="fas fa-truck"></i></div>
                                <span>Out for Delivery</span>
                            </div>
                            <div class="mini-line <?php echo $order['status'] === 'delivered' ? 'active' : ''; ?>"></div>
                            <div class="mini-step <?php echo $order['status'] === 'delivered' ? 'completed' : ''; ?>">
                                <div class="ms-dot"><i class="fas fa-home"></i></div>
                                <span>Delivered</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="od-actions">
                        <a href="track-order.php?order_id=<?php echo $order['order_number']; ?>" class="btn-od-action btn-track">
                            <i class="fas fa-truck"></i> Track Order
                        </a>
                        <button class="btn-od-action btn-invoice" onclick="alert('Invoice download will be available soon!')">
                            <i class="fas fa-file-invoice"></i> Download Invoice
                        </button>
                        <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn-od-action btn-cancel" onclick="if(confirm('Cancel this order?')) { alert('Order cancellation requested!'); }">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                        <?php endif; ?>
                        <a href="contact.php" class="btn-od-action btn-help">
                            <i class="fas fa-headset"></i> Need Help?
                        </a>
                    </div>
                </div>
                
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
        <?php endif; ?>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>