<?php
// ============================================
// admin/includes/admin-header.php
// Admin Panel - Sidebar + Top Bar (Foundation)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============ ADMIN LOGIN CHECK ============
// Agar admin logged in nahi, to login page pe bhejo
// ============ ADMIN LOGIN CHECK ============
$adminCurrentPage = basename($_SERVER['PHP_SELF']);
if ($adminCurrentPage !== 'login.php' && !isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// ============ DB CONNECTION ============
include_once __DIR__ . '/../../includes/db.php';
include_once __DIR__ . '/settings-functions.php';
$storeSettings = getSettings($pdo);

// ============ FETCH PENDING ORDERS COUNT ============
$pendingOrdersCount = 0;
$totalOrdersCount = 0;
$totalProductsCount = 0;
$totalUsersCount = 0;

if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
        $pendingOrdersCount = (int)$stmt->fetch()['cnt'];

        $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM orders");
        $totalOrdersCount = (int)$stmt->fetch()['cnt'];

        $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM products");
        $totalProductsCount = (int)$stmt->fetch()['cnt'];

        $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user'");
        $totalUsersCount = (int)$stmt->fetch()['cnt'];
    } catch (Exception $e) {
        // Silent fail
    }
}

// ============ ADMIN INFO ============
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - FreshMart</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ================= ADMIN GLOBAL ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2E7D32;
            --primary-dark: #1B5E20;
            --primary-light: #4CAF50;
            --accent: #FF6F00;
            --gold: #FFB300;
            --cream: #FAF8F3;
            --light: #F5F5F5;
            --dark: #212121;
            --gray: #616161;
            --border: #E8E8E8;
            --white: #FFFFFF;
            --sidebar-bg: #1A1A1A;
            --sidebar-hover: #2A2A2A;
            --sidebar-active: #2E7D32;
            --danger: #E53935;
            --success: #43A047;
            --warning: #FFB300;
            --info: #2196F3;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #F0F2F5;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* ================= SIDEBAR ================= */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: var(--white);
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 5px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 22px 22px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.35);
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .sidebar-brand-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.3px;
        }

        .sidebar-brand-name span {
            color: var(--gold);
        }

        .sidebar-brand-tag {
            font-size: 9px;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .sidebar-section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.35);
            padding: 20px 22px 8px;
            font-weight: 600;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0 12px;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 4px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
        }

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
            font-size: 15px;
            transition: var(--transition);
        }

        .sidebar-nav a:hover {
            background: var(--sidebar-hover);
            color: var(--white);
            transform: translateX(3px);
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.35);
        }

        .sidebar-nav a.active i {
            color: var(--white);
        }

        .sidebar-nav a .badge {
            margin-left: auto;
            background: var(--accent);
            color: var(--white);
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 50px;
            font-weight: 700;
        }

        .sidebar-nav a.active .badge {
            background: rgba(255, 255, 255, 0.25);
        }

        .sidebar-footer {
            padding: 20px 12px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-view-store {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.85);
            padding: 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .sidebar-view-store:hover {
            background: var(--accent);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* ================= MAIN CONTENT ================= */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            transition: var(--transition);
        }

        /* ================= TOPBAR ================= */
        .admin-topbar {
            background: var(--white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid var(--border);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .sidebar-toggle {
            display: none;
            background: var(--cream);
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            color: var(--dark);
            font-size: 18px;
            cursor: pointer;
            transition: var(--transition);
        }

        .sidebar-toggle:hover {
            background: var(--primary);
            color: var(--white);
        }

        .topbar-page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .topbar-page-subtitle {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar-icon-btn {
            width: 42px;
            height: 42px;
            background: var(--cream);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 16px;
            text-decoration: none;
            position: relative;
            transition: var(--transition);
        }

        .topbar-icon-btn:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .topbar-icon-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: var(--white);
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 50px;
            font-weight: 700;
            border: 2px solid var(--white);
        }

        .topbar-admin {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 14px 6px 6px;
            background: var(--cream);
            border-radius: 50px;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
        }

        .topbar-admin:hover {
            background: var(--light);
        }

        .topbar-admin-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
        }

        .topbar-admin-info {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .topbar-admin-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--dark);
        }

        .topbar-admin-role {
            font-size: 10px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Admin Dropdown */
        .admin-dropdown-wrap {
            position: relative;
        }

        .admin-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            min-width: 220px;
            border-radius: 14px;
            box-shadow: var(--shadow-lg);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            z-index: 100;
            border: 1px solid var(--border);
            margin-top: 8px;
        }

        .admin-dropdown-wrap:hover .admin-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .admin-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--dark);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .admin-dropdown a i {
            width: 18px;
            color: var(--primary);
            font-size: 14px;
        }

        .admin-dropdown a:hover {
            background: var(--cream);
            color: var(--primary);
            padding-left: 18px;
        }

        .admin-dropdown a.logout-link {
            color: var(--danger);
        }

        .admin-dropdown a.logout-link i {
            color: var(--danger);
        }

        .admin-dropdown a.logout-link:hover {
            background: rgba(229, 57, 53, 0.08);
        }

        .admin-dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 6px 8px;
        }

        /* ================= CONTENT AREA ================= */
        .admin-content {
            padding: 30px;
            min-height: calc(100vh - 72px);
        }

        /* ================= PAGE HEADER ================= */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header-left h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .page-header-left p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
        }

        .page-header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ================= BUTTONS ================= */
        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-primary-admin {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
        }

        .btn-primary-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.35);
            color: var(--white);
        }

        .btn-outline-admin {
            background: var(--white);
            color: var(--dark);
            border: 2px solid var(--border);
        }

        .btn-outline-admin:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(46, 125, 50, 0.05);
        }

        .btn-danger-admin {
            background: linear-gradient(135deg, #E53935, #B71C1C);
            color: var(--white);
        }

        .btn-danger-admin:hover {
            transform: translateY(-2px);
            color: var(--white);
            box-shadow: 0 8px 20px rgba(229, 57, 53, 0.35);
        }

        /* ================= CARDS ================= */
        .admin-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .admin-card:hover {
            box-shadow: var(--shadow-md);
        }

        /* ================= STAT CARDS ================= */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-admin-card {
            background: var(--white);
            border-radius: 16px;
            padding: 22px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .stat-admin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .stat-admin-card.stat-orange::before {
            background: var(--accent);
        }

        .stat-admin-card.stat-blue::before {
            background: var(--info);
        }

        .stat-admin-card.stat-red::before {
            background: var(--danger);
        }

        .stat-admin-card.stat-gold::before {
            background: var(--gold);
        }

        .stat-admin-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-admin-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .stat-admin-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.25);
        }

        .stat-admin-card.stat-orange .stat-admin-icon {
            background: linear-gradient(135deg, var(--accent), #FF9800);
            box-shadow: 0 6px 16px rgba(255, 111, 0, 0.25);
        }

        .stat-admin-card.stat-blue .stat-admin-icon {
            background: linear-gradient(135deg, var(--info), #1565C0);
            box-shadow: 0 6px 16px rgba(33, 150, 243, 0.25);
        }

        .stat-admin-card.stat-red .stat-admin-icon {
            background: linear-gradient(135deg, var(--danger), #B71C1C);
            box-shadow: 0 6px 16px rgba(229, 57, 53, 0.25);
        }

        .stat-admin-card.stat-gold .stat-admin-icon {
            background: linear-gradient(135deg, var(--gold), #FF8F00);
            box-shadow: 0 6px 16px rgba(255, 179, 0, 0.3);
        }

        .stat-admin-trend {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 50px;
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary);
        }

        .stat-admin-trend.down {
            background: rgba(229, 57, 53, 0.1);
            color: var(--danger);
        }

        .stat-admin-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 4px;
            line-height: 1;
        }

        .stat-admin-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* ================= TABLES ================= */
        .admin-table-wrap {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        .admin-table-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .admin-table-header h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-table-header h3 i {
            color: var(--primary);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table thead th {
            background: var(--cream);
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
            font-weight: 700;
            border-bottom: 1px solid var(--border);
        }

        .admin-table tbody td {
            padding: 16px 20px;
            font-size: 13px;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .admin-table tbody tr {
            transition: var(--transition);
        }

        .admin-table tbody tr:hover {
            background: #FCFAF5;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-product {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-product img {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid var(--border);
        }

        .table-product-info strong {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .table-product-info span {
            font-size: 11px;
            color: var(--gray);
        }

        .table-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background: rgba(255, 179, 0, 0.15);
            color: #B26A00;
        }

        .badge-confirmed {
            background: rgba(33, 150, 243, 0.15);
            color: #1565C0;
        }

        .badge-delivered {
            background: rgba(46, 125, 50, 0.15);
            color: var(--primary-dark);
        }

        .badge-cancelled {
            background: rgba(229, 57, 53, 0.15);
            color: #B71C1C;
        }

        .badge-sale {
            background: rgba(255, 111, 0, 0.15);
            color: var(--accent);
        }

        .badge-hot {
            background: rgba(229, 57, 53, 0.15);
            color: var(--danger);
        }

        .badge-new {
            background: rgba(46, 125, 50, 0.15);
            color: var(--primary-dark);
        }

        .badge-active {
            background: rgba(46, 125, 50, 0.15);
            color: var(--primary-dark);
        }

        .badge-inactive {
            background: rgba(97, 97, 97, 0.15);
            color: var(--gray);
        }

        .table-actions {
            display: flex;
            gap: 6px;
        }

        .table-action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--cream);
            color: var(--gray);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
        }

        .table-action-btn:hover {
            transform: translateY(-2px);
        }

        .table-action-btn.edit:hover {
            background: var(--info);
            color: var(--white);
        }

        .table-action-btn.delete:hover {
            background: var(--danger);
            color: var(--white);
        }

        .table-action-btn.view:hover {
            background: var(--primary);
            color: var(--white);
        }

        .table-action-btn.stock:hover {
            background: var(--accent);
            color: var(--white);
        }

        /* ================= FORMS ================= */
        .admin-form-group {
            margin-bottom: 20px;
        }

        .admin-form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .admin-form-group label .req {
            color: var(--danger);
        }

        .admin-form-group input[type="text"],
        .admin-form-group input[type="email"],
        .admin-form-group input[type="number"],
        .admin-form-group input[type="tel"],
        .admin-form-group input[type="password"],
        .admin-form-group input[type="date"],
        .admin-form-group select,
        .admin-form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: var(--dark);
            outline: none;
            transition: var(--transition);
            background: var(--white);
            resize: vertical;
        }

        .admin-form-group input:focus,
        .admin-form-group select:focus,
        .admin-form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.08);
        }

        /* ================= EMPTY STATE ================= */
        .admin-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .admin-empty i {
            font-size: 60px;
            color: var(--border);
            margin-bottom: 20px;
        }

        .admin-empty h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .admin-empty p {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 20px;
        }

        /* ================= TOAST ================= */
        .admin-toast {
            position: fixed;
            top: 90px;
            right: 30px;
            padding: 14px 22px;
            background: var(--primary);
            color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
            font-size: 13px;
            font-weight: 600;
            z-index: 9999;
            transform: translateX(400px);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-toast.show {
            transform: translateX(0);
        }

        .admin-toast.toast-error {
            background: var(--danger);
            box-shadow: 0 10px 30px rgba(229, 57, 53, 0.3);
        }

        .admin-toast.toast-warning {
            background: var(--warning);
            color: var(--dark);
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 767px) {
            .admin-content {
                padding: 20px 15px;
            }

            .admin-topbar {
                padding: 12px 18px;
            }

            .topbar-page-title {
                font-size: 15px;
            }

            .topbar-page-subtitle {
                display: none;
            }

            .topbar-admin-info {
                display: none;
            }

            .topbar-admin {
                padding: 6px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-table {
                font-size: 12px;
            }

            .admin-table thead th,
            .admin-table tbody td {
                padding: 12px 15px;
            }

            .page-header-left h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- ================= SIDEBAR ================= -->
    <aside class="admin-sidebar" id="adminSidebar">

        <!-- Brand -->
        <a href="index.php" class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="fas fa-leaf"></i></div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">Fresh<span>Mart</span></span>
                <span class="sidebar-brand-tag">Admin Panel</span>
            </div>
        </a>

        <!-- Dashboard -->
        <div class="sidebar-section-title">Main</div>
        <ul class="sidebar-nav">
            <li>
                <a href="index.php" class="<?php echo $adminCurrentPage === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>

            <li>
                <a href="reports.php" class="<?php echo $adminCurrentPage === 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
        </ul>

        <!-- Store Management -->
        <div class="sidebar-section-title">Store</div>
        <ul class="sidebar-nav">
            <li>
                <a href="products.php" class="<?php echo in_array($adminCurrentPage, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                    <span class="badge"><?php echo $totalProductsCount; ?></span>
                </a>
            </li>
            <li>
                <a href="coupons.php" class="<?php echo $adminCurrentPage === 'coupons.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> Coupons
                </a>
            </li>
            <li>
                <a href="categories.php" class="<?php echo $adminCurrentPage === 'categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i> Categories
                </a>
            </li>
        </ul>

        <!-- Orders -->
        <div class="sidebar-section-title">Sales</div>
        <ul class="sidebar-nav">
            <li>
                <a href="orders.php" class="<?php echo in_array($adminCurrentPage, ['orders.php', 'order-detail.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i> Orders
                    <?php if ($pendingOrdersCount > 0): ?>
                        <span class="badge"><?php echo $pendingOrdersCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <!-- Messages -->
        <div class="sidebar-section-title">Communication</div>
        <ul class="sidebar-nav">
            <li>
                <a href="messages.php" class="<?php echo $adminCurrentPage === 'messages.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Messages
                    <?php
                    // Unread messages count
                    $unreadMsgs = 0;
                    if (isset($pdo)) {
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM contact_messages WHERE status = 'new'");
                            $unreadMsgs = (int)$stmt->fetch()['cnt'];
                        } catch (Exception $e) {
                        }
                    }
                    if ($unreadMsgs > 0):
                    ?>
                        <span class="badge"><?php echo $unreadMsgs; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="faqs.php" class="<?php echo $adminCurrentPage === 'faqs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-circle-question"></i> FAQs
                </a>
            </li>
        </ul>

        <!-- Users -->
        <div class="sidebar-section-title">Users</div>
        <ul class="sidebar-nav">
            <li>
                <a href="users.php" class="<?php echo $adminCurrentPage === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Customers
                    <span class="badge"><?php echo $totalUsersCount; ?></span>
                </a>
            </li>
        </ul>

        <!-- Settings -->
        <div class="sidebar-section-title">Account</div>
        <ul class="sidebar-nav">
            <li>
                <a href="profile.php" class="<?php echo $adminCurrentPage === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
            </li>

            <li>
                <a href="export.php" class="<?php echo $adminCurrentPage === 'export.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-csv"></i> Export Data
                </a>
            </li>

            <li>
                <a href="settings.php" class="<?php echo $adminCurrentPage === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Store Settings
                </a>
            </li>
        </ul>

        <!-- Footer -->
        <div class="sidebar-footer">
            <a href="../index.php" target="_blank" class="sidebar-view-store">
                <i class="fas fa-external-link-alt"></i> View Store
            </a>
        </div>

    </aside>

    <!-- ================= MAIN ================= -->
    <div class="admin-main">

        <!-- Topbar -->
        <div class="admin-topbar">

            <!-- Left: Toggle + Page Title -->
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="topbar-page-title"><?php echo $pageTitle ?? 'Admin Panel'; ?></h1>
                    <p class="topbar-page-subtitle"><?php echo $pageSubtitle ?? 'Welcome to FreshMart Admin'; ?></p>
                </div>
            </div>

            <!-- Right: Actions + Admin -->
            <div class="topbar-right">

                <!-- View Store -->
                <a href="../index.php" target="_blank" class="topbar-icon-btn" title="View Store">
                    <i class="fas fa-store"></i>
                </a>

                <!-- Orders Notification -->
                <a href="orders.php" class="topbar-icon-btn" title="Orders">
                    <i class="fas fa-bell"></i>
                    <?php if ($pendingOrdersCount > 0): ?>
                        <span class="badge"><?php echo $pendingOrdersCount; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Admin Dropdown -->
                <div class="admin-dropdown-wrap">
                    <div class="topbar-admin">
                        <div class="topbar-admin-avatar">
                            <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                        </div>
                        <div class="topbar-admin-info">
                            <span class="topbar-admin-name"><?php echo htmlspecialchars($adminName); ?></span>
                            <span class="topbar-admin-role">Administrator</span>
                        </div>
                    </div>
                    <div class="admin-dropdown">
                        <a href="../index.php" target="_blank">
                            <i class="fas fa-store"></i> View Store
                        </a>
                        <a href="index.php">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                        <div class="admin-dropdown-divider"></div>
                        <a href="logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>

            </div>

        </div>

        <!-- Content -->
        <div class="admin-content">