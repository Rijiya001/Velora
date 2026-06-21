<?php
$page_title = "My Profile";
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch current user details
$stmt = mysqli_prepare($con, "SELECT fullname, email, phone, status FROM users WHERE id = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $fullname, $email, $phone, $status);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
$role = "customer";

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    verify_csrf_token($_POST['csrf_token']);
    
    $new_name = trim($_POST['fullname']);
    $new_phone = trim($_POST['phone']);

    if (empty($new_name)) {
        $error = "Name cannot be empty.";
    } else {
        $update_stmt = mysqli_prepare($con, "UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "ssi", $new_name, $new_phone, $user_id);
            if (mysqli_stmt_execute($update_stmt)) {
                $fullname = $new_name;
                $phone = $new_phone;
                $_SESSION['fullname'] = $fullname;
                $success = "Profile updated successfully.";
                log_activity($con, $email, 'user', "Updated personal profile details");
            } else {
                $error = "Failed to update profile details.";
            }
            mysqli_stmt_close($update_stmt);
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    verify_csrf_token($_POST['csrf_token']);
    
    $current_pwd = $_POST['current_password'];
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
        $error = "Please fill in all password fields.";
    } elseif ($new_pwd !== $confirm_pwd) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_pwd) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Verify current password
        $pwd_stmt = mysqli_prepare($con, "SELECT password FROM users WHERE id = ? LIMIT 1");
        if ($pwd_stmt) {
            mysqli_stmt_bind_param($pwd_stmt, "i", $user_id);
            mysqli_stmt_execute($pwd_stmt);
            mysqli_stmt_bind_result($pwd_stmt, $stored_pwd);
            mysqli_stmt_fetch($pwd_stmt);
            mysqli_stmt_close($pwd_stmt);

            if (password_verify($current_pwd, $stored_pwd)) {
                // Update password
                $new_hashed = password_hash($new_pwd, PASSWORD_BCRYPT);
                $pass_update = mysqli_prepare($con, "UPDATE users SET password = ? WHERE id = ?");
                if ($pass_update) {
                    mysqli_stmt_bind_param($pass_update, "si", $new_hashed, $user_id);
                    if (mysqli_stmt_execute($pass_update)) {
                        $success = "Password updated successfully.";
                        log_activity($con, $email, 'user', "Changed account password");
                    } else {
                        $error = "Failed to update password.";
                    }
                    mysqli_stmt_close($pass_update);
                }
            } else {
                $error = "Incorrect current password.";
            }
        }
    }
}

// Active tab selector helper
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'details';

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="profile-wrapper">
    <div class="container">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="max-width:900px; margin: 0 auto 30px;"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="max-width:900px; margin: 0 auto 30px;"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            
            <!-- Sidebar Panel -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo xss_clean(strtoupper($fullname[0])); ?>
                </div>
                <h3><?php echo xss_clean($fullname); ?></h3>
                <span class="role-badge"><?php echo xss_clean($role); ?></span>
                
                <ul class="profile-nav">
                    <li><a href="?tab=details" class="<?php echo $tab === 'details' ? 'active' : ''; ?>">Account Details</a></li>
                    <li><a href="?tab=password" class="<?php echo $tab === 'password' ? 'active' : ''; ?>">Security Settings</a></li>
                    <li><a href="?tab=wishlist" class="<?php echo $tab === 'wishlist' ? 'active' : ''; ?>">Saved Jewelry (<?php 
                        $c_q = mysqli_query($con, "SELECT COUNT(*) FROM wishlist WHERE user_id = $user_id");
                        $c_r = mysqli_fetch_row($c_q);
                        echo $c_r[0];
                    ?>)</a></li>
                </ul>
            </div>

            <!-- Main Content Area -->
            <div class="profile-main">
                
                <?php if ($tab === 'details'): ?>
                    <h2>Account Information</h2>
                    <form action="?tab=details" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="text" class="form-control" value="<?php echo xss_clean($email); ?>" disabled style="background-color:var(--color-border-gray); cursor:not-allowed;">
                            <small style="color:var(--color-warm-gray); display:block; margin-top:5px;">Email address changes require contacting administrative concierge support.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo xss_clean($fullname); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo xss_clean($phone); ?>">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-sm">Update Details</button>
                    </form>

                <?php elseif ($tab === 'password'): ?>
                    <h2>Change Password</h2>
                    <form action="?tab=password" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required autocomplete="new-password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-sm">Save Password</button>
                    </form>

                <?php elseif ($tab === 'wishlist'): ?>
                    <h2>My Saved Pieces</h2>
                    
                    <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <?php
                        $wl_query = mysqli_query($con, "SELECT p.* FROM wishlist w 
                                                        JOIN products p ON w.product_id = p.id 
                                                        WHERE w.user_id = $user_id AND p.status = 'active'");
                        if (mysqli_num_rows($wl_query) > 0):
                            while ($prod = mysqli_fetch_assoc($wl_query)):
                        ?>
                            <div class="product-card" style="flex-direction: row; height: 160px; border-radius: 4px;">
                                <div style="width: 140px; min-width:140px; position:relative; overflow:hidden;">
                                    <img src="<?php echo xss_clean($prod['main_image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <div style="padding: 20px; display:flex; flex-direction:column; justify-content:space-between; flex-grow:1;">
                                    <div>
                                        <div style="font-size:0.7rem; color:var(--color-warm-gray); text-transform:uppercase; letter-spacing:0.1em;"><?php echo xss_clean($prod['material']); ?></div>
                                        <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:5px;"><?php echo xss_clean($prod['name']); ?></h3>
                                        <code style="font-size:0.75rem; background-color:var(--color-light-beige); padding: 1px 5px; border-radius: 2px;"><?php echo xss_clean($prod['product_code']); ?></code>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <a href="product.php?code=<?php echo urlencode($prod['product_code']); ?>" style="font-size:0.75rem; font-weight:600; color:var(--color-champagne-gold);">View Details</a>
                                        <a href="?tab=wishlist" onclick="removeWishItem(<?php echo $prod['id']; ?>)" style="font-size:0.75rem; color:var(--color-error);">Remove</a>
                                    </div>
                                </div>
                            </div>
                        <?php
                            endwhile;
                        else:
                            echo '<p style="grid-column: 1/-1; color: var(--color-warm-gray); font-style:italic;">You haven\'t saved any jewelry pieces yet.</p>';
                        endif;
                        ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<script>
function removeWishItem(id) {
    const formData = new FormData();
    formData.append('product_id', id);
    fetch('api/wishlist.php', { method: 'POST', body: formData })
    .then(() => window.location.reload());
}
</script>

<?php
include 'includes/footer.php';
?>
