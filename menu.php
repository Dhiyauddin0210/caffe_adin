<?php
include 'config.php';
include 'auth_check.php';

// Ambil data menu dengan kategori
$menuResult = $conn->query("
    SELECT m.*, k.nama_kategori 
    FROM menu m 
    LEFT JOIN kategori k ON m.kategori_id = k.id 
    ORDER BY m.id DESC
");

// Ambil data kategori untuk dropdown
$kategoriResult = $conn->query("SELECT * FROM kategori");

// Statistik
$totalMenu = $conn->query("SELECT COUNT(*) as total FROM menu")->fetch_assoc()['total'];
$totalKategori = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar {
            background: linear-gradient(180deg, #1e3a5f 0%, #1a365d 50%, #1e293b 100%);
            box-shadow: 4px 0 30px rgba(0,0,0,0.08);
        }
        .sidebar-item {
            transition: all 0.3s ease;
            padding: 12px 16px;
            border-radius: 12px;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 500;
        }
        .sidebar-item:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background: rgba(255,255,255,0.12);
            color: white;
            box-shadow: inset 3px 0 0 #3b82f6;
        }
        .sidebar-item.logout {
            color: rgba(255,100,100,0.7);
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 20px;
        }
        .sidebar-item.logout:hover {
            background: rgba(255,50,50,0.1);
            color: #ff6b6b;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 18px 22px;
            transition: all 0.3s ease;
            border: 1px solid rgba(59, 130, 246, 0.06);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.08);
        }
        .action-btn {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .action-btn:hover { transform: scale(1.05); }
        .menu-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }
        .menu-img-placeholder {
            width: 50px;
            height: 50px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #94a3b8;
            border: 2px dashed #cbd5e1;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card {
            animation: fadeInUp 0.5s ease forwards;
        }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
    </style>
</head>
<body class="bg-[#f0f4f8]">

<div class="flex min-h-screen">
    <!-- SIDEBAR - HAPUS LINK "LIHAT MENU" KARENA REDUNDANT -->
    <div class="sidebar w-64 p-6 fixed h-full overflow-y-auto z-10">
        <div class="flex items-center gap-3 mb-10">
            <?php 
            $logo = 'assets/logo.png';
            if (file_exists($logo)): 
            ?>
                <img src="<?= $logo ?>" alt="Caffe Adin" class="h-10 w-10 object-contain rounded-xl bg-white/10 p-1.5">
            <?php else: ?>
                <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-mug-saucer text-white text-xl"></i>
                </div>
            <?php endif; ?>
            <span class="text-xl font-bold text-white">Caffe Adin</span>
        </div>
        <nav class="space-y-1.5">
            <a href="admin.php" class="sidebar-item">
                <i class="fas fa-chart-line w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="export.php" class="sidebar-item">
                <i class="fas fa-file-export w-5"></i>
                <span>Export Laporan</span>
            </a>
            <a href="menu.php" class="sidebar-item active">
                <i class="fas fa-utensils w-5"></i>
                <span>Manajemen Menu</span>
            </a>
            <a href="meja.php" class="sidebar-item">
                <i class="fas fa-chair w-5"></i>
                <span>Manajemen Meja</span>
            </a>
            <a href="qr_generator.php" class="sidebar-item">
                <i class="fas fa-qrcode w-5"></i>
                <span>QR Code Meja</span>
            </a>
            <!-- 🔧 FIXED: Hapus link "Lihat Menu" karena redundant di halaman Manajemen Menu -->
            <a href="logout.php" class="sidebar-item logout">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 flex-1 p-6 lg:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800">
                    <i class="fas fa-utensils text-blue-600 mr-2"></i> Manajemen Menu
                </h1>
                <p class="text-gray-500 text-sm">Kelola daftar menu restoran Caffe Adin</p>
            </div>
            <button onclick="showTambah()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl transition flex items-center gap-2 shadow-lg shadow-blue-500/30 font-semibold hover:scale-[1.02] active:scale-95">
                <i class="fas fa-plus"></i> Tambah Menu
            </button>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Menu</p>
                        <p class="text-2xl font-extrabold text-gray-800"><?= $totalMenu ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-utensils text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Kategori</p>
                        <p class="text-2xl font-extrabold text-gray-800"><?= $totalKategori ?></p>
                    </div>
                    <div class="bg-indigo-100 p-2.5 rounded-2xl">
                        <i class="fas fa-tags text-indigo-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Tersedia</p>
                        <p class="text-2xl font-extrabold text-green-600">
                            <?= $conn->query("SELECT COUNT(*) as total FROM menu WHERE tersedia = 1")->fetch_assoc()['total'] ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-2.5 rounded-2xl">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Tambah Menu -->
        <div id="formTambah" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100/50 mb-6 hidden">
            <h3 class="font-bold text-lg text-gray-800 mb-4">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i> Tambah Menu Baru
            </h3>
            <form action="menu_proses.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="action" value="tambah">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Nama Menu</label>
                    <input type="text" name="nama_menu" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Kategori</label>
                    <select name="kategori_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Pilih Kategori</option>
                        <?php $kategoriResult->data_seek(0); while($k = $kategoriResult->fetch_assoc()) { ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Harga</label>
                    <input type="number" name="harga" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div class="col-span-full">
                    <label class="text-sm font-medium text-gray-700 block mb-1">Gambar Menu</label>
                    <input type="file" name="gambar" accept="image/*" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, JPEG. Max: 2MB</p>
                </div>
                <div class="flex items-center gap-3 col-span-full mt-2">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-2.5 rounded-xl transition font-semibold">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                    <button type="button" onclick="hideTambah()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-2.5 rounded-xl transition font-semibold">
                        <i class="fas fa-times mr-2"></i> Batal
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Menu -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-base font-bold text-gray-800">
                    <i class="fas fa-list text-blue-600 mr-2"></i> Daftar Menu
                </h2>
                <span class="text-sm text-gray-500 bg-gray-50 px-4 py-1.5 rounded-xl"><?= $menuResult->num_rows ?> menu</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gambar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Menu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if($menuResult->num_rows > 0): ?>
                            <?php while($row = $menuResult->fetch_assoc()) { 
                                $gambar = !empty($row['gambar']) ? 'uploads/' . $row['gambar'] : '';
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <?php if($gambar && file_exists($gambar)): ?>
                                        <img src="<?= $gambar ?>" alt="<?= $row['nama_menu'] ?>" class="menu-img">
                                    <?php else: ?>
                                        <div class="menu-img-placeholder">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">#<?= $row['id'] ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?= $row['nama_menu'] ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <span class="bg-gray-100 px-3 py-1 rounded-full text-xs"><?= $row['nama_kategori'] ?? 'Tanpa Kategori' ?></span>
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-800">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $row['tersedia'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $row['tersedia'] ? '✅ Tersedia' : '❌ Tidak Tersedia' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="editMenu(<?= $row['id'] ?>)" class="text-blue-500 hover:text-blue-700 mr-3 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="hapusMenu(<?= $row['id'] ?>)" class="text-red-500 hover:text-red-700 transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-utensils text-4xl block mb-3 text-gray-300"></i>
                                    Belum ada menu. Tambahkan menu sekarang!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function showTambah() {
    document.getElementById('formTambah').classList.remove('hidden');
    document.getElementById('formTambah').scrollIntoView({ behavior: 'smooth' });
}
function hideTambah() {
    document.getElementById('formTambah').classList.add('hidden');
}
function hapusMenu(id) {
    if(!confirm('Yakin ingin menghapus menu ini?')) return;
    window.location.href = 'menu_proses.php?action=hapus&id=' + id;
}
function editMenu(id) {
    window.location.href = 'menu_edit.php?id=' + id;
}
</script>
</body>
</html>