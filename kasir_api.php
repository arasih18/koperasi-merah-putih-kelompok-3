<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = $conn->real_escape_string($q);

$query_str = "SELECT id_barang, kode_barang, nama_barang, harga_jual, stok FROM barang WHERE stok > 0";
if ($q !== '') {
    $query_str .= " AND (kode_barang LIKE '%$q%' OR nama_barang LIKE '%$q%')";
}
$query_str .= " LIMIT 20";

$result = $conn->query($query_str);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id_barang' => $row['id_barang'],
        'kode_barang' => $row['kode_barang'],
        'nama_barang' => $row['nama_barang'],
        'harga_jual' => intval($row['harga_jual']),
        'stok' => intval($row['stok']),
        'label' => $row['kode_barang'] . ' - ' . $row['nama_barang']
    ];
}

echo json_encode(['status' => 'success', 'data' => $data]);
?>
