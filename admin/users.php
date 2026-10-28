<?php
// ============================================
// admin/users.php - Customers Management
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ HANDLE DELETE ============
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    // Check karo user ke orders hain ya nahi
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?");
    $stmt->execute([$deleteId]);
    $orderCount = (int)$stmt->fetch()['cnt'];
    
    if ($orderCount > 0) {
        $_SESSION['admin_flash'] = [
            'type' => 'error',
            'msg' => "Cannot delete! This customer has {$orderCount} orders."
        ];
    } else {
        // Sirf user role delete karo (admin/manager nahi)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$deleteId]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Customer deleted successfully'];
        } else {
            $_SESSION['admin_flash'] = ['type' => 'error', 'msg' => 'Cannot delete admin or manager'];
        }
    }
    
    header('Location: users.php');
    exit;
}

// ============ FILTERS ============
$searchQuery  = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFilter   = isset($_GET['date']) ? $_GET['date'] : '';
$sortBy       = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// ============ PAGINATION ============
$perPage = 10;
$currentPage = 1;
if (isset($_GET['page']) && $_GET['page'] !== '' && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
    if ($currentPage < 1) $currentPage = 1;
}

// ============ BUILD WHERE ============
$where = "WHERE u.role = 'user'";
$params = [];

if ($searchQuery !== '') {
    $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.city LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

if ($dateFilter === 'today') {
    $where .= " AND DATE(u.created_at) = CURDATE()";
} elseif ($dateFilter === 'week') {
    $where .= " AND DATE(u.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dateFilter === 'month') {
    $where .= " AND DATE(u.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

// ============ SORT ============
$orderBy = "ORDER BY u.id DESC";
if ($sortBy === 'oldest') {
    $orderBy = "ORDER BY u.id ASC";
} elseif ($sortBy === 'name') {
    $orderBy = "ORDER BY u.name ASC";
} elseif ($sortBy === 'orders') {
    $orderBy = "ORDER BY order_count DESC";
}

// ============ COUNT ============
$countSql = "SELECT COUNT(*) AS cnt FROM users u $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalUsers = (int)$stmt->fetch()['cnt'];

$totalPages = (int)ceil($totalUsers / $perPage);
if ($totalPages < 1) $totalPages = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

// ============ FETCH USERS ============
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
        (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id AND status != 'cancelled') AS total_spent
        FROM users u 
        $where 
        $orderBy 
        LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// ============ MINI STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user'");
$statTotal = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user' AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$statNew = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$statWeek = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) AS cnt FROM orders WHERE user_id IS NOT NULL");
$statActive = (int)$stmt->fetch()['cnt'];

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ PAGE VARIABLES ============
$pageTitle = 'Customers';
$pageSubtitle = 'Manage registered customers';

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
        <h1>Customers Management</h1>
        <p>Total <?php echo $totalUsers; ?> customer<?php echo $totalUsers != 1 ? 's' : ''; ?> found</p>
    </div>
</div>

<!-- ================= MINI STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    <div class="stat-admin-card stat-green" data-aos="fade-up">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-admin-value"><?php echo $statTotal; ?></div>
        <div class="stat-admin-label">Total Customers</div>
    </div>
    <div class="stat-admin-card stat-blue" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-user-check"></i></div></div>
        <div class="stat-admin-value"><?php echo $statActive; ?></div>
        <div class="stat-admin-label">Active Buyers</div>
    </div>
    <div class="stat-admin-card stat-orange" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-user-plus"></i></div></div>
        <div class="stat-admin-value"><?php echo $statWeek; ?></div>
        <div class="stat-admin-label">New This Week</div>
    </div>
    <div class="stat-admin-card stat-gold" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-calendar"></i></div></div>
        <div class="stat-admin-value"><?php echo $statNew; ?></div>
        <div class="stat-admin-label">Last 30 Days</div>
    </div>
</div>

<!-- ================= FILTER BAR ================= -->
<div class="admin-card" style="padding: 20px; margin-bottom: 25px;" data-aos="fade-up">
    <form method="GET" action="users.php" id="filterForm" style="display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:12px; align-items:end;">

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Search</label>
            <input type="text" name="search" placeholder="Name, email, phone, city..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Date Joined</label>
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
                <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                <option value="orders" <?php echo $sortBy === 'orders' ? 'selected' : ''; ?>>Most Orders</option>
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-admin btn-primary-admin" style="padding:12px 18px;">
                <i class="fas fa-filter"></i>
            </button>
            <a href="users.php" class="btn-admin btn-outline-admin" style="padding:12px 18px;">
                <i class="fas fa-redo"></i>
            </a>
        </div>

    </form>
</div>

<!-- ================= USERS TABLE ================= -->
<div class="admin-table-wrap" data-aos="fade-up">

    <div class="admin-table-header">
        <h3><i class="fas fa-users"></i> All Customers</h3>
        <div style="font-size:12px; color:var(--gray);">
            Showing <strong><?php echo count($users); ?></strong> of <strong><?php echo $totalUsers; ?></strong>
        </div>
    </div>

    <?php if (empty($users)): ?>
    
    <div class="admin-empty">
        <i class="fas fa-users"></i>
        <h3>No customers found</h3>
        <p>Try adjusting filters</p>
    </div>
    
    <?php else: ?>
    
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>City</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): 
                    $initial = strtoupper(substr($u['name'], 0, 1));
                    $colors = ['#2E7D32', '#FF6F00', '#2196F3', '#E53935', '#7B1FA2'];
                    $avatarColor = $colors[$u['id'] % count($colors)];
                ?>
                <tr>
                    <td>
                        <div class="table-product">
                            <div style="width:50px; height:50px; background:<?php echo $avatarColor; ?>; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:20px; flex-shrink:0;">
                                <?php echo $initial; ?>
                            </div>
                            <div class="table-product-info">
                                <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                                <span>ID: #<?php echo str_pad($u['id'], 4, '0', STR_PAD_LEFT); ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px;">
                            <div style="margin-bottom:3px;">
                                <i class="fas fa-envelope" style="color:var(--primary); font-size:11px; margin-right:5px;"></i>
                                <?php echo htmlspecialchars($u['email']); ?>
                            </div>
                            <div style="color:var(--gray);">
                                <i class="fas fa-phone" style="font-size:11px; margin-right:5px;"></i>
                                <?php echo htmlspecialchars($u['phone']); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($u['city'])): ?>
                        <span style="background:var(--cream); padding:4px 12px; border-radius:50px; font-size:11px; font-weight:600;">
                            <i class="fas fa-map-marker-alt" style="color:var(--accent); font-size:10px;"></i>
                            <?php echo htmlspecialchars($u['city']); ?>
                        </span>
                        <?php else: ?>
                        <span style="color:var(--gray); font-size:11px;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['order_count'] > 0): ?>
                        <a href="orders.php?search=<?php echo urlencode($u['email']); ?>" style="background:rgba(46,125,50,0.1); color:var(--primary-dark); padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700; text-decoration:none;">
                            <?php echo (int)$u['order_count']; ?> orders
                        </a>
                        <?php else: ?>
                        <span style="background:rgba(97,97,97,0.1); color:var(--gray); padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700;">
                            0 orders
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color:var(--primary-dark); font-size:14px;">
                            Rs. <?php echo number_format($u['total_spent']); ?>
                        </strong>
                    </td>
                    <td>
                        <span style="font-size:12px; color:var(--gray);">
                            <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content: flex-end;">
                            <a href="orders.php?search=<?php echo urlencode($u['email']); ?>" class="table-action-btn view" title="View Orders">
                                <i class="fas fa-shopping-bag"></i>
                            </a>
                            <a href="javascript:void(0)" onclick="confirmDeleteUser(<?php echo (int)$u['id']; ?>, '<?php echo htmlspecialchars(addslashes($u['name'])); ?>', <?php echo (int)$u['order_count']; ?>)" class="table-action-btn delete" title="Delete">
                                <i class="fas fa-trash"></i>
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
        $tp = (int)$totalUsers;
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

// ============ DELETE CONFIRM ============
function confirmDeleteUser(id, name, orderCount) {
    if (orderCount > 0) {
        alert('⚠️ Cannot delete "' + name + '"\n\nThis customer has ' + orderCount + ' orders.\n\nCustomers with orders cannot be deleted for record keeping.');
        return;
    }
    if (confirm('Are you sure you want to delete customer "' + name + '"?\n\nThis action cannot be undone.')) {
        window.location.href = 'users.php?delete=' + id;
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>