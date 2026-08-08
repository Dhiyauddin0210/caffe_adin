<?php
include 'config.php';
include 'auth_check.php';

// Filter Tanggal
$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-d', strtotime('-7 days'));
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');

// Statistik Utama dengan Filter Tanggal
$totalPesanan = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'")->fetch_assoc()['total'];
$totalPendapatan = $conn->query("SELECT SUM(total) as total FROM pesanan WHERE status = 'selesai' AND DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'")->fetch_assoc()['total'] ?? 0;
$menunggu = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'menunggu' AND DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'")->fetch_assoc()['total'];
$diproses = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'diproses' AND DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'")->fetch_assoc()['total'];
$selesai = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'selesai' AND DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'")->fetch_assoc()['total'];
$batal = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'batal' AND DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'")->fetch_assoc()['total'];

// Pesanan dengan Filter Tanggal
$pesanan = $conn->query("SELECT * FROM pesanan 
                         WHERE DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
                         ORDER BY created_at DESC");

// Grafik Penjualan Harian (7 hari terakhir)
$chartData = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($date));
    
    $sql = "SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as pendapatan 
            FROM pesanan 
            WHERE DATE(created_at) = '$date' AND status = 'selesai'";
    $result = $conn->query($sql)->fetch_assoc();
    
    $chartData[] = [
        'tanggal' => $date,
        'total' => (int)$result['total'],
        'pendapatan' => (float)$result['pendapatan']
    ];
}

// Grafik Kategori Terlaris
$kategoriTerlaris = $conn->query("
    SELECT k.nama_kategori, COUNT(d.id) as total_terjual, COALESCE(SUM(d.subtotal), 0) as pendapatan
    FROM detail_pesanan d
    JOIN menu m ON d.menu_id = m.id
    JOIN kategori k ON m.kategori_id = k.id
    JOIN pesanan p ON d.pesanan_id = p.id
    WHERE p.status = 'selesai'
    GROUP BY k.id
    ORDER BY total_terjual DESC
    LIMIT 5
");

// Menu Terlaris
$menuTerlaris = $conn->query("
    SELECT m.nama_menu, COUNT(d.id) as total_terjual, COALESCE(SUM(d.subtotal), 0) as pendapatan
    FROM detail_pesanan d
    JOIN menu m ON d.menu_id = m.id
    JOIN pesanan p ON d.pesanan_id = p.id
    WHERE p.status = 'selesai'
    GROUP BY m.id
    ORDER BY total_terjual DESC
    LIMIT 5
");

// Total Meja
$totalMeja = $conn->query("SELECT COUNT(*) as total FROM meja")->fetch_assoc()['total'];

// Pesanan Hari Ini
$hariIni = date('Y-m-d');
$pesananHariIni = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) = '$hariIni'")->fetch_assoc()['total'];
$pendapatanHariIni = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM pesanan WHERE DATE(created_at) = '$hariIni' AND status = 'selesai'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { 
            font-family: 'Inter', -apple-system, sans-serif;
            scroll-behavior: smooth;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
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
            animation: fadeInUp 0.5s ease forwards;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.08);
            border-color: rgba(59, 130, 246, 0.15);
        }
        .status-badge {
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-menunggu { background: #fef3c7; color: #92400e; }
        .status-diproses { background: #dbeafe; color: #1e40af; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-batal { background: #fee2e2; color: #991b1b; }
        .action-btn {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .action-btn:hover { transform: scale(1.05); }
        .chart-box {
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(59, 130, 246, 0.06);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease forwards;
        }
        .chart-box:hover {
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.06);
        }
        .chart-wrapper {
            position: relative;
            height: 220px;
            width: 100%;
        }
        .refresh-btn {
            background: #f1f5f9;
            padding: 8px 18px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .refresh-btn:hover {
            background: #e2e8f0;
            transform: scale(1.02);
        }
        .catatan-cell {
            max-width: 180px;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            line-height: 1.4;
        }
        .catatan-bubble {
            display: inline-block;
            background: #fef3c7;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            color: #92400e;
            max-width: 160px;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            line-height: 1.4;
        }
        .catatan-bubble i {
            margin-right: 4px;
            font-size: 10px;
        }
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
            background: #f8fafc;
        }
        .filter-box {
            background: white;
            border-radius: 20px;
            padding: 16px 20px;
            border: 1px solid rgba(59, 130, 246, 0.06);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .filter-box input {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 14px;
            font-size: 13px;
            background: #f8fafc;
            transition: all 0.3s ease;
            width: 100%;
            min-width: 140px;
        }
        .filter-box input:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
        }
        .filter-box .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            display: block;
            margin-bottom: 4px;
        }
        .filter-box .filter-group {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
        }
        .filter-box .filter-item {
            min-width: 140px;
            flex: 1;
        }
        .filter-box .filter-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-box .filter-info {
            font-size: 13px;
            color: #94a3b8;
            margin-left: auto;
            padding-top: 4px;
        }
        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 16px 8px; }
            .sidebar span:not(.badge) { display: none; }
            .sidebar-item { justify-content: center; padding: 10px; }
            .ml-64 { margin-left: 60px; }
            .filter-box .filter-group { flex-direction: column; align-items: stretch; }
            .filter-box .filter-item { min-width: 100%; }
            .filter-box .filter-info { margin-left: 0; margin-top: 8px; }
        }
    </style>
</head>
<body class="bg-[#f0f4f8]">

<div class="flex min-h-screen">
    <!-- Sidebar -->
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
            <a href="admin.php" class="sidebar-item active">
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
                <h1 class="text-3xl font-extrabold text-gray-800">Dashboard</h1>
                <p class="text-gray-500 text-sm">Manajemen restoran Caffe Adin</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="location.reload()" class="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
                    <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>
                    <?= date('d F Y H:i') ?>
                </span>
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-500/20">
                    <?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)) ?>
                </div>
            </div>
        </div>

        <!-- Stats Row 1 -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Pesanan</p>
                        <p class="text-2xl font-extrabold text-gray-800"><?= $totalPesanan ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Pendapatan</p>
                        <p class="text-xl font-extrabold text-green-600">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
                    </div>
                    <div class="bg-green-100 p-2.5 rounded-2xl">
                        <i class="fas fa-money-bill-wave text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Menunggu</p>
                        <p class="text-2xl font-extrabold text-yellow-600"><?= $menunggu ?></p>
                    </div>
                    <div class="bg-yellow-100 p-2.5 rounded-2xl">
                        <i class="fas fa-clock text-yellow-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Diproses</p>
                        <p class="text-2xl font-extrabold text-blue-600"><?= $diproses ?></p>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-2xl">
                        <i class="fas fa-spinner text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Selesai</p>
                        <p class="text-2xl font-extrabold text-green-600"><?= $selesai ?></p>
                    </div>
                    <div class="bg-green-100 p-2.5 rounded-2xl">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Batal</p>
                        <p class="text-2xl font-extrabold text-red-600"><?= $batal ?></p>
                    </div>
                    <div class="bg-red-100 p-2.5 rounded-2xl">
                        <i class="fas fa-times-circle text-red-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Row 2 - Hari Ini -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Pesanan Hari Ini</p>
                        <p class="text-2xl font-extrabold text-blue-700"><?= $pesananHariIni ?></p>
                    </div>
                    <div class="bg-blue-200 p-3 rounded-2xl">
                        <i class="fas fa-calendar-day text-blue-700 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Pendapatan Hari Ini</p>
                        <p class="text-xl font-extrabold text-green-700">Rp <?= number_format($pendapatanHariIni, 0, ',', '.') ?></p>
                    </div>
                    <div class="bg-green-200 p-3 rounded-2xl">
                        <i class="fas fa-coins text-green-700 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-gradient-to-r from-indigo-50 to-indigo-100 border-indigo-200">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Meja</p>
                        <p class="text-2xl font-extrabold text-indigo-700"><?= $totalMeja ?></p>
                    </div>
                    <div class="bg-indigo-200 p-3 rounded-2xl">
                        <i class="fas fa-chair text-indigo-700 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="chart-box">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800">
                        <i class="fas fa-chart-bar text-blue-600 mr-2"></i> Penjualan 7 Hari Terakhir
                    </h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="flex justify-center gap-6 mt-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-blue-600 rounded-full"></span>
                        <span class="text-gray-600">Jumlah Pesanan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-blue-300 rounded-full"></span>
                        <span class="text-gray-600">Pendapatan (Rp)</span>
                    </div>
                </div>
            </div>

            <div class="chart-box">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800">
                        <i class="fas fa-pie-chart text-blue-600 mr-2"></i> Kategori Terlaris
                    </h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Menu Terlaris + Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="chart-box">
                <h3 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-crown text-yellow-500 mr-2"></i> Menu Terlaris
                </h3>
                <div class="space-y-3">
                    <?php if ($menuTerlaris->num_rows > 0): ?>
                        <?php $no = 1; while($row = $menuTerlaris->fetch_assoc()): ?>
                            <div class="flex items-center gap-3">
                                <span class="w-6 text-center font-bold text-gray-400 text-sm">#<?= $no++ ?></span>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-gray-700 text-sm"><?= $row['nama_menu'] ?></span>
                                        <span class="text-gray-500 text-xs font-semibold"><?= $row['total_terjual'] ?>x</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-1000" style="width: <?= min(100, ($row['total_terjual'] / 10) * 100) ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-400 text-center py-6 text-sm">Belum ada data penjualan</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chart-box">
                <h3 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i> Ringkasan Cepat
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl hover:bg-blue-50 transition-all">
                        <span class="text-gray-600 text-sm"><i class="fas fa-shopping-bag text-blue-500 mr-2"></i>Total Pesanan</span>
                        <span class="font-bold text-gray-800"><?= $totalPesanan ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl hover:bg-blue-50 transition-all">
                        <span class="text-gray-600 text-sm"><i class="fas fa-money-bill-wave text-green-500 mr-2"></i>Total Pendapatan</span>
                        <span class="font-bold text-green-600">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl hover:bg-blue-50 transition-all">
                        <span class="text-gray-600 text-sm"><i class="fas fa-clock text-yellow-500 mr-2"></i>Menunggu Diproses</span>
                        <span class="font-bold text-yellow-600"><?= $menunggu ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl hover:bg-blue-50 transition-all">
                        <span class="text-gray-600 text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Pesanan Selesai</span>
                        <span class="font-bold text-green-600"><?= $selesai ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl hover:bg-blue-50 transition-all">
                        <span class="text-gray-600 text-sm"><i class="fas fa-chair text-indigo-500 mr-2"></i>Total Meja</span>
                        <span class="font-bold text-indigo-600"><?= $totalMeja ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tanggal - FIXED -->
        <div class="filter-box mb-6" id="pesanan">
            <form method="GET" action="admin.php#pesanan" class="filter-group">
                <div class="filter-item">
                    <label class="filter-label"><i class="fas fa-calendar-alt mr-1 text-blue-500"></i> Dari Tanggal</label>
                    <input type="date" name="tanggal_awal" value="<?= $tanggal_awal ?>">
                </div>
                <div class="filter-item">
                    <label class="filter-label"><i class="fas fa-calendar-alt mr-1 text-blue-500"></i> Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" value="<?= $tanggal_akhir ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-semibold transition flex items-center gap-2 h-[42px]">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="admin.php#pesanan" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-xl font-semibold transition inline-flex items-center gap-2 h-[42px]">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
                <div class="filter-info">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Menampilkan: <span class="font-semibold text-gray-700"><?= date('d M Y', strtotime($tanggal_awal)) ?></span> 
                    - <span class="font-semibold text-gray-700"><?= date('d M Y', strtotime($tanggal_akhir)) ?></span>
                </div>
            </form>
        </div>

        <!-- Tabel Pesanan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <h2 class="text-base font-bold text-gray-800">
                    <i class="fas fa-list text-blue-600 mr-2"></i> Daftar Pesanan
                </h2>
                <span class="text-sm text-gray-500 bg-gray-50 px-4 py-1.5 rounded-xl"><?= $pesanan->num_rows ?> pesanan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pesanan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meja</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if($pesanan->num_rows > 0): ?>
                            <?php while($row = $pesanan->fetch_assoc()): ?>
                            <tr class="table-row">
                                <td class="px-4 py-3 font-mono font-semibold text-xs text-gray-700"><?= $row['nomor_pesanan'] ?></td>
                                <td class="px-4 py-3"><span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1 rounded-full text-xs"><i class="fas fa-chair text-gray-400 text-xs"></i> <?= $row['meja'] ?></span></td>
                                <td class="px-4 py-3 font-bold text-gray-800 text-sm">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td class="px-4 py-3">
                                    <span class="status-badge status-<?= $row['status'] ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= ($row['payment_status'] ?? 'pending') == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ($row['payment_status'] ?? 'pending') == 'paid' ? '✅ Lunas' : '⏳ Pending' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 catatan-cell">
                                    <?php if (!empty($row['catatan'])): ?>
                                        <div class="catatan-bubble">
                                            <i class="fas fa-pen"></i>
                                            <?= htmlspecialchars($row['catatan']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    <?= date('H:i', strtotime($row['created_at'])) ?>
                                    <br><span class="text-xs text-gray-400"><?= date('d M', strtotime($row['created_at'])) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php if ($row['status'] == 'menunggu'): ?>
                                            <button onclick="updateStatus(<?= $row['id'] ?>, 'diproses')" class="action-btn bg-blue-500 text-white hover:bg-blue-600">
                                                <i class="fas fa-play"></i> Proses
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($row['status'] == 'diproses'): ?>
                                            <button onclick="updateStatus(<?= $row['id'] ?>, 'selesai')" class="action-btn bg-green-500 text-white hover:bg-green-600">
                                                <i class="fas fa-check"></i> Selesai
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($row['status'] == 'menunggu' || $row['status'] == 'diproses'): ?>
                                            <button onclick="updateStatus(<?= $row['id'] ?>, 'batal')" class="action-btn bg-red-500 text-white hover:bg-red-600">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                        <?php endif; ?>
                                        <a href="invoice.php?order_id=<?= $row['nomor_pesanan'] ?>" target="_blank" class="action-btn bg-gray-500 text-white hover:bg-gray-600 text-center">
                                            <i class="fas fa-print"></i> Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-inbox text-4xl block mb-3 text-gray-300"></i>
                                    Belum ada pesanan untuk periode ini
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
// ========== GRAFIK PENJUALAN HARIAN ==========
const ctx = document.getElementById('salesChart').getContext('2d');
const salesData = <?= json_encode($chartData) ?>;
const labels = <?= json_encode($labels) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Jumlah Pesanan',
                data: salesData.map(item => item.total),
                backgroundColor: 'rgba(37, 99, 235, 0.6)',
                borderColor: 'rgb(37, 99, 235)',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y'
            },
            {
                label: 'Pendapatan (Rp)',
                data: salesData.map(item => item.pendapatan),
                backgroundColor: 'rgba(147, 197, 253, 0.6)',
                borderColor: 'rgb(147, 197, 253)',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { size: 10 } }
            },
            y1: {
                position: 'right',
                beginAtZero: true,
                grid: { display: false },
                ticks: { font: { size: 10 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 } }
            }
        }
    }
});

// ========== GRAFIK KATEGORI TERLARIS ==========
const ctx2 = document.getElementById('categoryChart').getContext('2d');
const categoryData = <?php 
    $catData = [];
    $kategoriTerlaris->data_seek(0);
    while($row = $kategoriTerlaris->fetch_assoc()) {
        $catData[] = $row;
    }
    echo json_encode($catData);
?>;

new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: categoryData.map(item => item.nama_kategori),
        datasets: [{
            data: categoryData.map(item => item.total_terjual),
            backgroundColor: ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 12,
                    usePointStyle: true,
                    font: { size: 11, weight: '500' }
                }
            }
        },
        cutout: '65%'
    }
});

// ========== UPDATE STATUS ==========
function updateStatus(id, status) {
    if (!confirm('Ubah status pesanan ini menjadi ' + status + '?')) return;
    
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Gagal mengupdate status');
        }
    })
    .catch(() => alert('Terjadi kesalahan'));
}
</script>
</body>
</html>