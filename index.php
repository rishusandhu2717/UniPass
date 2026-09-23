<?php
// index.php — Main Entry Router
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: checkin.php");
    }
} else {
    header("Location: login.php");
}
exit();
?>
