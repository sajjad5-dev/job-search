<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        die("<h2 style='font-family:Arial;color:red;padding:30px;'>
             Access Denied. You must be a <b>$role</b> to view this page.
             <br><br><a href='index.php'>Go Home</a></h2>");
    }
}
?>
