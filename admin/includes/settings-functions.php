<?php
// ============================================
// admin/includes/settings-functions.php
// Store Settings Fetch & Save Functions
// ============================================

// Check if function already exists to prevent duplicate errors
if (!function_exists('getSettings')) {
    function getSettings() {
        global $pdo; // Database connection use karne ke liye
        
        $settings = [];
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            // Agar table na mile to empty array return karega
            return [];
        }
        return $settings;
    }
}

if (!function_exists('updateSetting')) {
    function updateSetting($key, $value) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            return $stmt->execute([$key, $value, $value]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

if (!function_exists('getSetting')) {
    function getSetting($key, $default = '') {
        $settings = getSettings();
        return $settings[$key] ?? $default;
    }
}
?>