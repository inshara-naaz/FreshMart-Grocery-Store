<?php
// ============================================
// faqs.php - FAQs (DB Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// Fetch FAQs
$stmt = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC");
$faqs = $stmt->fetchAll();

$pageTitle = 'FAQs - FreshMart';
$cartCount = getCartCount($pdo);
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= PAGE HERO ================= -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb-nav breadcrumb-light">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">FAQs</span>
        </nav>
        <div class="page-hero-content" data-aos="fade-up">
            <span class="page-hero-tag"><i class="fas fa-circle-question"></i> Help Center</span>
            <h1>Frequently Asked <span class="highlight">Questions</span></h1>
            <p>Find quick answers to common questions</p>
        </div>
    </div>
</section>

<!-- ================= FAQ SECTION ================= -->
<section class="faq-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                
                <!-- Quick Links -->
                <div class="faq-quick-links" data-aos="fade-up">
                    <a href="track-order.php" class="quick-link-card">
                        <i class="fas fa-truck"></i>
                        <span>Track Order</span>
                    </a>
                    <a href="my-orders.php" class="quick-link-card">
                        <i class="fas fa-box"></i>
                        <span>My Orders</span>
                    </a>
                    <a href="contact.php" class="quick-link-card">
                        <i class="fas fa-headset"></i>
                        <span>Contact Us</span>
                    </a>
                </div>
                
                <!-- FAQs from DB -->
                <div class="faq-wrapper">
                    <?php if (empty($faqs)): ?>
                    <div class="no-products">
                        <i class="fas fa-circle-question"></i>
                        <h3>No FAQs available</h3>
                        <p>Please check back later</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($faqs as $i => $faq): ?>
                        <div class="faq-item <?php echo $i === 0 ? 'active' : ''; ?>" data-aos="fade-up">
                            <button class="faq-question">
                                <span><?php echo htmlspecialchars($faq['question']); ?></span>
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="faq-answer">
                                <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Still Have Questions -->
                <div class="faq-cta" data-aos="fade-up">
                    <i class="fas fa-comments"></i>
                    <h3>Still Have Questions?</h3>
                    <p>Our support team is here to help you 24/7</p>
                    <a href="contact.php" class="btn-primary-custom">
                        <i class="fas fa-headset"></i> Contact Support
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>