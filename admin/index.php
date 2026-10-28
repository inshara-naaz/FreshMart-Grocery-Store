<?php
// ============================================
// admin/index.php - Admin Dashboard
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============ LOGIN CHECK ============
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ STATS ============
// Total Orders
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders");
$totalOrders = (int)$stmt->fetch()['cnt'];

// Total Sales (delivered + pending + confirmed)
$stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE status IN ('pending', 'confirmed', 'delivered')");
$totalSales = (float)$stmt->fetch()['total'];

// Total Users (role='user')
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user'");
$totalUsers = (int)$stmt->fetch()['cnt'];

// Total Products
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products");
$totalProducts = (int)$stmt->fetch()['cnt'];

// Pending Orders
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
$pendingOrders = (int)$stmt->fetch()['cnt'];

// Delivered Orders
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'delivered'");
$deliveredOrders = (int)$stmt->fetch()['cnt'];

// Cancelled Orders
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'cancelled'");
$cancelledOrders = (int)$stmt->fetch()['cnt'];

// Confirmed Orders
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'confirmed'");
$confirmedOrders = (int)$stmt->fetch()['cnt'];

// ============ SALES CHART (Last 7 days) ============
$salesData = [];
$salesLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $salesLabels[] = date('D', strtotime($date));
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE DATE(created_at) = ? AND status != 'cancelled'");
    $stmt->execute([$date]);
    $salesData[] = (float)$stmt->fetch()['total'];
}

// ============ RECENT ORDERS ============
$stmt = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentOrders = $stmt->fetchAll();

// ============ TOP PRODUCTS (Best Selling) ============
$stmt = $pdo->query("
    SELECT 
        oi.product_id,
        oi.product_name,
        oi.product_image,
        SUM(oi.quantity) AS total_qty,
        SUM(oi.line_total) AS total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled'
    GROUP BY oi.product_id, oi.product_name, oi.product_image
    ORDER BY total_qty DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll();

// ============ LOW STOCK PRODUCTS ============
$stmt = $pdo->query("
    SELECT * FROM products 
    WHERE stock <= 15 
    ORDER BY stock ASC 
    LIMIT 5
");
$lowStockProducts = $stmt->fetchAll();

// ============ PAGE VARIABLES ============
$pageTitle = 'Dashboard';
$pageSubtitle = 'Welcome back, ' . ($_SESSION['admin_name'] ?? 'Admin');

// ============ INCLUDE HEADER ============
include 'includes/admin-header.php';
?>

<!-- ================= STATS GRID ================= -->
<div class="stats-grid">
    
    <div class="stat-admin-card stat-green" data-aos="fade-up" data-aos-delay="0">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-shopping-bag"></i></div>
            <span class="stat-admin-trend"><i class="fas fa-arrow-up"></i> All Time</span>
        </div>
        <div class="stat-admin-value" data-counter="<?php echo $totalOrders; ?>">0</div>
        <div class="stat-admin-label">Total Orders</div>
    </div>
    
    <div class="stat-admin-card stat-orange" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-money-bill-wave"></i></div>
            <span class="stat-admin-trend"><i class="fas fa-arrow-up"></i> Revenue</span>
        </div>
        <div class="stat-admin-value">Rs. <span data-counter="<?php echo round($totalSales); ?>">0</span></div>
        <div class="stat-admin-label">Total Sales</div>
    </div>
    
    <div class="stat-admin-card stat-blue" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-users"></i></div>
            <span class="stat-admin-trend"><i class="fas fa-arrow-up"></i> Customers</span>
        </div>
        <div class="stat-admin-value" data-counter="<?php echo $totalUsers; ?>">0</div>
        <div class="stat-admin-label">Registered Users</div>
    </div>
    
    <div class="stat-admin-card stat-gold" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-box"></i></div>
            <span class="stat-admin-trend">In Stock</span>
        </div>
        <div class="stat-admin-value" data-counter="<?php echo $totalProducts; ?>">0</div>
        <div class="stat-admin-label">Total Products</div>
    </div>
    
</div>

<!-- ================= CHARTS ROW ================= -->
<div class="row g-4 mb-4">
    
    <!-- Sales Chart -->
    <div class="col-lg-8" data-aos="fade-up">
        <div class="admin-card" style="height: 100%;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-chart-line" style="color:var(--primary);"></i> Sales Overview
                    </h3>
                    <p style="font-size:12px; color:var(--gray); margin:4px 0 0;">Last 7 days performance</p>
                </div>
                <span style="background: rgba(46,125,50,0.1); color:var(--primary-dark); padding:5px 12px; border-radius:50px; font-size:11px; font-weight:700;">
                    <i class="fas fa-calendar-alt"></i> This Week
                </span>
            </div>
            <div style="position:relative; height: 280px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Order Status Chart -->
    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
        <div class="admin-card" style="height: 100%;">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-chart-pie" style="color:var(--accent);"></i> Order Status
            </h3>
            <div style="position:relative; height: 200px; margin-bottom:15px;">
                <canvas id="orderStatusChart"></canvas>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:15px;">
                <div style="display:flex; align-items:center; gap:8px; font-size:12px;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#FFB300;"></span>
                    <span style="color:var(--gray);">Pending: <strong style="color:var(--dark);"><?php echo $pendingOrders; ?></strong></span>
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#2196F3;"></span>
                    <span style="color:var(--gray);">Confirmed: <strong style="color:var(--dark);"><?php echo $confirmedOrders; ?></strong></span>
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#2E7D32;"></span>
                    <span style="color:var(--gray);">Delivered: <strong style="color:var(--dark);"><?php echo $deliveredOrders; ?></strong></span>
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#E53935;"></span>
                    <span style="color:var(--gray);">Cancelled: <strong style="color:var(--dark);"><?php echo $cancelledOrders; ?></strong></span>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- ================= RECENT ORDERS + TOP PRODUCTS ================= -->
<div class="row g-4 mb-4">
    
    <!-- Recent Orders -->
    <div class="col-lg-7" data-aos="fade-up">
        <div class="admin-table-wrap" style="height: 100%;">
            <div class="admin-table-header">
                <h3><i class="fas fa-clock"></i> Recent Orders</h3>
                <a href="orders.php" class="btn-admin btn-outline-admin" style="padding:7px 14px; font-size:12px;">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (empty($recentOrders)): ?>
            <div class="admin-empty" style="padding: 40px 20px;">
                <i class="fas fa-shopping-bag"></i>
                <h3>No orders yet</h3>
                <p>Orders will appear here once customers start buying</p>
            </div>
            <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order):
                            $statusClass = 'badge-' . $order['status'];
                        ?>
                        <tr>
                            <td>
                                <strong style="color:var(--primary-dark); font-size:12px; letter-spacing:0.5px;">
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </strong>
                            </td>
                            <td>
                                <div class="table-product-info">
                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                    <span><?php echo date('d M, h:i A', strtotime($order['created_at'])); ?></span>
                                </div>
                            </td>
                            <td><strong>Rs. <?php echo number_format($order['total']); ?></strong></td>
                            <td>
                                <span class="table-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="table-action-btn view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
        <div class="admin-card" style="height: 100%;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-fire" style="color:var(--accent);"></i> Top Products
                </h3>
                <a href="products.php" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">
                    View All
                </a>
            </div>
            
            <?php if (empty($topProducts)): ?>
            <div class="admin-empty" style="padding: 40px 20px;">
                <i class="fas fa-box"></i>
                <h3>No sales yet</h3>
                <p>Best sellers will appear here</p>
            </div>
            <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:14px;">
                <?php foreach ($topProducts as $i => $tp): ?>
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                    <div style="position:relative; flex-shrink:0;">
                        <img src="<?php echo htmlspecialchars($tp['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($tp['product_name']); ?>"
                             style="width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid var(--border);">
                        <span style="position:absolute; top:-6px; left:-6px; background:var(--primary); color:var(--white); font-size:9px; font-weight:700; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid var(--white);">
                            <?php echo $i + 1; ?>
                        </span>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <strong style="display:block; font-size:13px; color:var(--dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($tp['product_name']); ?>
                        </strong>
                        <span style="font-size:11px; color:var(--gray);">
                            Sold: <?php echo $tp['total_qty']; ?> units
                        </span>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <strong style="display:block; font-size:13px; color:var(--primary-dark);">
                            Rs. <?php echo number_format($tp['total_revenue']); ?>
                        </strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- ================= LOW STOCK ALERT ================= -->
<?php if (!empty($lowStockProducts)): ?>
<div class="admin-card" style="border-left: 4px solid var(--warning);" data-aos="fade-up">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
            <h3 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Low Stock Alert
            </h3>
            <p style="font-size:12px; color:var(--gray); margin:4px 0 0;">Products running low on inventory</p>
        </div>
        <a href="products.php" class="btn-admin btn-outline-admin" style="padding:7px 14px; font-size:12px;">
            Manage Stock <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $lp): 
                    $stockColor = $lp['stock'] <= 5 ? 'var(--danger)' : ($lp['stock'] <= 10 ? 'var(--warning)' : 'var(--info)');
                ?>
                <tr>
                    <td>
                        <div class="table-product">
                            <img src="<?php echo htmlspecialchars($lp['image']); ?>" alt="<?php echo htmlspecialchars($lp['name']); ?>">
                            <div class="table-product-info">
                                <strong><?php echo htmlspecialchars($lp['name']); ?></strong>
                                <span><?php echo htmlspecialchars($lp['unit']); ?></span>
                            </div>
                        </div>
                    </td>
                    <td><code style="font-size:11px; background:var(--cream); padding:3px 8px; border-radius:5px;"><?php echo htmlspecialchars($lp['sku']); ?></code></td>
                    <td><strong>Rs. <?php echo number_format($lp['price']); ?></strong></td>
                    <td>
                        <span style="background: <?php echo $stockColor; ?>; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700;">
                            <?php echo $lp['stock']; ?> left
                        </span>
                    </td>
                    <td>
                        <a href="product-edit.php?id=<?php echo $lp['id']; ?>" class="table-action-btn stock" title="Update Stock">
                            <i class="fas fa-boxes"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ================= QUICK ACTIONS ================= -->
<div class="row g-4 mt-2">
    <div class="col-12" data-aos="fade-up">
        <div class="admin-card">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-bolt" style="color:var(--gold);"></i> Quick Actions
            </h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                <a href="product-add.php" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--cream); border-radius:12px; text-decoration:none; color:var(--dark); transition:all 0.3s ease;" onmouseover="this.style.background='rgba(46,125,50,0.1)'; this.style.transform='translateY(-3px)'" onmouseout="this.style.background='var(--cream)'; this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg, var(--primary), var(--primary-light)); color:var(--white); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px;">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <strong style="display:block; font-size:13px;">Add Product</strong>
                        <span style="font-size:11px; color:var(--gray);">Create new product</span>
                    </div>
                </a>
                
                <a href="orders.php?status=pending" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--cream); border-radius:12px; text-decoration:none; color:var(--dark); transition:all 0.3s ease;" onmouseover="this.style.background='rgba(255,111,0,0.1)'; this.style.transform='translateY(-3px)'" onmouseout="this.style.background='var(--cream)'; this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg, var(--accent), #FF9800); color:var(--white); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <strong style="display:block; font-size:13px;">Pending Orders</strong>
                        <span style="font-size:11px; color:var(--gray);"><?php echo $pendingOrders; ?> need attention</span>
                    </div>
                </a>
                
                <a href="categories.php" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--cream); border-radius:12px; text-decoration:none; color:var(--dark); transition:all 0.3s ease;" onmouseover="this.style.background='rgba(33,150,243,0.1)'; this.style.transform='translateY(-3px)'" onmouseout="this.style.background='var(--cream)'; this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg, #2196F3, #1565C0); color:var(--white); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px;">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <div>
                        <strong style="display:block; font-size:13px;">Categories</strong>
                        <span style="font-size:11px; color:var(--gray);">Manage categories</span>
                    </div>
                </a>
                
                <a href="users.php" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--cream); border-radius:12px; text-decoration:none; color:var(--dark); transition:all 0.3s ease;" onmouseover="this.style.background='rgba(255,179,0,0.15)'; this.style.transform='translateY(-3px)'" onmouseout="this.style.background='var(--cream)'; this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg, var(--gold), #FF8F00); color:var(--white); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <strong style="display:block; font-size:13px;">Customers</strong>
                        <span style="font-size:11px; color:var(--gray);"><?php echo $totalUsers; ?> registered</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ================= CHART.JS SCRIPTS ================= -->
<script>
// ============ SALES CHART (Bar) ============
const salesCtx = document.getElementById('salesChart');
if (salesCtx) {
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($salesLabels); ?>,
            datasets: [{
                label: 'Sales (Rs.)',
                data: <?php echo json_encode($salesData); ?>,
                backgroundColor: 'rgba(46, 125, 50, 0.8)',
                hoverBackgroundColor: 'rgba(46, 125, 50, 1)',
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 40,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1A1A1A',
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { family: 'Poppins', size: 12, weight: '700' },
                    bodyFont: { family: 'Poppins', size: 12 },
                    callbacks: {
                        label: function(context) {
                            return 'Rs. ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F0F0F0', drawBorder: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11 },
                        color: '#999',
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11, weight: '600' },
                        color: '#616161'
                    }
                }
            }
        }
    });
}

// ============ ORDER STATUS CHART (Doughnut) ============
const statusCtx = document.getElementById('orderStatusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [
                    <?php echo $pendingOrders; ?>,
                    <?php echo $confirmedOrders; ?>,
                    <?php echo $deliveredOrders; ?>,
                    <?php echo $cancelledOrders; ?>
                ],
                backgroundColor: ['#FFB300', '#2196F3', '#2E7D32', '#E53935'],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1A1A1A',
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { family: 'Poppins', size: 12, weight: '700' },
                    bodyFont: { family: 'Poppins', size: 12 },
                }
            }
        }
    });
}

// ============ ANIMATED COUNTERS ============
document.querySelectorAll('[data-counter]').forEach(el => {
    const target = parseInt(el.getAttribute('data-counter')) || 0;
    const duration = 1500;
    const start = performance.now();
    
    function update(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(eased * target);
        el.textContent = current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            el.textContent = target.toLocaleString();
        }
    }
    requestAnimationFrame(update);
});
</script>

<?php
include 'includes/admin-footer.php';
?>