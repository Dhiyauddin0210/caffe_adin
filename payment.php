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
    <title>Pembayaran - Caffe Adin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .payment-card {
            background: white;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.06);
        }
        .method-btn {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            padding: 16px;
            border-radius: 16px;
            cursor: pointer;
            text-align: center;
        }
        .method-btn:hover {
            border-color: #2563eb;
            background: #eff6ff;
            transform: translateY(-2px);
        }
        .method-btn.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.15);
        }
        .method-btn i {
            font-size: 28px;
            display: block;
            margin-bottom: 8px;
        }
        .pay-btn {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white;
            padding: 16px;
            border-radius: 16px;
            border: none;
            font-weight: 700;
            font-size: 18px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .pay-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }
        .pay-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-[#f0f4f8] min-h-screen flex items-center justify-center p-4">

<div class="max-w-2xl w-full">
    <a href="index.php?meja=<?= $pesanan['meja'] ?>" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 mb-4 no-print transition-all">
        <i class="fas fa-arrow-left"></i> Kembali ke Menu
    </a>

    <div class="payment-card">
        <div class="text-center mb-8">
            <?php 
            $logo = 'assets/logo.png';
            if (file_exists($logo)): 
            ?>
                <img src="<?= $logo ?>" alt="Caffe Adin" class="h-16 w-16 object-contain rounded-xl mx-auto mb-4">
            <?php else: ?>
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-mug-saucer text-3xl text-blue-600"></i>
                </div>
            <?php endif; ?>
            <h1 class="text-2xl font-bold text-gray-800">Pembayaran</h1>
            <p class="text-gray-500 text-sm">Silakan pilih metode pembayaran</p>
        </div>

        <div class="bg-gray-50 rounded-2xl p-4 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500">Nomor Pesanan</p>
                    <p class="font-bold text-gray-800"><?= $pesanan['nomor_pesanan'] ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Meja</p>
                    <p class="font-bold text-gray-800"><?= $pesanan['meja'] ?></p>
                </div>
            </div>
            <div class="mt-2">
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

        <div class="flex justify-between items-center mb-6 p-4 bg-blue-50 rounded-2xl border border-blue-200">
            <span class="text-gray-700 font-medium">Total Pembayaran</span>
            <span class="text-2xl font-bold text-blue-600">Rp <?= number_format($pesanan['total'], 0, ',', '.') ?></span>
        </div>

        <div class="mb-6">
            <p class="text-sm font-semibold text-gray-700 mb-3">Pilih Metode Pembayaran</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="method-btn selected" onclick="selectMethod(this, 'tunai')" id="method-tunai">
                    <i class="fas fa-money-bill-wave text-green-600"></i>
                    <span class="text-sm font-medium">Tunai</span>
                </div>
                <div class="method-btn" onclick="selectMethod(this, 'qris')" id="method-qris">
                    <i class="fas fa-qrcode text-blue-600"></i>
                    <span class="text-sm font-medium">QRIS</span>
                </div>
                <div class="method-btn" onclick="selectMethod(this, 'transfer')" id="method-transfer">
                    <i class="fas fa-university text-purple-600"></i>
                    <span class="text-sm font-medium">Transfer Bank</span>
                </div>
                <div class="method-btn" onclick="selectMethod(this, 'gopay')" id="method-gopay">
                    <i class="fas fa-wallet text-green-500"></i>
                    <span class="text-sm font-medium">GoPay</span>
                </div>
            </div>
        </div>

        <div id="qrisPopup" class="hidden text-center p-4 bg-gray-50 rounded-2xl mb-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Scan QR Code untuk Bayar</p>
            <div class="bg-white p-4 rounded-xl inline-block">
                <i class="fas fa-qrcode text-8xl text-gray-800"></i>
            </div>
            <p class="text-xs text-gray-400 mt-2">Gunakan aplikasi e-wallet atau mobile banking</p>
        </div>

        <button onclick="processPayment('<?= $pesanan['nomor_pesanan'] ?>')" class="pay-btn no-print" id="payBtn">
            <i class="fas fa-check-circle mr-2"></i> Bayar Sekarang
        </button>

        <p class="text-center text-xs text-gray-400 mt-4 no-print">
            <i class="fas fa-lock mr-1"></i> Pembayaran aman dan terenkripsi
        </p>
    </div>
</div>

<script>
let selectedMethod = 'tunai';

function selectMethod(el, method) {
    document.querySelectorAll('.method-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    el.classList.add('selected');
    selectedMethod = method;
    
    const qrisPopup = document.getElementById('qrisPopup');
    if (method === 'qris') {
        qrisPopup.classList.remove('hidden');
    } else {
        qrisPopup.classList.add('hidden');
    }
}

function processPayment(orderId) {
    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

    fetch('process_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            order_id: orderId, 
            payment_method: selectedMethod 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'order_success.php?order_id=' + orderId;
        } else {
            alert('Pembayaran gagal: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Bayar Sekarang';
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Bayar Sekarang';
    });
}
</script>
</body>
</html>