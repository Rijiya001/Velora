<?php
$page_title = "Home";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Fetch dynamic homepage details from website settings
$hero_title = get_setting($con, 'hero_title', 'Give your looks<br>A New Style');
$hero_subtitle = get_setting($con, 'hero_subtitle', '"Make jewellery contact before eyes contact"');
$hero_image = get_setting($con, 'hero_image', 'designs/design3.jpg');
$about_story = get_setting($con, 'about_story', '');
?>

<!-- Hero Section -->
<section class="hero-wrapper">
    <div class="container">
        <div class="row">
            <div class="col col-text">
                <div class="hero-text">
                    <span class="hero-tag">Signature Showcase</span>
                    <h1><?php echo $hero_title; // HTML tags allowed for styling br ?></h1>
                    <p><?php echo xss_clean($hero_subtitle); ?></p>
                    <a href="showcase.php" class="btn">Explore Showcase</a>
                </div>
            </div>
            <div class="col col-img">
                <div class="hero-image">
                    <img src="<?php echo xss_clean($hero_image); ?>" alt="Velora Fine Jewelry Signature Series">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Brand Campaign Section -->
<section class="section-padding alt-bg">
    <div class="container">
        <div class="row promo-row">
            <div class="col col-img">
                <div class="promo-image">
                    <img src="designs/design4.jpg" alt="Velora Craftsmanship Atelier">
                </div>
            </div>
            <div class="col col-text">
                <div class="promo-text">
                    <h2>Jewelry is like ice cream.</h2>
                    <p><em>"There is always room for more."</em> At Velora, we believe that jewelry is more than an ornament—it is a silent statement of beauty, a legacy of craft, and a canvas of self-expression.</p>
                    <a href="about.php" class="btn btn-secondary">Our Legacy Story</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Collections Slider/Grid -->
<section class="section-padding">
    <div class="container">
        <h2>Curated Collections</h2>
        <p class="section-subtitle">Exquisite archives crafted with distinct inspirations.</p>
        
        <div class="products-grid" style="grid-template-columns: repeat(2, 1fr);">
            <?php
            $collections_query = mysqli_query($con, "SELECT * FROM collections ORDER BY display_order ASC, id ASC LIMIT 2");
            while ($col_data = mysqli_fetch_assoc($collections_query)):
            ?>
            <div class="product-card" style="border-radius: 4px;">
                <div class="product-image-container" style="padding-bottom: 70%;">
                    <img src="<?php echo xss_clean($col_data['banner_image']); ?>" alt="<?php echo xss_clean($col_data['name']); ?>">
                </div>
                <div class="product-info" style="text-align: left; align-items: flex-start;">
                    <h3 style="font-family: var(--font-display); font-size: 1.4rem; margin-bottom: 10px;"><?php echo xss_clean($col_data['name']); ?></h3>
                    <p style="font-size: 0.85rem; margin-bottom: 20px; line-height: 1.6;"><?php echo xss_clean($col_data['description']); ?></p>
                    <a href="showcase.php?collection=<?php echo $col_data['id']; ?>" class="btn btn-sm btn-secondary">Explore Archive</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Jewelry Products Section -->
<section class="section-padding alt-bg">
    <div class="container">
        <h2>Featured Showcase</h2>
        <p class="section-subtitle">Select masterpieces currently trending in our atelier archives.</p>
        
        <div class="products-grid">
            <?php
            $products_query = mysqli_query($con, "SELECT * FROM products WHERE is_featured = 1 AND status = 'active' ORDER BY display_order ASC, id DESC LIMIT 3");
            if (mysqli_num_rows($products_query) > 0):
                while ($prod = mysqli_fetch_assoc($products_query)):
            ?>
                <div class="product-card">
                    <div class="product-badge">Featured</div>
                    <div class="product-image-container">
                        <img src="<?php echo xss_clean($prod['main_image']); ?>" alt="<?php echo xss_clean($prod['name']); ?>">
                    </div>
                    <div class="product-info">
                        <div>
                            <div class="product-material"><?php echo xss_clean($prod['material']); ?></div>
                            <h3 class="product-title"><?php echo xss_clean($prod['name']); ?></h3>
                            <ul class="product-specs">
                                <li>Weight: <span><?php echo xss_clean($prod['weight']); ?></span></li>
                                <li>Code: <span><?php echo xss_clean($prod['product_code']); ?></span></li>
                            </ul>
                        </div>
                        <div style="margin-top: 15px;">
                            <a href="product.php?code=<?php echo urlencode($prod['product_code']); ?>" class="btn btn-secondary btn-sm" style="display:block;">View Masterpiece</a>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--color-warm-gray);">No featured jewelry added yet.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
