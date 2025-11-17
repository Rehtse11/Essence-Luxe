<?php
if(!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/functions.php';
}

$currentUser = getCurrentUser();
$cartCount = getCartCount();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Discover our exclusive collection of luxury perfumes'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌸</text></svg>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Additional CSS -->
    <?php if(isset($additionalCSS)) echo $additionalCSS; ?>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <nav class="nav">
            <div class="nav-container">
                <a href="index.php" class="nav-brand">
                    <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span class="brand-text"><?php echo SITE_NAME; ?></span>
                </a>
                
                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="shop.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>">Shop</a></li>
                    <li><a href="about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                    <li><a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li><a href="account.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'active' : ''; ?>">My Account</a></li>
                        <?php if(isAdmin()): ?>
                            <li><a href="admin/dashboard.php" class="nav-link">Admin</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-actions">
                    <button class="search-btn" id="searchBtn" aria-label="Search" title="Search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                    
                    <?php if(isLoggedIn()): ?>
                        <a href="account.php" class="user-btn" aria-label="Account" title="My Account">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>
                        <a href="logout.php" class="logout-btn" aria-label="Logout" title="Logout">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="login-btn" aria-label="Login" title="Login">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="cart-btn" aria-label="Shopping Cart" title="Shopping Cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <?php if($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    <?php if($flash): ?>
        <div class="toast show <?php echo $flash['type']; ?>" id="flashToast">
            <div class="toast-content">
                <div class="toast-icon">
                    <?php echo $flash['type'] === 'success' ? '✓' : '✗'; ?>
                </div>
                <span class="toast-message"><?php echo htmlspecialchars($flash['message']); ?></span>
                <button class="toast-close" onclick="document.getElementById('flashToast').classList.remove('show')">&times;</button>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('flashToast');
                if(toast) toast.classList.remove('show');
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Search Modal -->
    <div class="search-modal" id="searchModal">
        <div class="search-modal-content">
            <div class="search-header">
                <form action="shop.php" method="GET" id="searchForm">
                    <input type="text" name="search" id="searchInput" placeholder="Search fragrances..." autocomplete="off" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </form>
                <button class="search-close" id="searchClose" aria-label="Close search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="search-results" id="searchResults">
                <div class="search-suggestions">
                    <h4>Popular Searches</h4>
                    <div class="suggestion-tags">
                        <a href="shop.php?search=Rose" class="suggestion-tag">Rose</a>
                        <a href="shop.php?search=Vanilla" class="suggestion-tag">Vanilla</a>
                        <a href="shop.php?search=Woody" class="suggestion-tag">Woody</a>
                        <a href="shop.php?search=Citrus" class="suggestion-tag">Citrus</a>
                        <a href="shop.php?search=Floral" class="suggestion-tag">Floral</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="main-content"><?php /* Page content starts here */ ?>