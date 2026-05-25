<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

$action = $_GET['action'] ?? '';

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_anggota = intval($_POST['id_anggota']);
    $tanggal_pinjam = $conn->real_escape_string($_POST['tanggal_pinjam']);
    $jumlah_pinjaman = floatval($_POST['jumlah_pinjaman']);
    $lama_angsuran = intval($_POST['lama_angsuran']);
    $bunga = floatval($_POST['bunga']);

    // Generate kode pinjaman PJ + timestamp/uniq
    $kode_pinjaman = 'PJ' . date('YmdHis');

    $query = "INSERT INTO pinjaman (kode_pinjaman, id_anggota, jumlah_pinjaman, bunga, lama_angsuran, tanggal_pinjam, status) 
              VALUES ('$kode_pinjaman', $id_anggota, $jumlah_pinjaman, $bunga, $lama_angsuran, '$tanggal_pinjam', 'pending')";
    
    if ($conn->query($query)) {
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'INSERT', 'pinjaman', 'Pengajuan pinjaman baru $kode_pinjaman untuk anggota ID $id_anggota')");
        $_SESSION['success'] = "Pengajuan pinjaman berhasil ditambahkan dan menunggu persetujuan!";
    } else {
        $_SESSION['error'] = "Gagal mengajukan pinjaman: " . $conn->error;
    }
    header("Location: pinjaman.php");
    exit;
} 
elseif ($action == 'approve' && isset($_GET['id'])) {
    if ($_SESSION['role'] !== 'Admin') {
        $_SESSION['error'] = "Hanya Admin yang dapat menyetujui pinjaman.";
        header("Location: pinjaman.php");
        exit;
    }
    
    $id_pinjaman = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    $query = "UPDATE pinjaman SET status = 'approved', approved_by = $user_id WHERE id_pinjaman = $id_pinjaman AND status = 'pending'";
    if ($conn->query($query)) {
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'UPDATE', 'pinjaman', 'Menyetujui pinjaman ID $id_pinjaman')");
        $_SESSION['success'] = "Pinjaman berhasil disetujui!";
    } else {
        $_SESSION['error'] = "Gagal menyetujui pinjaman: " . $conn->error;
    }
    header("Location: pinjaman.php");
    exit;
}
elseif ($action == 'reject' && isset($_GET['id'])) {
    if ($_SESSION['role'] !== 'Admin') {
        $_SESSION['error'] = "Hanya Admin yang dapat menolak pinjaman.";
        header("Location: pinjaman.php");
        exit;
    }
    
    $id_pinjaman = intval($_GET['id']);
    
    $query = "UPDATE pinjaman SET status = 'ditolak' WHERE id_pinjaman = $id_pinjaman AND status = 'pending'";
    if ($conn->query($query)) {
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'UPDATE', 'pinjaman', 'Menolak pinjaman ID $id_pinjaman')");
        $_SESSION['success'] = "Pinjaman telah ditolak!";
    } else {
        $_SESSION['error'] = "Gagal menolak pinjaman: " . $conn->error;
    }
    header("Location: pinjaman.php");
    exit;
} else {
    header("Location: pinjaman.php");
    exit;
}
?>
