<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
$kategori_query = $conn->query("SELECT * FROM kategori_barang ORDER BY nama_kategori ASC");

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex align-items-center">
        <a href="barang.php" class="btn btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1 class="m-0"><i class="fas fa-box-open me-2"></i> Tambah Barang Baru</h1>
    </div>
</div>

<section class="content">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <form action="barang_proses.php?action=tambah" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kode Barang</label>
                        <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: BRG001"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($k = $kategori_query->fetch_assoc()): ?>
                                <option value="<?php echo $k['id_kategori']; ?>">
                                    <?php echo htmlspecialchars($k['nama_kategori']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Harga Beli (Rp)</label>
                        <input type="number" name="harga_beli" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Stok Awal</label>
                        <input type="number" name="stok" class="form-control" min="0" value="0" required>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-light me-2">Reset</button>
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Simpan
                        Barang</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>