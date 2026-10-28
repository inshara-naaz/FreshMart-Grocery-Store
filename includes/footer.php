<?php
// ============================================
// includes/footer.php
// Ye file har page ke bottom pe include hogi
// Ismein: Footer + WhatsApp + Back-to-top + Scripts
// ============================================
?>

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
                    <li><a href="track-order.php"><i class="fas fa-angle-right"></i> Track Order</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> <strong>FreshMart</strong>. All Rights Reserved. Made with <i class="fas fa-heart"></i> in Pakistan</p>
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

<!-- ================= BACK TO TOP ================= -->
<button class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></button>

<!-- ================= WHATSAPP FLOAT ================= -->
<a href="https://wa.me/923001234567" target="_blank" class="whatsapp-float" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- ================= SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // ============ AOS Init ============
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });

    // ============ Mobile Menu Toggle ============
    const mobileToggle = document.getElementById('mobileToggle');
    const mainNav = document.getElementById('mainNav');
    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', () => {
            mainNav.classList.toggle('open');
            const icon = mobileToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }

    // ============ Mobile Dropdown Toggle (All Dropdowns) ============
    document.querySelectorAll('.main-nav .dropdown').forEach(dropdown => {
        const toggleBtn = dropdown.querySelector('.dropdown-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                if (window.innerWidth <= 991) {
                    e.preventDefault();
                    // Baaki dropdowns band karo
                    document.querySelectorAll('.main-nav .dropdown').forEach(d => {
                        if (d !== dropdown) d.classList.remove('active');
                    });
                    dropdown.classList.toggle('active');
                }
            });
        }
    });
    // ============ Back to Top ============
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) backToTop.classList.add('show');
            else backToTop.classList.remove('show');
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ============ Sticky Header Shadow on Scroll ============
    const mainHeader = document.getElementById('mainHeader');
    if (mainHeader) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                mainHeader.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
            } else {
                mainHeader.style.boxShadow = '0 2px 8px rgba(0,0,0,0.06)';
            }
        });
    }

    // Product-JS
    // ============ Product Page: Thumbnail Switch ============
    const mainImg = document.getElementById('mainImg');
    const thumbs = document.querySelectorAll('.thumb');
    if (mainImg && thumbs.length) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                const newImg = thumb.getAttribute('data-img');
                mainImg.src = newImg;
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
    }

    // ============ Product Page: Quantity Option Select ============
    const qtyOptions = document.querySelectorAll('.qty-option');
    if (qtyOptions.length) {
        qtyOptions.forEach(opt => {
            opt.addEventListener('click', () => {
                qtyOptions.forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                opt.querySelector('input').checked = true;
            });
        });
    }

    // ============ Product Page: Qty +/- ============
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    if (qtyInput && qtyMinus && qtyPlus) {
        qtyMinus.addEventListener('click', () => {
            let v = parseInt(qtyInput.value) || 1;
            if (v > 1) qtyInput.value = v - 1;
        });
        qtyPlus.addEventListener('click', () => {
            let v = parseInt(qtyInput.value) || 1;
            if (v < 10) qtyInput.value = v + 1;
        });
    }

    // ============ Product Page: Tabs ============
    const tabBtns = document.querySelectorAll('.tab-btn');
    if (tabBtns.length) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = 'tab-' + btn.getAttribute('data-tab');
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    }

    // ============ Product Page: Star Rating Input ============
    const starsInput = document.querySelectorAll('.stars-input i');
    if (starsInput.length) {
        starsInput.forEach(star => {
            star.addEventListener('mouseenter', () => {
                const val = parseInt(star.getAttribute('data-value'));
                starsInput.forEach((s, i) => {
                    s.classList.toggle('active', i < val);
                });
            });
            star.addEventListener('click', () => {
                const val = parseInt(star.getAttribute('data-value'));
                starsInput.forEach((s, i) => {
                    s.classList.toggle('active', i < val);
                });
            });
        });
    }

    // ============ Add to Cart (Real AJAX) ============
    const addCartBtns = document.querySelectorAll('#addToCartBtn, .add-cart-btn');
    addCartBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            // Product ID
            const productId = btn.getAttribute('data-id');
            if (!productId) return;

            // Quantity (agar product.php pe hai to qty input se, warna 1)
            let qty = 1;
            const qtyInput = document.getElementById('qtyInput');
            if (qtyInput) qty = parseInt(qtyInput.value) || 1;

            // Unit + Price (agar product.php pe hai to selected quantity option se)
            let unitLabel = '1 kg';
            let unitPrice = 0;
            const selectedQty = document.querySelector('.qty-option.active input');
            if (selectedQty) {
                unitPrice = parseInt(selectedQty.getAttribute('data-price')) || 0;
                const qtyLabel = document.querySelector('.qty-option.active .qty-label');
                if (qtyLabel) unitLabel = qtyLabel.textContent.trim();
            }

            // Animation
            btn.style.transform = 'scale(0.9)';
            setTimeout(() => btn.style.transform = '', 150);

            // AJAX Call
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('qty', qty);
            formData.append('unit_label', unitLabel);
            formData.append('unit_price', unitPrice);

            fetch('ajax/add-to-cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message + ' 🛒');
                        // Cart badge update karo
                        const cartBadge = document.querySelector('.cart-count');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                            cartBadge.style.transform = 'scale(1.3)';
                            setTimeout(() => cartBadge.style.transform = 'scale(1)', 200);
                        }
                    } else {
                        showToast('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('Something went wrong. Try again.');
                });
        });
    });

    // ============ Toast Notification ============
    function showToast(message) {
        let toast = document.getElementById('customToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'customToast';
            toast.style.cssText = `
            position: fixed;
            top: 100px;
            right: 30px;
            background: #2E7D32;
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(46,125,50,0.3);
            font-weight: 600;
            font-size: 14px;
            z-index: 9999;
            transform: translateX(400px);
            transition: transform 0.4s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: Poppins, sans-serif;
        `;
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        setTimeout(() => toast.style.transform = 'translateX(400px)', 3000);
    }


    // ============ Shop Page: Filter Toggle (Mobile) ============
    const filterToggle = document.getElementById('filterToggle');
    const shopSidebar = document.getElementById('shopSidebar');
    if (filterToggle && shopSidebar) {
        filterToggle.addEventListener('click', () => {
            shopSidebar.classList.toggle('open');
            const icon = filterToggle.querySelector('i');
            const isOpen = shopSidebar.classList.contains('open');
            icon.className = isOpen ? 'fas fa-times' : 'fas fa-filter';
            filterToggle.childNodes[1].textContent = isOpen ? ' Hide Filters' : ' Show Filters';
        });
    }

    // ============ About Page: Stats Counter Animation ============
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target'));
                    const duration = 2000;
                    const step = target / (duration / 16);
                    let current = 0;

                    const updateCounter = () => {
                        current += step;
                        if (current < target) {
                            counter.textContent = Math.floor(current).toLocaleString();
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.textContent = target.toLocaleString() + (target >= 100 ? '+' : '');
                        }
                    };
                    updateCounter();
                    counterObserver.unobserve(counter);
                }
            });
        }, {
            threshold: 0.5
        });

        counters.forEach(counter => counterObserver.observe(counter));
    }

    // ============ Contact Page: FAQ Accordion ============
    const faqItems = document.querySelectorAll('.faq-item');
    if (faqItems.length > 0) {
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                // Close all
                faqItems.forEach(i => i.classList.remove('active'));
                // Open clicked if it was closed
                if (!isActive) item.classList.add('active');
            });
        });
    }

    // ============ Contact Page: Form Success ============
    function showContactSuccess() {
        const success = document.getElementById('contactSuccess');
        if (success) {
            success.classList.add('show');
            showToast('Message sent successfully! ✅');
        }
    }

    // ============ Offers Page: Countdown Timer ============
    function initCountdown() {
        // 2 din 18 ghante 45 min 30 sec set karo (demo)
        const endTime = new Date().getTime() + (2 * 24 * 60 * 60 * 1000) + (18 * 60 * 60 * 1000) + (45 * 60 * 1000) + (30 * 1000);

        const daysEl = document.getElementById('cd-days');
        const hoursEl = document.getElementById('cd-hours');
        const minsEl = document.getElementById('cd-mins');
        const secsEl = document.getElementById('cd-secs');

        if (!daysEl) return;

        function updateCountdown() {
            const now = new Date().getTime();
            const diff = endTime - now;

            if (diff <= 0) {
                daysEl.textContent = '00';
                hoursEl.textContent = '00';
                minsEl.textContent = '00';
                secsEl.textContent = '00';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);

            daysEl.textContent = String(days).padStart(2, '0');
            hoursEl.textContent = String(hours).padStart(2, '0');
            minsEl.textContent = String(mins).padStart(2, '0');
            secsEl.textContent = String(secs).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
    initCountdown();

    // ============ Offers Page: Flash Timer ============
    const flashTimer = document.getElementById('flashTimer');
    if (flashTimer) {
        let totalSeconds = 4 * 3600 + 23 * 60 + 15; // 04:23:15
        setInterval(() => {
            if (totalSeconds <= 0) return;
            totalSeconds--;
            const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const s = String(totalSeconds % 60).padStart(2, '0');
            flashTimer.textContent = `Ends in ${h}:${m}:${s}`;
        }, 1000);
    }

    // ============ Offers Page: Copy Coupon ============
    function copyCoupon(btn, code) {
        navigator.clipboard.writeText(code).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.add('copied');
            showToast('Coupon ' + code + ' copied! 🎉');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('copied');
            }, 2000);
        });
    }

    // ============ Offers Page: Deal Qty ============
    const dealQtyBtns = document.querySelectorAll('.deal-qty-btn');
    if (dealQtyBtns.length) {
        dealQtyBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('input');
                let val = parseInt(input.value) || 1;
                if (btn.textContent.trim() === '-') {
                    if (val > 1) val--;
                } else {
                    if (val < 10) val++;
                }
                input.value = val;
            });
        });
    }


    // ============ Cart Page: Qty +/- (Auto Update) ============
    document.querySelectorAll('.cart-item .qty-minus, .cart-item .qty-plus').forEach(btn => {
        btn.addEventListener('click', () => {
            const cartItem = btn.closest('.cart-item');
            const input = cartItem.querySelector('.qty-value');
            let val = parseInt(input.value) || 1;

            if (btn.classList.contains('qty-minus')) {
                if (val > 1) val--;
            } else {
                if (val < 20) val++;
            }
            input.value = val;

            // Subtotal update karo (live feel)
            const price = parseInt(input.getAttribute('data-price'));
            const newSubtotal = val * price;
            cartItem.querySelector('.subtotal-value').textContent = 'Rs. ' + newSubtotal;

            // Auto-submit form
            const form = cartItem.querySelector('.qty-form');
            if (form) form.submit();
        });
    });

    // ============ Cart Page: Qty Manual Change ============
    document.querySelectorAll('.cart-item .qty-value').forEach(input => {
        let timeout;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const cartItem = input.closest('.cart-item');
            const price = parseInt(input.getAttribute('data-price'));
            let val = parseInt(input.value) || 1;
            if (val < 1) val = 1;
            if (val > 20) val = 20;
            input.value = val;
            const newSubtotal = val * price;
            cartItem.querySelector('.subtotal-value').textContent = 'Rs. ' + newSubtotal;

            // 800ms baad auto-submit
            timeout = setTimeout(() => {
                const form = cartItem.querySelector('.qty-form');
                if (form) form.submit();
            }, 800);
        });
    });


    // ============ Checkout Page: Payment Method Toggle ============
    const paymentOptions = document.querySelectorAll('.payment-option');
    if (paymentOptions.length > 0) {
        paymentOptions.forEach(option => {
            option.addEventListener('click', () => {
                paymentOptions.forEach(o => o.classList.remove('active'));
                option.classList.add('active');
                option.querySelector('input').checked = true;

                // Card details show/hide
                const cardDetails = document.getElementById('cardDetails');
                const value = option.querySelector('input').value;
                if (cardDetails) {
                    cardDetails.style.display = value === 'card' ? 'block' : 'none';
                }
            });
        });
    }

    // ============ Checkout Page: Shipping Same as Billing ============
    const sameAsBilling = document.getElementById('sameAsBilling');
    const shippingCard = document.getElementById('shippingCard');
    if (sameAsBilling && shippingCard) {
        sameAsBilling.addEventListener('change', () => {
            shippingCard.style.display = sameAsBilling.checked ? 'none' : 'block';
        });
    }

    // ============ My Orders: Toggle Details ============
    document.querySelectorAll('.order-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const details = document.getElementById(targetId);

            if (details.style.display === 'none') {
                details.style.display = 'block';
                btn.classList.add('open');
            } else {
                details.style.display = 'none';
                btn.classList.remove('open');
            }
        });
    });

    // ============ My Orders: Reorder ============
    function reorder(orderId) {
        showToast('Reorder feature coming soon! 🛒');
    }
</script>

</body>

</html>