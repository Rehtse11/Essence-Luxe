<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Shopping Cart';
$pageDescription = 'Review your cart items';

// Handle cart updates
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request');
    } else {
        // Update quantity
        if(isset($_POST['update_quantity'])) {
            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            updateCartQuantity($productId, $quantity);
            setFlash('success', 'Cart updated');
        }
        
        // Remove item
        if(isset($_POST['remove_item'])) {
            $productId = intval($_POST['product_id']);
            removeFromCart($productId);
            setFlash('success', 'Item removed from cart');
        }
        
        // Clear cart
        if(isset($_POST['clear_cart'])) {
            clearCart();
            setFlash('success', 'Cart cleared');
        }
    }
    redirect('cart.php');
}

// Get cart items with product details
$cartItems = [];
$subtotal = 0;

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $db = getDB();
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND is_active = 1");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();
    
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
}

// Calculate totals
$shipping = $subtotal >= 100 ? 0 : 10;
$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $shipping + $tax;
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>Shopping Cart</h1>
        <p><?php echo count($cartItems); ?> item<?php echo count($cartItems) !== 1 ? 's' : ''; ?> in your cart</p>
    </div>
</div>

<section class="cart-section">
    <div class="container">
        <?php if(empty($cartItems)): ?>
            <div class="empty-cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <h2>Your Cart is Empty</h2>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <a href="product.php?slug=<?php echo $item['product']['slug']; ?>">
                                    <img src="assets/images/products/<?php echo $item['product']['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'130\'%3E%3Crect fill=\'%23f5f1eb\' width=\'100\' height=\'130\'/%3E%3C/svg%3E'">
                                </a>
                            </div>
                            <div class="item-details">
                                <h3>
                                    <a href="product.php?slug=<?php echo $item['product']['slug']; ?>">
                                        <?php echo htmlspecialchars($item['product']['name']); ?>
                                    </a>
                                </h3>
                                <p class="item-category"><?php echo htmlspecialchars($item['product']['category_name']); ?></p>
                                <div class="item-price"><?php echo formatPrice($item['product']['price']); ?></div>
                            </div>
                            <div class="item-quantity">
                                <form method="POST" class="quantity-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <div class="quantity-controls">
                                        <button type="submit" name="update_quantity" value="<?php echo $item['quantity'] - 1; ?>" class="qty-btn">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['product']['stock_quantity']; ?>" class="qty-input" readonly>
                                        <button type="submit" name="update_quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn">+</button>
                                    </div>
                                </form>
                            </div>
                            <div class="item-total">
                                <?php echo formatPrice($item['item_total']); ?>
                            </div>
                            <div class="item-remove">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn" title="Remove item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-actions">
                        <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                            <button type="submit" name="clear_cart" class="btn btn-outline" onclick="return confirm('Are you sure you want to clear your cart?')">Clear Cart</button>
                        </form>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?></span>
                    </div>
                    <?php if($shipping > 0): ?>
                        <div class="shipping-note">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            Add <?php echo formatPrice(100 - $subtotal); ?> more for free shipping
                        </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Tax (8%)</span>
                        <span><?php echo formatPrice($tax); ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-block btn-lg">Proceed to Checkout</a>
                    
                    <div class="payment-badges">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='25' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23666' font-size='8'%3EVISA%3C/text%3E%3C/svg%3E" alt="Visa">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='25' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ccircle cx='15' cy='12.5' r='6' fill='%23eb001b'/%3E%3Ccircle cx='25' cy='12.5' r='6' fill='%23f79e1b'/%3E%3C/svg%3E" alt="Mastercard">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='25' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%230079c1' font-size='6'%3EPayPal%3C/text%3E%3C/svg%3E" alt="PayPal">
                    </div>
                    
                    <div class="security-badges">
                        <div class="badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span>Secure Checkout</span>
                        </div>
                        <div class="badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Money Back Guarantee</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.page-header {
    background: var(--gradient-hero);
    padding: 3rem 0;
    text-align: center;
}

.page-header h1 {
    font-size: clamp(2rem, 5vw, 3rem);
    margin-bottom: 0.5rem;
}

.cart-section {
    padding: 3rem 0;
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    max-width: 500px;
    margin: 0 auto;
}

.empty-cart svg {
    width: 5rem;
    height: 5rem;
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

.empty-cart h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.empty-cart p {
    color: var(--text-light);
    margin-bottom: 2rem;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
}

.cart-items {
    background: white;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto auto;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 1px solid var(--gray);
    border-radius: var(--border-radius-lg);
    margin-bottom: 1rem;
    align-items: center;
}

.item-image {
    width: 100px;
    height: 130px;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details h3 {
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
}

.item-details h3 a {
    color: var(--text-dark);
    text-decoration: none;
    transition: var(--transition);
}

.item-details h3 a:hover {
    color: var(--primary);
}

.item-category {
    color: var(--text-light);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.item-price {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--primary);
}

.quantity-form {
    display: inline-block;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    width: 2rem;
    height: 2rem;
    border: 2px solid var(--gray);
    background: white;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 1.125rem;
    font-weight: 600;
    transition: var(--transition);
}

.qty-btn:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.qty-input {
    width: 3rem;
    height: 2rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    text-align: center;
    font-weight: 600;
}

.item-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-dark);
    min-width: 100px;
    text-align: right;
}

.remove-btn {
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: var(--transition);
}

.remove-btn:hover {
    background: #fee2e2;
    color: #ef4444;
}

.remove-btn svg {
    width: 1.25rem;
    height: 1.25rem;
}

.cart-actions {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid var(--gray);
    margin-top: 1rem;
}

.cart-summary {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    border: 1px solid var(--gray);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.cart-summary h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    color: var(--text-dark);
}

.shipping-note {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #dbeafe;
    border-radius: var(--border-radius);
    color: #1e40af;
    font-size: 0.875rem;
    margin: 0.5rem 0;
}

.shipping-note svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding: 1.5rem 0;
    margin-top: 1rem;
    border-top: 2px solid var(--gray);
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
}

.payment-badges {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 1.5rem 0;
}

.payment-badges img {
    height: 25px;
    border-radius: 4px;
}

.security-badges {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.security-badges .badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    font-size: 0.875rem;
}

.security-badges .badge svg {
    width: 1.125rem;
    height: 1.125rem;
    color: #10b981;
}

@media (max-width: 1024px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 1rem;
    }
    
    .item-image {
        width: 80px;
        height: 100px;
    }
    
    .item-quantity,
    .item-total {
        grid-column: 2;
    }
    
    .item-remove {
        grid-column: 2;
        justify-self: end;
    }
    
    .cart-actions {
        flex-direction: column;
    }
}
</style>

<?php include 'includes/footer.php'; ?>