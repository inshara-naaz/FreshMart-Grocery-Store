<?php
// ============================================
// admin/logout.php - Admin Logout
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin session data clear karo
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

// Redirect to login
header('Location: login.php');
exit;
?>