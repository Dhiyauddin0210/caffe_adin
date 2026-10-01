<?php
include 'config.php';
include 'auth_check.php';

// Hanya kasir atau admin yang bisa akses
cekAkses(['kasir', 'admin']);

// Ambil data menu
$menuResult = $conn->query("SELECT * FROM menu WHERE tersedia = 1 ORDER BY kategori_id, nama_menu");

// Ambil data meja kosong
$mejaResult = $conn->query("SELECT * FROM meja WHERE status = 'kosong' ORDER BY nomor_meja");

// Ambil kategori untuk filter
$kategoriResult = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");

// Statistik hari ini
$hariIni = date('Y-m-d');
$totalPesananHariIni = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) = '$hariIni'")->fetch_assoc()['total'];
$pendapatanHariIni = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM pesanan WHERE DATE(created_at) = '$hariIni' AND status = 'selesai'")->fetch_assoc()['total'];
$totalMejaKosong = $conn->query("SELECT COUNT(*) as total FROM meja WHERE status = 'kosong'")->fetch_assoc()['total'];

// Pesanan terbaru
$pesananTerbaru = $conn->query("SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { 
            font-family: 'Inter', -apple-system, sans-serif;
            scroll-behavior: smooth;
        }

        /* ===== BACKGROUND PATTERN ===== */
        body {
            background: #f0f4f8;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(30, 58, 95, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: linear-gradient(180deg, #0f1a2e 0%, #1a2d4a 50%, #1e3a5f 100%);
            box-shadow: 8px 0 40px rgba(0,0,0,0.15);
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-item {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 12px 16px;
            border-radius: 14px;
            color: rgba(255,255,255,0.55);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #fbbf24;
            transform: scaleY(0);
            transform-origin: center;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-item:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(6px);
        }
        .sidebar-item:hover::before {
            transform: scaleY(1);
        }
        .sidebar-item.active {
            background: rgba(251, 191, 36, 0.1);
            color: white;
        }
        .sidebar-item.active::before {
            transform: scaleY(1);
        }
        .sidebar-item.logout {
            color: rgba(255,100,100,0.5);
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 20px;
        }
        .sidebar-item.logout:hover {
            background: rgba(255,50,50,0.1);
            color: #ff9999;
        }

        /* ===== HEADER / NAVBAR ===== */
        .header-premium {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%);
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 12px 40px rgba(30, 58, 95, 0.15);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
        }
        .header-premium::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.05), transparent);
            animation: shimmer 3s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes shimmer {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(30, 58, 95, 0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .stat-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 20px 60px rgba(30, 58, 95, 0.12);
            border-color: rgba(30, 58, 95, 0.15);
        }
        .stat-card:hover::before {
            opacity: 1;
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .stat-label {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ===== MENU GRID ===== */
        .menu-btn {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .menu-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        .menu-btn:hover::before {
            transform: translateX(100%);
        }
        .menu-btn:hover {
            border-color: #2563eb;
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 16px 40px rgba(37, 99, 235, 0.15);
        }
        .menu-btn:active {
            transform: scale(0.97);
        }
        .menu-name {
            font-weight: 700;
            font-size: 15px;
            color: #1e293b;
            line-height: 1.4;
        }
        .menu-price {
            font-weight: 800;
            font-size: 18px;
            color: #1e3a5f;
            margin-top: 6px;
        }
        .menu-category {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 8px;
        }
        .menu-add-icon {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(30, 58, 95, 0.2);
            transition: all 0.3s ease;
        }
        .menu-btn:hover .menu-add-icon {
            transform: scale(1.15) rotate(90deg);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        /* ===== CART STYLING ===== */
        .cart-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(30, 58, 95, 0.06);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }
        .cart-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            color: white;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .cart-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(45deg, rgba(255,255,255,0.05) 0%, transparent 50%, rgba(255,255,255,0.05) 100%);
            animation: shimmer 3s ease-in-out infinite;
        }
        .cart-items-container {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        .cart-items-container::-webkit-scrollbar {
            width: 6px;
        }
        .cart-items-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .cart-items-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .cart-items-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .cart-item {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transition: all 0.25s ease;
            position: relative;
        }
        .cart-item:hover {
            border-color: #2563eb;
            background: #f0f9ff;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.1);
            transform: translateX(4px);
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            border-radius: 12px;
            padding: 4px 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qty-btn.minus {
            background: #e2e8f0;
            color: #475569;
        }
        .qty-btn.minus:hover {
            background: #cbd5e1;
            transform: scale(1.1);
        }
        .qty-btn.plus {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
        }
        .qty-btn.plus:hover {
            box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3);
            transform: scale(1.1);
        }

        /* ===== CART FOOTER ===== */
        .cart-footer {
            background: white;
            border-top: 2px solid #f1f5f9;
            padding: 20px;
            gap: 12px;
            display: flex;
            flex-direction: column;
        }
        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-top: 2px solid #f1f5f9;
            border-bottom: 2px solid #f1f5f9;
        }
        .cart-total-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }
        .cart-total-value {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a5f;
            letter-spacing: -0.5px;
        }

        /* ===== BUTTONS ===== */
        .btn-primary {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            font-weight: 700;
            padding: 14px;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(30, 58, 95, 0.2);
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        .btn-primary:hover::before {
            transform: translateX(100%);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(30, 58, 95, 0.3);
        }
        .btn-primary:active {
            transform: scale(0.97);
        }
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-secondary {
            background: transparent;
            color: #dc2626;
            border: 1.5px solid #fecaca;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-secondary:hover {
            background: #fee2e2;
            border-color: #dc2626;
            color: #991b1b;
        }

        /* ===== CATEGORY FILTER ===== */
        .category-btn {
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            border: 2px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .category-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.1);
        }
        .category-btn.active {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            border-color: transparent;
            box-shadow: 0 8px 25px rgba(30, 58, 95, 0.2);
        }

        /* ===== TABLE ===== */
        .table-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(30, 58, 95, 0.06);
            overflow: hidden;
        }
        .table-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 20px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-wrap {
            overflow-x: auto;
        }
        .table-wrap table {
            min-width: 100%;
        }
        table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        table th {
            padding: 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        table tbody tr {
            transition: all 0.2s ease;
        }
        table tbody tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .status-menunggu {
            background: #fef3c7;
            color: #92400e;
        }
        .status-diproses {
            background: #dbeafe;
            color: #0c4a6e;
        }
        .status-selesai {
            background: #d1fae5;
            color: #065f46;
        }
        .status-batal {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ===== TOAST ===== */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 16px 28px;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            z-index: 999;
            transform: translateY(120px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            max-width: 420px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        .toast.success {
            background: linear-gradient(135deg, #059669, #10b981);
        }
        .toast.error {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .empty-state-icon {
            font-size: 64px;
            color: #e2e8f0;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        .empty-state-text {
            color: #94a3b8;
            font-size: 15px;
            font-weight: 500;
        }

        /* ===== SCROLLBAR ===== */
        .scrollable {
            max-height: 480px;
            overflow-y: auto;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 12px;
            }
            .sidebar-item span {
                display: none;
            }
            .ml-64 {
                margin-left: 70px;
            }
            .stat-value {
                font-size: 24px;
            }
        }

        /* ===== PULSE ANIMATION ===== */
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
            margin-right: 6px;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.6;
                transform: scale(0.8);
            }
        }
    </style>
</head>
<body>

<div class="flex min-h-screen relative z-10">
    <?php include 'sidebar.php'; ?>

    <div class="ml-64 flex-1 p-6 lg:p-8">
        
        <!-- ===== HEADER / NAVBAR ===== -->
        <header class="header-premium relative rounded-2xl mb-8 px-6 py-5 text-white">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
                <!-- Left -->
                <div class="flex items-center gap-4">
                    <?php 
                    $logo = 'assets/logo.png';
                    if (file_exists($logo)): 
                    ?>
                        <img src="<?= $logo ?>" alt="Caffe Adin" class="h-12 w-12 object-contain rounded-xl bg-white/15 p-1.5 backdrop-blur-sm">
                    <?php else: ?>
                        <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                            <i class="fas fa-mug-saucer text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-2xl font-extrabold">Caffe Adin</h1>
                        <p class="text-sm text-white/70 flex items-center gap-2">
                            <span class="pulse-dot"></span> Sistem Kasir Aktif
                        </p>
                    </div>
                </div>

                <!-- Right -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2.5 rounded-xl flex items-center gap-2.5">
                        <i class="far fa-clock text-white/70"></i>
                        <span class="text-sm font-medium" id="realtimeDate"><?= date('d F Y H:i') ?></span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2.5 rounded-xl flex items-center gap-2.5">
                        <i class="fas fa-user-circle text-white/70 text-lg"></i>
                        <span class="text-sm font-semibold"><?= $_SESSION['admin_nama'] ?? 'Kasir' ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- ===== STATISTIK CARDS ===== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Pesanan Hari Ini -->
            <div class="stat-card fade-in-up" style="animation-delay: 0.05s;">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <p class="stat-label text-gray-500">Pesanan Hari Ini</p>
                        <p class="stat-value text-gray-800 mt-2"><?= $totalPesananHariIni ?></p>
                    </div>
                    <div class="stat-icon bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
            </div>

            <!-- Pendapatan Hari Ini -->
            <div class="stat-card fade-in-up" style="animation-delay: 0.1s;">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <p class="stat-label text-gray-500">Pendapatan Hari Ini</p>
                        <p class="stat-value text-emerald-600 mt-2">Rp <?= number_format($pendapatanHariIni, 0, ',', '.') ?></p>
                    </div>
                    <div class="stat-icon bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>

            <!-- Meja Kosong -->
            <div class="stat-card fade-in-up" style="animation-delay: 0.15s;">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <p class="stat-label text-gray-500">Meja Kosong</p>
                        <p class="stat-value text-gray-800 mt-2"><?= $totalMejaKosong ?></p>
                    </div>
                    <div class="stat-icon bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600">
                        <i class="fas fa-chair"></i>
                    </div>
                </div>
            </div>

            <!-- Total Menu -->
            <div class="stat-card fade-in-up" style="animation-delay: 0.2s;">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <p class="stat-label text-white/70">Total Menu</p>
                        <p class="stat-value text-white mt-2"><?= $conn->query("SELECT COUNT(*) as total FROM menu WHERE tersedia = 1")->fetch_assoc()['total'] ?></p>
                    </div>
                    <div class="stat-icon bg-white/20 text-white">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
                <style>
                    .stat-card:nth-child(4) {
                        background: linear-gradient(135deg, #1e3a5f, #2563eb);
                    }
                </style>
            </div>
        </div>

        <!-- ===== MAIN CONTENT (2 KOLOM) ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-7 gap-6 mb-8">
            
            <!-- KOLOM KIRI: MENU GRID -->
            <div class="lg:col-span-4 bg-white rounded-2xl shadow-md border border-gray-100/50 p-6 flex flex-col">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-utensils text-blue-600"></i> Pilih Menu
                        <span class="text-xs font-normal text-gray-400 bg-gray-100 px-3 py-1.5 rounded-full">Klik untuk menambah</span>
                    </h2>
                    
                    <!-- Category Filter -->
                    <div class="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
                        <button onclick="filterKategori('semua')" class="category-btn active" data-kat="semua">
                            <i class="fas fa-th-large mr-1.5"></i> Semua
                        </button>
                        <?php 
                        $kategoriResult->data_seek(0);
                        while($kat = $kategoriResult->fetch_assoc()): 
                        ?>
                            <button onclick="filterKategori('<?= $kat['id'] ?>')" class="category-btn" data-kat="<?= $kat['id'] ?>">
                                <i class="fas fa-tag mr-1.5"></i> <?= $kat['nama_kategori'] ?>
                            </button>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Menu Grid -->
                <div class="scrollable grid grid-cols-2 gap-3 flex-1">
                    <div id="menuGrid" class="contents">
                        <?php 
                        $menuResult->data_seek(0);
                        while($menu = $menuResult->fetch_assoc()): 
                            $kategoriNama = '';
                            $kategoriResult->data_seek(0);
                            while($kat = $kategoriResult->fetch_assoc()) {
                                if($kat['id'] == $menu['kategori_id']) {
                                    $kategoriNama = $kat['nama_kategori'];
                                    break;
                                }
                            }
                        ?>
                            <button onclick="tambahItem(<?= $menu['id'] ?>, '<?= addslashes($menu['nama_menu']) ?>', <?= $menu['harga'] ?>)" 
                                    class="menu-btn relative" data-kat="<?= $menu['kategori_id'] ?>">
                                <div class="pr-12">
                                    <div class="menu-name"><?= $menu['nama_menu'] ?></div>
                                    <div class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></div>
                                    <span class="menu-category"><?= $kategoriNama ?></span>
                                </div>
                                <div class="menu-add-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                            </button>
                        <?php endwhile; ?>
                    </div>
                    <?php if($menuResult->num_rows == 0): ?>
                        <div class="col-span-2 empty-state">
                            <div class="empty-state-icon"><i class="fas fa-utensils"></i></div>
                            <div class="empty-state-text">Belum ada menu tersedia</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KOLOM KANAN: KERANJANG -->
            <div class="lg:col-span-3 cart-card fade-in-up" style="animation-delay: 0.25s;">
                <!-- Header -->
                <div class="cart-header relative">
                    <div class="flex justify-between items-center relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center border border-white/30">
                                <i class="fas fa-shopping-cart text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Keranjang</h3>
                                <p id="cartItemCount" class="text-sm text-white/70">Kosong</p>
                            </div>
                        </div>
                        <button onclick="kosongkanKeranjang()" class="btn-secondary" style="background: transparent; color: white; border-color: rgba(255,255,255,0.3);">
                            <i class="fas fa-trash-alt"></i> Kosongkan
                        </button>
                    </div>
                </div>

                <!-- Items Container -->
                <div class="cart-items-container">
                    <div id="cartItems"></div>
                    <div id="emptyCart" class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="empty-state-text">Keranjang kosong</div>
                        <p class="text-sm text-gray-400 mt-1">Pilih menu dari daftar di samping</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="cart-footer">
                    <!-- Meja Selection -->
                    <div>
                        <label class="text-sm font-bold text-gray-700 block mb-2 flex items-center gap-2">
                            <i class="fas fa-chair text-blue-600"></i> Pilih Meja
                        </label>
                        <select id="mejaSelect" class="w-full border-2 border-gray-200 rounded-12 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-gray-700 font-medium text-sm">
                            <option value="">-- Pilih Meja --</option>
                            <?php 
                            $mejaResult->data_seek(0);
                            while($meja = $mejaResult->fetch_assoc()): 
                            ?>
                                <option value="<?= $meja['nomor_meja'] ?>">Meja <?= $meja['nomor_meja'] ?> (<?= $meja['kapasitas'] ?> orang)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="text-sm font-bold text-gray-700 block mb-2 flex items-center gap-2">
                            <i class="fas fa-pen text-blue-600"></i> Catatan (opsional)
                        </label>
                        <textarea id="catatanPesanan" rows="2" placeholder="Pedas level 2, jangan bawang..." 
                            class="w-full border-2 border-gray-200 rounded-12 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white transition resize-none font-medium"></textarea>
                    </div>

                    <!-- Total -->
                    <div class="cart-total">
                        <span class="cart-total-label">Total Pesanan</span>
                        <span class="cart-total-value" id="cartTotal">Rp 0</span>
                    </div>

                    <!-- Submit Button -->
                    <button onclick="submitPesanan()" id="submitBtn" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Buat Pesanan
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== PESANAN TERBARU ===== -->
        <div class="table-container fade-in-up" style="animation-delay: 0.3s;">
            <div class="table-header">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-history text-blue-600"></i> Pesanan Terbaru
                </h2>
                <span class="text-sm text-gray-500 bg-gray-100 px-4 py-1.5 rounded-full font-medium"><?= $pesananTerbaru->num_rows ?> pesanan</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Meja</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($pesananTerbaru->num_rows > 0): ?>
                            <?php while($row = $pesananTerbaru->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="font-mono font-bold text-blue-600">#<?= $row['nomor_pesanan'] ?></span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1.5 rounded-full text-xs font-bold text-gray-700">
                                        <i class="fas fa-chair text-gray-400 text-xs"></i> <?= $row['meja'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="font-bold text-gray-800">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $row['status'] ?>">
                                        <?php 
                                        if($row['status'] == 'menunggu') echo '🟡 Menunggu';
                                        elseif($row['status'] == 'diproses') echo '🔵 Diproses';
                                        elseif($row['status'] == 'selesai') echo '✅ Selesai';
                                        else echo '❌ Batal';
                                        ?>
                                    </span>
                                </td>
                                <td class="text-gray-500 text-sm"><?= date('H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="invoice.php?order_id=<?= $row['nomor_pesanan'] ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 font-bold text-sm transition flex items-center gap-1 hover:underline">
                                        <i class="fas fa-print"></i> Invoice
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                                        <div class="empty-state-text">Belum ada pesanan</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== TOAST NOTIFICATION ===== -->
<div id="toast" class="toast success">
    <div class="flex items-center gap-2">
        <i class="fas fa-check-circle text-xl"></i>
        <span id="toastMessage">Berhasil!</span>
    </div>
</div>

<script>
// ============ STATE ============
let cart = [];

// ============ FILTER KATEGORI ============
function filterKategori(katId) {
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.kat == katId) {
            btn.classList.add('active');
        }
    });
    
    document.querySelectorAll('.menu-btn').forEach(btn => {
        if (katId === 'semua' || btn.dataset.kat == katId) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    });
}

// ============ CART FUNCTIONS ============
function tambahItem(id, nama, harga) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ id, nama, harga, qty: 1 });
    }
    renderCart();
    showToast(nama + ' ditambahkan!', 'success');
}

function ubahQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        renderCart();
    }
}

function hapusItem(id) {
    const item = cart.find(i => i.id === id);
    cart = cart.filter(i => i.id !== id);
    renderCart();
    if (item) showToast(item.nama + ' dihapus!', 'error');
}

function kosongkanKeranjang() {
    if (cart.length === 0) return;
    if (!confirm('Kosongkan semua pesanan?')) return;
    cart = [];
    renderCart();
    showToast('Keranjang dikosongkan', 'error');
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const empty = document.getElementById('emptyCart');
    const totalEl = document.getElementById('cartTotal');
    const itemCountEl = document.getElementById('cartItemCount');
    const submitBtn = document.getElementById('submitBtn');

    if (cart.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'flex';
        totalEl.innerText = 'Rp 0';
        itemCountEl.innerText = 'Kosong';
        submitBtn.disabled = false;
        return;
    }

    empty.style.display = 'none';
    let total = 0;
    let html = '';
    let itemCount = 0;

    cart.forEach((item) => {
        total += item.harga * item.qty;
        itemCount += item.qty;
        html += `
            <div class="cart-item">
                <div class="flex-1">
                    <p class="font-bold text-gray-800 text-sm">${item.nama}</p>
                    <p class="text-xs text-gray-500 mt-1">Rp ${item.harga.toLocaleString('id-ID')}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="qty-control">
                        <button onclick="ubahQty(${item.id}, -1)" class="qty-btn minus">−</button>
                        <span class="font-bold text-gray-700 w-6 text-center text-sm">${item.qty}</span>
                        <button onclick="ubahQty(${item.id}, 1)" class="qty-btn plus">+</button>
                    </div>
                    <button onclick="hapusItem(${item.id})" class="text-red-500 hover:text-red-700 text-xs transition ml-2">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
    itemCountEl.innerText = itemCount + ' item dalam keranjang';
}

// ============ SUBMIT ORDER ============
function submitPesanan() {
    if (cart.length === 0) {
        showToast('Keranjang masih kosong!', 'error');
        return;
    }

    const meja = document.getElementById('mejaSelect').value;
    if (!meja) {
        showToast('Pilih meja terlebih dahulu!', 'error');
        return;
    }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    const catatan = document.getElementById('catatanPesanan').value;

    fetch('place_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            meja: meja, 
            items: cart,
            catatan: catatan 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Pesanan berhasil dibuat!', 'success');
            setTimeout(() => {
                window.location.href = 'payment.php?order_id=' + data.pesanan_id;
            }, 800);
        } else {
            showToast('❌ Gagal: ' + data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Buat Pesanan';
        }
    })
    .catch(() => {
        showToast('❌ Terjadi kesalahan', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Buat Pesanan';
    });
}

// ============ TOAST NOTIFICATION ============
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const msg = document.getElementById('toastMessage');
    
    toast.className = 'toast ' + type;
    msg.textContent = message;
    
    toast.classList.add('show');
    
    clearTimeout(toast.timeout);
    toast.timeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ============ REALTIME CLOCK ============
function updateClock() {
    const now = new Date();
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    document.getElementById('realtimeDate').textContent = now.toLocaleDateString('id-ID', options);
}
updateClock();
setInterval(updateClock, 1000);

// ============ KEYBOARD SHORTCUTS ============
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && cart.length > 0) {
        if (confirm('Reset keranjang?')) {
            cart = [];
            renderCart();
            showToast('Keranjang direset', 'error');
        }
    }
});
</script>
</body>
</html>