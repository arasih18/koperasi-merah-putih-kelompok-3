<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-truck me-2"></i> Master Data Supplier</h1>
        <a href="supplier_tambah.php" class="btn btn-crimson"><i class="fas fa-plus me-1"></i> Tambah Supplier</a>
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
                            <th>Nama Supplier</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th>Tanggal Ditambahkan</th>
                            <th class="text-center" width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_str = "SELECT * FROM supplier ORDER BY nama_supplier ASC";
                        $query = $conn->query($query_str);
                        if($query->num_rows > 0):
                            while($row = $query->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['nama_supplier']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                            <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td class="text-center">
                                <a href="supplier_edit.php?id=<?php echo $row['id_supplier']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="supplier_proses.php?action=hapus&id=<?php echo $row['id_supplier']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data supplier ini? Pastikan supplier ini tidak sedang digunakan pada transaksi pembelian.');" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data supplier.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
