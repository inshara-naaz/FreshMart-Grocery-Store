<?php
// ============================================
// admin/faqs.php - FAQs Management
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
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$deleteId]);
    $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'FAQ deleted successfully'];
    header('Location: faqs.php');
    exit;
}

// ============ HANDLE TOGGLE ACTIVE ============
if (isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE faqs SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$toggleId]);
    $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'FAQ status updated'];
    header('Location: faqs.php');
    exit;
}

// ============ HANDLE ADD/EDIT ============
$errors = [];
$editMode = false;
$editFaq = [
    'id' => 0,
    'question' => '',
    'answer' => '',
    'category' => 'general',
    'sort_order' => 0,
    'is_active' => 1,
];

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$editId]);
    $editFaq = $stmt->fetch();
    
    if ($editFaq) {
        $editMode = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faqId     = (int)($_POST['id'] ?? 0);
    $question  = trim($_POST['question'] ?? '');
    $answer    = trim($_POST['answer'] ?? '');
    $category  = trim($_POST['category'] ?? 'general');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive  = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($question) || strlen($question) < 5) {
        $errors['question'] = 'Question must be at least 5 characters';
    }
    if (empty($answer) || strlen($answer) < 10) {
        $errors['answer'] = 'Answer must be at least 10 characters';
    }
    
    // Insert/Update
    if (empty($errors)) {
        try {
            if ($faqId > 0) {
                $stmt = $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$question, $answer, $category, $sortOrder, $isActive, $faqId]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'FAQ updated successfully!'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$question, $answer, $category, $sortOrder, $isActive]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'FAQ added successfully!'];
            }
            header('Location: faqs.php');
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Failed: ' . $e->getMessage();
        }
    }
    
    // Show old values
    $editFaq = [
        'id' => $faqId,
        'question' => $question,
        'answer' => $answer,
        'category' => $category,
        'sort_order' => $sortOrder,
        'is_active' => $isActive,
    ];
    $editMode = ($faqId > 0);
}

// ============ FETCH FAQS ============
$stmt = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC");
$faqs = $stmt->fetchAll();

// ============ MINI STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM faqs");
$statTotal = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM faqs WHERE is_active = 1");
$statActive = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM faqs WHERE is_active = 0");
$statInactive = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT category) AS cnt FROM faqs");
$statCategories = (int)$stmt->fetch()['cnt'];

// ============ GET UNIQUE CATEGORIES ============
$stmt = $pdo->query("SELECT DISTINCT category FROM faqs ORDER BY category ASC");
$existingCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ PAGE VARIABLES ============
$pageTitle = 'FAQs';
$pageSubtitle = 'Manage frequently asked questions';

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
        <h1>FAQs Management</h1>
        <p>Total <?php echo $statTotal; ?> FAQ<?php echo $statTotal != 1 ? 's' : ''; ?></p>
    </div>
</div>

<!-- ================= MINI STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    <div class="stat-admin-card stat-green" data-aos="fade-up">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-circle-question"></i></div></div>
        <div class="stat-admin-value"><?php echo $statTotal; ?></div>
        <div class="stat-admin-label">Total FAQs</div>
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
    <div class="stat-admin-card stat-gold" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-tags"></i></div></div>
        <div class="stat-admin-value"><?php echo $statCategories; ?></div>
        <div class="stat-admin-label">Categories</div>
    </div>
</div>

<div class="row g-4">
    
    <!-- ================= LEFT: FORM ================= -->
    <div class="col-lg-4">
        <div class="admin-card" style="position:sticky; top:90px;" data-aos="fade-up">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:15px; border-bottom:1px dashed var(--border); display:flex; align-items:center; gap:8px;">
                <i class="fas fa-<?php echo $editMode ? 'pen' : 'plus-circle'; ?>" style="color:var(--primary);"></i>
                <?php echo $editMode ? 'Edit FAQ' : 'Add New FAQ'; ?>
            </h3>
            
            <?php if (isset($errors['general'])): ?>
            <div style="background:#f8d7da; color:#721c24; padding:12px 16px; border-radius:10px; margin-bottom:15px; font-size:13px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="faqs.php">
                <input type="hidden" name="id" value="<?php echo (int)$editFaq['id']; ?>">
                
                <div class="admin-form-group">
                    <label>Question <span class="req">*</span></label>
                    <input type="text" name="question" placeholder="e.g. How long does delivery take?" value="<?php echo htmlspecialchars($editFaq['question']); ?>" required>
                    <?php if (isset($errors['question'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['question']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="admin-form-group">
                    <label>Answer <span class="req">*</span></label>
                    <textarea name="answer" rows="5" placeholder="Write a detailed answer..." required><?php echo htmlspecialchars($editFaq['answer']); ?></textarea>
                    <?php if (isset($errors['answer'])): ?>
                    <span style="color:var(--danger); font-size:12px; margin-top:5px; display:block;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['answer']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="admin-form-group">
                    <label>Category</label>
                    <input type="text" name="category" list="categoryList" placeholder="e.g. delivery" value="<?php echo htmlspecialchars($editFaq['category']); ?>">
                    <datalist id="categoryList">
                        <option value="general">
                        <option value="orders">
                        <option value="delivery">
                        <option value="payment">
                        <option value="returns">
                        <option value="products">
                        <?php foreach ($existingCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <small style="color:var(--gray); font-size:11px; margin-top:4px; display:block;">
                        <i class="fas fa-info-circle"></i> Type or select category
                    </small>
                </div>
                
                <div class="admin-form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" placeholder="1" value="<?php echo (int)$editFaq['sort_order']; ?>" min="0">
                    <small style="color:var(--gray); font-size:11px; margin-top:4px; display:block;">
                        <i class="fas fa-info-circle"></i> Lower number = shown first
                    </small>
                </div>
                
                <div class="admin-form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo $editFaq['is_active'] ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                        <span>Active (Show on FAQs page)</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-admin btn-primary-admin" style="width:100%; justify-content:center; padding:14px;">
                    <i class="fas fa-<?php echo $editMode ? 'save' : 'plus'; ?>"></i>
                    <?php echo $editMode ? 'Update FAQ' : 'Add FAQ'; ?>
                </button>
                
                <?php if ($editMode): ?>
                <a href="faqs.php" class="btn-admin btn-outline-admin" style="width:100%; justify-content:center; margin-top:10px;">
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
                <h3><i class="fas fa-list"></i> All FAQs</h3>
                <div style="font-size:12px; color:var(--gray);">
                    <?php echo count($faqs); ?> FAQ<?php echo count($faqs) != 1 ? 's' : ''; ?>
                </div>
            </div>
            
            <?php if (empty($faqs)): ?>
            
            <div class="admin-empty">
                <i class="fas fa-circle-question"></i>
                <h3>No FAQs yet</h3>
                <p>Add your first FAQ</p>
            </div>
            
            <?php else: ?>
            
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Question</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faqs as $f): 
                            $statusColor = $f['is_active'] ? '#2E7D32' : '#616161';
                            $statusText = $f['is_active'] ? 'Active' : 'Inactive';
                            
                            // Category colors
                            $catColors = [
                                'general' => '#2196F3',
                                'orders' => '#FF6F00',
                                'delivery' => '#7B1FA2',
                                'payment' => '#2E7D32',
                                'returns' => '#E53935',
                                'products' => '#FFB300',
                            ];
                            $catColor = $catColors[$f['category']] ?? '#616161';
                        ?>
                        <tr>
                            <td>
                                <span style="background: var(--cream); color: var(--dark); width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                    <?php echo (int)$f['sort_order']; ?>
                                </span>
                            </td>
                            <td>
                                <div style="max-width: 400px;">
                                    <strong style="display: block; font-size: 13px; color: var(--dark); margin-bottom: 4px; line-height: 1.4;">
                                        <?php echo htmlspecialchars($f['question']); ?>
                                    </strong>
                                    <span style="font-size: 11px; color: var(--gray); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars(substr($f['answer'], 0, 100)); ?>...
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="background: <?php echo $catColor; ?>15; color: <?php echo $catColor; ?>; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid <?php echo $catColor; ?>30;">
                                    <?php echo htmlspecialchars($f['category']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="background: <?php echo $statusColor; ?>; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700;">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="faqs.php?toggle=<?php echo (int)$f['id']; ?>" class="table-action-btn <?php echo $f['is_active'] ? 'stock' : 'view'; ?>" title="<?php echo $f['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-<?php echo $f['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </a>
                                    <a href="faqs.php?edit=<?php echo (int)$f['id']; ?>" class="table-action-btn edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDeleteFaq(<?php echo (int)$f['id']; ?>, '<?php echo htmlspecialchars(addslashes(substr($f['question'], 0, 50))); ?>')" class="table-action-btn delete" title="Delete">
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
function confirmDeleteFaq(id, question) {
    if (confirm('Are you sure you want to delete this FAQ?\n\n"' + question + '..."\n\nThis action cannot be undone.')) {
        window.location.href = 'faqs.php?delete=' + id;
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>