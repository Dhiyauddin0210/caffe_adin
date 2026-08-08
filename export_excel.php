<?php
include 'config.php';
include 'auth_check.php';

// Ambil parameter filter
$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-d', strtotime('-30 days'));
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');

// Ambil data pesanan
$sql = "SELECT * FROM pesanan 
        WHERE DATE(created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

// Jika tidak ada data
if ($result->num_rows == 0) {
    die("Tidak ada data pesanan untuk periode tersebut.");
}

// Buat file CSV (Excel bisa buka CSV)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_Pesanan_'.date('Y-m-d').'.csv"');

// Buka output
$output = fopen('php://output', 'w');

// Header CSV (judul kolom)
fputcsv($output, [
    'No',
    'Nomor Pesanan',
    'Meja',
    'Total (Rp)',
    'Status',
    'Metode Pembayaran',
    'Status Pembayaran',
    'Catatan',
    'Tanggal'
]);

// Isi data
$no = 1;
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $no++,
        $row['nomor_pesanan'],
        $row['meja'],
        number_format($row['total'], 0, ',', '.'),
        $row['status'],
        ucfirst($row['payment_method'] ?? 'Tunai'),
        ($row['payment_status'] ?? 'pending') == 'paid' ? 'Lunas' : 'Pending',
        $row['catatan'] ?? '-',
        date('d/m/Y H:i', strtotime($row['created_at']))
    ]);
}

fclose($output);
exit();
?>