<?php
$page_title = "Admin Dashboard";
require_once 'config/database.php';

// Session protection
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_user']);
    session_destroy();
    header('location:login.php');
    exit();
}

// Check authorization
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('location:login.php');
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="table-section">
    <div class="container">
        <!-- Dashboard Sub-Header / Management Bar -->
        <div style="display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px solid var(--color-border-gray); padding-bottom: 15px; margin-bottom: 40px;">
            <h2 style="margin: 0; padding: 0; text-align: left; text-transform: capitalize;">Inquiries Dashboard</h2>
            <div style="display: flex; gap: 20px; font-family: var(--font-body); font-size: 0.8rem; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase;">
                <a href="addrate.php" style="color: var(--color-champagne-gold);">Update market rates</a>
                <a href="display.php?action=logout" style="color: var(--color-error);">Log Out</a>
            </div>
        </div>

        <p style="color: var(--color-warm-gray); margin-bottom: 25px; font-size: 0.9rem;">
            Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong>. Below is the registry of custom order inquiries and catalog reservations.
        </p>

        <div class="table-container">
            <table class="luxury-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client Details</th>
                        <th>Mobile</th>
                        <th>Address</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Design Sketch</th>
                        <th>Specifications / Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $selectquery = "SELECT * FROM userinfodata ORDER BY id DESC";
                    $query = mysqli_query($con, $selectquery);
                    
                    if ($query && mysqli_num_rows($query) > 0) {
                        while ($data = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($data['user']); ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--color-warm-gray);"><?php echo htmlspecialchars($data['email']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($data['mobile']); ?></td>
                                <td><?php echo htmlspecialchars($data['address']); ?></td>
                                <td><code style="background-color: var(--color-light-beige); padding: 2px 6px; border-radius: 2px; font-weight: 500; color: var(--color-deep-charcoal);"><?php echo htmlspecialchars($data['code']); ?></code></td>
                                <td><?php echo htmlspecialchars($data['interestedon']); ?></td>
                                <td>
                                    <?php if (!empty($data['image']) && file_exists($data['image'])): ?>
                                        <a href="<?php echo htmlspecialchars($data['image']); ?>" target="_blank">
                                            <img src="<?php echo htmlspecialchars($data['image']); ?>" alt="client upload" style="width: 80px; height: 50px; object-fit: cover; border-radius: 2px; border: 1px solid var(--color-border-gray); transition: var(--transition-fast);">
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 0.75rem; color: var(--color-warm-gray); font-style: italic;">No Sketch</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; max-width: 250px; line-height: 1.4; color: var(--color-warm-gray);">
                                    <?php echo nl2br(htmlspecialchars($data['comment'])); ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="8" style="text-align: center; padding: 40px;">No customer orders found in registry.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
