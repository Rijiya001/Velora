<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    // Configure session cookies for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " | Velora Jewelry" : "Velora | Luxury Jewelry & Fine Accessories"; ?></title>
    <meta name="description" content="Discover Velora Luxury Jewelry. Crafted for timeless elegance, using conflict-free diamonds and ethically sourced gold. Shop our signature collections today.">
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Inline styles for dynamic features or fine overrides if needed -->
</head>
<body>
