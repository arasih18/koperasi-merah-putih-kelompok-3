<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-file-invoice-dollar me-2"></i> Riwayat Penjualan</h1>
        <a href="kasir.php" class="btn btn-crimson"><i class="fas fa-desktop me-1"></i> Buka Kasir</a>
    </div>
</div>

<section class="content">
    <!-- Summary Box -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <?php
                    $q_total = $conn->query("SELECT SUM(total) as total FROM penjualan");
                    $total_pendapatan = $q_total->fetch_assoc()['total'] ?? 0;
                    ?>
                    <h6 class="text-muted text-uppercase mb-1">Total Pendapatan Toko</h6>
                    <h3 class="mb-0 fw-bold text-success">Rp
                        <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Daftar Transaksi</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Kasir</th>
                            <th>Barang yang Dibeli</th>
                            <th class="text-end">Total Belanja (Rp)</th>
                            <th class="text-center" width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = $conn->query("
                            SELECT p.*, u.nama as nama_kasir, a.nama_anggota 
                            FROM penjualan p 
                            LEFT JOIN users u ON p.id_user = u.id_user 
                            LEFT JOIN anggota a ON p.id_anggota = a.id_anggota
                            ORDER BY p.id_penjualan ASC
                        ");
                        if ($query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $row['kode_penjualan']; ?></span></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($row['id_anggota'])): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-user-tag me-1"></i>
                                                <?php echo htmlspecialchars($row['nama_anggota']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Umum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nama_kasir']); ?></td>
                                    <td>
                                        <ul class="list-unstyled mb-0" style="font-size: 0.85em;">
                                            <?php
                                            $id_penjualan = $row['id_penjualan'];
                                            $q_detail = $conn->query("
                                        SELECT dp.qty, b.nama_barang 
                                        FROM detail_penjualan dp
                                        JOIN barang b ON dp.id_barang = b.id_barang
                                        WHERE dp.id_penjualan = '$id_penjualan'
                                    ");
                                            while ($detail = $q_detail->fetch_assoc()) {
                                                echo '<li>- ' . htmlspecialchars($detail['nama_barang']) . ' (' . $detail['qty'] . 'x)</li>';
                                            }
                                            ?>
                                        </ul>
                                    </td>
                                    <td class="text-end fw-bold text-success">Rp
                                        <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <a href="kasir_struk.php?id=<?php echo $row['id_penjualan']; ?>" target="_blank"
                                            class="btn btn-sm btn-primary" title="Cetak Ulang Struk">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi penjualan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>