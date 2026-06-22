<?php
$page_title = "Manage Products";
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Capture action type
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Handle Archive Action
if ($action === 'archive' && $product_id > 0) {
    $stmt = mysqli_prepare($con, "UPDATE products SET status = 'archived' WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Masterpiece archived successfully.";
            log_activity($con, $_SESSION['email'], $_SESSION['role'], "Archived product ID $product_id");
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: products.php");
    exit();
}

// 2. Handle Restore Action
if ($action === 'restore' && $product_id > 0) {
    $stmt = mysqli_prepare($con, "UPDATE products SET status = 'active' WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Masterpiece restored successfully.";
            log_activity($con, $_SESSION['email'], $_SESSION['role'], "Restored product ID $product_id");
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: products.php");
    exit();
}

// 3. Handle Delete Action
if ($action === 'delete' && $product_id > 0) {
    // Check if superadmin (Only superadmin can delete completely)
    if ($_SESSION['role'] !== 'superadmin') {
        $error = "Only Super Administrators can permanently delete masterpieces.";
    } else {
        $stmt = mysqli_prepare($con, "DELETE FROM products WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Masterpiece permanently deleted.";
                log_activity($con, $_SESSION['email'], $_SESSION['role'], "Deleted product ID $product_id");
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: products.php");
        exit();
    }
}

// 4. Handle Create & Edit POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    verify_csrf_token($_POST['csrf_token']);
    
    $name = trim($_POST['name']);
    $collection_id = !empty($_POST['collection_id']) ? intval($_POST['collection_id']) : null;
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $material = trim($_POST['material']);
    $weight = trim($_POST['weight']);
    $product_code = trim($_POST['product_code']);
    
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $display_order = intval($_POST['display_order']);
    
    $main_image_path = isset($_POST['existing_image']) ? $_POST['existing_image'] : 'designs/necklace1.jpg';

    // Handle File Upload if present
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['main_image']['tmp_name'];
        $file_name = basename($_FILES['main_image']['name']);
        // Sanitize file name to prevent directory traversal
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
        
        // Define directory relative to workspace root
        $target_dir = dirname(__DIR__) . "/assets/uploads/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $timestamp = time();
        $dest_path = $target_dir . $timestamp . "_" . $file_name;
        if (move_uploaded_file($file_tmp, $dest_path)) {
            // Path relative to root of website for rendering in src attributes
            $main_image_path = "assets/uploads/products/" . $timestamp . "_" . $file_name;
        } else {
            $error = "Failed to upload image.";
        }
    }

    // Handle Additional Images
    $additional_images = [];
    if (isset($_FILES['additional_images'])) {
        $total_files = count($_FILES['additional_images']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['additional_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['additional_images']['tmp_name'][$i];
                $file_name = basename($_FILES['additional_images']['name'][$i]);
                $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
                
                $target_dir = dirname(__DIR__) . "/assets/uploads/products/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $timestamp = time() . "_" . $i;
                $dest_path = $target_dir . $timestamp . "_" . $file_name;
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    $additional_images[] = "assets/uploads/products/" . $timestamp . "_" . $file_name;
                }
            }
        }
    }

    if (empty($name) || empty($category) || empty($material) || empty($product_code)) {
        $error = "Please fill in all required fields.";
    } elseif (empty($error)) {
        if ($action === 'add') {
            // Verify unique product code
            $code_check = mysqli_prepare($con, "SELECT id FROM products WHERE product_code = ? LIMIT 1");
            mysqli_stmt_bind_param($code_check, "s", $product_code);
            mysqli_stmt_execute($code_check);
            mysqli_stmt_store_result($code_check);
            
            if (mysqli_stmt_num_rows($code_check) > 0) {
                $error = "Product code is already in use.";
                mysqli_stmt_close($code_check);
            } else {
                mysqli_stmt_close($code_check);
                
                $stmt = mysqli_prepare($con, "INSERT INTO products (name, collection_id, category, description, material, weight, product_code, is_featured, is_trending, is_new_arrival, display_order, main_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sisssssiiiis", $name, $collection_id, $category, $description, $material, $weight, $product_code, $is_featured, $is_trending, $is_new_arrival, $display_order, $main_image_path);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $new_product_id = mysqli_insert_id($con);
                        foreach ($additional_images as $img_path) {
                            $img_stmt = mysqli_prepare($con, "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                            mysqli_stmt_bind_param($img_stmt, "is", $new_product_id, $img_path);
                            mysqli_stmt_execute($img_stmt);
                            mysqli_stmt_close($img_stmt);
                        }
                        $success = "Masterpiece catalogued successfully.";
                        log_activity($con, $_SESSION['email'], $_SESSION['role'], "Created product code $product_code");
                        header("Location: products.php");
                        exit();
                    } else {
                        $error = "Database insertion error: " . mysqli_error($con);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        } else {
            // Action edit
            // Verify unique code excluding current ID
            $code_check = mysqli_prepare($con, "SELECT id FROM products WHERE product_code = ? AND id != ? LIMIT 1");
            mysqli_stmt_bind_param($code_check, "si", $product_code, $product_id);
            mysqli_stmt_execute($code_check);
            mysqli_stmt_store_result($code_check);
            
            if (mysqli_stmt_num_rows($code_check) > 0) {
                $error = "Product code is already in use by another masterpiece.";
                mysqli_stmt_close($code_check);
            } else {
                mysqli_stmt_close($code_check);
                
                $stmt = mysqli_prepare($con, "UPDATE products SET name = ?, collection_id = ?, category = ?, description = ?, material = ?, weight = ?, product_code = ?, is_featured = ?, is_trending = ?, is_new_arrival = ?, display_order = ?, main_image = ? WHERE id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sisssssiiiisi", $name, $collection_id, $category, $description, $material, $weight, $product_code, $is_featured, $is_trending, $is_new_arrival, $display_order, $main_image_path, $product_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        foreach ($additional_images as $img_path) {
                            $img_stmt = mysqli_prepare($con, "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                            mysqli_stmt_bind_param($img_stmt, "is", $product_id, $img_path);
                            mysqli_stmt_execute($img_stmt);
                            mysqli_stmt_close($img_stmt);
                        }
                        $success = "Masterpiece updated successfully.";
                        log_activity($con, $_SESSION['email'], $_SESSION['role'], "Updated product ID $product_id");
                        header("Location: products.php");
                        exit();
                    } else {
                        $error = "Database update error: " . mysqli_error($con);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1>Jewelry Masterpieces</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Catalog control center.</p>
            </div>
            
            <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-sm">Add Masterpiece</a>
            <?php else: ?>
                <a href="products.php" class="btn btn-sm btn-secondary">← Back to List</a>
            <?php endif; ?>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <!-- List View -->
        <?php if ($action === 'list'): ?>
            <div class="table-card">
                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Collection</th>
                            <th>Material</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $prods = mysqli_query($con, "SELECT p.*, c.name as col_name FROM products p 
                                                     LEFT JOIN collections c ON p.collection_id = c.id 
                                                     ORDER BY p.status ASC, p.display_order ASC, p.id DESC");
                        if (mysqli_num_rows($prods) > 0):
                            while ($row = mysqli_fetch_assoc($prods)):
                        ?>
                            <tr>
                                <td><img src="../<?php echo xss_clean($row['main_image']); ?>" alt="product"></td>
                                <td><code><?php echo xss_clean($row['product_code']); ?></code></td>
                                <td style="font-weight:600;"><?php echo xss_clean($row['name']); ?></td>
                                <td><?php echo xss_clean($row['col_name'] ?? 'General'); ?></td>
                                <td><?php echo xss_clean($row['material']); ?></td>
                                <td><?php echo $row['is_featured'] ? '★ Yes' : 'No'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status'] === 'active' ? 'active' : 'suspended'; ?>">
                                        <?php echo xss_clean($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="action-edit">Edit</a>
                                        <?php if ($row['status'] === 'active'): ?>
                                            <a href="?action=archive&id=<?php echo $row['id']; ?>" style="color:var(--color-warm-gray);">Archive</a>
                                        <?php else: ?>
                                            <a href="?action=restore&id=<?php echo $row['id']; ?>" style="color:var(--color-success);">Restore</a>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['role'] === 'superadmin'): ?>
                                            <a href="?action=delete&id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Permanently delete this design archive?')">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                            echo '<tr><td colspan="8" style="text-align:center; color:var(--color-warm-gray);">No jewelry catalogued yet.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

        <!-- Add or Edit View -->
        <?php elseif ($action === 'add' || $action === 'edit'): 
            $form_title = "Add New Masterpiece";
            $p_name = $p_col = $p_cat = $p_desc = $p_mat = $p_weight = $p_code = $p_img = "";
            $p_feat = $p_trend = $p_new = $p_order = 0;
            
            if ($action === 'edit' && $product_id > 0) {
                $form_title = "Edit Masterpiece Details";
                $edit_stmt = mysqli_prepare($con, "SELECT name, collection_id, category, description, material, weight, product_code, is_featured, is_trending, is_new_arrival, display_order, main_image FROM products WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($edit_stmt, "i", $product_id);
                mysqli_stmt_execute($edit_stmt);
                mysqli_stmt_bind_result($edit_stmt, $p_name, $p_col, $p_cat, $p_desc, $p_mat, $p_weight, $p_code, $p_feat, $p_trend, $p_new, $p_order, $p_img);
                mysqli_stmt_fetch($edit_stmt);
                mysqli_stmt_close($edit_stmt);
            }
        ?>
            <div class="table-card" style="max-width:800px;">
                <h3 style="margin-bottom:30px;"><?php echo xss_clean($form_title); ?></h3>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="existing_image" value="<?php echo xss_clean($p_img); ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col col-6" style="flex:1;">
                            <div class="form-group">
                                <label for="name">Product Name *</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo xss_clean($p_name); ?>" required>
                            </div>
                        </div>
                        <div class="col col-6" style="flex:1;">
                            <div class="form-group">
                                <label for="product_code">Product Catalog Code *</label>
                                <input type="text" id="product_code" name="product_code" class="form-control" placeholder="e.g. #09" value="<?php echo xss_clean($p_code); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6" style="flex:1;">
                            <div class="form-group">
                                <label for="collection_id">Collection</label>
                                <select id="collection_id" name="collection_id" class="form-control">
                                    <option value="">No Collection (General)</option>
                                    <?php
                                    $cols = mysqli_query($con, "SELECT id, name FROM collections ORDER BY display_order ASC");
                                    while ($col = mysqli_fetch_assoc($cols)) {
                                        $sel = ($p_col === intval($col['id'])) ? 'selected' : '';
                                        echo '<option value="' . $col['id'] . '" ' . $sel . '>' . xss_clean($col['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col col-6" style="flex:1;">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <input type="text" id="category" name="category" class="form-control" placeholder="e.g. Rings, Necklaces, Anklets" value="<?php echo xss_clean($p_cat); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6" style="flex:1;">
                            <div class="form-group">
                                <label for="material">Material *</label>
                                <input type="text" id="material" name="material" class="form-control" placeholder="e.g. 24K Gold, Pure Silver" value="<?php echo xss_clean($p_mat); ?>" required>
                            </div>
                        </div>
                        <div class="col col-6" style="flex:1;">
                            <div class="form-group">
                                <label for="weight">Weight / Purity Details</label>
                                <input type="text" id="weight" name="weight" class="form-control" placeholder="e.g. 20gm, 11.11gm" value="<?php echo xss_clean($p_weight); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Detailed Description</label>
                        <textarea id="description" name="description" class="form-control"><?php echo xss_clean($p_desc); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="main_image">Main Campaign Image</label>
                        <input type="file" id="main_image" name="main_image" class="form-control">
                        <?php if (!empty($p_img)): ?>
                            <div style="margin-top:10px;">
                                <small style="color:var(--color-warm-gray); display:block; margin-bottom:5px;">Current Image:</small>
                                <img src="../<?php echo xss_clean($p_img); ?>" style="width:100px; height:100px; object-fit:cover; border:1px solid var(--color-border-gray); border-radius:2px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="additional_images">Additional Showcase Images (Multiple)</label>
                        <input type="file" id="additional_images" name="additional_images[]" class="form-control" multiple accept="image/*">
                        <?php if ($action === 'edit'): ?>
                            <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
                                <?php
                                $imgs_query = mysqli_query($con, "SELECT image_path FROM product_images WHERE product_id = " . intval($product_id));
                                while ($img_data = mysqli_fetch_assoc($imgs_query)):
                                ?>
                                    <img src="../<?php echo xss_clean($img_data['image_path']); ?>" style="width:80px; height:80px; object-fit:cover; border:1px solid var(--color-border-gray); border-radius:2px;">
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row" style="margin-bottom:20px;">
                        <div class="col" style="flex:1;">
                            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $p_feat ? 'checked' : ''; ?>>
                                <label for="is_featured" style="margin-bottom:0; cursor:pointer;">Mark as Featured</label>
                            </div>
                        </div>
                        <div class="col" style="flex:1;">
                            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" id="is_trending" name="is_trending" value="1" <?php echo $p_trend ? 'checked' : ''; ?>>
                                <label for="is_trending" style="margin-bottom:0; cursor:pointer;">Mark as Trending</label>
                            </div>
                        </div>
                        <div class="col" style="flex:1;">
                            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" id="is_new_arrival" name="is_new_arrival" value="1" <?php echo $p_new ? 'checked' : ''; ?>>
                                <label for="is_new_arrival" style="margin-bottom:0; cursor:pointer;">Mark as New Arrival</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order Index</label>
                        <input type="number" id="display_order" name="display_order" class="form-control" value="<?php echo intval($p_order); ?>">
                    </div>

                    <button type="submit" class="btn"><?php echo $action === 'add' ? 'Publish Piece' : 'Save Changes'; ?></button>
                </form>
            </div>
        <?php endif; ?>

    </main>

</div>

</body>
</html>
