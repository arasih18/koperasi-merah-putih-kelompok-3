<?php
session_start();
// Hanya Admin dan Bendahara yang boleh mengakses
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';

$filter_pinjaman = isset($_GET['id_pinjaman']) ? intval($_GET['id_pinjaman']) : null;
$pinjaman_info = null;

if ($filter_pinjaman) {
    $q_pinjaman = $conn->query("SELECT p.*, a.nama_anggota, a.no_anggota FROM pinjaman p JOIN anggota a ON p.id_anggota = a.id_anggota WHERE p.id_pinjaman = $filter_pinjaman");
    if ($q_pinjaman && $q_pinjaman->num_rows > 0) {
        $pinjaman_info = $q_pinjaman->fetch_assoc();
    }
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-file-invoice-dollar me-2"></i> Data Angsuran</h1>
            <?php if ($pinjaman_info): ?>
                <p class="text-muted mb-0 mt-1">Pinjaman: <strong><?php echo $pinjaman_info['kode_pinjaman']; ?></strong> -
                    <?php echo htmlspecialchars($pinjaman_info['nama_anggota']); ?></p>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($filter_pinjaman): ?>
                <a href="pinjaman.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            <?php endif; ?>
            <a href="angsuran_tambah.php<?php echo $filter_pinjaman ? '?id_pinjaman=' . $filter_pinjaman : ''; ?>"
                class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Bayar Angsuran</a>
        </div>
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
                    $where_clause = $filter_pinjaman ? "WHERE id_pinjaman = $filter_pinjaman" : "";
                    $res_angsuran = $conn->query("SELECT SUM(jumlah_bayar) as total FROM angsuran $where_clause");
                    $total_angsuran = $res_angsuran ? $res_angsuran->fetch_assoc()['total'] : 0;
                    ?>
                    <h6 class="text-muted text-uppercase mb-1">Total Angsuran Diterima
                        <?php echo $filter_pinjaman ? ' (Pinjaman Ini)' : ''; ?></h6>
                    <h3 class="mb-0 fw-bold" style="color: var(--accent-red);">Rp
                        <?php echo number_format($total_angsuran, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <?php if ($pinjaman_info): ?>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <?php
                        // Hitung total hutang (pokok + bunga)
                        $pokok = $pinjaman_info['jumlah_pinjaman'];
                        $bunga_persen = $pinjaman_info['bunga'];
                        $bunga_rp = $pokok * ($bunga_persen / 100);
                        $total_harus_dibayar = $pokok + $bunga_rp;
                        $sisa_hutang = $total_harus_dibayar - $total_angsuran;
                        ?>
                        <h6 class="text-muted text-uppercase mb-1">Sisa Hutang</h6>
                        <h3 class="mb-0 fw-bold <?php echo $sisa_hutang <= 0 ? 'text-success' : 'text-danger'; ?>">
                            Rp <?php echo number_format(max(0, $sisa_hutang), 0, ',', '.'); ?>
                        </h3>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Riwayat Pembayaran Angsuran</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <?php if (!$filter_pinjaman): ?>
                                <th>Kode Pinjaman</th>
                                <th>Nama Anggota</th>
                            <?php endif; ?>
                            <th class="text-center">Cicilan Ke</th>
                            <th>Tanggal Bayar</th>
                            <th class="text-end">Denda (Rp)</th>
                            <th class="text-end">Jumlah Bayar (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = "
                            SELECT ang.*, p.kode_pinjaman, a.nama_anggota, a.no_anggota 
                            FROM angsuran ang 
                            JOIN pinjaman p ON ang.id_pinjaman = p.id_pinjaman 
                            JOIN anggota a ON p.id_anggota = a.id_anggota 
                        ";
                        if ($filter_pinjaman) {
                            $sql .= " WHERE ang.id_pinjaman = $filter_pinjaman ";
                        }
                        $sql .= " ORDER BY ang.id_angsuran ASC ";

                        $query = $conn->query($sql);
                        if ($query && $query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <?php if (!$filter_pinjaman): ?>
                                        <td><span class="badge bg-secondary"><?php echo $row['kode_pinjaman']; ?></span></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['nama_anggota']); ?></div>
                                            <small class="text-muted"><?php echo $row['no_anggota']; ?></small>
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-center"><span
                                            class="badge bg-info">Ke-<?php echo $row['cicilan_ke']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal_bayar'])); ?></td>
                                    <td class="text-end text-danger">
                                        <?php echo isset($row['denda']) && $row['denda'] > 0 ? '+ ' . number_format($row['denda'], 0, ',', '.') : '-'; ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        + <?php echo number_format($row['jumlah_bayar'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="<?php echo $filter_pinjaman ? '4' : '6'; ?>"
                                    class="text-center py-4 text-muted">Belum ada data angsuran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>