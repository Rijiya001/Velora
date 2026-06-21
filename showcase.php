<?php
$page_title = "Showcase";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Capture and sanitize filters
$selected_collection = isset($_GET['collection']) ? intval($_GET['collection']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Base query construction
$query_string = "SELECT p.*, c.name as collection_name FROM products p 
                 LEFT JOIN collections c ON p.collection_id = c.id 
                 WHERE p.status = 'active'";
$params = [];
$types = "";

if ($selected_collection > 0) {
    $query_string .= " AND p.collection_id = ?";
    $params[] = $selected_collection;
    $types .= "i";
}

if (!empty($search_query)) {
    $query_string .= " AND (p.name LIKE ? OR p.product_code LIKE ? OR p.material LIKE ? OR p.category LIKE ?)";
    $like_search = "%" . $search_query . "%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $types .= "ssss";
}

if (!empty($selected_category)) {
    $query_string .= " AND p.category = ?";
    $params[] = $selected_category;
    $types .= "s";
}

$query_string .= " ORDER BY p.display_order ASC, p.id DESC";

// Prepare and execute statement
$stmt = mysqli_prepare($con, $query_string);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $products_result = mysqli_stmt_get_result($stmt);
} else {
    die("Database query error.");
}
?>

<section class="section-padding" style="background-color: var(--color-soft-ivory); text-align: center;">
    <div class="container">
        <span class="hero-tag">Design Catalog</span>
        <h1 style="margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.1em;">The Showcase Archive</h1>
        <p class="section-subtitle" style="margin-bottom: 0;">View detailed specifications of our gold and silver archive creations. Contact our concierge to request private viewings.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        
        <!-- Search and Filter Bar -->
        <div style="background-color: var(--color-soft-ivory); border: 1px solid var(--color-border-gray); padding: 25px; border-radius: 4px; margin-bottom: 50px;">
            <form action="" method="get" style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                
                <div style="display: flex; gap: 15px; flex-grow: 1; min-width: 300px;">
                    <input type="text" name="search" placeholder="Search by name, code, material..." class="form-control" value="<?php echo xss_clean($search_query); ?>" style="background-color: var(--color-pure-white); margin-bottom:0;">
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <select name="collection" class="form-control" style="background-color: var(--color-pure-white); width: 200px; margin-bottom:0;">
                        <option value="">All Collections</option>
                        <?php
                        $cols = mysqli_query($con, "SELECT id, name FROM collections ORDER BY display_order ASC");
                        while ($col = mysqli_fetch_assoc($cols)) {
                            $sel = ($selected_collection === intval($col['id'])) ? 'selected' : '';
                            echo '<option value="' . $col['id'] . '" ' . $sel . '>' . xss_clean($col['name']) . '</option>';
                        }
                        ?>
                    </select>

                    <select name="category" class="form-control" style="background-color: var(--color-pure-white); width: 200px; margin-bottom:0;">
                        <option value="">All Categories</option>
                        <?php
                        $cats = mysqli_query($con, "SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category ASC");
                        while ($cat = mysqli_fetch_assoc($cats)) {
                            $sel = ($selected_category === $cat['category']) ? 'selected' : '';
                            echo '<option value="' . xss_clean($cat['category']) . '" ' . $sel . '>' . xss_clean($cat['category']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-sm" style="padding: 12px 25px;">Filter</button>
            </form>
        </div>

        <!-- Product Grid -->
        <div class="products-grid">
            <?php
            if ($products_result && mysqli_num_rows($products_result) > 0):
                while ($prod = mysqli_fetch_assoc($products_result)):
                    // Check if user has this item in wishlist
                    $is_wishlisted = false;
                    if (isset($_SESSION['user_id'])) {
                        $wl_check = mysqli_prepare($con, "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1");
                        if ($wl_check) {
                            mysqli_stmt_bind_param($wl_check, "ii", $_SESSION['user_id'], $prod['id']);
                            mysqli_stmt_execute($wl_check);
                            mysqli_stmt_store_result($wl_check);
                            if (mysqli_stmt_num_rows($wl_check) > 0) {
                                $is_wishlisted = true;
                            }
                            mysqli_stmt_close($wl_check);
                        }
                    }
            ?>
                <div class="product-card">
                    <?php if ($prod['is_new_arrival']): ?>
                        <div class="product-badge">New Arrival</div>
                    <?php elseif ($prod['is_trending']): ?>
                        <div class="product-badge">Trending</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="product-wishlist <?php echo $is_wishlisted ? 'active' : ''; ?>" onclick="handleWishlistToggle(this, <?php echo $prod['id']; ?>)" aria-label="Wishlist"></button>
                    <?php endif; ?>

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
                            <a href="product.php?code=<?php echo urlencode($prod['product_code']); ?>" class="btn btn-secondary btn-sm" style="display:block;">View Details</a>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--color-warm-gray); padding: 50px 0;">No matching jewelry designs found in the showcase.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<script>
// AJAX Wishlist toggle handler
function handleWishlistToggle(button, productId) {
    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('api/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'added') {
            button.classList.add('active');
        } else if (data.status === 'removed') {
            button.classList.remove('active');
        }
    })
    .catch(err => console.error("Wishlist Error:", err));
}
</script>

<?php
mysqli_stmt_close($stmt);
include 'includes/footer.php';
?>
