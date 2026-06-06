<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus-circle me-2"></i> Tambah Supplier Baru</h1>
        <a href="supplier.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
</div>

<section class="content">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-crimson text-white">
                    <h5 class="card-title mb-0">Form Data Supplier</h5>
                </div>
                <div class="card-body">
                    <form action="supplier_proses.php?action=tambah" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_supplier" required placeholder="Masukkan nama supplier atau perusahaan">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">No HP / Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_hp" required placeholder="Contoh: 08123456789">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" name="alamat" rows="3" placeholder="Masukkan alamat lengkap supplier"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-light me-md-2">Reset</button>
                            <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
