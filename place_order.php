<?php
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$meja = isset($data['meja']) ? (int)$data['meja'] : 1;
$items = isset($data['items']) ? $data['items'] : [];
$catatan = isset($data['catatan']) ? $data['catatan'] : '';

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += (int)$item['harga'] * (int)$item['qty'];
}

$nomor_pesanan = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

$conn->begin_transaction();

try {
    // Insert ke tabel pesanan (tambah catatan)
    $sql = "INSERT INTO pesanan (nomor_pesanan, meja, total, status, payment_status, catatan) 
            VALUES (?, ?, ?, 'menunggu', 'pending', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siis", $nomor_pesanan, $meja, $total, $catatan);
    $stmt->execute();
    $pesanan_id = $conn->insert_id;
    
    // Insert detail pesanan
    foreach ($items as $item) {
        $menu_id = (int)$item['id'];
        $qty = (int)$item['qty'];
        $harga = (int)$item['harga'];
        $subtotal = $harga * $qty;
        
        $sql_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, qty, harga, subtotal) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_detail);
        $stmt_detail->bind_param("iiiii", $pesanan_id, $menu_id, $qty, $harga, $subtotal);
        $stmt_detail->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'pesanan_id' => $nomor_pesanan]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>