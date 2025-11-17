<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'My Account';
$pageDescription = 'Manage your account and orders';

$currentUser = getCurrentUser();
$activeTab = $_GET['tab'] ?? 'dashboard';

// Handle profile update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if(verifyCSRF($_POST['csrf_token'] ?? '')) {
        $firstName = clean($_POST['first_name'] ?? '');
        $lastName = clean($_POST['last_name'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $address = clean($_POST['address'] ?? '');
        $city = clean($_POST['city'] ?? '');
        $state = clean($_POST['state'] ?? '');
        $zipCode = clean($_POST['zip_code'] ?? '');
        $country = clean($_POST['country'] ?? '');
        
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, 
                             address = ?, city = ?, state = ?, zip_code = ?, country = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $phone, $address, $city, $state, $zipCode, $country, $_SESSION['user_id']]);
        
        setFlash('success', 'Profile updated successfully');
        redirect('account.php');
    }
}

// Handle password change
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if(verifyCSRF($_POST['csrf_token'] ?? '')) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if(password_verify($currentPassword, $currentUser['password'])) {
            if(strlen($newPassword) >= 8 && $newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
                $db = getDB();
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                setFlash('success', 'Password changed successfully');
                redirect('account.php?tab=settings');
            } else {
                setFlash('error', 'New password must be at least 8 characters and match confirmation');
            }
        } else {
            setFlash('error', 'Current password is incorrect');
        }
    }
}

// Get user orders
$db = getDB();
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Get order statistics
$stmt = $db->prepare("SELECT COUNT(*) as total_orders, 
                     SUM(total_amount) as total_spent 
                     FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>
<?php include 'includes/header.php'; ?>

<section class="account-section">
    <div class="container">
        <h1>My Account</h1>
        
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <aside class="account-sidebar">
                <div class="account-user">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
                </div>
                
                <nav class="account-nav">
                    <a href="account.php?tab=dashboard" class="account-nav-link <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Dashboard
                    </a>
                    <a href="account.php?tab=orders" class="account-nav-link <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                        </svg>
                        My Orders
                    </a>
                    <a href="account.php?tab=profile" class="account-nav-link <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Profile
                    </a>
                    <a href="account.php?tab=settings" class="account-nav-link <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m8.66-12l-5.2 3m-2.92 1.5l-5.2 3M21 12h-6m-6 0H3m15.66 8.66l-5.2-3m-2.92-1.5l-5.2-3"></path>
                        </svg>
                        Settings
                    </a>
                    <a href="logout.php" class="account-nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </nav>
            </aside>
            
            <!-- Main Content -->
            <div class="account-content">
                <?php if($activeTab === 'dashboard'): ?>
                    <div class="account-dashboard">
                        <h2>Welcome Back, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</h2>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    </svg>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                                    <p>Total Orders</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo formatPrice($stats['total_spent'] ?? 0); ?></h3>
                                    <p>Total Spent</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $currentUser['address'] ? 'Complete' : 'Incomplete'; ?></h3>
                                    <p>Shipping Address</p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if(!empty($orders)): ?>
                            <div class="recent-orders">
                                <h3>Recent Orders</h3>
                                <div class="orders-list">
                                    <?php foreach(array_slice($orders, 0, 5) as $order): ?>
                                        <div class="order-item">
                                            <div class="order-info">
                                                <h4>Order #<?php echo htmlspecialchars($order['order_number']); ?></h4>
                                                <p><?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                                            </div>
                                            <div class="order-status">
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                            <div class="order-total">
                                                <?php echo formatPrice($order['total_amount']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="account.php?tab=orders" class="btn btn-outline">View All Orders</a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                </svg>
                                <h3>No Orders Yet</h3>
                                <p>Start shopping to see your orders here</p>
                                <a href="shop.php" class="btn btn-primary">Browse Products</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php elseif($activeTab === 'orders'): ?>
                    <div class="orders-section">
                        <h2>My Orders</h2>
                        
                        <?php if(!empty($orders)): ?>
                            <div class="orders-table">
                                <?php foreach($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div>
                                                <h4>Order #<?php echo htmlspecialchars($order['order_number']); ?></h4>
                                                <p>Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                                            </div>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <div class="order-details">
                                            <div class="detail-item">
                                                <span class="label">Total:</span>
                                                <span class="value"><?php echo formatPrice($order['total_amount']); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="label">Payment:</span>
                                                <span class="value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="label">Shipping:</span>
                                                <span class="value"><?php echo htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_state']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                </svg>
                                <h3>No Orders Yet</h3>
                                <p>You haven't placed any orders</p>
                                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php elseif($activeTab === 'profile'): ?>
                    <div class="profile-section">
                        <h2>Profile Information</h2>
                        
                        <form method="POST" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                                <small>Email cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($currentUser['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($currentUser['state'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="zip_code">ZIP Code</label>
                                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($currentUser['zip_code'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($currentUser['country'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                    
                <?php elseif($activeTab === 'settings'): ?>
                    <div class="settings-section">
                        <h2>Account Settings</h2>
                        
                        <div class="settings-box">
                            <h3>Change Password</h3>
                            <form method="POST" class="password-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required>
                                    <small>Minimum 8 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                        
                        <div class="settings-box">
                            <h3>Account Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Member Since:</span>
                                    <span class="info-value"><?php echo date('M j, Y', strtotime($currentUser['created_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Last Login:</span>
                                    <span class="info-value"><?php echo $currentUser['last_login'] ? date('M j, Y g:i A', strtotime($currentUser['last_login'])) : 'Never'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Account Status:</span>
                                    <span class="info-value status-active">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.account-section {
    padding: 3rem 0;
}

.account-section h1 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
}

.account-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 3rem;
}

.account-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.account-user {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: var(--border-radius-lg);
    border: 1px solid var(--gray);
    margin-bottom: 1.5rem;
}

.user-avatar {
    width: 5rem;
    height: 5rem;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin: 0 auto 1rem;
}

.account-user h3 {
    font-size: 1.125rem;
    margin-bottom: 0.25rem;
}

.account-user p {
    color: var(--text-light);
    font-size: 0.875rem;
    margin: 0;
}

.account-nav {
    display: flex;
    flex-direction: column;
}

.account-nav-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: var(--border-radius);
    transition: var(--transition);
    margin-bottom: 0.5rem;
}

.account-nav-link:hover,
.account-nav-link.active {
    background: var(--light-gray);
    color: var(--primary);
}

.account-nav-link svg {
    width: 1.25rem;
    height: 1.25rem;
}

.account-content {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    border: 1px solid var(--gray);
}

.account-content h2 {
    font-size: 2rem;
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    background: var(--light-gray);
    border-radius: var(--border-radius-lg);
}

.stat-icon {
    width: 3.5rem;
    height: 3.5rem;
    background: var(--gradient-primary);
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon svg {
    width: 1.75rem;
    height: 1.75rem;
    stroke: white;
}

.stat-info h3 {
    font-size: 1.75rem;
    margin-bottom: 0.25rem;
}

.stat-info p {
    color: var(--text-light);
    margin: 0;
    font-size: 0.925rem;
}

.recent-orders h3 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}

.orders-list {
    margin-bottom: 2rem;
}

.order-item {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 2rem;
    align-items: center;
    padding: 1.5rem;
    border: 1px solid var(--gray);
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.order-info h4 {
    font-size: 1.125rem;
    margin-bottom: 0.25rem;
}

.order-info p {
    color: var(--text-light);
    font-size: 0.875rem;
    margin: 0;
}

.status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #e0e7ff; color: #4338ca; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.order-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state svg {
    width: 4rem;
    height: 4rem;
    color: var(--text-light);
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 2rem;
}

.order-card {
    background: white;
    border: 1px solid var(--gray);
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray);
}

.order-header h4 {
    font-size: 1.125rem;
    margin-bottom: 0.25rem;
}

.order-header p {
    color: var(--text-light);
    font-size: 0.875rem;
    margin: 0;
}

.order-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item .label {
    color: var(--text-light);
    font-size: 0.875rem;
}

.detail-item .value {
    font-weight: 600;
}

.profile-form,
.password-form {
    max-width: 600px;
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
}

.form-group input {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid var(--gray);
    border-radius: var(--border-radius);
    font-size: 1rem;
}

.form-group input:disabled {
    background: var(--light-gray);
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-light);
    font-size: 0.875rem;
}

.settings-box {
    background: var(--light-gray);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    margin-bottom: 2rem;
}

.settings-box h3 {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
}

.info-grid {
    display: grid;
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 1rem;
    background: white;
    border-radius: var(--border-radius);
}

.info-label {
    color: var(--text-light);
}

.info-value {
    font-weight: 600;
}

.info-value.status-active {
    color: #059669;
}

@media (max-width: 968px) {
    .account-layout {
        grid-template-columns: 1fr;
    }
    
    .account-sidebar {
        position: static;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>