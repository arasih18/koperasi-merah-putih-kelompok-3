<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

$action = $_GET['action'] ?? '';

if($action == 'tambah') {
    $kode = $conn->real_escape_string($_POST['kode_barang']);
    $nama = $conn->real_escape_string($_POST['nama_barang']);
    $kategori = intval($_POST['id_kategori']);
    $beli = floatval($_POST['harga_beli']);
    $jual = floatval($_POST['harga_jual']);
    $stok = intval($_POST['stok']);

    $q = $conn->query("INSERT INTO barang (kode_barang, nama_barang, id_kategori, harga_beli, harga_jual, stok) VALUES ('$kode', '$nama', $kategori, $beli, $jual, $stok)");
    
    if($q) {
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'INSERT', 'barang', 'Tambah barang $nama')");
        $_SESSION['success'] = "Data barang berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Gagal menambah data: " . $conn->error;
    }
    header("Location: barang.php");
}
elseif($action == 'edit') {
    $id = intval($_POST['id_barang']);
    $kode = $conn->real_escape_string($_POST['kode_barang']);
    $nama = $conn->real_escape_string($_POST['nama_barang']);
    $kategori = intval($_POST['id_kategori']);
    $beli = floatval($_POST['harga_beli']);
    $jual = floatval($_POST['harga_jual']);
    $stok = intval($_POST['stok']);

    $q = $conn->query("UPDATE barang SET kode_barang='$kode', nama_barang='$nama', id_kategori=$kategori, harga_beli=$beli, harga_jual=$jual, stok=$stok WHERE id_barang=$id");
    
    if($q) {
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'UPDATE', 'barang', 'Edit barang $nama')");
        $_SESSION['success'] = "Data barang berhasil diupdate.";
    } else {
        $_SESSION['error'] = "Gagal mengupdate data: " . $conn->error;
    }
    header("Location: barang.php");
}
elseif($action == 'hapus' && $_SESSION['role'] === 'Admin') {
    $id = intval($_GET['id']);
    
    // Cek apakah sudah pernah ada transaksi penjualan
    $cek = $conn->query("SELECT id_detail_penjualan FROM detail_penjualan WHERE id_barang = $id LIMIT 1");
    if($cek->num_rows > 0) {
        $_SESSION['error'] = "Gagal menghapus: Barang ini sudah memiliki riwayat penjualan.";
    } else {
        $q = $conn->query("DELETE FROM barang WHERE id_barang = $id");
        if($q) {
            $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'DELETE', 'barang', 'Hapus barang ID $id')");
            $_SESSION['success'] = "Data barang berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus data: " . $conn->error;
        }
    }
    header("Location: barang.php");
} else {
    header("Location: barang.php");
}
?>
