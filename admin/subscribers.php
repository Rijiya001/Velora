<?php
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session - Subscribers is SUPERADMIN ONLY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$sub_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Handle CSV Export Action
if ($action === 'export') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=velora_subscribers_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Header Row
    fputcsv($output, array('ID', 'Name', 'Email Address', 'Subscription Date'));
    
    $query = mysqli_query($con, "SELECT id, name, email, created_at FROM subscribers ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    log_activity($con, $_SESSION['email'], $_SESSION['role'], "Exported subscriber list as CSV");
    exit();
}

// 2. Handle Delete Action
if ($action === 'delete' && $sub_id > 0) {
    $stmt = mysqli_prepare($con, "DELETE FROM subscribers WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $sub_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Subscriber removed.";
            log_activity($con, $_SESSION['email'], $_SESSION['role'], "Deleted subscriber ID $sub_id");
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: subscribers.php");
    exit();
}

$page_title = "Manage Subscribers";
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">
    
    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <div>
                <h1>Club Subscribers</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Subscribed contacts for newsletters and exclusive launches.</p>
            </div>
            
            <a href="?action=export" class="btn btn-sm">Export CSV</a>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <div class="table-card">
            <table class="luxury-table">
                <thead>
                    <tr>
                        <th>Subscriber ID</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Subscription Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subs = mysqli_query($con, "SELECT * FROM subscribers ORDER BY id DESC");
                    if (mysqli_num_rows($subs) > 0):
                        while ($row = mysqli_fetch_assoc($subs)):
                    ?>
                        <tr>
                            <td><code>#<?php echo $row['id']; ?></code></td>
                            <td style="font-weight:600;"><?php echo xss_clean($row['name'] ?? 'Not Provided'); ?></td>
                            <td><a href="mailto:<?php echo xss_clean($row['email']); ?>" style="color:var(--color-champagne-gold);"><?php echo xss_clean($row['email']); ?></a></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Remove this email from the subscribers list?')">Remove</a>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                        echo '<tr><td colspan="5" style="text-align:center; color:var(--color-warm-gray);">No subscribers found in the database.</td></tr>';
                    endif;
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
