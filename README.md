# Essence Luxe - Premium Perfume E-Commerce Store

A complete, responsive PHP/MySQL e-commerce platform for luxury perfume sales with user authentication, shopping cart, checkout, and admin capabilities.

## 🎯 Features

### ✅ User Features
- **Authentication System**
  - User registration with validation
  - Secure login with remember me
  - Password hashing (bcrypt)
  - Session management
  - Profile management

- **Shopping Experience**
  - Product catalog with search & filters
  - Category browsing
  - Product detail pages
  - Shopping cart (session + database)
  - Checkout process
  - Order tracking

- **User Dashboard**
  - Order history
  - Profile editing
  - Password change
  - Account statistics

### 🔒 Security Features
- PDO prepared statements (SQL injection protection)
- CSRF token protection
- XSS prevention with htmlspecialchars
- Password strength validation
- Secure session handling
- Input sanitization

### 📱 Responsive Design
- Mobile-first approach
- Hamburger menu that pushes content down (no overlay issues)
- Responsive grid layouts
- Touch-friendly buttons
- Optimized for all screen sizes (mobile, tablet, desktop)

## 📁 Project Structure

```
essence-luxe/
├── config/
│   └── database.php              # Database configuration
├── includes/
│   ├── header.php                # Header template
│   ├── footer.php                # Footer template
│   └── functions.php             # Helper functions
├── assets/
│   ├── css/
│   │   └── style.css             # Main stylesheet
│   ├── js/
│   │   └── main.js               # JavaScript functions
│   └── images/
│       ├── products/             # Product images
│       ├── categories/           # Category images
│       └── hero/                 # Hero section images
├── admin/                        # Admin panel (to be created)
├── database.sql                  # Database schema
├── index.php                     # Homepage
├── shop.php                      # Product listing
├── product.php                   # Product details
├── cart.php                      # Shopping cart
├── checkout.php                  # Checkout
├── login.php                     # Login page
├── register.php                  # Registration
├── account.php                   # User dashboard
├── about.php                     # About page
├── contact.php                   # Contact form
├── logout.php                    # Logout
└── README.md                     # This file
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for future extensions)

### Step 1: Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE essence_luxe;
```

2. Import the database schema:
```bash
mysql -u your_username -p essence_luxe < database.sql
```

Or use phpMyAdmin to import `database.sql`

### Step 2: Configuration

1. Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'essence_luxe');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Update site URL in `config/database.php`:
```php
define('SITE_URL', 'http://localhost/essence-luxe');
```

### Step 3: File Permissions

Ensure proper permissions:
```bash
chmod 755 -R /path/to/essence-luxe
chmod 777 -R /path/to/essence-luxe/assets/images
```

### Step 4: Image Setup

Add product images to the following directories:
- `assets/images/products/` - Product images (named as per database entries)
- `assets/images/categories/` - Category images
- `assets/images/hero/` - Hero section images

Sample image names from database:
- rose-noir.jpg
- midnight-oud.jpg
- ocean-breeze.jpg
- vanilla-dreams.jpg
- citrus-burst.jpg
- smoky-leather.jpg
- floral-paradise.jpg
- mystic-forest.jpg

### Step 5: Test the Installation

1. Access the site: `http://localhost/essence-luxe`
2. Test registration and login
3. Browse products and add to cart
4. Test checkout process

## 🔑 Default Credentials

### Admin Account
- **Email:** admin@essenceluxe.com
- **Password:** admin123

### Test Customer
- **Email:** john.doe@example.com
- **Password:** customer123

**⚠️ IMPORTANT:** Change these passwords in production!

## 📊 Database Tables

- `users` - User accounts and authentication
- `categories` - Product categories
- `products` - Product information
- `product_images` - Multiple images per product
- `orders` - Customer orders
- `order_items` - Order line items
- `cart` - Shopping cart (persistent)
- `wishlist` - User wishlists
- `reviews` - Product reviews
- `contact_messages` - Contact form submissions
- `newsletter_subscribers` - Newsletter emails
- `password_resets` - Password reset tokens

## 🎨 Customization

### Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary: #d4af37;
    --secondary: #2c1810;
    --accent: #ff6b9d;
}
```

### Site Information
Update in `config/database.php`:
```php
define('SITE_NAME', 'Your Store Name');
define('ADMIN_EMAIL', 'your@email.com');
```

### Logo
Replace the SVG in `includes/header.php` and `includes/footer.php`

## 📱 Responsive Breakpoints

- **Desktop:** > 968px
- **Tablet:** 640px - 968px
- **Mobile:** < 640px

## ⚡ Performance Tips

1. Enable PHP OPcache
2. Use image optimization (TinyPNG, ImageOptim)
3. Enable gzip compression
4. Use browser caching
5. Minify CSS/JS for production
6. Consider CDN for static assets

## 🔧 Troubleshooting

### Cart not working
- Check session configuration in php.ini
- Verify session_start() is called

### Database connection errors
- Verify credentials in config/database.php
- Check MySQL service is running
- Ensure database exists

### Images not displaying
- Check file permissions (755 for directories, 644 for files)
- Verify image paths in database match actual filenames
- Check image file extensions are lowercase

### Email not sending
- Configure SMTP settings (use PHPMailer for production)
- Check sendEmail() function in includes/functions.php
- Verify server has mail functionality enabled

## 📧 Email Configuration

For production, replace the basic `mail()` function with PHPMailer:

```bash
composer require phpmailer/phpmailer
```

Update `includes/functions.php` sendEmail() function with SMTP configuration.

## 🔐 Security Recommendations

### For Production:

1. **Change default passwords** for admin and test accounts
2. **Enable HTTPS** and update SITE_URL
3. **Set secure session settings:**
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
```

4. **Disable error display:**
```php
error_reporting(0);
ini_set('display_errors', 0);
```

5. **Regular backups** of database and files
6. **Keep PHP and MySQL updated**
7. **Use prepared statements** (already implemented)
8. **Implement rate limiting** for login attempts
9. **Add reCAPTCHA** to forms
10. **Regular security audits**

## 🚧 Future Enhancements

- [ ] Admin dashboard
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Advanced search with filters
- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Email notifications
- [ ] Order tracking system
- [ ] Inventory management
- [ ] Sales analytics
- [ ] Discount codes/coupons
- [ ] Multi-language support
- [ ] Social login (OAuth)

## 📝 License

This project is provided as-is for educational purposes.

## 👥 Support

For issues or questions:
1. Check this README
2. Review code comments
3. Check database schema
4. Test with default credentials

## 🎓 Learning Resources

- **PHP:** https://www.php.net/docs.php
- **MySQL:** https://dev.mysql.com/doc/
- **Security:** https://owasp.org/www-project-top-ten/
- **Responsive Design:** https://web.dev/responsive-web-design-basics/

---

**Built with ❤️ using PHP, MySQL, HTML, CSS, and JavaScript**

Last Updated: November 2025