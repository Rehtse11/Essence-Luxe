<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if request is POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Invalid request method');
    redirect('shop.php');
}

// Verify CSRF token
if(!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
    setFlash('error', 'Invalid security token');
    redirect('shop.php');
}

// Get product ID and quantity
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate inputs
if($productId <= 0) {
    setFlash('error', 'Invalid product');
    redirect('shop.php');
}

if($quantity <= 0) {
    $quantity = 1;
}

// Get product details
$db = getDB();
$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if(!$product) {
    setFlash('error', 'Product not found');
    redirect('shop.php');
}

// Check stock availability
if($product['stock_quantity'] < $quantity) {
    setFlash('error', 'Insufficient stock. Only ' . $product['stock_quantity'] . ' items available.');
    redirect('product.php?slug=' . $product['slug']);
}

// Add to cart
try {
    addToCart($productId, $quantity);
    setFlash('success', $product['name'] . ' added to cart!');
    
    // Redirect based on action
    if(isset($_POST['buy_now'])) {
        redirect('cart.php');
    } else {
        // Redirect back to previous page or shop
        $referer = $_SERVER['HTTP_REFERER'] ?? 'shop.php';
        header('Location: ' . $referer);
        exit();
    }
} catch(Exception $e) {
    setFlash('error', 'Failed to add item to cart');
    redirect('shop.php');
}
?>