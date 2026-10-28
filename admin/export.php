<?php
// ============================================
// admin/export.php - CSV Export
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../includes/db.php';

// ============ HANDLE EXPORT ============
if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $filename = 'freshmart_' . $type . '_' . date('Y-m-d_His') . '.csv';
    
    // Headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM for Excel (so Urdu/English chars show correctly)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    switch ($type) {
        
        // ============ ORDERS EXPORT ============
        case 'orders':
            fputcsv($output, [
                'Order ID',
                'Customer Name',
                'Email',
                'Phone',
                'Address',
                'City',
                'Postal Code',
                'Items Count',
                'Subtotal',
                'Delivery Fee',
                'Discount',
                'Total',
                'Payment Method',
                'Status',
                'Order Date'
            ]);
            
            $stmt = $pdo->query("
                SELECT o.*, 
                (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_id = o.id) AS items_count
                FROM orders o 
                ORDER BY o.id DESC
            ");
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, [
                    $row['order_number'],
                    $row['customer_name'],
                    $row['customer_email'],
                    $row['customer_phone'],
                    $row['customer_address'],
                    $row['customer_city'],
                    $row['customer_postal'] ?? '',
                    $row['items_count'],
                    $row['subtotal'],
                    $row['delivery_fee'],
                    $row['discount'],
                    $row['total'],
                    $row['payment_method'],
                    $row['status'],
                    $row['created_at'],
                ]);
            }
            break;
        
        // ============ PRODUCTS EXPORT ============
        case 'products':
            fputcsv($output, [
                'ID',
                'Name',
                'SKU',
                'Category',
                'Description',
                'Price',
                'Old Price',
                'Unit',
                'Stock',
                'Rating',
                'Badge',
                'Image URL',
                'Created At'
            ]);
            
            $stmt = $pdo->query("
                SELECT p.*, c.name AS category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id ASC
            ");
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['sku'],
                    $row['category_name'] ?? 'N/A',
                    $row['description'] ?? '',
                    $row['price'],
                    $row['old_price'],
                    $row['unit'],
                    $row['stock'],
                    $row['rating'],
                    $row['badge'] ?? '',
                    $row['image'],
                    $row['created_at'],
                ]);
            }
            break;
        
        // ============ CUSTOMERS EXPORT ============
        case 'customers':
            fputcsv($output, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'City',
                'Postal Code',
                'Address',
                'Total Orders',
                'Total Spent',
                'Joined Date'
            ]);
            
            $stmt = $pdo->query("
                SELECT u.*,
                (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
                (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id AND status != 'cancelled') AS total_spent
                FROM users u 
                WHERE u.role = 'user'
                ORDER BY u.id DESC
            ");
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['phone'],
                    $row['city'] ?? '',
                    $row['postal_code'] ?? '',
                    $row['address'] ?? '',
                    $row['order_count'],
                    $row['total_spent'],
                    $row['created_at'],
                ]);
            }
            break;
        
        // ============ CATEGORIES EXPORT ============
        case 'categories':
            fputcsv($output, [
                'ID',
                'Name',
                'Slug',
                'Icon',
                'Image URL',
                'Products Count',
                'Created At'
            ]);
            
            $stmt = $pdo->query("
                SELECT c.*,
                (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
                FROM categories c 
                ORDER BY c.id ASC
            ");
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['slug'],
                    $row['icon'] ?? '',
                    $row['image'] ?? '',
                    $row['product_count'],
                    $row['created_at'],
                ]);
            }
            break;
        
        default:
            fputcsv($output, ['Invalid export type']);
            break;
    }
    
    fclose($output);
    exit;
}

// ============ FETCH STATS ============
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders");
$totalOrders = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products");
$totalProducts = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user'");
$totalUsers = (int)$stmt->fetch()['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM categories");
$totalCategories = (int)$stmt->fetch()['cnt'];

// ============ PAGE VARIABLES ============
$pageTitle = 'Export Data';
$pageSubtitle = 'Download store data as CSV';

include 'includes/admin-header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Export Data</h1>
        <p>Download your store data in CSV format (Excel compatible)</p>
    </div>
</div>

<!-- ================= INFO BANNER ================= -->
<div class="admin-card" style="margin-bottom: 25px; background: linear-gradient(135deg, rgba(46,125,50,0.05), rgba(255,111,0,0.05)); border-left: 4px solid var(--primary);" data-aos="fade-up">
    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <div style="width: 55px; height: 55px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            <i class="fas fa-file-csv"></i>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">CSV Export</h3>
            <p style="font-size: 13px; color: var(--gray); margin: 0;">Download your data as CSV files. All files can be opened in Excel, Google Sheets, or Numbers.</p>
        </div>
    </div>
</div>

<!-- ================= EXPORT CARDS ================= -->
<div class="row g-4">
    
    <!-- Orders Export -->
    <div class="col-lg-6" data-aos="fade-up">
        <div class="admin-card" style="height: 100%; display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; box-shadow: 0 8px 20px rgba(46,125,50,0.25);">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">Orders Data</h3>
                    <p style="font-size: 13px; color: var(--gray); margin: 0;"><?php echo $totalOrders; ?> orders available</p>
                </div>
            </div>
            
            <p style="font-size: 13px; color: var(--gray); line-height: 1.7; margin-bottom: 20px; flex: 1;">
                Export all orders with customer details, payment info, order status, and total amounts. Perfect for accounting and record keeping.
            </p>
            
            <div style="background: var(--cream); padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 12px; color: var(--gray);">
                <strong style="color: var(--dark);">Includes:</strong> Order ID, Customer, Address, Total, Status, Date
            </div>
            
            <a href="export.php?type=orders" class="btn-admin btn-primary-admin" style="justify-content: center; padding: 12px;">
                <i class="fas fa-download"></i> Download Orders CSV
            </a>
        </div>
    </div>
    
    <!-- Products Export -->
    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
        <div class="admin-card" style="height: 100%; display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent), #FF9800); color: white; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; box-shadow: 0 8px 20px rgba(255,111,0,0.25);">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">Products Data</h3>
                    <p style="font-size: 13px; color: var(--gray); margin: 0;"><?php echo $totalProducts; ?> products available</p>
                </div>
            </div>
            
            <p style="font-size: 13px; color: var(--gray); line-height: 1.7; margin-bottom: 20px; flex: 1;">
                Export your complete product catalog with SKU, prices, stock levels, categories, and all details. Ideal for inventory management and bulk updates.
            </p>
            
            <div style="background: var(--cream); padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 12px; color: var(--gray);">
                <strong style="color: var(--dark);">Includes:</strong> SKU, Name, Category, Price, Stock, Image URL
            </div>
            
            <a href="export.php?type=products" class="btn-admin btn-primary-admin" style="justify-content: center; padding: 12px; background: linear-gradient(135deg, var(--accent), #E65100);">
                <i class="fas fa-download"></i> Download Products CSV
            </a>
        </div>
    </div>
    
    <!-- Customers Export -->
    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
        <div class="admin-card" style="height: 100%; display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--info), #1565C0); color: white; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; box-shadow: 0 8px 20px rgba(33,150,243,0.25);">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">Customers Data</h3>
                    <p style="font-size: 13px; color: var(--gray); margin: 0;"><?php echo $totalUsers; ?> customers available</p>
                </div>
            </div>
            
            <p style="font-size: 13px; color: var(--gray); line-height: 1.7; margin-bottom: 20px; flex: 1;">
                Export customer list with contact information, total orders, and lifetime spending. Great for marketing campaigns and customer analysis.
            </p>
            
            <div style="background: var(--cream); padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 12px; color: var(--gray);">
                <strong style="color: var(--dark);">Includes:</strong> Name, Email, Phone, City, Orders, Total Spent
            </div>
            
            <a href="export.php?type=customers" class="btn-admin btn-primary-admin" style="justify-content: center; padding: 12px; background: linear-gradient(135deg, var(--info), #0D47A1);">
                <i class="fas fa-download"></i> Download Customers CSV
            </a>
        </div>
    </div>
    
    <!-- Categories Export -->
    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
        <div class="admin-card" style="height: 100%; display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--gold), #FF8F00); color: white; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; box-shadow: 0 8px 20px rgba(255,179,0,0.3);">
                    <i class="fas fa-th-large"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">Categories Data</h3>
                    <p style="font-size: 13px; color: var(--gray); margin: 0;"><?php echo $totalCategories; ?> categories available</p>
                </div>
            </div>
            
            <p style="font-size: 13px; color: var(--gray); line-height: 1.7; margin-bottom: 20px; flex: 1;">
                Export all product categories with slugs, icons, and product counts. Useful for organizing and analyzing your store structure.
            </p>
            
            <div style="background: var(--cream); padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 12px; color: var(--gray);">
                <strong style="color: var(--dark);">Includes:</strong> Name, Slug, Icon, Image, Product Count
            </div>
            
            <a href="export.php?type=categories" class="btn-admin btn-primary-admin" style="justify-content: center; padding: 12px; background: linear-gradient(135deg, var(--gold), #E65100);">
                <i class="fas fa-download"></i> Download Categories CSV
            </a>
        </div>
    </div>
    
</div>

<!-- ================= HELP SECTION ================= -->
<div class="admin-card" style="margin-top: 25px;" data-aos="fade-up">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-question-circle" style="color: var(--primary);"></i> How to use CSV files?
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
        <div style="padding: 15px; background: var(--cream); border-radius: 12px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--dark); margin-bottom: 8px;">
                <i class="fas fa-file-excel" style="color: #2E7D32; margin-right: 6px;"></i> Open in Excel
            </div>
            <p style="font-size: 12px; color: var(--gray); margin: 0; line-height: 1.6;">
                Double-click the downloaded file to open in Microsoft Excel or Google Sheets.
            </p>
        </div>
        
        <div style="padding: 15px; background: var(--cream); border-radius: 12px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--dark); margin-bottom: 8px;">
                <i class="fas fa-cloud-upload-alt" style="color: #FF6F00; margin-right: 6px;"></i> Import Later
            </div>
            <p style="font-size: 12px; color: var(--gray); margin: 0; line-height: 1.6;">
                Use the exported CSV to bulk-import data later or for backup purposes.
            </p>
        </div>
        
        <div style="padding: 15px; background: var(--cream); border-radius: 12px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--dark); margin-bottom: 8px;">
                <i class="fas fa-shield-alt" style="color: #2196F3; margin-right: 6px;"></i> Keep Secure
            </div>
            <p style="font-size: 12px; color: var(--gray); margin: 0; line-height: 1.6;">
                CSV files contain customer data — store them securely and don't share publicly.
            </p>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>