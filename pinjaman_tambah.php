<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus-circle me-2"></i> Pengajuan Pinjaman</h1>
        <a href="pinjaman.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
</div>

<section class="content">
    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Form Pengajuan Pinjaman</h3>
        </div>
        <div class="card-body">
            <form action="pinjaman_proses.php?action=add" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Anggota <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_anggota" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php
                            $anggota_query = $conn->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota WHERE status = 'aktif' ORDER BY nama_anggota ASC");
                            while ($anggota = $anggota_query->fetch_assoc()):
                            ?>
                                <option value="<?php echo $anggota['id_anggota']; ?>">
                                    <?php echo $anggota['no_anggota'] . ' - ' . htmlspecialchars($anggota['nama_anggota']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Pengajuan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jumlah Pinjaman (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="jumlah_pinjaman" min="10000" step="1000" placeholder="Contoh: 1000000" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lama Angsuran (Bulan) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="lama_angsuran" min="1" max="60" placeholder="Contoh: 12" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bunga (%) per Tahun <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="bunga" step="0.01" min="0" placeholder="Contoh: 5.0" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Ajukan Pinjaman</button>
                    <button type="reset" class="btn btn-light">Reset</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
