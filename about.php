<?php
// ============================================
// about.php - About Us Page
// ============================================

$pageTitle = 'About Us - FreshMart';
$cartCount = 0;
$userName = null;

include 'includes/header.php';
?>

<!-- ================= PAGE HEADER ================= -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb-nav breadcrumb-light">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-angle-right separator"></i>
            <span class="current">About Us</span>
        </nav>
        <div class="page-hero-content" data-aos="fade-up">
            <span class="page-hero-tag"><i class="fas fa-seedling"></i> Our Story</span>
            <h1>About <span class="highlight">FreshMart</span></h1>
            <p>Fresh groceries, delivered with love — since 2024</p>
        </div>
    </div>
</section>

<!-- ================= OUR STORY ================= -->
<section class="story-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="story-images">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=700&q=80" alt="Fresh Vegetables" class="story-img-main">
                    <img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=400&q=80" alt="Farmer" class="story-img-small">
                    <div class="story-experience">
                        <h3>2+</h3>
                        <p>Years of<br>Excellence</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="section-tag-left">Our Story</span>
                <h2 class="story-title">From Local Farms <br>To Your <span class="highlight-green">Doorstep</span></h2>
                <p class="story-text">
                    FreshMart's journey began in 2024 with a simple vision:
                    <strong>"To deliver fresh and organic groceries to every home — easily and affordably."</strong>
                </p>
                <p class="story-text">
                    Today, we proudly deliver fresh vegetables, fruits, dairy, and daily essentials to over 5,000 families
                    across 5 cities in Pakistan. Every product comes directly from <strong>local farmers</strong> — with no middlemen involved.
                </p>
                <ul class="story-list">
                    <li><i class="fas fa-check-circle"></i> Direct from local farms — no middlemen</li>
                    <li><i class="fas fa-check-circle"></i> 100% organic & pesticide-free</li>
                    <li><i class="fas fa-check-circle"></i> Same-day delivery within 60 minutes</li>
                    <li><i class="fas fa-check-circle"></i> 100% money-back guarantee</li>
                </ul>
                <a href="shop.php" class="btn-primary-custom">
                    <i class="fas fa-shopping-basket"></i> Explore Our Products
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ================= STATS COUNTER ================= -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="stat-box">
                    <i class="fas fa-users"></i>
                    <h2 class="counter" data-target="5000">0</h2>
                    <p>Happy Customers</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-box">
                    <i class="fas fa-box-open"></i>
                    <h2 class="counter" data-target="72">0</h2>
                    <p>Products Available</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-box">
                    <i class="fas fa-truck-fast"></i>
                    <h2 class="counter" data-target="15000">0</h2>
                    <p>Orders Delivered</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-box">
                    <i class="fas fa-star"></i>
                    <h2 class="counter" data-target="98">0</h2>
                    <p>% Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= MISSION & VISION ================= -->
<section class="mission-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">Our Purpose</span>
            <h2>Mission & Vision</h2>
            <p>The values that drive us every single day</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="mv-card mv-mission">
                    <div class="mv-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>
                        To deliver fresh, organic, and affordable groceries to every Pakistani household —
                        so families can live healthier lives without any hassle. We support local farmers
                        and bring their products directly to our customers.
                    </p>
                    <ul class="mv-list">
                        <li><i class="fas fa-arrow-right"></i> Fresh & organic products</li>
                        <li><i class="fas fa-arrow-right"></i> Fair prices for everyone</li>
                        <li><i class="fas fa-arrow-right"></i> Support local farmers</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="mv-card mv-vision">
                    <div class="mv-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>
                        To become Pakistan's most trusted online grocery platform — where people trust us for
                        <strong>quality, freshness, and reliability</strong>. We want FreshMart to be a name
                        spoken with respect in every home.
                    </p>
                    <ul class="mv-list">
                        <li><i class="fas fa-arrow-right"></i> Trusted by millions</li>
                        <li><i class="fas fa-arrow-right"></i> Nationwide delivery</li>
                        <li><i class="fas fa-arrow-right"></i> Innovative & sustainable</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= OUR VALUES ================= -->
<section class="values-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">What We Stand For</span>
            <h2>Our Core Values</h2>
            <p>The principles that guide everything we do</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-leaf"></i></div>
                    <h4>Freshness</h4>
                    <p>Every product is freshly harvested and delivered the same day</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-handshake"></i></div>
                    <h4>Trust</h4>
                    <p>The trust of 5,000+ families is our greatest achievement</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-medal"></i></div>
                    <h4>Quality</h4>
                    <p>Every product passes strict quality checks before delivery</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-heart"></i></div>
                    <h4>Care</h4>
                    <p>Customer satisfaction is our number one priority</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= MEET OUR TEAM ================= -->
<section class="team-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">The People Behind</span>
            <h2>Meet Our Team</h2>
            <p>Passionate people working to serve you better</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80" alt="Ahmed Raza">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h4>Ahmed Raza</h4>
                        <span>Founder & CEO</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&q=80" alt="Sara Khan">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h4>Sara Khan</h4>
                        <span>Operations Manager</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&q=80" alt="Bilal Ahmed">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h4>Bilal Ahmed</h4>
                        <span>Head of Delivery</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="team-card">
                    <div class="team-img">
                        <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=400&q=80" alt="Ayesha Malik">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h4>Ayesha Malik</h4>
                        <span>Customer Support</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ================= TESTIMONIALS ================= -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-tag">What They Say</span>
            <h2>Customer Testimonials</h2>
            <p>Real stories from our happy customers</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up">
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="testimonial-text">
                        "FreshMart has made my life so much easier! Now I can get fresh vegetables and fruits
                        delivered without leaving my home. The quality is always top-notch."
                    </p>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&q=80" alt="Customer">
                        <div>
                            <h5>Hira Sheikh</h5>
                            <span>Lahore</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="testimonial-text">
                        "Amazing service! I placed an order and it was delivered in just 45 minutes.
                        The prices are also very reasonable compared to the market. Highly recommended!"
                    </p>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&q=80" alt="Customer">
                        <div>
                            <h5>Usman Tariq</h5>
                            <span>Karachi</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <p class="testimonial-text">
                        "I've been using FreshMart for 6 months now. Every time I receive fresh and organic products.
                        Their customer support is also very helpful. 5 stars!"
                    </p>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&q=80" alt="Customer">
                        <div>
                            <h5>Maryam Ali</h5>
                            <span>Islamabad</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CTA SECTION ================= -->
<section class="cta-section">
    <div class="container">
        <div class="cta-inner" data-aos="zoom-in">
            <div class="cta-content">
                <h2>Ready To Experience Freshness?</h2>
                <p>Join 5,000+ happy families who trust FreshMart for their daily groceries</p>
                <div class="cta-buttons">
                    <a href="shop.php" class="btn-white-custom">
                        <i class="fas fa-shopping-basket"></i> Start Shopping
                    </a>
                    <a href="contact.php" class="btn-outline-white-custom">
                        <i class="fas fa-envelope"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>