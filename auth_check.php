<?php
session_start();

// ===== CEK KONEKSI DATABASE (DITAMBAHKAN) =====
if (!isset($conn)) {
    // Cari file koneksi
    $possible_files = ['config.php', 'koneksi.php', 'db.php', 'database.php'];
    $found = false;
    foreach ($possible_files as $file) {
        if (file_exists($file)) {
            require_once $file;
            $found = true;
            break;
        }
    }
    if (!$found) {
        die("File koneksi database tidak ditemukan! Buat file config.php");
    }
}

// Cek login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil role dari session
$role = $_SESSION['role'] ?? 'kasir';

// ===== REDIRECT OTOMATIS KE DASHBOARD SESUAI ROLE =====
$current_page = basename($_SERVER['PHP_SELF']);

// Jika di index.php, redirect ke dashboard sesuai role
if ($current_page == 'index.php') {
    if ($role == 'admin') {
        header('Location: admin.php');
    } elseif ($role == 'kasir') {
        header('Location: kasir.php');
    } elseif ($role == 'dapur') {
        header('Location: dapur.php');
    } else {
        header('Location: login.php');
    }
    exit;
}

// Fungsi cek akses (hanya untuk role tertentu)
function cekAkses($roles = []) {
    global $role;
    if (!in_array($role, $roles)) {
        if ($role == 'admin') {
            header('Location: admin.php');
        } elseif ($role == 'kasir') {
            header('Location: kasir.php');
        } elseif ($role == 'dapur') {
            header('Location: dapur.php');
        } else {
            header('Location: login.php');
        }
        exit;
    }
}

// Fungsi cek role
function isAdmin() {
    global $role;
    return $role == 'admin';
}

function isKasir() {
    global $role;
    return $role == 'kasir' || $role == 'admin';
}

function isDapur() {
    global $role;
    return $role == 'dapur' || $role == 'admin';
}
?>