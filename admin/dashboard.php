<?php
$page_title = "Admin Dashboard";
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_role = $_SESSION['role'];
$user_name = $_SESSION['fullname'];

// 1. Fetch statistics
$total_users = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM users WHERE role = 'user'"))[0];
$total_subs = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM subscribers"))[0];
$total_prods = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM products"))[0];
$total_cols = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM collections"))[0];
$total_admins = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM users WHERE role IN ('admin', 'superadmin')"))[0];
$total_inqs = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM contact_messages"))[0];

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">
    
    <!-- Sidebar -->
    <?php include 'admin_sidebar.php'; ?>
    
    <!-- Main Dashboard Area -->
    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1>Dashboard</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Overview of the Velora platform.</p>
            </div>
            
            <div class="admin-profile-badge">
                Logged in as: <strong><?php echo xss_clean($user_name); ?></strong> (<?php echo xss_clean(strtoupper($user_role)); ?>)
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-label">Products</span>
                <span class="stat-value"><?php echo $total_prods; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Collections</span>
                <span class="stat-value"><?php echo $total_cols; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Subscribers</span>
                <span class="stat-value"><?php echo $total_subs; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Inquiries</span>
                <span class="stat-value"><?php echo $total_inqs; ?></span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
            
            <!-- Recent Inquiries -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Recent Client Inquiries</h3>
                    <a href="dashboard.php" style="font-size:0.75rem; color:var(--color-champagne-gold);">Refresh</a>
                </div>
                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Code</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $inqs_query = mysqli_query($con, "SELECT name, product_code, created_at FROM contact_messages ORDER BY id DESC LIMIT 5");
                        if (mysqli_num_rows($inqs_query) > 0):
                            while ($inq = mysqli_fetch_assoc($inqs_query)):
                        ?>
                            <tr>
                                <td style="font-weight:500;"><?php echo xss_clean($inq['name']); ?></td>
                                <td><code style="background-color:var(--color-light-beige); padding:2px 5px; border-radius:2px;"><?php echo xss_clean($inq['product_code'] ?? 'General'); ?></code></td>
                                <td style="font-size:0.75rem; color:var(--color-warm-gray);"><?php echo date('M d, H:i', strtotime($inq['created_at'])); ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                            echo '<tr><td colspan="3" style="text-align:center; color:var(--color-warm-gray);">No inquiries yet.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent User Registrations -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Recent Registrations</h3>
                    <a href="dashboard.php" style="font-size:0.75rem; color:var(--color-champagne-gold);">Refresh</a>
                </div>
                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users_query = mysqli_query($con, "SELECT fullname, email, status FROM users WHERE role = 'user' ORDER BY id DESC LIMIT 5");
                        if (mysqli_num_rows($users_query) > 0):
                            while ($usr = mysqli_fetch_assoc($users_query)):
                        ?>
                            <tr>
                                <td style="font-weight:500;"><?php echo xss_clean($usr['fullname']); ?></td>
                                <td style="font-size:0.8rem;"><?php echo xss_clean($usr['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $usr['status'] === 'active' ? 'active' : 'suspended'; ?>">
                                        <?php echo xss_clean($usr['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                            echo '<tr><td colspan="3" style="text-align:center; color:var(--color-warm-gray);">No users registered yet.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- System Activity Log (For Audit Trail) -->
        <div class="table-card">
            <div class="table-header">
                <h3>Atelier Activity Audit Logs</h3>
            </div>
            <table class="luxury-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logs_query = mysqli_query($con, "SELECT a.*, u.fullname FROM activity_logs a 
                                                      LEFT JOIN users u ON a.user_id = u.id 
                                                      ORDER BY a.id DESC LIMIT 6");
                    if (mysqli_num_rows($logs_query) > 0):
                        while ($log = mysqli_fetch_assoc($logs_query)):
                    ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo $log['user_id'] ? xss_clean($log['fullname']) . ' (ID: ' . $log['user_id'] . ')' : 'Guest'; ?></td>
                            <td><?php echo xss_clean($log['action']); ?></td>
                            <td><code><?php echo xss_clean($log['ip_address']); ?></code></td>
                            <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                        echo '<tr><td colspan="4" style="text-align:center; color:var(--color-warm-gray);">No logs recorded.</td></tr>';
                    endif;
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
