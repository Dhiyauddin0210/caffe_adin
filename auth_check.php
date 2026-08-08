<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Cek role (opsional)
function isAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

function isKasir() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'kasir';
}

function isChef() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'chef';
}
?>