<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with our fragrance experts';

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    }
    
    $firstName = clean($_POST['first_name'] ?? '');
    $lastName = clean($_POST['last_name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');
    
    // Validation
    if(empty($firstName)) $errors[] = 'First name is required';
    if(empty($lastName)) $errors[] = 'Last name is required';
    if(empty($email)) {
        $errors[] = 'Email is required';
    } elseif(!isValidEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    if(empty($subject)) $errors[] = 'Subject is required';
    if(empty($message)) $errors[] = 'Message is required';
    
    if(empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO contact_messages (first_name, last_name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $email, $subject, $message]);
            
            // Send notification email to admin
            $adminSubject = "New Contact Message: " . $subject;
            $adminMessage = "
                <h3>New Contact Message</h3>
                <p><strong>From:</strong> {$firstName} {$lastName} ({$email})</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong></p>
                <p>{$message}</p>
            ";
            sendEmail(ADMIN_EMAIL, $adminSubject, $adminMessage);
            
            // Send confirmation to customer
            $customerSubject = "We received your message - " . SITE_NAME;
            $customerMessage = "
                <h2>Thank you for contacting us!</h2>
                <p>Dear {$firstName},</p>
                <p>We've received your message and one of our team members will get back to you within 24 hours.</p>
                <p><strong>Your message:</strong></p>
                <p>{$message}</p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
            ";
            sendEmail($email, $customerSubject, $customerMessage);
            
            $success = true;
            
        } catch(Exception $e) {
            $errors[] = 'Failed to send message. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>Get in Touch</h1>
        <p>We'd love to hear from you</p>
    </div>
</div>

<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info-side">
                <h2>Contact Information</h2>
                <p>Have questions about our fragrances? Our perfume experts are here to help you find your perfect scent.</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="method-content">
                            <h4>Visit Our Boutique</h4>
                            <p>123 Fragrance Avenue<br>
                            Luxury District, NY 10001<br>
                            United States</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </div>
                        <div class="method-content">
                            <h4>Call Us</h4>
                            <p>+1 (555) 123-SCENT<br>
                            Mon-Sat: 10:00 AM - 8:00 PM<br>
                            Sun: 12:00 PM - 6:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <div class="method-content">
                            <h4>Email Us</h4>
                            <p>hello@essenceluxe.com<br>
                            support@essenceluxe.com<br>
                            We reply within 24 hours</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="method-content">
                            <h4>Business Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 9:00 PM<br>
                            Saturday: 10:00 AM - 8:00 PM<br>
                            Sunday: 12:00 PM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="social-section">
                    <h4>Follow Us</h4>
                    <div class="social-links-large">
                        <a href="#" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="white" fill="none"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="white"></line>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitter">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Pinterest">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0a12 12 0 0 0-4.37 23.17c-.05-.93-.1-2.37.02-3.39.1-.93.7-2.95.7-2.95s-.18-.36-.18-.89c0-.83.48-1.45 1.08-1.45.51 0 .76.38.76.84 0 .51-.33 1.27-.5 1.98-.14.6.3 1.08.89 1.08 1.07 0 1.9-1.13 1.9-2.76 0-1.44-1.04-2.45-2.52-2.45-1.72 0-2.73 1.29-2.73 2.62 0 .52.2 1.08.45 1.38.05.06.06.11.04.17l-.17.7c-.03.11-.1.13-.23.08-.81-.38-1.32-1.57-1.32-2.53 0-1.9 1.38-3.64 3.98-3.64 2.09 0 3.71 1.49 3.71 3.48 0 2.08-1.31 3.75-3.13 3.75-.61 0-1.18-.32-1.38-.69l-.37 1.42c-.14.52-.5 1.17-.75 1.57.57.17 1.17.27 1.8.27A12 12 0 1 0 12 0z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-side">
                <?php if($success): ?>
                    <div class="success-message">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <h3>Message Sent Successfully!</h3>
                        <p>Thank you for contacting us. We'll get back to you within 24 hours.</p>
                        <button onclick="location.reload()" class="btn btn-primary">Send Another Message</button>
                    </div>
                <?php else: ?>
                    <h2>Send Us a Message</h2>
                    
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a topic</option>
                                <option value="product-inquiry">Product Inquiry</option>
                                <option value="custom-fragrance">Custom Fragrance</option>
                                <option value="wholesale">Wholesale Inquiry</option>
                                <option value="support">Customer Support</option>
                                <option value="feedback">Feedback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="map-section">
    <div class="map-placeholder">
        <svg viewBox="0 0 1920 400" xmlns="http://www.w3.org/2000/svg">
            <rect fill="#e8ddd4" width="1920" height="400"/>
            <text x="50%" y="50%" text-anchor="middle" fill="#d4af37" font-size="32" font-family="Arial" dy=".3em">Map Placeholder - Visit Us at 123 Fragrance Avenue, NY 10001</text>
        </svg>
    </div>
</section>

<style>
.contact-section {
    padding: 3rem 0 5rem;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 4rem;
}

.contact-info-side h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.contact-info-side > p {
    color: var(--text-light);
    margin-bottom: 2rem;
    line-height: 1.7;
}

.contact-methods {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-bottom: 3rem;
}

.contact-method {
    display: flex;
    gap: 1.5rem;
}

.method-icon {
    width: 3.5rem;
    height: 3.5rem;
    background: var(--gradient-primary);
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.method-icon svg {
    width: 1.75rem;
    height: 1.75rem;
    stroke: white;
}

.method-content h4 {
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
}

.method-content p {
    color: var(--text-light);
    line-height: 1.7;
    margin: 0;
}

.social-section {
    padding: 2rem;
    background: var(--light-gray);
    border-radius: var(--border-radius-lg);
}

.social-section h4 {
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.social-links-large {
    display: flex;
    gap: 1rem;
}

.social-links-large a {
    width: 3rem;
    height: 3rem;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: var(--transition);
}

.social-links-large a:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.social-links-large svg {
    width: 1.5rem;
    height: 1.5rem;
}

.contact-form-side {
    background: white;
    padding: 3rem;
    border-radius: var(--border-radius-lg);
    border: 1px solid var(--gray);
}

.contact-form-side h2 {
    font-size: 2rem;
    margin-bottom: 2rem;
}

.success-message {
    text-align: center;
    padding: 3rem 2rem;
}

.success-message svg {
    width: 4rem;
    height: 4rem;
    color: #10b981;
    margin-bottom: 1.5rem;
}

.success-message h3 {
    font-size: 1.75rem;
    margin-bottom: 1rem;
    color: var(--text-dark);
}

.success-message p {
    color: var(--text-light);
    margin-bottom: 2rem;
}

.contact-form {
    display: flex;
    flex-direction: column;
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

.form-group textarea {
    resize: vertical;
}

.map-section {
    margin-top: 4rem;
}

.map-placeholder {
    width: 100%;
    height: 400px;
    background: var(--light-gray);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
}

.map-placeholder svg {
    width: 100%;
    height: 100%;
}

.alert {
    padding: 1rem;
    border-radius: var(--border-radius);
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

@media (max-width: 968px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .contact-form-side {
        padding: 2rem;
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .contact-method {
        flex-direction: column;
        text-align: center;
    }
    
    .method-icon {
        margin: 0 auto;
    }
    
    .social-links-large {
        justify-content: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?>