<?php
session_start();
// HANYA ADMIN
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk fitur ini.";
    header("Location: anggota.php");
    exit;
}

require_once 'config/database.php';

if(isset($_GET['id'])) {
    $id_anggota = intval($_GET['id']);
    
    // Cek apakah anggota valid
    $cek_anggota = $conn->query("SELECT * FROM anggota WHERE id_anggota = $id_anggota AND status = 'aktif'");
    if($cek_anggota->num_rows == 0){
        $_SESSION['error'] = "Anggota tidak ditemukan atau sudah nonaktif.";
        header("Location: anggota.php");
        exit;
    }
    
    $anggota = $cek_anggota->fetch_assoc();
    $nama_anggota = $anggota['nama_anggota'];

    // Hitung sisa total saldo (seluruh jenis simpanan)
    $q_simpan = $conn->query("SELECT SUM(jumlah) as total FROM simpanan WHERE id_anggota = $id_anggota");
    $total_simpan = $q_simpan->fetch_assoc()['total'] ?? 0;

    $q_tarik = $conn->query("SELECT SUM(jumlah) as total FROM penarikan_simpanan WHERE id_anggota = $id_anggota");
    $total_tarik = $q_tarik->fetch_assoc()['total'] ?? 0;

    $saldo_tersisa = $total_simpan - $total_tarik;

    $conn->begin_transaction();
    try {
        // Jika ada saldo, tarik semuanya
        if($saldo_tersisa > 0){
            // Generate kode penarikan khusus
            $query_no = $conn->query("SELECT MAX(CAST(SUBSTRING(kode_penarikan, 5) AS UNSIGNED)) as last_no FROM penarikan_simpanan WHERE kode_penarikan LIKE 'OUT-%'");
            $data_no = $query_no->fetch_assoc();
            $next_no = $data_no['last_no'] ? $data_no['last_no'] + 1 : 1;
            $kode_penarikan_baru = 'OUT-' . str_pad($next_no, 3, '0', STR_PAD_LEFT);
            $tanggal = date('Y-m-d');

            $conn->query("INSERT INTO penarikan_simpanan (kode_penarikan, id_anggota, jumlah, tanggal) 
                          VALUES ('$kode_penarikan_baru', $id_anggota, $saldo_tersisa, '$tanggal')");
        }

        // Nonaktifkan anggota
        $conn->query("UPDATE anggota SET status = 'nonaktif' WHERE id_anggota = $id_anggota");

        // Log audit
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) 
                      VALUES (".$_SESSION['user_id'].", 'UPDATE', 'anggota', 'Anggota $nama_anggota keluar, saldo ditarik Rp $saldo_tersisa, status nonaktif')");

        $conn->commit();
        $_SESSION['success'] = "Anggota $nama_anggota berhasil dikeluarkan. Seluruh sisa saldo sebesar Rp " . number_format($saldo_tersisa, 0, ',', '.') . " telah ditarik.";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Terjadi kesalahan saat memproses data: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "ID Anggota tidak valid.";
}

header("Location: anggota.php");
exit;
?>
