<?php
// Velora Inquiry Submission API Handler
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $product_code = isset($_POST['product_code']) ? trim($_POST['product_code']) : null;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields missing. Name, email, and message are required.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address format.']);
        exit();
    }

    // Insert into contact_messages table
    $stmt = mysqli_prepare($con, "INSERT INTO contact_messages (name, email, phone, product_code, message) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $product_code, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            // Optional: log user activity if authenticated
            if (isset($_SESSION['email'])) {
                log_activity($con, $_SESSION['email'], $_SESSION['role'], "Submitted custom product inquiry for Code " . ($product_code ?? 'None'));
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Inquiry submitted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute query: ' . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare database statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
mysqli_close($con);
?>
