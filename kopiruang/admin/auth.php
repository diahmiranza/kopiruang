<?php
// Auth guard — include at top of every admin page
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminUser = $_SESSION['admin_user'] ?? 'admin';
?>
