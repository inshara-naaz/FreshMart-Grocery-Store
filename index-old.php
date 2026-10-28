<?php
// index.php - FreshMart Grocery Shop Homepage (All-in-One)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="FreshMart - Premium organic groceries delivered to your doorstep">
<title>FreshMart — Fresh Groceries Delivered</title>

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
* { margin: 0; padding: 0; box-sizing: border-box; }

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
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    font-family: 'Poppins', sans-serif;
    color: var(--dark);
    background: var(--cream);
    overflow-x: hidden;
    line-height: 1.6;
}

h1,h2,h3,h4,h5,h6 { font-family:'Poppins',sans-serif; font-weight:700; color:var(--dark); }
a { text-decoration: none; transition: var(--transition); }
img { max-width: 100%; height: auto; }
ul { list-style: none; padding: 0; margin: 0; }

/* ================= TOP TICKER ================= */
.top-ticker {
    background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 100%);
    color: var(--white);
    padding: 8px 0;
    font-size: 13px;
    overflow: hidden;
}
.ticker-wrap { display: flex; align-items: center; gap: 20px; }
.ticker-label {
    background: var(--accent);
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 11px;
    letter-spacing: 1px;
    white-space: nowrap;
}
.ticker { flex: 1; overflow: hidden; position: relative; height: 20px; }
.ticker-item {
    position: absolute;
    white-space: nowrap;
    animation: tickerScroll 30s linear infinite;
    font-weight: 500;
    padding-left: 100%;
}
@keyframes tickerScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
.ticker-contact { white-space: nowrap; font-weight: 500; }

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
.logo { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.logo-icon {
    width: 46px; height: 46px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: var(--white);
    font-size: 22px;
    box-shadow: 0 4px 12px rgba(46,125,50,0.3);
}
.logo-text { display: flex; flex-direction: column; line-height: 1.1; }
.logo-name { font-size: 22px; font-weight: 800; color: var(--primary-dark); letter-spacing: -0.5px; }
.logo-name span { color: var(--accent); }
.logo-tag { font-size: 10px; color: var(--gray); font-weight: 500; letter-spacing: 1px; text-transform: uppercase; }

/* Search Box */
.search-box { flex: 1; max-width: 550px; }
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
    box-shadow: 0 0 0 4px rgba(46,125,50,0.1);
}
.search-icon { color: var(--gray); margin-right: 10px; font-size: 15px; }
.search-box input {
    flex: 1; border: none; background: transparent; outline: none;
    padding: 10px 0; font-size: 14px; font-family: 'Poppins',sans-serif;
    color: var(--dark);
}
.search-box input::placeholder { color: #999; }
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
.search-box button:hover { background: var(--primary-dark); transform: scale(1.02); }

/* Header Actions */
.header-actions { display: flex; align-items: center; gap: 8px; }
.action-btn {
    display: flex; flex-direction: column; align-items: center;
    color: var(--dark); font-size: 11px; font-weight: 500;
    padding: 8px 12px; border-radius: 10px;
    position: relative; transition: var(--transition);
}
.action-btn i { font-size: 18px; margin-bottom: 2px; color: var(--gray); transition: var(--transition); }
.action-btn:hover { background: var(--light); color: var(--primary); }
.action-btn:hover i { color: var(--primary); transform: translateY(-2px); }
.cart-btn { position: relative; }
.cart-count {
    position: absolute; top: 2px; right: 4px;
    background: var(--accent);
    color: var(--white);
    font-size: 10px; font-weight: 700;
    width: 18px; height: 18px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%,100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    background: var(--primary); color: var(--white);
    border: none; width: 42px; height: 42px; border-radius: 10px;
    font-size: 18px; cursor: pointer;
}

/* ================= NAVIGATION ================= */
.main-nav {
    background: var(--primary-dark);
    border-top: 1px solid rgba(255,255,255,0.1);
}
.nav-list { display: flex; align-items: center; gap: 4px; padding: 0; }
.nav-list > li { position: relative; }
.nav-list > li > a {
    display: flex; align-items: center; gap: 8px;
    color: rgba(255,255,255,0.9);
    padding: 14px 18px;
    font-size: 14px; font-weight: 500;
    position: relative;
}
.nav-list > li > a i { font-size: 13px; }
.nav-list > li > a::after {
    content: ''; position: absolute;
    bottom: 8px; left: 50%; transform: translateX(-50%);
    width: 0; height: 2px; background: var(--gold);
    transition: var(--transition);
}
.nav-list > li > a:hover, .nav-list > li > a.active {
    color: var(--white); background: rgba(255,255,255,0.08);
}
.nav-list > li > a:hover::after, .nav-list > li > a.active::after { width: 60%; }

/* Dropdown */
.dropdown { position: relative; }
.dropdown-toggle i.fa-chevron-down { font-size: 10px; transition: var(--transition); }
.dropdown:hover .dropdown-toggle i.fa-chevron-down { transform: rotate(180deg); }
.dropdown-menu {
    position: absolute; top: 100%; left: 0;
    background: var(--white);
    min-width: 240px;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    padding: 10px;
    opacity: 0; visibility: hidden;
    transform: translateY(10px);
    transition: var(--transition);
    z-index: 100;
    display: block;
    border: none;
}
.dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-menu li a {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; border-radius: 8px;
    color: var(--dark); font-size: 13px; font-weight: 500;
}
.dropdown-menu li a i { color: var(--primary); font-size: 14px; width: 18px; }
.dropdown-menu li a:hover { background: var(--cream); color: var(--primary); padding-left: 20px; }

/* ================= HERO SECTION ================= */
.hero-section {
    padding: 70px 0;
    background: linear-gradient(135deg, #FAF8F3 0%, #E8F5E9 100%);
    position: relative;
    overflow: hidden;
}
.hero-section::before {
    content: ''; position: absolute; top: -100px; right: -100px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(76,175,80,0.15), transparent 70%);
    border-radius: 50%;
}
.hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(46,125,50,0.1);
    color: var(--primary-dark);
    padding: 8px 18px; border-radius: 50px;
    font-size: 13px; font-weight: 600;
    margin-bottom: 20px;
    border: 1px solid rgba(46,125,50,0.2);
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
    content: ''; position: absolute;
    bottom: 5px; left: 0; right: 0;
    height: 10px; background: rgba(255,179,0,0.3);
    z-index: -1; border-radius: 5px;
}
.hero-text {
    font-size: 16px; color: var(--gray);
    margin-bottom: 30px; max-width: 500px;
}
.hero-buttons { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 40px; }

.btn-primary-custom {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--primary);
    color: var(--white);
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600; font-size: 14px;
    border: none; cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 6px 20px rgba(46,125,50,0.3);
}
.btn-primary-custom:hover {
    background: var(--primary-dark);
    color: var(--white);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(46,125,50,0.4);
}
.btn-outline-custom {
    display: inline-flex; align-items: center; gap: 10px;
    background: transparent;
    color: var(--primary-dark);
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600; font-size: 14px;
    border: 2px solid var(--primary);
    cursor: pointer; transition: var(--transition);
}
.btn-outline-custom:hover {
    background: var(--primary); color: var(--white);
    transform: translateY(-3px);
}
.btn-white-custom {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--white); color: var(--primary-dark);
    padding: 14px 30px; border-radius: 50px;
    font-weight: 600; font-size: 14px;
    transition: var(--transition);
}
.btn-white-custom:hover {
    background: var(--dark); color: var(--white);
    transform: translateY(-3px);
}

.hero-stats { display: flex; gap: 40px; flex-wrap: wrap; }
.hero-stats .stat h3 {
    font-size: 28px; color: var(--primary-dark);
    margin-bottom: 2px; font-weight: 800;
}
.hero-stats .stat p { font-size: 12px; color: var(--gray); margin: 0; }

/* Hero Image */
.hero-img-wrap { position: relative; padding: 20px; }
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
    display: flex; align-items: center; gap: 12px;
    animation: float 3s ease-in-out infinite;
}
.floating-card i {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: var(--white);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.floating-card strong { display: block; font-size: 13px; color: var(--dark); }
.floating-card small { font-size: 11px; color: var(--gray); }
.card-1 { top: 40px; left: -10px; animation-delay: 0s; }
.card-2 { bottom: 40px; right: -10px; animation-delay: 1.5s; }
@keyframes float {
    0%,100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
}

/* ================= FEATURES STRIP ================= */
.features-strip { padding: 50px 0; background: var(--white); }
.feature-box {
    display: flex; align-items: center; gap: 15px;
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
    width: 55px; height: 55px; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(46,125,50,0.1), rgba(76,175,80,0.15));
    color: var(--primary);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    transition: var(--transition);
}
.feature-box:hover .feature-icon {
    background: var(--primary); color: var(--white);
    transform: rotate(-8deg);
}
.feature-box h5 { font-size: 15px; margin-bottom: 2px; }
.feature-box p { font-size: 12px; color: var(--gray); margin: 0; }

/* ================= SECTION HEADER ================= */
.section-header { text-align: center; margin-bottom: 50px; }
.section-tag {
    display: inline-block;
    color: var(--primary);
    font-size: 13px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 2px;
    margin-bottom: 10px;
    position: relative;
    padding: 0 30px;
}
.section-tag::before, .section-tag::after {
    content: ''; position: absolute; top: 50%;
    width: 20px; height: 1px; background: var(--primary);
}
.section-tag::before { left: 0; }
.section-tag::after { right: 0; }
.section-header h2 {
    font-size: clamp(1.6rem, 3vw, 2.3rem);
    margin-bottom: 10px;
    letter-spacing: -0.5px;
}
.section-header p { color: var(--gray); font-size: 15px; }

/* ================= CATEGORIES ================= */
.categories-section { padding: 70px 0; }
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
.cat-img { position: relative; height: 220px; overflow: hidden; }
.cat-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.7s cubic-bezier(0.4,0,0.2,1);
}
.category-card:hover .cat-img img { transform: scale(1.12); }
.cat-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(27,94,32,0.7), transparent 60%);
    opacity: 0; transition: var(--transition);
}
.category-card:hover .cat-overlay { opacity: 1; }
.cat-info { padding: 24px; text-align: center; position: relative; }
.cat-icon {
    width: 60px; height: 60px;
    background: var(--cream);
    color: var(--primary);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: -50px auto 15px;
    position: relative;
    border: 4px solid var(--white);
    transition: var(--transition);
}
.category-card:hover .cat-icon {
    background: var(--primary); color: var(--white);
    transform: rotate(360deg) scale(1.1);
}
.cat-info h4 { font-size: 18px; margin-bottom: 4px; }
.cat-info > span { font-size: 13px; color: var(--gray); display: block; margin-bottom: 15px; }
.cat-link {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--primary); font-weight: 600; font-size: 13px;
    transition: var(--transition);
}
.category-card:hover .cat-link { gap: 14px; color: var(--accent); }

/* ================= PRODUCTS ================= */
.products-section { padding: 70px 0; background: var(--white); }
.product-card {
    background: var(--white);
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid var(--border);
    transition: var(--transition);
    height: 100%;
    display: flex; flex-direction: column;
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
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.7s cubic-bezier(0.4,0,0.2,1);
}
.product-card:hover .product-img img { transform: scale(1.1); }

.product-badge {
    position: absolute; top: 12px; left: 12px;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 11px; font-weight: 700;
    color: var(--white);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.badge-sale { background: var(--accent); }
.badge-hot { background: #E53935; }
.badge-new { background: var(--primary); }

.product-actions {
    position: absolute; top: 12px; right: 12px;
    display: flex; flex-direction: column; gap: 8px;
    opacity: 0; transform: translateX(20px);
    transition: var(--transition);
}
.product-card:hover .product-actions { opacity: 1; transform: translateX(0); }
.action-icon {
    width: 36px; height: 36px;
    background: var(--white);
    border: none; border-radius: 50%;
    color: var(--dark);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    font-size: 13px;
}
.action-icon:hover { background: var(--primary); color: var(--white); transform: scale(1.15); }

.product-info { padding: 18px; flex: 1; display: flex; flex-direction: column; }
.product-cat {
    font-size: 11px; color: var(--primary);
    font-weight: 600; text-transform: uppercase;
    letter-spacing: 1px; margin-bottom: 6px;
}
.product-name { font-size: 15px; margin-bottom: 10px; line-height: 1.4; }
.product-name a { color: var(--dark); }
.product-name a:hover { color: var(--primary); }

.product-rating { display: flex; align-items: center; gap: 2px; margin-bottom: 12px; }
.product-rating i { font-size: 12px; color: #DDD; }
.product-rating i.filled { color: var(--gold); }
.product-rating span { font-size: 11px; color: var(--gray); margin-left: 6px; }

.product-bottom {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: auto;
}
.product-price { display: flex; align-items: baseline; gap: 8px; }
.new-price { font-size: 17px; font-weight: 700; color: var(--primary-dark); }
.old-price { font-size: 13px; color: var(--gray); text-decoration: line-through; }

.add-cart-btn {
    width: 40px; height: 40px;
    background: var(--primary);
    color: var(--white);
    border: none; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    font-size: 14px;
}
.add-cart-btn:hover {
    background: var(--accent);
    transform: rotate(-15deg) scale(1.1);
    box-shadow: 0 6px 16px rgba(255,111,0,0.4);
}

/* ================= OFFER BANNER ================= */
.offer-banner { padding: 70px 0; }
.offer-inner {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    border-radius: 25px;
    padding: 50px;
    color: var(--white);
    position: relative;
    overflow: hidden;
}
.offer-inner::before {
    content: ''; position: absolute;
    top: -50px; right: 250px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,179,0,0.2), transparent 70%);
    border-radius: 50%;
}
.offer-tag {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.15);
    padding: 6px 16px; border-radius: 50px;
    font-size: 12px; font-weight: 600;
    margin-bottom: 15px;
    backdrop-filter: blur(10px);
}
.offer-inner h2 {
    color: var(--white);
    font-size: clamp(1.5rem, 3vw, 2.2rem);
    margin-bottom: 15px;
    line-height: 1.2;
}
.offer-inner h2 span { color: var(--gold); }
.offer-inner p { color: rgba(255,255,255,0.9); margin-bottom: 25px; font-size: 15px; }
.offer-inner p strong { color: var(--gold); background: rgba(0,0,0,0.2); padding: 2px 10px; border-radius: 5px; }
.offer-inner img {
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    max-width: 350px;
}

/* ================= WHY CHOOSE US ================= */
.why-section { padding: 70px 0; background: var(--white); }
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
    width: 75px; height: 75px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: var(--white);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px;
    margin: 0 auto 20px;
    transition: var(--transition);
    box-shadow: 0 10px 25px rgba(46,125,50,0.25);
}
.why-card:hover .why-icon {
    transform: rotate(-10deg) scale(1.1);
    background: linear-gradient(135deg, var(--accent), #FF9800);
}
.why-card h4 { font-size: 17px; margin-bottom: 10px; }
.why-card p { font-size: 13px; color: var(--gray); margin: 0; }

/* ================= NEWSLETTER ================= */
.newsletter-section { padding: 0 0 70px; background: var(--white); }
.newsletter-box {
    background: linear-gradient(135deg, var(--cream) 0%, #E8F5E9 100%);
    padding: 45px;
    border-radius: 25px;
    border: 1px solid var(--border);
}
.newsletter-box h3 {
    font-size: 22px; margin-bottom: 8px;
    display: flex; align-items: center; gap: 12px;
}
.newsletter-box h3 i { color: var(--primary); }
.newsletter-box p { color: var(--gray); margin: 0; font-size: 14px; }
.newsletter-form { display: flex; gap: 10px; }
.newsletter-form input {
    flex: 1; padding: 15px 22px;
    border: 2px solid var(--border);
    border-radius: 50px;
    font-family: 'Poppins',sans-serif;
    font-size: 14px;
    outline: none;
    transition: var(--transition);
    background: var(--white);
}
.newsletter-form input:focus { border-color: var(--primary); }
.newsletter-form button {
    background: var(--primary); color: var(--white);
    border: none; padding: 15px 30px;
    border-radius: 50px; font-weight: 600;
    font-size: 14px; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    transition: var(--transition);
    white-space: nowrap;
}
.newsletter-form button:hover { background: var(--primary-dark); transform: translateY(-2px); }

/* ================= FOOTER ================= */
.main-footer {
    background: #1A1A1A;
    color: rgba(255,255,255,0.8);
    padding: 60px 0 0;
}
.footer-top { padding-bottom: 40px; }
.footer-logo .logo-name { color: var(--white); }
.footer-logo .logo-tag { color: rgba(255,255,255,0.5); }
.footer-about p {
    font-size: 13px; line-height: 1.8;
    color: rgba(255,255,255,0.6);
    margin: 20px 0;
}
.social-links { display: flex; gap: 10px; }
.social-links a {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.8);
    font-size: 16px;
    transition: var(--transition);
}
.social-links a:hover { transform: translateY(-4px) scale(1.05); color: var(--white); }
.social-fb:hover { background: #1877F2; }
.social-ig:hover { background: linear-gradient(45deg,#F58529,#DD2A7B,#8134AF); }
.social-tw:hover { background: #1DA1F2; }
.social-wa:hover { background: #25D366; }
.social-yt:hover { background: #FF0000; }

.footer-title {
    color: var(--white);
    font-size: 16px;
    margin-bottom: 22px;
    position: relative;
    padding-bottom: 12px;
}
.footer-title::after {
    content: ''; position: absolute;
    bottom: 0; left: 0;
    width: 35px; height: 3px;
    background: var(--accent);
    border-radius: 2px;
}
.footer-links li { margin-bottom: 10px; }
.footer-links a {
    color: rgba(255,255,255,0.6);
    font-size: 13px;
    display: inline-flex; align-items: center; gap: 8px;
    transition: var(--transition);
}
.footer-links a i { font-size: 10px; color: var(--accent); transition: var(--transition); }
.footer-links a:hover { color: var(--white); padding-left: 5px; }

.footer-contact li {
    display: flex; gap: 14px;
    margin-bottom: 16px;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    line-height: 1.6;
}
.footer-contact li i {
    color: var(--accent);
    font-size: 15px;
    margin-top: 3px;
    flex-shrink: 0;
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding: 20px 0;
}
.footer-bottom-inner {
    display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 15px;
}
.footer-bottom-inner p {
    margin: 0; font-size: 13px;
    color: rgba(255,255,255,0.5);
}
.footer-bottom-inner p strong { color: var(--white); }
.footer-bottom-inner p i { color: #E53935; }
.payment-icons { display: flex; gap: 12px; font-size: 26px; color: rgba(255,255,255,0.4); }
.payment-icons i:hover { color: var(--white); transition: var(--transition); }

/* ================= BACK TO TOP ================= */
.back-to-top {
    position: fixed;
    bottom: 30px; right: 30px;
    width: 48px; height: 48px;
    background: var(--primary);
    color: var(--white);
    border: none; border-radius: 50%;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(46,125,50,0.4);
    opacity: 0; visibility: hidden;
    transform: translateY(20px);
    transition: var(--transition);
    z-index: 999;
}
.back-to-top.show { opacity: 1; visibility: visible; transform: translateY(0); }
.back-to-top:hover { background: var(--accent); transform: translateY(-5px); }

/* ================= WHATSAPP FLOAT ================= */
.whatsapp-float {
    position: fixed;
    bottom: 90px; right: 30px;
    width: 52px; height: 52px;
    background: #25D366;
    color: var(--white);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    box-shadow: 0 6px 20px rgba(37,211,102,0.4);
    z-index: 999;
    animation: waPulse 2s infinite;
}
.whatsapp-float:hover { color: var(--white); transform: scale(1.1); }
@keyframes waPulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(37,211,102,0.5); }
    50% { box-shadow: 0 0 0 15px rgba(37,211,102,0); }
}

/* ================= RESPONSIVE ================= */
@media (max-width: 991px) {
    .header-inner { flex-wrap: wrap; }
    .search-box { order: 3; flex-basis: 100%; max-width: 100%; margin-top: 10px; }
    .mobile-toggle { display: flex; align-items: center; justify-content: center; }
    .main-nav {
        position: fixed;
        top: 0; left: -100%;
        width: 280px; height: 100vh;
        background: var(--primary-dark);
        transition: var(--transition);
        z-index: 1100;
        padding-top: 70px;
        overflow-y: auto;
    }
    .main-nav.open { left: 0; box-shadow: var(--shadow-lg); }
    .nav-list { flex-direction: column; align-items: stretch; padding: 10px; }
    .nav-list > li > a { padding: 14px 18px; border-radius: 8px; }
    .dropdown-menu {
        position: static; opacity: 1; visibility: visible;
        transform: none; box-shadow: none;
        background: rgba(0,0,0,0.2);
        margin: 0 10px; min-width: auto;
        display: none;
    }
    .dropdown.active .dropdown-menu { display: block; }
    .dropdown-menu li a { color: rgba(255,255,255,0.85); }
    .dropdown-menu li a i { color: var(--gold); }
    .dropdown-menu li a:hover { background: rgba(255,255,255,0.1); color: var(--white); }
    .hero-img-wrap img { height: 380px; }
    .floating-card { padding: 10px 14px; }
    .floating-card i { width: 34px; height: 34px; font-size: 14px; }
    .offer-inner { padding: 35px 25px; }
    .newsletter-box { padding: 30px 25px; }
    .newsletter-form { flex-direction: column; margin-top: 20px; }
    .newsletter-form button { justify-content: center; }
}

@media (max-width: 767px) {
    .ticker-contact { display: none; }
    .hero-section { padding: 40px 0; }
    .hero-stats { gap: 25px; }
    .hero-stats .stat h3 { font-size: 22px; }
    .hero-img-wrap { margin-top: 40px; }
    .hero-img-wrap img { height: 300px; }
    .card-1 { top: 10px; left: 5px; }
    .card-2 { bottom: 10px; right: 5px; }
    .section-header { margin-bottom: 35px; }
    .offer-inner { text-align: center; }
    .offer-inner img { margin-top: 25px; max-width: 250px; }
    .footer-bottom-inner { justify-content: center; text-align: center; }
    .back-to-top { bottom: 20px; right: 20px; width: 42px; height: 42px; }
    .whatsapp-float { bottom: 75px; right: 20px; width: 46px; height: 46px; font-size: 20px; }
    .product-actions { opacity: 1; transform: translateX(0); }
}

@media (max-width: 480px) {
    .logo-name { font-size: 18px; }
    .logo-icon { width: 40px; height: 40px; font-size: 18px; }
    .action-btn span { display: none; }
    .action-btn { padding: 8px; }
    .hero-buttons { flex-direction: column; }
    .hero-buttons a { justify-content: center; }
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
                <a href="login.php" class="action-btn">
                    <i class="far fa-user"></i><span>Account</span>
                </a>
                <a href="wishlist.php" class="action-btn">
                    <i class="far fa-heart"></i><span>Wishlist</span>
                </a>
                <a href="cart.php" class="action-btn cart-btn">
                    <i class="fas fa-shopping-bag"></i><span>Cart</span>
                    <span class="cart-count">3</span>
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
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="shop.php"><i class="fas fa-store"></i> Shop</a></li>
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
                <li><a href="offers.php"><i class="fas fa-tags"></i> Offers</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
        </div>
    </nav>
</header>

<!-- ================= HERO ================= -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content" data-aos="fade-right">
                <span class="hero-badge"><i class="fas fa-leaf"></i> 100% Organic & Fresh</span>
                <h1 class="hero-title">
                    Fresh Groceries <br>
                    Delivered <span class="highlight">To Your Door</span>
                </h1>
                <p class="hero-text">
                    Shop from a wide range of farm-fresh vegetables, fruits, dairy and daily essentials.
                    Quality you can trust, prices you'll love.
                </p>
                <div class="hero-buttons">
                    <a href="shop.php" class="btn-primary-custom"><i class="fas fa-shopping-basket"></i> Shop Now</a>
                    <a href="offers.php" class="btn-outline-custom"><i class="fas fa-tag"></i> View Offers</a>
                </div>
                <div class="hero-stats">
                    <div class="stat"><h3>5000+</h3><p>Happy Customers</p></div>
                    <div class="stat"><h3>1500+</h3><p>Products</p></div>
                    <div class="stat"><h3>24/7</h3><p>Support</p></div>
                </div>
            </div>
            <div class="col-lg-6 hero-image" data-aos="fade-left">
                <div class="hero-img-wrap">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80" alt="Fresh Groceries">
                    <div class="floating-card card-1">
                        <i class="fas fa-truck"></i>
                        <div><strong>Free Delivery</strong><small>Orders above Rs. 1500</small></div>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-shield-alt"></i>
                        <div><strong>100% Fresh</strong><small>Quality Guaranteed</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FEATURES STRIP ================= -->
<section class="features-strip">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-truck-fast"></i></div>
                    <div><h5>Free Delivery</h5><p>On orders above Rs. 1500</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-rotate-left"></i></div>
                    <div><h5>Easy Returns</h5><p>7 days return policy</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <div><h5>24/7 Support</h5><p>Always here to help</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-lock"></i></div>
                    <div><h5>Secure Payment</h5><p>100% safe checkout</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CATEGORIES ================= -->
<section class="categories-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Shop By Category</span>
            <h2>Browse Our Categories</h2>
            <p>Everything you need, organized just the way you like</p>
        </div>
        <div class="row g-4">
            <?php
            $categories = [
                ['name'=>'Fresh Vegetables','img'=>'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&q=80','count'=>'120+ items','icon'=>'fa-carrot'],
                ['name'=>'Fresh Fruits','img'=>'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=500&q=80','count'=>'95+ items','icon'=>'fa-apple-alt'],
                ['name'=>'Dairy & Eggs','img'=>'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=500&q=80','count'=>'60+ items','icon'=>'fa-cheese'],
                ['name'=>'Meat & Fish','img'=>'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=500&q=80','count'=>'45+ items','icon'=>'fa-drumstick-bite'],
                ['name'=>'Bakery','img'=>'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&q=80','count'=>'70+ items','icon'=>'fa-bread-slice'],
                ['name'=>'Beverages','img'=>'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=500&q=80','count'=>'80+ items','icon'=>'fa-coffee'],
            ];
            foreach($categories as $i=>$cat):
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i*100; ?>">
                <a href="shop.php?cat=<?php echo strtolower(str_replace(' ','-',$cat['name'])); ?>" class="category-card">
                    <div class="cat-img">
                        <img src="<?php echo $cat['img']; ?>" alt="<?php echo $cat['name']; ?>">
                        <div class="cat-overlay"></div>
                    </div>
                    <div class="cat-info">
                        <div class="cat-icon"><i class="fas <?php echo $cat['icon']; ?>"></i></div>
                        <h4><?php echo $cat['name']; ?></h4>
                        <span><?php echo $cat['count']; ?></span>
                        <div class="cat-link">Shop Now <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= FEATURED PRODUCTS ================= -->
<section class="products-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Best Sellers</span>
            <h2>Featured Products</h2>
            <p>Hand-picked fresh products just for you</p>
        </div>
        <div class="row g-4">
            <?php
            $products = [
                ['name'=>'Organic Tomatoes','cat'=>'Vegetables','price'=>120,'old'=>180,'img'=>'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&q=80','rating'=>5,'badge'=>'Sale'],
                ['name'=>'Fresh Red Apples','cat'=>'Fruits','price'=>350,'old'=>450,'img'=>'https://images.unsplash.com/photo-1568702846914-96b305d2aaeb?w=500&q=80','rating'=>5,'badge'=>'Hot'],
                ['name'=>'Farm Fresh Milk','cat'=>'Dairy','price'=>220,'old'=>250,'img'=>'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&q=80','rating'=>4,'badge'=>'New'],
                ['name'=>'Whole Wheat Bread','cat'=>'Bakery','price'=>150,'old'=>0,'img'=>'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&q=80','rating'=>5,'badge'=>''],
                ['name'=>'Organic Carrots','cat'=>'Vegetables','price'=>90,'old'=>130,'img'=>'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=500&q=80','rating'=>4,'badge'=>'Sale'],
                ['name'=>'Fresh Bananas','cat'=>'Fruits','price'=>180,'old'=>0,'img'=>'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=500&q=80','rating'=>5,'badge'=>''],
                ['name'=>'Cheddar Cheese','cat'=>'Dairy','price'=>650,'old'=>800,'img'=>'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=500&q=80','rating'=>5,'badge'=>'Hot'],
                ['name'=>'Green Lettuce','cat'=>'Vegetables','price'=>100,'old'=>0,'img'=>'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=500&q=80','rating'=>4,'badge'=>'New'],
            ];
            foreach($products as $i=>$p):
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($i%4)*100; ?>">
                <div class="product-card">
                    <div class="product-img">
                        <img src="<?php echo $p['img']; ?>" alt="<?php echo $p['name']; ?>">
                        <?php if($p['badge']): ?>
                        <span class="product-badge badge-<?php echo strtolower($p['badge']); ?>"><?php echo $p['badge']; ?></span>
                        <?php endif; ?>
                        <div class="product-actions">
                            <button class="action-icon" title="Wishlist"><i class="far fa-heart"></i></button>
                            <button class="action-icon" title="Quick View"><i class="far fa-eye"></i></button>
                            <button class="action-icon" title="Compare"><i class="fas fa-exchange-alt"></i></button>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="product-cat"><?php echo $p['cat']; ?></span>
                        <h5 class="product-name"><a href="product.php"><?php echo $p['name']; ?></a></h5>
                        <div class="product-rating">
                            <?php for($s=0;$s<5;$s++): ?>
                                <i class="fas fa-star <?php echo $s<$p['rating']?'filled':''; ?>"></i>
                            <?php endfor; ?>
                            <span>(<?php echo rand(20,200); ?>)</span>
                        </div>
                        <div class="product-bottom">
                            <div class="product-price">
                                <span class="new-price">Rs. <?php echo $p['price']; ?></span>
                                <?php if($p['old']): ?>
                                <span class="old-price">Rs. <?php echo $p['old']; ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="add-cart-btn" title="Add to Cart"><i class="fas fa-shopping-bag"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="shop.php" class="btn-primary-custom">View All Products <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ================= OFFER BANNER ================= -->
<section class="offer-banner">
    <div class="container">
        <div class="offer-inner" data-aos="zoom-in">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="offer-tag"><i class="fas fa-gift"></i> Special Offer</span>
                    <h2>Get <span>30% OFF</span> On Your First Order</h2>
                    <p>Use code <strong>FRESH30</strong> at checkout. Fresh groceries delivered to your door in 60 minutes.</p>
                    <a href="shop.php" class="btn-white-custom">Grab The Deal <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="https://images.unsplash.com/photo-1610348725531-843dff563e2c?w=500&q=80" alt="Offer">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE US ================= -->
<section class="why-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Why FreshMart</span>
            <h2>Why Choose Us?</h2>
            <p>We're committed to bringing you the best</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-seedling"></i></div>
                    <h4>100% Organic</h4>
                    <p>Directly sourced from trusted local farms</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-clock"></i></div>
                    <h4>60-Min Delivery</h4>
                    <p>Fastest delivery in your city, guaranteed</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <h4>Best Prices</h4>
                    <p>Unbeatable prices with daily discounts</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="why-card">
                    <div class="why-icon"><i class="fas fa-award"></i></div>
                    <h4>Premium Quality</h4>
                    <p>Every product passes strict quality checks</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= NEWSLETTER ================= -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-box" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3><i class="fas fa-envelope-open-text"></i> Subscribe to Our Newsletter</h3>
                    <p>Get the latest offers and updates directly in your inbox</p>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Subscribed successfully!');">
                        <input type="email" placeholder="Enter your email address" required>
                        <button type="submit">Subscribe <i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="main-footer">
    <div class="container">
        <div class="row g-4 footer-top">
            <div class="col-lg-4 col-md-6">
                <div class="footer-about">
                    <a href="index.php" class="logo footer-logo">
                        <div class="logo-icon"><i class="fas fa-leaf"></i></div>
                        <div class="logo-text">
                            <span class="logo-name">Fresh<span>Mart</span></span>
                            <span class="logo-tag">Organic & Fresh</span>
                        </div>
                    </a>
                    <p>FreshMart brings you the freshest groceries from local farms to your home. Quality, freshness, and trust — since 2024.</p>
                    <div class="social-links">
                        <a href="https://facebook.com" target="_blank" class="social-fb" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com" target="_blank" class="social-ig" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://twitter.com" target="_blank" class="social-tw" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://wa.me/923001234567" target="_blank" class="social-wa" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://youtube.com" target="_blank" class="social-yt" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-angle-right"></i> Home</a></li>
                    <li><a href="shop.php"><i class="fas fa-angle-right"></i> Shop</a></li>
                    <li><a href="offers.php"><i class="fas fa-angle-right"></i> Offers</a></li>
                    <li><a href="about.php"><i class="fas fa-angle-right"></i> About Us</a></li>
                    <li><a href="contact.php"><i class="fas fa-angle-right"></i> Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6">
                <h4 class="footer-title">Categories</h4>
                <ul class="footer-links">
                    <li><a href="shop.php?cat=vegetables"><i class="fas fa-angle-right"></i> Vegetables</a></li>
                    <li><a href="shop.php?cat=fruits"><i class="fas fa-angle-right"></i> Fruits</a></li>
                    <li><a href="shop.php?cat=dairy"><i class="fas fa-angle-right"></i> Dairy</a></li>
                    <li><a href="shop.php?cat=meat"><i class="fas fa-angle-right"></i> Meat</a></li>
                    <li><a href="shop.php?cat=bakery"><i class="fas fa-angle-right"></i> Bakery</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h4 class="footer-title">Get In Touch</h4>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i><span>123 Main Boulevard, Gulberg III, Lahore, Pakistan</span></li>
                    <li><i class="fas fa-phone-alt"></i><span>+92 300 1234567</span></li>
                    <li><i class="fas fa-envelope"></i><span>support@freshmart.com</span></li>
                    <li><i class="fas fa-clock"></i><span>Mon - Sun: 8:00 AM - 11:00 PM</span></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <p>&copy; 2024 <strong>FreshMart</strong>. All Rights Reserved. Made with <i class="fas fa-heart"></i> in Pakistan</p>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></button>

<!-- WhatsApp Float -->
<a href="https://wa.me/923001234567" target="_blank" class="whatsapp-float"><i class="fab fa-whatsapp"></i></a>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// AOS Init
AOS.init({ duration: 800, once: true, offset: 100 });

// Mobile Menu Toggle
const mobileToggle = document.getElementById('mobileToggle');
const mainNav = document.getElementById('mainNav');
mobileToggle.addEventListener('click', () => {
    mainNav.classList.toggle('open');
    const icon = mobileToggle.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
});

// Mobile Dropdown Toggle
const catDropdown = document.getElementById('catDropdown');
if (catDropdown) {
    catDropdown.querySelector('.dropdown-toggle').addEventListener('click', (e) => {
        if (window.innerWidth <= 991) {
            e.preventDefault();
            catDropdown.classList.toggle('active');
        }
    });
}

// Back to Top
const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
    if (window.scrollY > 400) backToTop.classList.add('show');
    else backToTop.classList.remove('show');
});
backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Sticky Header Shadow on Scroll
window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
    } else {
        header.style.boxShadow = '0 2px 8px rgba(0,0,0,0.06)';
    }
});
</script>
</body>
</html>