<?php
// ============================================
// admin/order-detail.php - Single Order Detail
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ GET ORDER ID ============
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'msg' => 'Invalid order ID'];
    header('Location: orders.php');
    exit;
}

// ============ HANDLE STATUS UPDATE ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $allowed = ['pending', 'confirmed', 'delivered', 'cancelled'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Status updated to ' . ucfirst($newStatus)];
    }
    header('Location: order-detail.php?id=' . $orderId);
    exit;
}

// ============ FETCH ORDER ============
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'msg' => 'Order not found'];
    header('Location: orders.php');
    exit;
}

// ============ FETCH ORDER ITEMS ============
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// ============ FETCH CUSTOMER INFO (if registered) ============
$customer = null;
if (!empty($order['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$order['user_id']]);
    $customer = $stmt->fetch();
}

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ STATUS CONFIG ============
$statusColor = [
    'pending' => ['color' => '#FFB300', 'bg' => 'rgba(255,179,0,0.15)', 'icon' => 'fa-clock'],
    'confirmed' => ['color' => '#2196F3', 'bg' => 'rgba(33,150,243,0.15)', 'icon' => 'fa-check'],
    'delivered' => ['color' => '#2E7D32', 'bg' => 'rgba(46,125,50,0.15)', 'icon' => 'fa-check-circle'],
    'cancelled' => ['color' => '#E53935', 'bg' => 'rgba(229,57,53,0.15)', 'icon' => 'fa-times-circle'],
];
$st = $statusColor[$order['status']] ?? $statusColor['pending'];

// ============ PAGE VARIABLES ============
$pageTitle = 'Order Details';
$pageSubtitle = 'Order #' . $order['order_number'];

include 'includes/admin-header.php';
?>

<!-- Flash Message -->
<?php if ($flash): ?>
<div style="background:<?php echo $flash['type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; color:<?php echo $flash['type'] === 'success' ? '#155724' : '#721c24'; ?>; padding:14px 20px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:10px; border-left:4px solid <?php echo $flash['type'] === 'success' ? '#28a745' : '#dc3545'; ?>;" data-aos="fade-down">
    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($flash['msg']); ?>
</div>
<?php endif; ?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        <p>Placed on <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <div class="page-header-actions">
        <a href="orders.php" class="btn-admin btn-outline-admin">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <button onclick="window.print()" class="btn-admin btn-primary-admin">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<!-- ================= STATUS BANNER ================= -->
<div class="admin-card" style="margin-bottom:20px; border-left:4px solid <?php echo $st['color']; ?>; background:<?php echo $st['bg']; ?>;" data-aos="fade-up">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="width:60px; height:60px; background:<?php echo $st['color']; ?>; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:26px;">
                <i class="fas <?php echo $st['icon']; ?>"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--gray); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Current Status</span>
                <h2 style="font-size:24px; color:<?php echo $st['color']; ?>; margin:4px 0 0; text-transform:capitalize;">
                    <?php echo htmlspecialchars($order['status']); ?>
                </h2>
            </div>
        </div>
        
        <form method="POST" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="update_status" value="1">
            <label style="font-size:13px; font-weight:600; color:var(--dark);">Change Status:</label>
            <select name="status" class="admin-form-group" style="padding:10px 15px; border:2px solid <?php echo $st['color']; ?>; border-radius:10px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; color:<?php echo $st['color']; ?>; cursor:pointer; outline:none; background:white;">
                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>✓ Confirmed</option>
                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>✓✓ Delivered</option>
                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>✗ Cancelled</option>
            </select>
            <button type="submit" class="btn-admin btn-primary-admin" style="padding:10px 18px;">
                <i class="fas fa-save"></i> Update
            </button>
        </form>
    </div>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="row g-4">
    
    <!-- LEFT COLUMN -->
    <div class="col-lg-8">
        
        <!-- Order Items -->
        <div class="admin-table-wrap" style="margin-bottom:20px;" data-aos="fade-up">
            <div class="admin-table-header">
                <h3><i class="fas fa-shopping-bag"></i> Order Items (<?php echo count($items); ?>)</h3>
            </div>
            
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="table-product">
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    <div class="table-product-info">
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        <span><?php echo htmlspecialchars($item['unit_label']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>Rs. <?php echo number_format($item['unit_price']); ?></td>
                            <td>
                                <span style="background:var(--cream); padding:3px 10px; border-radius:50px; font-size:12px; font-weight:700;">
                                    × <?php echo (int)$item['quantity']; ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <strong style="color:var(--primary-dark);">Rs. <?php echo number_format($item['line_total']); ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Totals -->
            <div style="padding:20px 25px; border-top:2px dashed var(--border); background:var(--cream);">
                <div style="max-width:350px; margin-left:auto;">
                    <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:13px;">
                        <span style="color:var(--gray);">Subtotal:</span>
                        <strong>Rs. <?php echo number_format($order['subtotal']); ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:13px;">
                        <span style="color:var(--gray);">Delivery Fee:</span>
                        <strong><?php echo $order['delivery_fee'] == 0 ? 'FREE' : 'Rs. ' . number_format($order['delivery_fee']); ?></strong>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:13px; color:var(--danger);">
                        <span>Discount (<?php echo htmlspecialchars($order['coupon_code']); ?>):</span>
                        <strong>- Rs. <?php echo number_format($order['discount']); ?></strong>
                    </div>
                    <?php endif; ?>
                    <div style="display:flex; justify-content:space-between; padding:12px 0 6px; border-top:2px solid var(--primary); margin-top:8px; font-size:16px;">
                        <strong>Total:</strong>
                        <strong style="color:var(--primary-dark); font-size:20px;">Rs. <?php echo number_format($order['total']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delivery Notes -->
        <?php if (!empty($order['delivery_notes'])): ?>
        <div class="admin-card" data-aos="fade-up">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-comment-dots" style="color:var(--info);"></i> Delivery Notes
            </h3>
            <p style="font-size:13px; color:var(--gray); line-height:1.8; margin:0; padding:15px; background:var(--cream); border-radius:10px; border-left:3px solid var(--info);">
                <?php echo nl2br(htmlspecialchars($order['delivery_notes'])); ?>
            </p>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">
        
        <!-- Customer Info -->
        <div class="admin-card" style="margin-bottom:20px;" data-aos="fade-up" data-aos-delay="100">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                <i class="fas fa-user" style="color:var(--primary);"></i> Customer
            </h3>
            
            <div style="display:flex; flex-direction:column; gap:15px;">
                <div style="display:flex; gap:12px; align-items:center;">
                    <div style="width:45px; height:45px; background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; flex-shrink:0;">
                        <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <strong style="display:block; font-size:14px;"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                        <span style="font-size:11px; color:var(--gray);">
                            <?php echo $customer ? 'Registered Customer' : 'Guest Customer'; ?>
                        </span>
                    </div>
                </div>
                
                <div style="border-top:1px dashed var(--border); padding-top:15px;">
                    <div style="display:flex; gap:10px; margin-bottom:10px; font-size:13px;">
                        <i class="fas fa-envelope" style="color:var(--primary); margin-top:3px;"></i>
                        <span style="word-break:break-all;"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </div>
                    <div style="display:flex; gap:10px; margin-bottom:10px; font-size:13px;">
                        <i class="fas fa-phone" style="color:var(--primary); margin-top:3px;"></i>
                        <span><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                    </div>
                    <div style="display:flex; gap:10px; font-size:13px;">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary); margin-top:3px;"></i>
                        <div>
                            <div><?php echo htmlspecialchars($order['customer_address']); ?></div>
                            <div style="color:var(--gray);"><?php echo htmlspecialchars($order['customer_city']); ?>
                            <?php if (!empty($order['customer_postal'])): ?>
                                - <?php echo htmlspecialchars($order['customer_postal']); ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Info -->
        <div class="admin-card" style="margin-bottom:20px;" data-aos="fade-up" data-aos-delay="200">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                <i class="fas fa-credit-card" style="color:var(--accent);"></i> Payment
            </h3>
            
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px dashed var(--border); font-size:13px;">
                <span style="color:var(--gray);">Method</span>
                <strong><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; font-size:13px;">
                <span style="color:var(--gray);">Amount</span>
                <strong style="color:var(--primary-dark); font-size:16px;">Rs. <?php echo number_format($order['total']); ?></strong>
            </div>
        </div>
        
        <!-- Order Timeline -->
        <div class="admin-card" data-aos="fade-up" data-aos-delay="300">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                <i class="fas fa-clock" style="color:var(--info);"></i> Order Timeline
            </h3>
            
            <div style="position:relative; padding-left:25px;">
                <!-- Timeline line -->
                <div style="position:absolute; left:8px; top:8px; bottom:8px; width:2px; background:var(--border);"></div>
                
                <!-- Step 1: Placed -->
                <div style="position:relative; padding-bottom:20px;">
                    <div style="position:absolute; left:-25px; width:18px; height:18px; background:var(--primary); border:3px solid white; border-radius:50%; box-shadow:0 0 0 2px var(--primary);"></div>
                    <div style="font-size:13px; font-weight:700; color:var(--dark);">Order Placed</div>
                    <div style="font-size:11px; color:var(--gray);"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
                </div>
                
                <!-- Step 2: Confirmed -->
                <div style="position:relative; padding-bottom:20px;">
                    <div style="position:absolute; left:-25px; width:18px; height:18px; background:<?php echo in_array($order['status'], ['confirmed', 'delivered']) ? 'var(--primary)' : 'var(--border)'; ?>; border:3px solid white; border-radius:50%; box-shadow:0 0 0 2px <?php echo in_array($order['status'], ['confirmed', 'delivered']) ? 'var(--primary)' : 'var(--border)'; ?>;"></div>
                    <div style="font-size:13px; font-weight:700; color:<?php echo in_array($order['status'], ['confirmed', 'delivered']) ? 'var(--dark)' : 'var(--gray)'; ?>;">Confirmed</div>
                </div>
                
                <!-- Step 3: Delivered -->
                <div style="position:relative;">
                    <div style="position:absolute; left:-25px; width:18px; height:18px; background:<?php echo $order['status'] === 'delivered' ? 'var(--primary)' : 'var(--border)'; ?>; border:3px solid white; border-radius:50%; box-shadow:0 0 0 2px <?php echo $order['status'] === 'delivered' ? 'var(--primary)' : 'var(--border)'; ?>;"></div>
                    <div style="font-size:13px; font-weight:700; color:<?php echo $order['status'] === 'delivered' ? 'var(--dark)' : 'var(--gray)'; ?>;">Delivered</div>
                </div>
            </div>
        </div>
        
    </div>
    
</div>

<?php include 'includes/admin-footer.php'; ?>