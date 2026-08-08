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
    
    // Proses upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        $file_name = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validasi file
        $allowed = ['jpg', 'jpeg', 'png'];
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
    
    // Ambil nama gambar untuk dihapus
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
    $gambar = $_POST['gambar_lama'] ?? '';
    
    // Proses upload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        $file_name = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($imageFileType, $allowed)) {
            // Hapus gambar lama
            if (!empty($gambar) && file_exists('uploads/' . $gambar)) {
                unlink('uploads/' . $gambar);
            }
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $file_name;
            }
        }
    }
    
    $sql = "UPDATE menu SET nama_menu=?, kategori_id=?, harga=?, tersedia=?, gambar=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissis", $nama_menu, $kategori_id, $harga, $tersedia, $gambar, $id);
    
    if ($stmt->execute()) {
        header('Location: menu.php?success=1');
    } else {
        header('Location: menu.php?error=' . $conn->error);
    }
}
?>