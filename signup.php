<?php
// ============================================
// signup.php - User Registration (Database)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

// ============ IF ALREADY LOGGED IN ============
if (isset($_SESSION['user_id'])) {
    header('Location: my-orders.php');
    exit;
}

// ============ HANDLE FORM SUBMIT ============
$errors = [];
$success = false;
$oldData = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $oldData = ['name' => $name, 'email' => $email, 'phone' => $phone];

    // Validation
    if (empty($name) || strlen($name) < 3) {
        $errors['name'] = 'Name must be at least 3 characters';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    if (empty($phone) || strlen(preg_replace('/\D/', '', $phone)) < 10) {
        $errors['phone'] = 'Please enter a valid phone number (at least 10 digits)';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    if (!isset($_POST['terms'])) {
        $errors['terms'] = 'You must agree to the Terms & Conditions';
    }

    // Check email exists
    if (empty($errors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already registered. Try logging in.';
        }
    }

    // Insert user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $phone, $hashedPassword]);

        $userId = $pdo->lastInsertId();

        // Auto-login
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_role'] = 'user';

        $success = true;
        header('Refresh: 2; url=my-orders.php');
    }
}

$pageTitle = 'Sign Up - FreshMart';
$userName = $_SESSION['user_name'] ?? null;

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="row g-0 auth-wrapper">

            <!-- LEFT: Visual -->
            <div class="col-lg-5 auth-visual">
                <div class="auth-visual-content">
                    <div class="auth-logo">
                        <div class="logo-icon"><i class="fas fa-leaf"></i></div>
                        <span class="logo-name">Fresh<span>Mart</span></span>
                    </div>
                    <h2>Join FreshMart Today!</h2>
                    <p>Get fresh groceries delivered to your door in 60 minutes. Sign up now and enjoy exclusive benefits.</p>

                    <ul class="auth-benefits">
                        <li><i class="fas fa-check-circle"></i> Free delivery on first order</li>
                        <li><i class="fas fa-check-circle"></i> Exclusive member discounts</li>
                        <li><i class="fas fa-check-circle"></i> Save your favorite products</li>
                        <li><i class="fas fa-check-circle"></i> Track orders in real-time</li>
                        <li><i class="fas fa-check-circle"></i> Priority customer support</li>
                    </ul>

                    <div class="auth-quote">
                        <i class="fas fa-quote-left"></i>
                        <p>"FreshMart has made my life so much easier. Quality is always top-notch!"</p>
                        <span>— Hira S., Lahore</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Form -->
            <div class="col-lg-7 auth-form-side">
                <div class="auth-form-wrap">

                    <?php if ($success): ?>
                        <div class="auth-success">
                            <div class="auth-success-icon"><i class="fas fa-check"></i></div>
                            <h2>Account Created! 🎉</h2>
                            <p>Welcome to FreshMart, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>
                            <p class="auth-success-redirect">
                                <i class="fas fa-spinner fa-spin"></i> Redirecting to your orders...
                            </p>
                        </div>

                    <?php else: ?>
                        <div class="auth-header">
                            <h1>Create Account</h1>
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                        </div>

                        <?php if (isset($errors['terms'])): ?>
                            <div class="auth-alert">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $errors['terms']; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="signup.php" class="auth-form">

                            <div class="form-group">
                                <label>Full Name <span class="req">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($oldData['name']); ?>" required>
                                </div>
                                <?php if (isset($errors['name'])): ?>
                                    <span class="form-error"><?php echo $errors['name']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label>Email Address <span class="req">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" name="email" placeholder="example@email.com" value="<?php echo htmlspecialchars($oldData['email']); ?>" required>
                                </div>
                                <?php if (isset($errors['email'])): ?>
                                    <span class="form-error"><?php echo $errors['email']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label>Phone Number <span class="req">*</span></label>
                                <div class="input-with-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" name="phone" placeholder="+92 300 1234567" value="<?php echo htmlspecialchars($oldData['phone']); ?>" required>
                                </div>
                                <?php if (isset($errors['phone'])): ?>
                                    <span class="form-error"><?php echo $errors['phone']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password <span class="req">*</span></label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" name="password" placeholder="Min. 6 characters" id="passwordInput" required>
                                            <button type="button" class="toggle-password" data-target="passwordInput">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <?php if (isset($errors['password'])): ?>
                                            <span class="form-error"><?php echo $errors['password']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm Password <span class="req">*</span></label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" name="confirm_password" placeholder="Re-enter password" id="confirmInput" required>
                                            <button type="button" class="toggle-password" data-target="confirmInput">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <?php if (isset($errors['confirm_password'])): ?>
                                            <span class="form-error"><?php echo $errors['confirm_password']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="terms-check">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="terms">
                                    <span class="checkmark"></span>
                                    <span>I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></span>
                                </label>
                            </div>

                            <button type="submit" class="btn-auth">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>

                            <div class="auth-divider">
                                <span>or continue with</span>
                            </div>

                            <div class="social-auth">
                                <button type="button" class="social-auth-btn google">
                                    <i class="fab fa-google"></i> Google
                                </button>
                                <button type="button" class="social-auth-btn facebook">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </button>
                            </div>

                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>