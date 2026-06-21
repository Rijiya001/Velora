<?php
$page_title = "Website Settings";
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session - Settings is SUPERADMIN ONLY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle Settings Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);
    
    $settings = [
        'brand_name' => trim($_POST['brand_name']),
        'promo_banner_text' => trim($_POST['promo_banner_text']),
        'hero_title' => trim($_POST['hero_title']),
        'hero_subtitle' => trim($_POST['hero_subtitle']),
        'about_story' => trim($_POST['about_story']),
        'about_mission' => trim($_POST['about_mission']),
        'about_vision' => trim($_POST['about_vision']),
        'contact_address' => trim($_POST['contact_address']),
        'contact_email' => trim($_POST['contact_email']),
        'contact_phone' => trim($_POST['contact_phone']),
        'contact_hours' => trim($_POST['contact_hours']),
    ];

    // Handle Hero Image Upload if present
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['hero_image']['tmp_name'];
        $file_name = basename($_FILES['hero_image']['name']);
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
        
        $target_dir = dirname(__DIR__) . "/assets/uploads/banners/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $dest_path = $target_dir . "hero_" . time() . "_" . $file_name;
        if (move_uploaded_file($file_tmp, $dest_path)) {
            $settings['hero_image'] = "assets/uploads/banners/hero_" . time() . "_" . $file_name;
        }
    }

    // Save each setting to DB using prepared statements
    $update_error = false;
    foreach ($settings as $key => $val) {
        $stmt = mysqli_prepare($con, "INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $key, $val, $val);
            if (!mysqli_stmt_execute($stmt)) {
                $update_error = true;
            }
            mysqli_stmt_close($stmt);
        } else {
            $update_error = true;
        }
    }

    if (!$update_error) {
        $success = "Website settings updated successfully.";
        log_activity($con, $user_id, "Updated website settings");
    } else {
        $error = "Failed to update some settings.";
    }
}

// Fetch current settings
$brand_name_val = get_setting($con, 'brand_name', 'Velora');
$promo_banner_text_val = get_setting($con, 'promo_banner_text', '');
$hero_title_val = get_setting($con, 'hero_title', '');
$hero_subtitle_val = get_setting($con, 'hero_subtitle', '');
$hero_image_val = get_setting($con, 'hero_image', '');
$about_story_val = get_setting($con, 'about_story', '');
$about_mission_val = get_setting($con, 'about_mission', '');
$about_vision_val = get_setting($con, 'about_vision', '');
$contact_address_val = get_setting($con, 'contact_address', '');
$contact_email_val = get_setting($con, 'contact_email', '');
$contact_phone_val = get_setting($con, 'contact_phone', '');
$contact_hours_val = get_setting($con, 'contact_hours', '');

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1>Website Settings</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Manage all headings, banner copy, contacts, and brand parameters.</p>
            </div>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <div class="table-card" style="max-width:850px;">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <h3 style="margin-bottom:20px; border-bottom:1px solid var(--color-border-gray); padding-bottom:10px; color:var(--color-rose-gold);">General Configurations</h3>
                
                <div class="row">
                    <div class="col" style="flex:1;">
                        <div class="form-group">
                            <label for="brand_name">Brand Name</label>
                            <input type="text" id="brand_name" name="brand_name" class="form-control" value="<?php echo xss_clean($brand_name_val); ?>" required>
                        </div>
                    </div>
                    <div class="col" style="flex:2;">
                        <div class="form-group">
                            <label for="promo_banner_text">Header Promo Message</label>
                            <input type="text" id="promo_banner_text" name="promo_banner_text" class="form-control" value="<?php echo xss_clean($promo_banner_text_val); ?>">
                        </div>
                    </div>
                </div>

                <h3 style="margin-top:40px; margin-bottom:20px; border-bottom:1px solid var(--color-border-gray); padding-bottom:10px; color:var(--color-rose-gold);">Homepage Hero Section</h3>
                
                <div class="form-group">
                    <label for="hero_title">Hero H1 Title (supports HTML formatting)</label>
                    <input type="text" id="hero_title" name="hero_title" class="form-control" value="<?php echo xss_clean($hero_title_val); ?>">
                </div>

                <div class="form-group">
                    <label for="hero_subtitle">Hero Subtitle</label>
                    <input type="text" id="hero_subtitle" name="hero_subtitle" class="form-control" value="<?php echo xss_clean($hero_subtitle_val); ?>">
                </div>

                <div class="form-group">
                    <label for="hero_image">Hero Cover Image</label>
                    <input type="file" id="hero_image" name="hero_image" class="form-control">
                    <?php if (!empty($hero_image_val)): ?>
                        <div style="margin-top:10px;">
                            <small style="color:var(--color-warm-gray); display:block; margin-bottom:5px;">Current Cover:</small>
                            <img src="../<?php echo xss_clean($hero_image_val); ?>" style="width:200px; height:80px; object-fit:cover; border:1px solid var(--color-border-gray); border-radius:2px;">
                        </div>
                    <?php endif; ?>
                </div>

                <h3 style="margin-top:40px; margin-bottom:20px; border-bottom:1px solid var(--color-border-gray); padding-bottom:10px; color:var(--color-rose-gold);">About Brand Narrative</h3>
                
                <div class="form-group">
                    <label for="about_story">Brand Heritage Story</label>
                    <textarea id="about_story" name="about_story" class="form-control"><?php echo xss_clean($about_story_val); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="about_mission">Our Mission</label>
                    <textarea id="about_mission" name="about_mission" class="form-control"><?php echo xss_clean($about_mission_val); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="about_vision">Our Vision</label>
                    <textarea id="about_vision" name="about_vision" class="form-control"><?php echo xss_clean($about_vision_val); ?></textarea>
                </div>

                <h3 style="margin-top:40px; margin-bottom:20px; border-bottom:1px solid var(--color-border-gray); padding-bottom:10px; color:var(--color-rose-gold);">Contact Details & Hours</h3>
                
                <div class="form-group">
                    <label for="contact_address">Showroom Address</label>
                    <input type="text" id="contact_address" name="contact_address" class="form-control" value="<?php echo xss_clean($contact_address_val); ?>">
                </div>

                <div class="row">
                    <div class="col" style="flex:1;">
                        <div class="form-group">
                            <label for="contact_email">Concierge Email</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo xss_clean($contact_email_val); ?>">
                        </div>
                    </div>
                    <div class="col" style="flex:1;">
                        <div class="form-group">
                            <label for="contact_phone">Concierge Hotline</label>
                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo xss_clean($contact_phone_val); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact_hours">Business Hours Schedule</label>
                    <input type="text" id="contact_hours" name="contact_hours" class="form-control" value="<?php echo xss_clean($contact_hours_val); ?>">
                </div>

                <button type="submit" class="btn" style="margin-top:20px; width:100%;">Save Website settings</button>
            </form>
        </div>

    </main>

</div>

</body>
</html>
