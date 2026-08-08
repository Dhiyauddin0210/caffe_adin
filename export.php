<?php
include 'config.php';
include 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Laporan - Caffe Adin</title>
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
        .export-card {
            background: white;
            border-radius: 24px;
            padding: 32px;
            border: 1px solid rgba(59, 130, 246, 0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        .btn-export {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            padding: 14px 32px;
            border-radius: 14px;
            border: none;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-export:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        input, select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 16px;
            font-size: 14px;
            background: #f8fafc;
            width: 100%;
        }
        input:focus, select:focus {
            border-color: #2563eb;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            display: block;
            margin-bottom: 4px;
        }
        .stat-box {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
        }
        .stat-box .number {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
        }
        .stat-box .label {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .export-card { animation: fadeInUp 0.5s ease forwards; }
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
            <a href="admin.php" class="sidebar-item">
                <i class="fas fa-chart-line w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="export.php" class="sidebar-item active">
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
                    <i class="fas fa-file-export text-blue-600 mr-2"></i> Export Laporan
                </h1>
                <p class="text-gray-500 text-sm">Download laporan penjualan ke Excel / CSV</p>
            </div>
        </div>

        <?php
        // Statistik
        $totalPesanan = $conn->query("SELECT COUNT(*) as total FROM pesanan")->fetch_assoc()['total'];
        $totalPendapatan = $conn->query("SELECT SUM(total) as total FROM pesanan WHERE status = 'selesai'")->fetch_assoc()['total'] ?? 0;
        $pesananSelesai = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'selesai'")->fetch_assoc()['total'];
        ?>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="stat-box">
                <div class="number"><?= $totalPesanan ?></div>
                <div class="label">Total Pesanan</div>
            </div>
            <div class="stat-box">
                <div class="number text-green-600">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
                <div class="label">Total Pendapatan</div>
            </div>
            <div class="stat-box">
                <div class="number text-blue-600"><?= $pesananSelesai ?></div>
                <div class="label">Pesanan Selesai</div>
            </div>
        </div>

        <!-- Export Card -->
        <div class="export-card">
            <h2 class="text-xl font-bold text-gray-800 mb-6">
                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i> Pilih Periode Laporan
            </h2>

            <form action="export_excel.php" method="GET" target="_blank" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label>Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="<?= date('Y-m-d', strtotime('-30 days')) ?>" required>
                </div>
                <div>
                    <label>Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-export w-full justify-center">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </form>

            <div class="mt-6 p-4 bg-blue-50 rounded-2xl border border-blue-200 flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm text-blue-700 font-medium">Info Export</p>
                    <p class="text-sm text-blue-600">File akan di-download dalam format <strong>CSV</strong> (dapat dibuka di Microsoft Excel). Data yang diexport sesuai periode yang dipilih.</p>
                </div>
            </div>

            <!-- Export Semua -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Export Semua Data</h3>
                <a href="export_excel.php?tanggal_awal=2020-01-01&tanggal_akhir=<?= date('Y-m-d') ?>" target="_blank" class="btn-export bg-gray-600 hover:bg-gray-700" style="background: linear-gradient(135deg, #475569, #64748b);">
                    <i class="fas fa-download"></i> Export Semua Data
                </a>
            </div>
        </div>

        <!-- Preview Data -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-base font-bold text-gray-800">
                    <i class="fas fa-table text-blue-600 mr-2"></i> Preview Data (10 Terbaru)
                </h2>
                <span class="text-sm text-gray-500">Data siap diexport</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor Pesanan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meja</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $preview = $conn->query("SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 10");
                        $no = 1;
                        while($row = $preview->fetch_assoc()): 
                        ?>
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 text-xs"><?= $no++ ?></td>
                            <td class="px-4 py-3 font-mono font-semibold text-xs"><?= $row['nomor_pesanan'] ?></td>
                            <td class="px-4 py-3">Meja <?= $row['meja'] ?></td>
                            <td class="px-4 py-3 font-bold text-gray-800">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    <?= $row['status'] == 'menunggu' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                    <?= $row['status'] == 'diproses' ? 'bg-blue-100 text-blue-800' : '' ?>
                                    <?= $row['status'] == 'selesai' ? 'bg-green-100 text-green-800' : '' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($preview->num_rows == 0): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                <i class="fas fa-inbox text-3xl block mb-2 text-gray-300"></i>
                                Belum ada data pesanan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>