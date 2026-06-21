<?php
$page_title = "Gallery";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="section-padding" style="background-color: var(--color-soft-ivory); text-align: center;">
    <div class="container">
        <span class="hero-tag">Visual Archives</span>
        <h1 style="margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.1em;">The Velora Gallery</h1>
        <p class="section-subtitle" style="margin-bottom: 0;">Step inside our atelier. Discover close-ups of our crafting process, signature catalog details, and campaigns.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="gallery-grid">
            <?php
            $gallery_query = mysqli_query($con, "SELECT * FROM gallery ORDER BY id DESC");
            if (mysqli_num_rows($gallery_query) > 0):
                while ($item = mysqli_fetch_assoc($gallery_query)):
            ?>
                <div class="gallery-card" onclick="openLightbox('<?php echo xss_clean($item['image_path']); ?>', '<?php echo xss_clean($item['title']); ?>', '<?php echo xss_clean($item['description']); ?>')">
                    <img src="<?php echo xss_clean($item['image_path']); ?>" alt="<?php echo xss_clean($item['title']); ?>">
                </div>
            <?php 
                endwhile; 
            else:
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--color-warm-gray); padding: 50px 0;">No gallery items added yet.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.9); z-index:2000; align-items:center; justify-content:center; flex-direction:column; padding:20px; backdrop-filter:blur(4px);">
    <button onclick="closeLightbox()" style="position:absolute; top:25px; right:35px; background:none; border:none; color:white; font-size:2.5rem; cursor:pointer;">&times;</button>
    <img id="lightbox-img" src="" style="max-width:90%; max-height:80%; object-fit:contain; border-radius:2px; margin-bottom:20px;">
    <h3 id="lightbox-title" style="color:white; margin-bottom:5px;"></h3>
    <p id="lightbox-desc" style="color:#AAA; font-size:0.9rem;"></p>
</div>

<script>
function openLightbox(img, title, desc) {
    const modal = document.getElementById('lightbox-modal');
    document.getElementById('lightbox-img').src = img;
    document.getElementById('lightbox-title').textContent = title;
    document.getElementById('lightbox-desc').textContent = desc;
    modal.style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox-modal').style.display = 'none';
}
</script>

<?php
include 'includes/footer.php';
?>
