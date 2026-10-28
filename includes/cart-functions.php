<?php
// ============================================
// includes/cart-functions.php
// Cart ke saare DB operations
// ============================================

/**
 * Get current cart identifier (user_id ya session_id)
 */
function getCartIdentifier($pdo) {
    if (isset($_SESSION['user_id'])) {
        return ['type' => 'user', 'value' => $_SESSION['user_id']];
    } else {
        // Guest user — session_id use karo
        if (!isset($_SESSION['guest_session_id'])) {
            $_SESSION['guest_session_id'] = session_id();
        }
        return ['type' => 'guest', 'value' => $_SESSION['guest_session_id']];
    }
}

/**
 * Get cart items from database
 */
function getCartItems($pdo) {
    $identifier = getCartIdentifier($pdo);
    
    if ($identifier['type'] === 'user') {
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.image, p.unit AS product_unit
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$identifier['value']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.image, p.unit AS product_unit
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$identifier['value']]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Get cart count
 */
function getCartCount($pdo) {
    $identifier = getCartIdentifier($pdo);
    
    if ($identifier['type'] === 'user') {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS cnt FROM cart WHERE user_id = ?");
        $stmt->execute([$identifier['value']]);
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS cnt FROM cart WHERE session_id = ?");
        $stmt->execute([$identifier['value']]);
    }
    
    return (int)$stmt->fetch()['cnt'];
}

/**
 * Add item to cart
 */
function addToCart($pdo, $productId, $qty, $unitLabel, $unitPrice) {
    $identifier = getCartIdentifier($pdo);
    
    // Pehle check karo — product already cart mein hai?
    if ($identifier['type'] === 'user') {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND unit_label = ?");
        $stmt->execute([$identifier['value'], $productId, $unitLabel]);
    } else {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND unit_label = ?");
        $stmt->execute([$identifier['value'], $productId, $unitLabel]);
    }
    
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update quantity
        $newQty = min(20, $existing['quantity'] + $qty);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
        return 'updated';
    } else {
        // Insert new
        if ($identifier['type'] === 'user') {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, unit_label, unit_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$identifier['value'], $productId, $qty, $unitLabel, $unitPrice]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity, unit_label, unit_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$identifier['value'], $productId, $qty, $unitLabel, $unitPrice]);
        }
        return 'added';
    }
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($pdo, $cartId, $qty) {
    $qty = max(1, min(20, (int)$qty));
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$qty, $cartId]);
}

/**
 * Remove item from cart
 */
function removeFromCart($pdo, $cartId) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->execute([$cartId]);
}

/**
 * Clear cart
 */
function clearCart($pdo) {
    $identifier = getCartIdentifier($pdo);
    
    if ($identifier['type'] === 'user') {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$identifier['value']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->execute([$identifier['value']]);
    }
}

/**
 * Merge guest cart to user cart (login ke baad)
 */
function mergeGuestCart($pdo, $userId, $guestSessionId) {
    // Guest cart items lo
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE session_id = ?");
    $stmt->execute([$guestSessionId]);
    $guestItems = $stmt->fetchAll();
    
    foreach ($guestItems as $item) {
        // Check karo user ke cart mein already hai?
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND unit_label = ?");
        $stmt->execute([$userId, $item['product_id'], $item['unit_label']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Quantity merge karo
            $newQty = min(20, $existing['quantity'] + $item['quantity']);
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            // Naya item user ke cart mein add karo
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, unit_label, unit_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $item['product_id'], $item['quantity'], $item['unit_label'], $item['unit_price']]);
        }
    }
    
    // Guest cart clear karo
    $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
    $stmt->execute([$guestSessionId]);
}
?>