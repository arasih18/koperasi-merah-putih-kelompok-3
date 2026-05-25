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
        <h1 class="m-0"><i class="fas fa-hand-holding-usd me-2"></i> Data Pinjaman</h1>
        <a href="pinjaman_tambah.php" class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Ajukan Pinjaman</a>
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
                    $res_pinjaman = $conn->query("SELECT SUM(jumlah_pinjaman) as total FROM pinjaman WHERE status = 'approved' OR status = 'lunas'");
                    $total_pinjaman = $res_pinjaman ? $res_pinjaman->fetch_assoc()['total'] : 0;
                    ?>
                    <h6 class="text-muted text-uppercase mb-1">Total Pinjaman Disetujui</h6>
                    <h3 class="mb-0 fw-bold" style="color: var(--accent-red);">Rp
                        <?php echo number_format($total_pinjaman, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Riwayat Pinjaman</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Anggota</th>
                            <th class="text-end">Jumlah (Rp)</th>
                            <th class="text-center">Bunga / Tenor</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = $conn->query("
                            SELECT p.*, a.nama_anggota, a.no_anggota 
                            FROM pinjaman p 
                            JOIN anggota a ON p.id_anggota = a.id_anggota 
                            ORDER BY p.id_pinjaman ASC
                        ");
                        if ($query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $row['kode_pinjaman']; ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_anggota']); ?></div>
                                        <small class="text-muted"><?php echo $row['no_anggota']; ?></small>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        <?php echo number_format($row['jumlah_pinjaman'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo floatval($row['bunga']); ?>% / <?php echo $row['lama_angsuran']; ?> Bln
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $status = $row['status'];
                                        if ($status == 'pending')
                                            echo '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>';
                                        elseif ($status == 'approved')
                                            echo '<span class="badge bg-success"><i class="fas fa-check"></i> Disetujui</span>';
                                        elseif ($status == 'ditolak')
                                            echo '<span class="badge bg-danger"><i class="fas fa-times"></i> Ditolak</span>';
                                        elseif ($status == 'lunas')
                                            echo '<span class="badge bg-primary"><i class="fas fa-flag-checkered"></i> Lunas</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($status == 'pending' && $_SESSION['role'] === 'Admin'): ?>
                                            <a href="pinjaman_proses.php?action=approve&id=<?php echo $row['id_pinjaman']; ?>"
                                                class="btn btn-sm btn-success" title="Setujui"
                                                onclick="return confirm('Yakin ingin menyetujui pinjaman ini?')"><i
                                                    class="fas fa-check"></i></a>
                                            <a href="pinjaman_proses.php?action=reject&id=<?php echo $row['id_pinjaman']; ?>"
                                                class="btn btn-sm btn-danger" title="Tolak"
                                                onclick="return confirm('Yakin ingin menolak pinjaman ini?')"><i
                                                    class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                        <?php if ($status == 'approved'): ?>
                                            <a href="angsuran.php?id_pinjaman=<?php echo $row['id_pinjaman']; ?>"
                                                class="btn btn-sm btn-info text-white" title="Lihat Angsuran"><i
                                                    class="fas fa-list"></i> Angsuran</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada data pinjaman.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>