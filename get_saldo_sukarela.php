<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'config/database.php';

if (isset($_GET['id_anggota'])) {
    $id_anggota = intval($_GET['id_anggota']);

    $q_simpan = $conn->query("SELECT SUM(jumlah) as total FROM simpanan WHERE id_anggota = $id_anggota AND jenis_simpanan = 'sukarela'");
    $total_simpan = $q_simpan->fetch_assoc()['total'] ?? 0;

    $q_tarik = $conn->query("SELECT SUM(jumlah) as total FROM penarikan_simpanan WHERE id_anggota = $id_anggota");
    $total_tarik = $q_tarik->fetch_assoc()['total'] ?? 0;

    $saldo = $total_simpan - $total_tarik;

    echo json_encode([
        'status' => 'success',
        'id_anggota' => $id_anggota,
        'saldo_sukarela' => $saldo
    ]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'id_anggota parameter is missing']);
}
?>
