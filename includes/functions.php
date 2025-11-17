<?php
/**
 * Global Helper Functions for Essence Luxe
 */

/**
 * Sanitize input data
 */
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirect to a page
 */
function redirect($page) {
    header("Location: " . SITE_URL . "/" . $page);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require login
 */
function requireLogin() {
    if(!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    requireLogin();
    if(!isAdmin()) {
        redirect('index.php');
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if(!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if(isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate CSRF token
 */
function generateCSRF() {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format price
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Calculate discount percentage
 */
function getDiscountPercentage($original, $current) {
    if($original <= $current) return 0;
    return round((($original - $current) / $original) * 100);
}

/**
 * Generate slug from string
 */
function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Get cart total
 */
function getCartTotal() {
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    $db = getDB();
    
    foreach($_SESSION['cart'] as $productId => $quantity) {
        $stmt = $db->prepare("SELECT price FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if($product) {
            $total += $product['price'] * $quantity;
        }
    }
    
    return $total;
}

/**
 * Get cart item count
 */
function getCartCount() {
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

/**
 * Add to cart
 */
function addToCart($productId, $quantity = 1) {
    // Initialize cart if not exists
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Validate product exists and is active
    $db = getDB();
    $stmt = $db->prepare("SELECT id, stock_quantity FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if(!$product) {
        throw new Exception('Product not found');
    }
    
    // Calculate new quantity
    $currentQty = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
    $newQty = $currentQty + $quantity;
    
    // Check stock
    if($newQty > $product['stock_quantity']) {
        throw new Exception('Insufficient stock');
    }
    
    // Update cart
    $_SESSION['cart'][$productId] = $newQty;
    
    // Sync with database if logged in
    if(isLoggedIn()) {
        try {
            $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $productId]);
            $cartItem = $stmt->fetch();
            
            if($cartItem) {
                // Update existing
                $stmt = $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$newQty, $_SESSION['user_id'], $productId]);
            } else {
                // Insert new
                $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $productId, $newQty]);
            }
        } catch(Exception $e) {
            // Database sync failed, but session cart still works
            error_log("Cart DB sync failed: " . $e->getMessage());
        }
    }
    
    return true;
}

/**
 * Remove from cart
 */
function removeFromCart($productId) {
    if(isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    
    // Remove from database if logged in
    if(isLoggedIn()) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
    }
}

/**
 * Update cart quantity
 */
function updateCartQuantity($productId, $quantity) {
    if($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
        
        // Update database if logged in
        if(isLoggedIn()) {
            $db = getDB();
            $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $_SESSION['user_id'], $productId]);
        }
    }
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
    
    // Clear database if logged in
    if(isLoggedIn()) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
}

/**
 * Load cart from database
 */
function loadCartFromDB($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    $_SESSION['cart'] = [];
    foreach($items as $item) {
        $_SESSION['cart'][$item['product_id']] = $item['quantity'];
    }
}

/**
 * Send email (simple wrapper - configure for production)
 */
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
    
    // In production, use a proper email library like PHPMailer
    return mail($to, $subject, $message, $headers);
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if(strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if($difference < 60) return 'Just now';
    if($difference < 3600) return floor($difference / 60) . ' minutes ago';
    if($difference < 86400) return floor($difference / 3600) . ' hours ago';
    if($difference < 604800) return floor($difference / 86400) . ' days ago';
    
    return date('M j, Y', $timestamp);
}

/**
 * Get products with filters
 */
function getProducts($filters = []) {
    $db = getDB();
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1";
    $params = [];
    
    if(isset($filters['category'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = $filters['category'];
    }
    
    if(isset($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.notes LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if(isset($filters['min_price'])) {
        $sql .= " AND p.price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if(isset($filters['max_price'])) {
        $sql .= " AND p.price <= ?";
        $params[] = $filters['max_price'];
    }
    
    if(isset($filters['featured']) && $filters['featured']) {
        $sql .= " AND p.is_featured = 1";
    }
    
    $orderBy = isset($filters['sort']) ? $filters['sort'] : 'p.created_at DESC';
    $sql .= " ORDER BY " . $orderBy;
    
    if(isset($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
        if(isset($filters['offset'])) {
            $sql .= " OFFSET " . (int)$filters['offset'];
        }
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get single product by slug
 */
function getProductBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.slug = ? AND p.is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Increment product views
 */
function incrementProductViews($productId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
    $stmt->execute([$productId]);
}
?>