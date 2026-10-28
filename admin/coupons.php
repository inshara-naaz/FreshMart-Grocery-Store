<?php
// ============================================
// admin/coupons.php - Coupons Management
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
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$deleteId]);
    $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Coupon deleted successfully'];
    header('Location: coupons.php');
    exit;
}

// ============ HANDLE TOGGLE ACTIVE ============
if (isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE coupons SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$toggleId]);
    $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Coupon status updated'];
    header('Location: coupons.php');
    exit;
}

// ============ HANDLE ADD/EDIT ============
$errors = [];
$editMode = false;
$editCoupon = [
    'id' => 0,
    'code' => '',
    'discount_type' => 'percent',
    'discount_value' => '',
    'min_order' => 0,
    'expiry_date' => '',
    'is_active' => 1,
];

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$editId]);
    $editCoupon = $stmt->fetch();
    
    if ($editCoupon) {
        $editMode = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $couponId      = (int)($_POST['id'] ?? 0);
    $code          = strtoupper(trim($_POST['code'] ?? ''));
    $discountType  = $_POST['discount_type'] ?? 'percent';
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $minOrder      = (float)($_POST['min_order'] ?? 0);
    $expiryDate    = trim($_POST['expiry_date'] ?? '');
    $isActive      = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($code) || strlen($code) < 3) {
        $errors['code'] = 'Coupon code must be at least 3 characters';
    }
    if (!preg_match('/^[A-Z0-9]+$/', $code)) {
        $errors['code'] = 'Only uppercase letters and numbers allowed';
    }
    if ($discountValue <= 0) {
        $errors['discount_value'] = 'Discount value must be greater than 0';
    }
    if ($discountType === 'percent' && $discountValue > 100) {
        $errors['discount_value'] = 'Percentage cannot exceed 100';
    }
    
    // Check unique code
    if (empty($errors['code'])) {
        if ($couponId > 0) {
            $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
            $stmt->execute([$code, $couponId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
        }
        if ($stmt->fetch()) {
            $errors['code'] = 'This coupon code already exists';
        }
    }
    
    // Insert/Update
    if (empty($errors)) {
        try {
            $expiryValue = !empty($expiryDate) ? $expiryDate : null;
            
            if ($couponId > 0) {
                $stmt = $pdo->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, min_order = ?, expiry_date = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$code, $discountType, $discountValue, $minOrder, $expiryValue, $isActive, $couponId]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Coupon updated successfully!'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $discountType, $discountValue, $minOrder, $expiryValue, $isActive]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Coupon added successfully!'];
            }
            header('Location: coupons.php');
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Failed: ' . $e->getMessage();
        }
    }
    
    // Show old values
    $editCoupon = [
        'id' => $couponId,
        'code' => $code,
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
        'min_order' => $minOrder,
        'expiry_date' => $expiryDate,
        'is_active' => $isActive,
    ];
    $editMode = ($couponId > 0);
}

// ============ FETCH COUPONS ============
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY is_active DESC, id DESC");
$coupons = $stmt->fetchAll();

// ============ MINI STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM coupons");
$statTotal = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM coupons WHERE is_active = 1");
$statActive = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM coupons WHERE is_active = 0");
$statInactive = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM coupons WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()");
$statExpired = (int)$stmt->fetch()['cnt'];

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ PAGE VARIABLES ============
$pageTitle = 'Coupons';
$pageSubtitle = 'Manage discount coupons';

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
        <h1>Coupons Management</h1>
        <p>Total <?php echo $statTotal; ?> coupon<?php echo $statTotal != 1 ? 's' : ''; ?></p>
    </div>
</div>

<!-- ================= MINI STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    <div class="stat-admin-card stat-green" data-aos="fade-up">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-ticket-alt"></i></div></div>
        <div class="stat-admin-value"><?php echo $statTotal; ?></div>
        <div class="stat-admin-label">Total Coupons</div>
    </div>
    <div class="stat-admin-card stat-blue" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-check-circle"></i></div></div>
        <div class="stat-admin-value"><?php echo $statActive; ?></div>
        <div class="stat-admin-label">Active</div>
    </div>
    <div class="stat-admin-card stat-orange" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-pause-circle"></i></div></div>
        <div class="stat-admin-value"><?php echo $statInactive; ?></div>
        <div class="stat-admin-label">Inactive</div>
    </div>
    <div class="stat-admin-card stat-red" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-calendar-times"></i></div></div>
        <div class="stat-admin-value"><?php echo $statExpired; ?></div>
        <div class="stat-admin-label">Expired</div>
    </div>
</div>

<div class="row g-4">
    
    <!-- ================= LEFT: FORM ================= -->
    <div class="col-lg-4">
        <div class="admin-card" style="position:sticky; top:90px;" data-aos="fade-up">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                <i class="fas fa-<?php echo $editMode ? 'pen' : 'plus-circle'; ?>" style="color:var(--primary);"></i>
                <?php echo $editMode ? 'Edit Coupon' : 'Add New Coupon'; ?>
            </h3>
            
            <?php if (isset($errors['general'])): ?>
            <div style="background:#f8d7da; color:#721c24; padding:12px 16px; border-radius:10px; margin-bottom:15px; font-size:13px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="coupons.php">
                <input type="hidden" name="id" value="<?php echo (int)$editCoupon['id']; ?>">
                
                <div class="admin-form-group">
                    <label>Coupon Code <span class="req">*</span></label>
                    <input type="text" name="code" placeholder="FRESH30" value="<?php echo htmlspecialchars($editCoupon['code']); ?>" style="text-transform: uppercase; font-weight: 700; letter-spacing: 2px;" required>
                    <small style="color:var(--gray); font-size:11px; margin-top:4px; display:block;">
                        <i class="fas fa-info-circle"></i> Only UPPERCASE letters and numbers
                    </small>
                    <?php if (isset($errors['code'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['code']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="row g-2">
                    <div class="col-6">
                        <div class="admin-form-group">
                            <label>Type <span class="req">*</span></label>
                            <select name="discount_type" id="discountType" required>
                                <option value="percent" <?php echo $editCoupon['discount_type'] === 'percent' ? 'selected' : ''; ?>>Percent (%)</option>
                                <option value="fixed" <?php echo $editCoupon['discount_type'] === 'fixed' ? 'selected' : ''; ?>>Fixed (Rs.)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="admin-form-group">
                            <label>Value <span class="req">*</span></label>
                            <input type="number" name="discount_value" placeholder="30" value="<?php echo $editCoupon['discount_value']; ?>" min="1" step="0.01" required>
                        </div>
                    </div>
                </div>
                <?php if (isset($errors['discount_value'])): ?>
                <span style="color:var(--danger); font-size:12px; margin-top:-10px; margin-bottom:10px; display:block;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errors['discount_value']; ?>
                </span>
                <?php endif; ?>
                
                <div class="admin-form-group">
                    <label>Minimum Order (Rs.)</label>
                    <input type="number" name="min_order" placeholder="500" value="<?php echo $editCoupon['min_order']; ?>" min="0" step="1">
                    <small style="color:var(--gray); font-size:11px; margin-top:4px; display:block;">
                        <i class="fas fa-info-circle"></i> 0 = No minimum
                    </small>
                </div>
                
                <div class="admin-form-group">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" value="<?php echo htmlspecialchars($editCoupon['expiry_date'] ?? ''); ?>">
                    <small style="color:var(--gray); font-size:11px; margin-top:4px; display:block;">
                        <i class="fas fa-info-circle"></i> Leave empty for no expiry
                    </small>
                </div>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo $editCoupon['is_active'] ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                        <span>Active (Available for customers)</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-admin btn-primary-admin" style="width:100%; justify-content:center; padding:14px;">
                    <i class="fas fa-<?php echo $editMode ? 'save' : 'plus'; ?>"></i>
                    <?php echo $editMode ? 'Update Coupon' : 'Add Coupon'; ?>
                </button>
                
                <?php if ($editMode): ?>
                <a href="coupons.php" class="btn-admin btn-outline-admin" style="width:100%; justify-content:center; margin-top:10px;">
                    <i class="fas fa-times"></i> Cancel Edit
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- ================= RIGHT: TABLE ================= -->
    <div class="col-lg-8">
        <div class="admin-table-wrap" data-aos="fade-up" data-aos-delay="100">
            
            <div class="admin-table-header">
                <h3><i class="fas fa-ticket-alt"></i> All Coupons</h3>
                <div style="font-size:12px; color:var(--gray);">
                    <?php echo count($coupons); ?> coupon<?php echo count($coupons) != 1 ? 's' : ''; ?>
                </div>
            </div>
            
            <?php if (empty($coupons)): ?>
            
            <div class="admin-empty">
                <i class="fas fa-ticket-alt"></i>
                <h3>No coupons yet</h3>
                <p>Add your first coupon</p>
            </div>
            
            <?php else: ?>
            
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Min Order</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): 
                            $isExpired = !empty($c['expiry_date']) && $c['expiry_date'] < date('Y-m-d');
                            $statusColor = $isExpired ? '#E53935' : ($c['is_active'] ? '#2E7D32' : '#616161');
                            $statusText = $isExpired ? 'Expired' : ($c['is_active'] ? 'Active' : 'Inactive');
                        ?>
                        <tr>
                            <td>
                                <div style="background: linear-gradient(135deg, #FFF8E1, #FFECB3); border: 2px dashed #FFB300; padding: 10px 16px; border-radius: 12px; display: inline-block;">
                                    <code style="font-size: 14px; font-weight: 800; letter-spacing: 2px; color: #1B5E20;">
                                        <?php echo htmlspecialchars($c['code']); ?>
                                    </code>
                                </div>
                            </td>
                            <td>
                                <?php if ($c['discount_type'] === 'percent'): ?>
                                <strong style="color:var(--primary-dark); font-size:16px;">
                                    <?php echo $c['discount_value']; ?>% OFF
                                </strong>
                                <?php else: ?>
                                <strong style="color:var(--primary-dark); font-size:16px;">
                                    Rs. <?php echo number_format($c['discount_value']); ?> OFF
                                </strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['min_order'] > 0): ?>
                                <span style="font-size:12px;">Rs. <?php echo number_format($c['min_order']); ?></span>
                                <?php else: ?>
                                <span style="font-size:12px; color:var(--gray);">No minimum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($c['expiry_date'])): ?>
                                <span style="font-size:12px; color: <?php echo $isExpired ? 'var(--danger)' : 'var(--dark)'; ?>;">
                                    <?php echo date('d M Y', strtotime($c['expiry_date'])); ?>
                                </span>
                                <?php else: ?>
                                <span style="font-size:12px; color:var(--gray);">No expiry</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="background: <?php echo $statusColor; ?>; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700;">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="coupons.php?toggle=<?php echo (int)$c['id']; ?>" class="table-action-btn <?php echo $c['is_active'] ? 'stock' : 'view'; ?>" title="<?php echo $c['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-<?php echo $c['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </a>
                                    <a href="coupons.php?edit=<?php echo (int)$c['id']; ?>" class="table-action-btn edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDeleteCoupon(<?php echo (int)$c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['code'])); ?>')" class="table-action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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

<script>
// ============ DELETE CONFIRM ============
function confirmDeleteCoupon(id, code) {
    if (confirm('Are you sure you want to delete coupon "' + code + '"?\n\nThis action cannot be undone.')) {
        window.location.href = 'coupons.php?delete=' + id;
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>