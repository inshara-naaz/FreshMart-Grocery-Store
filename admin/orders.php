<?php
// ============================================
// admin/orders.php - Orders Management
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ HANDLE STATUS UPDATE (Quick) ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    
    $allowed = ['pending', 'confirmed', 'delivered', 'cancelled'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Order status updated to ' . ucfirst($newStatus)];
    }
    header('Location: orders.php' . (isset($_POST['return_query']) ? '?' . $_POST['return_query'] : ''));
    exit;
}

// ============ FILTERS ============
$searchQuery   = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter  = isset($_GET['status']) ? $_GET['status'] : '';
$dateFilter    = isset($_GET['date']) ? $_GET['date'] : '';
$sortBy        = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// ============ PAGINATION ============
$perPage = 10;
$currentPage = 1;
if (isset($_GET['page']) && $_GET['page'] !== '' && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
    if ($currentPage < 1) $currentPage = 1;
}

// ============ BUILD WHERE ============
$where = "WHERE 1=1";
$params = [];

if ($searchQuery !== '') {
    $where .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$allowedStatuses = ['pending', 'confirmed', 'delivered', 'cancelled'];
if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses)) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($dateFilter === 'today') {
    $where .= " AND DATE(created_at) = CURDATE()";
} elseif ($dateFilter === 'week') {
    $where .= " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dateFilter === 'month') {
    $where .= " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

// ============ SORT ============
$orderBy = "ORDER BY id DESC";
if ($sortBy === 'oldest') {
    $orderBy = "ORDER BY id ASC";
} elseif ($sortBy === 'amount_high') {
    $orderBy = "ORDER BY total DESC";
} elseif ($sortBy === 'amount_low') {
    $orderBy = "ORDER BY total ASC";
}

// ============ COUNT ============
$countSql = "SELECT COUNT(*) AS cnt FROM orders $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$countRow = $stmt->fetch();
$totalOrders = $countRow ? (int)$countRow['cnt'] : 0;

$totalPages = (int)ceil($totalOrders / $perPage);
if ($totalPages < 1) $totalPages = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

// ============ FETCH ORDERS ============
$sql = "SELECT o.*, 
        (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_id = o.id) AS total_items
        FROM orders o 
        $where 
        $orderBy 
        LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// ============ MINI STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders");
$statTotal = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
$statPending = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'confirmed'");
$statConfirmed = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'delivered'");
$statDelivered = (int)$stmt->fetch()['cnt'];

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ CURRENT QUERY STRING (for redirect) ============
$returnQuery = http_build_query(array_filter([
    'search' => $searchQuery,
    'status' => $statusFilter,
    'date' => $dateFilter,
    'sort' => $sortBy,
    'page' => $currentPage > 1 ? $currentPage : null,
]));

// ============ PAGE VARIABLES ============
$pageTitle = 'Orders';
$pageSubtitle = 'Manage customer orders';

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
        <h1>Orders Management</h1>
        <p>Total <?php echo $totalOrders; ?> order<?php echo $totalOrders != 1 ? 's' : ''; ?> found</p>
    </div>
</div>

<!-- ================= MINI STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    <div class="stat-admin-card stat-green" data-aos="fade-up">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-shopping-bag"></i></div></div>
        <div class="stat-admin-value"><?php echo $statTotal; ?></div>
        <div class="stat-admin-label">Total Orders</div>
    </div>
    <div class="stat-admin-card stat-gold" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-clock"></i></div></div>
        <div class="stat-admin-value"><?php echo $statPending; ?></div>
        <div class="stat-admin-label">Pending</div>
    </div>
    <div class="stat-admin-card stat-blue" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-check"></i></div></div>
        <div class="stat-admin-value"><?php echo $statConfirmed; ?></div>
        <div class="stat-admin-label">Confirmed</div>
    </div>
    <div class="stat-admin-card stat-orange" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-check-circle"></i></div></div>
        <div class="stat-admin-value"><?php echo $statDelivered; ?></div>
        <div class="stat-admin-label">Delivered</div>
    </div>
</div>

<!-- ================= FILTER BAR ================= -->
<div class="admin-card" style="padding: 20px; margin-bottom: 25px;" data-aos="fade-up">
    <form method="GET" action="orders.php" id="filterForm" style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:12px; align-items:end;">

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Search</label>
            <input type="text" name="search" placeholder="Order#, customer, phone..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Date</label>
            <select name="date">
                <option value="">All Time</option>
                <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $dateFilter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="month" <?php echo $dateFilter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
            </select>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Sort</label>
            <select name="sort">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="amount_high" <?php echo $sortBy === 'amount_high' ? 'selected' : ''; ?>>Amount: High to Low</option>
                <option value="amount_low" <?php echo $sortBy === 'amount_low' ? 'selected' : ''; ?>>Amount: Low to High</option>
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-admin btn-primary-admin" style="padding:12px 18px;">
                <i class="fas fa-filter"></i>
            </button>
            <a href="orders.php" class="btn-admin btn-outline-admin" style="padding:12px 18px;">
                <i class="fas fa-redo"></i>
            </a>
        </div>

    </form>
</div>

<!-- ================= ORDERS TABLE ================= -->
<div class="admin-table-wrap" data-aos="fade-up">

    <div class="admin-table-header">
        <h3><i class="fas fa-list"></i> All Orders</h3>
        <div style="font-size:12px; color:var(--gray);">
            Showing <strong><?php echo count($orders); ?></strong> of <strong><?php echo $totalOrders; ?></strong>
        </div>
    </div>

    <?php if (empty($orders)): ?>
    
    <div class="admin-empty">
        <i class="fas fa-shopping-bag"></i>
        <h3>No orders found</h3>
        <p>Try adjusting filters</p>
    </div>
    
    <?php else: ?>
    
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): 
                    $statusColor = [
                        'pending' => '#FFB300',
                        'confirmed' => '#2196F3',
                        'delivered' => '#2E7D32',
                        'cancelled' => '#E53935',
                    ][$o['status']] ?? '#616161';
                ?>
                <tr>
                    <td>
                        <div>
                            <strong style="color:var(--primary-dark); font-size:12px; letter-spacing:0.5px;">
                                <?php echo htmlspecialchars($o['order_number']); ?>
                            </strong>
                            <span style="display:block; font-size:11px; color:var(--gray); margin-top:2px;">
                                <?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="table-product-info">
                            <strong><?php echo htmlspecialchars($o['customer_name']); ?></strong>
                            <span><i class="fas fa-phone" style="font-size:10px;"></i> <?php echo htmlspecialchars($o['customer_phone']); ?></span>
                            <span><i class="fas fa-map-marker-alt" style="font-size:10px;"></i> <?php echo htmlspecialchars($o['customer_city']); ?></span>
                        </div>
                    </td>
                    <td>
                        <span style="background:var(--cream); padding:4px 10px; border-radius:50px; font-size:11px; font-weight:600;">
                            <i class="fas fa-box"></i> <?php echo (int)$o['total_items']; ?> item<?php echo $o['total_items'] != 1 ? 's' : ''; ?>
                        </span>
                    </td>
                    <td>
                        <strong style="color:var(--primary-dark); font-size:14px;">
                            Rs. <?php echo number_format($o['total']); ?>
                        </strong>
                        <span style="display:block; font-size:10px; color:var(--gray); text-transform:uppercase; font-weight:600; margin-top:2px;">
                            <?php echo ucwords(str_replace('_', ' ', $o['payment_method'])); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="orders.php" style="display:inline;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                            <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($returnQuery); ?>">
                            
                            <select name="status" onchange="this.form.submit()" 
                                    style="padding:6px 10px; border:2px solid <?php echo $statusColor; ?>; border-radius:8px; font-size:11px; font-weight:700; color:<?php echo $statusColor; ?>; background:white; cursor:pointer; font-family:'Poppins',sans-serif; outline:none;">
                                <option value="pending" <?php echo $o['status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                <option value="confirmed" <?php echo $o['status'] === 'confirmed' ? 'selected' : ''; ?>>✓ Confirmed</option>
                                <option value="delivered" <?php echo $o['status'] === 'delivered' ? 'selected' : ''; ?>>✓✓ Delivered</option>
                                <option value="cancelled" <?php echo $o['status'] === 'cancelled' ? 'selected' : ''; ?>>✗ Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content: flex-end;">
                            <a href="order-detail.php?id=<?php echo (int)$o['id']; ?>" class="table-action-btn view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ================= PAGINATION ================= -->
    <?php if ($totalPages > 1): 
        $cp = (int)$currentPage;
        $pp = (int)$perPage;
        $tp = (int)$totalOrders;
        $tpg = (int)$totalPages;
        $startNum = (($cp - 1) * $pp) + 1;
        $endNum = min($cp * $pp, $tp);
    ?>
    <div style="padding: 22px 25px; border-top: 1px solid #E8E8E8; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background: #FAF8F3;">
        <div style="font-size: 13px; color: #616161;">
            Page <strong style="color:#1B5E20;"><?php echo $cp; ?></strong> 
            of <strong style="color:#1B5E20;"><?php echo $tpg; ?></strong>
            <span style="margin-left:10px; color:#999;">|</span>
            <span style="margin-left:10px;">
                Showing <strong><?php echo $startNum; ?>-<?php echo $endNum; ?></strong> of <strong><?php echo $tp; ?></strong>
            </span>
        </div>
        <div style="display: flex; gap: 6px; flex-wrap:wrap;">
            <?php if ($cp > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $cp - 1])); ?>" 
               style="min-width:38px; height:38px; padding: 0 12px; border-radius:10px; background:#FFF; border:2px solid #E8E8E8; display:flex; align-items:center; justify-content:center; color:#212121; text-decoration:none; font-size: 13px; font-weight: 600;">
                <i class="fas fa-chevron-left"></i> Prev
            </a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $cp - 2);
            $endPage = min($tpg, $cp + 2);
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
               style="min-width:38px; height:38px; padding: 0 12px; border-radius:10px; background:<?php echo $i === $cp ? '#2E7D32' : '#FFF'; ?>; border:2px solid <?php echo $i === $cp ? '#2E7D32' : '#E8E8E8'; ?>; display:flex; align-items:center; justify-content:center; color:<?php echo $i === $cp ? '#FFF' : '#212121'; ?>; text-decoration:none; font-size:13px; font-weight:700;">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($cp < $tpg): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $cp + 1])); ?>" 
               style="min-width:38px; height:38px; padding: 0 12px; border-radius:10px; background:#FFF; border:2px solid #E8E8E8; display:flex; align-items:center; justify-content:center; color:#212121; text-decoration:none; font-size: 13px; font-weight: 600;">
                Next <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>

</div>
<!-- End admin-table-wrap -->

<script>
// ============ AUTO SUBMIT ON FILTER CHANGE ============
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.querySelectorAll('select').forEach(function(select) {
            select.addEventListener('change', function() {
                form.submit();
            });
        });
        
        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>