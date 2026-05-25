<?php
session_start();
// Hanya Admin dan Bendahara yang boleh mengakses
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-wallet me-2"></i> Transaksi Simpanan</h1>
        <a href="simpanan_tambah.php" class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Input Simpanan</a>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Box -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <?php
                    $res_simpanan = $conn->query("SELECT SUM(jumlah) as total FROM simpanan");
                    $total_simpanan_masuk = $res_simpanan ? $res_simpanan->fetch_assoc()['total'] : 0;

                    $res_penarikan = $conn->query("SELECT SUM(jumlah) as total FROM penarikan_simpanan");
                    $total_penarikan = $res_penarikan ? $res_penarikan->fetch_assoc()['total'] : 0;

                    $total_simpanan = $total_simpanan_masuk - $total_penarikan;
                    ?>
                    <h6 class="text-muted text-uppercase mb-1">Total Saldo Simpanan</h6>
                    <h3 class="mb-0 fw-bold" style="color: var(--accent-red);">Rp
                        <?php echo number_format($total_simpanan, 0, ',', '.'); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Riwayat Transaksi Simpanan</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Nama Anggota</th>
                            <th>Jenis Simpanan</th>
                            <th class="text-end">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = $conn->query("
                            SELECT s.*, a.nama_anggota, a.no_anggota 
                            FROM simpanan s 
                            JOIN anggota a ON s.id_anggota = a.id_anggota 
                            ORDER BY s.id_simpanan ASC
                        ");
                        if ($query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $row['kode_simpanan']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_anggota']); ?></div>
                                        <small class="text-muted"><?php echo $row['no_anggota']; ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $jenis = $row['jenis_simpanan'];
                                        if ($jenis == 'pokok')
                                            echo '<span class="badge bg-primary">Pokok</span>';
                                        elseif ($jenis == 'wajib')
                                            echo '<span class="badge bg-info">Wajib</span>';
                                        else
                                            echo '<span class="badge bg-success">Sukarela</span>';
                                        ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        + <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi simpanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>