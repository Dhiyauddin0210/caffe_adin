<?php
include 'config.php';
include 'auth_check.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// GET data meja (untuk edit modal)
if($action == 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM meja WHERE id = $id");
    if($result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode($result->fetch_assoc());
    }
    exit;
}

// Tambah meja
if($action == 'tambah' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_meja = $conn->real_escape_string($_POST['nomor_meja']);
    $kapasitas = intval($_POST['kapasitas']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("INSERT INTO meja (nomor_meja, kapasitas, status) VALUES ('$nomor_meja', $kapasitas, '$status')");
    
    header('Location: meja.php?msg=added');
    exit;
}

// Edit meja
if($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nomor_meja = $conn->real_escape_string($_POST['nomor_meja']);
    $kapasitas = intval($_POST['kapasitas']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("UPDATE meja SET nomor_meja = '$nomor_meja', kapasitas = $kapasitas, status = '$status' WHERE id = $id");
    
    header('Location: meja.php?msg=updated');
    exit;
}

// Hapus meja
if($action == 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM meja WHERE id = $id");
    header('Location: meja.php?msg=deleted');
    exit;
}

// Toggle status meja (kosong <-> terisi)
if($action == 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE meja SET status = IF(status = 'kosong', 'terisi', 'kosong') WHERE id = $id");
    header('Location: meja.php?msg=toggled');
    exit;
}

// Jika aksi tidak dikenal
header('Location: meja.php');
exit;
?>