<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_pembelian = $conn->real_escape_string($_POST['kode_pembelian']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $id_supplier = $conn->real_escape_string($_POST['id_supplier']);
    $total_pembelian = (float)$_POST['total_pembelian'];

    $id_barang_arr = $_POST['id_barang'] ?? [];
    $harga_arr = $_POST['harga'] ?? [];
    $qty_arr = $_POST['qty'] ?? [];

    if (empty($id_barang_arr) || count($id_barang_arr) === 0) {
        $_SESSION['error'] = "Transaksi gagal: Tidak ada barang yang dibeli.";
        header("Location: pembelian_tambah.php");
        exit;
    }

    $conn->begin_transaction();
    try {
        // Insert into pembelian
        $query_pembelian = "INSERT INTO pembelian (kode_pembelian, id_supplier, tanggal, total) 
                            VALUES ('$kode_pembelian', '$id_supplier', '$tanggal', '$total_pembelian')";
        
        if (!$conn->query($query_pembelian)) {
            throw new Exception("Gagal menyimpan data pembelian utama: " . $conn->error);
        }

        $id_pembelian = $conn->insert_id;

        // Insert details
        for ($i = 0; $i < count($id_barang_arr); $i++) {
            $id_barang = $conn->real_escape_string($id_barang_arr[$i]);
            $harga = (float)$harga_arr[$i];
            $qty = (int)$qty_arr[$i];
            $subtotal = $harga * $qty;

            if ($id_barang && $qty > 0) {
                $query_detail = "INSERT INTO detail_pembelian (id_pembelian, id_barang, qty, harga, subtotal) 
                                 VALUES ('$id_pembelian', '$id_barang', '$qty', '$harga', '$subtotal')";
                
                if (!$conn->query($query_detail)) {
                    throw new Exception("Gagal menyimpan detail barang: " . $conn->error);
                }
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Transaksi pembelian barang (Restock) berhasil dicatat, dan stok otomatis bertambah!";
        header("Location: pembelian.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: pembelian_tambah.php");
        exit;
    }
} else {
    header("Location: pembelian.php");
    exit;
}
?>
