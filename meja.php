<?php
include 'config.php';
include 'auth_check.php';

// Ambil semua data meja
$mejaResult = $conn->query("SELECT * FROM meja ORDER BY nomor_meja");

// Statistik
$totalMeja = $conn->query("SELECT COUNT(*) as total FROM meja")->fetch_assoc()['total'];
$mejaKosong = $conn->query("SELECT COUNT(*) as total FROM meja WHERE status = 'kosong'")->fetch_assoc()['total'];
$mejaTerisi = $conn->query("SELECT COUNT(*) as total FROM meja WHERE status = 'terisi'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Meja - Caffe Adin</title>
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
        .meja-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(59, 130, 246, 0.06);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            text-align: center;
        }
        .meja-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.08);
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card, .meja-card {
            animation: fadeInUp 0.5s ease forwards;
        }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .modal {
            transition: opacity 0.3s ease;
        }
        .modal.hidden {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-[#f0f4f8]">

<div class="flex min-h-screen">
    <!-- SIDEBAR - SAMA UNTUK SEMUA HALAMAN -->
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
            <a href="menu.php" class="sidebar-item">
                <i class="fas fa-utensils w-5"></i>
                <span>Manajemen Menu</span>
            </a>
            <a href="meja.php" class="sidebar-item active">
                <i class="fas fa-chair w-5"></i>
                <span>Manajemen Meja</span>
            </a>
            <a href="qr_generator.php" class="sidebar-item">
                <i class="fas fa-qrcode w-5"></i>
                <span>QR Code Meja</span>
            </a>
            <a href="index.php" class="sidebar-item">
                <i class="fas fa-store w-5"></i>
                <span>Lihat Menu</span>
            </a>
            <a href="logout.php" class="sidebar-item logout">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 flex-1 p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800">
                    <i class="fas fa-chair text-blue-600 mr-2"></i> Manajemen Meja
                </h1>
                <p class="text-gray-500 text-sm">Kelola daftar meja restoran Caffe Adin</p>
            </div>
            <button onclick="showTambah()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl transition flex items-center gap-2 shadow-lg shadow-blue-500/30 font-semibold hover:scale-[1.02] active:scale-95">
                <i class="fas fa-plus"></i> Tambah Meja
            </button>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Meja</p>
                        <p class="text-2xl font-extrabold text-gray-800"><?= $totalMeja ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-chair text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Meja Kosong</p>
                        <p class="text-2xl font-extrabold text-green-600"><?= $mejaKosong ?></p>
                    </div>
                    <div class="bg-green-100 p-2.5 rounded-2xl">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Meja Terisi</p>
                        <p class="text-2xl font-extrabold text-red-600"><?= $mejaTerisi ?></p>
                    </div>
                    <div class="bg-red-100 p-2.5 rounded-2xl">
                        <i class="fas fa-times-circle text-red-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Tambah Meja -->
        <div id="formTambah" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100/50 mb-6 hidden">
            <h3 class="font-bold text-lg text-gray-800 mb-4">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i> Tambah Meja Baru
            </h3>
            <form action="meja_proses.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="action" value="tambah">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Nomor Meja</label>
                    <input type="text" name="nomor_meja" placeholder="Contoh: 1, A1, VIP-1" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Kapasitas</label>
                    <input type="number" name="kapasitas" value="2" min="1" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="kosong">Kosong</option>
                        <option value="terisi">Terisi</option>
                    </select>
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

        <!-- Daftar Meja -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if($mejaResult->num_rows > 0): ?>
                <?php while($meja = $mejaResult->fetch_assoc()): ?>
                <div class="meja-card">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs text-gray-400">#<?= $meja['id'] ?></span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold 
                            <?= $meja['status'] == 'kosong' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= ucfirst($meja['status']) ?>
                        </span>
                    </div>
                    <div class="text-4xl font-extrabold text-gray-800 my-2">
                        <?= $meja['nomor_meja'] ?>
                    </div>
                    <p class="text-sm text-gray-500 mb-3">
                        <i class="fas fa-users mr-1"></i> <?= $meja['kapasitas'] ?> orang
                    </p>
                    <div class="flex gap-2 justify-center">
                        <button onclick="editMeja(<?= $meja['id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm transition">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="hapusMeja(<?= $meja['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm transition">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button onclick="toggleStatus(<?= $meja['id'] ?>)" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-lg text-sm transition">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12 text-gray-400">
                    <i class="fas fa-chair text-4xl block mb-3 text-gray-300"></i>
                    Belum ada meja. Tambahkan meja sekarang!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Edit Meja -->
<div id="modalEdit" class="modal hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl transform transition-all scale-95">
        <h3 class="font-bold text-lg text-gray-800 mb-4">
            <i class="fas fa-edit text-blue-600 mr-2"></i> Edit Meja
        </h3>
        <form action="meja_proses.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label class="text-sm font-medium text-gray-700 block mb-1">Nomor Meja</label>
                <input type="text" name="nomor_meja" id="edit_nomor" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-3">
                <label class="text-sm font-medium text-gray-700 block mb-1">Kapasitas</label>
                <input type="number" name="kapasitas" id="edit_kapasitas" min="1" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Status</label>
                <select name="status" id="edit_status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="kosong">Kosong</option>
                    <option value="terisi">Terisi</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2.5 rounded-xl transition shadow-lg shadow-blue-500/30">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 rounded-xl transition">
                    <i class="fas fa-times mr-2"></i> Batal
                </button>
            </div>
        </form>
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
function hapusMeja(id) {
    if(!confirm('Yakin ingin menghapus meja ini?')) return;
    window.location.href = 'meja_proses.php?action=hapus&id=' + id;
}
function toggleStatus(id) {
    if(!confirm('Ubah status meja ini?')) return;
    window.location.href = 'meja_proses.php?action=toggle&id=' + id;
}
function editMeja(id) {
    fetch('meja_proses.php?action=get&id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_nomor').value = data.nomor_meja;
            document.getElementById('edit_kapasitas').value = data.kapasitas;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('modalEdit').classList.remove('hidden');
        })
        .catch(() => {
            alert('Gagal mengambil data meja');
        });
}
function closeModal() {
    document.getElementById('modalEdit').classList.add('hidden');
}
// Tutup modal klik di luar
document.getElementById('modalEdit').addEventListener('click', function(e) {
    if(e.target === this) closeModal();
});
</script>
</body>
</html>