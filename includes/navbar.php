<?php
// Reusable navigation bar with context-aware user options
$is_logged_in = isset($_SESSION['role']);
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['fullname'] ?? '';
?>
<header id="main-header">
    <nav>
        <div class="logo-container">
            <a href="<?php echo $rel_path; ?>index.php"><?php echo xss_clean($brand_name); ?><span>.</span></a>
        </div>
        
        <ul class="nav-links">
            <li><a href="<?php echo $rel_path; ?>index.php">Home</a></li>
            <li><a href="<?php echo $rel_path; ?>collections.php">Collections</a></li>
            <li><a href="<?php echo $rel_path; ?>showcase.php">Showcase</a></li>
            <li><a href="<?php echo $rel_path; ?>gallery.php">Gallery</a></li>
            <li><a href="<?php echo $rel_path; ?>about.php">Our Story</a></li>
            <li><a href="<?php echo $rel_path; ?>contact.php">Contact</a></li>
        </ul>
        
        <ul class="nav-icons">
            <?php if ($is_logged_in): ?>
                <?php if ($user_role === 'admin' || $user_role === 'superadmin'): ?>
                    <li><a href="<?php echo $rel_path; ?>admin/dashboard.php" class="nav-admin-badge" title="Admin Dashboard">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $rel_path; ?>profile.php" title="My Profile" class="nav-profile-link">👤 <?php echo xss_clean(explode(' ', $user_name)[0]); ?></a></li>
                <li><a href="<?php echo $rel_path; ?>auth/logout.php" title="Log Out" class="nav-logout-btn">Log Out</a></li>
            <?php else: ?>
                <li><a href="<?php echo $rel_path; ?>auth/login.php" class="nav-login-btn">Log In</a></li>
                <li><a href="<?php echo $rel_path; ?>auth/register.php" class="nav-register-btn">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
