<?php
include 'config.php';

// Buat folder uploads jika belum ada
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'tambah') {
    $nama_menu = $_POST['nama_menu'];
    $kategori_id = $_POST['kategori_id'];
    $harga = $_POST['harga'];
    $gambar = '';
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $file_name = time() . '.' . $imageFileType;
        $target_file = $target_dir . $file_name;
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $file_name;
            }
        }
    }
    
    $sql = "INSERT INTO menu (nama_menu, kategori_id, harga, gambar) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $nama_menu, $kategori_id, $harga, $gambar);
    
    if ($stmt->execute()) {
        header('Location: menu.php?success=1');
    } else {
        header('Location: menu.php?error=' . $conn->error);
    }
    
} elseif ($action == 'hapus') {
    $id = $_GET['id'];
    
    $result = $conn->query("SELECT gambar FROM menu WHERE id = $id");
    $row = $result->fetch_assoc();
    if (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])) {
        unlink('uploads/' . $row['gambar']);
    }
    
    $conn->query("DELETE FROM menu WHERE id = $id");
    header('Location: menu.php?success=1');
    
} elseif ($action == 'edit') {
    $id = $_POST['id'];
    $nama_menu = $_POST['nama_menu'];
    $kategori_id = $_POST['kategori_id'];
    $harga = $_POST['harga'];
    $tersedia = isset($_POST['tersedia']) ? 1 : 0;
    $gambar_lama = $_POST['gambar_lama'] ?? '';
    $gambar = $gambar_lama;
    
    // 🔥 CEK APAKAH ADA FILE BARU YANG DIUPLOAD
    $upload_gambar_baru = false;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $file_name = time() . '.' . $imageFileType;
        $target_file = $target_dir . $file_name;
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                // Hapus gambar lama hanya jika upload berhasil
                if (!empty($gambar_lama) && file_exists('uploads/' . $gambar_lama)) {
                    unlink('uploads/' . $gambar_lama);
                }
                $gambar = $file_name;
                $upload_gambar_baru = true;
            }
        }
    }
    
    // 🔥 INI YANG DIPERBAIKI! TIPE DATA BIND_PARAM
    // Format: "siiisi" = string, integer, integer, integer, string, integer
    $sql = "UPDATE menu SET nama_menu=?, kategori_id=?, harga=?, tersedia=?, gambar=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiisi", $nama_menu, $kategori_id, $harga, $tersedia, $gambar, $id);
    
    if ($stmt->execute()) {
        header('Location: menu.php?success=1');
    } else {
        header('Location: menu.php?error=' . $conn->error);
    }
}
?>