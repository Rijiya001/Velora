<?php
$page_title = "Manage Admin & User Access";
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session - Users is SUPERADMIN ONLY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$user_role = $_SESSION['role'];
$error = "";
$success = "";

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$target_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'user'; // 'admin' or 'user'
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'admins'; // 'admins' or 'customers'

// 1. Handle Suspend Action
if ($action === 'suspend' && $target_id > 0) {
    if ($type === 'admin') {
        if ($target_id === $user_id) {
            $error = "You cannot suspend your own administrative account.";
        } else {
            $stmt = mysqli_prepare($con, "UPDATE admins SET status = 'suspended' WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $target_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Administrator account suspended.";
                    log_activity($con, $user_email, $user_role, "Suspended Administrator ID $target_id");
                }
                mysqli_stmt_close($stmt);
            }
        }
    } else {
        $stmt = mysqli_prepare($con, "UPDATE users SET status = 'suspended' WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $target_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Customer account suspended.";
                log_activity($con, $user_email, $user_role, "Suspended Customer ID $target_id");
            }
            mysqli_stmt_close($stmt);
        }
    }
    if (empty($error)) {
        header("Location: users.php?tab=" . ($type === 'admin' ? 'admins' : 'customers'));
        exit();
    }
}

// 2. Handle Activate Action
if ($action === 'activate' && $target_id > 0) {
    if ($type === 'admin') {
        $stmt = mysqli_prepare($con, "UPDATE admins SET status = 'active' WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $target_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Administrator account activated.";
                log_activity($con, $user_email, $user_role, "Activated Administrator ID $target_id");
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $stmt = mysqli_prepare($con, "UPDATE users SET status = 'active' WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $target_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Customer account activated.";
                log_activity($con, $user_email, $user_role, "Activated Customer ID $target_id");
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: users.php?tab=" . ($type === 'admin' ? 'admins' : 'customers'));
    exit();
}

// 3. Handle Delete Action
if ($action === 'delete' && $target_id > 0) {
    if ($type === 'admin') {
        if ($target_id === $user_id) {
            $error = "You cannot delete your own administrative account.";
        } else {
            $stmt = mysqli_prepare($con, "DELETE FROM admins WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $target_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Administrator account permanently deleted.";
                    log_activity($con, $user_email, $user_role, "Deleted Administrator ID $target_id");
                }
                mysqli_stmt_close($stmt);
            }
        }
    } else {
        $stmt = mysqli_prepare($con, "DELETE FROM users WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $target_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Customer account permanently deleted.";
                log_activity($con, $user_email, $user_role, "Deleted Customer ID $target_id");
            }
            mysqli_stmt_close($stmt);
        }
    }
    if (empty($error)) {
        header("Location: users.php?tab=" . ($type === 'admin' ? 'admins' : 'customers'));
        exit();
    }
}

// 4. Handle Create Admin POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_admin') {
    verify_csrf_token($_POST['csrf_token']);
    
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    if (empty($fullname) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($role, ['admin', 'superadmin'])) {
        $error = "Invalid role assigned.";
    } else {
        // Check if email already registered in either table
        $check1 = mysqli_prepare($con, "SELECT id FROM admins WHERE email = ? LIMIT 1");
        $check2 = mysqli_prepare($con, "SELECT id FROM users WHERE email = ? LIMIT 1");
        
        if ($check1 && $check2) {
            mysqli_stmt_bind_param($check1, "s", $email);
            mysqli_stmt_execute($check1);
            mysqli_stmt_store_result($check1);
            
            mysqli_stmt_bind_param($check2, "s", $email);
            mysqli_stmt_execute($check2);
            mysqli_stmt_store_result($check2);
            
            if (mysqli_stmt_num_rows($check1) > 0 || mysqli_stmt_num_rows($check2) > 0) {
                $error = "This email is already in use.";
            } else {
                // Hash password and insert
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($con, "INSERT INTO admins (fullname, email, phone, password, role, status, created_by) VALUES (?, ?, ?, ?, ?, 'active', ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $email, $phone, $hashed_password, $role, $user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = "New administrative account registered successfully.";
                        log_activity($con, $user_email, $user_role, "Created administrative login: $email ($role)");
                        header("Location: users.php?tab=admins");
                        exit();
                    } else {
                        $error = "Failed to register administrative account.";
                    }
                    mysqli_stmt_close($stmt);
                }
            }
            mysqli_stmt_close($check1);
            mysqli_stmt_close($check2);
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
                <h1>Access Control Dashboard</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Manage administrative accounts and registered customers separately.</p>
            </div>
            
            <?php if ($action === 'list'): ?>
                <a href="?action=add_admin" class="btn btn-sm">Grant Admin Access</a>
            <?php else: ?>
                <a href="users.php" class="btn btn-sm btn-secondary">← Back to Accounts</a>
            <?php endif; ?>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <!-- List View with Separate Tabs -->
        <?php if ($action === 'list'): ?>
            
            <!-- Tab Headers -->
            <div class="admin-tabs">
                <a href="?tab=admins" class="admin-tab <?php echo $tab === 'admins' ? 'admin-tab-active' : 'admin-tab-inactive'; ?>">
                    Administrators &amp; Staff (<?php 
                        $c_q = mysqli_query($con, "SELECT COUNT(*) FROM admins");
                        echo mysqli_fetch_row($c_q)[0];
                    ?>)
                </a>
                <a href="?tab=customers" class="admin-tab <?php echo $tab === 'customers' ? 'admin-tab-active' : 'admin-tab-inactive'; ?>">
                    Registered Customers (<?php 
                        $c_q = mysqli_query($con, "SELECT COUNT(*) FROM users");
                        echo mysqli_fetch_row($c_q)[0];
                    ?>)
                </a>
            </div>

            <!-- Administrators Tab Content -->
            <?php if ($tab === 'admins'): ?>
                <div class="table-card">
                    <h3 style="margin-bottom: 20px; font-family: var(--font-body); font-weight: 600; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-champagne-gold);">Administrative Team</h3>
                    <table class="luxury-table">
                        <thead>
                            <tr>
                                <th>Admin ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Created By</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $admins_query = mysqli_query($con, "SELECT a.*, creator.fullname as creator_name FROM admins a 
                                                                 LEFT JOIN admins creator ON a.created_by = creator.id 
                                                                 ORDER BY a.role DESC, a.id ASC");
                            if (mysqli_num_rows($admins_query) > 0):
                                while ($row = mysqli_fetch_assoc($admins_query)):
                            ?>
                                <tr>
                                    <td><code>#ADM-<?php echo $row['id']; ?></code></td>
                                    <td style="font-weight:600;"><?php echo xss_clean($row['fullname']); ?></td>
                                    <td><?php echo xss_clean($row['email']); ?></td>
                                    <td><?php echo xss_clean($row['phone'] ?? '-'); ?></td>
                                    <td><span class="badge badge-role"><?php echo xss_clean(strtoupper($row['role'])); ?></span></td>
                                    <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo $row['creator_name'] ? xss_clean($row['creator_name']) : 'System/Seeder'; ?></td>
                                    <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo $row['last_login'] ? date('Y-m-d H:i', strtotime($row['last_login'])) : 'Never'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status'] === 'active' ? 'active' : 'suspended'; ?>">
                                            <?php echo xss_clean($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['id'] !== $user_id): ?>
                                            <div class="action-links">
                                                <?php if ($row['status'] === 'active'): ?>
                                                    <a href="?action=suspend&type=admin&id=<?php echo $row['id']; ?>" style="color:var(--color-warm-gray);">Suspend</a>
                                                <?php else: ?>
                                                    <a href="?action=activate&type=admin&id=<?php echo $row['id']; ?>" style="color:var(--color-success);">Activate</a>
                                                <?php endif; ?>
                                                <a href="?action=delete&type=admin&id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Permanently revoke administrative access for this account?')">Delete</a>
                                            </div>
                                        <?php else: ?>
                                            <small style="color:var(--color-warm-gray);">Current Session</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                                echo '<tr><td colspan="9" style="text-align:center; color:var(--color-warm-gray);">No administrators found.</td></tr>';
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>

            <!-- Customers Tab Content -->
            <?php else: ?>
                <div class="table-card">
                    <h3 style="margin-bottom: 20px; font-family: var(--font-body); font-weight: 600; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-champagne-gold);">Registered Customers</h3>
                    <table class="luxury-table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered Date</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users_query = mysqli_query($con, "SELECT * FROM users ORDER BY id DESC");
                            if (mysqli_num_rows($users_query) > 0):
                                while ($row = mysqli_fetch_assoc($users_query)):
                            ?>
                                <tr>
                                    <td><code>#USR-<?php echo $row['id']; ?></code></td>
                                    <td style="font-weight:600;"><?php echo xss_clean($row['fullname']); ?></td>
                                    <td><?php echo xss_clean($row['email']); ?></td>
                                    <td><?php echo xss_clean($row['phone'] ?? '-'); ?></td>
                                    <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                    <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo $row['last_login'] ? date('Y-m-d H:i', strtotime($row['last_login'])) : 'Never'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status'] === 'active' ? 'active' : 'suspended'; ?>">
                                            <?php echo xss_clean($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-links">
                                            <?php if ($row['status'] === 'active'): ?>
                                                <a href="?action=suspend&type=user&id=<?php echo $row['id']; ?>" style="color:var(--color-warm-gray);">Suspend</a>
                                            <?php else: ?>
                                                <a href="?action=activate&type=user&id=<?php echo $row['id']; ?>" style="color:var(--color-success);">Activate</a>
                                            <?php endif; ?>
                                            <a href="?action=delete&type=user&id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Permanently delete this customer account? All wishlist items will be removed.')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                                echo '<tr><td colspan="8" style="text-align:center; color:var(--color-warm-gray);">No registered customers yet.</td></tr>';
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <!-- Add Admin View -->
        <?php elseif ($action === 'add_admin'): ?>
            <div class="table-card" style="max-width:600px;">
                <h3 style="margin-bottom:30px;">Register Administrative Account</h3>
                
                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" required placeholder="e.g. Richard Vance">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="e.g. richard@velora.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. 9865480193">
                    </div>

                    <div class="form-group">
                        <label for="role">Role Permission Level *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin">Admin (Manage jewelry catalog & collections)</option>
                            <option value="superadmin">Super Admin (Full system permissions)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="At least 6 characters">
                    </div>

                    <button type="submit" class="btn">Grant Access</button>
                </form>
            </div>
        <?php endif; ?>

    </main>

</div>

</body>
</html>
