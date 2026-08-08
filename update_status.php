<?php
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;
$status = $data['status'] ?? '';

$allowed = ['menunggu', 'diproses', 'selesai', 'batal'];
if (!in_array($status, $allowed)) {
    echo json_encode(['success' => false]);
    exit;
}

$sql = "UPDATE pesanan SET status = '$status' WHERE id = $id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>