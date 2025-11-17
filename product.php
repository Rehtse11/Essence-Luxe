<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get product slug
$slug = $_GET['slug'] ?? '';

if(!$slug) {
    redirect('shop.php');
}

// Get product details
$product = getProductBySlug($slug);

if(!$product) {
    setFlash('error', 'Product not found');
    redirect('shop.php');
}

// Increment views
incrementProductViews($product['id']);

// Handle add to cart
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if(!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request');
    } else {
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        
        if($product['stock_quantity'] < $quantity) {
            setFlash('error', 'Insufficient stock available');
        } else {
            addToCart($product['id'], $quantity);
            setFlash('success', $product['name'] . ' added to cart!');
            
            // Check if it's "Buy Now" or "Add to Cart"
            if(isset($_POST['buy_now'])) {
                redirect('cart.php');
            } else {
                redirect('product.php?slug=' . $slug);
            }
        }
    }
}

// Get related products
$relatedProducts = getProducts([
    'category' => $product['category_id'],
    'limit' => 4
]);

// Remove current product from related
$relatedProducts = array_filter($relatedProducts, function($p) use ($product) {
    return $p['id'] !== $product['id'];
});
$relatedProducts = array_slice($relatedProducts, 0, 3);

// Parse notes
$notes = $product['notes'] ? explode(',', $product['notes']) : [];
$sizes = $product['sizes'] ? explode(',', $product['sizes']) : [];

$pageTitle = $product['name'];
$pageDescription = truncate($product['description'], 160);
?>
<?php include 'includes/header.php'; ?>

<section class="product-detail">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="shop.php">Shop</a>
            <span>/</span>
            <a href="shop.php?category=<?php echo $product['category_slug']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
        
        <div class="product-layout">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="assets/images/products/<?php echo $product['image']; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         id="mainProductImage"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'600\' height=\'800\'%3E%3Crect fill=\'%23f5f1eb\' width=\'600\' height=\'800\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' fill=\'%23d4af37\' font-size=\'24\' font-family=\'Arial\'%3E<?php echo htmlspecialchars($product['name']); ?>%3C/text%3E%3C/svg%3E'">
                    <?php if($product['badge']): ?>
                        <div class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-info">
                <div class="product-header">
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-meta">
                        <span class="product-views">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <?php echo number_format($product['views']); ?> views
                        </span>
                    </div>
                </div>
                
                <div class="product-price-section">
                    <div class="price-wrapper">
                        <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php if($product['original_price']): ?>
                            <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                            <span class="discount-badge">
                                Save <?php echo getDiscountPercentage($product['original_price'], $product['price']); ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <?php if(!empty($notes)): ?>
                    <div class="product-notes">
                        <h3>Notes</h3>
                        <div class="notes-list">
                            <?php foreach($notes as $note): ?>
                                <span class="note-tag"><?php echo trim(htmlspecialchars($note)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($sizes)): ?>
                    <div class="product-sizes">
                        <h3>Available Sizes</h3>
                        <div class="sizes-list">
                            <?php foreach($sizes as $size): ?>
                                <span class="size-option"><?php echo trim(htmlspecialchars($size)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="product-stock">
                    <?php if($product['stock_quantity'] > 0): ?>
                        <span class="in-stock">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            In Stock (<?php echo $product['stock_quantity']; ?> available)
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if($product['stock_quantity'] > 0): ?>
                    <form method="POST" class="add-to-cart-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn qty-decrease">-</button>
                                <input type="number" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $product['stock_quantity']; ?>"
                                       class="qty-input">
                                <button type="button" class="qty-btn qty-increase">+</button>
                            </div>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            Add to Cart
                        </button>
                        <button type="submit" name="buy_now" class="btn btn-outline btn-lg btn-block" style="margin-top: 0.75rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Buy Now
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="product-features">
                    <div class="feature">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>100% Authentic</span>
                    </div>
                    <div class="feature">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span>Free Shipping $100+</span>
                    </div>
                    <div class="feature">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>30-Day Returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if(!empty($relatedProducts)): ?>
<section class="related-products">
    <div class="container">
        <h2>You May Also Like</h2>
        <div class="products-grid">
            <?php foreach($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <div class="product-image">
                        <a href="product.php?slug=<?php echo $relatedProduct['slug']; ?>">
                            <img src="assets/images/products/<?php echo $relatedProduct['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>"
                                 loading="lazy"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'400\'%3E%3Crect fill=\'%23f5f1eb\' width=\'300\' height=\'400\'/%3E%3C/svg%3E'">
                        </a>
                        <?php if($relatedProduct['badge']): ?>
                            <div class="product-badge"><?php echo htmlspecialchars($relatedProduct['badge']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-name">
                            <a href="product.php?slug=<?php echo $relatedProduct['slug']; ?>">
                                <?php echo htmlspecialchars($relatedProduct['name']); ?>
                            </a>
                        </h3>
                        <div class="product-price"><?php echo formatPrice($relatedProduct['price']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.product-detail {
    padding: 3rem 0;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.breadcrumb a {
    color: var(--text-light);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb a:hover {
    color: var(--primary);
}

.breadcrumb span:not(.breadcrumb a span) {
    color: var(--text-light);
}

.product-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
}

.product-gallery {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.main-image {
    position: relative;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.main-image img {
    width: 100%;
    height: auto;
    display: block;
}

.product-header {
    margin-bottom: 2rem;
}

.product-category {
    display: inline-block;
    color: var(--text-light);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}

.product-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
}

.product-meta {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    color: var(--text-light);
    font-size: 0.925rem;
}

.product-views {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.product-views svg {
    width: 1.125rem;
    height: 1.125rem;
}

.product-price-section {
    padding: 1.5rem 0;
    border-top: 1px solid var(--gray);
    border-bottom: 1px solid var(--gray);
    margin-bottom: 2rem;
}

.price-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.current-price {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary);
}

.original-price {
    font-size: 1.5rem;
    color: var(--text-light);
    text-decoration: line-through;
}

.discount-badge {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
}

.product-description {
    margin-bottom: 2rem;
}

.product-description p {
    font-size: 1.125rem;
    line-height: 1.7;
    color: var(--text-dark);
}

.product-notes,
.product-sizes {
    margin-bottom: 2rem;
}

.product-notes h3,
.product-sizes h3 {
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.notes-list,
.sizes-list {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.note-tag,
.size-option {
    padding: 0.5rem 1rem;
    background: var(--light-gray);
    border-radius: 50px;
    font-size: 0.925rem;
    color: var(--text-dark);
}

.product-stock {
    margin-bottom: 2rem;
}

.in-stock,
.out-of-stock {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 500;
}

.in-stock {
    background: #d1fae5;
    color: #065f46;
}

.out-of-stock {
    background: #fee2e2;
    color: #991b1b;
}

.in-stock svg,
.out-of-stock svg {
    width: 1.25rem;
    height: 1.25rem;
}

.add-to-cart-form {
    margin-bottom: 2rem;
}

.quantity-selector {
    margin-bottom: 1.5rem;
}

.quantity-selector label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 200px;
}

.qty-btn {
    width: 2.5rem;
    height: 2.5rem;
    border: 2px solid var(--gray);
    background: white;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 1.25rem;
    font-weight: 600;
    transition: var(--transition);
}

.qty-btn:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.qty-input {
    width: 4rem;
    height: 2.5rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    text-align: center;
    font-size: 1rem;
    font-weight: 600;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}

.product-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    padding: 2rem 0;
    border-top: 1px solid var(--gray);
}

.feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.925rem;
    color: var(--text-dark);
}

.feature svg {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--primary);
    flex-shrink: 0;
}

.related-products {
    padding: 3rem 0;
    background: var(--light-gray);
}

.related-products h2 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 3rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

@media (max-width: 968px) {
    .product-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .product-gallery {
        position: static;
    }
    
    .product-header h1 {
        font-size: 2rem;
    }
    
    .current-price {
        font-size: 2rem;
    }
    
    .product-features {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Quantity controls
const qtyInput = document.getElementById('quantity');
const qtyDecrease = document.querySelector('.qty-decrease');
const qtyIncrease = document.querySelector('.qty-increase');

if(qtyDecrease) {
    qtyDecrease.addEventListener('click', function() {
        const current = parseInt(qtyInput.value);
        if(current > 1) {
            qtyInput.value = current - 1;
        }
    });
}

if(qtyIncrease) {
    qtyIncrease.addEventListener('click', function() {
        const current = parseInt(qtyInput.value);
        const max = parseInt(qtyInput.getAttribute('max'));
        if(current < max) {
            qtyInput.value = current + 1;
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>