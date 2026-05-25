<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

// Generate otomatis Kode Penarikan (contoh format: TRK-001)
$query_no = $conn->query("SELECT MAX(CAST(SUBSTRING(kode_penarikan, 5) AS UNSIGNED)) as last_no FROM penarikan_simpanan WHERE kode_penarikan LIKE 'TRK-%'");
$data_no = $query_no->fetch_assoc();
$next_no = $data_no['last_no'] ? $data_no['last_no'] + 1 : 1;
$kode_penarikan_baru = 'TRK-' . str_pad($next_no, 3, '0', STR_PAD_LEFT);

// Ambil daftar anggota aktif
$anggota_query = $conn->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota WHERE status = 'aktif' ORDER BY nama_anggota ASC");

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex align-items-center">
        <a href="penarikan.php" class="btn btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1 class="m-0"><i class="fas fa-hand-holding-usd me-2"></i> Input Penarikan Simpanan</h1>
    </div>
</div>

<section class="content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-warning">
        <i class="fas fa-info-circle me-1"></i> <strong>Informasi:</strong> Penarikan hanya dapat dilakukan pada <strong>Simpanan Sukarela</strong> dan harus menyisakan <strong>saldo mengendap minimal Rp 100.000</strong>.
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <form action="penarikan_proses.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kode Penarikan</label>
                        <input type="text" name="kode_penarikan" class="form-control"
                            value="<?php echo $kode_penarikan_baru; ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Penarikan</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Anggota</label>
                    <select name="id_anggota" id="id_anggota" class="form-select" required>
                        <option value="">-- Pilih Anggota Koperasi --</option>
                        <?php while ($agt = $anggota_query->fetch_assoc()): ?>
                            <option value="<?php echo $agt['id_anggota']; ?>">
                                <?php echo $agt['no_anggota']; ?> -
                                <?php echo htmlspecialchars($agt['nama_anggota']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div id="saldoInfo" class="mt-2 text-primary fw-bold" style="display:none;">
                        <i class="fas fa-wallet me-1"></i> Saldo Sukarela: Rp <span id="saldoValue">0</span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Jumlah Penarikan (Rp)</label>
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
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Proses Penarikan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>

<script>
document.getElementById('id_anggota').addEventListener('change', function() {
    var id = this.value;
    var infoDiv = document.getElementById('saldoInfo');
    var valSpan = document.getElementById('saldoValue');
    
    if(id) {
        fetch('get_saldo_sukarela.php?id_anggota=' + id)
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                infoDiv.style.display = 'block';
                // Format rupiah
                valSpan.innerText = new Intl.NumberFormat('id-ID').format(data.saldo_sukarela);
            }
        })
        .catch(error => console.error('Error fetching saldo:', error));
    } else {
        infoDiv.style.display = 'none';
    }
});
</script>
