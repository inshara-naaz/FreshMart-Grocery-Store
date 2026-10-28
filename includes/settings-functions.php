<?php
// ============================================
// includes/settings-functions.php
// Store Settings Helper Functions
// ============================================

/**
 * Get all settings as key-value array
 */
function getSettings($pdo) {
    static $cache = null;
    if ($cache !== null) return $cache;
    
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $cache = [];
    while ($row = $stmt->fetch()) {
        $cache[$row['setting_key']] = $row['setting_value'];
    }
    return $cache;
}

/**
 * Get single setting value
 */
function getSetting($pdo, $key, $default = '') {
    $settings = getSettings($pdo);
    return $settings[$key] ?? $default;
}

/**
 * Update setting value
 */
function updateSetting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}
?>