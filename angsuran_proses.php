<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pinjaman = intval($_POST['id_pinjaman']);
    $tanggal_bayar = $conn->real_escape_string($_POST['tanggal_bayar']);
    $jumlah_bayar = floatval($_POST['jumlah_bayar']);
    $cicilan_ke = intval($_POST['cicilan_ke']);

    // 1. Dapatkan informasi pinjaman untuk cek lunas atau tidak
    $q_pinjaman = $conn->query("SELECT * FROM pinjaman WHERE id_pinjaman = $id_pinjaman AND status = 'approved'");
    if ($q_pinjaman->num_rows == 0) {
        $_SESSION['error'] = "Pinjaman tidak ditemukan atau belum disetujui/sudah lunas.";
        header("Location: angsuran.php");
        exit;
    }
    $pin = $q_pinjaman->fetch_assoc();

    // Insert angsuran
    $query = "INSERT INTO angsuran (id_pinjaman, cicilan_ke, jumlah_bayar, tanggal_bayar) 
              VALUES ($id_pinjaman, $cicilan_ke, $jumlah_bayar, '$tanggal_bayar')";
    
    if ($conn->query($query)) {
        // Cek total terbayar
        $q_total = $conn->query("SELECT SUM(jumlah_bayar) as total FROM angsuran WHERE id_pinjaman = $id_pinjaman");
        $total_bayar = $q_total->fetch_assoc()['total'];

        // Hitung hutang
        $pokok = $pin['jumlah_pinjaman'];
        $bunga_rp = $pokok * ($pin['bunga'] / 100);
        $total_hutang = $pokok + $bunga_rp;

        // Update status lunas jika sudah mencukupi
        if ($total_bayar >= $total_hutang) {
            $conn->query("UPDATE pinjaman SET status = 'lunas' WHERE id_pinjaman = $id_pinjaman");
            $msg_lunas = " dan Pinjaman telah LUNAS!";
        } else {
            $msg_lunas = "";
        }

        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'INSERT', 'angsuran', 'Input angsuran ke-$cicilan_ke untuk pinjaman ID $id_pinjaman sebesar $jumlah_bayar')");
        
        $_SESSION['success'] = "Angsuran berhasil disimpan" . $msg_lunas;
    } else {
        $_SESSION['error'] = "Gagal menyimpan angsuran: " . $conn->error;
    }
    
    header("Location: angsuran.php?id_pinjaman=$id_pinjaman");
    exit;
} else {
    header("Location: angsuran.php");
    exit;
}
?>
