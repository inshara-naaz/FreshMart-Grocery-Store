<?php
// ============================================
// admin/messages.php - Contact Messages
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
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$deleteId]);
    $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Message deleted successfully'];
    header('Location: messages.php');
    exit;
}

// ============ HANDLE STATUS CHANGE ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $msgId = (int)$_POST['message_id'];
    $newStatus = $_POST['status'];
    
    $allowed = ['new', 'read', 'replied'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $msgId]);
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Status updated to ' . ucfirst($newStatus)];
    }
    header('Location: messages.php');
    exit;
}

// ============ FILTERS ============
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

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
    $where .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$allowedStatuses = ['new', 'read', 'replied'];
if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses)) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

// ============ SORT ============
$orderBy = "ORDER BY id DESC";
if ($sortBy === 'oldest') {
    $orderBy = "ORDER BY id ASC";
}

// ============ COUNT ============
$countSql = "SELECT COUNT(*) AS cnt FROM contact_messages $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalMessages = (int)$stmt->fetch()['cnt'];

$totalPages = (int)ceil($totalMessages / $perPage);
if ($totalPages < 1) $totalPages = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

// ============ FETCH MESSAGES ============
$sql = "SELECT * FROM contact_messages $where $orderBy LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// ============ MINI STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM contact_messages");
$statTotal = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM contact_messages WHERE status = 'new'");
$statNew = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM contact_messages WHERE status = 'read'");
$statRead = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM contact_messages WHERE status = 'replied'");
$statReplied = (int)$stmt->fetch()['cnt'];

// ============ FLASH ============
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// ============ CURRENT QUERY FOR RETURN ============
$returnQuery = http_build_query(array_filter([
    'search' => $searchQuery,
    'status' => $statusFilter,
    'sort' => $sortBy,
    'page' => $currentPage > 1 ? $currentPage : null,
]));

// ============ PAGE VARIABLES ============
$pageTitle = 'Messages';
$pageSubtitle = 'Customer contact messages';

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
        <h1>Messages Management</h1>
        <p>Total <?php echo $totalMessages; ?> message<?php echo $totalMessages != 1 ? 's' : ''; ?> found</p>
    </div>
</div>

<!-- ================= MINI STATS ================= -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    <div class="stat-admin-card stat-green" data-aos="fade-up">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-envelope"></i></div></div>
        <div class="stat-admin-value"><?php echo $statTotal; ?></div>
        <div class="stat-admin-label">Total Messages</div>
    </div>
    <div class="stat-admin-card stat-red" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-envelope-open-text"></i></div></div>
        <div class="stat-admin-value"><?php echo $statNew; ?></div>
        <div class="stat-admin-label">New / Unread</div>
    </div>
    <div class="stat-admin-card stat-blue" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-eye"></i></div></div>
        <div class="stat-admin-value"><?php echo $statRead; ?></div>
        <div class="stat-admin-label">Read</div>
    </div>
    <div class="stat-admin-card stat-gold" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-admin-top"><div class="stat-admin-icon"><i class="fas fa-reply"></i></div></div>
        <div class="stat-admin-value"><?php echo $statReplied; ?></div>
        <div class="stat-admin-label">Replied</div>
    </div>
</div>

<!-- ================= FILTER BAR ================= -->
<div class="admin-card" style="padding: 20px; margin-bottom: 25px;" data-aos="fade-up">
    <form method="GET" action="messages.php" id="filterForm" style="display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:12px; align-items:end;">

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Search</label>
            <input type="text" name="search" placeholder="Name, email, subject..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>New</option>
                <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Read</option>
                <option value="replied" <?php echo $statusFilter === 'replied' ? 'selected' : ''; ?>>Replied</option>
            </select>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
            <label>Sort</label>
            <select name="sort">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-admin btn-primary-admin" style="padding:12px 18px;">
                <i class="fas fa-filter"></i>
            </button>
            <a href="messages.php" class="btn-admin btn-outline-admin" style="padding:12px 18px;">
                <i class="fas fa-redo"></i>
            </a>
        </div>

    </form>
</div>

<!-- ================= MESSAGES LIST ================= -->
<?php if (empty($messages)): ?>

<div class="admin-card" data-aos="fade-up">
    <div class="admin-empty">
        <i class="fas fa-envelope"></i>
        <h3>No messages found</h3>
        <p>Customer messages will appear here</p>
    </div>
</div>

<?php else: ?>

<div style="display: flex; flex-direction: column; gap: 15px;">

    <?php foreach ($messages as $msg): 
        $statusInfo = [
            'new' => ['label' => 'New', 'color' => '#E53935', 'bg' => 'rgba(229,57,53,0.1)', 'icon' => 'fa-circle'],
            'read' => ['label' => 'Read', 'color' => '#2196F3', 'bg' => 'rgba(33,150,243,0.1)', 'icon' => 'fa-eye'],
            'replied' => ['label' => 'Replied', 'color' => '#2E7D32', 'bg' => 'rgba(46,125,50,0.1)', 'icon' => 'fa-check-circle'],
        ];
        $st = $statusInfo[$msg['status']] ?? $statusInfo['new'];
        $initial = strtoupper(substr($msg['name'], 0, 1));
        $colors = ['#2E7D32', '#FF6F00', '#2196F3', '#E53935', '#7B1FA2'];
        $avatarColor = $colors[$msg['id'] % count($colors)];
    ?>
    
    <div class="admin-card" style="padding: 0; overflow: hidden; border-left: 4px solid <?php echo $st['color']; ?>;" data-aos="fade-up">
        
        <!-- Header -->
        <div style="padding: 20px 25px; background: <?php echo $msg['status'] === 'new' ? 'rgba(229,57,53,0.03)' : 'var(--white)'; ?>; border-bottom: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
                
                <div style="display: flex; gap: 15px; align-items: center; flex: 1;">
                    <div style="width: 50px; height: 50px; background: <?php echo $avatarColor; ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0;">
                        <?php echo $initial; ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
                            <strong style="font-size: 15px; color: var(--dark);"><?php echo htmlspecialchars($msg['name']); ?></strong>
                            <span style="background: <?php echo $st['bg']; ?>; color: <?php echo $st['color']; ?>; padding: 3px 12px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas <?php echo $st['icon']; ?>" style="font-size: 7px;"></i> <?php echo $st['label']; ?>
                            </span>
                        </div>
                        <div style="font-size: 12px; color: var(--gray); display: flex; flex-wrap: wrap; gap: 12px;">
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($msg['email']); ?></span>
                            <?php if (!empty($msg['phone'])): ?>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($msg['phone']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-size: 12px; color: var(--gray);">
                        <i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?>
                    </span>
                </div>
                
            </div>
        </div>
        
        <!-- Subject -->
        <?php if (!empty($msg['subject'])): ?>
        <div style="padding: 15px 25px 0;">
            <div style="font-size: 11px; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 4px;">
                Subject
            </div>
            <strong style="font-size: 14px; color: var(--primary-dark);">
                <?php echo htmlspecialchars($msg['subject']); ?>
            </strong>
        </div>
        <?php endif; ?>
        
        <!-- Message Body -->
        <div style="padding: 15px 25px 20px;">
            <div style="font-size: 11px; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 8px;">
                Message
            </div>
            <p style="font-size: 14px; color: var(--dark); line-height: 1.8; margin: 0; padding: 15px; background: var(--cream); border-radius: 10px;">
                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
            </p>
        </div>
        
        <!-- Footer Actions -->
        <div style="padding: 15px 25px; background: var(--cream); border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            
            <!-- Status Change -->
            <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                
                <label style="font-size: 12px; font-weight: 600; color: var(--gray);">Status:</label>
                <select name="status" onchange="this.form.submit()" 
                        style="padding: 6px 12px; border: 2px solid <?php echo $st['color']; ?>; border-radius: 8px; font-size: 12px; font-weight: 600; color: <?php echo $st['color']; ?>; background: white; cursor: pointer; font-family: 'Poppins', sans-serif; outline: none;">
                    <option value="new" <?php echo $msg['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo $msg['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $msg['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
            </form>
            
            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject'] ?? 'Your Message'); ?>" 
                   class="btn-admin btn-primary-admin" style="padding: 8px 16px; font-size: 12px;">
                    <i class="fas fa-reply"></i> Reply via Email
                </a>
                <a href="javascript:void(0)" onclick="confirmDeleteMessage(<?php echo (int)$msg['id']; ?>, '<?php echo htmlspecialchars(addslashes($msg['name'])); ?>')" 
                   class="btn-admin btn-danger-admin" style="padding: 8px 16px; font-size: 12px;">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
            
        </div>
        
    </div>
    
    <?php endforeach; ?>
    
</div>

<!-- ================= PAGINATION ================= -->
<?php if ($totalPages > 1): 
    $cp = (int)$currentPage;
    $pp = (int)$perPage;
    $tp = (int)$totalMessages;
    $tpg = (int)$totalPages;
    $startNum = (($cp - 1) * $pp) + 1;
    $endNum = min($cp * $pp, $tp);
?>
<div class="admin-card" style="margin-top: 20px; padding: 20px 25px;" data-aos="fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
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
</div>
<?php endif; ?>

<?php endif; ?>

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
function confirmDeleteMessage(id, name) {
    if (confirm('Are you sure you want to delete message from "' + name + '"?\n\nThis action cannot be undone.')) {
        window.location.href = 'messages.php?delete=' + id;
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>