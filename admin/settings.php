<?php
// ============================================
// admin/settings.php - Store Settings
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';
include '../includes/settings-functions.php';

// ============ HANDLE FORM SUBMIT ============
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'store_name' => trim($_POST['store_name'] ?? ''),
        'store_tagline' => trim($_POST['store_tagline'] ?? ''),
        'store_email' => trim($_POST['store_email'] ?? ''),
        'store_phone' => trim($_POST['store_phone'] ?? ''),
        'store_address' => trim($_POST['store_address'] ?? ''),
        'free_delivery_threshold' => (float)($_POST['free_delivery_threshold'] ?? 0),
        'delivery_fee' => (float)($_POST['delivery_fee'] ?? 0),
        'facebook_url' => trim($_POST['facebook_url'] ?? ''),
        'instagram_url' => trim($_POST['instagram_url'] ?? ''),
        'twitter_url' => trim($_POST['twitter_url'] ?? ''),
        'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
        'youtube_url' => trim($_POST['youtube_url'] ?? ''),
    ];
    
    // Validation
    if (empty($fields['store_name'])) {
        $errors['store_name'] = 'Store name is required';
    }
    if (!empty($fields['store_email']) && !filter_var($fields['store_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['store_email'] = 'Invalid email address';
    }
    
    if (empty($errors)) {
        try {
            foreach ($fields as $key => $value) {
                updateSetting($pdo, $key, $value);
            }
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Settings saved successfully!'];
            header('Location: settings.php');
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Failed: ' . $e->getMessage();
        }
    }
}

// ============ FETCH SETTINGS ============
$settings = getSettings($pdo);

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ PAGE VARIABLES ============
$pageTitle = 'Store Settings';
$pageSubtitle = 'Configure your store';

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
        <h1>Store Settings</h1>
        <p>Manage your store information, delivery, and social links</p>
    </div>
</div>

<form method="POST" action="settings.php">
    <div class="row g-4">
        
        <!-- ================= LEFT: STORE INFO + DELIVERY ================= -->
        <div class="col-lg-7">
            
            <!-- Store Info -->
            <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-store" style="color: var(--primary);"></i> Store Information
                </h3>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Store Name <span class="req">*</span></label>
                            <input type="text" name="store_name" value="<?php echo htmlspecialchars($settings['store_name'] ?? 'FreshMart'); ?>" required>
                            <?php if (isset($errors['store_name'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['store_name']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Store Tagline</label>
                            <input type="text" name="store_tagline" value="<?php echo htmlspecialchars($settings['store_tagline'] ?? 'Organic & Fresh'); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Store Email</label>
                            <input type="email" name="store_email" value="<?php echo htmlspecialchars($settings['store_email'] ?? ''); ?>">
                            <?php if (isset($errors['store_email'])): ?>
                            <span style="color: var(--danger); font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors['store_email']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Store Phone</label>
                            <input type="text" name="store_phone" value="<?php echo htmlspecialchars($settings['store_phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="admin-form-group">
                            <label>Store Address</label>
                            <textarea name="store_address" rows="2"><?php echo htmlspecialchars($settings['store_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Settings -->
            <div class="admin-card" data-aos="fade-up" data-aos-delay="100">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-truck" style="color: var(--accent);"></i> Delivery Settings
                </h3>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Free Delivery Threshold (Rs.)</label>
                            <input type="number" name="free_delivery_threshold" value="<?php echo (float)($settings['free_delivery_threshold'] ?? 1500); ?>" min="0" step="1">
                            <small style="color: var(--gray); font-size: 11px; margin-top: 4px; display: block;">
                                <i class="fas fa-info-circle"></i> Orders above this amount = free delivery
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Delivery Fee (Rs.)</label>
                            <input type="number" name="delivery_fee" value="<?php echo (float)($settings['delivery_fee'] ?? 150); ?>" min="0" step="1">
                            <small style="color: var(--gray); font-size: 11px; margin-top: 4px; display: block;">
                                <i class="fas fa-info-circle"></i> Fee for orders below threshold
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- ================= RIGHT: SOCIAL + SAVE ================= -->
        <div class="col-lg-5">
            
            <!-- Social Media -->
            <div class="admin-card" style="margin-bottom: 20px;" data-aos="fade-up">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-share-alt" style="color: var(--info);"></i> Social Media Links
                </h3>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <i class="fab fa-facebook" style="color: #1877F2; font-size: 16px;"></i> Facebook URL
                    </label>
                    <input type="url" name="facebook_url" placeholder="https://facebook.com/yourpage" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                </div>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <i class="fab fa-instagram" style="color: #E4405F; font-size: 16px;"></i> Instagram URL
                    </label>
                    <input type="url" name="instagram_url" placeholder="https://instagram.com/yourpage" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                </div>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <i class="fab fa-twitter" style="color: #1DA1F2; font-size: 16px;"></i> Twitter URL
                    </label>
                    <input type="url" name="twitter_url" placeholder="https://twitter.com/yourpage" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                </div>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <i class="fab fa-whatsapp" style="color: #25D366; font-size: 16px;"></i> WhatsApp Number
                    </label>
                    <input type="text" name="whatsapp_number" placeholder="923001234567" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
                    <small style="color: var(--gray); font-size: 11px; margin-top: 4px; display: block;">
                        <i class="fas fa-info-circle"></i> Format: Country code + number (no + or spaces)
                    </small>
                </div>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <i class="fab fa-youtube" style="color: #FF0000; font-size: 16px;"></i> YouTube URL
                    </label>
                    <input type="url" name="youtube_url" placeholder="https://youtube.com/yourchannel" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Save Button Card -->
            <div class="admin-card" data-aos="fade-up" data-aos-delay="100">
                <div style="background: linear-gradient(135deg, rgba(46,125,50,0.05), rgba(255,111,0,0.05)); border-left: 3px solid var(--primary); padding: 15px 18px; border-radius: 10px; margin-bottom: 15px;">
                    <div style="font-size: 12px; color: var(--gray); line-height: 1.7;">
                        <strong style="color: var(--dark); display: block; margin-bottom: 5px;">
                            <i class="fas fa-info-circle" style="color: var(--primary);"></i> Note
                        </strong>
                        Ye settings <strong>header, footer, contact page</strong> mein use honge. Change karne ke baad **foran** apply ho jayenge.
                    </div>
                </div>
                
                <button type="submit" class="btn-admin btn-primary-admin" style="width: 100%; justify-content: center; padding: 14px; font-size: 14px;">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
            </div>
            
        </div>
        
    </div>
</form>

<?php include 'includes/admin-footer.php'; ?>