<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_simpanan = $conn->real_escape_string($_POST['kode_simpanan']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $id_anggota = intval($_POST['id_anggota']);
    $jenis_simpanan = $conn->real_escape_string($_POST['jenis_simpanan']);
    $jumlah = floatval($_POST['jumlah']);

    $query = "INSERT INTO simpanan (kode_simpanan, id_anggota, jenis_simpanan, jumlah, tanggal) 
              VALUES ('$kode_simpanan', $id_anggota, '$jenis_simpanan', $jumlah, '$tanggal')";
    
    if($conn->query($query)){
        // Log activity
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'INSERT', 'simpanan', 'Input simpanan $jenis_simpanan sebesar $jumlah untuk anggota ID $id_anggota')");
        $_SESSION['success'] = "Transaksi simpanan berhasil dicatat!";
    } else {
        $_SESSION['error'] = "Gagal mencatat transaksi: " . $conn->error;
    }
    header("Location: simpanan.php");
    exit;
} else {
    header("Location: simpanan.php");
    exit;
}
?>
