<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if(isLoggedIn()) {
    redirect('account.php');
}

$errors = [];
$email = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate inputs
    if(empty($email)) {
        $errors[] = 'Email is required';
    } elseif(!isValidEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if(empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token';
    }
    
    if(empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Load cart from database
            loadCartFromDB($user['id']);
            
            // Remember me functionality
            if($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
            }
            
            setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
            
            // Redirect to intended page or account
            $redirect = $_SESSION['redirect_after_login'] ?? 'account.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}

$pageTitle = 'Login';
$pageDescription = 'Login to your account';
?>
<?php include 'includes/header.php'; ?>

<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Login to your account</p>
                </div>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>"
                            required 
                            autofocus
                            placeholder="your@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="Enter your password">
                    </div>
                    
                    <div class="form-row space-between">
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="text-link">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                    
                    <div class="auth-divider">
                        <span>or</span>
                    </div>
                    
                    <p class="auth-footer">
                        Don't have an account? 
                        <a href="register.php" class="text-link-primary">Create Account</a>
                    </p>
                </form>
            </div>
            
            <div class="auth-image">
                <img src="assets/images/auth-bg.jpg" alt="Essence Luxe" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'600\' height=\'800\'%3E%3Crect fill=\'%23f5f1eb\' width=\'600\' height=\'800\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' fill=\'%23d4af37\' font-size=\'48\' font-family=\'Arial\'%3EEssence Luxe%3C/text%3E%3C/svg%3E'">
                <div class="auth-image-overlay">
                    <h2>Discover Your Signature Scent</h2>
                    <p>Join thousands of fragrance enthusiasts</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>.auth-section {
    min-height: calc(100vh - 120px); /* Reduced height */
    display: flex;
    align-items: center;
    padding: 2rem 0; /* Reduced padding */
    background: linear-gradient(135deg, #f5f1eb 0%, #e8ddd4 100%);
    margin-top: 80px;
}

.auth-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    max-width: 750px; /* Reduced width */
    margin: 0 auto;
    background: white;
    border-radius: 1.2rem;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.auth-card {
    padding: 2rem; /* Reduced padding */
}

.auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-header h1 {
    font-size: 1.8rem;
    color: var(--text-dark);
    margin-bottom: 0.4rem;
}

.auth-header p {
    color: var(--text-light);
    margin: 0;
}

.auth-form {
    max-width: 400px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.4rem;
    color: var(--text-dark);
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--gray);
    border-radius: 0.75rem;
    font-family: var(--font-secondary);
    font-size: 1rem;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.form-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1.25rem;
}

.form-row.space-between {
    justify-content: space-between;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-check input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

.text-link {
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.925rem;
    transition: color 0.3s;
}

.text-link:hover {
    color: var(--primary);
}

.text-link-primary {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.text-link-primary:hover {
    text-decoration: underline;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.auth-divider {
    position: relative;
    text-align: center;
    margin: 1.25rem 0;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--gray);
}

.auth-divider span {
    position: relative;
    background: white;
    padding: 0 1rem;
    color: var(--text-light);
    font-size: 0.925rem;
}

.auth-footer {
    text-align: center;
    margin-top: 1.25rem;
    color: var(--text-light);
}

.auth-image {
    position: relative;
    min-height: 300px; /* Reduced height */
}

.auth-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.auth-image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.9), rgba(44, 24, 16, 0.8));
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: white;
    text-align: center;
}

.auth-image-overlay h2 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: white;
}

.auth-image-overlay p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

.alert {
    padding: 1rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
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

.alert li {
    margin-bottom: 0.25rem;
}

/* Responsive */
@media (max-width: 968px) {
    .auth-wrapper {
        grid-template-columns: 1fr;
        max-width: 90%; /* Smaller on tablet */
    }
    
    .auth-image {
        min-height: 250px; /* Reduce mobile height */
    }
    
    .auth-card {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .auth-section {
        padding: 1.5rem 0;
    }
    
    .form-row.space-between {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .auth-card {
        padding: 1.25rem;
    }
}

</style>

<?php include 'includes/footer.php'; ?>