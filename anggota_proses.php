<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'tambah') {
    $no_anggota = $conn->real_escape_string($_POST['no_anggota']);
    $nama_anggota = $conn->real_escape_string($_POST['nama_anggota']);
    $jk = $conn->real_escape_string($_POST['jk']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $no_hp = $conn->real_escape_string($_POST['no_hp']);
    $tanggal_gabung = $conn->real_escape_string($_POST['tanggal_gabung']);

    $query = "INSERT INTO anggota (no_anggota, nama_anggota, jk, alamat, no_hp, tanggal_gabung) 
              VALUES ('$no_anggota', '$nama_anggota', '$jk', '$alamat', '$no_hp', '$tanggal_gabung')";
    
    if($conn->query($query)){
        // Log activity
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'INSERT', 'anggota', 'Menambahkan anggota: $nama_anggota')");
        $_SESSION['success'] = "Data anggota berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan data: " . $conn->error;
    }
    
    if (isset($_GET['redirect']) && $_GET['redirect'] == 'simpanan_tambah') {
        header("Location: simpanan_tambah.php");
        exit;
    }
    header("Location: anggota.php");
    exit;
}
elseif ($action == 'edit') {
    $id = intval($_POST['id_anggota']);
    $nama_anggota = $conn->real_escape_string($_POST['nama_anggota']);
    $jk = $conn->real_escape_string($_POST['jk']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $no_hp = $conn->real_escape_string($_POST['no_hp']);
    $tanggal_gabung = $conn->real_escape_string($_POST['tanggal_gabung']);

    $query = "UPDATE anggota SET 
              nama_anggota = '$nama_anggota', 
              jk = '$jk', 
              alamat = '$alamat', 
              no_hp = '$no_hp', 
              tanggal_gabung = '$tanggal_gabung' 
              WHERE id_anggota = $id";
              
    if($conn->query($query)){
        // Log activity
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'UPDATE', 'anggota', 'Memperbarui anggota ID: $id')");
        $_SESSION['success'] = "Data anggota berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui data: " . $conn->error;
    }
    header("Location: anggota.php");
    exit;
}
elseif ($action == 'hapus') {
    $id = intval($_GET['id']);
    
    // Menggunakan Soft Delete (Hanya ubah status dan isi deleted_at)
    $query = "UPDATE anggota SET status = 'nonaktif', deleted_at = NOW() WHERE id_anggota = $id";
    
    if($conn->query($query)){
        // Log activity
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'SOFT DELETE', 'anggota', 'Menonaktifkan anggota ID: $id')");
        $_SESSION['success'] = "Data anggota berhasil dihapus (dinonaktifkan)!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . $conn->error;
    }
    header("Location: anggota.php");
    exit;
}
else {
    header("Location: anggota.php");
    exit;
}
?>
