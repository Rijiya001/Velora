<?php
$page_title = "Manage Admin Access";
require_once dirname(__DIR__) . '/config/database.php';

// Validate Admin/Superadmin session - Users is SUPERADMIN ONLY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$target_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Handle Suspend Action
if ($action === 'suspend' && $target_user_id > 0) {
    // Prevent self-suspension
    if ($target_user_id === $user_id) {
        $error = "You cannot suspend your own account.";
    } else {
        $stmt = mysqli_prepare($con, "UPDATE users SET status = 'suspended' WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $target_user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "User access suspended.";
                log_activity($con, $user_id, "Suspended access of User ID $target_user_id");
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: users.php");
    exit();
}

// 2. Handle Activate Action
if ($action === 'activate' && $target_user_id > 0) {
    $stmt = mysqli_prepare($con, "UPDATE users SET status = 'active' WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $target_user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "User access activated.";
            log_activity($con, $user_id, "Activated access of User ID $target_user_id");
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: users.php");
    exit();
}

// 3. Handle Delete Action
if ($action === 'delete' && $target_user_id > 0) {
    // Prevent self-deletion
    if ($target_user_id === $user_id) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = mysqli_prepare($con, "DELETE FROM users WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $target_user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "User account removed.";
                log_activity($con, $user_id, "Deleted account User ID $target_user_id");
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: users.php");
    exit();
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
        // Check if email already registered
        $check = mysqli_prepare($con, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        
        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "This email is already in use.";
            mysqli_stmt_close($check);
        } else {
            mysqli_stmt_close($check);
            
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($con, "INSERT INTO users (fullname, email, phone, password, role, status, created_by) VALUES (?, ?, ?, ?, ?, 'active', ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $email, $phone, $hashed_password, $role, $user_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "New admin account registered successfully.";
                    log_activity($con, $user_id, "Created administrative login: $email ($role)");
                    header("Location: users.php");
                    exit();
                } else {
                    $error = "Failed to register account.";
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
                <h1>Access Control Dashboard</h1>
                <p style="color:var(--color-warm-gray); font-size:0.9rem;">Manage administrative accounts, role levels, and audit login trails.</p>
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

        <!-- List View -->
        <?php if ($action === 'list'): ?>
            <div class="table-card">
                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Created By</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = mysqli_query($con, "SELECT u.*, creator.fullname as creator_name FROM users u 
                                                     LEFT JOIN users creator ON u.created_by = creator.id 
                                                     ORDER BY u.role DESC, u.id ASC");
                        if (mysqli_num_rows($users) > 0):
                            while ($row = mysqli_fetch_assoc($users)):
                        ?>
                            <tr>
                                <td><code>#<?php echo $row['id']; ?></code></td>
                                <td style="font-weight:600;"><?php echo xss_clean($row['fullname']); ?></td>
                                <td><?php echo xss_clean($row['email']); ?></td>
                                <td><span class="badge badge-role"><?php echo xss_clean(strtoupper($row['role'])); ?></span></td>
                                <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo $row['creator_name'] ? xss_clean($row['creator_name']) : 'System/Seeder'; ?></td>
                                <td style="font-size:0.8rem; color:var(--color-warm-gray);"><?php echo $row['last_login'] ? date('Y-m-d H:i', strtotime($row['last_login'])) : 'Never logged in'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status'] === 'active' ? 'active' : 'suspended'; ?>">
                                        <?php echo xss_clean($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['id'] !== $user_id): ?>
                                        <div class="action-links">
                                            <?php if ($row['status'] === 'active'): ?>
                                                <a href="?action=suspend&id=<?php echo $row['id']; ?>" style="color:var(--color-warm-gray);">Suspend</a>
                                            <?php else: ?>
                                                <a href="?action=activate&id=<?php echo $row['id']; ?>" style="color:var(--color-success);">Activate</a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Permanently remove this account? This cannot be undone.')">Delete</a>
                                        </div>
                                    <?php else: ?>
                                        <small style="color:var(--color-warm-gray);">Logged In</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                            echo '<tr><td colspan="8" style="text-align:center; color:var(--color-warm-gray);">No accounts registered.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

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
