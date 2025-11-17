</main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <span><?php echo SITE_NAME; ?></span>
                    </div>
                    <p class="footer-description">
                        Creating exceptional fragrances that capture the essence of luxury and sophistication.
                    </p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook" title="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram" title="Instagram">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitter" title="Twitter">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Pinterest" title="Pinterest">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Collections</h3>
                    <ul>
                        <li><a href="shop.php?category=women">Women's Fragrances</a></li>
                        <li><a href="shop.php?category=men">Men's Fragrances</a></li>
                        <li><a href="shop.php?category=unisex">Unisex Collection</a></li>
                        <li><a href="shop.php?featured=1">Limited Editions</a></li>
                        <li><a href="shop.php">Gift Sets</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="shop.php">Shop All</a></li>
                        <?php if(isLoggedIn()): ?>
                            <li><a href="account.php">My Account</a></li>
                            <li><a href="account.php?tab=orders">Order History</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Create Account</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Subscribe to get special offers and updates</p>
                    <form action="subscribe.php" method="POST" class="newsletter-form">
                        <input type="email" name="email" placeholder="Your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                    <div class="payment-methods">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='25' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23666' font-size='8' font-family='Arial'%3EVISA%3C/text%3E%3C/svg%3E" alt="Visa">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='25' viewBox='0 0 40 25'%3E%3Crect fill='%23fff' width='40' height='25' rx='3'/%3E%3Ccircle cx='15' cy='12.5' r='7' fill='%23eb001b'/%3E%3Ccircle cx='25' cy='12.5' r='7' fill='%23f79e1b'/%3E%3C/svg%3E" alt="Mastercard">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='25' viewBox='0 0 40 25'%3E%3Crect fill='%23003087' width='40' height='25' rx='3'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fff' font-size='7' font-family='Arial' font-weight='bold'%3EPayPal%3C/text%3E%3C/svg%3E" alt="PayPal">
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. | <a style="color: var(--white); text-decoration: none;" href="https://github.com/Rehtse11">Developed by Ayomide</a></p>
                    <div class="footer-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="terms.php">Terms of Service</a>
                        <a href="shipping.php">Shipping Info</a>
                        <a href="returns.php">Returns</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <?php if(isset($additionalJS)) echo $additionalJS; ?>
</body>
</html>

<style>
.footer {
    background: #787f56;
    color: var(--white);
    padding: 3rem 0 1rem;
    margin-top: 4rem;
}

.footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr;
    gap: 3rem;
    margin-bottom: 2rem;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-family: var(--font-primary);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.footer-brand .brand-icon {
    width: 2rem;
    height: 2rem;
    fill: var(--primary);
}

.footer-description {
    margin-bottom: 1.5rem;
    color: rgba(255, 255, 255, 0.8);
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-links a {
    width: 2.5rem;
    height: 2.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    transition: var(--transition);
}

.social-links a:hover {
    background: var(--primary);
    transform: translateY(-2px);
}

.social-links svg {
    width: 1.25rem;
    height: 1.25rem;
}

.footer-section h3 {
    margin-bottom: 1rem;
    color: var(--white);
    font-size: 1.125rem;
}

.footer-section p {
    margin-bottom: 1rem;
    color: var(--white);
    font-size: 0.825rem;
}

.footer-section ul {
    list-style: none;
}

.footer-section ul li {
    margin-bottom: 0.5rem;
}

.footer-section ul li a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.925rem;
}

.footer-section ul li a:hover {
    color: var(--primary);
}

.newsletter-form {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    margin-bottom: 1.5rem;
}

.newsletter-form input {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: var(--border-radius);
    font-size: 0.925rem;
}

.newsletter-form button {
    padding: 0.75rem 1.5rem;
    font-size: 0.925rem;
}

.payment-methods {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.payment-methods img {
    height: 25px;
    width: auto;
    border-radius: 4px;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 2rem;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-bottom p {
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    font-size: 0.925rem;
}

.footer-links {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.925rem;
}

.footer-links a:hover {
    color: var(--primary);
}

@media (max-width: 968px) {
    .footer-content {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .footer-brand,
    .social-links {
        justify-content: center;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .payment-methods {
        justify-content: center;
    }
}
</style>