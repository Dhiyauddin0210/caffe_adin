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
    <title>Invoice - <?= $order_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .invoice { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        }
        .invoice {
            background: white;
            max-width: 700px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header-line {
            border-top: 2px dashed #e2e8f0;
            margin: 20px 0;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">

<div class="invoice">
    <!-- Header -->
    <div class="text-center">
        <div class="flex items-center justify-center gap-3 mb-2">
            <i class="fas fa-utensils text-3xl text-orange-600"></i>
            <h1 class="text-2xl font-bold text-gray-800">Caffe Kita</h1>
        </div>
        <p class="text-sm text-gray-500">Jl. Kuliner No. 123, Kota Makan</p>
        <p class="text-sm text-gray-500">Telp: 0812-3456-7890</p>
    </div>

    <div class="header-line"></div>

    <!-- Invoice Info -->
    <div class="flex justify-between items-start">
        <div>
            <p class="text-sm text-gray-500">Nomor Invoice</p>
            <p class="font-bold text-gray-800"><?= $pesanan['nomor_pesanan'] ?></p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Tanggal</p>
            <p class="font-bold text-gray-800"><?= date('d/m/Y H:i', strtotime($pesanan['created_at'])) ?></p>
        </div>
    </div>

    <div class="flex justify-between items-start mt-2">
        <div>
            <p class="text-sm text-gray-500">Meja</p>
            <p class="font-bold text-gray-800"><?= $pesanan['meja'] ?></p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Status</p>
            <p class="font-bold text-green-600"><?= ucfirst($pesanan['status']) ?></p>
        </div>
    </div>

    <div class="header-line"></div>

    <!-- Items -->
    <div class="mb-4">
        <p class="text-sm font-semibold text-gray-700 mb-2">Detail Pesanan</p>
        <p class="text-gray-700"><?= $pesanan['detail_menu'] ?></p>
    </div>

    <div class="header-line"></div>

    <!-- Total -->
    <div class="flex justify-between items-center">
        <span class="text-lg font-bold text-gray-800">Total</span>
        <span class="text-2xl font-bold text-orange-600">Rp <?= number_format($pesanan['total'], 0, ',', '.') ?></span>
    </div>

    <div class="flex justify-between items-center mt-1">
        <span class="text-sm text-gray-500">Metode Pembayaran</span>
        <span class="font-semibold text-gray-700"><?= ucfirst($pesanan['payment_method'] ?? 'Tunai') ?></span>
    </div>

    <div class="flex justify-between items-center mt-1">
        <span class="text-sm text-gray-500">Status Pembayaran</span>
        <span class="font-semibold <?= ($pesanan['payment_status'] ?? 'pending') == 'paid' ? 'text-green-600' : 'text-yellow-600' ?>">
            <?= ($pesanan['payment_status'] ?? 'pending') == 'paid' ? '✅ Lunas' : '⏳ Pending' ?>
        </span>
    </div>

    <div class="header-line"></div>

    <!-- Footer -->
    <div class="text-center text-sm text-gray-400">
        <p>Terima kasih telah berkunjung!</p>
        <p class="text-xs mt-1">Struk ini adalah bukti pembayaran yang sah</p>
    </div>
</div>

<!-- Print Button -->
<div class="text-center mt-6 no-print">
    <button onclick="window.print()" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl transition font-semibold inline-flex items-center gap-2">
        <i class="fas fa-print"></i> Cetak Invoice
    </button>
    <a href="admin.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-xl transition font-semibold inline-flex items-center gap-2 ml-3">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

</body>
</html>