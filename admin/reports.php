<?php
// ============================================
// admin/reports.php - Sales Reports & Analytics
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ DATE RANGE ============
$range = isset($_GET['range']) ? $_GET['range'] : 'month';
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

if ($range === 'today') {
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d');
} elseif ($range === 'week') {
    $startDate = date('Y-m-d', strtotime('-7 days'));
    $endDate = date('Y-m-d');
} elseif ($range === 'month') {
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d');
} elseif ($range === 'year') {
    $startDate = date('Y-m-d', strtotime('-365 days'));
    $endDate = date('Y-m-d');
}

// ============ SUMMARY STATS ============
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(total), 0) AS total_sales,
        COALESCE(AVG(total), 0) AS avg_order,
        COALESCE(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END), 0) AS delivered_orders,
        COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancelled_orders
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
");
$stmt->execute([$startDate, $endDate]);
$stats = $stmt->fetch();

$totalOrders = (int)$stats['total_orders'];
$totalSales = (float)$stats['total_sales'];
$avgOrder = (float)$stats['avg_order'];
$deliveredOrders = (int)$stats['delivered_orders'];
$cancelledOrders = (int)$stats['cancelled_orders'];

// ============ PREVIOUS PERIOD (for growth) ============
$daysDiff = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;
$prevStart = date('Y-m-d', strtotime($startDate . ' -' . $daysDiff . ' days'));
$prevEnd = date('Y-m-d', strtotime($endDate . ' -' . $daysDiff . ' days'));

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(total), 0) AS total_sales
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
");
$stmt->execute([$prevStart, $prevEnd]);
$prevStats = $stmt->fetch();
$prevSales = (float)$prevStats['total_sales'];
$prevOrders = (int)$prevStats['total_orders'];

$salesGrowth = $prevSales > 0 ? (($totalSales - $prevSales) / $prevSales) * 100 : 0;
$ordersGrowth = $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;

// ============ DAILY SALES (for line chart) ============
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) AS sale_date,
        COUNT(*) AS orders_count,
        COALESCE(SUM(total), 0) AS daily_sales
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY sale_date ASC
");
$stmt->execute([$startDate, $endDate]);
$dailySales = $stmt->fetchAll();

// Agar kam din hain, to missing dates fill karo
$dateMap = [];
foreach ($dailySales as $row) {
    $dateMap[$row['sale_date']] = $row;
}

$chartLabels = [];
$chartSales = [];
$chartOrders = [];

$currentDate = $startDate;
$maxPoints = 30;
$totalDays = $daysDiff;

// Agar zyada din hain, to last 30 din
if ($totalDays > $maxPoints) {
    $currentDate = date('Y-m-d', strtotime($endDate . ' -' . ($maxPoints - 1) . ' days'));
}

while (strtotime($currentDate) <= strtotime($endDate)) {
    $chartLabels[] = date('d M', strtotime($currentDate));
    if (isset($dateMap[$currentDate])) {
        $chartSales[] = (float)$dateMap[$currentDate]['daily_sales'];
        $chartOrders[] = (int)$dateMap[$currentDate]['orders_count'];
    } else {
        $chartSales[] = 0;
        $chartOrders[] = 0;
    }
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

// ============ TOP PRODUCTS ============
$stmt = $pdo->prepare("
    SELECT 
        oi.product_id,
        oi.product_name,
        oi.product_image,
        SUM(oi.quantity) AS total_qty,
        SUM(oi.line_total) AS total_revenue,
        COUNT(DISTINCT oi.order_id) AS order_count
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'cancelled'
    GROUP BY oi.product_id, oi.product_name, oi.product_image
    ORDER BY total_revenue DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$topProducts = $stmt->fetchAll();

// ============ CATEGORY SALES ============
$stmt = $pdo->prepare("
    SELECT 
        c.name AS category_name,
        COALESCE(SUM(oi.line_total), 0) AS category_sales,
        COALESCE(SUM(oi.quantity), 0) AS category_qty
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status != 'cancelled'
    GROUP BY c.id, c.name
    ORDER BY category_sales DESC
");
$stmt->execute([$startDate, $endDate]);
$categorySales = $stmt->fetchAll();

$catLabels = [];
$catSales = [];
foreach ($categorySales as $cs) {
    $catLabels[] = $cs['category_name'];
    $catSales[] = (float)$cs['category_sales'];
}

// ============ RECENT ORDERS ============
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    ORDER BY id DESC 
    LIMIT 5
");
$stmt->execute([$startDate, $endDate]);
$recentOrders = $stmt->fetchAll();

// ============ PAGE VARIABLES ============
$pageTitle = 'Reports';
$pageSubtitle = 'Sales analytics and insights';

include 'includes/admin-header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Reports & Analytics</h1>
        <p><?php echo date('d M Y', strtotime($startDate)); ?> - <?php echo date('d M Y', strtotime($endDate)); ?></p>
    </div>
    <div class="page-header-actions">
        <button onclick="window.print()" class="btn-admin btn-outline-admin">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- ================= DATE RANGE FILTER ================= -->
<div class="admin-card" style="padding: 20px; margin-bottom: 25px;" data-aos="fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-calendar-alt" style="color: var(--primary); font-size: 18px;"></i>
            <strong style="font-size: 14px;">Date Range:</strong>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="?range=today" class="btn-admin <?php echo $range === 'today' ? 'btn-primary-admin' : 'btn-outline-admin'; ?>" style="padding: 8px 18px; font-size: 12px;">
                Today
            </a>
            <a href="?range=week" class="btn-admin <?php echo $range === 'week' ? 'btn-primary-admin' : 'btn-outline-admin'; ?>" style="padding: 8px 18px; font-size: 12px;">
                Last 7 Days
            </a>
            <a href="?range=month" class="btn-admin <?php echo $range === 'month' ? 'btn-primary-admin' : 'btn-outline-admin'; ?>" style="padding: 8px 18px; font-size: 12px;">
                Last 30 Days
            </a>
            <a href="?range=year" class="btn-admin <?php echo $range === 'year' ? 'btn-primary-admin' : 'btn-outline-admin'; ?>" style="padding: 8px 18px; font-size: 12px;">
                Last Year
            </a>
        </div>
    </div>
</div>

<!-- ================= SUMMARY STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    
    <div class="stat-admin-card stat-green" data-aos="fade-up">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-money-bill-wave"></i></div>
            <span class="stat-admin-trend <?php echo $salesGrowth >= 0 ? '' : 'down'; ?>">
                <i class="fas fa-<?php echo $salesGrowth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo number_format(abs($salesGrowth), 1); ?>%
            </span>
        </div>
        <div class="stat-admin-value" style="font-size: 22px;">Rs. <?php echo number_format($totalSales); ?></div>
        <div class="stat-admin-label">Total Sales</div>
    </div>
    
    <div class="stat-admin-card stat-blue" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-shopping-bag"></i></div>
            <span class="stat-admin-trend <?php echo $ordersGrowth >= 0 ? '' : 'down'; ?>">
                <i class="fas fa-<?php echo $ordersGrowth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo number_format(abs($ordersGrowth), 1); ?>%
            </span>
        </div>
        <div class="stat-admin-value"><?php echo $totalOrders; ?></div>
        <div class="stat-admin-label">Total Orders</div>
    </div>
    
    <div class="stat-admin-card stat-orange" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="stat-admin-value" style="font-size: 22px;">Rs. <?php echo number_format($avgOrder); ?></div>
        <div class="stat-admin-label">Avg Order Value</div>
    </div>
    
    <div class="stat-admin-card stat-gold" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top">
            <div class="stat-admin-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-admin-value"><?php echo $deliveredOrders; ?></div>
        <div class="stat-admin-label">Delivered Orders</div>
    </div>
    
</div>

<!-- ================= SALES TREND CHART ================= -->
<div class="admin-card" style="margin-bottom: 25px;" data-aos="fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-line" style="color: var(--primary);"></i> Sales Trend
            </h3>
            <p style="font-size: 12px; color: var(--gray); margin: 4px 0 0;">Daily sales over selected period</p>
        </div>
        <div style="display: flex; gap: 15px; font-size: 12px;">
            <span style="display: flex; align-items: center; gap: 6px;">
                <span style="width: 12px; height: 12px; background: #2E7D32; border-radius: 3px;"></span>
                Sales (Rs.)
            </span>
        </div>
    </div>
    <div style="position: relative; height: 320px;">
        <canvas id="salesTrendChart"></canvas>
    </div>
</div>

<!-- ================= CATEGORY + TOP PRODUCTS ================= -->
<div class="row g-4 mb-4">
    
    <!-- Category Sales -->
    <div class="col-lg-5" data-aos="fade-up">
        <div class="admin-card" style="height: 100%;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-pie" style="color: var(--accent);"></i> Sales by Category
            </h3>
            
            <?php if (empty($categorySales)): ?>
            <div class="admin-empty" style="padding: 40px 20px;">
                <i class="fas fa-chart-pie"></i>
                <h3>No sales data</h3>
                <p>No category sales in this period</p>
            </div>
            <?php else: ?>
            <div style="position: relative; height: 250px; margin-bottom: 20px;">
                <canvas id="categoryChart"></canvas>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                <?php foreach ($categorySales as $i => $cs): 
                    $colors = ['#2E7D32', '#FF6F00', '#2196F3', '#E53935', '#FFB300', '#7B1FA2', '#00ACC1', '#5D4037'];
                    $color = $colors[$i % count($colors)];
                ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: var(--cream); border-radius: 8px; font-size: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; background: <?php echo $color; ?>; border-radius: 3px;"></span>
                        <strong><?php echo htmlspecialchars($cs['category_name']); ?></strong>
                    </div>
                    <strong style="color: var(--primary-dark);">Rs. <?php echo number_format($cs['category_sales']); ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
        <div class="admin-card" style="height: 100%;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-trophy" style="color: var(--gold);"></i> Top 10 Best Sellers
            </h3>
            
            <?php if (empty($topProducts)): ?>
            <div class="admin-empty" style="padding: 40px 20px;">
                <i class="fas fa-box"></i>
                <h3>No product sales</h3>
                <p>No products sold in this period</p>
            </div>
            <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Product</th>
                            <th>Sold</th>
                            <th>Orders</th>
                            <th style="text-align: right;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $i => $tp): 
                            $rankColors = ['#FFB300', '#9E9E9E', '#8D6E63'];
                            $rankColor = $i < 3 ? $rankColors[$i] : 'var(--cream)';
                            $rankTextColor = $i < 3 ? 'white' : 'var(--dark)';
                        ?>
                        <tr>
                            <td>
                                <span style="width: 28px; height: 28px; background: <?php echo $rankColor; ?>; color: <?php echo $rankTextColor; ?>; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11px;">
                                    <?php echo $i + 1; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="<?php echo htmlspecialchars($tp['product_image']); ?>" alt="" style="width: 36px; height: 36px; border-radius: 8px; object-fit: cover;">
                                    <strong style="font-size: 12px;"><?php echo htmlspecialchars($tp['product_name']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span style="background: rgba(46,125,50,0.1); color: var(--primary-dark); padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 700;">
                                    <?php echo (int)$tp['total_qty']; ?> units
                                </span>
                            </td>
                            <td>
                                <span style="color: var(--gray);"><?php echo (int)$tp['order_count']; ?></span>
                            </td>
                            <td style="text-align: right;">
                                <strong style="color: var(--primary-dark);">Rs. <?php echo number_format($tp['total_revenue']); ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- ================= RECENT ORDERS ================= -->
<div class="admin-table-wrap" data-aos="fade-up">
    <div class="admin-table-header">
        <h3><i class="fas fa-clock"></i> Recent Orders in Period</h3>
        <a href="orders.php" class="btn-admin btn-outline-admin" style="padding: 7px 14px; font-size: 12px;">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <?php if (empty($recentOrders)): ?>
    <div class="admin-empty" style="padding: 40px 20px;">
        <i class="fas fa-shopping-bag"></i>
        <h3>No orders in this period</h3>
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
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): 
                    $statusColor = [
                        'pending' => '#FFB300',
                        'confirmed' => '#2196F3',
                        'delivered' => '#2E7D32',
                        'cancelled' => '#E53935',
                    ][$o['status']] ?? '#616161';
                ?>
                <tr>
                    <td>
                        <strong style="color: var(--primary-dark); font-size: 12px;">
                            <?php echo htmlspecialchars($o['order_number']); ?>
                        </strong>
                    </td>
                    <td>
                        <div class="table-product-info">
                            <strong><?php echo htmlspecialchars($o['customer_name']); ?></strong>
                            <span><?php echo htmlspecialchars($o['customer_phone']); ?></span>
                        </div>
                    </td>
                    <td>
                        <strong style="color: var(--primary-dark);">Rs. <?php echo number_format($o['total']); ?></strong>
                    </td>
                    <td>
                        <span style="background: <?php echo $statusColor; ?>; color: white; padding: 3px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                            <?php echo htmlspecialchars($o['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: var(--gray);">
                            <?php echo date('d M, h:i A', strtotime($o['created_at'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="order-detail.php?id=<?php echo (int)$o['id']; ?>" class="table-action-btn view">
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

<!-- ================= CHART.JS ================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ============ SALES TREND CHART ============
    const salesCtx = document.getElementById('salesTrendChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Sales (Rs.)',
                    data: <?php echo json_encode($chartSales); ?>,
                    borderColor: '#2E7D32',
                    backgroundColor: 'rgba(46,125,50,0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2E7D32',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
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
                                return 'Sales: Rs. ' + context.parsed.y.toLocaleString();
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
                            font: { family: 'Poppins', size: 10 },
                            color: '#616161',
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }
    
    // ============ CATEGORY CHART ============
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($catSales); ?>,
                    backgroundColor: ['#2E7D32', '#FF6F00', '#2196F3', '#E53935', '#FFB300', '#7B1FA2', '#00ACC1', '#5D4037'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 10,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1A1A1A',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Poppins', size: 12, weight: '700' },
                        bodyFont: { family: 'Poppins', size: 12 },
                        callbacks: {
                            label: function(context) {
                                return context.label + ': Rs. ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>