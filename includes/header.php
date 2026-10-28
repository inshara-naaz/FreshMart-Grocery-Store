<?php
// ============ SESSION START ============
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============ DATABASE CONNECTION ============
if (!isset($pdo)) {
    include_once __DIR__ . '/db.php';
}

// ============ CART FUNCTIONS ============
include_once __DIR__ . '/cart-functions.php';
// ============================================
// includes/header.php
// Ye file har page ke top pe include hogi
// Ismein: CSS + Top Ticker + Header + Navigation
// ============================================

// ============ SESSION START (agar nahi hua) ============
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============ CART COUNT FROM DATABASE ============
$cartCount = 0;
if (isset($pdo)) {
    // Agar db.php already included hai to
    include_once __DIR__ . '/cart-functions.php';
    $cartCount = getCartCount($pdo);
} else {
    // Fallback: session-based count
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += isset($item['qty']) ? (int)$item['qty'] : 1;
        }
    }
}

// Page title (agar page ne set nahi kiya to default)
$pageTitle = $pageTitle ?? 'FreshMart — Fresh Groceries Delivered';

// User name (session se — agar login ho)
$userName = $_SESSION['user_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FreshMart - Premium organic groceries delivered to your doorstep">
    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* ================= GLOBAL ================= */
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
            --accent-hover: #E65100;
            --gold: #FFB300;
            --cream: #FAF8F3;
            --light: #F5F5F5;
            --dark: #212121;
            --gray: #616161;
            --border: #E8E8E8;
            --white: #FFFFFF;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            background: var(--cream);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--dark);
        }

        a {
            text-decoration: none;
            transition: var(--transition);
        }

        img {
            max-width: 100%;
            height: auto;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* ================= TOP TICKER ================= */
        .top-ticker {
            background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: var(--white);
            padding: 8px 0;
            font-size: 13px;
            overflow: hidden;
        }

        .ticker-wrap {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .ticker-label {
            background: var(--accent);
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .ticker {
            flex: 1;
            overflow: hidden;
            position: relative;
            height: 20px;
        }

        .ticker-item {
            position: absolute;
            white-space: nowrap;
            animation: tickerScroll 30s linear infinite;
            font-weight: 500;
            padding-left: 100%;
        }

        @keyframes tickerScroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .ticker-contact {
            white-space: nowrap;
            font-weight: 500;
        }

        /* ================= HEADER ================= */
        .main-header {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .header-inner {
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 16px 0;
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .logo-icon {
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .logo-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }

        .logo-name span {
            color: var(--accent);
        }

        .logo-tag {
            font-size: 10px;
            color: var(--gray);
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Search Box */
        .search-box {
            flex: 1;
            max-width: 550px;
        }

        .search-box form {
            display: flex;
            align-items: center;
            background: var(--light);
            border: 2px solid transparent;
            border-radius: 50px;
            padding: 6px 6px 6px 20px;
            transition: var(--transition);
        }

        .search-box form:focus-within {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
        }

        .search-icon {
            color: var(--gray);
            margin-right: 10px;
            font-size: 15px;
        }

        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            outline: none;
            padding: 10px 0;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
        }

        .search-box input::placeholder {
            color: #999;
        }

        .search-box button {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-box button:hover {
            background: var(--primary-dark);
            transform: scale(1.02);
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--dark);
            font-size: 11px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 10px;
            position: relative;
            transition: var(--transition);
        }

        .action-btn i {
            font-size: 18px;
            margin-bottom: 2px;
            color: var(--gray);
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--light);
            color: var(--primary);
        }

        .action-btn:hover i {
            color: var(--primary);
            transform: translateY(-2px);
        }

        .cart-btn {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: 2px;
            right: 4px;
            background: var(--accent);
            color: var(--white);
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.15);
            }
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: var(--primary);
            color: var(--white);
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            font-size: 18px;
            cursor: pointer;
        }

        /* ================= NAVIGATION ================= */
        .main-nav {
            background: var(--primary-dark);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-list {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 0;
        }

        .nav-list>li {
            position: relative;
        }

        .nav-list>li>a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
        }

        .nav-list>li>a i {
            font-size: 13px;
        }

        .nav-list>li>a::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: var(--transition);
        }

        .nav-list>li>a:hover,
        .nav-list>li>a.active {
            color: var(--white);
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-list>li>a:hover::after,
        .nav-list>li>a.active::after {
            width: 60%;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle i.fa-chevron-down {
            font-size: 10px;
            transition: var(--transition);
        }

        .dropdown:hover .dropdown-toggle i.fa-chevron-down {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--white);
            min-width: 240px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            z-index: 100;
            display: block;
            border: none;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--dark);
            font-size: 13px;
            font-weight: 500;
        }

        .dropdown-menu li a i {
            color: var(--primary);
            font-size: 14px;
            width: 18px;
        }

        .dropdown-menu li a:hover {
            background: var(--cream);
            color: var(--primary);
            padding-left: 20px;
        }

        /* ================= HERO SECTION ================= */
        .hero-section {
            padding: 70px 0;
            background: linear-gradient(135deg, #FAF8F3 0%, #E8F5E9 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(76, 175, 80, 0.15), transparent 70%);
            border-radius: 50%;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary-dark);
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(46, 125, 50, 0.2);
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            line-height: 1.15;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-title .highlight {
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .hero-title .highlight::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            right: 0;
            height: 10px;
            background: rgba(255, 179, 0, 0.3);
            z-index: -1;
            border-radius: 5px;
        }

        .hero-text {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 30px;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary);
            color: var(--white);
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.4);
        }

        .btn-outline-custom {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            color: var(--primary-dark);
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid var(--primary);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-outline-custom:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-3px);
        }

        .btn-white-custom {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--white);
            color: var(--primary-dark);
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
        }

        .btn-white-custom:hover {
            background: var(--dark);
            color: var(--white);
            transform: translateY(-3px);
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .hero-stats .stat h3 {
            font-size: 28px;
            color: var(--primary-dark);
            margin-bottom: 2px;
            font-weight: 800;
        }

        .hero-stats .stat p {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
        }

        /* Hero Image */
        .hero-img-wrap {
            position: relative;
            padding: 20px;
        }

        .hero-img-wrap img {
            border-radius: 30px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            object-fit: cover;
            height: 500px;
        }

        .floating-card {
            position: absolute;
            background: var(--white);
            padding: 14px 18px;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: float 3s ease-in-out infinite;
        }

        .floating-card i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .floating-card strong {
            display: block;
            font-size: 13px;
            color: var(--dark);
        }

        .floating-card small {
            font-size: 11px;
            color: var(--gray);
        }

        .card-1 {
            top: 40px;
            left: -10px;
            animation-delay: 0s;
        }

        .card-2 {
            bottom: 40px;
            right: -10px;
            animation-delay: 1.5s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        /* ================= FEATURES STRIP ================= */
        .features-strip {
            padding: 50px 0;
            background: var(--white);
        }

        .feature-box {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-radius: 16px;
            transition: var(--transition);
            border: 1px solid var(--border);
            background: var(--white);
        }

        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.1), rgba(76, 175, 80, 0.15));
            color: var(--primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: var(--transition);
        }

        .feature-box:hover .feature-icon {
            background: var(--primary);
            color: var(--white);
            transform: rotate(-8deg);
        }

        .feature-box h5 {
            font-size: 15px;
            margin-bottom: 2px;
        }

        .feature-box p {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
        }

        /* ================= SECTION HEADER ================= */
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-tag {
            display: inline-block;
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            position: relative;
            padding: 0 30px;
        }

        .section-tag::before,
        .section-tag::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 20px;
            height: 1px;
            background: var(--primary);
        }

        .section-tag::before {
            left: 0;
        }

        .section-tag::after {
            right: 0;
        }

        .section-header h2 {
            font-size: clamp(1.6rem, 3vw, 2.3rem);
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .section-header p {
            color: var(--gray);
            font-size: 15px;
        }

        /* ================= CATEGORIES ================= */
        .categories-section {
            padding: 70px 0;
        }

        .category-card {
            display: block;
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .cat-img {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .cat-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .category-card:hover .cat-img img {
            transform: scale(1.12);
        }

        .cat-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(27, 94, 32, 0.7), transparent 60%);
            opacity: 0;
            transition: var(--transition);
        }

        .category-card:hover .cat-overlay {
            opacity: 1;
        }

        .cat-info {
            padding: 24px;
            text-align: center;
            position: relative;
        }

        .cat-icon {
            width: 60px;
            height: 60px;
            background: var(--cream);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin: -50px auto 15px;
            position: relative;
            border: 4px solid var(--white);
            transition: var(--transition);
        }

        .category-card:hover .cat-icon {
            background: var(--primary);
            color: var(--white);
            transform: rotate(360deg) scale(1.1);
        }

        .cat-info h4 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .cat-info>span {
            font-size: 13px;
            color: var(--gray);
            display: block;
            margin-bottom: 15px;
        }

        .cat-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-weight: 600;
            font-size: 13px;
            transition: var(--transition);
        }

        .category-card:hover .cat-link {
            gap: 14px;
            color: var(--accent);
        }

        /* ================= PRODUCTS ================= */
        .products-section {
            padding: 70px 0;
            background: var(--white);
        }

        .product-card {
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .product-img {
            position: relative;
            height: 230px;
            overflow: hidden;
            background: var(--cream);
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover .product-img img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-sale {
            background: var(--accent);
        }

        .badge-hot {
            background: #E53935;
        }

        .badge-new {
            background: var(--primary);
        }

        .product-actions {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            opacity: 0;
            transform: translateX(20px);
            transition: var(--transition);
        }

        .product-card:hover .product-actions {
            opacity: 1;
            transform: translateX(0);
        }

        .action-icon {
            width: 36px;
            height: 36px;
            background: var(--white);
            border: none;
            border-radius: 50%;
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            font-size: 13px;
        }

        .action-icon:hover {
            background: var(--primary);
            color: var(--white);
            transform: scale(1.15);
        }

        .product-info {
            padding: 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-cat {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .product-name {
            font-size: 15px;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .product-name a {
            color: var(--dark);
        }

        .product-name a:hover {
            color: var(--primary);
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 2px;
            margin-bottom: 12px;
        }

        .product-rating i {
            font-size: 12px;
            color: #DDD;
        }

        .product-rating i.filled {
            color: var(--gold);
        }

        .product-rating span {
            font-size: 11px;
            color: var(--gray);
            margin-left: 6px;
        }

        .product-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }

        .product-price {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .new-price {
            font-size: 17px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .old-price {
            font-size: 13px;
            color: var(--gray);
            text-decoration: line-through;
        }

        .add-cart-btn {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }

        .add-cart-btn:hover {
            background: var(--accent);
            transform: rotate(-15deg) scale(1.1);
            box-shadow: 0 6px 16px rgba(255, 111, 0, 0.4);
        }

        /* ================= OFFER BANNER ================= */
        .offer-banner {
            padding: 70px 0;
        }

        .offer-inner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            border-radius: 25px;
            padding: 50px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .offer-inner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: 250px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.2), transparent 70%);
            border-radius: 50%;
        }

        .offer-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
        }

        .offer-inner h2 {
            color: var(--white);
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .offer-inner h2 span {
            color: var(--gold);
        }

        .offer-inner p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 25px;
            font-size: 15px;
        }

        .offer-inner p strong {
            color: var(--gold);
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 10px;
            border-radius: 5px;
        }

        .offer-inner img {
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            max-width: 350px;
        }

        /* ================= WHY CHOOSE US ================= */
        .why-section {
            padding: 70px 0;
            background: var(--white);
        }

        .why-card {
            text-align: center;
            padding: 35px 20px;
            border-radius: 20px;
            background: var(--cream);
            transition: var(--transition);
            height: 100%;
            border: 2px solid transparent;
        }

        .why-card:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: translateY(-8px);
            box-shadow: var(--shadow-md);
        }

        .why-icon {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
            transition: var(--transition);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.25);
        }

        .why-card:hover .why-icon {
            transform: rotate(-10deg) scale(1.1);
            background: linear-gradient(135deg, var(--accent), #FF9800);
        }

        .why-card h4 {
            font-size: 17px;
            margin-bottom: 10px;
        }

        .why-card p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
        }

        /* ================= NEWSLETTER ================= */
        .newsletter-section {
            padding: 0 0 70px;
            background: var(--white);
        }

        .newsletter-box {
            background: linear-gradient(135deg, var(--cream) 0%, #E8F5E9 100%);
            padding: 45px;
            border-radius: 25px;
            border: 1px solid var(--border);
        }

        .newsletter-box h3 {
            font-size: 22px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .newsletter-box h3 i {
            color: var(--primary);
        }

        .newsletter-box p {
            color: var(--gray);
            margin: 0;
            font-size: 14px;
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 15px 22px;
            border: 2px solid var(--border);
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            outline: none;
            transition: var(--transition);
            background: var(--white);
        }

        .newsletter-form input:focus {
            border-color: var(--primary);
        }

        .newsletter-form button {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            white-space: nowrap;
        }

        .newsletter-form button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* ================= FOOTER ================= */
        .main-footer {
            background: #1A1A1A;
            color: rgba(255, 255, 255, 0.8);
            padding: 60px 0 0;
        }

        .footer-top {
            padding-bottom: 40px;
        }

        .footer-logo .logo-name {
            color: var(--white);
        }

        .footer-logo .logo-tag {
            color: rgba(255, 255, 255, 0.5);
        }

        .footer-about p {
            font-size: 13px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.6);
            margin: 20px 0;
        }

        .social-links {
            display: flex;
            gap: 10px;
        }

        .social-links a {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            transition: var(--transition);
        }

        .social-links a:hover {
            transform: translateY(-4px) scale(1.05);
            color: var(--white);
        }

        .social-fb:hover {
            background: #1877F2;
        }

        .social-ig:hover {
            background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF);
        }

        .social-tw:hover {
            background: #1DA1F2;
        }

        .social-wa:hover {
            background: #25D366;
        }

        .social-yt:hover {
            background: #FF0000;
        }

        .footer-title {
            color: var(--white);
            font-size: 16px;
            margin-bottom: 22px;
            position: relative;
            padding-bottom: 12px;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 35px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .footer-links a i {
            font-size: 10px;
            color: var(--accent);
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
            padding-left: 5px;
        }

        .footer-contact li {
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
        }

        .footer-contact li i {
            color: var(--accent);
            font-size: 15px;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
        }

        .footer-bottom-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .footer-bottom-inner p {
            margin: 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        .footer-bottom-inner p strong {
            color: var(--white);
        }

        .footer-bottom-inner p i {
            color: #E53935;
        }

        .payment-icons {
            display: flex;
            gap: 12px;
            font-size: 26px;
            color: rgba(255, 255, 255, 0.4);
        }

        .payment-icons i:hover {
            color: var(--white);
            transition: var(--transition);
        }

        /* ================= BACK TO TOP ================= */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: var(--transition);
            z-index: 999;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: var(--accent);
            transform: translateY(-5px);
        }

        /* ================= WHATSAPP FLOAT ================= */
        .whatsapp-float {
            position: fixed;
            bottom: 90px;
            right: 30px;
            width: 52px;
            height: 52px;
            background: #25D366;
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
            z-index: 999;
            animation: waPulse 2s infinite;
        }

        .whatsapp-float:hover {
            color: var(--white);
            transform: scale(1.1);
        }

        @keyframes waPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.5);
            }

            50% {
                box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
            }
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 991px) {
            .header-inner {
                flex-wrap: wrap;
            }

            .search-box {
                order: 3;
                flex-basis: 100%;
                max-width: 100%;
                margin-top: 10px;
            }

            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .main-nav {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                background: var(--primary-dark);
                transition: var(--transition);
                z-index: 1100;
                padding-top: 70px;
                overflow-y: auto;
            }

            .main-nav.open {
                left: 0;
                box-shadow: var(--shadow-lg);
            }

            .nav-list {
                flex-direction: column;
                align-items: stretch;
                padding: 10px;
            }

            .nav-list>li>a {
                padding: 14px 18px;
                border-radius: 8px;
            }

            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                background: rgba(0, 0, 0, 0.2);
                margin: 0 10px;
                min-width: auto;
                display: none;
            }

            .dropdown.active .dropdown-menu {
                display: block;
            }

            .dropdown-menu li a {
                color: rgba(255, 255, 255, 0.85);
            }

            .dropdown-menu li a i {
                color: var(--gold);
            }

            .dropdown-menu li a:hover {
                background: rgba(255, 255, 255, 0.1);
                color: var(--white);
            }

            .hero-img-wrap img {
                height: 380px;
            }

            .floating-card {
                padding: 10px 14px;
            }

            .floating-card i {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }

            .offer-inner {
                padding: 35px 25px;
            }

            .newsletter-box {
                padding: 30px 25px;
            }

            .newsletter-form {
                flex-direction: column;
                margin-top: 20px;
            }

            .newsletter-form button {
                justify-content: center;
            }
        }

        @media (max-width: 767px) {
            .ticker-contact {
                display: none;
            }

            .hero-section {
                padding: 40px 0;
            }

            .hero-stats {
                gap: 25px;
            }

            .hero-stats .stat h3 {
                font-size: 22px;
            }

            .hero-img-wrap {
                margin-top: 40px;
            }

            .hero-img-wrap img {
                height: 300px;
            }

            .card-1 {
                top: 10px;
                left: 5px;
            }

            .card-2 {
                bottom: 10px;
                right: 5px;
            }

            .section-header {
                margin-bottom: 35px;
            }

            .offer-inner {
                text-align: center;
            }

            .offer-inner img {
                margin-top: 25px;
                max-width: 250px;
            }

            .footer-bottom-inner {
                justify-content: center;
                text-align: center;
            }

            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 42px;
                height: 42px;
            }

            .whatsapp-float {
                bottom: 75px;
                right: 20px;
                width: 46px;
                height: 46px;
                font-size: 20px;
            }

            .product-actions {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 480px) {
            .logo-name {
                font-size: 18px;
            }

            .logo-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .action-btn span {
                display: none;
            }

            .action-btn {
                padding: 8px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-buttons a {
                justify-content: center;
            }
        }


        /* Product CSS */
        /* ================= BREADCRUMB ================= */
        .breadcrumb-section {
            background: var(--white);
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            flex-wrap: wrap;
        }

        .breadcrumb-nav a {
            color: var(--gray);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .breadcrumb-nav a:hover {
            color: var(--primary);
        }

        .breadcrumb-nav i.separator {
            color: #BBB;
            font-size: 10px;
        }

        .breadcrumb-nav span.current {
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* ================= PRODUCT DETAIL ================= */
        .product-detail-section {
            padding: 50px 0;
            background: var(--white);
        }

        .product-gallery {
            position: sticky;
            top: 100px;
        }

        .main-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: var(--cream);
            margin-bottom: 15px;
            box-shadow: var(--shadow-sm);
        }

        .main-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .main-image:hover img {
            transform: scale(1.05);
        }

        .thumbnail-list {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .thumb {
            width: 85px;
            height: 85px;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
            background: var(--cream);
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumb:hover {
            border-color: var(--primary-light);
            transform: translateY(-3px);
        }

        .thumb.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
        }

        .product-detail-info {
            padding-top: 10px;
        }

        .product-cat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary-dark);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .product-detail-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .product-detail-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--border);
        }

        .product-detail-rating .stars i {
            font-size: 14px;
            color: #DDD;
        }

        .product-detail-rating .stars i.filled {
            color: var(--gold);
        }

        .rating-text {
            font-weight: 700;
            color: var(--dark);
            font-size: 14px;
        }

        .reviews-link {
            color: var(--gray);
            font-size: 13px;
        }

        .stock-status {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            margin-left: auto;
        }

        .stock-status i {
            font-size: 12px;
        }

        .product-detail-price {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .current-price {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .per-unit {
            font-size: 16px;
            color: var(--gray);
        }

        .old-price {
            font-size: 18px;
            color: var(--gray);
            text-decoration: line-through;
        }

        .discount-badge {
            background: #E53935;
            color: var(--white);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
        }

        .product-short-desc {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.8;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px dashed var(--border);
        }

        /* Quantity Selector */
        .quantity-section {
            margin-bottom: 25px;
        }

        .quantity-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--dark);
        }

        .quantity-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .qty-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            background: var(--white);
            min-width: 85px;
            position: relative;
        }

        .qty-option input {
            display: none;
        }

        .qty-option:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }

        .qty-option.active {
            border-color: var(--primary);
            background: rgba(46, 125, 50, 0.05);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.15);
        }

        .qty-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
        }

        .qty-price {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            margin-top: 3px;
        }

        /* Actions Row */
        .product-actions-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .qty-input {
            display: flex;
            align-items: center;
            border: 2px solid var(--border);
            border-radius: 50px;
            overflow: hidden;
            height: 54px;
        }

        .qty-btn {
            width: 45px;
            height: 100%;
            background: transparent;
            border: none;
            color: var(--dark);
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }

        .qty-btn:hover {
            background: var(--cream);
            color: var(--primary);
        }

        .qty-input input {
            width: 50px;
            height: 100%;
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            outline: none;
            background: transparent;
            color: var(--dark);
        }

        .btn-add-cart-large {
            flex: 1;
            height: 54px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
            min-width: 200px;
        }

        .btn-add-cart-large:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(46, 125, 50, 0.4);
        }

        .btn-add-cart-large:active {
            transform: translateY(0) scale(0.98);
        }

        /* Secondary Actions */
        .secondary-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .btn-secondary-action {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--dark);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary-action:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(46, 125, 50, 0.05);
        }

        /* Meta */
        .product-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 20px;
            background: var(--cream);
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            gap: 8px;
            font-size: 13px;
            align-items: baseline;
        }

        .meta-label {
            color: var(--gray);
            font-weight: 600;
            min-width: 70px;
        }

        .meta-value {
            color: var(--dark);
            font-weight: 500;
        }

        .meta-value i {
            color: var(--primary);
            font-size: 12px;
        }

        /* Share */
        .product-share {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--gray);
        }

        .share-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light);
            color: var(--gray);
            font-size: 13px;
            transition: var(--transition);
        }

        .share-icon:hover {
            transform: translateY(-3px);
            color: var(--white);
        }

        .share-fb:hover {
            background: #1877F2;
        }

        .share-tw:hover {
            background: #1DA1F2;
        }

        .share-wa:hover {
            background: #25D366;
        }

        .share-pt:hover {
            background: #E60023;
        }

        /* ================= TABS ================= */
        .product-tabs-section {
            padding: 60px 0;
            background: var(--cream);
        }

        .tabs-wrapper {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .tabs-header {
            display: flex;
            border-bottom: 2px solid var(--border);
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 20px 30px;
            background: transparent;
            border: none;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray);
            cursor: pointer;
            position: relative;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--primary);
            background: var(--cream);
        }

        .tab-btn.active {
            color: var(--primary-dark);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
        }

        .tab-content {
            padding: 35px;
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-content h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .tab-content p {
            color: var(--gray);
            line-height: 1.9;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .features-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 15px;
        }

        .features-list li {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 12px 15px;
            background: var(--cream);
            border-radius: 10px;
            font-size: 14px;
            color: var(--dark);
        }

        .features-list li i {
            color: var(--primary);
            font-size: 16px;
        }

        /* Reviews */
        .reviews-summary {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 40px;
            padding-bottom: 30px;
            border-bottom: 1px dashed var(--border);
            margin-bottom: 30px;
        }

        .summary-score {
            text-align: center;
        }

        .summary-score h2 {
            font-size: 56px;
            color: var(--primary-dark);
            font-weight: 800;
            line-height: 1;
            margin-bottom: 8px;
        }

        .summary-score .stars i {
            color: var(--gold);
            font-size: 18px;
        }

        .summary-score p {
            color: var(--gray);
            font-size: 13px;
            margin-top: 8px;
        }

        .summary-bars {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
        }

        .bar-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        .bar-label {
            min-width: 45px;
            color: var(--dark);
            font-weight: 600;
        }

        .bar-label i {
            color: var(--gold);
            font-size: 11px;
        }

        .bar-track {
            flex: 1;
            height: 8px;
            background: var(--border);
            border-radius: 50px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: var(--gold);
            border-radius: 50px;
            transition: width 1s ease;
        }

        .bar-percent {
            min-width: 40px;
            color: var(--gray);
            font-size: 12px;
            text-align: right;
        }

        .reviews-list h3 {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .review-card {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: var(--cream);
            border-radius: 14px;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .review-card:hover {
            background: #F5F1E8;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .review-body {
            flex: 1;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .review-header h5 {
            font-size: 15px;
            margin-bottom: 2px;
        }

        .review-date {
            font-size: 12px;
            color: var(--gray);
        }

        .review-stars i {
            font-size: 12px;
            color: #DDD;
        }

        .review-stars i.filled {
            color: var(--gold);
        }

        .review-text {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.7;
            margin: 0;
        }

        .review-form-wrap {
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px dashed var(--border);
        }

        .review-form-wrap h4 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .rating-input {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 600;
        }

        .stars-input {
            display: flex;
            gap: 4px;
        }

        .stars-input i {
            font-size: 22px;
            color: #DDD;
            cursor: pointer;
            transition: var(--transition);
        }

        .stars-input i:hover,
        .stars-input i.active {
            color: var(--gold);
            transform: scale(1.15);
        }

        .review-form input,
        .review-form textarea {
            width: 100%;
            padding: 12px 18px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            outline: none;
            transition: var(--transition);
            background: var(--white);
            color: var(--dark);
        }

        .review-form input:focus,
        .review-form textarea:focus {
            border-color: var(--primary);
        }

        /* Related Products */
        .related-products-section {
            padding: 70px 0;
            background: var(--white);
        }

        .per-unit-small {
            font-size: 11px;
            color: var(--gray);
            margin-left: 2px;
        }

        /* Responsive Product Detail */
        @media (max-width: 991px) {
            .product-gallery {
                position: static;
                margin-bottom: 30px;
            }

            .main-image img {
                height: 400px;
            }

            .reviews-summary {
                grid-template-columns: 1fr;
                gap: 25px;
                text-align: center;
            }

            .summary-score h2 {
                font-size: 44px;
            }

            .features-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .main-image img {
                height: 300px;
            }

            .thumb {
                width: 65px;
                height: 65px;
            }

            .current-price {
                font-size: 26px;
            }

            .product-meta {
                grid-template-columns: 1fr;
            }

            .tabs-header {
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .tab-btn {
                padding: 15px 18px;
                white-space: nowrap;
                font-size: 13px;
            }

            .tab-content {
                padding: 25px 20px;
            }

            .product-actions-row {
                flex-direction: column;
                align-items: stretch;
            }

            .qty-input {
                justify-content: center;
            }

            .btn-add-cart-large {
                min-width: auto;
            }

            .stock-status {
                margin-left: 0;
                width: 100%;
            }

            .review-card {
                flex-direction: column;
            }
        }


        /* ================= SHOP PAGE HEADER ================= */
        .shop-page-header {
            background: var(--white);
            padding: 20px 0 25px;
            border-bottom: 1px solid var(--border);
        }

        .shop-header-content {
            margin-top: 15px;
        }

        .shop-header-content h1 {
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .shop-header-content p {
            color: var(--gray);
            font-size: 14px;
            margin: 0;
        }

        .shop-header-content p strong {
            color: var(--primary);
        }

        /* ================= SHOP SECTION ================= */
        .shop-section {
            padding: 40px 0 70px;
            background: var(--cream);
        }

        /* Filter Toggle (mobile) */
        .filter-toggle-mobile {
            display: none;
            width: 100%;
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 20px;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* ================= SIDEBAR ================= */
        .shop-sidebar {
            background: var(--white);
            border-radius: 16px;
            padding: 22px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 100px;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 18px;
        }

        .sidebar-header h3 {
            font-size: 17px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .sidebar-header h3 i {
            color: var(--primary);
            font-size: 14px;
        }

        .clear-filters {
            font-size: 12px;
            color: var(--accent);
            font-weight: 600;
        }

        .clear-filters:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        /* Active Filters */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 10px;
            background: var(--cream);
            border-radius: 10px;
            margin-bottom: 18px;
            align-items: center;
        }

        .active-label {
            font-size: 11px;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--white);
            color: var(--primary-dark);
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid var(--primary);
        }

        .filter-chip a {
            color: var(--primary);
            font-weight: 700;
            font-size: 14px;
            line-height: 1;
        }

        .filter-chip a:hover {
            color: #E53935;
        }

        /* Filter Group */
        .filter-group {
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .filter-group:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .filter-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-list li {
            margin-bottom: 2px;
        }

        .filter-list a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--gray);
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .filter-list a i:first-child {
            color: var(--primary);
            font-size: 13px;
            width: 18px;
        }

        .filter-list a:hover {
            background: var(--cream);
            color: var(--primary-dark);
            padding-left: 16px;
        }

        .filter-list a.active {
            background: rgba(46, 125, 50, 0.08);
            color: var(--primary-dark);
            font-weight: 600;
        }

        .filter-list a .count {
            margin-left: auto;
            background: var(--light);
            color: var(--gray);
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 600;
        }

        .filter-list a.active .count {
            background: var(--primary);
            color: var(--white);
        }

        /* Rating Filter */
        .rating-filter a {
            padding: 6px 12px;
        }

        .rating-filter .stars i {
            font-size: 12px;
            color: #DDD;
            margin-right: 1px;
        }

        .rating-filter .stars i.filled {
            color: var(--gold);
        }

        .rating-filter .rating-text {
            font-size: 12px;
            color: var(--gray);
            margin-left: 8px;
        }

        /* Price Inputs */
        .price-inputs {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }

        .price-input {
            flex: 1;
        }

        .price-input label {
            display: block;
            font-size: 11px;
            color: var(--gray);
            margin-bottom: 5px;
            font-weight: 600;
        }

        .price-input input {
            width: 100%;
            padding: 9px 12px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
            transition: var(--transition);
            color: var(--dark);
        }

        .price-input input:focus {
            border-color: var(--primary);
        }

        .btn-apply-filter {
            width: 100%;
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .btn-apply-filter:hover {
            background: var(--primary-dark);
        }

        /* ================= SHOP TOOLBAR ================= */
        .shop-toolbar {
            background: var(--white);
            border-radius: 14px;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-count {
            font-size: 13px;
            color: var(--gray);
        }

        .results-count strong {
            color: var(--dark);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .view-toggle {
            display: flex;
            gap: 4px;
            background: var(--light);
            padding: 4px;
            border-radius: 10px;
        }

        .view-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 13px;
            transition: var(--transition);
        }

        .view-btn:hover {
            color: var(--primary);
        }

        .view-btn.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .sort-box {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }

        .sort-box label {
            color: var(--gray);
            white-space: nowrap;
            font-weight: 500;
        }

        .sort-box select {
            padding: 9px 35px 9px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: var(--dark);
            cursor: pointer;
            outline: none;
            transition: var(--transition);
            background: var(--white);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23616161' stroke-width='3'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }

        .sort-box select:focus {
            border-color: var(--primary);
        }

        /* ================= LIST VIEW ================= */
        .list-view .product-card-list {
            display: flex;
            flex-direction: row;
            height: auto;
        }

        .list-view .product-card-list .product-img {
            width: 280px;
            height: auto;
            min-height: 240px;
            flex-shrink: 0;
        }

        .list-view .product-card-list .product-info {
            padding: 25px 30px;
            justify-content: center;
        }

        .list-view .product-card-list .product-name {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .list-view .product-list-desc {
            font-size: 13px;
            color: var(--gray);
            line-height: 1.7;
            margin-bottom: 12px;
            display: block;
        }

        .list-view .product-card-list .product-bottom {
            margin-top: 15px;
        }

        .list-view .product-card-list .new-price {
            font-size: 22px;
        }

        /* ================= NO PRODUCTS ================= */
        .no-products {
            text-align: center;
            padding: 80px 20px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
        }

        .no-products i {
            font-size: 60px;
            color: var(--border);
            margin-bottom: 20px;
        }

        .no-products h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .no-products p {
            color: var(--gray);
            margin-bottom: 25px;
            font-size: 14px;
        }

        /* ================= PAGINATION ================= */
        .pagination-wrap {
            text-align: center;
            margin-top: 50px;
        }

        .pagination {
            display: inline-flex;
            gap: 8px;
            padding: 0;
            list-style: none;
        }

        .pagination a {
            min-width: 44px;
            height: 44px;
            padding: 0 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            color: var(--dark);
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .pagination a:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
        }

        .pagination a.active {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
        }

        /* ================= SHOP RESPONSIVE ================= */
        @media (max-width: 991px) {
            .filter-toggle-mobile {
                display: flex;
            }

            .shop-sidebar {
                display: none;
                position: static;
                margin-bottom: 25px;
            }

            .shop-sidebar.open {
                display: block;
                animation: fadeIn 0.3s ease;
            }

            .list-view .product-card-list {
                flex-direction: column;
            }

            .list-view .product-card-list .product-img {
                width: 100%;
                height: 220px;
                min-height: auto;
            }

            .list-view .product-card-list .product-info {
                padding: 20px;
            }
        }

        @media (max-width: 767px) {
            .shop-toolbar {
                padding: 12px 15px;
            }

            .toolbar-left {
                width: 100%;
            }

            .toolbar-right {
                width: 100%;
                justify-content: space-between;
            }

            .sort-box label {
                display: none;
            }

            .pagination a {
                min-width: 38px;
                height: 38px;
                padding: 0 10px;
                font-size: 13px;
            }

            .product-actions {
                opacity: 1;
                transform: translateX(0);
            }
        }


        /* ================= PAGE HERO (About, Contact, etc.) ================= */
        .page-hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 50px 0 60px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.15), transparent 70%);
            border-radius: 50%;
        }

        .breadcrumb-light a,
        .breadcrumb-light span.current {
            color: rgba(255, 255, 255, 0.9);
        }

        .breadcrumb-light a:hover {
            color: var(--gold);
        }

        .breadcrumb-light i.separator {
            color: rgba(255, 255, 255, 0.5);
        }

        .page-hero-content {
            text-align: center;
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }

        .page-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
        }

        .page-hero-content h1 {
            color: var(--white);
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 10px;
        }

        .page-hero-content h1 .highlight {
            color: var(--gold);
        }

        .page-hero-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
        }

        /* ================= STORY SECTION ================= */
        .story-section {
            padding: 80px 0;
            background: var(--white);
        }

        .story-images {
            position: relative;
            padding: 20px;
        }

        .story-img-main {
            width: 100%;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            height: 480px;
            object-fit: cover;
        }

        .story-img-small {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 20px;
            border: 6px solid var(--white);
            box-shadow: var(--shadow-lg);
        }

        .story-experience {
            position: absolute;
            top: 40px;
            right: 0;
            background: linear-gradient(135deg, var(--accent), #FF9800);
            color: var(--white);
            padding: 20px 22px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(255, 111, 0, 0.4);
            animation: float 3s ease-in-out infinite;
        }

        .story-experience h3 {
            font-size: 32px;
            color: var(--white);
            margin-bottom: 2px;
            font-weight: 800;
            line-height: 1;
        }

        .story-experience p {
            font-size: 11px;
            margin: 0;
            line-height: 1.3;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-tag-left {
            display: inline-block;
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
            position: relative;
            padding-left: 30px;
        }

        .section-tag-left::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 20px;
            height: 2px;
            background: var(--primary);
        }

        .story-title {
            font-size: clamp(1.6rem, 3vw, 2.3rem);
            margin-bottom: 20px;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .story-title .highlight-green {
            color: var(--primary);
        }

        .story-text {
            font-size: 15px;
            color: var(--gray);
            line-height: 1.9;
            margin-bottom: 15px;
        }

        .story-text strong {
            color: var(--primary-dark);
        }

        .story-list {
            margin: 25px 0;
            padding: 0;
        }

        .story-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            font-size: 14px;
            color: var(--dark);
            font-weight: 500;
        }

        .story-list li i {
            color: var(--primary);
            font-size: 16px;
            flex-shrink: 0;
        }

        /* ================= STATS COUNTER ================= */
        .stats-section {
            padding: 70px 0;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.15), transparent 70%);
            border-radius: 50%;
        }

        .stat-box {
            text-align: center;
            padding: 30px 15px;
            color: var(--white);
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .stat-box:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .stat-box i {
            font-size: 38px;
            color: var(--gold);
            margin-bottom: 15px;
            display: block;
        }

        .stat-box h2 {
            font-size: 42px;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-box p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* ================= MISSION & VISION ================= */
        .mission-section {
            padding: 80px 0;
            background: var(--cream);
        }

        .mv-card {
            background: var(--white);
            border-radius: 24px;
            padding: 40px 35px;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .mv-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .mv-mission::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(46, 125, 50, 0.1), transparent 70%);
            border-radius: 50%;
        }

        .mv-vision::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(255, 111, 0, 0.1), transparent 70%);
            border-radius: 50%;
        }

        .mv-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--white);
            margin-bottom: 22px;
            position: relative;
            z-index: 1;
        }

        .mv-mission .mv-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.3);
        }

        .mv-vision .mv-icon {
            background: linear-gradient(135deg, var(--accent), #FF9800);
            box-shadow: 0 10px 25px rgba(255, 111, 0, 0.3);
        }

        .mv-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .mv-card p {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.9;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .mv-card p strong {
            color: var(--primary-dark);
        }

        .mv-list {
            margin: 0;
            padding: 0;
            position: relative;
            z-index: 1;
        }

        .mv-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 13px;
            color: var(--dark);
            font-weight: 500;
        }

        .mv-list li i {
            color: var(--accent);
            font-size: 11px;
        }

        /* ================= VALUES ================= */
        .values-section {
            padding: 80px 0;
            background: var(--white);
        }

        .value-card {
            text-align: center;
            padding: 35px 22px;
            border-radius: 20px;
            background: var(--cream);
            height: 100%;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .value-card:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: translateY(-8px);
            box-shadow: var(--shadow-md);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
            transition: var(--transition);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.25);
        }

        .value-card:hover .value-icon {
            transform: rotate(-10deg) scale(1.1);
            background: linear-gradient(135deg, var(--accent), #FF9800);
            box-shadow: 0 10px 25px rgba(255, 111, 0, 0.3);
        }

        .value-card h4 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .value-card p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
            line-height: 1.7;
        }

        /* ================= TEAM ================= */
        .team-section {
            padding: 80px 0;
            background: var(--cream);
        }

        .team-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            height: 100%;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .team-img {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .team-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .team-card:hover .team-img img {
            transform: scale(1.1);
        }

        .team-social {
            position: absolute;
            bottom: -60px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: linear-gradient(to top, rgba(27, 94, 32, 0.9), transparent);
            transition: var(--transition);
        }

        .team-card:hover .team-social {
            bottom: 0;
        }

        .team-social a {
            width: 38px;
            height: 38px;
            background: var(--white);
            color: var(--primary-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: var(--transition);
        }

        .team-social a:hover {
            background: var(--accent);
            color: var(--white);
            transform: translateY(-3px);
        }

        .team-info {
            padding: 22px;
            text-align: center;
        }

        .team-info h4 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .team-info span {
            font-size: 13px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ================= TESTIMONIALS ================= */
        .testimonials-section {
            padding: 80px 0;
            background: var(--white);
        }

        .testimonial-card {
            background: var(--cream);
            border-radius: 20px;
            padding: 35px 30px;
            height: 100%;
            position: relative;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .testimonial-card:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: translateY(-8px);
            box-shadow: var(--shadow-md);
        }

        .quote-icon {
            font-size: 36px;
            color: var(--primary);
            opacity: 0.15;
            margin-bottom: 15px;
            line-height: 1;
        }

        .testimonial-text {
            font-size: 14px;
            color: var(--dark);
            line-height: 1.9;
            margin-bottom: 18px;
            font-style: italic;
        }

        .testimonial-rating {
            margin-bottom: 20px;
        }

        .testimonial-rating i {
            color: var(--gold);
            font-size: 14px;
            margin-right: 2px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
        }

        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
        }

        .testimonial-author h5 {
            font-size: 15px;
            margin-bottom: 2px;
        }

        .testimonial-author span {
            font-size: 12px;
            color: var(--gray);
        }

        /* ================= CTA SECTION ================= */
        .cta-section {
            padding: 70px 0;
            background: var(--white);
        }

        .cta-inner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            border-radius: 28px;
            padding: 70px 50px;
            text-align: center;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .cta-inner::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.25), transparent 70%);
            border-radius: 50%;
        }

        .cta-inner::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(76, 175, 80, 0.3), transparent 70%);
            border-radius: 50%;
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-content h2 {
            color: var(--white);
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .cta-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin-bottom: 30px;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-outline-white-custom {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            color: var(--white);
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            transition: var(--transition);
        }

        .btn-outline-white-custom:hover {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            border-color: var(--white);
            transform: translateY(-3px);
        }

        /* ================= ABOUT RESPONSIVE ================= */
        @media (max-width: 991px) {
            .story-img-main {
                height: 380px;
            }

            .story-img-small {
                width: 150px;
                height: 150px;
            }

            .story-experience {
                padding: 15px 18px;
            }

            .story-experience h3 {
                font-size: 26px;
            }

            .page-hero {
                padding: 40px 0 50px;
            }

            .cta-inner {
                padding: 50px 30px;
            }
        }

        @media (max-width: 767px) {
            .story-images {
                margin-bottom: 40px;
            }

            .story-img-main {
                height: 300px;
            }

            .story-img-small {
                width: 120px;
                height: 120px;
                border-width: 4px;
            }

            .story-experience {
                top: 20px;
                right: -10px;
            }

            .story-experience h3 {
                font-size: 22px;
            }

            .story-experience p {
                font-size: 10px;
            }

            .stat-box {
                padding: 25px 12px;
            }

            .stat-box i {
                font-size: 30px;
            }

            .stat-box h2 {
                font-size: 32px;
            }

            .mv-card {
                padding: 30px 25px;
            }

            .team-img {
                height: 260px;
            }

            .cta-buttons a {
                width: 100%;
                justify-content: center;
            }
        }


        /* ================= CONTACT HERO ================= */
        .contact-hero {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 50%, #4CAF50 100%);
            padding: 50px 0 70px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.2), transparent 70%);
            border-radius: 50%;
        }

        .contact-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 111, 0, 0.15), transparent 70%);
            border-radius: 50%;
        }

        .contact-hero-content {
            text-align: center;
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }

        .contact-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 22px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .contact-hero-content h1 {
            color: var(--white);
            font-size: clamp(2rem, 5vw, 3.2rem);
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .contact-hero-content h1 .highlight {
            color: var(--gold);
        }

        .contact-hero-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ================= CONTACT INFO CARDS ================= */
        .contact-info-section {
            padding: 0 0 60px;
            background: var(--cream);
            margin-top: -40px;
            position: relative;
            z-index: 2;
        }

        .contact-info-card {
            background: var(--white);
            border-radius: 20px;
            padding: 35px 25px;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            height: 100%;
            border-top: 4px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .contact-info-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-top-color: var(--primary);
        }

        .ci-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--white);
            margin: 0 auto 20px;
            transition: var(--transition);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .contact-info-card:hover .ci-icon {
            transform: rotate(-10deg) scale(1.1);
        }

        .ci-phone {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
        }

        .ci-email {
            background: linear-gradient(135deg, #FF6F00, #E65100);
        }

        .ci-location {
            background: linear-gradient(135deg, #E53935, #C62828);
        }

        .ci-chat {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .contact-info-card h4 {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .contact-info-card>p {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contact-info-card a,
        .contact-info-card>span {
            display: block;
            color: var(--dark);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
            transition: var(--transition);
        }

        .contact-info-card a:hover {
            color: var(--primary);
        }

        .ci-whatsapp-btn {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            background: #25D366;
            color: var(--white) !important;
            padding: 10px 18px;
            border-radius: 50px;
            font-size: 12px !important;
            margin-top: 5px;
        }

        .ci-whatsapp-btn:hover {
            background: #128C7E;
            color: var(--white) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
        }

        /* ================= CONTACT FORM + MAP ================= */
        .contact-main-section {
            padding: 30px 0 80px;
            background: var(--cream);
        }

        .contact-form-wrap {
            background: var(--white);
            border-radius: 24px;
            padding: 45px 40px;
            box-shadow: var(--shadow-md);
            height: 100%;
            position: relative;
        }

        .contact-form-wrap h2 {
            font-size: clamp(1.4rem, 3vw, 1.9rem);
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .form-subtitle {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 30px;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 5px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-group .req {
            color: #E53935;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 13px 18px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--dark);
            outline: none;
            transition: var(--transition);
            background: var(--white);
            resize: vertical;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #AAA;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.08);
        }

        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23616161' stroke-width='3'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            cursor: pointer;
        }

        .btn-submit-contact {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 16px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-submit-contact:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(46, 125, 50, 0.4);
        }

        /* Success Message */
        .contact-success {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--white);
            border-radius: 24px;
            padding: 60px 40px;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            z-index: 10;
        }

        .contact-success.show {
            display: flex;
            animation: fadeIn 0.4s ease;
        }

        .success-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 25px;
            animation: pulse-success 1s ease infinite alternate;
        }

        @keyframes pulse-success {
            from {
                box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.4);
            }

            to {
                box-shadow: 0 0 0 20px rgba(46, 125, 50, 0);
            }
        }

        .contact-success h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .contact-success p {
            color: var(--gray);
            margin-bottom: 25px;
            font-size: 14px;
        }

        .contact-success button {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .contact-success button:hover {
            background: var(--primary-dark);
        }

        /* Map Wrapper */
        .map-wrapper {
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .map-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--border);
        }

        .map-header h4 {
            font-size: 17px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .map-header h4 i {
            color: var(--primary);
        }

        .map-header p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
        }

        .map-embed {
            flex: 1;
            min-height: 350px;
            background: var(--light);
        }

        .map-embed iframe {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 350px;
        }

        .map-footer {
            padding: 20px 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            border-top: 1px solid var(--border);
            background: var(--cream);
        }

        .map-info-item {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 13px;
        }

        .map-info-item i {
            width: 38px;
            height: 38px;
            background: var(--white);
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .map-info-item strong {
            display: block;
            font-size: 13px;
            color: var(--dark);
        }

        .map-info-item span {
            font-size: 12px;
            color: var(--gray);
        }

        /* ================= STORE LOCATIONS ================= */
        .locations-section {
            padding: 80px 0;
            background: var(--white);
        }

        .location-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid var(--border);
            height: 100%;
        }

        .location-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .location-img {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .location-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .location-card:hover .location-img img {
            transform: scale(1.1);
        }

        .location-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary);
            color: var(--white);
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .location-info {
            padding: 25px;
        }

        .location-info h4 {
            font-size: 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .location-info h4 i {
            color: var(--accent);
            font-size: 16px;
        }

        .location-info p {
            font-size: 13px;
            color: var(--gray);
            line-height: 1.7;
            margin-bottom: 18px;
        }

        .location-actions {
            display: flex;
            gap: 10px;
        }

        .location-actions a {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px;
            background: var(--cream);
            color: var(--primary-dark);
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            transition: var(--transition);
        }

        .location-actions a:hover {
            background: var(--primary);
            color: var(--white);
        }

        /* ================= FAQ ================= */
        .faq-section {
            padding: 80px 0;
            background: var(--cream);
        }

        .faq-wrapper {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .faq-item {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .faq-item.active {
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.1);
        }

        .faq-question {
            width: 100%;
            padding: 22px 28px;
            background: transparent;
            border: none;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
        }

        .faq-question:hover {
            color: var(--primary);
        }

        .faq-question i {
            width: 32px;
            height: 32px;
            background: var(--cream);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .faq-item.active .faq-question i {
            background: var(--primary);
            color: var(--white);
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 300px;
        }

        .faq-answer p {
            padding: 0 28px 25px;
            margin: 0;
            font-size: 14px;
            color: var(--gray);
            line-height: 1.9;
        }

        /* ================= SOCIAL CTA ================= */
        .social-cta-section {
            padding: 60px 0 80px;
            background: var(--white);
        }

        .social-cta-inner {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            border-radius: 28px;
            padding: 60px 50px;
            text-align: center;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .social-cta-inner::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.2), transparent 70%);
            border-radius: 50%;
        }

        .social-cta-content {
            position: relative;
            z-index: 1;
        }

        .social-cta-content h2 {
            color: var(--white);
            font-size: clamp(1.5rem, 3.5vw, 2.2rem);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .social-cta-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            margin-bottom: 35px;
        }

        .social-cta-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .social-big-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 28px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            font-size: 14px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .social-big-btn i {
            font-size: 18px;
        }

        .social-big-btn:hover {
            transform: translateY(-5px);
            color: var(--white);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .social-big-btn.fb:hover {
            background: #1877F2;
            border-color: #1877F2;
        }

        .social-big-btn.ig:hover {
            background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF);
            border-color: transparent;
        }

        .social-big-btn.tw:hover {
            background: #1DA1F2;
            border-color: #1DA1F2;
        }

        .social-big-btn.yt:hover {
            background: #FF0000;
            border-color: #FF0000;
        }

        /* ================= CONTACT RESPONSIVE ================= */
        @media (max-width: 991px) {
            .contact-form-wrap {
                padding: 35px 28px;
            }

            .social-cta-inner {
                padding: 45px 30px;
            }

            .map-wrapper {
                margin-top: 30px;
            }
        }

        @media (max-width: 767px) {
            .contact-hero {
                padding: 40px 0 60px;
            }

            .contact-info-section {
                margin-top: -30px;
                padding-bottom: 40px;
            }

            .contact-form-wrap {
                padding: 30px 22px;
            }

            .map-embed iframe {
                min-height: 280px;
            }

            .faq-question {
                padding: 18px 20px;
                font-size: 14px;
            }

            .faq-answer p {
                padding: 0 20px 20px;
                font-size: 13px;
            }

            .social-cta-inner {
                padding: 40px 25px;
            }

            .social-big-btn {
                padding: 12px 20px;
                font-size: 13px;
            }

            .social-big-btn i {
                font-size: 16px;
            }
        }


        /* ================= OFFERS HERO ================= */
        .offers-hero {
            background: linear-gradient(135deg, #B71C1C 0%, #E53935 50%, #FF6F00 100%);
            padding: 50px 0 60px;
            color: var(--white);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .offers-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent 70%);
            border-radius: 50%;
            animation: float 4s ease-in-out infinite;
        }

        .offers-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.25), transparent 70%);
            border-radius: 50%;
            animation: float 5s ease-in-out infinite;
        }

        .offers-hero-content {
            position: relative;
            z-index: 1;
            margin-top: 20px;
        }

        .offers-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 22px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }

        .offers-hero-content h1 {
            color: var(--white);
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 15px;
            letter-spacing: -1px;
            line-height: 1.15;
        }

        .offers-hero-content h1 .highlight {
            color: var(--gold);
            text-shadow: 0 0 30px rgba(255, 179, 0, 0.5);
        }

        .offers-hero-content p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            margin-bottom: 30px;
        }

        .offers-hero-btns {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        /* Countdown */
        .hero-countdown {
            position: relative;
            z-index: 1;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px 30px;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .countdown-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .countdown-timer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .countdown-box {
            background: var(--white);
            color: var(--dark);
            border-radius: 12px;
            padding: 12px 16px;
            min-width: 70px;
            text-align: center;
        }

        .countdown-num {
            display: block;
            font-size: 26px;
            font-weight: 800;
            color: #E53935;
            line-height: 1;
            margin-bottom: 3px;
        }

        .countdown-txt {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
        }

        .countdown-sep {
            font-size: 24px;
            font-weight: 800;
            color: var(--white);
        }

        /* ================= DEAL OF THE DAY ================= */
        .deal-of-day-section {
            padding: 70px 0;
            background: var(--cream);
        }

        .deal-wrapper {
            background: var(--white);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            padding: 40px;
            border: 3px solid transparent;
            background-image: linear-gradient(var(--white), var(--white)),
                linear-gradient(135deg, #E53935, #FF6F00);
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }

        .deal-img {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 450px;
        }

        .deal-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .deal-img:hover img {
            transform: scale(1.08);
        }

        .deal-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #E53935, #FF6F00);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 6px 20px rgba(229, 57, 53, 0.4);
            animation: pulse 2s infinite;
        }

        .deal-save {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--white);
            color: #E53935;
            border-radius: 50%;
            width: 85px;
            height: 85px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: rotate(-15deg);
        }

        .deal-save span {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--gray);
        }

        .deal-save strong {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
        }

        .deal-info {
            padding: 10px 0 10px 20px;
        }

        .deal-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(229, 57, 53, 0.1);
            color: #E53935;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .deal-info h2 {
            font-size: clamp(1.6rem, 3.5vw, 2.4rem);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .deal-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            color: var(--gray);
        }

        .deal-rating i {
            color: var(--gold);
            font-size: 14px;
        }

        .deal-desc {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.9;
            margin-bottom: 25px;
        }

        .deal-price-row {
            display: flex;
            align-items: baseline;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .deal-price {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .deal-currency {
            font-size: 20px;
            color: var(--primary-dark);
            font-weight: 700;
        }

        .deal-new {
            font-size: 44px;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1;
        }

        .deal-old {
            font-size: 20px;
            color: var(--gray);
            text-decoration: line-through;
        }

        .deal-per-unit {
            font-size: 14px;
            color: var(--gray);
        }

        .deal-stock {
            margin-bottom: 25px;
        }

        .stock-info {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 8px;
            color: var(--gray);
        }

        .stock-info strong {
            color: #E53935;
        }

        .stock-bar {
            height: 8px;
            background: var(--light);
            border-radius: 50px;
            overflow: hidden;
        }

        .stock-fill {
            height: 100%;
            background: linear-gradient(90deg, #E53935, #FF6F00);
            border-radius: 50px;
            animation: pulse 2s infinite;
        }

        .deal-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .deal-qty {
            display: flex;
            align-items: center;
            border: 2px solid var(--border);
            border-radius: 50px;
            overflow: hidden;
            height: 54px;
        }

        .deal-qty-btn {
            width: 45px;
            height: 100%;
            background: transparent;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: var(--dark);
            transition: var(--transition);
        }

        .deal-qty-btn:hover {
            background: var(--cream);
            color: var(--primary);
        }

        .deal-qty input {
            width: 50px;
            height: 100%;
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            outline: none;
            color: var(--dark);
        }

        .btn-add-deal-cart {
            flex: 1;
            height: 54px;
            background: linear-gradient(135deg, #E53935, #FF6F00);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 6px 20px rgba(229, 57, 53, 0.3);
            font-family: 'Poppins', sans-serif;
            min-width: 180px;
        }

        .btn-add-deal-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(229, 57, 53, 0.4);
        }

        .btn-deal-view {
            height: 54px;
            padding: 0 20px;
            background: transparent;
            border: 2px solid var(--border);
            color: var(--dark);
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-deal-view:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(46, 125, 50, 0.05);
        }

        /* ================= FLASH SALE ================= */
        .flash-sale-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #1A1A1A 0%, #2C2C2C 100%);
            position: relative;
            overflow: hidden;
        }

        .flash-sale-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 111, 0, 0.15), transparent 70%);
            border-radius: 50%;
        }

        .flash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .flash-title-wrap {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .flash-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF6F00, #E53935);
            color: var(--white);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 10px 25px rgba(255, 111, 0, 0.4);
            animation: pulse 2s infinite;
        }

        .flash-title-wrap h2 {
            color: var(--white);
            font-size: 32px;
            margin-bottom: 3px;
            letter-spacing: -0.5px;
        }

        .flash-title-wrap p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            margin: 0;
        }

        .flash-timer {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            padding: 12px 22px;
            border-radius: 50px;
            color: var(--white);
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .flash-timer i {
            color: #FF6F00;
            animation: pulse 1s infinite;
        }

        .flash-card {
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
        }

        .flash-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(255, 111, 0, 0.2);
        }

        .flash-img {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: var(--cream);
        }

        .flash-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .flash-card:hover .flash-img img {
            transform: scale(1.1);
        }

        .flash-discount {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #E53935;
            color: var(--white);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(229, 57, 53, 0.4);
        }

        .flash-stock-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: rgba(0, 0, 0, 0.1);
        }

        .flash-stock-fill {
            height: 100%;
            background: linear-gradient(90deg, #E53935, #FF6F00);
        }

        .flash-info {
            padding: 18px;
        }

        .flash-cat {
            font-size: 10px;
            color: #E53935;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .flash-info h5 {
            font-size: 15px;
            margin: 5px 0 8px;
        }

        .flash-info h5 a {
            color: var(--dark);
        }

        .flash-info h5 a:hover {
            color: var(--primary);
        }

        .flash-rating i {
            font-size: 11px;
            color: #DDD;
        }

        .flash-rating i.filled {
            color: var(--gold);
        }

        .flash-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }

        .flash-new {
            font-size: 18px;
            font-weight: 800;
            color: #E53935;
        }

        .flash-old {
            font-size: 13px;
            color: var(--gray);
            text-decoration: line-through;
            margin-left: 6px;
        }

        .flash-cart-btn {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #E53935, #FF6F00);
            color: var(--white);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }

        .flash-cart-btn:hover {
            transform: rotate(-15deg) scale(1.15);
            box-shadow: 0 6px 20px rgba(229, 57, 53, 0.5);
        }

        /* ================= CATEGORY OFFERS ================= */
        .category-offers-section {
            padding: 80px 0;
            background: var(--white);
        }

        .cat-offer-card {
            display: block;
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            height: 320px;
            transition: var(--transition);
        }

        .cat-offer-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .cat-offer-img {
            position: absolute;
            inset: 0;
        }

        .cat-offer-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s ease;
        }

        .cat-offer-card:hover .cat-offer-img img {
            transform: scale(1.15);
        }

        .cat-offer-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.2) 60%, transparent 100%);
        }

        .cat-offer-discount {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--white);
            color: #E53935;
            padding: 12px 16px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transform: rotate(-8deg);
            z-index: 2;
        }

        .cat-offer-discount span {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--gray);
        }

        .cat-offer-discount strong {
            display: block;
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            margin: 2px 0;
        }

        .cat-offer-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 25px;
            color: var(--white);
            z-index: 2;
        }

        .cat-offer-icon {
            width: 50px;
            height: 50px;
            background: var(--accent);
            color: var(--white);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .cat-offer-card:hover .cat-offer-icon {
            transform: rotate(-10deg) scale(1.1);
        }

        .cat-offer-info h4 {
            color: var(--white);
            font-size: 20px;
            margin-bottom: 8px;
        }

        .cat-offer-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--gold);
            font-weight: 600;
            transition: var(--transition);
        }

        .cat-offer-card:hover .cat-offer-link {
            gap: 14px;
        }

        .cat-offer-green .cat-offer-icon {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
        }

        .cat-offer-orange .cat-offer-icon {
            background: linear-gradient(135deg, #FF6F00, #E65100);
        }

        .cat-offer-blue .cat-offer-icon {
            background: linear-gradient(135deg, #2196F3, #1565C0);
        }

        .cat-offer-red .cat-offer-icon {
            background: linear-gradient(135deg, #E53935, #B71C1C);
        }

        /* ================= COUPONS ================= */
        .coupons-section {
            padding: 80px 0;
            background: var(--cream);
        }

        .coupon-card {
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .coupon-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .coupon-left {
            padding: 25px;
            text-align: center;
            position: relative;
            color: var(--white);
        }

        .coupon-orange .coupon-left {
            background: linear-gradient(135deg, #FF6F00, #E65100);
        }

        .coupon-green .coupon-left {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
        }

        .coupon-blue .coupon-left {
            background: linear-gradient(135deg, #2196F3, #1565C0);
        }

        .coupon-red .coupon-left {
            background: linear-gradient(135deg, #E53935, #B71C1C);
        }

        .coupon-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin: 0 auto 12px;
            backdrop-filter: blur(10px);
        }

        .coupon-discount {
            display: block;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }

        .coupon-desc {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .coupon-min {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .coupon-divider {
            display: flex;
            justify-content: space-around;
            padding: 0 10px;
            background: var(--white);
            margin-top: -6px;
            height: 12px;
            position: relative;
        }

        .coupon-divider::before,
        .coupon-divider::after {
            content: '';
            position: absolute;
            top: 0;
            width: 14px;
            height: 14px;
            background: var(--cream);
            border-radius: 50%;
            transform: translateY(-50%);
        }

        .coupon-divider::before {
            left: -7px;
        }

        .coupon-divider::after {
            right: -7px;
        }

        .coupon-divider span {
            width: 6px;
            height: 6px;
            background: var(--border);
            border-radius: 50%;
            align-self: center;
        }

        .coupon-right {
            padding: 20px;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
        }

        .coupon-code {
            border: 2px dashed var(--border);
            border-radius: 10px;
            padding: 10px;
            background: var(--cream);
        }

        .coupon-code-label {
            display: block;
            font-size: 9px;
            color: var(--gray);
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .coupon-code-value {
            font-size: 16px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: 2px;
        }

        .coupon-copy-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .coupon-copy-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .coupon-copy-btn.copied {
            background: #4CAF50;
        }

        .coupon-expiry {
            font-size: 11px;
            color: var(--gray);
        }

        .coupon-expiry i {
            color: var(--accent);
            margin-right: 3px;
        }

        /* ================= BUNDLES ================= */
        .bundles-section {
            padding: 80px 0;
            background: var(--white);
        }

        .bundle-card {
            background: var(--white);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
            border: 2px solid var(--border);
        }

        .bundle-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .bundle-large {
            display: flex;
            flex-direction: row;
            height: 100%;
        }

        .bundle-content {
            flex: 1;
            padding: 35px 30px;
        }

        .bundle-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 111, 0, 0.15);
            color: var(--accent);
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .bundle-content h3 {
            font-size: 24px;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .bundle-content>p {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .bundle-list {
            margin-bottom: 22px;
            padding: 0;
        }

        .bundle-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 13px;
            color: var(--dark);
        }

        .bundle-list li i {
            color: var(--primary);
            font-size: 13px;
        }

        .bundle-price {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .bundle-old {
            font-size: 16px;
            color: var(--gray);
            text-decoration: line-through;
        }

        .bundle-new {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .bundle-save {
            background: #E53935;
            color: var(--white);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
        }

        .btn-add-bundle {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 14px 26px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-add-bundle:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.4);
        }

        .bundle-img {
            width: 40%;
            min-height: 100%;
            overflow: hidden;
        }

        .bundle-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .bundle-card:hover .bundle-img img {
            transform: scale(1.1);
        }

        .bundle-small {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 20px 25px;
            gap: 20px;
        }

        .bundle-small-content {
            flex: 1;
        }

        .small-tag {
            background: rgba(46, 125, 50, 0.12);
            color: var(--primary);
            margin-bottom: 10px;
        }

        .bundle-fruits .small-tag {
            background: rgba(255, 111, 0, 0.12);
            color: var(--accent);
        }

        .bundle-small-content h4 {
            font-size: 17px;
            margin-bottom: 5px;
        }

        .bundle-small-content>p {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 12px;
        }

        .bundle-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .bundle-price-row .bundle-old {
            font-size: 13px;
        }

        .bundle-price-row .bundle-new {
            font-size: 22px;
        }

        .btn-add-small {
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
            font-size: 15px;
            flex-shrink: 0;
        }

        .btn-add-small:hover {
            background: var(--accent);
            transform: rotate(-15deg) scale(1.1);
        }

        .bundle-small img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 16px;
            flex-shrink: 0;
        }

        /* ================= HOT DEALS ================= */
        .hot-deals-section {
            padding: 80px 0;
            background: var(--cream);
        }

        /* ================= OFFERS NEWSLETTER ================= */
        .offers-newsletter {
            padding: 60px 0 80px;
            background: var(--white);
        }

        .offers-newsletter-inner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            border-radius: 28px;
            padding: 60px 40px;
            text-align: center;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .offers-newsletter-inner::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.2), transparent 70%);
            border-radius: 50%;
        }

        .newsletter-icon-big {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            color: var(--gold);
            margin: 0 auto 25px;
            position: relative;
            z-index: 1;
            animation: pulse 2s infinite;
        }

        .offers-newsletter-inner h2 {
            color: var(--white);
            font-size: clamp(1.5rem, 3.5vw, 2.2rem);
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .offers-newsletter-inner>p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .offers-newsletter-form {
            display: flex;
            gap: 10px;
            max-width: 600px;
            margin: 0 auto 25px;
            position: relative;
            z-index: 1;
        }

        .offers-newsletter-form input {
            flex: 1;
            padding: 16px 24px;
            border: none;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            outline: none;
            color: var(--dark);
        }

        .offers-newsletter-form button {
            background: var(--gold);
            color: var(--dark);
            border: none;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .offers-newsletter-form button:hover {
            background: var(--accent);
            color: var(--white);
            transform: translateY(-3px);
        }

        .newsletter-perks {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            font-size: 13px;
            position: relative;
            z-index: 1;
        }

        .newsletter-perks span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.9);
        }

        .newsletter-perks i {
            color: var(--gold);
        }

        /* ================= OFFERS RESPONSIVE ================= */
        @media (max-width: 991px) {
            .deal-wrapper {
                padding: 25px;
            }

            .deal-img {
                height: 350px;
                margin-bottom: 20px;
            }

            .deal-info {
                padding: 0;
            }

            .deal-new {
                font-size: 36px;
            }

            .bundle-large {
                flex-direction: column;
            }

            .bundle-img {
                width: 100%;
                height: 250px;
                min-height: auto;
            }

            .offers-newsletter-form {
                flex-direction: column;
            }

            .offers-newsletter-form button {
                justify-content: center;
            }
        }

        @media (max-width: 767px) {
            .offers-hero {
                padding: 40px 0 50px;
            }

            .offers-hero-content h1 {
                font-size: 1.8rem;
            }

            .hero-countdown {
                padding: 15px 20px;
            }

            .countdown-box {
                padding: 10px 12px;
                min-width: 55px;
            }

            .countdown-num {
                font-size: 20px;
            }

            .countdown-txt {
                font-size: 8px;
            }

            .countdown-sep {
                font-size: 18px;
            }

            .deal-img {
                height: 250px;
            }

            .deal-save {
                width: 65px;
                height: 65px;
            }

            .deal-save strong {
                font-size: 18px;
            }

            .deal-new {
                font-size: 30px;
            }

            .deal-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .deal-qty {
                justify-content: center;
            }

            .btn-add-deal-cart {
                min-width: auto;
            }

            .cat-offer-card {
                height: 260px;
            }

            .bundle-small {
                flex-direction: column;
                text-align: center;
            }

            .bundle-small img {
                width: 100%;
                height: 180px;
            }

            .coupon-card {
                max-width: 350px;
                margin: 0 auto;
            }
        }


        /* ================= CART PAGE ================= */
        .cart-section {
            padding: 40px 0 70px;
            background: var(--cream);
        }

        /* Empty Cart */
        .empty-cart {
            background: var(--white);
            border-radius: 20px;
            padding: 80px 30px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .empty-cart-icon {
            width: 120px;
            height: 120px;
            background: var(--cream);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 50px;
            color: var(--primary-light);
            animation: float 3s ease-in-out infinite;
        }

        .empty-cart h2 {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: var(--gray);
            margin-bottom: 30px;
            font-size: 15px;
        }

        /* Cart Items */
        .cart-items-wrap {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .cart-header-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1.4fr 1fr 50px;
            gap: 15px;
            padding: 18px 25px;
            background: var(--cream);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
            border-bottom: 1px solid var(--border);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1.4fr 1fr 50px;
            gap: 15px;
            padding: 20px 25px;
            align-items: center;
            border-bottom: 1px solid var(--border);
            transition: var(--transition);
        }

        .cart-item:last-of-type {
            border-bottom: none;
        }

        .cart-item:hover {
            background: #FCFAF5;
        }

        .cart-product {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-product-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            background: var(--cream);
        }

        .cart-product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .cart-product-img:hover img {
            transform: scale(1.1);
        }

        .cart-product-info {
            flex: 1;
        }

        .cart-product-cat {
            font-size: 10px;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cart-product-info h4 {
            font-size: 15px;
            margin: 4px 0 5px;
            line-height: 1.4;
        }

        .cart-product-info h4 a {
            color: var(--dark);
        }

        .cart-product-info h4 a:hover {
            color: var(--primary);
        }

        .cart-product-unit {
            font-size: 12px;
            color: var(--gray);
            display: inline-block;
            background: var(--cream);
            padding: 2px 10px;
            border-radius: 50px;
        }

        .cart-price .new-price {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .qty-control {
            display: inline-flex;
            align-items: center;
            border: 2px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            background: var(--white);
        }

        .qty-control button {
            width: 34px;
            height: 38px;
            background: transparent;
            border: none;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
            font-size: 12px;
        }

        .qty-control button:hover {
            background: var(--cream);
            color: var(--primary);
        }

        .qty-value {
            width: 45px;
            height: 38px;
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            outline: none;
            color: var(--dark);
            appearance: textfield;
            -webkit-appearance: none;
            -moz-appearance: textfield;
        }

        .qty-value::-webkit-outer-spin-button,
        .qty-value::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .cart-subtotal .subtotal-value {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
        }

        .cart-remove .remove-btn {
            width: 36px;
            height: 36px;
            background: transparent;
            border: 2px solid var(--border);
            border-radius: 50%;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-decoration: none;
        }

        .cart-remove .remove-btn:hover {
            background: #E53935;
            border-color: #E53935;
            color: var(--white);
            transform: rotate(90deg);
        }

        /* Cart Actions */
        .cart-actions {
            display: flex;
            justify-content: space-between;
            padding: 20px 25px;
            background: var(--cream);
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn-continue-shopping {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 13px;
        }

        .btn-continue-shopping:hover {
            color: var(--primary);
            gap: 12px;
        }

        .btn-clear-cart {
            background: transparent;
            border: 2px solid #E53935;
            color: #E53935;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
        }

        .btn-clear-cart:hover {
            background: #E53935;
            color: var(--white);
        }

        /* Cart Summary */
        .cart-summary-wrap {
            position: sticky;
            top: 100px;
        }

        .coupon-box {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 2px dashed var(--primary);
        }

        .coupon-box h4 {
            font-size: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .coupon-box h4 i {
            color: var(--primary);
        }

        .coupon-form {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
        }

        .coupon-form input {
            flex: 1;
            padding: 11px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
            transition: var(--transition);
            color: var(--dark);
            text-transform: uppercase;
        }

        .coupon-form input:focus {
            border-color: var(--primary);
        }

        .coupon-form button {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 0 18px;
            border-radius: 10px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }

        .coupon-form button:hover {
            background: var(--primary-dark);
        }

        .coupon-hint {
            font-size: 11px;
            color: var(--gray);
            margin: 0;
        }

        .coupon-hint strong {
            color: var(--accent);
            background: var(--cream);
            padding: 2px 8px;
            border-radius: 5px;
        }

        .summary-box {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
        }

        .summary-box h3 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-row span {
            color: var(--gray);
        }

        .summary-row strong {
            color: var(--dark);
            font-weight: 700;
        }

        .summary-row .free-ship {
            color: var(--primary);
            background: rgba(46, 125, 50, 0.1);
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 12px;
        }

        .discount-row strong {
            color: #E53935;
        }

        .summary-divider {
            border-top: 1px dashed var(--border);
            margin: 18px 0;
        }

        .total-row span {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
        }

        .total-row strong {
            font-size: 22px;
            color: var(--primary-dark);
        }

        .free-ship-hint {
            background: rgba(255, 179, 0, 0.15);
            color: #B26A00;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12px;
            margin-bottom: 15px;
            text-align: center;
        }

        .free-ship-hint strong {
            color: #B26A00;
        }

        .btn-checkout {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            width: 100%;
            background: var(--primary);
            color: var(--white);
            padding: 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            margin-top: 10px;
            transition: var(--transition);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-checkout:hover {
            background: var(--primary-dark);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(46, 125, 50, 0.4);
        }

        .secure-payment {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            font-size: 12px;
            color: var(--gray);
        }

        .secure-payment i {
            color: var(--primary);
            font-size: 14px;
        }

        .payment-methods {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .payment-methods p {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .payment-methods .payment-icons {
            justify-content: center;
            font-size: 30px;
            color: var(--gray);
        }

        /* Cart Responsive */
        @media (max-width: 991px) {
            .cart-summary-wrap {
                position: static;
                margin-top: 30px;
            }
        }

        @media (max-width: 767px) {
            .cart-header-row {
                display: none;
            }

            .cart-item {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 20px;
                position: relative;
            }

            .cart-product {
                padding-right: 40px;
            }

            .cart-price,
            .cart-subtotal {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 13px;
            }

            .cart-price::before,
            .cart-subtotal::before {
                content: attr(data-label);
                font-size: 12px;
                color: var(--gray);
                font-weight: 600;
                text-transform: uppercase;
            }

            .cart-qty {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .cart-qty::before {
                content: attr(data-label);
                font-size: 12px;
                color: var(--gray);
                font-weight: 600;
                text-transform: uppercase;
            }

            .cart-remove {
                position: absolute;
                top: 15px;
                right: 15px;
            }

            .cart-actions {
                flex-direction: column;
            }

            .cart-actions a {
                width: 100%;
                justify-content: center;
            }
        }

        /* ================= CHECKOUT HEADER ================= */
        .checkout-header-section {
            background: var(--white);
            padding: 20px 0 30px;
            border-bottom: 1px solid var(--border);
        }

        /* Progress Bar */
        .checkout-progress {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-top: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--light);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            transition: var(--transition);
            border: 3px solid var(--white);
            box-shadow: 0 0 0 2px var(--border);
        }

        .step-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-step.active .step-icon {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 0 0 2px var(--primary), 0 8px 20px rgba(46, 125, 50, 0.3);
            transform: scale(1.1);
        }

        .progress-step.active .step-label {
            color: var(--primary-dark);
            font-weight: 700;
        }

        .progress-step.completed .step-icon {
            background: var(--primary-light);
            color: var(--white);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .progress-step.completed .step-label {
            color: var(--primary);
        }

        .progress-line {
            flex: 1;
            height: 3px;
            background: var(--border);
            margin: 0 15px;
            margin-bottom: 25px;
            border-radius: 2px;
            max-width: 120px;
        }

        .progress-line.active {
            background: var(--primary-light);
        }

        /* ================= CHECKOUT MAIN ================= */
        .checkout-section {
            padding: 40px 0 70px;
            background: var(--cream);
        }

        .checkout-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .checkout-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--border);
        }

        .cc-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.25);
        }

        .cc-icon.cc-orange {
            background: linear-gradient(135deg, var(--accent), #FF9800);
            box-shadow: 0 6px 16px rgba(255, 111, 0, 0.25);
        }

        .cc-icon.cc-green {
            background: linear-gradient(135deg, #2E7D32, #4CAF50);
        }

        .checkout-card-header h3 {
            font-size: 19px;
            margin-bottom: 3px;
        }

        .checkout-card-header p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
        }

        /* Shipping Check */
        .shipping-check {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
        }

        .custom-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: var(--dark);
        }

        .custom-checkbox input {
            display: none;
        }

        .checkmark {
            width: 22px;
            height: 22px;
            border: 2px solid var(--border);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            flex-shrink: 0;
            position: relative;
        }

        .custom-checkbox input:checked+.checkmark {
            background: var(--primary);
            border-color: var(--primary);
        }

        .custom-checkbox input:checked+.checkmark::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--white);
            font-size: 12px;
        }

        /* Payment Options */
        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .payment-option {
            cursor: pointer;
            display: block;
        }

        .payment-option input {
            display: none;
        }

        .po-content {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 20px;
            border: 2px solid var(--border);
            border-radius: 14px;
            transition: var(--transition);
            background: var(--white);
        }

        .payment-option:hover .po-content {
            border-color: var(--primary-light);
        }

        .payment-option.active .po-content {
            border-color: var(--primary);
            background: rgba(46, 125, 50, 0.04);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.1);
        }

        .po-icon {
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .po-icon.po-blue {
            background: linear-gradient(135deg, #2196F3, #1565C0);
        }

        .po-icon.po-purple {
            background: linear-gradient(135deg, #7B1FA2, #4A148C);
        }

        .po-info {
            flex: 1;
        }

        .po-info strong {
            display: block;
            font-size: 15px;
            color: var(--dark);
            margin-bottom: 3px;
        }

        .po-info span {
            font-size: 12px;
            color: var(--gray);
        }

        .po-check {
            width: 26px;
            height: 26px;
            border: 2px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            font-size: 12px;
            transition: var(--transition);
        }

        .payment-option.active .po-check {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--white);
        }

        /* Card Details */
        .card-details {
            margin-top: 20px;
            padding: 20px;
            background: var(--cream);
            border-radius: 14px;
            animation: fadeIn 0.3s ease;
        }

        /* ================= ORDER SUMMARY ================= */
        .order-summary-wrap {
            position: sticky;
            top: 100px;
        }

        .order-summary-box {
            background: var(--white);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow-md);
        }

        .os-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px dashed var(--border);
        }

        .os-header h3 {
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .os-header h3 i {
            color: var(--primary);
        }

        .edit-cart {
            font-size: 12px;
            color: var(--accent);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .edit-cart:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        .os-items {
            max-height: 280px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 5px;
        }

        .os-items::-webkit-scrollbar {
            width: 4px;
        }

        .os-items::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .os-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #F5F5F5;
        }

        .os-item:last-child {
            border-bottom: none;
        }

        .os-item-img {
            position: relative;
            width: 55px;
            height: 55px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            background: var(--cream);
        }

        .os-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .os-item-qty {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--primary);
            color: var(--white);
            font-size: 10px;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--white);
        }

        .os-item-info {
            flex: 1;
            min-width: 0;
        }

        .os-item-info h5 {
            font-size: 13px;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .os-item-info span {
            font-size: 11px;
            color: var(--gray);
        }

        .os-item-price {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-dark);
            flex-shrink: 0;
        }

        .os-totals {
            padding-top: 15px;
            border-top: 1px dashed var(--border);
        }

        .os-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .os-row span {
            color: var(--gray);
        }

        .os-row strong {
            color: var(--dark);
            font-weight: 600;
        }

        .os-row.os-free {
            color: var(--primary);
        }

        .os-row .os-free {
            color: var(--primary);
            background: rgba(46, 125, 50, 0.1);
            padding: 2px 10px;
            border-radius: 50px;
            font-size: 11px;
        }

        .os-discount strong {
            color: #E53935;
        }

        .os-divider {
            border-top: 1px dashed var(--border);
            margin: 15px 0;
        }

        .os-total span {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
        }

        .os-total strong {
            font-size: 20px;
            color: var(--primary-dark);
            font-weight: 800;
        }

        .os-coupon-applied {
            background: rgba(46, 125, 50, 0.08);
            color: var(--primary-dark);
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .os-coupon-applied i {
            color: var(--primary);
        }

        .os-coupon-applied strong {
            color: var(--primary-dark);
        }

        .btn-place-order {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
            font-family: 'Poppins', sans-serif;
        }

        .btn-place-order:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(46, 125, 50, 0.4);
        }

        .os-trust {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px dashed var(--border);
        }

        .trust-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .trust-item i {
            font-size: 18px;
            color: var(--primary);
        }

        .os-payments {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed var(--border);
        }

        .os-payments p {
            font-size: 10px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .os-payments .payment-icons {
            justify-content: center;
            font-size: 22px;
            color: var(--gray);
        }

        /* ================= ORDER SUCCESS PAGE ================= */
        .success-hero {
            background: linear-gradient(135deg, #E8F5E9 0%, #FAF8F3 100%);
            padding: 60px 0 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-hero-content {
            position: relative;
            z-index: 1;
        }

        .success-check {
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 50px;
            box-shadow: 0 15px 40px rgba(46, 125, 50, 0.3);
            animation: popIn 0.6s ease;
        }

        @keyframes popIn {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.15);
            }

            100% {
                transform: scale(1);
            }
        }

        .success-ring {
            position: absolute;
            inset: -10px;
            border: 3px solid var(--primary);
            border-radius: 50%;
            opacity: 0;
            animation: ripple 2s ease-out infinite;
        }

        .success-ring.delay-1 {
            animation-delay: 0.6s;
        }

        .success-ring.delay-2 {
            animation-delay: 1.2s;
        }

        @keyframes ripple {
            0% {
                transform: scale(0.9);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        .success-hero h1 {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            margin-bottom: 12px;
            color: var(--primary-dark);
        }

        .success-hero p {
            font-size: 15px;
            color: var(--gray);
            margin-bottom: 22px;
        }

        .success-hero p strong {
            color: var(--primary-dark);
        }

        .order-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--white);
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: var(--shadow-md);
            font-size: 14px;
        }

        .order-id-badge span {
            color: var(--gray);
        }

        .order-id-badge strong {
            color: var(--primary-dark);
            font-size: 16px;
            letter-spacing: 1px;
        }

        .success-details-section {
            padding: 50px 0 70px;
            background: var(--cream);
        }

        .success-card {
            background: var(--white);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .success-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border);
        }

        .success-card-header i {
            color: var(--primary);
            font-size: 18px;
        }

        .success-card-header h3 {
            font-size: 17px;
            margin: 0;
        }

        .success-items {
            margin-bottom: 20px;
        }

        .success-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #F5F5F5;
        }

        .success-item:last-child {
            border-bottom: none;
        }

        .success-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            flex-shrink: 0;
        }

        .success-item-info {
            flex: 1;
        }

        .success-item-info h5 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .success-item-info span {
            font-size: 12px;
            color: var(--gray);
        }

        .success-item-price {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .success-totals {
            padding-top: 15px;
            border-top: 1px dashed var(--border);
        }

        .suc-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .suc-row span {
            color: var(--gray);
        }

        .suc-row strong {
            color: var(--dark);
        }

        .suc-row.discount strong {
            color: #E53935;
        }

        .suc-divider {
            border-top: 1px dashed var(--border);
            margin: 15px 0;
        }

        .suc-row.total span {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
        }

        .suc-row.total strong {
            font-size: 20px;
            color: var(--primary-dark);
            font-weight: 800;
        }

        .info-card .info-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: var(--dark);
            line-height: 1.6;
        }

        .info-item i {
            width: 32px;
            height: 32px;
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary-dark);
            padding: 10px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .payment-badge i {
            color: var(--primary);
            font-size: 16px;
        }

        .payment-note {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
        }

        .payment-note strong {
            color: var(--primary-dark);
        }

        .estimate-card {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
        }

        .estimate-card .success-card-header {
            border-bottom-color: rgba(255, 255, 255, 0.15);
        }

        .estimate-card .success-card-header i {
            color: var(--gold);
        }

        .estimate-card .success-card-header h3 {
            color: var(--white);
        }

        .estimate-time {
            text-align: center;
            padding: 10px 0;
        }

        .estimate-time h2 {
            font-size: 36px;
            color: var(--gold);
            margin-bottom: 5px;
            font-weight: 800;
        }

        .estimate-time p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            margin: 0;
        }

        .success-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* Checkout Responsive */
        @media (max-width: 991px) {
            .order-summary-wrap {
                position: static;
                margin-top: 25px;
            }

            .checkout-progress {
                gap: 0;
            }

            .progress-line {
                margin: 0 8px;
                margin-bottom: 25px;
            }

            .step-label {
                font-size: 10px;
            }
        }

        @media (max-width: 767px) {
            .checkout-card {
                padding: 22px 20px;
            }

            .step-icon {
                width: 42px;
                height: 42px;
                font-size: 14px;
            }

            .step-label {
                font-size: 9px;
            }

            .progress-line {
                max-width: 40px;
                margin: 0 5px;
                margin-bottom: 22px;
            }

            .success-item img {
                width: 50px;
                height: 50px;
            }

            .success-actions a {
                width: 100%;
                justify-content: center;
            }
        }

        /* ================= MY ORDERS ================= */
        .myorders-section {
            padding: 40px 0 70px;
            background: var(--cream);
        }

        /* Empty State */
        .empty-orders {
            background: var(--white);
            border-radius: 20px;
            padding: 80px 30px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .empty-orders-icon {
            width: 120px;
            height: 120px;
            background: var(--cream);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 50px;
            color: var(--primary-light);
            animation: float 3s ease-in-out infinite;
        }

        .empty-orders h2 {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .empty-orders p {
            color: var(--gray);
            margin-bottom: 30px;
            font-size: 15px;
        }

        /* Stats */
        .orders-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 20px;
            flex-shrink: 0;
        }

        .si-total {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .si-pending {
            background: linear-gradient(135deg, #FFB300, #FF8F00);
        }

        .si-delivered {
            background: linear-gradient(135deg, #2196F3, #1565C0);
        }

        .si-cancelled {
            background: linear-gradient(135deg, #E53935, #B71C1C);
        }

        .stat-card h3 {
            font-size: 24px;
            margin-bottom: 2px;
            color: var(--dark);
            font-weight: 800;
        }

        .stat-card p {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Filter Tabs */
        .orders-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--white);
            border-radius: 50px;
            color: var(--gray);
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .filter-tab:hover {
            color: var(--primary);
        }

        .filter-tab.active {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
        }

        .tab-count {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 11px;
        }

        .filter-tab.active .tab-count {
            background: rgba(255, 255, 255, 0.25);
        }

        /* No Filter Orders */
        .no-filter-orders {
            background: var(--white);
            border-radius: 20px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .no-filter-orders i {
            font-size: 50px;
            color: var(--border);
            margin-bottom: 15px;
        }

        .no-filter-orders h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .no-filter-orders p {
            color: var(--gray);
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Orders List */
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .order-card {
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .order-card:hover {
            box-shadow: var(--shadow-md);
            border-color: rgba(46, 125, 50, 0.2);
        }

        /* Order Card Header */
        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 22px 25px;
            background: linear-gradient(135deg, #FAF8F3 0%, #F5F1E8 100%);
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-head-left {
            display: flex;
            gap: 35px;
            flex-wrap: wrap;
        }

        .order-label {
            display: block;
            font-size: 10px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .order-id {
            font-size: 14px;
            color: var(--primary-dark);
            letter-spacing: 1px;
            font-weight: 700;
        }

        .order-date-wrap strong {
            font-size: 13px;
            color: var(--dark);
        }

        .order-total-price {
            font-size: 16px;
            color: var(--primary-dark);
            font-weight: 800;
        }

        .order-head-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(255, 179, 0, 0.15);
            color: #B26A00;
        }

        .status-confirmed {
            background: rgba(33, 150, 243, 0.15);
            color: #1565C0;
        }

        .status-delivered {
            background: rgba(46, 125, 50, 0.15);
            color: var(--primary-dark);
        }

        .status-cancelled {
            background: rgba(229, 57, 53, 0.15);
            color: #B71C1C;
        }

        .order-toggle {
            width: 40px;
            height: 40px;
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 50%;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .order-toggle:hover {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .order-toggle.open i {
            transform: rotate(180deg);
        }

        /* Order Summary Row */
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 25px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .order-items-preview {
            display: flex;
            align-items: center;
        }

        .preview-item {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--white);
            box-shadow: var(--shadow-sm);
            margin-left: -15px;
            transition: var(--transition);
            background: var(--cream);
        }

        .preview-item:first-child {
            margin-left: 0;
        }

        .preview-item:hover {
            transform: translateY(-4px) scale(1.1);
            z-index: 2;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-more {
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        .order-items-count {
            font-size: 13px;
            color: var(--gray);
            font-weight: 600;
        }

        .order-items-count i {
            color: var(--primary);
            margin-right: 5px;
        }

        /* Order Details */
        .order-details {
            padding: 25px;
            background: var(--white);
            border-top: 1px solid var(--border);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }

            to {
                opacity: 1;
                max-height: 2000px;
            }
        }

        .od-section {
            margin-bottom: 25px;
        }

        .od-section:last-child {
            margin-bottom: 0;
        }

        .od-section h4 {
            font-size: 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--border);
        }

        .od-section h4 i {
            color: var(--primary);
            font-size: 13px;
        }

        .od-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .od-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            background: var(--cream);
            border-radius: 12px;
            transition: var(--transition);
        }

        .od-item:hover {
            background: #F5F1E8;
        }

        .od-item img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .od-item-info {
            flex: 1;
            min-width: 0;
        }

        .od-item-info h5 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .od-item-info span {
            font-size: 12px;
            color: var(--gray);
        }

        .od-item-price {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary-dark);
            flex-shrink: 0;
        }

        .od-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .od-info {
            background: var(--cream);
            border-radius: 12px;
            padding: 18px;
            font-size: 13px;
            line-height: 1.8;
        }

        .od-info strong {
            display: block;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .od-info p {
            margin: 0 0 5px;
            color: var(--gray);
        }

        .od-info p i {
            color: var(--primary);
            width: 16px;
            margin-right: 5px;
            font-size: 11px;
        }

        .od-info .od-discount {
            color: #E53935;
        }

        .od-info .od-total {
            color: var(--primary-dark);
            font-size: 15px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--border);
        }

        .od-info .od-total strong {
            color: var(--primary-dark);
            display: inline;
        }

        /* Mini Progress */
        .od-progress-section {
            margin-bottom: 25px;
        }

        .mini-progress {
            display: flex;
            align-items: center;
            padding: 0 10px;
        }

        .mini-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .ms-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: var(--transition);
            border: 3px solid var(--white);
            box-shadow: 0 0 0 2px var(--border);
        }

        .mini-step span {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mini-step.completed .ms-dot {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 0 0 2px var(--primary), 0 4px 12px rgba(46, 125, 50, 0.3);
        }

        .mini-step.completed span {
            color: var(--primary-dark);
            font-weight: 700;
        }

        .mini-line {
            flex: 1;
            height: 3px;
            background: var(--border);
            border-radius: 2px;
            margin: 0 8px;
            margin-bottom: 28px;
        }

        .mini-line.active {
            background: var(--primary);
        }

        /* Order Actions */
        .od-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
        }

        .btn-od-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid var(--border);
            background: transparent;
            color: var(--dark);
            font-family: 'Poppins', sans-serif;
        }

        .btn-od-action:hover {
            transform: translateY(-2px);
        }

        .btn-reorder {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .btn-reorder:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
        }

        .btn-invoice:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(46, 125, 50, 0.05);
        }

        .btn-cancel {
            color: #E53935;
            border-color: #E53935;
        }

        .btn-cancel:hover {
            background: #E53935;
            color: var(--white);
        }

        .btn-help:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(255, 111, 0, 0.05);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .orders-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .od-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .orders-stats {
                grid-template-columns: 1fr;
            }

            .order-head-left {
                gap: 20px;
            }

            .order-head-right {
                width: 100%;
                justify-content: space-between;
            }

            .order-card-header {
                padding: 18px 20px;
            }

            .order-summary-row {
                padding: 15px 20px;
            }

            .order-details {
                padding: 20px;
            }

            .mini-progress {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .mini-line {
                width: 3px;
                height: 25px;
                margin: 0;
                margin-left: 18px;
                margin-bottom: 0;
            }

            .mini-step {
                flex-direction: row;
            }

            .btn-od-action {
                flex: 1;
                justify-content: center;
            }
        }


        /* ================= TRACK ORDER PAGE ================= */
        .track-hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 50px 0 60px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .track-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 179, 0, 0.2), transparent 70%);
            border-radius: 50%;
        }

        .track-hero-content {
            text-align: center;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .track-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 22px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .track-hero-content h1 {
            color: var(--white);
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 12px;
        }

        .track-hero-content h1 .highlight {
            color: var(--gold);
        }

        .track-hero-content p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            margin: 0;
        }

        .track-search-box {
            margin-top: 35px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 1;
        }

        .track-form {
            display: flex;
            gap: 10px;
            background: var(--white);
            padding: 8px;
            border-radius: 50px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .track-input-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-left: 20px;
        }

        .track-input-wrap i {
            color: var(--gray);
            font-size: 16px;
        }

        .track-input-wrap input {
            flex: 1;
            border: none;
            outline: none;
            padding: 14px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            color: var(--dark);
            background: transparent;
        }

        .track-input-wrap input::placeholder {
            color: #AAA;
            font-size: 14px;
        }

        .track-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
        }

        .track-btn:hover {
            background: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 111, 0, 0.4);
        }

        .track-hint {
            text-align: center;
            color: rgba(255, 255, 255, 0.85);
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 0;
        }

        .track-hint i {
            color: var(--gold);
            margin-right: 5px;
        }

        .track-result-section {
            padding: 50px 0 70px;
            background: var(--cream);
            min-height: 400px;
        }

        .track-found {
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            padding: 30px;
        }

        .track-found-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-bottom: 25px;
            border-bottom: 2px dashed var(--border);
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .track-found-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.3);
        }

        .track-found-header>div:nth-child(2) {
            flex: 1;
        }

        .track-found-header h3 {
            font-size: 22px;
            margin-bottom: 4px;
            color: var(--primary-dark);
        }

        .track-found-header p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
        }

        .track-found-header p strong {
            color: var(--dark);
            letter-spacing: 1px;
        }

        .track-found-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .track-found-status.status-pending {
            background: rgba(255, 179, 0, 0.15);
            color: #B26A00;
        }

        .track-found-status.status-confirmed {
            background: rgba(33, 150, 243, 0.15);
            color: #1565C0;
        }

        .track-found-status.status-delivered {
            background: rgba(46, 125, 50, 0.15);
            color: var(--primary-dark);
        }

        .tracker-wrapper {
            background: var(--cream);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .tracker-wrapper h4 {
            font-size: 15px;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tracker-wrapper h4 i {
            color: var(--primary);
        }

        .tracker-progress {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0;
            margin-bottom: 25px;
        }

        .tracker-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
            text-align: center;
            min-width: 100px;
        }

        .tracker-dot {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: var(--white);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 3px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .tracker-step.completed .tracker-dot {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-color: var(--primary);
            box-shadow: 0 0 0 6px rgba(46, 125, 50, 0.15), 0 8px 20px rgba(46, 125, 50, 0.3);
        }

        .tracker-info strong {
            display: block;
            font-size: 13px;
            color: var(--dark);
            margin-bottom: 3px;
            font-weight: 700;
        }

        .tracker-info span {
            font-size: 11px;
            color: var(--gray);
            line-height: 1.4;
            display: block;
        }

        .tracker-step.completed .tracker-info strong {
            color: var(--primary-dark);
        }

        .tracker-line {
            flex: 1;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            margin-top: 26px;
            transition: var(--transition);
            min-width: 30px;
        }

        .tracker-line.active {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            box-shadow: 0 0 10px rgba(46, 125, 50, 0.3);
        }

        .tracker-estimate {
            display: flex;
            align-items: center;
            gap: 15px;
            background: var(--white);
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--accent);
        }

        .tracker-estimate i {
            font-size: 26px;
            color: var(--accent);
        }

        .tracker-estimate strong {
            display: block;
            font-size: 14px;
            color: var(--dark);
            margin-bottom: 3px;
        }

        .tracker-estimate span {
            font-size: 12px;
            color: var(--gray);
        }

        .track-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .track-detail-card {
            background: var(--cream);
            border-radius: 16px;
            padding: 22px;
        }

        .track-detail-card h4 {
            font-size: 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border);
        }

        .track-detail-card h4 i {
            color: var(--primary);
        }

        .track-items {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .track-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: var(--white);
            border-radius: 10px;
        }

        .track-item img {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .track-item>div {
            flex: 1;
            min-width: 0;
        }

        .track-item strong {
            display: block;
            font-size: 13px;
            color: var(--dark);
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .track-item span {
            font-size: 11px;
            color: var(--gray);
        }

        .track-item-price {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-dark);
            flex-shrink: 0;
        }

        .track-info p {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .track-info p strong {
            color: var(--dark);
            font-size: 14px;
        }

        .track-info p i {
            color: var(--primary);
            width: 16px;
            margin-right: 4px;
        }

        .track-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            padding-top: 25px;
            border-top: 1px dashed var(--border);
        }

        .track-not-found {
            background: var(--white);
            border-radius: 20px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            max-width: 600px;
            margin: 0 auto;
        }

        .not-found-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #FFB300, #FF8F00);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            margin: 0 auto 25px;
            box-shadow: 0 12px 30px rgba(255, 179, 0, 0.3);
        }

        .track-not-found h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .track-not-found p {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 12px;
        }

        .track-not-found p strong {
            color: var(--dark);
            letter-spacing: 1px;
        }

        .not-found-hint {
            background: var(--cream);
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 13px !important;
            margin: 20px 0 25px !important;
            border-left: 3px solid var(--accent);
            text-align: left;
        }

        .not-found-hint i {
            color: var(--accent);
            margin-right: 5px;
        }

        .not-found-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .track-info-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .info-card-step {
            background: var(--white);
            border-radius: 18px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .info-card-step:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-md);
        }

        .ics-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.25);
        }

        .info-card-step:nth-child(2) .ics-icon {
            background: linear-gradient(135deg, var(--accent), #FF9800);
            box-shadow: 0 10px 25px rgba(255, 111, 0, 0.25);
        }

        .info-card-step:nth-child(3) .ics-icon {
            background: linear-gradient(135deg, #2196F3, #1565C0);
            box-shadow: 0 10px 25px rgba(33, 150, 243, 0.25);
        }

        .info-card-step h4 {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .info-card-step p {
            font-size: 13px;
            color: var(--gray);
            line-height: 1.7;
            margin: 0;
        }

        .info-card-step p strong {
            color: var(--primary-dark);
            background: var(--cream);
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 12px;
        }

        @media (max-width: 991px) {
            .tracker-progress {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .tracker-line {
                width: 4px;
                height: 30px;
                margin: 0;
                margin-left: 26px;
                min-width: auto;
            }

            .tracker-step {
                flex-direction: row;
                text-align: left;
                min-width: auto;
                width: 100%;
            }

            .track-details-grid {
                grid-template-columns: 1fr;
            }

            .track-info-cards {
                grid-template-columns: 1fr;
                max-width: 450px;
            }
        }

        @media (max-width: 767px) {
            .track-hero {
                padding: 40px 0 50px;
            }

            .track-form {
                flex-direction: column;
                border-radius: 20px;
                padding: 15px;
            }

            .track-input-wrap {
                padding-left: 0;
                padding: 0 10px;
            }

            .track-btn {
                justify-content: center;
                border-radius: 14px;
            }

            .track-found {
                padding: 20px;
            }

            .track-found-header>div:nth-child(2) {
                flex: 1 1 100%;
            }

            .track-found-status {
                width: 100%;
                justify-content: center;
            }

            .tracker-wrapper {
                padding: 20px;
            }

            .tracker-dot {
                width: 46px;
                height: 46px;
                font-size: 16px;
            }

            .track-actions {
                flex-direction: column;
            }

            .track-actions a {
                width: 100%;
                justify-content: center;
            }

            .not-found-actions {
                flex-direction: column;
            }

            .not-found-actions a {
                width: 100%;
                justify-content: center;
            }
        }

        /* ================= FAQ QUICK LINKS ================= */
        .faq-quick-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        .quick-link-card {
            background: var(--white);
            border-radius: 16px;
            padding: 22px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .quick-link-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .quick-link-card i {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .quick-link-card span {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
        }

        /* ================= FAQ CTA ================= */
        .faq-cta {
            background: linear-gradient(135deg, var(--cream) 0%, #E8F5E9 100%);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            margin-top: 40px;
            border: 1px solid var(--border);
        }

        .faq-cta>i {
            font-size: 50px;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .faq-cta h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .faq-cta p {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* FAQ Responsive */
        @media (max-width: 767px) {
            .faq-quick-links {
                grid-template-columns: 1fr;
            }
        }


        .btn-od-action.btn-track {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            border-color: var(--primary);
        }

        .btn-od-action.btn-track:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
        }

        /* ================= USER MENU ================= */
.user-menu-wrap { position: relative; }
.user-menu-toggle { cursor: pointer; }
.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white);
    min-width: 260px;
    border-radius: 16px;
    box-shadow: var(--shadow-lg);
    padding: 10px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: var(--transition);
    z-index: 100;
    border: 1px solid var(--border);
    margin-top: 8px;
}
.user-menu-wrap:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.user-dropdown-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px dashed var(--border);
    margin-bottom: 8px;
}
.user-avatar {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: var(--white);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.user-dropdown-header strong {
    display: block;
    font-size: 14px;
    color: var(--dark);
    margin-bottom: 2px;
}
.user-dropdown-header span {
    font-size: 11px;
    color: var(--gray);
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 170px;
}
.user-dropdown-menu { padding: 0; margin: 0; list-style: none; }
.user-dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    color: var(--dark);
    font-size: 13px;
    font-weight: 500;
    transition: var(--transition);
}
.user-dropdown-menu li a i {
    color: var(--primary);
    font-size: 14px;
    width: 18px;
}
.user-dropdown-menu li a:hover {
    background: var(--cream);
    color: var(--primary);
    padding-left: 20px;
}
.user-dropdown-menu li.divider {
    height: 1px;
    background: var(--border);
    margin: 6px 10px;
}
.user-dropdown-menu li a.logout-link { color: #E53935; }
.user-dropdown-menu li a.logout-link i { color: #E53935; }
.user-dropdown-menu li a.logout-link:hover { background: rgba(229,57,53,0.08); }

/* ================= AUTH PAGES ================= */
.auth-section {
    padding: 50px 0 70px;
    background: var(--cream);
    min-height: calc(100vh - 200px);
}
.auth-wrapper {
    background: var(--white);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    max-width: 1100px;
    margin: 0 auto;
}
.auth-visual {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    padding: 55px 45px;
    color: var(--white);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}
.auth-visual::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,179,0,0.25), transparent 70%);
    border-radius: 50%;
}
.auth-visual::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -60px;
    width: 250px; height: 250px;
    background: radial-gradient(circle, rgba(76,175,80,0.3), transparent 70%);
    border-radius: 50%;
}
.auth-visual-login {
    background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 60%, #FF6F00 200%);
}
.auth-visual-content { position: relative; z-index: 1; }
.auth-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 35px;
}
.auth-logo .logo-icon {
    width: 48px; height: 48px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    color: var(--white);
}
.auth-logo .logo-name {
    color: var(--white);
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.5px;
}
.auth-logo .logo-name span { color: var(--gold); }
.auth-visual h2 {
    color: var(--white);
    font-size: 30px;
    margin-bottom: 15px;
    line-height: 1.2;
}
.auth-visual > .auth-visual-content > p {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 30px;
}
.auth-benefits { margin-bottom: 35px; padding: 0; }
.auth-benefits li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    font-size: 14px;
    color: rgba(255,255,255,0.95);
}
.auth-benefits li i { color: var(--gold); font-size: 16px; flex-shrink: 0; }
.auth-quote {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 22px 25px;
    border-radius: 18px;
}
.auth-quote i {
    color: var(--gold);
    font-size: 20px;
    margin-bottom: 8px;
    display: block;
    opacity: 0.7;
}
.auth-quote p {
    font-size: 13px;
    color: rgba(255,255,255,0.95);
    font-style: italic;
    line-height: 1.7;
    margin-bottom: 10px;
}
.auth-quote span { font-size: 12px; color: var(--gold); font-weight: 600; }
.auth-form-side {
    padding: 55px 50px;
    display: flex;
    align-items: center;
}
.auth-form-wrap {
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
}
.auth-header { margin-bottom: 30px; }
.auth-header h1 {
    font-size: 28px;
    margin-bottom: 8px;
    color: var(--dark);
}
.auth-header p { font-size: 14px; color: var(--gray); margin: 0; }
.auth-header p a { color: var(--primary); font-weight: 600; }
.auth-header p a:hover { text-decoration: underline; }
.auth-alert {
    background: rgba(229,57,53,0.1);
    color: #B71C1C;
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid #E53935;
}
.auth-alert i { color: #E53935; font-size: 16px; }
.demo-hint {
    background: linear-gradient(135deg, #FFF8E1, #FFECB3);
    border: 2px dashed var(--gold);
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 22px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.demo-hint > i { color: #B26A00; font-size: 18px; margin-top: 2px; }
.demo-hint strong {
    display: block;
    font-size: 12px;
    color: #B26A00;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}
.demo-hint span {
    font-size: 12px;
    color: var(--dark);
    line-height: 1.6;
    display: block;
}
.demo-hint code {
    background: var(--white);
    padding: 2px 8px;
    border-radius: 5px;
    font-size: 11px;
    color: var(--primary-dark);
    font-weight: 600;
}
.auth-form .form-group { margin-bottom: 20px; }
.auth-form label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 8px;
}
.auth-form .req { color: #E53935; }
.input-with-icon { position: relative; }
.input-with-icon > i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 14px;
    pointer-events: none;
}
.input-with-icon input {
    width: 100%;
    padding: 14px 50px 14px 46px;
    border: 2px solid var(--border);
    border-radius: 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: var(--dark);
    outline: none;
    transition: var(--transition);
    background: var(--white);
}
.input-with-icon input::placeholder { color: #AAA; }
.input-with-icon input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(46,125,50,0.08);
}
.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: var(--gray);
    cursor: pointer;
    font-size: 14px;
    padding: 5px;
    transition: var(--transition);
}
.toggle-password:hover { color: var(--primary); }
.form-error {
    display: block;
    color: #E53935;
    font-size: 12px;
    margin-top: 6px;
    font-weight: 500;
}
.terms-check { margin-bottom: 22px; }
.custom-checkbox {
    display: inline-flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: var(--dark);
    line-height: 1.5;
}
.custom-checkbox.small { font-size: 12px; }
.custom-checkbox input { display: none; }
.checkmark {
    width: 20px; height: 20px;
    border: 2px solid var(--border);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition);
    flex-shrink: 0;
    position: relative;
    margin-top: 1px;
}
.custom-checkbox input:checked + .checkmark {
    background: var(--primary);
    border-color: var(--primary);
}
.custom-checkbox input:checked + .checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: var(--white);
    font-size: 11px;
}
.custom-checkbox a { color: var(--primary); font-weight: 600; }
.custom-checkbox a:hover { text-decoration: underline; }
.auth-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 10px;
}
.forgot-link { font-size: 13px; color: var(--primary); font-weight: 600; }
.forgot-link:hover { text-decoration: underline; }
.btn-auth {
    width: 100%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--white);
    border: none;
    padding: 15px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-family: 'Poppins', sans-serif;
    box-shadow: 0 6px 20px rgba(46,125,50,0.3);
}
.btn-auth:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(46,125,50,0.4);
}
.auth-divider {
    text-align: center;
    margin: 25px 0 20px;
    position: relative;
}
.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0; right: 0;
    height: 1px;
    background: var(--border);
}
.auth-divider span {
    background: var(--white);
    padding: 0 18px;
    font-size: 12px;
    color: var(--gray);
    position: relative;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}
.social-auth {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.social-auth-btn {
    padding: 12px;
    border: 2px solid var(--border);
    border-radius: 12px;
    background: var(--white);
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--dark);
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.social-auth-btn.google:hover {
    border-color: #DB4437;
    color: #DB4437;
    background: rgba(219,68,55,0.05);
}
.social-auth-btn.facebook:hover {
    border-color: #1877F2;
    color: #1877F2;
    background: rgba(24,119,242,0.05);
}
.auth-success {
    text-align: center;
    padding: 40px 20px;
}
.auth-success-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: var(--white);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    margin: 0 auto 25px;
    animation: popIn 0.6s ease;
    box-shadow: 0 15px 40px rgba(46,125,50,0.3);
}
.auth-success h2 {
    font-size: 24px;
    margin-bottom: 10px;
    color: var(--primary-dark);
}
.auth-success p {
    font-size: 14px;
    color: var(--gray);
    margin-bottom: 8px;
}
.auth-success-redirect {
    color: var(--primary) !important;
    font-weight: 600;
    margin-top: 20px !important;
}
@keyframes popIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

/* Auth Responsive */
@media (max-width: 991px) {
    .auth-visual { padding: 40px 35px; }
    .auth-form-side { padding: 40px 30px; }
}
@media (max-width: 767px) {
    .auth-visual { padding: 35px 25px; }
    .auth-visual h2 { font-size: 24px; }
    .auth-form-side { padding: 35px 22px; }
    .auth-header h1 { font-size: 24px; }
    .social-auth { grid-template-columns: 1fr; }
}
    </style>
</head>

<body>

    <!-- ================= TOP TICKER ================= -->
    <div class="top-ticker">
        <div class="container-fluid px-3">
            <div class="ticker-wrap">
                <div class="ticker-label"><i class="fas fa-bolt"></i> TODAY'S DEALS</div>
                <div class="ticker">
                    <div class="ticker-item">
                        🥬 Fresh Vegetables 30% OFF &nbsp; • &nbsp;
                        🍎 Buy 1 Get 1 on Fruits &nbsp; • &nbsp;
                        🚚 Free Delivery above Rs. 1500 &nbsp; • &nbsp;
                        🎁 Use Code: FRESH20 for extra 20% off &nbsp; • &nbsp;
                        ⏰ Limited Time Offer — Ends Tonight!
                    </div>
                </div>
                <div class="ticker-contact"><i class="fas fa-phone"></i> +92 300 1234567</div>
            </div>
        </div>
    </div>

    <!-- ================= HEADER ================= -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="header-inner">
                <!-- Logo -->
                <a href="index.php" class="logo">
                    <div class="logo-icon"><i class="fas fa-leaf"></i></div>
                    <div class="logo-text">
                        <span class="logo-name">Fresh<span>Mart</span></span>
                        <span class="logo-tag">Organic & Fresh</span>
                    </div>
                </a>

                <!-- Search -->
                <div class="search-box">
                    <form action="shop.php" method="GET">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Search for vegetables, fruits, dairy...">
                        <button type="submit">Search</button>
                    </form>
                </div>

                <!-- Actions -->
                <div class="header-actions">
                    <?php if ($userName): ?>
                        <div class="user-menu-wrap">
                            <a href="#" class="action-btn user-menu-toggle">
                                <i class="fas fa-user-circle"></i>
                                <span>Hi, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?></span>
                            </a>
                            <div class="user-dropdown">
                                <div class="user-dropdown-header">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($userName); ?></strong>
                                        <span><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <ul class="user-dropdown-menu">
                                    <li><a href="my-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                                    <li><a href="track-order.php"><i class="fas fa-truck"></i> Track Order</a></li>
                                    <li><a href="cart.php"><i class="fas fa-shopping-bag"></i> My Cart</a></li>
                                    <li class="divider"></li>
                                    <li><a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="action-btn">
                            <i class="far fa-user"></i><span>Account</span>
                        </a>
                    <?php endif; ?>

                    <a href="wishlist.php" class="action-btn">
                        <i class="far fa-heart"></i><span>Wishlist</span>
                    </a>
                    <a href="cart.php" class="action-btn cart-btn">
                        <i class="fas fa-shopping-bag"></i><span>Cart</span>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                    </a>
                </div>
                <!-- Mobile Toggle -->
                <button class="mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
            </div>
        </div>

        <!-- ================= NAVIGATION ================= -->
        <nav class="main-nav" id="mainNav">
            <div class="container">
                <ul class="nav-list">
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="shop.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>"><i class="fas fa-store"></i> Shop</a></li>
                    <li class="dropdown" id="catDropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-th-large"></i> Categories <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="shop.php?cat=vegetables"><i class="fas fa-carrot"></i> Fresh Vegetables</a></li>
                            <li><a href="shop.php?cat=fruits"><i class="fas fa-apple-alt"></i> Fruits</a></li>
                            <li><a href="shop.php?cat=dairy"><i class="fas fa-cheese"></i> Dairy & Eggs</a></li>
                            <li><a href="shop.php?cat=meat"><i class="fas fa-drumstick-bite"></i> Meat & Fish</a></li>
                            <li><a href="shop.php?cat=bakery"><i class="fas fa-bread-slice"></i> Bakery</a></li>
                            <li><a href="shop.php?cat=beverages"><i class="fas fa-coffee"></i> Beverages</a></li>
                            <li><a href="shop.php?cat=snacks"><i class="fas fa-cookie-bite"></i> Snacks</a></li>
                            <li><a href="shop.php?cat=household"><i class="fas fa-spray-can"></i> Household</a></li>
                        </ul>
                    </li>
                    <li><a href="offers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'offers.php' ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Offers</a></li>
                    <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-question-circle"></i> Help <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="track-order.php"><i class="fas fa-truck"></i> Track Order</a></li>
                            <li><a href="my-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                            <li><a href="contact.php"><i class="fas fa-headset"></i> Contact Us</a></li>
                            <li><a href="faqs.php"><i class="fas fa-circle-question"></i> FAQs</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>