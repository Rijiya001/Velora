<?php
$page_title = "Media Manager";
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
$media_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Handle Delete Action
if ($action === 'delete' && $media_id > 0) {
    // Select path to delete file from disk first
    $stmt = mysqli_prepare($con, "SELECT image_path FROM gallery WHERE id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $media_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $file_path);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Delete from DB
        $del_stmt = mysqli_prepare($con, "DELETE FROM gallery WHERE id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $media_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $success = "Media item deleted.";
                log_activity($con, $_SESSION['email'], $_SESSION['role'], "Deleted gallery media item ID $media_id");
                
                // Delete actual file
                $full_path = dirname(__DIR__) . '/' . $file_path;
                if (!empty($file_path) && file_exists($full_path) && is_file($full_path)) {
                    unlink($full_path);
                }
            }
            mysqli_stmt_close($del_stmt);
        }
    }
    header("Location: media.php");
    exit();
}

// 2. Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'upload') {
    verify_csrf_token($_POST['csrf_token']);
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $error = "Title is required for cataloguing.";
    } elseif (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid image file to upload.";
    } else {
        $file_tmp = $_FILES['media_file']['tmp_name'];
        $file_name = basename($_FILES['media_file']['name']);
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
        
        $target_dir = dirname(__DIR__) . "/assets/uploads/gallery/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $timestamp = time();
        $dest_path = $target_dir . $timestamp . "_" . $file_name;
        if (move_uploaded_file($file_tmp, $dest_path)) {
            $relative_path = "assets/uploads/gallery/" . $timestamp . "_" . $file_name;
            
            $stmt = mysqli_prepare($con, "INSERT INTO gallery (image_path, title, description) VALUES (?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $relative_path, $title, $description);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Image uploaded to atelier gallery.";
                    log_activity($con, $_SESSION['email'], $_SESSION['role'], "Uploaded gallery photo: $title");
                    header("Location: media.php");
                    exit();
                } else {
                    $error = "Database insertion failed.";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Failed to write image file to disk.";
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
                <h1>Atelier Media Manager</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Upload campaigns, model features, and showroom gallery archives.</p>
            </div>
            
            <?php if ($action === 'list'): ?>
                <a href="?action=upload" class="btn btn-sm">Upload Photo</a>
            <?php else: ?>
                <a href="media.php" class="btn btn-sm btn-secondary">← Back to Gallery</a>
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
            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <?php
                $imgs = mysqli_query($con, "SELECT * FROM gallery ORDER BY id DESC");
                if (mysqli_num_rows($imgs) > 0):
                    while ($row = mysqli_fetch_assoc($imgs)):
                ?>
                    <div style="background-color: var(--color-pure-white); border: 1px solid var(--color-border-gray); border-radius: 4px; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
                        <div style="width:100%; padding-bottom:90%; position:relative; background-color:var(--color-light-beige);">
                            <img src="../<?php echo xss_clean($row['image_path']); ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;">
                        </div>
                        <div style="padding: 15px;">
                            <h4 style="font-family:var(--font-body); font-weight:600; font-size:0.85rem; margin-bottom:5px;"><?php echo xss_clean($row['title']); ?></h4>
                            <p style="font-size:0.75rem; color:var(--color-warm-gray); margin-bottom:12px; height: 35px; overflow: hidden;"><?php echo xss_clean($row['description']); ?></p>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <code style="font-size:0.7rem; color:var(--color-warm-gray);"><?php echo $row['id']; ?></code>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" style="font-size:0.75rem; color:var(--color-error); font-weight:600;" onclick="return confirm('Permanently remove this image from the showroom archive?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                    echo '<p style="grid-column: 1/-1; text-align: center; color: var(--color-warm-gray); padding: 50px 0;">No media files uploaded yet.</p>';
                endif;
                ?>
            </div>

        <!-- Upload View -->
        <?php elseif ($action === 'upload'): ?>
            <div class="table-card" style="max-width:600px;">
                <h3 style="margin-bottom:30px;">Upload Gallery Masterpiece</h3>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Royal Bangle Details">
                    </div>

                    <div class="form-group">
                        <label for="description">Short Description / Credits</label>
                        <input type="text" id="description" name="description" class="form-control" placeholder="e.g. Campaign photo shot in Hetauda Atelier">
                    </div>

                    <div class="form-group">
                        <label for="media_file">Select Image File *</label>
                        <input type="file" id="media_file" name="media_file" class="form-control" required>
                        <small style="color:var(--color-warm-gray); display:block; margin-top:5px;">Supports JPEG, PNG, or WebP.</small>
                    </div>

                    <button type="submit" class="btn">Upload to Gallery</button>
                </form>
            </div>
        <?php endif; ?>

    </main>

</div>

</body>
</html>
