<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Shop';
$pageDescription = 'Browse our complete collection of luxury perfumes';

// Get filter parameters
$filters = [];
$categorySlug = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build filters
if($categorySlug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$categorySlug]);
    $category = $stmt->fetch();
    if($category) {
        $filters['category'] = $category['id'];
    }
}

if($searchQuery) {
    $filters['search'] = $searchQuery;
}

// Set sort order
$sortOptions = [
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name_az' => 'p.name ASC',
    'name_za' => 'p.name DESC'
];
$filters['sort'] = $sortOptions[$sortBy] ?? $sortOptions['newest'];

// Get total count for pagination
$db = getDB();
$countSql = "SELECT COUNT(*) as total FROM products p WHERE p.is_active = 1";
$countParams = [];

if(isset($filters['category'])) {
    $countSql .= " AND p.category_id = ?";
    $countParams[] = $filters['category'];
}

if(isset($filters['search'])) {
    $countSql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.notes LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
}

$stmt = $db->prepare($countSql);
$stmt->execute($countParams);
$totalProducts = $stmt->fetch()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$filters['limit'] = $perPage;
$filters['offset'] = $offset;
$products = getProducts($filters);

// Get all categories for filter
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>Shop Our Collection</h1>
        <p>Discover <?php echo $totalProducts; ?> luxury fragrances</p>
    </div>
</div>

<section class="shop-section">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="shop.php" class="<?php echo !$categorySlug ? 'active' : ''; ?>">
                                All Products
                            </a>
                        </li>
                        <?php foreach($categories as $cat): ?>
                            <li>
                                <a href="shop.php?category=<?php echo $cat['slug']; ?>" 
                                   class="<?php echo $categorySlug === $cat['slug'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="filter-section">
                    <h3>Sort By</h3>
                    <select id="sortSelect" class="form-select">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_az" <?php echo $sortBy === 'name_az' ? 'selected' : ''; ?>>Name: A-Z</option>
                        <option value="name_za" <?php echo $sortBy === 'name_za' ? 'selected' : ''; ?>>Name: Z-A</option>
                    </select>
                </div>
                
                <?php if($categorySlug || $searchQuery): ?>
                    <div class="filter-section">
                        <a href="shop.php" class="btn btn-outline btn-block">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </aside>
            
            <!-- Products Grid -->
            <div class="shop-content">
                <?php if($searchQuery): ?>
                    <div class="search-info">
                        <p>Showing results for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"</p>
                    </div>
                <?php endif; ?>
                
                <?php if(empty($products)): ?>
                    <div class="no-products">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h2>No Products Found</h2>
                        <p>Try adjusting your filters or search terms</p>
                        <a href="shop.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product.php?slug=<?php echo $product['slug']; ?>">
                                        <img src="assets/images/products/<?php echo $product['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             loading="lazy"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'400\'%3E%3Crect fill=\'%23f5f1eb\' width=\'300\' height=\'400\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' fill=\'%23d4af37\' font-size=\'16\' font-family=\'Arial\'%3E<?php echo htmlspecialchars($product['name']); ?>%3C/text%3E%3C/svg%3E'">
                                    </a>
                                    <?php if($product['badge']): ?>
                                        <div class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></div>
                                    <?php endif; ?>
                                    <?php if($product['original_price']): 
                                        $discount = getDiscountPercentage($product['original_price'], $product['price']);
                                        if($discount > 0):
                                    ?>
                                        <div class="product-badge discount"><?php echo $discount; ?>% OFF</div>
                                    <?php endif; endif; ?>
                                    
                                    <?php if($product['stock_quantity'] <= 0): ?>
                                        <div class="product-badge out-of-stock">Out of Stock</div>
                                    <?php elseif($product['stock_quantity'] < 10): ?>
                                        <div class="product-badge low-stock">Only <?php echo $product['stock_quantity']; ?> left</div>
                                    <?php endif; ?>
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
                                        <?php if($product['original_price']): ?>
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
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 4px;">
                                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                                    </svg>
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
                    
                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $baseQuery = http_build_query($queryParams);
                            $baseUrl = 'shop.php?' . ($baseQuery ? $baseQuery . '&' : '');
                            ?>
                            
                            <?php if($page > 1): ?>
                                <a href="<?php echo $baseUrl; ?>page=<?php echo $page - 1; ?>" class="pagination-link">
                                    ← Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <a href="<?php echo $baseUrl; ?>page=<?php echo $i; ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                                <a href="<?php echo $baseUrl; ?>page=<?php echo $page + 1; ?>" class="pagination-link">
                                    Next →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
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

.page-header p {
    font-size: 1.125rem;
    color: var(--text-light);
    margin: 0;
}

.shop-section {
    padding: 3rem 0;
}

.shop-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 3rem;
}

.shop-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.filter-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--gray);
}

.filter-section:last-child {
    border-bottom: none;
}

.filter-section h3 {
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.filter-list {
    list-style: none;
}

.filter-list li {
    margin-bottom: 0.5rem;
}

.filter-list a {
    display: block;
    padding: 0.5rem 1rem;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.filter-list a:hover,
.filter-list a.active {
    background: var(--light-gray);
    color: var(--primary);
}

.form-select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    font-family: var(--font-secondary);
    font-size: 1rem;
    cursor: pointer;
}

.search-info {
    margin-bottom: 2rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--border-radius);
}

.no-products {
    text-align: center;
    padding: 4rem 2rem;
}

.no-products svg {
    width: 4rem;
    height: 4rem;
    color: var(--text-light);
    margin-bottom: 1rem;
}

.no-products h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.no-products p {
    color: var(--text-light);
    margin-bottom: 2rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.product-badge.low-stock {
    background: #f59e0b;
    top: 3.5rem;
}

.product-badge.out-of-stock {
    background: #ef4444;
    top: 3.5rem;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 3rem;
    flex-wrap: wrap;
}

.pagination-link {
    padding: 0.5rem 1rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    color: var(--text-dark);
    text-decoration: none;
    transition: var(--transition);
    min-width: 2.5rem;
    text-align: center;
}

.pagination-link:hover,
.pagination-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.pagination-ellipsis {
    padding: 0.5rem;
    color: var(--text-light);
}

@media (max-width: 968px) {
    .shop-layout {
        grid-template-columns: 1fr;
    }
    
    .shop-sidebar {
        position: static;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
}

@media (max-width: 640px) {
    .shop-sidebar {
        grid-template-columns: 1fr;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.getElementById('sortSelect').addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('sort', this.value);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
});
</script>

<?php include 'includes/footer.php'; ?>