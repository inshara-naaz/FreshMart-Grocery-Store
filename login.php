<?php
// ============================================
// login.php - User Login (Database)
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

// ============ HANDLE LOGIN ============
$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $oldEmail = $email;

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    }
    if (empty($password)) {
        $errors['password'] = 'Please enter your password';
    }

    // Check credentials
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_role'] = $user['role'];

                if ($remember) {
                    setcookie('freshmart_email', $email, time() + (30 * 24 * 60 * 60), '/');
                }

                // Check karein agar Admin hai to Dashboard bhejein
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                    // Admin ke liye admin session bhi set karein taake dashboard khul jaye
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    header("Location: admin/index.php");
                    exit();
                } else {
                    $redirect = $_GET['redirect'] ?? 'my-orders.php';
                    header('Location: ' . $redirect);
                    exit();
                }
                }
            } else {
                $errors['general'] = 'Incorrect email or password';
            }
        } else {
            $errors['general'] = 'No account found with this email. Please sign up first.';
        }
    }


// Pre-fill from cookie
if (isset($_COOKIE['freshmart_email']) && empty($oldEmail)) {
    $oldEmail = $_COOKIE['freshmart_email'];
}

$pageTitle = 'Login - FreshMart';
$userName = null;

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="row g-0 auth-wrapper">

            <!-- LEFT: Visual -->
            <div class="col-lg-5 auth-visual auth-visual-login">
                <div class="auth-visual-content">
                    <div class="auth-logo">
                        <div class="logo-icon"><i class="fas fa-leaf"></i></div>
                        <span class="logo-name">Fresh<span>Mart</span></span>
                    </div>
                    <h2>Welcome Back!</h2>
                    <p>Login to continue shopping fresh groceries and track your orders easily.</p>

                    <ul class="auth-benefits">
                        <li><i class="fas fa-check-circle"></i> View order history</li>
                        <li><i class="fas fa-check-circle"></i> Faster checkout</li>
                        <li><i class="fas fa-check-circle"></i> Save multiple addresses</li>
                        <li><i class="fas fa-check-circle"></i> Personalized recommendations</li>
                    </ul>

                    <div class="auth-quote">
                        <i class="fas fa-quote-left"></i>
                        <p>"I order from FreshMart every week. Their service is unbeatable!"</p>
                        <span>— Usman T., Karachi</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Form -->
            <div class="col-lg-7 auth-form-side">
                <div class="auth-form-wrap">

                    <div class="auth-header">
                        <h1>Sign In</h1>
                        <p>New to FreshMart? <a href="signup.php">Create an account</a></p>
                    </div>

                    <?php if (isset($errors['general'])): ?>
                        <div class="auth-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['general']; ?>
                        </div>
                    <?php endif; ?>



                    <form method="POST" action="login.php" class="auth-form">

                        <div class="form-group">
                            <label>Email Address <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="example@email.com" value="<?php echo htmlspecialchars($oldEmail); ?>" required autofocus>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <span class="form-error"><?php echo $errors['email']; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Password <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Enter your password" id="loginPassword" required>
                                <button type="button" class="toggle-password" data-target="loginPassword">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <span class="form-error"><?php echo $errors['password']; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="auth-options">
                            <label class="custom-checkbox small">
                                <input type="checkbox" name="remember">
                                <span class="checkmark"></span>
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn-auth">
                            <i class="fas fa-sign-in-alt"></i> Sign In
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

                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>