<?php
$page_title = "Our Story";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Fetch dynamic about details
$story = get_setting($con, 'about_story', 'Velora is one of the most trusted fashion jewelry houses located in Huprachaur, Hetauda. For over 20 years, our designers have blended classical Nepalese heritage with contemporary aesthetics, offering an unmatched experience in luxury-inspired imitation jewelry.');
$mission = get_setting($con, 'about_mission', 'To craft beautifully designed, fashion-forward imitation jewelry that embodies quiet luxury, contemporary elegance, and exceptional quality at an accessible price.');
$val_vision = get_setting($con, 'about_vision', 'To establish Velora as the premier destination for luxury-inspired fashion jewelry, celebrated globally for unique design and accessible elegance.');
?>

<section class="section-padding" style="background-color: var(--color-soft-ivory);">
    <div class="container" style="max-width: 900px; text-align: center;">
        <span class="hero-tag" style="margin-bottom: 20px;">Est. 2003</span>
        <h1 style="margin-bottom: 40px; text-transform: uppercase; letter-spacing: 0.1em;">The Velora Heritage</h1>
        
        <p style="font-family: var(--font-display); font-size: 1.4rem; line-height: 1.8; color: var(--color-deep-charcoal); margin-bottom: 50px; font-style: italic;">
            "Jewelry is a signature piece that tells a story, marks a milestone, and endures across generations."
        </p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row promo-row">
            <div class="col col-text" style="padding-right: 60px;">
                <h2 style="text-align: left; margin-bottom: 25px; padding-bottom: 0;">Our Story</h2>
                <p style="font-size: 1rem; margin-bottom: 20px; text-align: justify;"><?php echo nl2br(xss_clean($story)); ?></p>
            </div>
            <div class="col col-img">
                <div class="promo-image">
                    <img src="designs/design4.jpg" alt="Velora Atelier Craftsman">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding alt-bg">
    <div class="container">
        <div class="row" style="gap: 40px;">
            <div class="col" style="background-color: var(--color-pure-white); padding: 40px; border: 1px solid var(--color-border-gray); border-radius: 4px; text-align: center;">
                <h3 style="font-family: var(--font-display); font-size: 1.6rem; color: var(--color-rose-gold); margin-bottom: 20px;">Our Mission</h3>
                <p style="font-size: 0.95rem; line-height: 1.7;"><?php echo nl2br(xss_clean($mission)); ?></p>
            </div>
            <div class="col" style="background-color: var(--color-pure-white); padding: 40px; border: 1px solid var(--color-border-gray); border-radius: 4px; text-align: center;">
                <h3 style="font-family: var(--font-display); font-size: 1.6rem; color: var(--color-rose-gold); margin-bottom: 20px;">Our Vision</h3>
                <p style="font-size: 0.95rem; line-height: 1.7;"><?php echo nl2br(xss_clean($val_vision)); ?></p>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
