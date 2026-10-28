<?php
// ============================================
// ajax/add-to-cart.php (Database Version)
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/db.php';
include '../includes/cart-functions.php';

header('Content-Type: application/json');

// ============ GET DATA ============
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? max(1, min(20, (int)$_POST['qty'])) : 1;
$unitLabel = isset($_POST['unit_label']) ? $_POST['unit_label'] : '1 kg';
$unitPrice = isset($_POST['unit_price']) ? (int)$_POST['unit_price'] : 0;

// ============ VALIDATION ============
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Product DB se lo
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Agar unit_price 0 hai to product ki default price
if ($unitPrice <= 0) {
    $unitPrice = $product['price'];
    $unitLabel = '1 ' . $product['unit'];
}

// ============ ADD TO CART ============
$result = addToCart($pdo, $productId, $qty, $unitLabel, $unitPrice);

$message = $result === 'updated' 
    ? 'Cart updated — ' . $product['name']
    : $product['name'] . ' added to cart!';

// ============ CART COUNT ============
$cartCount = getCartCount($pdo);

echo json_encode([
    'success' => true,
    'message' => $message,
    'cart_count' => $cartCount,
    'product' => $product['name'],
]);
?>