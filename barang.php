<?php
session_start();
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Bendahara', 'Kasir'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-boxes me-2"></i> Data Barang Minimarket</h1>
        <?php if($_SESSION['role'] !== 'Kasir'): ?>
        <a href="barang_tambah.php" class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Tambah Barang</a>
        <?php endif; ?>
    </div>
</div>

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
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Harga Jual</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center" width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = $conn->query("
                            SELECT b.*, k.nama_kategori 
                            FROM barang b 
                            LEFT JOIN kategori_barang k ON b.id_kategori = k.id_kategori 
                            ORDER BY b.nama_barang ASC
                        ");
                        if($query->num_rows > 0):
                            while($row = $query->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $row['kode_barang']; ?></span></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                            <td class="text-end text-muted">Rp <?php echo number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                            <td class="text-end fw-bold text-success">Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <?php if($row['stok'] <= 5): ?>
                                    <span class="badge bg-danger"><?php echo $row['stok']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?php echo $row['stok']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($_SESSION['role'] !== 'Kasir'): ?>
                                <a href="barang_edit.php?id=<?php echo $row['id_barang']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if($_SESSION['role'] === 'Admin'): ?>
                                <a href="barang_proses.php?action=hapus&id=<?php echo $row['id_barang']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus barang ini?');" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data barang.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
