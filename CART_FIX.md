# Shopping Cart Fix Guide

## Files Created/Updated

### ✅ New Files Created:
1. **add-to-cart.php** - Standalone cart handler
2. **test-cart.php** - Debug/testing tool (DELETE in production)

### ✅ Files Updated:
1. **shop.php** - Added "Add to Cart" buttons
2. **index.php** - Added "Add to Cart" buttons  
3. **product.php** - Added "Buy Now" button
4. **includes/functions.php** - Improved addToCart() function

---

## Quick Test Steps

### Step 1: Test Cart Functionality
1. Access: `http://localhost/essence-luxe/test-cart.php`
2. Click "Add Test Product" button
3. Verify product appears in cart
4. Check for any error messages

### Step 2: Test from Shop Page
1. Go to `shop.php`
2. Click "Add to Cart" on any product
3. Check top-right cart icon - number should increase
4. Click cart icon or go to `cart.php`
5. Verify product is in cart

### Step 3: Test from Product Page
1. Go to any product detail page
2. Select quantity
3. Click "Add to Cart" - stays on page, shows success message
4. OR click "Buy Now" - goes directly to cart

---

## Troubleshooting

### Problem: "Add to Cart" button does nothing

**Solution 1: Check PHP Session**
```php
// Add to top of config/database.php (if not already there)
session_start();
```

**Solution 2: Check Error Logs**
```bash
# Enable error display temporarily
# Edit config/database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Solution 3: Verify Database**
```sql
-- Check products exist
SELECT * FROM products WHERE is_active = 1;

-- Check cart table exists
DESCRIBE cart;
```

### Problem: Cart count not updating

**Check:**
1. Browser refresh (Ctrl+F5)
2. Session is active: `test-cart.php`
3. JavaScript console for errors (F12)

### Problem: "Insufficient stock" error

**Fix:**
```sql
-- Update stock quantities
UPDATE products SET stock_quantity = 100 WHERE stock_quantity < 10;
```

### Problem: CSRF token error

**Fix:**
```php
// Make sure this is in config/database.php
session_start();

// And in includes/functions.php
function generateCSRF() {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
```

---

## Manual Cart Test

### Test 1: Direct PHP Test
Create file: `quick-test.php`
```php
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "Session: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "<br>";

// Add product ID 1 to cart
try {
    addToCart(1, 1);
    echo "Success! Cart count: " . getCartCount() . "<br>";
    echo "<pre>";
    print_r($_SESSION['cart']);
    echo "</pre>";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo '<a href="cart.php">View Cart</a>';
?>
```

Access: `http://localhost/essence-luxe/quick-test.php`

### Test 2: JavaScript Console Test
Press F12, go to Console, paste:
```javascript
// Check if jQuery is loaded (not required but helpful)
console.log('Testing cart...');

// Check cart count element
console.log('Cart element:', document.querySelector('.cart-count'));

// Simulate form submission
const form = document.querySelector('form[action="add-to-cart.php"]');
if(form) {
    console.log('Form found:', form);
} else {
    console.log('No cart form found!');
}
```

---

## Database Verification

### Check Cart Data:
```sql
-- View all cart items
SELECT c.*, p.name, p.price 
FROM cart c 
JOIN products p ON c.product_id = p.id;

-- Check session carts
SELECT * FROM cart WHERE session_id IS NOT NULL;

-- Check user carts
SELECT * FROM cart WHERE user_id IS NOT NULL;
```

### Reset Cart (if needed):
```sql
-- Clear all cart data
TRUNCATE TABLE cart;

-- Or clear for specific user
DELETE FROM cart WHERE user_id = 1;
```

---

## Common Issues & Solutions

### Issue 1: Button clicks but nothing happens
**Cause:** JavaScript preventing form submission  
**Fix:** Check browser console (F12) for errors

### Issue 2: Page reloads but no success message
**Cause:** Flash messages not displaying  
**Fix:** Check `includes/header.php` has flash message code

### Issue 3: Cart shows 0 items but products added
**Cause:** Session cart vs database cart mismatch  
**Fix:** 
```php
// In functions.php, ensure loadCartFromDB() is called on login
function loadCartFromDB($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    $_SESSION['cart'] = [];
    foreach($items as $item) {
        $_SESSION['cart'][$item['product_id']] = $item['quantity'];
    }
}
```

### Issue 4: "Invalid security token" error
**Cause:** CSRF token not generated  
**Fix:** Make sure header.php calls `generateCSRF()` in forms

---

## Production Checklist

Before going live:

- [ ] Delete `test-cart.php`
- [ ] Delete `quick-test.php` (if created)
- [ ] Disable error display:
  ```php
  error_reporting(0);
  ini_set('display_errors', 0);
  ```
- [ ] Test cart with real products
- [ ] Test guest checkout
- [ ] Test logged-in checkout
- [ ] Test cart persistence (logout/login)
- [ ] Verify stock updates after order
- [ ] Test cart limits (max quantity)
- [ ] Test empty cart flow

---

## Success Indicators

✅ Cart icon shows correct count  
✅ Success message appears after adding  
✅ Products visible in cart.php  
✅ Can update quantities  
✅ Can remove items  
✅ Can proceed to checkout  
✅ Stock checks work properly  
✅ Cart persists after login  

---

## Need More Help?

### Check These Files:
1. `includes/functions.php` - Cart functions
2. `add-to-cart.php` - Add to cart handler
3. `cart.php` - Cart display
4. `config/database.php` - Session & DB config

### Debug Mode:
Add to top of `add-to-cart.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all POST data
file_put_contents('cart-debug.log', print_r($_POST, true) . "\n", FILE_APPEND);
```

### Contact Information:
- Check PHP error logs: `error_log` file
- Check Apache/Nginx error logs
- Enable PHP error logging in php.ini

---

**Last Updated:** November 2025  
**Version:** 1.1