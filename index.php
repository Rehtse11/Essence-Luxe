<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
$pageDescription = 'Discover our exclusive collection of luxury perfumes for men and women';

// Get featured products
$featuredProducts = getProducts(['featured' => true, 'limit' => 4]);

// Get categories
$db = getDB();
$stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">
                Discover Your
                <span class="accent">Signature Scent</span>
            </h1>
            <p class="hero-description">
                Explore our curated collection of luxury perfumes and find the perfect fragrance 
                that tells your unique story. Each bottle is crafted with the finest ingredients.
            </p>
            <div class="hero-buttons">
                <a href="shop.php" class="btn btn-primary">
                    Shop Collection
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="about.php" class="btn btn-outline">
                    Our Story
                </a>
            </div>
        </div>

        <!-- HERO IMAGE (perfume-themed) -->
        <div class="hero-image" aria-hidden="false">
            <div class="perfume-bottle">
               
                <div class="bottle-glow" aria-hidden="true"></div>
            </div>

           
        </div>
    </div>

    
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-subtitle">Find the perfect fragrance for every occasion</p>
        </div>
        
       <?php
// Static category data
$staticCategories = [
    [
        'name' => 'Men',
        'slug' => 'men',
        'description' => 'Bold, woody, and masculine fragrances',
        // Image: Men's perfume bottle (dark masculine aesthetic)
        'image' => './assets/images/categories/men.jpg' /*  */
    ],
    [
        'name' => 'Women',
        'slug' => 'women',
        'description' => 'Elegant, floral, and captivating scents',
        // Image: Feminine pink perfume bottle
        'image' => './assets/images/hero/gold1.jpg' /*  */
    ],
    [
        'name' => 'Unisex',
        'slug' => 'unisex',
        'description' => 'Modern fragrances for every identity',
        // Image: Neutral minimalist unisex perfume
        'image' => './assets/images/categories/unisex.jpg' /*  */
    ],
];
?>

<div class="categories-grid">
    <?php foreach($staticCategories as $cat): ?>
        <a href="shop.php?category=<?php echo $cat['slug']; ?>" 
           class="category-card fade-in" 
           aria-label="<?php echo htmlspecialchars($cat['name']); ?>">

            <div class="category-image">
                <img 
                    src="<?php echo $cat['image']; ?>" 
                    alt="<?php echo htmlspecialchars($cat['name']); ?> Perfume Category"
                    loading="lazy"
                    onerror="this.src='https://images.unsplash.com/photo-1523294587484-bae6cc870010?auto=format&fit=crop&w=900&q=80';"
                >

                <div class="category-overlay">
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <p><?php echo htmlspecialchars($cat['description']); ?></p>
                    <span class="category-link">Explore Collection →</span>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>


<!-- Featured Products Section -->
<section class="featured-products">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Fragrances</h2>
            <p class="section-subtitle">Discover our most popular scents</p>
        </div>
        
        <div class="products-grid">
            <?php foreach($featuredProducts as $product): ?>
                <div class="product-card fade-in">
                    <div class="product-image">
                        <a href="product.php?slug=<?php echo $product['slug']; ?>">
                            <img 
                                src="assets/images/products/<?php echo $product['image']; ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                loading="lazy"
                                onerror="this.src='./assets/images/products/rose.jpg';"
                            >
                        </a>

                        <?php if(!empty($product['badge'])): ?>
                            <div class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></div>
                        <?php endif; ?>

                        <?php if(!empty($product['original_price'])): 
                            $discount = getDiscountPercentage($product['original_price'], $product['price']);
                            if($discount > 0):
                        ?>
                            <div class="product-badge discount"><?php echo $discount; ?>% OFF</div>
                        <?php endif; endif; ?>
                    </div>

                    <div class="product-content">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>

                        <h3 class="product-name">
                            <a href="product.php?slug=<?php echo $product['slug']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>

                        <p class="product-description"><?php echo truncate($product['description'], 80); ?></p>

                        <div class="product-price">
                            <?php echo formatPrice($product['price']); ?>
                            <?php if(!empty($product['original_price'])): ?>
                                <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="product-actions">
                            <?php if($product['stock_quantity'] > 0): ?>
                                <form method="POST" action="add-to-cart.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-outline btn-sm" disabled>Out of Stock</button>
                            <?php endif; ?>

                            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="shop.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card fade-in">
                <div class="feature-icon"><!-- svg --></div>
                <h3>Premium Quality</h3>
                <p>Crafted with the finest ingredients from around the world</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon"><!-- svg --></div>
                <h3>Expert Guidance</h3>
                <p>Personalized recommendations from fragrance specialists</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon"><!-- svg --></div>
                <h3>Fast Delivery</h3>
                <p>Free shipping on orders over $100</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon"><!-- svg --></div>
                <h3>100% Authentic</h3>
                <p>Guaranteed genuine products with certificate of authenticity</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Join Our Fragrance Community</h2>
            <p>Get exclusive access to new releases, special offers, and expert fragrance tips</p>
            <?php if(!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-lg">Create Account</a>
            <?php else: ?>
                <a href="shop.php" class="btn btn-primary btn-lg">Continue Shopping</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
/* ---- Layout / vars (you already have similar in your main CSS - these reinforced here) ---- */
:root{
  --container-padding: 2rem;
  --section-padding: 4rem 0;
  --primary: #d4af37;
  --primary-light: rgba(212,175,55,0.12);
  --text-dark: #1b1b1b;
  --text-light: #6b6b6b;
  --white: #ffffff;
  --light-gray: #f7f5f3;
  --border-radius-lg: 12px;
  --shadow: 0 6px 20px rgba(0,0,0,0.08);
  --shadow-xl: 0 20px 50px rgba(0,0,0,0.12);
  --transition: 300ms cubic-bezier(.2,.9,.2,1);
  --gradient-hero: linear-gradient(135deg,#f5f1eb 0%, #efe6dc 100%);
}

/* Hero Section - tightened to avoid huge vertical layout */
.hero {
    min-height: 120vh;
    background: url('./assets/images/hero/hero.jpg') center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 3rem 0;
    margin-top:50px;
   
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 480px;
    gap: 2.5rem;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--container-padding);
}

.hero-text { max-width: 720px; }

.hero-title {
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--text-dark);
}

.accent { color: var(--primary); display:inline-block; }

.hero-description {
    font-size: 1.05rem;
    margin-bottom: 1.25rem;
    max-width: 540px;
    line-height: 1.6;
}

/* Buttons */
.hero-buttons { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
.btn { display:inline-flex; align-items:center; gap:.5rem; padding:.75rem 1.1rem; border-radius:8px; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; font-weight:600; }
.btn-outline { border:1px solid rgba(0,0,0,0.08); background:white; color:var(--text-dark); }

/* Hero image */
.hero-image { display:flex; align-items:center; justify-content:center; position:relative; }
.perfume-bottle { position:relative; z-index:2; width:100%; max-width:380px; }


.bottle-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
    width: 180%;
    height: 180%;
    background: radial-gradient(circle, var(--primary-light) 0%, transparent 70%);
    opacity: 0.3;
    pointer-events:none;
}

/* Categories */
.categories-section { padding: 3.5rem 0; background: var(--white); }
.categories-grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap:1.25rem;
}

.category-card { text-decoration:none; color:inherit; display:block; }
.category-image { position:relative; height:220px; border-radius:12px; overflow:hidden; box-shadow:var(--shadow); background:#f3efe9; }
.category-image img { width:100%; height:100%; object-fit:cover; transition:var(--transition); }
.category-card:hover .category-image img { transform:scale(1.06); }
.category-overlay { position:absolute; inset:0; display:flex; flex-direction:column; justify-content:flex-end; padding:1rem 1.25rem; background: linear-gradient(180deg, rgba(0,0,0,0.12), rgba(0,0,0,0.45)); color:#fff; }
.category-overlay h3 { margin:0 0 .25rem; font-size:1.15rem; }
.category-overlay p { font-size:.95rem; margin:0 0 .5rem; color:rgba(255,255,255,0.9); }

/* Products */
.featured-products { padding: 3.5rem 0; background: var(--light-gray); }
.products-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap:1rem;
}
.product-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:var(--shadow); display:flex; flex-direction:column; }
.product-image { height:260px; overflow:hidden; position:relative; }
.product-image img { width:100%; height:100%; object-fit:cover; transition:var(--transition); }
.product-card:hover .product-image img { transform:scale(1.05); }
.product-badge { position:absolute; top:12px; right:12px; background:var(--primary); color:#fff; padding:.35rem .6rem; border-radius:999px; font-weight:600; font-size:.85rem; z-index:2; }
.product-badge.discount { background:#10b981; top:56px; }

.product-content { padding:1rem 1.25rem 1.25rem; display:flex; flex-direction:column; gap:.5rem; flex:1; }
.product-category { font-size:.78rem; color:var(--text-light); text-transform:uppercase; letter-spacing:.6px; }
.product-name { font-size:1.05rem; margin:0; }
.product-description { font-size:.92rem; color:#444; margin:0; }
.product-price { font-size:1.1rem; font-weight:700; color:var(--primary); margin-top:auto; }

/* Features / CTA */
.features-section { padding:3.5rem 0; background:#fff; }
.features-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.feature-card { text-align:center; padding:1.25rem; border-radius:12px; box-shadow:var(--shadow); }

.cta-section { padding:2.5rem 0; background: linear-gradient(180deg,#efe6dc, #f7f5f3); }
.cta-content { text-align:center; max-width:760px; margin:0 auto; }

/* Fade-in helper */
.fade-in { opacity:0; transform:translateY(18px); transition:opacity .6s ease, transform .6s ease; }
.fade-in.animate-in { opacity:1; transform:none; }

/* Responsive tweaks */
@media (max-width: 980px) {
    .hero-content { grid-template-columns: 1fr; gap:1.25rem; text-align:center; }
    .hero-image { order: -1; }
    .products-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
}

@media (max-width: 480px) {
    .hero { padding:2rem 0; min-height:65vh; }
    .hero-title { font-size:1.5rem; }
    .category-image { height:180px; }
    .product-image { height:220px; }
}
</style>

<?php include 'includes/footer.php'; ?>
