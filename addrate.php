<?php
$page_title = "Update Market Rates";
require_once 'config/database.php';

// Session protection
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Check authorization
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('location:login.php');
    exit();
}

$status_msg = "";
$status_type = "";

// Handle form submission
if (isset($_POST['submit'])) {
    $date = mysqli_real_escape_string($con, trim($_POST['date']));
    $gold = mysqli_real_escape_string($con, trim($_POST['gold']));
    $silver = mysqli_real_escape_string($con, trim($_POST['silver']));
    
    if (!empty($date) && !empty($gold) && !empty($silver)) {
        // Secure prepared statement
        $query = "INSERT INTO rate (Date, Gold, Silver) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $date, $gold, $silver);
            if (mysqli_stmt_execute($stmt)) {
                $status_msg = "Precious metal rates successfully updated.";
                $status_type = "success";
                
                // Redirect back to admin dashboard
                header("refresh:2; url=display.php");
            } else {
                $status_msg = "Error updating rates in database: " . mysqli_stmt_error($stmt);
                $status_type = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $status_msg = "Failed to prepare rate database insertion query.";
            $status_type = "error";
        }
    } else {
        $status_msg = "Please fill in all precious metal rates.";
        $status_type = "error";
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="form-section">
    <div class="form-container" style="max-width: 500px;">
        <!-- Navigation back to admin panel -->
        <div style="margin-bottom: 20px; font-family: var(--font-body); font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;">
            <a href="display.php" style="color: var(--color-warm-gray);">&larr; Back to Dashboard</a>
        </div>

        <h1>Update Market Rates</h1>
        <p class="form-subtitle">Record daily commodity pricing for gold and silver to ensure catalog calculation accuracy.</p>
        
        <?php if (!empty($status_msg)): ?>
            <div style="background-color: <?php echo ($status_type == 'success') ? 'var(--color-success-light)' : 'var(--color-error-light)'; ?>; 
                        color: <?php echo ($status_type == 'success') ? 'var(--color-success)' : 'var(--color-error)'; ?>; 
                        padding: 12px; 
                        border: 1px solid <?php echo ($status_type == 'success') ? '#d0e5d2' : '#eccdd3'; ?>; 
                        border-radius: 2px; 
                        margin-bottom: 20px; 
                        font-size: 0.85rem; 
                        text-align: center;">
                <?php echo htmlspecialchars($status_msg); ?>
            </div>
        <?php endif; ?>
        
        <form action="#" method="post">
            <div class="form-group">
                <label for="date">Effective Date</label>
                <!-- Pre-fill with current date for convenience -->
                <input type="date" id="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gold">Gold Rate (NPR per Tola)</label>
                <input type="text" id="gold" name="gold" class="form-control" placeholder="e.g. 1,02,400" required>
            </div>
            
            <div class="form-group">
                <label for="silver">Silver Rate (NPR per Tola)</label>
                <input type="text" id="silver" name="silver" class="form-control" placeholder="e.g. 1,350" required>
            </div>
            
            <button type="submit" name="submit" class="btn" style="width: 100%;">Record Rates</button>
        </form>
    </div>
</section>

<?php
include 'includes/footer.php';
?>