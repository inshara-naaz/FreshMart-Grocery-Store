<?php
// ============================================
// admin/profile.php - Admin Profile
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ FETCH ADMIN DATA ============
$adminId = (int)$_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'manager')");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'msg' => 'Admin not found'];
    header('Location: logout.php');
    exit;
}

// ============ HANDLE PROFILE UPDATE ============
$profileErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $city  = trim($_POST['city'] ?? '');
    
    // Validation
    if (empty($name) || strlen($name) < 3) {
        $profileErrors['name'] = 'Name must be at least 3 characters';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileErrors['email'] = 'Please enter a valid email address';
    }
    if (empty($phone)) {
        $profileErrors['phone'] = 'Phone number is required';
    }
    
    // Check email unique (except this admin)
    if (empty($profileErrors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $adminId]);
        if ($stmt->fetch()) {
            $profileErrors['email'] = 'This email is already registered';
        }
    }
    
    // Update
    if (empty($profileErrors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, city = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $city, $adminId]);
            
            // Update session
            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;
            
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
            header('Location: profile.php');
            exit;
        } catch (Exception $e) {
            $profileErrors['general'] = 'Failed: ' . $e->getMessage();
        }
    }
    
    // Update $admin for display
    $admin['name'] = $name;
    $admin['email'] = $email;
    $admin['phone'] = $phone;
    $admin['city'] = $city;
}

// ============ HANDLE PASSWORD CHANGE ============
$passwordErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($currentPassword)) {
        $passwordErrors['current_password'] = 'Please enter your current password';
    }
    if (empty($newPassword) || strlen($newPassword) < 6) {
        $passwordErrors['new_password'] = 'New password must be at least 6 characters';
    }
    if ($newPassword !== $confirmPassword) {
        $passwordErrors['confirm_password'] = 'Passwords do not match';
    }
    if ($currentPassword === $newPassword) {
        $passwordErrors['new_password'] = 'New password must be different from current';
    }
    
    // Verify current password
    if (empty($passwordErrors['current_password'])) {
        if (!password_verify($currentPassword, $admin['password'])) {
            $passwordErrors['current_password'] = 'Current password is incorrect';
        }
    }
    
    // Update password
    if (empty($passwordErrors)) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $adminId]);
            
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Password changed successfully!'];
            header('Location: profile.php');
            exit;
        } catch (Exception $e) {
            $passwordErrors['general'] = 'Failed: ' . $e->getMessage();
        }
    }
}

// ============ STATS (Admin Activity) ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders");
$totalOrders = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products");
$totalProducts = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user'");
$totalUsers = (int)$stmt->fetch()['cnt'];

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ PAGE VARIABLES ============
$pageTitle = 'My Profile';
$pageSubtitle = 'Manage your account settings';

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
        <h1>My Profile</h1>
        <p>Manage your account settings and password</p>
    </div>
</div>

<div class="row g-4">
    
    <!-- ================= LEFT: PROFILE CARD ================= -->
    <div class="col-lg-4">
        
        <!-- Profile Card -->
        <div class="admin-card" style="text-align: center; padding: 35px 25px; margin-bottom: 20px;" data-aos="fade-up">
            
            <!-- Avatar -->
            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 42px; font-weight: 800; margin: 0 auto 20px; box-shadow: 0 12px 30px rgba(46,125,50,0.3);">
                <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
            </div>
            
            <!-- Name -->
            <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 5px; color: var(--dark);">
                <?php echo htmlspecialchars($admin['name']); ?>
            </h2>
            
            <!-- Role Badge -->
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(46,125,50,0.1); color: var(--primary-dark); padding: 5px 16px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
                <i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($admin['role']); ?>
            </span>
            
            <!-- Contact Info -->
            <div style="border-top: 1px dashed var(--border); padding-top: 20px; text-align: left;">
                
                <div style="display: flex; gap: 12px; align-items: center; padding: 10px 0; font-size: 13px;">
                    <div style="width: 36px; height: 36px; background: var(--cream); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 10px; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Email</div>
                        <div style="font-size: 12px; color: var(--dark); word-break: break-all;"><?php echo htmlspecialchars($admin['email']); ?></div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; align-items: center; padding: 10px 0; font-size: 13px;">
                    <div style="width: 36px; height: 36px; background: var(--cream); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 10px; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Phone</div>
                        <div style="font-size: 12px; color: var(--dark);"><?php echo htmlspecialchars($admin['phone']); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($admin['city'])): ?>
                <div style="display: flex; gap: 12px; align-items: center; padding: 10px 0; font-size: 13px;">
                    <div style="width: 36px; height: 36px; background: var(--cream); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 10px; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">City</div>
                        <div style="font-size: 12px; color: var(--dark);"><?php echo htmlspecialchars($admin['city']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; gap: 12px; align-items: center; padding: 10px 0; font-size: 13px;">
                    <div style="width: 36px; height: 36px; background: var(--cream); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 10px; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Joined</div>
                        <div style="font-size: 12px; color: var(--dark);"><?php echo date('d M Y', strtotime($admin['created_at'])); ?></div>
                    </div>
                </div>
                
            </div>
            
        </div>
        
        <!-- Store Stats -->
        <div class="admin-card" data-aos="fade-up" data-aos-delay="100">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-line" style="color: var(--primary);"></i> Store Overview
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--border);">
                    <span style="font-size: 12px; color: var(--gray);">
                        <i class="fas fa-shopping-bag" style="color: var(--primary);"></i> Total Orders
                    </span>
                    <strong style="font-size: 14px; color: var(--dark);"><?php echo $totalOrders; ?></strong>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--border);">
                    <span style="font-size: 12px; color: var(--gray);">
                        <i class="fas fa-box" style="color: var(--accent);"></i> Total Products
                    </span>
                    <strong style="font-size: 14px; color: var(--dark);"><?php echo $totalProducts; ?></strong>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
                    <span style="font-size: 12px; color: var(--gray);">
                        <i class="fas fa-users" style="color: var(--info);"></i> Total Customers
                    </span>
                    <strong style="font-size: 14px; color: var(--dark);"><?php echo $totalUsers; ?></strong>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- ================= RIGHT: FORMS ================= -->
    <div class="col-lg-8">
        
        <!-- Update Profile Form -->
        <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-user-edit" style="color: var(--primary);"></i> Update Profile
            </h3>
            
            <?php if (isset($profileErrors['general'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 13px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($profileErrors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="profile.php">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Full Name <span class="req">*</span></label>
                            <input type="text" name="name" placeholder="Admin" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            <?php if (isset($profileErrors['name'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $profileErrors['name']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Email Address <span class="req">*</span></label>
                            <input type="email" name="email" placeholder="admin@freshmart.com" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            <?php if (isset($profileErrors['email'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $profileErrors['email']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Phone Number <span class="req">*</span></label>
                            <input type="tel" name="phone" placeholder="+92 300 1234567" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
                            <?php if (isset($profileErrors['phone'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $profileErrors['phone']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>City</label>
                            <input type="text" name="city" placeholder="Lahore" value="<?php echo htmlspecialchars($admin['city'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn-admin btn-primary-admin" style="padding: 12px 30px;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
                
            </form>
        </div>
        
        <!-- Change Password Form -->
        <div class="admin-card" data-aos="fade-up" data-aos-delay="100">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-lock" style="color: var(--accent);"></i> Change Password
            </h3>
            
            <?php if (isset($passwordErrors['general'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 13px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($passwordErrors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="profile.php">
                <input type="hidden" name="change_password" value="1">
                
                <div class="admin-form-group">
                    <label>Current Password <span class="req">*</span></label>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                    <?php if (isset($passwordErrors['current_password'])): ?>
                    <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $passwordErrors['current_password']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>New Password <span class="req">*</span></label>
                            <input type="password" name="new_password" placeholder="Min. 6 characters" required>
                            <?php if (isset($passwordErrors['new_password'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $passwordErrors['new_password']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Confirm Password <span class="req">*</span></label>
                            <input type="password" name="confirm_password" placeholder="Re-enter new password" required>
                            <?php if (isset($passwordErrors['confirm_password'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $passwordErrors['confirm_password']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div style="background: rgba(255,179,0,0.1); border-left: 3px solid var(--gold); padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="font-size: 12px; color: #B26A00; line-height: 1.7;">
                        <strong><i class="fas fa-info-circle"></i> Password Tips:</strong><br>
                        • Use at least 6 characters<br>
                        • Mix letters, numbers, and symbols<br>
                        • Don't reuse old passwords
                    </div>
                </div>
                
                <button type="submit" class="btn-admin btn-primary-admin" style="padding: 12px 30px;">
                    <i class="fas fa-key"></i> Change Password
                </button>
                
            </form>
        </div>
        
    </div>
    
</div>

<?php include 'includes/admin-footer.php'; ?>