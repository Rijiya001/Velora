<?php
$page_title = "Admin Login";
require_once 'config/database.php';

// Handle login POST request
$error_msg = "";
if (isset($_POST['username']) && isset($_POST['password'])) {
    $name = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Security: Prepared statements to prevent SQL Injection
    $query = "SELECT * FROM login WHERE USER=? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $user_data = mysqli_fetch_assoc($result);
            
            // Check password. Note: The existing database stores password as plaintext ('kritika').
            // To ensure compatibility with old data, we support plaintext check, 
            // but log that hash verification is recommended.
            if ($password === $user_data['PASSWORD'] || password_verify($password, $user_data['PASSWORD'])) {
                // Set admin session
                $_SESSION['admin_user'] = $user_data['USER'];
                $_SESSION['admin_logged_in'] = true;
                
                header('location:display.php');
                exit();
            } else {
                $error_msg = "Invalid password combination.";
            }
        } else {
            $error_msg = "Administrator account not found.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Database query preparation failed.";
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="login-section">
    <div class="form-container" style="max-width: 450px;">
        <h1>Admin Portal</h1>
        <p class="form-subtitle">Authorized administrator login only.</p>
        
        <?php if (!empty($error_msg)): ?>
            <div style="background-color: var(--color-error-light); color: var(--color-error); padding: 12px; border: 1px solid #eccdd3; border-radius: 2px; margin-bottom: 20px; font-size: 0.85rem; text-align: center;">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        
        <form action="#" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Sign In</button>
        </form>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
