<?php
include 'config.php';

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

$pesanan = $conn->query("
    SELECT p.*, 
           GROUP_CONCAT(CONCAT(m.nama_menu, ' (', d.qty, 'x)') SEPARATOR ', ') as detail_menu
    FROM pesanan p
    LEFT JOIN detail_pesanan d ON p.id = d.pesanan_id
    LEFT JOIN menu m ON d.menu_id = m.id
    WHERE p.nomor_pesanan = '$order_id'
    GROUP BY p.id
")->fetch_assoc();

if (!$pesanan) {
    die("Pesanan tidak ditemukan!");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .success-card {
            background: white;
            border-radius: 24px;
            padding: 48px 32px;
            box-shadow: 0 20px 60px rgba(37, 99, 235, 0.08);
            text-align: center;
            border: 1px solid rgba(37, 99, 235, 0.06);
        }
        .checkmark {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        .checkmark i {
            font-size: 40px;
            color: white;
        }
        .btn-print {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-print:hover {
            background: #e2e8f0;
        }
        .btn-order {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-order:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        @media print {
            .no-print { display: none !important; }
            .success-card { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-[#f0f4f8] min-h-screen flex items-center justify-center p-4">

<div class="max-w-2xl w-full">
    <div class="success-card">
        <?php 
        $logo = 'assets/logo.png';
        if (file_exists($logo)): 
        ?>
            <img src="<?= $logo ?>" alt="Caffe Adin" class="h-16 w-16 object-contain rounded-xl mx-auto mb-4">
        <?php endif; ?>
        
        <div class="checkmark">
            <i class="fas fa-check"></i>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-2">Pesanan Berhasil! 🎉</h1>
        <p class="text-gray-500 mb-6">Terima kasih, pesanan Anda sedang diproses</p>

        <div class="bg-gray-50 rounded-2xl p-6 text-left mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Nomor Pesanan</p>
                    <p class="font-bold text-gray-800"><?= $pesanan['nomor_pesanan'] ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Meja</p>
                    <p class="font-bold text-gray-800"><?= $pesanan['meja'] ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="font-bold text-blue-600">Rp <?= number_format($pesanan['total'], 0, ',', '.') ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Metode Pembayaran</p>
                    <p class="font-bold text-gray-800"><?= ucfirst($pesanan['payment_method'] ?? 'Tunai') ?></p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-200">
                <p class="text-xs text-gray-500">Detail Pesanan</p>
                <p class="text-sm text-gray-700"><?= $pesanan['detail_menu'] ?></p>
            </div>
            <?php if(!empty($pesanan['catatan'])): ?>
                <div class="mt-2 p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-xs text-gray-500">📝 Catatan:</p>
                    <p class="text-sm text-gray-700"><?= $pesanan['catatan'] ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-4 py-2 rounded-full mb-6">
            <i class="fas fa-clock"></i>
            <span class="text-sm font-medium">Status: Sedang Diproses</span>
        </div>

        <div class="text-sm text-gray-500 mb-6">
            <i class="fas fa-clock mr-1"></i>
            Estimasi siap: <span class="font-semibold">15-20 menit</span>
        </div>

        <div class="flex flex-wrap gap-3 justify-center no-print">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print mr-2"></i> Cetak Struk
            </button>
            <a href="index.php?meja=<?= $pesanan['meja'] ?>" class="btn-order">
                <i class="fas fa-arrow-left"></i> Pesan Lagi
            </a>
        </div>

        <p class="text-xs text-gray-400 mt-6 no-print">
            <i class="fas fa-check-circle text-green-500 mr-1"></i>
            Pembayaran berhasil diverifikasi
        </p>
    </div>
</div>
</body>
</html>