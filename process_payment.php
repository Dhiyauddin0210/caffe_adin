<?php
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? '';
$payment_method = $data['payment_method'] ?? 'tunai';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak ditemukan']);
    exit;
}

// Update status pesanan
$sql = "UPDATE pesanan SET 
        payment_status = 'paid',
        payment_method = ?,
        status = 'diproses',
        updated_at = NOW()
        WHERE nomor_pesanan = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $payment_method, $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>