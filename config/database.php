<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "koperasi_merah_putih_kelompok3";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
