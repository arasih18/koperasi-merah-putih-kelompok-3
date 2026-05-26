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
        <h1 class="m-0"><i class="fas fa-shopping-cart me-2"></i> Riwayat Pembelian Barang</h1>
        <a href="pembelian_tambah.php" class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Tambah Pembelian (Restock)</a>
    </div>
</div>

<section class="content">
    <!-- Summary Box -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <?php
                    $q_total = $conn->query("SELECT SUM(total) as total FROM pembelian");
                    $total_pengeluaran = $q_total->fetch_assoc()['total'] ?? 0;
                    ?>
                    <h6 class="text-muted text-uppercase mb-1">Total Pengeluaran Pembelian</h6>
                    <h3 class="mb-0 fw-bold text-danger">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Daftar Transaksi Pembelian</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>No. Pembelian</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Detail Barang</th>
                            <th class="text-end">Total Biaya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = $conn->query("
                            SELECT p.*, s.nama_supplier 
                            FROM pembelian p 
                            LEFT JOIN supplier s ON p.id_supplier = s.id_supplier 
                            ORDER BY p.tanggal DESC, p.id_pembelian DESC
                        ");
                        if ($query && $query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $row['kode_pembelian']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="fas fa-truck me-1"></i>
                                            <?php echo htmlspecialchars($row['nama_supplier'] ?? 'Tidak diketahui'); ?></span>
                                    </td>
                                    <td>
                                        <ul class="list-unstyled mb-0" style="font-size: 0.85em;">
                                            <?php
                                            $id_pembelian = $row['id_pembelian'];
                                            $q_detail = $conn->query("
                                                SELECT dp.qty, dp.harga, b.nama_barang 
                                                FROM detail_pembelian dp
                                                JOIN barang b ON dp.id_barang = b.id_barang
                                                WHERE dp.id_pembelian = '$id_pembelian'
                                            ");
                                            while ($detail = $q_detail->fetch_assoc()) {
                                                echo '<li>- ' . htmlspecialchars($detail['nama_barang']) . ' (' . $detail['qty'] . 'x @ ' . number_format($detail['harga'], 0, ',', '.') . ')</li>';
                                            }
                                            ?>
                                        </ul>
                                    </td>
                                    <td class="text-end fw-bold text-danger">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi pembelian.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
