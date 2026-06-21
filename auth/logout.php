<?php
// Velora Logout Controller
require_once dirname(__DIR__) . '/config/database.php';

if (isset($_SESSION['email'])) {
    log_activity($con, $_SESSION['email'], $_SESSION['role'], "User logged out");
}

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
