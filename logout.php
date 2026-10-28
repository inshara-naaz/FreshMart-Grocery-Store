<?php
// ============================================
// logout.php - User Logout
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User session data clear karo (cart aur orders rakho)
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_phone']);
unset($_SESSION['user_role']);

// Cookie clear karo
if (isset($_COOKIE['freshmart_email'])) {
    setcookie('freshmart_email', '', time() - 3600, '/');
}

header('Location: index.php?logout=1');
exit;
