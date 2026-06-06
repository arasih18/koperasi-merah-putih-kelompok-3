<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: barang.php");
    exit;
}

$id = intval($_GET['id']);
$query = $conn->query("SELECT * FROM barang WHERE id_barang = $id");
if ($query->num_rows == 0) {
    header("Location: barang.php");
    exit;
}
$barang = $query->fetch_assoc();

$kategori_query = $conn->query("SELECT * FROM kategori_barang ORDER BY nama_kategori ASC");

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex align-items-center">
        <a href="barang.php" class="btn btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1 class="m-0"><i class="fas fa-edit me-2"></i> Edit Data Barang</h1>
    </div>
</div>

<section class="content">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <form action="barang_proses.php?action=edit" method="POST">
                <input type="hidden" name="id_barang" value="<?php echo $barang['id_barang']; ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kode Barang</label>
                        <input type="text" name="kode_barang" class="form-control"
                            value="<?php echo htmlspecialchars($barang['kode_barang']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($k = $kategori_query->fetch_assoc()): ?>
                                <option value="<?php echo $k['id_kategori']; ?>" <?php echo $k['id_kategori'] == $barang['id_kategori'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($k['nama_kategori']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control"
                        value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Harga Beli (Rp)</label>
                        <input type="number" name="harga_beli" class="form-control" min="0"
                            value="<?php echo $barang['harga_beli']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" min="0"
                            value="<?php echo $barang['harga_jual']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Stok</label>
                        <input type="number" name="stok" class="form-control" min="0"
                            value="<?php echo $barang['stok']; ?>" required>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Update
                        Barang</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>