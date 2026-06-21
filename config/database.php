<?php
// Velora Luxury Jewelry Showcase Platform - Database Configuration & Auto-Installer
// Compatible with local XAMPP MySQL setup

// Configure session safety parameters globally
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Secure cookies should be enabled in production
    // ini_set('session.cookie_secure', 1);
    session_start();
}

// Dynamic relative path calculator for pages at different directory depths
$root_dir = realpath(dirname(__DIR__));
$current_dir = realpath(getcwd());
$relative_diff = str_replace($root_dir, '', $current_dir);
// Standardize path separators for Windows
$relative_diff = str_replace('\\', '/', $relative_diff);
$depth = substr_count(trim($relative_diff, '/'), '/');
if (empty(trim($relative_diff, '/'))) {
    $depth = 0;
} else {
    $depth = $depth + 1;
}
$rel_path = str_repeat('../', $depth);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kritika');

// Connect to MySQL server (no database selected first, to handle database creation)
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$con) {
    die("Database Connection Error: " . mysqli_connect_error());
}

// Create database if not exists
$db_create_query = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!mysqli_query($con, $db_create_query)) {
    die("Failed to create database: " . mysqli_error($con));
}

// Select database
if (!mysqli_select_db($con, DB_NAME)) {
    die("Failed to select database: " . mysqli_error($con));
}

// Set encoding
mysqli_set_charset($con, 'utf8mb4');

// Auto-Installer Logic: Check if 'users' table exists. If not, import schema.sql automatically.
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) == 0) {
    $schema_path = dirname(__DIR__) . '/database/schema.sql';
    
    if (file_exists($schema_path)) {
        $sql_content = file_get_contents($schema_path);
        
        // Remove comments
        $sql_content = preg_replace('/--.*\n/', '', $sql_content);
        $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
        
        // Split queries by semicolon (ensuring semicolons inside strings aren't broken, simple parser)
        $queries = explode(";\n", $sql_content);
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!mysqli_query($con, $query)) {
                    // Log error or continue
                    error_log("Auto-Installer Error running query: " . mysqli_error($con) . " (Query: $query)");
                }
            }
        }
    }
}

// Clean helper functions for global protection

/**
 * XSS Clean helper to sanitize output strings
 */
function xss_clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Token generation
 */
function get_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF Security Validation Failed.");
    }
    return true;
}

/**
 * Fetch a website setting from the database dynamically
 */
function get_setting($con, $key, $default = '') {
    $stmt = mysqli_prepare($con, "SELECT setting_value FROM website_settings WHERE setting_key = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $key);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $val);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $val !== null ? $val : $default;
    }
    return $default;
}

/**
 * Log user/admin activity
 */
function log_activity($con, $user_id, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt = mysqli_prepare($con, "INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $action, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
?>
