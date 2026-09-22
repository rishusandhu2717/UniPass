<?php
// includes/auth.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to check if user has admin role
function requireAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        // Redirect non-admins trying to access admin pages
        header("Location: checkin.php");
        exit();
    }
}
?>
