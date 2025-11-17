<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if(isLoggedIn()) {
    redirect('account.php');
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => ''
];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $formData['first_name'] = clean($_POST['first_name'] ?? '');
    $formData['last_name'] = clean($_POST['last_name'] ?? '');
    $formData['email'] = clean($_POST['email'] ?? '');
    $formData['phone'] = clean($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $agreeTerms = isset($_POST['agree_terms']);
    
    // Validate inputs
    if(empty($formData['first_name'])) {
        $errors[] = 'First name is required';
    } elseif(strlen($formData['first_name']) < 2) {
        $errors[] = 'First name must be at least 2 characters';
    }
    
    if(empty($formData['last_name'])) {
        $errors[] = 'Last name is required';
    } elseif(strlen($formData['last_name']) < 2) {
        $errors[] = 'Last name must be at least 2 characters';
    }
    
    if(empty($formData['email'])) {
        $errors[] = 'Email is required';
    } elseif(!isValidEmail($formData['email'])) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email already exists
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$formData['email']]);
        if($stmt->fetch()) {
            $errors[] = 'Email already registered';
        }
    }
    
    if(empty($password)) {
        $errors[] = 'Password is required';
    } elseif(strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    } elseif(!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    } elseif(!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    } elseif(!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if(!$agreeTerms) {
        $errors[] = 'You must agree to the terms and conditions';
    }
    
    // Verify CSRF token
    if(!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token';
    }
    
    if(empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
        
        // Insert user
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['first_name'],
                $formData['last_name'],
                $formData['email'],
                $formData['phone'],
                $hashedPassword
            ]);
            
            $userId = $db->lastInsertId();
            
            // Auto login
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $formData['email'];
            $_SESSION['user_name'] = $formData['first_name'] . ' ' . $formData['last_name'];
            $_SESSION['role'] = 'customer';
            
            // Send welcome email
            $subject = "Welcome to " . SITE_NAME;
            $message = "
                <h2>Welcome to " . SITE_NAME . "!</h2>
                <p>Thank you for creating an account, " . $formData['first_name'] . ".</p>
                <p>We're excited to have you join our community of fragrance enthusiasts.</p>
                <p>Start exploring our exclusive collection of luxury perfumes today!</p>
                <p><a href='" . SITE_URL . "/shop.php' style='background: #d4af37; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>Shop Now</a></p>
            ";
            sendEmail($formData['email'], $subject, $message);
            
            setFlash('success', 'Welcome to ' . SITE_NAME . '! Your account has been created successfully.');
            redirect('login.php');
        } catch(Exception $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Create Account';
$pageDescription = 'Create your account';
?>
<?php include 'includes/header.php'; ?>

<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Create Account</h1>
                    <p>Join our fragrance community</p>
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
                
                <form method="POST" action="register.php" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                required 
                                autofocus
                                placeholder="John">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                                required
                                placeholder="Doe">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($formData['email']); ?>"
                            required
                            placeholder="your@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($formData['phone']); ?>"
                            placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="Minimum 8 characters">
                        <small class="form-help">Must contain uppercase, lowercase, and number</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            placeholder="Re-enter password">
                    </div>
                    
                    <div class="form-check-group">
                        <input type="checkbox" id="agree_terms" name="agree_terms" required>
                        <label for="agree_terms">
                            I agree to the <a href="terms.php" class="text-link-primary">Terms & Conditions</a> 
                            and <a href="privacy.php" class="text-link-primary">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    
                    <div class="auth-divider">
                        <span>or</span>
                    </div>
                    
                    <p class="auth-footer">
                        Already have an account? 
                        <a href="login.php" class="text-link-primary">Login</a>
                    </p>
                </form>
            </div>
            
            <div class="auth-image">
                <img src="assets/images/auth-bg-2.jpg" alt="Essence Luxe" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'600\' height=\'800\'%3E%3Crect fill=\'%23e8ddd4\' width=\'600\' height=\'800\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' fill=\'%23d4af37\' font-size=\'48\' font-family=\'Arial\'%3EJoin Us%3C/text%3E%3C/svg%3E'">
                <div class="auth-image-overlay">
                    <h2>Start Your Fragrance Journey</h2>
                    <p>Exclusive access to luxury perfumes</p>
                    <ul class="benefits-list">
                        <li>✓ Personalized recommendations</li>
                        <li>✓ Early access to new releases</li>
                        <li>✓ Members-only discounts</li>
                        <li>✓ Free shipping on orders over $100</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>


.auth-section {
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


.benefits-list {
    list-style: none;
    padding: 0;
    margin-top: 1.5rem;
    font-size: 1rem;
    line-height: 1.8;
}

.benefits-list li {
    color: rgba(255, 255, 255, 0.95);
    font-weight: 500;
}

</style>

<?php include 'includes/footer.php'; ?>