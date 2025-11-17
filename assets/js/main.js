/**
 * Essence Luxe - Main JavaScript
 */

// DOM Elements
const header = document.getElementById('header');
const navMenu = document.getElementById('navMenu');
const mobileToggle = document.getElementById('mobileToggle');
const searchBtn = document.getElementById('searchBtn');
const searchModal = document.getElementById('searchModal');
const searchClose = document.getElementById('searchClose');
const searchForm = document.getElementById('searchForm');
const searchInput = document.getElementById('searchInput');

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initializeHeader();
    initializeSearch();
    initializeMobileMenu();
    initializeAnimations();
});

// Header scroll effect
function initializeHeader() {
    if (!header) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

// Search functionality
function initializeSearch() {
    if (searchBtn && searchModal) {
        searchBtn.addEventListener('click', function() {
            searchModal.classList.add('open');
            if (searchInput) searchInput.focus();
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (searchClose) {
        searchClose.addEventListener('click', function() {
            closeSearchModal();
        });
    }
    
    // Close on outside click
    if (searchModal) {
        searchModal.addEventListener('click', function(e) {
            if (e.target === searchModal) {
                closeSearchModal();
            }
        });
    }
    
    // Submit on enter
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && searchForm) {
                searchForm.submit();
            }
        });
    }
}

function closeSearchModal() {
    if (searchModal) {
        searchModal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Mobile menu
function initializeMobileMenu() {
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('open');
            document.body.classList.toggle('menu-open');
        });
        
        // Close menu when clicking nav links
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('open');
                document.body.classList.remove('menu-open');
            });
        });
        
        // Close menu on outside click
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
                navMenu.classList.remove('open');
                document.body.classList.remove('menu-open');
            }
        });
    }
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const headerHeight = header ? header.offsetHeight : 80;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
});

// Animation on scroll
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements with fade-in class
    document.querySelectorAll('.fade-in, .product-card, .feature-card').forEach(el => {
        observer.observe(el);
    });
}

// Toast notification helper
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast show ${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <div class="toast-icon">${type === 'success' ? '✓' : '✗'}</div>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Form validation helper
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Image lazy loading fallback
document.querySelectorAll('img[loading="lazy"]').forEach(img => {
    if ('loading' in HTMLImageElement.prototype) {
        // Browser supports lazy loading
        return;
    }
    
    // Fallback for browsers that don't support lazy loading
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                observer.unobserve(img);
            }
        });
    });
    
    observer.observe(img);
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    // ESC key closes modals
    if (e.key === 'Escape') {
        closeSearchModal();
    }
});

// Prevent multiple form submissions
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Processing...</span>';
            
            // Re-enable after 3 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.dataset.originalText || 'Submit';
            }, 3000);
        }
    });
});

// Store original button text
document.querySelectorAll('button[type="submit"]').forEach(btn => {
    btn.dataset.originalText = btn.innerHTML;
});

// Product quantity controls (if present on page)
document.querySelectorAll('.qty-increase').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.previousElementSibling;
        if (input && input.type === 'number') {
            input.value = parseInt(input.value) + 1;
            input.dispatchEvent(new Event('change'));
        }
    });
});

document.querySelectorAll('.qty-decrease').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.nextElementSibling;
        if (input && input.type === 'number' && parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            input.dispatchEvent(new Event('change'));
        }
    });
});

// Price range slider (if present)
const priceSlider = document.getElementById('priceRange');
if (priceSlider) {
    priceSlider.addEventListener('input', function() {
        const output = document.getElementById('priceValue');
        if (output) {
            output.textContent = '$' + this.value;
        }
    });
}

// Newsletter form submission
const newsletterForms = document.querySelectorAll('.newsletter-form');
newsletterForms.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // You would normally send this to the server
        showToast('Thank you for subscribing!', 'success');
        this.reset();
    });
});

// Initialize tooltips (if any)
document.querySelectorAll('[title]').forEach(el => {
    el.setAttribute('data-title', el.getAttribute('title'));
});

// Add to cart animation
function addToCartAnimation(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '✓ Added';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

// Export functions for use in other scripts
window.showToast = showToast;
window.validateForm = validateForm;
window.addToCartAnimation = addToCartAnimation;