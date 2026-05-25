<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_penarikan = $conn->real_escape_string($_POST['kode_penarikan']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $id_anggota = intval($_POST['id_anggota']);
    $jumlah_tarik = floatval($_POST['jumlah']);

    // Validasi saldo mengendap (minimal Rp 100.000)
    // 1. Hitung total simpanan sukarela
    $query_simpanan = $conn->query("SELECT SUM(jumlah) as total_simpan FROM simpanan WHERE id_anggota = $id_anggota AND jenis_simpanan = 'sukarela'");
    $total_simpan = $query_simpanan->fetch_assoc()['total_simpan'] ?? 0;

    // 2. Hitung total penarikan sebelumnya
    $query_penarikan = $conn->query("SELECT SUM(jumlah) as total_tarik FROM penarikan_simpanan WHERE id_anggota = $id_anggota");
    $total_tarik_sebelumnya = $query_penarikan->fetch_assoc()['total_tarik'] ?? 0;

    // 3. Saldo saat ini
    $saldo_saat_ini = $total_simpan - $total_tarik_sebelumnya;

    // 4. Saldo setelah ditarik
    $saldo_sisa = $saldo_saat_ini - $jumlah_tarik;

    if ($saldo_sisa < 100000) {
        $_SESSION['error'] = "Penarikan gagal! Saldo sukarela tidak mencukupi. (Saldo saat ini: Rp " . number_format($saldo_saat_ini, 0, ',', '.') . ". Harus menyisakan saldo mengendap minimal Rp 100.000)";
        header("Location: penarikan_tambah.php");
        exit;
    }

    // Insert ke database
    $query = "INSERT INTO penarikan_simpanan (kode_penarikan, id_anggota, jumlah, tanggal) 
              VALUES ('$kode_penarikan', $id_anggota, $jumlah_tarik, '$tanggal')";
    
    if($conn->query($query)){
        // Log activity
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'INSERT', 'penarikan_simpanan', 'Tarik simpanan sebesar $jumlah_tarik untuk anggota ID $id_anggota')");
        $_SESSION['success'] = "Transaksi penarikan berhasil dicatat!";
        header("Location: penarikan.php");
    } else {
        $_SESSION['error'] = "Gagal mencatat transaksi: " . $conn->error;
        header("Location: penarikan_tambah.php");
    }
    exit;
} else {
    header("Location: penarikan.php");
    exit;
}
?>
