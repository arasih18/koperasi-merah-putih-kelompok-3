<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-users me-2"></i> Master Data Anggota</h1>
        <a href="anggota_tambah.php" class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Tambah Anggota</a>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>No Anggota</th>
                            <th>Nama Anggota</th>
                            <th>L/P</th>
                            <th>No HP</th>
                            <th>Tanggal Gabung</th>
                            <th class="text-end">Total Saldo (Rp)</th>
                            <th class="text-center" width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_str = "SELECT a.*, 
                                      COALESCE((SELECT SUM(jumlah) FROM simpanan WHERE id_anggota = a.id_anggota), 0) -
                                      COALESCE((SELECT SUM(jumlah) FROM penarikan_simpanan WHERE id_anggota = a.id_anggota), 0) AS total_saldo
                                      FROM anggota a 
                                      WHERE a.status = 'aktif' 
                                      ORDER BY a.id_anggota ASC";
                        $query = $conn->query($query_str);
                        if($query->num_rows > 0):
                            while($row = $query->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $row['no_anggota']; ?></span></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['nama_anggota']); ?></td>
                            <td><?php echo $row['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_gabung'])); ?></td>
                            <td class="text-end fw-bold text-success"><?php echo number_format($row['total_saldo'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="anggota_edit.php?id=<?php echo $row['id_anggota']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if($_SESSION['role'] === 'Admin'): ?>
                                <a href="anggota_keluar.php?id=<?php echo $row['id_anggota']; ?>" class="btn btn-sm btn-warning text-dark" onclick="return confirm('Anggota akan dikeluarkan dan seluruh saldonya akan ditarik. Yakin?');" title="Anggota Keluar"><i class="fas fa-sign-out-alt"></i></a>
                                <?php endif; ?>
                                <a href="anggota_proses.php?action=hapus&id=<?php echo $row['id_anggota']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data anggota ini?');" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data anggota.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
