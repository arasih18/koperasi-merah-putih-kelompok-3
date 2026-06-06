<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang = $conn->real_escape_string($_POST['kode_barang']);
    $nama_barang = $conn->real_escape_string($_POST['nama_barang']);
    $id_kategori = $conn->real_escape_string($_POST['id_kategori']);
    $nama_kategori_baru = isset($_POST['nama_kategori_baru']) ? $conn->real_escape_string($_POST['nama_kategori_baru']) : '';
    $harga_beli = (float) $_POST['harga_beli'];
    $harga_jual = (float) $_POST['harga_jual'];

    // Validasi kode barang unik
    $cek = $conn->query("SELECT id_barang FROM barang WHERE kode_barang = '$kode_barang'");
    if ($cek->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Kode Barang sudah digunakan!']);
        exit;
    }

    // Jika membuat kategori baru
    if ($id_kategori === 'new') {
        if (empty($nama_kategori_baru)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Kategori Baru tidak boleh kosong!']);
            exit;
        }
        
        $cek_kat = $conn->query("SELECT id_kategori FROM kategori_barang WHERE nama_kategori = '$nama_kategori_baru'");
        if ($cek_kat->num_rows > 0) {
            $id_kategori = $cek_kat->fetch_assoc()['id_kategori'];
        } else {
            $conn->query("INSERT INTO kategori_barang (nama_kategori) VALUES ('$nama_kategori_baru')");
            $id_kategori = $conn->insert_id;
        }
    }

    $query = "INSERT INTO barang (kode_barang, nama_barang, id_kategori, harga_beli, harga_jual, stok) 
              VALUES ('$kode_barang', '$nama_barang', '$id_kategori', '$harga_beli', '$harga_jual', 0)";
    
    if ($conn->query($query)) {
        $new_id = $conn->insert_id;
        echo json_encode([
            'status' => 'success',
            'data' => [
                'id_barang' => $new_id,
                'nama_barang' => $nama_barang,
                'harga_beli' => $harga_beli
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
}
?>
