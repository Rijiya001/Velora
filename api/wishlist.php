<?php
// Velora Wishlist Toggle API
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

// Auth validation
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to save favorites.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
        exit();
    }

    // Check if already in wishlist
    $stmt = mysqli_prepare($con, "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            // Already saved, remove it
            mysqli_stmt_close($stmt);
            
            $delete_stmt = mysqli_prepare($con, "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $product_id);
                if (mysqli_stmt_execute($delete_stmt)) {
                    log_activity($con, $_SESSION['email'], 'user', "Removed product ID $product_id from wishlist");
                    echo json_encode(['status' => 'removed', 'message' => 'Removed from saved pieces.']);
                }
                mysqli_stmt_close($delete_stmt);
            }
        } else {
            // Not saved, add it
            mysqli_stmt_close($stmt);
            
            $insert_stmt = mysqli_prepare($con, "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $product_id);
                if (mysqli_stmt_execute($insert_stmt)) {
                    log_activity($con, $_SESSION['email'], 'user', "Added product ID $product_id to wishlist");
                    echo json_encode(['status' => 'added', 'message' => 'Saved to your profile.']);
                }
                mysqli_stmt_close($insert_stmt);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to query wishlist status.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
mysqli_close($con);
?>
