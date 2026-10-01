<?php
include 'config.php';
include 'auth_check.php';

// Ambil data meja
$mejaResult = $conn->query("SELECT * FROM meja ORDER BY nomor_meja");
$totalMeja = $mejaResult->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Meja - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
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
        .qr-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(59, 130, 246, 0.06);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .qr-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.08);
        }
        .qr-container {
            display: flex;
            justify-content: center;
            margin: 12px 0;
        }
        .qr-container canvas,
        .qr-container img {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }
        .print-btn {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .print-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        @media print {
            .no-print { display: none !important; }
            .qr-card { page-break-inside: avoid; border: none; box-shadow: none; }
            body { background: white; }
            .sidebar { display: none !important; }
            .ml-64 { margin-left: 0 !important; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .qr-card {
            animation: fadeInUp 0.5s ease forwards;
        }
        .qr-card:nth-child(1) { animation-delay: 0.05s; }
        .qr-card:nth-child(2) { animation-delay: 0.10s; }
        .qr-card:nth-child(3) { animation-delay: 0.15s; }
        .qr-card:nth-child(4) { animation-delay: 0.20s; }
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
            <a href="meja.php" class="sidebar-item">
                <i class="fas fa-chair w-5"></i>
                <span>Manajemen Meja</span>
            </a>
            <a href="qr_generator.php" class="sidebar-item active">
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
        <div class="flex justify-between items-center mb-8 no-print">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800">
                    <i class="fas fa-qrcode text-blue-600 mr-2"></i> QR Code Meja
                </h1>
                <p class="text-gray-500 text-sm">Scan QR untuk pesan langsung dari meja</p>
            </div>
            <button onclick="window.print()" class="print-btn">
                <i class="fas fa-print mr-2"></i> Print Semua QR
            </button>
        </div>

        <?php if($totalMeja > 0): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-6 py-4 rounded-2xl mb-8 no-print">
            <i class="fas fa-info-circle mr-2"></i>
            Tempelkan QR Code di setiap meja. Pelanggan scan QR → langsung ke halaman menu dengan meja terpilih.
        </div>
        <?php else: ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-6 py-4 rounded-2xl mb-8 no-print">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Belum ada meja. Silakan tambahkan meja di <a href="meja.php" class="font-semibold underline">Manajemen Meja</a> terlebih dahulu.
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php 
            $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?meja=";
            $mejaResult->data_seek(0);
            while($meja = $mejaResult->fetch_assoc()) { 
                $qr_url = $base_url . $meja['nomor_meja'];
                $qr_id = 'qr_' . $meja['nomor_meja'];
            ?>
                <div class="qr-card" id="card_<?= $meja['nomor_meja'] ?>">
                    <div class="flex items-center justify-between mb-3">
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">
                            Meja <?= $meja['nomor_meja'] ?>
                        </span>
                        <span class="text-xs text-gray-400">
                            <i class="fas fa-chair"></i>
                        </span>
                    </div>
                    
                    <div class="qr-container" id="<?= $qr_id ?>"></div>
                    
                    <p class="text-xs text-gray-400 mt-2 break-all">
                        <?= $qr_url ?>
                    </p>
                    
                    <div class="mt-3 flex gap-2 justify-center no-print">
                        <button onclick="downloadQR('<?= $meja['nomor_meja'] ?>')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm transition">
                            <i class="fas fa-download"></i>
                        </button>
                        <button onclick="window.location.href='index.php?meja=<?= $meja['nomor_meja'] ?>'" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm transition">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
// Generate QR Codes
<?php 
$mejaResult->data_seek(0);
while($meja = $mejaResult->fetch_assoc()) { 
    $qr_url = $base_url . $meja['nomor_meja'];
    $qr_id = 'qr_' . $meja['nomor_meja'];
?>
    new QRCode(document.getElementById("<?= $qr_id ?>"), {
        text: "<?= $qr_url ?>",
        width: 150,
        height: 150,
        colorDark: "#1e3a5f",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
<?php } ?>

// Download QR Code
function downloadQR(meja) {
    const card = document.getElementById('card_' + meja);
    const qrContainer = card.querySelector('.qr-container');
    const canvas = qrContainer.querySelector('canvas');
    
    if (canvas) {
        const link = document.createElement('a');
        link.download = 'QR_Meja_' + meja + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
}
</script>

</body>
</html>