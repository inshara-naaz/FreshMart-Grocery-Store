<?php
// ============================================
// contact.php - Contact Us (DB Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';
include 'includes/cart-functions.php';

// ============ HANDLE FORM SUBMIT ============
$contactSuccess = false;
$contactErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || strlen($name) < 3) $contactErrors[] = 'Name must be at least 3 characters';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $contactErrors[] = 'Invalid email address';
    if (empty($message) || strlen($message) < 10) $contactErrors[] = 'Message must be at least 10 characters';
    
    if (empty($contactErrors)) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        $contactSuccess = true;
    }
}

$pageTitle = 'Contact Us - FreshMart';
$cartCount = getCartCount($pdo);
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<!-- ================= PAGE HERO ================= -->
<section class="contact-hero">
    <div class="container">
        <nav class="breadcrumb-nav breadcrumb-light">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">Contact Us</span>
        </nav>
        <div class="contact-hero-content" data-aos="fade-up">
            <span class="contact-hero-tag"><i class="fas fa-comments"></i> We'd Love To Hear From You</span>
            <h1>Get In <span class="highlight">Touch</span></h1>
            <p>Have a question, feedback, or need help? Our team is here 24/7.</p>
        </div>
    </div>
</section>

<!-- ================= CONTACT INFO CARDS ================= -->
<section class="contact-info-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="contact-info-card">
                    <div class="ci-icon ci-phone"><i class="fas fa-phone-alt"></i></div>
                    <h4>Call Us</h4>
                    <p>Mon - Sun, 24/7</p>
                    <a href="tel:+923001234567">+92 300 1234567</a>
                    <a href="tel:+924212345678">+92 42 1234 5678</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-info-card">
                    <div class="ci-icon ci-email"><i class="fas fa-envelope"></i></div>
                    <h4>Email Us</h4>
                    <p>We reply within 2 hours</p>
                    <a href="mailto:support@freshmart.com">support@freshmart.com</a>
                    <a href="mailto:info@freshmart.com">info@freshmart.com</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-info-card">
                    <div class="ci-icon ci-location"><i class="fas fa-map-marker-alt"></i></div>
                    <h4>Visit Us</h4>
                    <p>Our Head Office</p>
                    <span>123 Main Boulevard,<br>Gulberg III, Lahore</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="contact-info-card">
                    <div class="ci-icon ci-chat"><i class="fab fa-whatsapp"></i></div>
                    <h4>Live Chat</h4>
                    <p>Instant support</p>
                    <a href="https://wa.me/923001234567" target="_blank" class="ci-whatsapp-btn">
                        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CONTACT FORM + MAP ================= -->
<section class="contact-main-section">
    <div class="container">
        <div class="row g-5 align-items-stretch">
            
            <!-- LEFT: Contact Form -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="contact-form-wrap">
                    <span class="section-tag-left">Send a Message</span>
                    <h2>Let's Talk About Your Needs</h2>
                    <p class="form-subtitle">Fill out the form below and we'll get back to you as soon as possible.</p>
                    
                    <?php if ($contactSuccess): ?>
                    <div class="alert alert-success" style="background:#d4edda;color:#155724;padding:15px 20px;border-radius:12px;margin-bottom:20px;border-left:4px solid #28a745;">
                        <i class="fas fa-check-circle"></i> <strong>Message Sent Successfully!</strong> Thank you for contacting us. Our team will get back to you within 2 hours.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($contactErrors)): ?>
                    <div class="alert alert-error" style="background:#f8d7da;color:#721c24;padding:15px 20px;border-radius:12px;margin-bottom:20px;border-left:4px solid #dc3545;">
                        <i class="fas fa-exclamation-circle"></i> <strong>Please fix:</strong>
                        <ul style="margin:8px 0 0 20px;">
                            <?php foreach($contactErrors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form class="contact-form" method="POST" action="contact.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name <span class="req">*</span></label>
                                    <input type="text" name="name" placeholder="Enter your name" required value="<?php echo htmlspecialchars($userName ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address <span class="req">*</span></label>
                                    <input type="email" name="email" placeholder="example@email.com" required value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" placeholder="+92 300 1234567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subject <span class="req">*</span></label>
                                    <select name="subject" required>
                                        <option value="">Select a topic</option>
                                        <option>Order Inquiry</option>
                                        <option>Product Question</option>
                                        <option>Delivery Issue</option>
                                        <option>Feedback / Suggestion</option>
                                        <option>Business Partnership</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Your Message <span class="req">*</span></label>
                                    <textarea name="message" rows="5" placeholder="Write your message here..." required></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-submit-contact">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- RIGHT: Map -->
            <div class="col-lg-5" data-aos="fade-left">
                <div class="map-wrapper">
                    <div class="map-header">
                        <h4><i class="fas fa-map-marked-alt"></i> Find Us Here</h4>
                        <p>123 Main Boulevard, Gulberg III, Lahore</p>
                    </div>
                    <div class="map-embed">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13605.614!2d74.3436!3d31.5204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzHCsDMxJzEzLjQiTiA3NMKwMjAnMzYuOSJF!5e0!3m2!1sen!2s!4v1234567890" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                    <div class="map-footer">
                        <div class="map-info-item">
                            <i class="fas fa-clock"></i>
                            <div><strong>Working Hours</strong><span>Mon - Sun: 8:00 AM - 11:00 PM</span></div>
                        </div>
                        <div class="map-info-item">
                            <i class="fas fa-phone-alt"></i>
                            <div><strong>Quick Contact</strong><span>+92 300 1234567</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= STORE LOCATIONS ================= -->
<section class="locations-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Our Presence</span>
            <h2>Visit Our Stores</h2>
            <p>We're available in 5 major cities across Pakistan</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="location-card">
                    <div class="location-img">
                        <img src="https://images.unsplash.com/photo-1548013146-72479768bada?w=600&q=80" alt="Lahore">
                        <span class="location-badge">Head Office</span>
                    </div>
                    <div class="location-info">
                        <h4><i class="fas fa-map-marker-alt"></i> Lahore</h4>
                        <p>123 Main Boulevard, Gulberg III<br>Lahore, Punjab 54000</p>
                        <div class="location-actions">
                            <a href="tel:+924212345678"><i class="fas fa-phone-alt"></i> Call</a>
                            <a href="#"><i class="fas fa-directions"></i> Directions</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="location-card">
                    <div class="location-img">
                        <img src="https://images.unsplash.com/photo-1587474260584-136574528ed5?w=600&q=80" alt="Karachi">
                        <span class="location-badge">Branch</span>
                    </div>
                    <div class="location-info">
                        <h4><i class="fas fa-map-marker-alt"></i> Karachi</h4>
                        <p>456 Shahrah-e-Faisal, Clifton<br>Karachi, Sindh 75600</p>
                        <div class="location-actions">
                            <a href="tel:+922112345678"><i class="fas fa-phone-alt"></i> Call</a>
                            <a href="#"><i class="fas fa-directions"></i> Directions</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="location-card">
                    <div class="location-img">
                        <img src="https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600&q=80" alt="Islamabad">
                        <span class="location-badge">Branch</span>
                    </div>
                    <div class="location-info">
                        <h4><i class="fas fa-map-marker-alt"></i> Islamabad</h4>
                        <p>789 Blue Area, F-7 Markaz<br>Islamabad, 44000</p>
                        <div class="location-actions">
                            <a href="tel:+925112345678"><i class="fas fa-phone-alt"></i> Call</a>
                            <a href="#"><i class="fas fa-directions"></i> Directions</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FAQ SECTION ================= -->
<section class="faq-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Need Help?</span>
            <h2>Frequently Asked Questions</h2>
            <p>Quick answers to the most common questions</p>
        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="faq-wrapper">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 5");
                    $homeFaqs = $stmt->fetchAll();
                    foreach ($homeFaqs as $i => $faq):
                    ?>
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
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= SOCIAL CTA ================= -->
<section class="social-cta-section">
    <div class="container">
        <div class="social-cta-inner" data-aos="zoom-in">
            <div class="social-cta-content">
                <h2>Connect With Us On Social Media</h2>
                <p>Follow us for daily deals, recipes, and fresh arrivals</p>
                <div class="social-cta-icons">
                    <a href="https://facebook.com" target="_blank" class="social-big-btn fb"><i class="fab fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://instagram.com" target="_blank" class="social-big-btn ig"><i class="fab fa-instagram"></i><span>Instagram</span></a>
                    <a href="https://twitter.com" target="_blank" class="social-big-btn tw"><i class="fab fa-twitter"></i><span>Twitter</span></a>
                    <a href="https://youtube.com" target="_blank" class="social-big-btn yt"><i class="fab fa-youtube"></i><span>YouTube</span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>