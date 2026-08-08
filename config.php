<?php
$host = "localhost";
$user = "root";
$pass = "";  // KOSONGKAN! (XAMPP default password kosong)
$db   = "pesan_makanan";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>