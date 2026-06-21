<?php
$page_title = "Log In";
require_once dirname(__DIR__) . '/config/database.php';

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all credentials.";
    } else {
        // First check in admins table
        $stmt = mysqli_prepare($con, "SELECT id, fullname, password, role, status FROM admins WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $admin_id, $fullname, $hashed_password, $role, $status);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                
                if ($status === 'suspended') {
                    $error = "Your account has been suspended by an administrator.";
                } elseif (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $admin_id;
                    $_SESSION['fullname'] = $fullname;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role; // admin or superadmin
                    
                    $update_stmt = mysqli_prepare($con, "UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    if ($update_stmt) {
                        mysqli_stmt_bind_param($update_stmt, "i", $admin_id);
                        mysqli_stmt_execute($update_stmt);
                        mysqli_stmt_close($update_stmt);
                    }
                    
                    log_activity($con, $email, $role, "Admin logged in successfully");
                    header('Location: ../admin/dashboard.php');
                    exit();
                } else {
                    $error = "Invalid password credential.";
                }
            } else {
                mysqli_stmt_close($stmt);
                // Not found in admins, check users (customers) table
                $stmt = mysqli_prepare($con, "SELECT id, fullname, password, status FROM users WHERE email = ? LIMIT 1");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $user_id, $fullname, $hashed_password, $status);
                        mysqli_stmt_fetch($stmt);
                        mysqli_stmt_close($stmt);
                        
                        if ($status === 'suspended') {
                            $error = "Your account has been suspended by an administrator.";
                        } elseif (password_verify($password, $hashed_password)) {
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['fullname'] = $fullname;
                            $_SESSION['email'] = $email;
                            $_SESSION['role'] = 'user';
                            
                            $update_stmt = mysqli_prepare($con, "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                            if ($update_stmt) {
                                mysqli_stmt_bind_param($update_stmt, "i", $user_id);
                                mysqli_stmt_execute($update_stmt);
                                mysqli_stmt_close($update_stmt);
                            }
                            
                            log_activity($con, $email, 'user', "Customer logged in successfully");
                            header('Location: ../index.php');
                            exit();
                        } else {
                            $error = "Invalid password credential.";
                        }
                    } else {
                        mysqli_stmt_close($stmt);
                        $error = "Account not found.";
                    }
                } else {
                    $error = "System query preparation error.";
                }
            }
        } else {
            $error = "System query preparation error.";
        }
    }
}

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Welcome</h1>
        <p class="auth-subtitle">Log in to your Velora profile showcase account.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. client@velora.com" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn">Log In</button>
        </form>
        
        <div style="margin-top: 25px; text-align: center; font-size: 0.85rem; color: var(--color-warm-gray);">
            Don't have an account? <a href="register.php" style="color: var(--color-champagne-gold); font-weight: 500;">Register Now</a>
        </div>
    </div>
</div>

<?php
include dirname(__DIR__) . '/includes/footer.php';
?>
