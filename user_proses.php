<?php
include 'config.php';
include 'auth_check.php';

// Hanya Admin yang bisa akses
cekAkses(['admin']);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// GET data user (untuk edit modal)
if($action == 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM admin WHERE id = $id");
    if($result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode($result->fetch_assoc());
    }
    exit;
}

// Tambah user
if($action == 'tambah' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $password = md5($_POST['password']);
    $role = $conn->real_escape_string($_POST['role']);
    
    // Cek username sudah ada
    $check = $conn->query("SELECT id FROM admin WHERE username = '$username'");
    if($check->num_rows > 0) {
        header('Location: user_management.php?error=Username sudah ada!');
        exit;
    }
    
    $conn->query("INSERT INTO admin (username, password, nama_lengkap, role) VALUES ('$username', '$password', '$nama_lengkap', '$role')");
    
    header('Location: user_management.php?msg=added');
    exit;
}

// Edit user
if($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $username = $conn->real_escape_string($_POST['username']);
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password'];
    
    // Cek username sudah dipakai user lain
    $check = $conn->query("SELECT id FROM admin WHERE username = '$username' AND id != $id");
    if($check->num_rows > 0) {
        header('Location: user_management.php?error=Username sudah dipakai user lain!');
        exit;
    }
    
    if(!empty($password)) {
        $password = md5($password);
        $conn->query("UPDATE admin SET username = '$username', password = '$password', nama_lengkap = '$nama_lengkap', role = '$role' WHERE id = $id");
    } else {
        $conn->query("UPDATE admin SET username = '$username', nama_lengkap = '$nama_lengkap', role = '$role' WHERE id = $id");
    }
    
    header('Location: user_management.php?msg=updated');
    exit;
}

// Hapus user
if($action == 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Cegah hapus diri sendiri
    if($id == $_SESSION['admin_id']) {
        header('Location: user_management.php?error=Tidak bisa menghapus akun sendiri!');
        exit;
    }
    
    $conn->query("DELETE FROM admin WHERE id = $id");
    header('Location: user_management.php?msg=deleted');
    exit;
}

header('Location: user_management.php');
exit;
?>