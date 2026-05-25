<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'error' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(!$data || empty($data['cart'])) {
    echo json_encode(['status' => 'error', 'error' => 'Keranjang kosong atau format tidak valid']);
    exit;
}

$kode_penjualan = $conn->real_escape_string($data['kode_penjualan']);
$total = floatval($data['total']);
$id_user = $_SESSION['user_id'];
$tanggal = date('Y-m-d');
$id_anggota = !empty($data['id_anggota']) ? intval($data['id_anggota']) : 'NULL';
$cart = $data['cart'];

$conn->begin_transaction();
try {
    // Insert tabel penjualan
    $q1 = $conn->query("INSERT INTO penjualan (kode_penjualan, id_anggota, id_user, tanggal, total) VALUES ('$kode_penjualan', $id_anggota, $id_user, '$tanggal', $total)");
    if(!$q1) throw new Exception("Gagal menyimpan data utama penjualan: " . $conn->error);
    
    $id_penjualan = $conn->insert_id;

    // Insert detail_penjualan
    foreach($cart as $item) {
        $id_barang = intval($item['id_barang']);
        $qty = intval($item['qty']);
        $harga = floatval($item['harga']);
        $subtotal = floatval($item['subtotal']);

        $q2 = $conn->query("INSERT INTO detail_penjualan (id_penjualan, id_barang, qty, harga, subtotal) VALUES ($id_penjualan, $id_barang, $qty, $harga, $subtotal)");
        if(!$q2) throw new Exception("Gagal menyimpan detail penjualan: " . $conn->error);

        // Stok akan otomatis berkurang berkat Trigger `trg_penjualan_stok` dan `trg_mutasi_stok_keluar` 
        // yang sudah ada di database koperasi_merah_putih_kelompok3.sql
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'id_penjualan' => $id_penjualan]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
?>
