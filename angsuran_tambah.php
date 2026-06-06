<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';

$filter_pinjaman = isset($_GET['id_pinjaman']) ? intval($_GET['id_pinjaman']) : null;
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus-circle me-2"></i> Input Angsuran</h1>
        <a href="angsuran.php<?php echo $filter_pinjaman ? '?id_pinjaman='.$filter_pinjaman : ''; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
</div>

<section class="content">
    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Form Pembayaran Angsuran</h3>
        </div>
        <div class="card-body">
            <form action="angsuran_proses.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pinjaman <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_pinjaman" id="id_pinjaman" required onchange="getPinjamanInfo()">
                            <option value="">-- Pilih Pinjaman (Hanya yang Disetujui) --</option>
                            <?php
                            $pinjaman_query = $conn->query("
                                SELECT p.*, a.nama_anggota, a.no_anggota 
                                FROM pinjaman p 
                                JOIN anggota a ON p.id_anggota = a.id_anggota 
                                WHERE p.status = 'approved' 
                                ORDER BY p.id_pinjaman DESC
                            ");
                            while ($pin = $pinjaman_query->fetch_assoc()):
                                $selected = ($filter_pinjaman == $pin['id_pinjaman']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $pin['id_pinjaman']; ?>" <?php echo $selected; ?>>
                                    <?php echo $pin['kode_pinjaman'] . ' - ' . htmlspecialchars($pin['nama_anggota']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_bayar" id="tanggal_bayar" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="jumlah_bayar" id="jumlah_bayar" min="1000" step="1000" required>
                        <small class="text-muted" id="rekomendasi_bayar"></small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Denda (Rp)</label>
                        <input type="number" class="form-control" name="denda" id="denda" min="0" value="0" required>
                        <small class="text-danger" id="info_denda" style="font-size: 0.8rem;"></small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cicilan Ke <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="cicilan_ke" id="cicilan_ke" min="1" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-crimson"><i class="fas fa-save me-1"></i> Simpan Pembayaran</button>
                    <button type="reset" class="btn btn-light">Reset</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- This could be done with AJAX, but for simplicity we load data in JS object if not too big, or use a simple fetch -->
<script>
    // Assuming simple fetch for demonstration or a direct API.
    // Let's create a quick mapping in JS since data isn't large
    const pinjamanData = {
    <?php
    $pinjaman_query->data_seek(0);
    while ($pin = $pinjaman_query->fetch_assoc()) {
        // total angsuran terbayar
        $id = $pin['id_pinjaman'];
        $q_ang = $conn->query("SELECT COUNT(*) as jml, SUM(jumlah_bayar) as tot FROM angsuran WHERE id_pinjaman = $id");
        $ang = $q_ang->fetch_assoc();
        $cicilan_ke = $ang['jml'] + 1;
        
        $pokok = $pin['jumlah_pinjaman'];
        $bunga_persen = $pin['bunga'];
        $bunga_rp = $pokok * ($bunga_persen / 100);
        $total_hutang = $pokok + $bunga_rp;
        $lama = $pin['lama_angsuran'];
        
        $cicilan_per_bulan = ceil($total_hutang / $lama);
        $tanggal_pinjam = $pin['tanggal_pinjam'];
        
        echo "$id: { cicilan_ke: $cicilan_ke, cicilan_per_bulan: $cicilan_per_bulan, tanggal_pinjam: '$tanggal_pinjam' },\n";
    }
    ?>
    };

    function getPinjamanInfo() {
        const id = document.getElementById('id_pinjaman').value;
        if (id && pinjamanData[id]) {
            document.getElementById('cicilan_ke').value = pinjamanData[id].cicilan_ke;
            document.getElementById('jumlah_bayar').value = pinjamanData[id].cicilan_per_bulan;
            document.getElementById('rekomendasi_bayar').innerText = "Rekomendasi cicilan per bulan: Rp " + new Intl.NumberFormat('id-ID').format(pinjamanData[id].cicilan_per_bulan);
            hitungDenda(id);
        } else {
            document.getElementById('cicilan_ke').value = '';
            document.getElementById('jumlah_bayar').value = '';
            document.getElementById('rekomendasi_bayar').innerText = '';
            document.getElementById('denda').value = '0';
            document.getElementById('info_denda').innerText = '';
        }
    }

    function hitungDenda(id) {
        if(!id || !pinjamanData[id]) return;
        
        const tanggal_bayar = new Date(document.getElementById('tanggal_bayar').value);
        const tanggal_pinjam = new Date(pinjamanData[id].tanggal_pinjam);
        const cicilan_ke = parseInt(pinjamanData[id].cicilan_ke);
        
        // Jatuh tempo: tanggal pinjam + (cicilan_ke * bulan)
        let jatuh_tempo = new Date(tanggal_pinjam);
        jatuh_tempo.setMonth(jatuh_tempo.getMonth() + cicilan_ke);
        
        const info_denda = document.getElementById('info_denda');
        const input_denda = document.getElementById('denda');
        
        tanggal_bayar.setHours(0,0,0,0);
        jatuh_tempo.setHours(0,0,0,0);
        
        const diffTime = tanggal_bayar.getTime() - jatuh_tempo.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) {
            // Denda Rp 1.000 per hari
            const dendaRp = diffDays * 1000;
            input_denda.value = dendaRp;
            info_denda.innerText = `Terlambat ${diffDays} hari (Jatuh tempo: ${jatuh_tempo.toLocaleDateString('id-ID')})`;
            info_denda.className = "text-danger";
        } else {
            input_denda.value = 0;
            info_denda.innerText = `Belum jatuh tempo (Jatuh tempo: ${jatuh_tempo.toLocaleDateString('id-ID')})`;
            info_denda.className = "text-success";
        }
    }

    document.getElementById('tanggal_bayar').addEventListener('change', function() {
        const id = document.getElementById('id_pinjaman').value;
        hitungDenda(id);
    });

    // Initialize if pre-selected
    window.onload = function() {
        if(document.getElementById('id_pinjaman').value) {
            getPinjamanInfo();
        }
    }
</script>

<?php include 'views/layouts/footer.php'; ?>
