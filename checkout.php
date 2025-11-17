<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin(); // Must be logged in to checkout

$pageTitle = 'Checkout';
$pageDescription = 'Complete your order';

// Check if cart is empty
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlash('error', 'Your cart is empty');
    redirect('cart.php');
}

// Get cart items
$db = getDB();
$productIds = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($productIds) - 1) . '?';
$stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND is_active = 1");
$stmt->execute($productIds);
$products = $stmt->fetchAll();

$cartItems = [];
$subtotal = 0;

foreach($products as $product) {
    $quantity = $_SESSION['cart'][$product['id']];
    $itemTotal = $product['price'] * $quantity;
    $subtotal += $itemTotal;
    $cartItems[] = [
        'product' => $product,
        'quantity' => $quantity,
        'item_total' => $itemTotal
    ];
}

// Get user info
$currentUser = getCurrentUser();

// Calculate totals
$shipping = $subtotal >= 100 ? 0 : 10;
$tax = $subtotal * 0.08;
$total = $subtotal + $shipping + $tax;

$errors = [];

// Process checkout
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if(!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    }
    
    // Validate inputs
    $shippingAddress = clean($_POST['shipping_address'] ?? '');
    $shippingCity = clean($_POST['shipping_city'] ?? '');
    $shippingState = clean($_POST['shipping_state'] ?? '');
    $shippingZip = clean($_POST['shipping_zip'] ?? '');
    $shippingCountry = clean($_POST['shipping_country'] ?? '');
    $paymentMethod = clean($_POST['payment_method'] ?? '');
    $notes = clean($_POST['notes'] ?? '');
    
    if(empty($shippingAddress)) $errors[] = 'Shipping address is required';
    if(empty($shippingCity)) $errors[] = 'City is required';
    if(empty($shippingState)) $errors[] = 'State is required';
    if(empty($shippingZip)) $errors[] = 'ZIP code is required';
    if(empty($shippingCountry)) $errors[] = 'Country is required';
    if(empty($paymentMethod)) $errors[] = 'Payment method is required';
    
    if(empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Generate order number
            $orderNumber = generateOrderNumber();
            
            // Create order
            $stmt = $db->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, payment_method, 
                                   shipping_address, shipping_city, shipping_state, 
                                   shipping_zip, shipping_country, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $orderNumber,
                $total,
                $paymentMethod,
                $shippingAddress,
                $shippingCity,
                $shippingState,
                $shippingZip,
                $shippingCountry,
                $notes
            ]);
            
            $orderId = $db->lastInsertId();
            
            // Create order items
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach($cartItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price'],
                    $item['item_total']
                ]);
                
                // Update stock
                $updateStmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $updateStmt->execute([$item['quantity'], $item['product']['id']]);
            }
            
            $db->commit();
            
            // Clear cart
            clearCart();
            
            // Send confirmation email
            $subject = "Order Confirmation - " . $orderNumber;
            $message = "
                <h2>Thank you for your order!</h2>
                <p>Your order number is: <strong>{$orderNumber}</strong></p>
                <p>Total: " . formatPrice($total) . "</p>
                <p>We'll send you a shipping confirmation when your order ships.</p>
            ";
            sendEmail($currentUser['email'], $subject, $message);
            
            setFlash('success', 'Order placed successfully! Order number: ' . $orderNumber);
            redirect('account.php?tab=orders');
            
        } catch(Exception $e) {
            $db->rollBack();
            $errors[] = 'Failed to process order. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <div class="checkout-steps">
            <div class="step active">
                <span class="step-number">1</span>
                <span class="step-label">Shipping</span>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <span class="step-label">Payment</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span class="step-label">Confirmation</span>
            </div>
        </div>
    </div>
</div>

<section class="checkout-section">
    <div class="container">
        <?php if(!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="checkout-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
            
            <div class="checkout-layout">
                <div class="checkout-main">
                    <!-- Shipping Information -->
                    <div class="checkout-section-box">
                        <h2>Shipping Information</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Street Address *</label>
                            <input type="text" id="shipping_address" name="shipping_address" 
                                   value="<?php echo htmlspecialchars($currentUser['address'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">City *</label>
                                <input type="text" id="shipping_city" name="shipping_city" 
                                       value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_state">State/Province *</label>
                                <input type="text" id="shipping_state" name="shipping_state" 
                                       value="<?php echo htmlspecialchars($currentUser['state'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_zip">ZIP/Postal Code *</label>
                                <input type="text" id="shipping_zip" name="shipping_zip" 
                                       value="<?php echo htmlspecialchars($currentUser['zip_code'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_country">Country *</label>
                                <select id="shipping_country" name="shipping_country" required>
                                    <option value="USA" <?php echo ($currentUser['country'] ?? '') === 'USA' ? 'selected' : ''; ?>>United States</option>
                                    <option value="CAN" <?php echo ($currentUser['country'] ?? '') === 'CAN' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="UK" <?php echo ($currentUser['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-section-box">
                        <h2>Payment Method</h2>
                        
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="credit_card" checked>
                                <div class="payment-content">
                                    <span class="payment-label">Credit Card</span>
                                    <div class="payment-icons">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='35' height='22' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23666' font-size='8'%3EVISA%3C/text%3E%3C/svg%3E" alt="Visa">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='35' height='22' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ccircle cx='15' cy='12.5' r='6' fill='%23eb001b'/%3E%3Ccircle cx='25' cy='12.5' r='6' fill='%23f79e1b'/%3E%3C/svg%3E" alt="Mastercard">
                                    </div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="paypal">
                                <div class="payment-content">
                                    <span class="payment-label">PayPal</span>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='22' viewBox='0 0 60 25'%3E%3Crect fill='%23fff' width='60' height='25' rx='3'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%230079c1' font-size='10'%3EPayPal%3C/text%3E%3C/svg%3E" alt="PayPal">
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod">
                                <div class="payment-content">
                                    <span class="payment-label">Cash on Delivery</span>
                                </div>
                            </label>
                        </div>
                        
                        <p class="payment-note">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            All transactions are secure and encrypted
                        </p>
                    </div>
                    
                    <!-- Order Notes -->
                    <div class="checkout-section-box">
                        <h2>Order Notes (Optional)</h2>
                        <div class="form-group">
                            <textarea name="notes" rows="4" placeholder="Special delivery instructions, gift message, etc."></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="checkout-sidebar">
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="summary-items">
                            <?php foreach($cartItems as $item): ?>
                                <div class="summary-item">
                                    <div class="item-info">
                                        <img src="assets/images/products/<?php echo $item['product']['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'50\' height=\'60\'%3E%3Crect fill=\'%23f5f1eb\' width=\'50\' height=\'60\'/%3E%3C/svg%3E'">
                                        <div class="item-details">
                                            <h4><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                                            <span class="item-qty">Qty: <?php echo $item['quantity']; ?></span>
                                        </div>
                                    </div>
                                    <div class="item-price">
                                        <?php echo formatPrice($item['item_total']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span><?php echo formatPrice($subtotal); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span><?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax</span>
                                <span><?php echo formatPrice($tax); ?></span>
                            </div>
                            <div class="summary-total">
                                <span>Total</span>
                                <span><?php echo formatPrice($total); ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary btn-block btn-lg">
                            Place Order
                        </button>
                        
                        <a href="cart.php" class="back-to-cart">← Back to Cart</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.checkout-steps {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 2rem;
}

.step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
}

.step.active {
    color: var(--primary);
}

.step-number {
    width: 2rem;
    height: 2rem;
    background: var(--gray);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.step.active .step-number {
    background: var(--primary);
    color: white;
}

.checkout-section {
    padding: 3rem 0;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
}

.checkout-section-box {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    border: 1px solid var(--gray);
    margin-bottom: 2rem;
}

.checkout-section-box h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-dark);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    font-family: var(--font-secondary);
    font-size: 1rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.form-group input:disabled {
    background: var(--light-gray);
    cursor: not-allowed;
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-option {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.payment-option:has(input:checked) {
    border-color: var(--primary);
    background: rgba(212, 175, 55, 0.05);
}

.payment-option input[type="radio"] {
    margin-right: 1rem;
}

.payment-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-label {
    font-weight: 500;
}

.payment-icons {
    display: flex;
    gap: 0.5rem;
}

.payment-icons img {
    height: 22px;
    border-radius: 4px;
}

.payment-note {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #d1fae5;
    border-radius: var(--border-radius);
    color: #065f46;
    font-size: 0.875rem;
}

.payment-note svg {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.order-summary {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    border: 1px solid var(--gray);
    position: sticky;
    top: 100px;
}

.order-summary h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray);
}

.summary-items {
    margin-bottom: 1.5rem;
    max-height: 300px;
    overflow-y: auto;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: start;
    padding: 1rem 0;
    border-bottom: 1px solid var(--light-gray);
}

.summary-item:last-child {
    border-bottom: none;
}

.item-info {
    display: flex;
    gap: 1rem;
}

.item-info img {
    width: 50px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.item-details h4 {
    font-size: 0.925rem;
    margin-bottom: 0.25rem;
}

.item-qty {
    font-size: 0.825rem;
    color: var(--text-light);
}

.item-price {
    font-weight: 600;
    color: var(--primary);
}

.summary-totals {
    padding: 1.5rem 0;
    border-top: 1px solid var(--gray);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    margin-top: 1rem;
    border-top: 2px solid var(--gray);
    font-size: 1.25rem;
    font-weight: 700;
}

.back-to-cart {
    display: block;
    text-align: center;
    margin-top: 1rem;
    color: var(--text-light);
    text-decoration: none;
    transition: var(--transition);
}

.back-to-cart:hover {
    color: var(--primary);
}

.alert {
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
}

.alert-error {
    background: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert ul {
    margin: 0;
    padding-left: 1.25rem;
}

@media (max-width: 1024px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .checkout-steps {
        gap: 1rem;
    }
    
    .step-label {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>