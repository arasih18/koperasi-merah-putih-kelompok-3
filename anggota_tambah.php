<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

// Generate otomatis Nomor Anggota (contoh format: AG00X)
$query_no = $conn->query("SELECT MAX(CAST(SUBSTRING(no_anggota, 3) AS UNSIGNED)) as last_no FROM anggota WHERE no_anggota LIKE 'AG%'");
$data_no = $query_no->fetch_assoc();
$next_no = $data_no['last_no'] ? $data_no['last_no'] + 1 : 1;
$no_anggota_baru = 'AG' . str_pad($next_no, 3, '0', STR_PAD_LEFT);

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex align-items-center">
        <a href="anggota.php" class="btn btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1 class="m-0"><i class="fas fa-user-plus me-2"></i> Tambah Anggota Baru</h1>
    </div>
</div>

<section class="content">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <form action="anggota_proses.php?action=tambah" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nomor Anggota</label>
                        <input type="text" name="no_anggota" class="form-control" value="<?php echo $no_anggota_baru; ?>" readonly>
                        <small class="text-muted">Dibuat otomatis oleh sistem</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Gabung</label>
                        <input type="date" name="tanggal_gabung" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_anggota" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jenis Kelamin</label>
                    <select name="jk" class="form-select" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat domisili" required></textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-light me-2">Reset</button>
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
