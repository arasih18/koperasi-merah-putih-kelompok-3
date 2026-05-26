<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

$action = $_GET['action'] ?? '';

if ($action === 'tambah') {
    $nama_supplier = $conn->real_escape_string($_POST['nama_supplier']);
    $no_hp = $conn->real_escape_string($_POST['no_hp']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    $query = "INSERT INTO supplier (nama_supplier, no_hp, alamat) VALUES ('$nama_supplier', '$no_hp', '$alamat')";
    
    if ($conn->query($query)) {
        $_SESSION['success'] = "Data supplier berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan data: " . $conn->error;
    }
    
    header("Location: supplier.php");
    exit;
} 
elseif ($action === 'edit') {
    $id = $conn->real_escape_string($_POST['id_supplier']);
    $nama_supplier = $conn->real_escape_string($_POST['nama_supplier']);
    $no_hp = $conn->real_escape_string($_POST['no_hp']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    $query = "UPDATE supplier SET nama_supplier = '$nama_supplier', no_hp = '$no_hp', alamat = '$alamat' WHERE id_supplier = '$id'";
    
    if ($conn->query($query)) {
        $_SESSION['success'] = "Data supplier berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal mengupdate data: " . $conn->error;
    }
    
    header("Location: supplier.php");
    exit;
} 
elseif ($action === 'hapus') {
    $id = $conn->real_escape_string($_GET['id']);
    
    // Check if supplier is used in pembelian
    $check = $conn->query("SELECT id_pembelian FROM pembelian WHERE id_supplier = '$id' LIMIT 1");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Supplier tidak bisa dihapus karena sedang digunakan dalam transaksi pembelian!";
    } else {
        $query = "DELETE FROM supplier WHERE id_supplier = '$id'";
        if ($conn->query($query)) {
            $_SESSION['success'] = "Data supplier berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data: " . $conn->error;
        }
    }
    
    header("Location: supplier.php");
    exit;
}
else {
    header("Location: supplier.php");
    exit;
}
?>
