<?php
// Include Database connection
require_once 'config/database.php';

if (isset($_POST['upload'])) {
    
    // Sanitize and capture input fields
    $user = mysqli_real_escape_string($con, trim($_POST['user']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $mobile = mysqli_real_escape_string($con, trim($_POST['mobile']));
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    $code = mysqli_real_escape_string($con, trim($_POST['code']));
    $jewellery = mysqli_real_escape_string($con, trim($_POST['jewellery']));
    $comments = mysqli_real_escape_string($con, trim($_POST['comments']));
    
    // File Upload handling logic
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        
        // Secure file upload: check file extension
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            // Generate a secure unique name to avoid overwrites
            $unique_name = uniqid("design_", true) . "." . $file_ext;
            $upload_dir = 'upload/';
            
            // Check if upload folder exists, otherwise create it
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $target_file = $upload_dir . $unique_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    // Prepare query using prepared statement for security
    // We handle optional image path insertion
    $query = "INSERT INTO userinfodata (user, email, mobile, address, code, interestedon, image, comment) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $user, $email, $mobile, $address, $code, $jewellery, $image_path, $comments);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to home page or custom page with success indicator
            header('location:index.php?status=success');
        } else {
            echo "Error executing registration query: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing registration query: " . mysqli_error($con);
    }
} else {
    echo "No button has been clicked";
}

mysqli_close($con);
?>
