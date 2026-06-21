<?php
// Reusable admin sidebar include
// Assumes config/database.php has been loaded and session started
$current_page = basename($_SERVER['PHP_SELF']);
$admin_role = $_SESSION['role'] ?? '';
?>
<div class="admin-sidebar">
    <div class="admin-logo">
        Velora<span>.</span>
    </div>
    
    <ul class="admin-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                Dashboard
            </a>
        </li>
        <li>
            <a href="products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                Products
            </a>
        </li>
        <li>
            <a href="collections.php" class="<?php echo $current_page === 'collections.php' ? 'active' : ''; ?>">
                Collections
            </a>
        </li>
        <li>
            <a href="media.php" class="<?php echo $current_page === 'media.php' ? 'active' : ''; ?>">
                Media Manager
            </a>
        </li>
        
        <?php if ($admin_role === 'superadmin'): ?>
            <li>
                <a href="settings.php" class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                    Site Settings
                </a>
            </li>
            <li>
                <a href="subscribers.php" class="<?php echo $current_page === 'subscribers.php' ? 'active' : ''; ?>">
                    Subscribers
                </a>
            </li>
            <li>
                <a href="users.php" class="<?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                    Admin Users
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <ul class="admin-footer-links">
        <li><a href="../index.php" style="color: #A0A0A0;">← View Website</a></li>
        <li><a href="../auth/logout.php" style="color: var(--color-error);">Sign Out</a></li>
    </ul>
</div>
