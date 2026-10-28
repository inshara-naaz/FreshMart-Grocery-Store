<?php
// ============================================
// admin/login.php - Admin Login
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============ IF ALREADY LOGGED IN ============
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

include '../includes/db.php';

// ============ HANDLE LOGIN ============
$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    $oldEmail = $email;
    
    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    if (empty($password)) {
        $errors['password'] = 'Please enter your password';
    }
    
    // Check credentials
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'manager')");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                
                header('Location: index.php');
                exit;
            } else {
                $errors['general'] = 'Incorrect password. Please try again.';
            }
        } else {
            $errors['general'] = 'No admin account found with this email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - FreshMart</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --primary: #2E7D32;
    --primary-dark: #1B5E20;
    --primary-light: #4CAF50;
    --accent: #FF6F00;
    --gold: #FFB300;
    --dark: #1A1A1A;
    --gray: #616161;
    --border: #E8E8E8;
    --white: #FFFFFF;
    --danger: #E53935;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    font-family: 'Poppins', sans-serif;
    background: #F0F2F5;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-x: hidden;
}

/* ================= LOGIN WRAPPER ================= */
.admin-login-wrapper {
    width: 100%;
    max-width: 1100px;
    background: var(--white);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(0,0,0,0.15);
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 600px;
    animation: fadeInUp 0.6s ease;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ================= LEFT: VISUAL ================= */
.admin-login-visual {
    background: linear-gradient(135deg, #1A1A1A 0%, #2E7D32 100%);
    padding: 55px 45px;
    color: var(--white);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.admin-login-visual::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 350px; height: 350px;
    background: radial-gradient(circle, rgba(255,179,0,0.2), transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}
.admin-login-visual::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(76,175,80,0.25), transparent 70%);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite;
}
@keyframes float {
    0%,100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.visual-content {
    position: relative;
    z-index: 1;
}

.visual-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 50px;
}
.visual-brand-icon {
    width: 55px; height: 55px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--white);
}
.visual-brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}
.visual-brand-name {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
}
.visual-brand-name span { color: var(--gold); }
.visual-brand-tag {
    font-size: 10px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 2px;
    text-transform: uppercase;
}

.visual-title {
    font-size: 36px;
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: 18px;
    letter-spacing: -1px;
}
.visual-title span {
    background: linear-gradient(135deg, var(--gold), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.visual-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,0.75);
    line-height: 1.8;
    margin-bottom: 40px;
    max-width: 380px;
}

.visual-features {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.visual-feature {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13px;
    color: rgba(255,255,255,0.9);
}
.visual-feature-icon {
    width: 40px; height: 40px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    color: var(--gold);
    flex-shrink: 0;
}

/* ================= RIGHT: FORM ================= */
.admin-login-form-wrap {
    padding: 60px 55px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.form-header {
    margin-bottom: 35px;
}
.form-header h1 {
    font-size: 28px;
    font-weight: 800;
    color: var(--dark);
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}
.form-header p {
    font-size: 14px;
    color: var(--gray);
    margin: 0;
}

/* Alert */
.admin-alert {
    background: rgba(229,57,53,0.1);
    color: #B71C1C;
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid var(--danger);
    animation: shake 0.4s ease;
}
@keyframes shake {
    0%,100% { transform: translateX(0); }
    25% { transform: translateX(-8px); }
    75% { transform: translateX(8px); }
}
.admin-alert i { color: var(--danger); font-size: 16px; }


/* Form */
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
.admin-form-group label .req { color: var(--danger); }

.admin-input-wrap {
    position: relative;
}
.admin-input-wrap > i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 14px;
    pointer-events: none;
}
.admin-input-wrap input {
    width: 100%;
    padding: 15px 50px 15px 46px;
    border: 2px solid var(--border);
    border-radius: 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: var(--dark);
    outline: none;
    transition: var(--transition);
    background: var(--white);
}
.admin-input-wrap input::placeholder { color: #AAA; }
.admin-input-wrap input:focus {
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
    color: var(--danger);
    font-size: 12px;
    margin-top: 6px;
    font-weight: 500;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 10px;
}
.form-check-custom {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--dark);
    cursor: pointer;
    font-weight: 500;
}
.form-check-custom input { display: none; }
.form-check-custom .check-box {
    width: 18px; height: 18px;
    border: 2px solid var(--border);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}
.form-check-custom input:checked + .check-box {
    background: var(--primary);
    border-color: var(--primary);
}
.form-check-custom input:checked + .check-box::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: var(--white);
    font-size: 10px;
}
.forgot-link {
    font-size: 12px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}
.forgot-link:hover { color: var(--primary-dark); text-decoration: underline; }

.btn-admin-login {
    width: 100%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--white);
    border: none;
    padding: 16px;
    border-radius: 50px;
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 8px 24px rgba(46,125,50,0.3);
}
.btn-admin-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 32px rgba(46,125,50,0.4);
}
.btn-admin-login:active { transform: translateY(0) scale(0.98); }

.back-to-store {
    text-align: center;
    margin-top: 28px;
    font-size: 13px;
    color: var(--gray);
}
.back-to-store a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}
.back-to-store a:hover { color: var(--primary-dark); text-decoration: underline; }
.back-to-store a i { margin-right: 5px; }

/* ================= RESPONSIVE ================= */
@media (max-width: 900px) {
    .admin-login-wrapper {
        grid-template-columns: 1fr;
        max-width: 480px;
    }
    .admin-login-visual {
        padding: 40px 35px;
        min-height: auto;
    }
    .visual-brand { margin-bottom: 25px; }
    .visual-title { font-size: 26px; }
    .visual-subtitle { margin-bottom: 20px; }
    .visual-features { display: none; }
    .admin-login-form-wrap { padding: 40px 35px; }
}
@media (max-width: 500px) {
    body { padding: 15px; }
    .admin-login-visual { padding: 30px 25px; }
    .visual-brand-icon { width: 45px; height: 45px; font-size: 20px; }
    .visual-brand-name { font-size: 22px; }
    .visual-title { font-size: 22px; }
    .admin-login-form-wrap { padding: 30px 25px; }
    .form-header h1 { font-size: 22px; }
}
</style>
</head>
<body>

<div class="admin-login-wrapper">
    
    <!-- LEFT: Visual -->
    <div class="admin-login-visual">
        <div class="visual-content">
            
            <div class="visual-brand">
                <div class="visual-brand-icon"><i class="fas fa-leaf"></i></div>
                <div class="visual-brand-text">
                    <span class="visual-brand-name">Fresh<span>Mart</span></span>
                    <span class="visual-brand-tag">Admin Panel</span>
                </div>
            </div>
            
            <h2 class="visual-title">
                Welcome to Your <br>
                <span>Admin Dashboard</span>
            </h2>
            
            <p class="visual-subtitle">
                Manage products, orders, customers, and track your business performance — all in one place.
            </p>
            
            <div class="visual-features">
                <div class="visual-feature">
                    <div class="visual-feature-icon"><i class="fas fa-box"></i></div>
                    <span>Manage products & inventory</span>
                </div>
                <div class="visual-feature">
                    <div class="visual-feature-icon"><i class="fas fa-shopping-bag"></i></div>
                    <span>Track orders & deliveries</span>
                </div>
                <div class="visual-feature">
                    <div class="visual-feature-icon"><i class="fas fa-users"></i></div>
                    <span>View customer insights</span>
                </div>
                <div class="visual-feature">
                    <div class="visual-feature-icon"><i class="fas fa-chart-line"></i></div>
                    <span>Monitor sales analytics</span>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- RIGHT: Login Form -->
    <div class="admin-login-form-wrap">
        
        <div class="form-header">
            <h1>Sign In</h1>
            <p>Enter your credentials to access the admin panel</p>
        </div>
        
        <?php if (isset($errors['general'])): ?>
        <div class="admin-alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
        <?php endif; ?>
        
        
        <form method="POST" action="login.php">
            
            <div class="admin-form-group">
                <label>Email Address <span class="req">*</span></label>
                <div class="admin-input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="admin@freshmart.com" value="<?php echo htmlspecialchars($oldEmail); ?>" required autofocus>
                </div>
                <?php if (isset($errors['email'])): ?>
                <span class="form-error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="admin-form-group">
                <label>Password <span class="req">*</span></label>
                <div class="admin-input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your password" id="passwordInput" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="far fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                <span class="form-error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-options">
                <label class="form-check-custom">
                    <input type="checkbox" name="remember">
                    <span class="check-box"></span>
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn-admin-login">
                <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
            </button>
            
        </form>
        
        <div class="back-to-store">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i> Back to Store
            </a>
        </div>
        
    </div>
    
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('far', 'fa-eye');
        icon.classList.add('fas', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fas', 'fa-eye-slash');
        icon.classList.add('far', 'fa-eye');
    }
}
</script>

</body>
</html>