<?php
$page_title = "Home";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Fetch the latest gold and silver rates for display or calculations
$rate_query = "SELECT * FROM rate ORDER BY Id DESC LIMIT 1";
$rate_result = mysqli_query($con, $rate_query);
$latest_rates = mysqli_fetch_assoc($rate_result);

$gold_rate = $latest_rates ? $latest_rates['Gold'] : '98,000';
$silver_rate = $latest_rates ? $latest_rates['Silver'] : '1,200';
?>

<!-- Hero Section -->
<section class="hero-wrapper">
    <div class="container">
        <div class="row">
            <div class="col col-text">
                <div class="hero-text">
                    <h1>Give your looks<br>A New Style</h1>
                    <p>"Make jewellery contact before eyes contact"</p>
                    <a href="#gallery" class="btn">Discover Collections</a>
                </div>
            </div>
            <div class="col col-img">
                <div class="hero-image">
                    <img src="designs/design3.jpg" alt="Velora Signature Ring Design">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Brand Campaign Section -->
<section class="promo-section">
    <div class="container">
        <div class="row">
            <div class="col col-img">
                <div class="promo-image">
                    <img src="designs/design4.jpg" alt="Velora Craftsmanship">
                </div>
            </div>
            <div class="col col-text">
                <div class="promo-text">
                    <h2>Jewelry is like ice cream.</h2>
                    <p><em>"There is always room for more."</em> At Velora, we believe that jewelry is more than an accessory—it is an extension of your persona, a silent statement of beauty, and a canvas of self-expression.</p>
                    <a href="#gallery" class="btn btn-secondary">Explore Designs</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section" id="about">
    <div class="container">
        <div class="about-content">
            <h2>Our Story</h2>
            <p>Velora (formerly Shakya Jewellers) is one of the oldest and most trusted jewelry houses located in Huprachaur, Hetauda. For over 20 years, our master artisans have blended classical Nepalese heritage with contemporary designs, offering an unmatched experience in bespoke gold and silver creations.</p>
            <div class="about-logo">Velora</div>
        </div>
    </div>
</section>

<!-- Gallery Catalog Section -->
<section class="gallery-section" id="gallery">
    <div class="container">
        <h2>The Signature Collection</h2>
        <p style="text-align: center; max-width: 600px; margin: -20px auto 40px; color: var(--color-warm-gray);">Explore our curated collection of luxury jewelry, hand-crafted with conflict-free materials and certified metals.</p>
        
        <div class="products-grid">
            
            <!-- Card 1: Necklace -->
            <div class="product-card">
                <div class="product-badge">Best Seller</div>
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/necklace1.jpg" alt="The Velora Heirloom Necklace">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">24K Gold</div>
                        <h3 class="product-title">Heirloom Necklace</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>20gm</span></li>
                            <li>Code: <span>#01</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2301&item=Necklace" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 2: Ring -->
            <div class="product-card">
                <div class="product-badge">New in</div>
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/rings1.jpg" alt="Velora Diamond Band">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">23K Gold</div>
                        <h3 class="product-title">Elegance Ring Band</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>20gm</span></li>
                            <li>Code: <span>#02</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2302&item=Rings" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 3: Anklet -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/anklet1.jpg" alt="Velora Silver Anklet">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">Pure Silver</div>
                        <h3 class="product-title">Classic Silver Anklet</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>20gm</span></li>
                            <li>Code: <span>#03</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2303&item=Anklets" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 4: Mangalsutra -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/mangalsutra1.jpg" alt="Velora Mangalsutra">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">24K Gold</div>
                        <h3 class="product-title">Sovereign Mangalsutra</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>20gm</span></li>
                            <li>Code: <span>#04</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2304&item=Mangalsutra" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 5: Naugedi -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/naugedi1.jpg" alt="Velora Traditional Naugedi">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">24K Gold</div>
                        <h3 class="product-title">Traditional Naugedi</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>11.11gm</span></li>
                            <li>Code: <span>#05</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2305&item=Naugedi" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 6: Chandrama -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/chandrama1.jpg" alt="Velora Moon Chandrama">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">24K Gold</div>
                        <h3 class="product-title">Lunar Chandrama</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>15gm</span></li>
                            <li>Code: <span>#06</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2306&item=Chandrama" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 7: Bangle -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/bangles1.jpg" alt="Velora Gold Bangle">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">24K Gold</div>
                        <h3 class="product-title">Sovereign Bangles</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>20gm</span></li>
                            <li>Code: <span>#07</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2307&item=Bangles" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 8: Bracelet -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/bracelets1.jpg" alt="Velora Bracelet">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">22K Gold</div>
                        <h3 class="product-title">Classic Gold Bracelet</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>22gm</span></li>
                            <li>Code: <span>#08</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2308&item=Bracelets" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

            <!-- Card 9: Ear Rings -->
            <div class="product-card">
                <button class="product-wishlist" aria-label="Add to wishlist"></button>
                <div class="product-image-container">
                    <img src="designs/ear rings1.jpg" alt="Velora Earrings">
                </div>
                <div class="product-info">
                    <div>
                        <div class="product-material">24K Gold</div>
                        <h3 class="product-title">Elegance Stud Earrings</h3>
                        <ul class="product-specs">
                            <li>Weight: <span>20gm</span></li>
                            <li>Code: <span>#09</span></li>
                        </ul>
                    </div>
                    <div>
                        <div class="product-price">Custom Quote</div>
                        <a href="form.php?code=%2309&item=Ear Rings" class="btn">Inquire Now</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section" id="contact">
    <div class="container">
        <h2>Contact Us</h2>
        <div class="contact-box">
            <div class="contact-info-pane">
                <h3>Get in Touch</h3>
                <ul class="contact-details">
                    <li><strong>Address:</strong>Huprachaur, Hetauda-4, Makwanpur, Nepal</li>
                    <li><strong>Box:</strong>P.O. Box 25963</li>
                    <li><strong>Tel:</strong>+977 057 91827</li>
                    <li><strong>Fax:</strong>+977 057 91827</li>
                    <li><strong>Email:</strong>concierge@velorajewelry.com</li>
                </ul>
                <p style="font-size: 0.85rem; color: var(--color-warm-gray);">We welcome custom design orders. Please upload your design details using our online order registration portal.</p>
                <div style="margin-top: 30px;">
                    <a href="form.php" class="btn">Register Custom Order</a>
                </div>
            </div>
            <div class="contact-map-pane">
                <img src="designs/map.png" alt="Velora Location Map">
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>