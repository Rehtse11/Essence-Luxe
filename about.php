<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
$pageDescription = 'Learn about Essence Luxe and our passion for luxury fragrances';
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero" style="background-image: linear-gradient(rgba(44, 24, 16, 0.7), rgba(44, 24, 16, 0.7)), url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'1920\' height=\'600\'%3E%3Crect fill=\'%23f5f1eb\' width=\'1920\' height=\'600\'/%3E%3C/svg%3E');">
    <div class="container">
        <h1>Our Story</h1>
        <p>Crafting Exceptional Fragrances Since 1990</p>
    </div>
</div>

<section class="about-intro">
    <div class="container">
        <div class="intro-content">
            <div class="intro-text">
                <h2>Welcome to Essence Luxe</h2>
                <p>For over three decades, Essence Luxe has been at the forefront of luxury perfumery, creating exceptional fragrances that capture the essence of sophistication and elegance. Our journey began with a simple vision: to craft scents that tell stories and evoke emotions.</p>
                <p>Today, we're proud to be recognized as one of the world's premier perfume houses, blending traditional craftsmanship with modern innovation. Each fragrance in our collection is meticulously crafted using the finest ingredients sourced from around the globe.</p>
            </div>
            <div class="intro-image">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='400'%3E%3Crect fill='%23e8ddd4' width='600' height='400'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23d4af37' font-size='24' font-family='Arial'%3EPerfume Crafting%3C/text%3E%3C/svg%3E" alt="Perfume Crafting">
            </div>
        </div>
    </div>
</section>

<section class="values-section">
    <div class="container">
        <h2>Our Values</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <h3>Premium Quality</h3>
                <p>We source only the finest ingredients from around the world, ensuring every fragrance meets our exacting standards of excellence.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <h3>Artisan Craftsmanship</h3>
                <p>Each perfume is crafted by master perfumers with decades of experience, blending art and science to create unique scents.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"></path>
                        <line x1="16" y1="8" x2="2" y2="22"></line>
                        <line x1="17.5" y1="15" x2="9" y2="15"></line>
                    </svg>
                </div>
                <h3>Sustainability</h3>
                <p>We're committed to sustainable practices, from eco-friendly packaging to responsible ingredient sourcing.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>Customer First</h3>
                <p>Your satisfaction is our priority. We provide personalized service and expert guidance to help you find your perfect scent.</p>
            </div>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="stats-grid-large">
            <div class="stat-large">
                <h3>30+</h3>
                <p>Years of Excellence</p>
            </div>
            <div class="stat-large">
                <h3>200+</h3>
                <p>Unique Fragrances</p>
            </div>
            <div class="stat-large">
                <h3>50k+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat-large">
                <h3>25+</h3>
                <p>Countries Worldwide</p>
            </div>
        </div>
    </div>
</section>

<section class="team-section">
    <div class="container">
        <h2>Our Master Perfumers</h2>
        <p class="section-subtitle">Meet the artists behind our exceptional fragrances</p>
        
        <div class="team-grid">
            <div class="team-member">
                <div class="member-image">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Ccircle cx='150' cy='150' r='150' fill='%23e8ddd4'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23d4af37' font-size='80' font-family='Arial' dy='.3em'%3EM%3C/text%3E%3C/svg%3E" alt="Marie Laurent">
                </div>
                <h3>Marie Laurent</h3>
                <p class="member-role">Master Perfumer</p>
                <p>With over 25 years of experience, Marie specializes in floral and oriental compositions.</p>
            </div>
            
            <div class="team-member">
                <div class="member-image">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Ccircle cx='150' cy='150' r='150' fill='%23e8ddd4'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23d4af37' font-size='80' font-family='Arial' dy='.3em'%3EJ%3C/text%3E%3C/svg%3E" alt="James Chen">
                </div>
                <h3>James Chen</h3>
                <p class="member-role">Senior Perfumer</p>
                <p>James brings innovation to traditional perfumery with his unique approach to woody and citrus notes.</p>
            </div>
            
            <div class="team-member">
                <div class="member-image">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Ccircle cx='150' cy='150' r='150' fill='%23e8ddd4'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%23d4af37' font-size='80' font-family='Arial' dy='.3em'%3ES%3C/text%3E%3C/svg%3E" alt="Sofia Rossi">
                </div>
                <h3>Sofia Rossi</h3>
                <p class="member-role">Creative Director</p>
                <p>Sofia leads our creative vision, blending art and science to create unforgettable scent experiences.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Experience the Essence of Luxury</h2>
            <p>Discover our collection and find your signature scent today</p>
            <a href="shop.php" class="btn btn-primary btn-lg">Browse Collection</a>
        </div>
    </div>
</section>

<style>
.page-hero {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background-size: cover;
    background-position: center;
    color: white;
    position: relative;
}

.page-hero h1 {
    font-size: clamp(2.5rem, 6vw, 4rem);
    margin-bottom: 1rem;
    color: white;
}

.page-hero p {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
}

.about-intro {
    padding: 5rem 0;
}

.intro-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.intro-text h2 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
}

.intro-text p {
    font-size: 1.125rem;
    line-height: 1.8;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
}

.intro-image img {
    width: 100%;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
}

.values-section {
    padding: 5rem 0;
    background: var(--light-gray);
}

.values-section h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 3rem;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.value-card {
    background: white;
    padding: 2.5rem;
    border-radius: var(--border-radius-lg);
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.value-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.value-icon {
    width: 4rem;
    height: 4rem;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.value-icon svg {
    width: 2rem;
    height: 2rem;
    stroke: white;
}

.value-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.value-card p {
    color: var(--text-light);
    line-height: 1.7;
}

.stats-section {
    padding: 5rem 0;
    background: var(--gradient-hero);
}

.stats-grid-large {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 3rem;
}

.stat-large {
    text-align: center;
}

.stat-large h3 {
    font-size: 4rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.stat-large p {
    font-size: 1.125rem;
    color: var(--text-dark);
    margin: 0;
}

.team-section {
    padding: 5rem 0;
}

.team-section h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.section-subtitle {
    text-align: center;
    font-size: 1.125rem;
    color: var(--text-light);
    margin-bottom: 3rem;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 3rem;
}

.team-member {
    text-align: center;
}

.member-image {
    width: 200px;
    height: 200px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.member-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.team-member h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.member-role {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 1rem;
}

.team-member p {
    color: var(--text-light);
    line-height: 1.7;
}

.cta-section {
    padding: 5rem 0;
    background: var(--gradient-hero);
    color: white;
}

.cta-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.cta-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: white;
}

.cta-content p {
    font-size: 1.125rem;
    margin-bottom: 2rem;
    color: rgba(255, 255, 255, 0.9);
}

@media (max-width: 968px) {
    .intro-content {
        grid-template-columns: 1fr;
    }
    
    .values-grid,
    .team-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-large h3 {
        font-size: 3rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>