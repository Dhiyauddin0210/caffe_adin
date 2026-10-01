<?php
include 'config.php';
include 'auth_check.php';

// Hanya Admin yang bisa akses
cekAkses(['admin']);

// Ambil semua user
$userResult = $conn->query("SELECT * FROM admin ORDER BY role, username");
$totalUser = $userResult->num_rows;

// Statistik per role
$totalAdmin = $conn->query("SELECT COUNT(*) as total FROM admin WHERE role = 'admin'")->fetch_assoc()['total'];
$totalKasir = $conn->query("SELECT COUNT(*) as total FROM admin WHERE role = 'kasir'")->fetch_assoc()['total'];
$totalDapur = $conn->query("SELECT COUNT(*) as total FROM admin WHERE role = 'dapur'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Caffe Adin</title>
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
        .role-badge {
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }
        .role-admin { background: #dbeafe; color: #1e40af; }
        .role-kasir { background: #d1fae5; color: #065f46; }
        .role-dapur { background: #fef3c7; color: #92400e; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card { animation: fadeInUp 0.5s ease forwards; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .user-row { transition: all 0.2s ease; }
        .user-row:hover { background: #f8fafc; }
    </style>
</head>
<body class="bg-[#f0f4f8]">

<div class="flex min-h-screen">
    <?php include 'sidebar.php'; ?>

    <div class="ml-64 flex-1 p-6 lg:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800">
                    <i class="fas fa-users text-blue-600 mr-2"></i> Manajemen User
                </h1>
                <p class="text-gray-500 text-sm">Kelola akun karyawan Caffe Adin</p>
            </div>
            <button onclick="showTambah()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl transition flex items-center gap-2 shadow-lg shadow-blue-500/30 font-semibold hover:scale-[1.02] active:scale-95">
                <i class="fas fa-plus"></i> Tambah User
            </button>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Admin</p>
                        <p class="text-2xl font-extrabold text-blue-600"><?= $totalAdmin ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-user-shield text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Kasir</p>
                        <p class="text-2xl font-extrabold text-green-600"><?= $totalKasir ?></p>
                    </div>
                    <div class="bg-green-100 p-2.5 rounded-2xl">
                        <i class="fas fa-user-tie text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Dapur</p>
                        <p class="text-2xl font-extrabold text-yellow-600"><?= $totalDapur ?></p>
                    </div>
                    <div class="bg-yellow-100 p-2.5 rounded-2xl">
                        <i class="fas fa-user-chef text-yellow-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Tambah User -->
        <div id="formTambah" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100/50 mb-6 hidden">
            <h3 class="font-bold text-lg text-gray-800 mb-4">
                <i class="fas fa-user-plus text-blue-600 mr-2"></i> Tambah User Baru
            </h3>
            <form action="user_proses.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" value="tambah">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Username</label>
                    <input type="text" name="username" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Password</label>
                    <input type="password" name="password" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Role</label>
                    <select name="role" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="kasir">Kasir</option>
                        <option value="dapur">Dapur</option>
                        <option value="admin">Admin</option>
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

        <!-- Tabel User -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-base font-bold text-gray-800">
                    <i class="fas fa-list text-blue-600 mr-2"></i> Daftar User
                </h2>
                <span class="text-sm text-gray-500 bg-gray-50 px-4 py-1.5 rounded-xl"><?= $totalUser ?> user</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if($userResult->num_rows > 0): ?>
                            <?php while($row = $userResult->fetch_assoc()): ?>
                            <tr class="user-row">
                                <td class="px-4 py-3 text-sm text-gray-500">#<?= $row['id'] ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?= $row['username'] ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= $row['nama_lengkap'] ?? '-' ?></td>
                                <td class="px-4 py-3">
                                    <span class="role-badge role-<?= $row['role'] ?>">
                                        <?php if($row['role'] == 'admin'): ?>
                                            🔑 Admin
                                        <?php elseif($row['role'] == 'kasir'): ?>
                                            💳 Kasir
                                        <?php else: ?>
                                            🍳 Dapur
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="editUser(<?= $row['id'] ?>)" class="text-blue-500 hover:text-blue-700 mr-3 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if($row['id'] != $_SESSION['admin_id']): ?>
                                        <button onclick="hapusUser(<?= $row['id'] ?>)" class="text-red-500 hover:text-red-700 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-xs">(Anda)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-users text-4xl block mb-3 text-gray-300"></i>
                                    Belum ada user
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div id="modalEdit" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="font-bold text-lg text-gray-800 mb-4">
            <i class="fas fa-edit text-blue-600 mr-2"></i> Edit User
        </h3>
        <form action="user_proses.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label class="text-sm font-medium text-gray-700 block mb-1">Username</label>
                <input type="text" name="username" id="edit_username" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-3">
                <label class="text-sm font-medium text-gray-700 block mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="edit_nama" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-3">
                <label class="text-sm font-medium text-gray-700 block mb-1">Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="edit_password" placeholder="Kosongkan jika tidak diubah" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Role</label>
                <select name="role" id="edit_role" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                    <option value="dapur">Dapur</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2.5 rounded-xl transition">
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
function hapusUser(id) {
    if(!confirm('Yakin ingin menghapus user ini?')) return;
    window.location.href = 'user_proses.php?action=hapus&id=' + id;
}
function editUser(id) {
    fetch('user_proses.php?action=get&id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_nama').value = data.nama_lengkap;
            document.getElementById('edit_role').value = data.role;
            document.getElementById('modalEdit').classList.remove('hidden');
        })
        .catch(() => alert('Gagal mengambil data user'));
}
function closeModal() {
    document.getElementById('modalEdit').classList.add('hidden');
}
</script>
</body>
</html>