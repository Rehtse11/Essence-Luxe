<?php
/**
 * Test Cart Functionality
 * Access this page to verify cart is working: test-cart.php
 * DELETE THIS FILE IN PRODUCTION
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Cart Test & Debug</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";

// Test 1: Check Session
echo "<h2>1. Session Status</h2>";
if(session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>✓ Session is active</p>";
    echo "<p class='info'>Session ID: " . session_id() . "</p>";
} else {
    echo "<p class='error'>✗ Session is NOT active</p>";
}

// Test 2: Check Database Connection
echo "<h2>2. Database Connection</h2>";
try {
    $db = getDB();
    echo "<p class='success'>✓ Database connected successfully</p>";
} catch(Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 3: Check Products Table
echo "<h2>3. Products in Database</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "<p class='success'>✓ Found " . $result['count'] . " active products</p>";
    
    // Show first 3 products
    $stmt = $db->query("SELECT id, name, price, stock_quantity FROM products WHERE is_active = 1 LIMIT 3");
    $products = $stmt->fetchAll();
    echo "<pre>";
    print_r($products);
    echo "</pre>";
} catch(Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 4: Current Cart Contents
echo "<h2>4. Current Cart Contents</h2>";
if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "<p class='success'>✓ Cart has items</p>";
    echo "<pre>";
    print_r($_SESSION['cart']);
    echo "</pre>";
    
    // Show cart details
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $stmt = $db->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $cartProducts = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr>";
    foreach($cartProducts as $prod) {
        $qty = $_SESSION['cart'][$prod['id']];
        $subtotal = $prod['price'] * $qty;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($prod['name']) . "</td>";
        echo "<td>$" . number_format($prod['price'], 2) . "</td>";
        echo "<td>" . $qty . "</td>";
        echo "<td>$" . number_format($subtotal, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='info'>Cart is empty</p>";
}

// Test 5: Add Test Product to Cart
echo "<h2>5. Test Add to Cart</h2>";
if(isset($_GET['add_test'])) {
    try {
        // Get first product
        $stmt = $db->query("SELECT id, name FROM products WHERE is_active = 1 AND stock_quantity > 0 LIMIT 1");
        $testProduct = $stmt->fetch();
        
        if($testProduct) {
            addToCart($testProduct['id'], 1);
            echo "<p class='success'>✓ Successfully added '" . htmlspecialchars($testProduct['name']) . "' to cart!</p>";
            echo "<p><a href='test-cart.php'>Refresh to see updated cart</a></p>";
        } else {
            echo "<p class='error'>✗ No products available for testing</p>";
        }
    } catch(Exception $e) {
        echo "<p class='error'>✗ Error adding to cart: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p><a href='test-cart.php?add_test=1' style='background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;display:inline-block;'>Click to Add Test Product</a></p>";
}

// Test 6: Clear Cart
echo "<h2>6. Clear Cart</h2>";
if(isset($_GET['clear_cart'])) {
    clearCart();
    echo "<p class='success'>✓ Cart cleared!</p>";
    echo "<p><a href='test-cart.php'>Refresh page</a></p>";
} else {
    echo "<p><a href='test-cart.php?clear_cart=1' style='background:#f44336;color:white;padding:10px 20px;text-decoration:none;display:inline-block;'>Clear Cart</a></p>";
}

// Test 7: Check addToCart Function
echo "<h2>7. Function Test</h2>";
if(function_exists('addToCart')) {
    echo "<p class='success'>✓ addToCart() function exists</p>";
} else {
    echo "<p class='error'>✗ addToCart() function NOT found</p>";
}

if(function_exists('getCartCount')) {
    echo "<p class='success'>✓ getCartCount() function exists</p>";
    echo "<p class='info'>Cart Count: " . getCartCount() . "</p>";
} else {
    echo "<p class='error'>✗ getCartCount() function NOT found</p>";
}

// Links
echo "<hr>";
echo "<h2>Quick Links</h2>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
echo "<p><a href='shop.php'>Go to Shop</a></p>";
echo "<p><a href='cart.php'>View Cart</a></p>";
echo "<p style='color:red;'><strong>⚠️ DELETE THIS FILE (test-cart.php) IN PRODUCTION!</strong></p>";
?>