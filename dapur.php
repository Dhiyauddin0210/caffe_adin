<?php
include 'config.php';
include 'auth_check.php';

// Hanya dapur atau admin yang bisa akses
cekAkses(['dapur', 'admin']);

// Ambil semua pesanan (terbaru di atas)
$pesananResult = $conn->query("SELECT * FROM pesanan ORDER BY 
    FIELD(status, 'menunggu', 'diproses', 'selesai', 'batal'), 
    created_at DESC");

// Statistik
$totalMenunggu = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'menunggu'")->fetch_assoc()['total'];
$totalDiproses = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'diproses'")->fetch_assoc()['total'];
$totalSelesai = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'selesai'")->fetch_assoc()['total'];
$totalPesanan = $conn->query("SELECT COUNT(*) as total FROM pesanan")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dapur - Caffe Adin</title>
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
        .status-badge {
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .status-menunggu { background: #fef3c7; color: #92400e; animation: pulse 2s infinite; }
        .status-diproses { background: #dbeafe; color: #1e40af; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-batal { background: #fee2e2; color: #991b1b; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .order-card {
            background: white;
            border-radius: 16px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .order-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.06);
        }
        .order-card.menunggu {
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
        }
        .order-card.diproses {
            border-left: 4px solid #3b82f6;
            background: #eff6ff;
        }
        .order-card.selesai {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        .btn-status {
            padding: 6px 16px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-status:hover { transform: scale(1.05); }
        .btn-status.proses { background: #2563eb; color: white; }
        .btn-status.proses:hover { background: #1d4ed8; }
        .btn-status.selesai { background: #10b981; color: white; }
        .btn-status.selesai:hover { background: #059669; }
        .btn-status.batal { background: #ef4444; color: white; }
        .btn-status.batal:hover { background: #dc2626; }
        .scrollable { max-height: 600px; overflow-y: auto; }
        .scrollable::-webkit-scrollbar { width: 6px; }
        .scrollable::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .scrollable::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .scrollable::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .order-card { animation: fadeInUp 0.4s ease forwards; }
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 16px 24px;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            z-index: 999;
            transform: translateX(120%);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            max-width: 400px;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { background: linear-gradient(135deg, #059669, #10b981); }
        .toast.error { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .toast.info { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    </style>
</head>
<body class="bg-[#f0f4f8]">

<div class="flex min-h-screen">
    <?php include 'sidebar.php'; ?>

    <div class="ml-64 flex-1 p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800">
                    <i class="fas fa-kitchen-set text-yellow-600 mr-2"></i> Dapur
                </h1>
                <p class="text-gray-500 text-sm">Kelola pesanan masuk dari customer</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-xl shadow-sm">
                    <i class="far fa-calendar mr-2"></i> <?= date('d F Y H:i') ?>
                </span>
                <span class="text-sm bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl font-semibold">
                    <i class="fas fa-user mr-2"></i> <?= $_SESSION['admin_nama'] ?? 'Dapur' ?>
                </span>
            </div>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Pesanan</p>
                        <p class="text-2xl font-extrabold text-gray-800"><?= $totalPesanan ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-shopping-bag text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">🟡 Menunggu</p>
                        <p class="text-2xl font-extrabold text-yellow-600"><?= $totalMenunggu ?></p>
                    </div>
                    <div class="bg-yellow-100 p-2.5 rounded-2xl">
                        <i class="fas fa-clock text-yellow-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">🔵 Diproses</p>
                        <p class="text-2xl font-extrabold text-blue-600"><?= $totalDiproses ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-spinner text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">✅ Selesai</p>
                        <p class="text-2xl font-extrabold text-green-600"><?= $totalSelesai ?></p>
                    </div>
                    <div class="bg-green-100 p-2.5 rounded-2xl">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Pesanan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-base font-bold text-gray-800">
                    <i class="fas fa-list text-blue-600 mr-2"></i> Daftar Pesanan
                </h2>
                <span class="text-sm text-gray-500"><?= $pesananResult->num_rows ?> pesanan</span>
            </div>
            <div class="p-4 scrollable">
                <?php if($pesananResult->num_rows > 0): ?>
                    <?php while($row = $pesananResult->fetch_assoc()): 
                        $statusClass = $row['status'];
                    ?>
                        <div class="order-card <?= $statusClass ?> mb-3">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="font-mono font-bold text-sm text-gray-700">#<?= $row['nomor_pesanan'] ?></span>
                                        <span class="text-xs text-gray-400">|</span>
                                        <span class="text-sm font-medium text-gray-600">
                                            <i class="fas fa-chair mr-1 text-gray-400"></i> Meja <?= $row['meja'] ?>
                                        </span>
                                        <span class="status-badge status-<?= $statusClass ?>">
                                            <?php if($statusClass == 'menunggu'): ?>
                                                🟡 Menunggu
                                            <?php elseif($statusClass == 'diproses'): ?>
                                                🔵 Diproses
                                            <?php elseif($statusClass == 'selesai'): ?>
                                                ✅ Selesai
                                            <?php else: ?>
                                                ❌ Batal
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="font-bold text-gray-800">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                                        <span class="text-xs text-gray-400 ml-3">
                                            <i class="far fa-clock mr-1"></i> <?= date('H:i', strtotime($row['created_at'])) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($row['catatan'])): ?>
                                        <div class="mt-1 text-xs text-yellow-700 bg-yellow-50 inline-block px-3 py-1 rounded-full">
                                            <i class="fas fa-pen mr-1"></i> <?= htmlspecialchars($row['catatan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2 flex-wrap">
                                    <?php if ($row['status'] == 'menunggu'): ?>
                                        <button onclick="updateStatus(<?= $row['id'] ?>, 'diproses')" class="btn-status proses">
                                            <i class="fas fa-play"></i> Ambil & Proses
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($row['status'] == 'diproses'): ?>
                                        <button onclick="updateStatus(<?= $row['id'] ?>, 'selesai')" class="btn-status selesai">
                                            <i class="fas fa-check"></i> Selesai Masak
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($row['status'] == 'menunggu' || $row['status'] == 'diproses'): ?>
                                        <button onclick="updateStatus(<?= $row['id'] ?>, 'batal')" class="btn-status batal">
                                            <i class="fas fa-times"></i> Batal
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($row['status'] == 'selesai'): ?>
                                        <span class="text-green-600 text-sm font-semibold flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Siap diambil
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-400">
                        <i class="fas fa-inbox text-4xl block mb-3 text-gray-300"></i>
                        Belum ada pesanan masuk
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// UPDATE STATUS
// ============================================
function updateStatus(id, status) {
    if (!confirm('Ubah status pesanan ini menjadi ' + status + '?')) return;
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Status berhasil diupdate!', 'success');
            setTimeout(() => { location.reload(); }, 500);
        } else {
            showToast('❌ Gagal update status', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Coba Lagi';
        }
    })
    .catch(() => {
        showToast('❌ Terjadi kesalahan', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Coba Lagi';
    });
}

// ============================================
// TOAST NOTIFICATION
// ============================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(() => { toast.classList.add('show'); }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => { toast.remove(); }, 500);
    }, 4000);
}

// ============================================
// AUTO REFRESH (30 detik)
// ============================================
let autoRefresh = setInterval(() => {
    location.reload();
}, 30000);

// Pause auto-refresh saat user interaksi
document.addEventListener('click', () => {
    clearInterval(autoRefresh);
    autoRefresh = setInterval(() => {
        location.reload();
    }, 30000);
});
</script>
</body>
</html>