<?php
session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = $conn->query("SELECT * FROM anggota WHERE id_anggota = $id AND status = 'aktif'");

if($query->num_rows == 0) {
    $_SESSION['error'] = "Data anggota tidak ditemukan!";
    header("Location: anggota.php");
    exit;
}

$data = $query->fetch_assoc();

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex align-items-center">
        <a href="anggota.php" class="btn btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1 class="m-0"><i class="fas fa-user-edit me-2"></i> Edit Data Anggota</h1>
    </div>
</div>

<section class="content">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <form action="anggota_proses.php?action=edit" method="POST">
                <input type="hidden" name="id_anggota" value="<?php echo $data['id_anggota']; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nomor Anggota</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['no_anggota']); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Gabung</label>
                        <input type="date" name="tanggal_gabung" class="form-control" value="<?php echo $data['tanggal_gabung']; ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_anggota" class="form-control" value="<?php echo htmlspecialchars($data['nama_anggota']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jenis Kelamin</label>
                    <select name="jk" class="form-select" required>
                        <option value="L" <?php echo $data['jk'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo $data['jk'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($data['no_hp']); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
