<?php
$page_title = "Collections";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="section-padding" style="background-color: var(--color-soft-ivory); text-align: center;">
    <div class="container">
        <span class="hero-tag">Exquisite Themes</span>
        <h1 style="margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.1em;">The Velora Collections</h1>
        <p class="section-subtitle" style="margin-bottom: 0;">Explore our curated archives of luxury-inspired imitation jewelry, each collection designed with unique artistic inspirations.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="products-grid" style="grid-template-columns: repeat(2, 1fr); gap: 50px;">
            <?php
            $collections_query = mysqli_query($con, "SELECT * FROM collections ORDER BY display_order ASC, id ASC");
            if (mysqli_num_rows($collections_query) > 0):
                while ($col_data = mysqli_fetch_assoc($collections_query)):
            ?>
                <div class="product-card" style="border-radius: 4px;">
                    <div class="product-image-container" style="padding-bottom: 70%;">
                        <img src="<?php echo xss_clean($col_data['banner_image']); ?>" alt="<?php echo xss_clean($col_data['name']); ?>">
                    </div>
                    <div class="product-info" style="text-align: left; align-items: flex-start; padding: 35px;">
                        <h3 style="font-family: var(--font-display); font-size: 1.6rem; margin-bottom: 12px; color: var(--color-deep-charcoal);"><?php echo xss_clean($col_data['name']); ?></h3>
                        <p style="font-size: 0.9rem; margin-bottom: 25px; line-height: 1.7;"><?php echo xss_clean($col_data['description']); ?></p>
                        <a href="showcase.php?collection=<?php echo $col_data['id']; ?>" class="btn">Explore Showcase</a>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--color-warm-gray); padding: 50px 0;">No collections added yet.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
