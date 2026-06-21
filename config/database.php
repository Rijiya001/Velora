<?php
// Velora Luxury E-commerce Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kritika');

// Connect to MySQL
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Select Database
if (!mysqli_select_db($con, DB_NAME)) {
    // If the database does not exist, we try to create it or handle gracefully
    // For now, fail with message
    die("Database selection failed: Database '" . DB_NAME . "' does not exist.");
}

// Set charset to utf8mb4 for unicode character support
mysqli_set_charset($con, 'utf8mb4');
?>
