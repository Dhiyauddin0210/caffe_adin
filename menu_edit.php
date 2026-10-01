<?php
include 'config.php';
$id = $_GET['id'];
$menu = $conn->query("SELECT * FROM menu WHERE id = $id")->fetch_assoc();
$kategoriResult = $conn->query("SELECT * FROM kategori");

$gambar = !empty($menu['gambar']) ? 'uploads/' . $menu['gambar'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f0f4f8] flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-gray-100/50">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-blue-100 p-3 rounded-2xl">
                <i class="fas fa-edit text-blue-600 text-xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Menu</h1>
        </div>
        
        <?php if($gambar && file_exists($gambar)): ?>
            <div class="mb-4 text-center">
                <img src="<?= $gambar ?>" alt="<?= $menu['nama_menu'] ?>" class="w-32 h-32 object-cover rounded-xl mx-auto border-2 border-gray-200">
            </div>
        <?php endif; ?>
        
        <form action="menu_proses.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $menu['id'] ?>">
            <input type="hidden" name="gambar_lama" value="<?= $menu['gambar'] ?>">
            
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" value="<?= $menu['nama_menu'] ?>" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Kategori</label>
                <select name="kategori_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <?php while($k = $kategoriResult->fetch_assoc()) { ?>
                        <option value="<?= $k['id'] ?>" <?= $k['id'] == $menu['kategori_id'] ? 'selected' : '' ?>>
                            <?= $k['nama_kategori'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Harga</label>
                <input type="number" name="harga" value="<?= $menu['harga'] ?>" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Gambar Menu</label>
                <input type="file" name="gambar" accept="image/*" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti gambar</p>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="tersedia" <?= $menu['tersedia'] ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 focus:ring-blue-500 rounded">
                    <span class="text-sm font-medium text-gray-700">Menu Tersedia</span>
                </label>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2.5 rounded-xl transition shadow-lg shadow-blue-500/30 hover:scale-[1.02] active:scale-95">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
                <a href="menu.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 rounded-xl transition text-center">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>