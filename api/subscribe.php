<?php
// Velora Subscription API Handler
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($name) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in both name and email.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address format.']);
        exit();
    }

    // Check if email already subscribed
    $check_stmt = mysqli_prepare($con, "SELECT id FROM subscribers WHERE email = ? LIMIT 1");
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This email is already subscribed.']);
            mysqli_stmt_close($check_stmt);
            exit();
        }
        mysqli_stmt_close($check_stmt);
    }

    // Insert subscriber
    $insert_stmt = mysqli_prepare($con, "INSERT INTO subscribers (name, email) VALUES (?, ?)");
    if ($insert_stmt) {
        mysqli_stmt_bind_param($insert_stmt, "ss", $name, $email);
        if (mysqli_stmt_execute($insert_stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Welcome to the Velora Club!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error occurred. Please try again.']);
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare subscription query.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
mysqli_close($con);
?>
