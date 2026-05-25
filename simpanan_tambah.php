<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

// Generate otomatis Kode Simpanan (contoh format: SIM-001)
$query_no = $conn->query("SELECT MAX(CAST(SUBSTRING(kode_simpanan, 5) AS UNSIGNED)) as last_no FROM simpanan WHERE kode_simpanan LIKE 'SIM-%'");
$data_no = $query_no->fetch_assoc();
$next_no = $data_no['last_no'] ? $data_no['last_no'] + 1 : 1;
$kode_simpanan_baru = 'SIM-' . str_pad($next_no, 3, '0', STR_PAD_LEFT);

// Ambil daftar anggota aktif
$anggota_query = $conn->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota WHERE status = 'aktif' ORDER BY nama_anggota ASC");

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex align-items-center">
        <a href="simpanan.php" class="btn btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1 class="m-0"><i class="fas fa-hand-holding-usd me-2"></i> Input Transaksi Simpanan</h1>
    </div>
</div>

<section class="content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <form action="simpanan_proses.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kode Transaksi</label>
                        <input type="text" name="kode_simpanan" class="form-control"
                            value="<?php echo $kode_simpanan_baru; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Transaksi</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Anggota</label>
                    <div class="d-flex">
                        <select name="id_anggota" class="form-select me-2" required>
                            <option value="">-- Pilih Anggota Koperasi --</option>
                            <?php while ($agt = $anggota_query->fetch_assoc()): ?>
                                <option value="<?php echo $agt['id_anggota']; ?>">
                                    <?php echo $agt['no_anggota']; ?> -
                                    <?php echo htmlspecialchars($agt['nama_anggota']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="button" class="btn btn-outline-crimson text-nowrap" data-bs-toggle="modal"
                            data-bs-target="#modalTambahAnggota">
                            <i class="fas fa-user-plus"></i> Baru
                        </button>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jenis Simpanan</label>
                        <select name="jenis_simpanan" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="pokok">Simpanan Pokok (Bayar 1x saat daftar)</option>
                            <option value="wajib">Simpanan Wajib (Rutin tiap bulan)</option>
                            <option value="sukarela">Simpanan Sukarela (Bebas)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jumlah Uang (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="jumlah" class="form-control" placeholder="Contoh: 50000"
                                min="1000" required>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-light me-2">Reset</button>
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Simpan
                        Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Modal Tambah Anggota Cepat -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahAnggotaLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahAnggotaLabel"><i class="fas fa-user-plus"></i> Tambah Anggota
                    Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="anggota_proses.php?action=tambah&redirect=simpanan_tambah" method="POST">
                <div class="modal-body">
                    <?php
                    // Generate otomatis Nomor Anggota untuk modal
                    $query_no_agt = $conn->query("SELECT MAX(CAST(SUBSTRING(no_anggota, 3) AS UNSIGNED)) as last_no FROM anggota WHERE no_anggota LIKE 'AG%'");
                    $data_no_agt = $query_no_agt->fetch_assoc();
                    $next_no_agt = $data_no_agt['last_no'] ? $data_no_agt['last_no'] + 1 : 1;
                    $no_anggota_baru = 'AG' . str_pad($next_no_agt, 3, '0', STR_PAD_LEFT);
                    ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor Anggota</label>
                        <input type="text" name="no_anggota" class="form-control"
                            value="<?php echo $no_anggota_baru; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama_anggota" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Kelamin</label>
                        <select name="jk" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">No HP</label>
                        <input type="text" name="no_hp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2" required></textarea>
                    </div>
                    <input type="hidden" name="tanggal_gabung" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save"></i> Simpan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>