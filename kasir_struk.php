<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}
require_once 'config/database.php';

if(!isset($_GET['id'])) die("Transaksi tidak valid.");
$id_penjualan = intval($_GET['id']);

$q_penjualan = $conn->query("
    SELECT p.*, u.nama as kasir, a.nama_anggota, a.no_anggota
    FROM penjualan p 
    LEFT JOIN users u ON p.id_user = u.id_user 
    LEFT JOIN anggota a ON p.id_anggota = a.id_anggota
    WHERE id_penjualan = $id_penjualan
");
if($q_penjualan->num_rows == 0) die("Transaksi tidak ditemukan.");
$penjualan = $q_penjualan->fetch_assoc();

$q_detail = $conn->query("
    SELECT d.*, b.nama_barang 
    FROM detail_penjualan d
    LEFT JOIN barang b ON d.id_barang = b.id_barang
    WHERE d.id_penjualan = $id_penjualan
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan - <?php echo $penjualan['kode_penjualan']; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 58mm; /* Lebar thermal printer standar */
            margin: 0 auto;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h3 { margin: 0; font-size: 14px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 10px; }
        .divider { border-bottom: 1px dashed #000; margin: 5px 0; }
        .info { font-size: 10px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { padding: 2px 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .footer { text-align: center; font-size: 10px; margin-top: 10px; }
        @media print {
            .no-print { display: none; }
            body { width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h3>KOPERASI MERAH PUTIH</h3>
        <p>Jl. Jenderal Sudirman No. 123</p>
        <p>Telp: 0812-3456-7890</p>
    </div>
    
    <div class="divider"></div>
    
    <table class="info">
        <tr><td>No.</td><td>: <?php echo $penjualan['kode_penjualan']; ?></td></tr>
        <tr><td>Tgl</td><td>: <?php echo date('d-m-Y H:i', strtotime($penjualan['created_at'])); ?></td></tr>
        <tr><td>Kasir</td><td>: <?php echo htmlspecialchars($penjualan['kasir']); ?></td></tr>
        <?php if(!empty($penjualan['id_anggota'])): ?>
        <tr><td>Plgn</td><td>: <?php echo htmlspecialchars($penjualan['nama_anggota']); ?> (<?php echo htmlspecialchars($penjualan['no_anggota']); ?>)</td></tr>
        <?php else: ?>
        <tr><td>Plgn</td><td>: Umum</td></tr>
        <?php endif; ?>
    </table>
    
    <div class="divider"></div>
    
    <table>
        <?php while($row = $q_detail->fetch_assoc()): ?>
        <tr>
            <td colspan="3"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
        </tr>
        <tr>
            <td><?php echo $row['qty']; ?> x</td>
            <td><?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
            <td class="text-right"><?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    
    <div class="divider"></div>
    
    <table>
        <tr>
            <td class="fw-bold">TOTAL</td>
            <td class="text-right fw-bold">Rp <?php echo number_format($penjualan['total'], 0, ',', '.'); ?></td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <div class="footer">
        <p>Terima Kasih Atas Kunjungan Anda</p>
        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
    </div>
    
    <div class="no-print" style="margin-top:20px; text-align:center;">
        <button onclick="window.print()" style="padding:10px 20px;">Cetak Ulang</button>
        <button onclick="window.close()" style="padding:10px 20px;">Tutup</button>
    </div>
</body>
</html>
