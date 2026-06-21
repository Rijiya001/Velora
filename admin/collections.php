<?php
$page_title = "Manage Collections";
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$col_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Handle Delete Action
if ($action === 'delete' && $col_id > 0) {
    if ($_SESSION['role'] !== 'superadmin') {
        $error = "Only Super Administrators can permanently delete collections.";
    } else {
        $stmt = mysqli_prepare($con, "DELETE FROM collections WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $col_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Collection archive permanently deleted.";
                log_activity($con, $_SESSION['email'], $_SESSION['role'], "Deleted collection ID $col_id");
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: collections.php");
        exit();
    }
}

// 2. Handle Create & Edit POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    verify_csrf_token($_POST['csrf_token']);
    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $display_order = intval($_POST['display_order']);
    
    $banner_image_path = isset($_POST['existing_image']) ? $_POST['existing_image'] : 'designs/design3.jpg';

    // Handle File Upload if present
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['banner_image']['tmp_name'];
        $file_name = basename($_FILES['banner_image']['name']);
        // Sanitize file name to prevent directory traversal
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
        
        $target_dir = dirname(__DIR__) . "/assets/uploads/banners/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $dest_path = $target_dir . time() . "_" . $file_name;
        if (move_uploaded_file($file_tmp, $dest_path)) {
            $banner_image_path = "assets/uploads/banners/" . time() . "_" . $file_name;
        } else {
            $error = "Failed to upload banner image.";
        }
    }

    if (empty($name)) {
        $error = "Collection Name is required.";
    } elseif (empty($error)) {
        if ($action === 'add') {
            $stmt = mysqli_prepare($con, "INSERT INTO collections (name, description, banner_image, display_order) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssi", $name, $description, $banner_image_path, $display_order);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Collection created successfully.";
                    log_activity($con, $_SESSION['email'], $_SESSION['role'], "Created collection: $name");
                    header("Location: collections.php");
                    exit();
                } else {
                    $error = "Database insertion error: " . mysqli_error($con);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // Action edit
            $stmt = mysqli_prepare($con, "UPDATE collections SET name = ?, description = ?, banner_image = ?, display_order = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssii", $name, $description, $banner_image_path, $display_order, $col_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Collection updated successfully.";
                    log_activity($con, $_SESSION['email'], $_SESSION['role'], "Updated collection ID $col_id");
                    header("Location: collections.php");
                    exit();
                } else {
                    $error = "Database update error: " . mysqli_error($con);
                }
                mysqli_stmt_close($stmt);
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
                <h1>Jewelry Collections</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Organize catalog by styles or themes.</p>
            </div>
            
            <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-sm">Add Collection</a>
            <?php else: ?>
                <a href="collections.php" class="btn btn-sm btn-secondary">← Back to List</a>
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
                            <th>Banner</th>
                            <th>Collection ID</th>
                            <th>Collection Name</th>
                            <th>Description</th>
                            <th>Display Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cols = mysqli_query($con, "SELECT * FROM collections ORDER BY display_order ASC, id DESC");
                        if (mysqli_num_rows($cols) > 0):
                            while ($row = mysqli_fetch_assoc($cols)):
                        ?>
                            <tr>
                                <td><img src="../<?php echo xss_clean($row['banner_image']); ?>" alt="banner" style="width: 80px; height: 50px; object-fit: cover;"></td>
                                <td><code><?php echo $row['id']; ?></code></td>
                                <td style="font-weight:600;"><?php echo xss_clean($row['name']); ?></td>
                                <td style="max-width: 300px; font-size:0.8rem;"><?php echo xss_clean($row['description']); ?></td>
                                <td><code><?php echo $row['display_order']; ?></code></td>
                                <td>
                                    <div class="action-links">
                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="action-edit">Edit</a>
                                        <?php if ($_SESSION['role'] === 'superadmin'): ?>
                                            <a href="?action=delete&id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Permanently delete this collection? Connected products will lose collection bindings.')">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                            echo '<tr><td colspan="6" style="text-align:center; color:var(--color-warm-gray);">No collections added yet.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

        <!-- Add or Edit View -->
        <?php elseif ($action === 'add' || $action === 'edit'): 
            $form_title = "Create New Collection";
            $c_name = $c_desc = $c_img = "";
            $c_order = 0;
            
            if ($action === 'edit' && $col_id > 0) {
                $form_title = "Edit Collection Details";
                $edit_stmt = mysqli_prepare($con, "SELECT name, description, banner_image, display_order FROM collections WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($edit_stmt, "i", $col_id);
                mysqli_stmt_execute($edit_stmt);
                mysqli_stmt_bind_result($edit_stmt, $c_name, $c_desc, $c_img, $c_order);
                mysqli_stmt_fetch($edit_stmt);
                mysqli_stmt_close($edit_stmt);
            }
        ?>
            <div class="table-card" style="max-width:700px;">
                <h3 style="margin-bottom:30px;"><?php echo xss_clean($form_title); ?></h3>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="existing_image" value="<?php echo xss_clean($c_img); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">Collection Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo xss_clean($c_name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Short Description / Campaign Narrative</label>
                        <textarea id="description" name="description" class="form-control"><?php echo xss_clean($c_desc); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="banner_image">Banner/Campaign Image</label>
                        <input type="file" id="banner_image" name="banner_image" class="form-control">
                        <?php if (!empty($c_img)): ?>
                            <div style="margin-top:10px;">
                                <small style="color:var(--color-warm-gray); display:block; margin-bottom:5px;">Current Banner:</small>
                                <img src="../<?php echo xss_clean($c_img); ?>" style="width:150px; height:80px; object-fit:cover; border:1px solid var(--color-border-gray); border-radius:2px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order Index</label>
                        <input type="number" id="display_order" name="display_order" class="form-control" value="<?php echo intval($c_order); ?>">
                    </div>

                    <button type="submit" class="btn"><?php echo $action === 'add' ? 'Create Collection' : 'Save Changes'; ?></button>
                </form>
            </div>
        <?php endif; ?>

    </main>

</div>

</body>
</html>
