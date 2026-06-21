<?php
$page_title = "Register";
require_once dirname(__DIR__) . '/config/database.php';

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    header('Location: ../index.php');
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if (empty($fullname) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check_stmt = mysqli_prepare($con, "SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error = "This email is already registered.";
            } else {
                // Securely hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert user
                $insert_stmt = mysqli_prepare($con, "INSERT INTO users (fullname, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "ssss", $fullname, $email, $phone, $hashed_password);
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $success = "Registration successful! You can now log in.";
                        // Clean values
                        $fullname = $email = $phone = "";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                    mysqli_stmt_close($insert_stmt);
                } else {
                    $error = "Query preparation failed.";
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    }
}

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Register</h1>
        <p class="auth-subtitle">Create a private account to save catalog favorites.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo xss_clean($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo xss_clean($success); ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group">
                <label for="fullname">Full Name *</label>
                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="e.g. Eleanor Vance" value="<?php echo isset($fullname) ? xss_clean($fullname) : ''; ?>" required autocomplete="name">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. eleanor@example.com" value="<?php echo isset($email) ? xss_clean($email) : ''; ?>" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 9865480193" value="<?php echo isset($phone) ? xss_clean($phone) : ''; ?>" autocomplete="tel">
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div style="margin-top: 25px; text-align: center; font-size: 0.85rem; color: var(--color-warm-gray);">
            Already have an account? <a href="login.php" style="color: var(--color-champagne-gold); font-weight: 500;">Log In</a>
        </div>
    </div>
</div>

<?php
include dirname(__DIR__) . '/includes/footer.php';
?>
