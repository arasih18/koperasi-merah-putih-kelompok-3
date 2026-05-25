<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';

// Cek apakah ada form submit
$laba_bersih = isset($_POST['laba_bersih']) ? floatval($_POST['laba_bersih']) : 0;
$persen_jua = isset($_POST['persen_jua']) ? floatval($_POST['persen_jua']) : 20; // Default 20%
$persen_jma = isset($_POST['persen_jma']) ? floatval($_POST['persen_jma']) : 25; // Default 25%

// Hitung total JUA dan JMA
$total_jua = ($persen_jua / 100) * $laba_bersih;
$total_jma = ($persen_jma / 100) * $laba_bersih;

// Ambil total simpanan seluruh anggota (TPM)
$q_tpm = $conn->query("SELECT SUM(jumlah) as total FROM simpanan");
$tpm = $q_tpm->fetch_assoc()['total'] ?? 0;
$tpm_divider = $tpm > 0 ? $tpm : 1; // Prevent division by zero

// Ambil total belanja seluruh anggota (TPA)
$q_tpa = $conn->query("SELECT SUM(total) as total FROM penjualan WHERE id_anggota IS NOT NULL");
$tpa = $q_tpa->fetch_assoc()['total'] ?? 0;
$tpa_divider = $tpa > 0 ? $tpa : 1; // Prevent division by zero

?>
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-calculator me-2"></i> Kalkulator SHU (Simulasi)</h1>
    </div>
</div>

<section class="content">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-elegant text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-sliders-h me-2"></i> Parameter SHU</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Total Laba Bersih Koperasi (Rp)</label>
                                <input type="number" class="form-control" name="laba_bersih"
                                    value="<?php echo $laba_bersih > 0 ? $laba_bersih : ''; ?>" min="0" required
                                    placeholder="Contoh: 10000000">
                                <small class="text-muted">Masukkan total laba koperasi periode ini.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Alokasi Jasa Usaha Anggota (%)</label>
                                <input type="number" class="form-control" name="persen_jua"
                                    value="<?php echo $persen_jua; ?>" min="0" max="100" step="0.1" required>
                                <small class="text-muted">Persentase alokasi untuk aktivitas transaksi/belanja.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Alokasi Jasa Modal Anggota (%)</label>
                                <input type="number" class="form-control" name="persen_jma"
                                    value="<?php echo $persen_jma; ?>" min="0" max="100" step="0.1" required>
                                <small class="text-muted">Persentase alokasi untuk kepemilikan simpanan.</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt me-1"></i> Kalkulasi
                            Sekarang</button>
                        <a href="kalkulator_shu.php" class="btn btn-secondary ms-2"><i class="fas fa-redo me-1"></i> Refresh</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <!-- Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body">
                        <h6>Dana Jasa Usaha (JUA)</h6>
                        <h4>Rp <?php echo number_format($total_jua, 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white text-center shadow-sm">
                    <div class="card-body">
                        <h6>Dana Jasa Modal (JMA)</h6>
                        <h4>Rp <?php echo number_format($total_jma, 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark text-center shadow-sm">
                    <div class="card-body">
                        <h6>Total Transaksi Anggota (TPA)</h6>
                        <h4>Rp <?php echo number_format($tpa, 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white text-center shadow-sm">
                    <div class="card-body">
                        <h6>Total Simpanan Anggota (TPM)</h6>
                        <h4>Rp <?php echo number_format($tpm, 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-3 pb-3">
                <h5 class="card-title mb-0 fw-bold text-success"><i class="fas fa-list-alt me-2"></i> Hasil Simulasi
                    Pembagian SHU per Anggota</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Anggota</th>
                                <th class="text-end">Total Belanja (SA)</th>
                                <th class="text-end">Total Simpanan (SM)</th>
                                <th class="text-end">Bagian Jasa Usaha</th>
                                <th class="text-end">Bagian Jasa Modal</th>
                                <th class="text-end bg-success bg-opacity-25 fw-bold text-dark">Total SHU Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_anggota = $conn->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota WHERE status='aktif' ORDER BY nama_anggota ASC");

                            if ($q_anggota->num_rows > 0):
                                while ($a = $q_anggota->fetch_assoc()):
                                    $id_anggota = $a['id_anggota'];

                                    // Ambil SA (Simpanan Anggota bersangkutan)
                                    $q_sa = $conn->query("SELECT SUM(total) as total FROM penjualan WHERE id_anggota = '$id_anggota'");
                                    $sa = $q_sa->fetch_assoc()['total'] ?? 0;

                                    // Ambil SM (Total Simpanan Anggota bersangkutan)
                                    $q_sm = $conn->query("SELECT SUM(jumlah) as total FROM simpanan WHERE id_anggota = '$id_anggota'");
                                    $sm = $q_sm->fetch_assoc()['total'] ?? 0;

                                    // Hitung SHU
                                    $bagian_jasa_usaha = ($total_jua * $sa / $tpa_divider);
                                    $bagian_jasa_modal = ($total_jma * $sm / $tpm_divider);
                                    $shu_anggota = $bagian_jasa_usaha + $bagian_jasa_modal;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($a['nama_anggota']); ?></div>
                                            <small class="text-muted"><i
                                                    class="fas fa-id-badge me-1"></i><?php echo htmlspecialchars($a['no_anggota']); ?></small>
                                        </td>
                                        <td class="text-end">Rp <?php echo number_format($sa, 0, ',', '.'); ?></td>
                                        <td class="text-end">Rp <?php echo number_format($sm, 0, ',', '.'); ?></td>
                                        <td class="text-end text-success">Rp
                                            <?php echo number_format($bagian_jasa_usaha, 0, ',', '.'); ?></td>
                                        <td class="text-end text-info">Rp
                                            <?php echo number_format($bagian_jasa_modal, 0, ',', '.'); ?></td>
                                        <td class="text-end bg-success bg-opacity-10 fw-bold text-success fs-6">Rp
                                            <?php echo number_format($shu_anggota, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada anggota aktif.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php include 'views/layouts/footer.php'; ?>