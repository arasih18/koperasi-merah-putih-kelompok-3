<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Kasir')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

if(!isset($_GET['id'])) {
    header("Location: supplier.php");
    exit;
}

$id = $conn->real_escape_string($_GET['id']);
$query = $conn->query("SELECT * FROM supplier WHERE id_supplier = '$id'");

if($query->num_rows == 0) {
    $_SESSION['error'] = "Data supplier tidak ditemukan!";
    header("Location: supplier.php");
    exit;
}

$supplier = $query->fetch_assoc();
include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-edit me-2"></i> Edit Data Supplier</h1>
        <a href="supplier.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
</div>

<section class="content">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Form Update Supplier</h5>
                </div>
                <div class="card-body">
                    <form action="supplier_proses.php?action=edit" method="POST">
                        <input type="hidden" name="id_supplier" value="<?php echo $supplier['id_supplier']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_supplier" value="<?php echo htmlspecialchars($supplier['nama_supplier']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">No HP / Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_hp" value="<?php echo htmlspecialchars($supplier['no_hp']); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($supplier['alamat']); ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
