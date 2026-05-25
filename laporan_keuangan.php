<?php
session_start();
// Hanya Admin dan Bendahara yang boleh mengakses
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Bendahara')) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
include 'views/layouts/header.php';

// Ambil bulan dan tahun saat ini untuk filter default
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Fungsi pembantu untuk mengambil total
function getTotal($conn, $query) {
    $res = $conn->query($query);
    if ($res) {
        $row = $res->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

// 1. PENDAPATAN
// Penjualan Toko
$pendapatan_toko = getTotal($conn, "SELECT SUM(total) as total FROM penjualan WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");

// Bunga Pinjaman (Estimasi dari proporsi bunga terhadap total angsuran)
// Kita hitung total angsuran masuk, dan perkirakan porsi bunganya berdasarkan pinjaman terkait
$q_angsuran = $conn->query("
    SELECT a.jumlah_bayar, p.jumlah_pinjaman, p.bunga 
    FROM angsuran a 
    JOIN pinjaman p ON a.id_pinjaman = p.id_pinjaman 
    WHERE MONTH(a.tanggal_bayar) = '$bulan' AND YEAR(a.tanggal_bayar) = '$tahun'
");
$pendapatan_bunga = 0;
$total_angsuran_masuk = 0;
if ($q_angsuran) {
    while ($row = $q_angsuran->fetch_assoc()) {
        $total_angsuran_masuk += $row['jumlah_bayar'];
        // Proporsi bunga = Bunga / (100 + Bunga) dari total yang dibayar jika fix, tapi untuk gampangnya:
        // Asumsi rumus flat: Total Pokok + (Total Pokok * Bunga%) = Total Bayar
        $pokok = $row['jumlah_pinjaman'];
        $bunga_rp = $pokok * ($row['bunga'] / 100);
        $total_hutang = $pokok + $bunga_rp;
        // Porsi bunga dalam setiap pembayaran
        $porsi_bunga = $bunga_rp / $total_hutang;
        $pendapatan_bunga += ($row['jumlah_bayar'] * $porsi_bunga);
    }
}
$total_pendapatan = $pendapatan_toko + $pendapatan_bunga;

// 2. PENGELUARAN / BEBAN
// Harga Pokok Penjualan (HPP) / Pembelian Barang
$pengeluaran_toko = getTotal($conn, "SELECT SUM(total) as total FROM pembelian WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");

// Beban Operasional (Diambil dari jurnal umum jika ada, kita buat placeholder atau cek tabel detail_jurnal)
$beban_operasional = getTotal($conn, "
    SELECT SUM(dj.debit) as total 
    FROM detail_jurnal dj 
    JOIN akun a ON dj.id_akun = a.id_akun 
    JOIN jurnal_umum ju ON dj.id_jurnal = ju.id_jurnal
    WHERE a.tipe = 'beban' AND MONTH(ju.tanggal) = '$bulan' AND YEAR(ju.tanggal) = '$tahun'
");

$total_pengeluaran = $pengeluaran_toko + $beban_operasional;

// LABA BERSIH
$laba_bersih = $total_pendapatan - $total_pengeluaran;

// 3. ARUS KAS (CASH FLOW)
$kas_masuk_simpanan = getTotal($conn, "SELECT SUM(jumlah) as total FROM simpanan WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");
$kas_keluar_pinjaman = getTotal($conn, "SELECT SUM(jumlah_pinjaman) as total FROM pinjaman WHERE status IN ('approved', 'lunas') AND MONTH(tanggal_pinjam) = '$bulan' AND YEAR(tanggal_pinjam) = '$tahun'");
// Catatan: penarikan simpanan tabelnya tidak selalu ada di beberapa versi dump, kita cek dengan show tables
$cek_tabel = $conn->query("SHOW TABLES LIKE 'penarikan_simpanan'");
$kas_keluar_simpanan = 0;
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $kas_keluar_simpanan = getTotal($conn, "SELECT SUM(jumlah) as total FROM penarikan_simpanan WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");
}

$total_kas_masuk = $pendapatan_toko + $total_angsuran_masuk + $kas_masuk_simpanan;
$total_kas_keluar = $pengeluaran_toko + $beban_operasional + $kas_keluar_pinjaman + $kas_keluar_simpanan;
$net_cash_flow = $total_kas_masuk - $total_kas_keluar;

$nama_bulan = array(
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-chart-pie me-2"></i> Laporan Keuangan</h1>
        <button class="btn btn-crimson" onclick="window.print()"><i class="fas fa-print me-1"></i> Cetak Laporan</button>
    </div>
</div>

<section class="content">
    <div class="card shadow-sm border-0 mb-4 d-print-none">
        <div class="card-body">
            <form method="GET" action="" class="row align-items-end">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php foreach($nama_bulan as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo $bulan == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php 
                        $thn_sekarang = date('Y');
                        for($i = $thn_sekarang - 2; $i <= $thn_sekarang + 1; $i++): 
                        ?>
                            <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cetak Header Khusus Print -->
    <div class="d-none d-print-block text-center mb-4">
        <h2>KOPERASI MERAH PUTIH</h2>
        <h4>LAPORAN KEUANGAN</h4>
        <p>Periode: <?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></p>
        <hr>
    </div>

    <div class="row">
        <!-- LABA RUGI -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-elegant text-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="fas fa-balance-scale me-2"></i> Laporan Laba Rugi</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Pendapatan</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Penjualan Toko</span>
                        <span>Rp <?php echo number_format($pendapatan_toko, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pendapatan Bunga Pinjaman</span>
                        <span>Rp <?php echo number_format($pendapatan_bunga, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mb-4 bg-light p-2 rounded">
                        <span>Total Pendapatan</span>
                        <span class="text-success">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></span>
                    </div>

                    <h6 class="text-danger fw-bold mb-3 border-bottom pb-2">Pengeluaran / Beban</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pembelian Barang (Toko)</span>
                        <span>Rp <?php echo number_format($pengeluaran_toko, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Beban Operasional</span>
                        <span>Rp <?php echo number_format($beban_operasional, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mb-4 bg-light p-2 rounded">
                        <span>Total Pengeluaran</span>
                        <span class="text-danger">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></span>
                    </div>

                    <div class="d-flex justify-content-between fw-bold p-3 rounded <?php echo $laba_bersih >= 0 ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                        <span class="fs-5">LABA BERSIH</span>
                        <span class="fs-5">Rp <?php echo number_format($laba_bersih, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ARUS KAS -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="fas fa-exchange-alt me-2"></i> Laporan Arus Kas</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-success fw-bold mb-3 border-bottom pb-2">Arus Kas Masuk</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pendapatan Penjualan Tunai</span>
                        <span>Rp <?php echo number_format($pendapatan_toko, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Setoran Angsuran Pinjaman</span>
                        <span>Rp <?php echo number_format($total_angsuran_masuk, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Setoran Simpanan Anggota</span>
                        <span>Rp <?php echo number_format($kas_masuk_simpanan, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mb-4 bg-light p-2 rounded">
                        <span>Total Kas Masuk</span>
                        <span class="text-success">Rp <?php echo number_format($total_kas_masuk, 0, ',', '.'); ?></span>
                    </div>

                    <h6 class="text-danger fw-bold mb-3 border-bottom pb-2">Arus Kas Keluar</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pembelian Barang Tunai</span>
                        <span>Rp <?php echo number_format($pengeluaran_toko, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Beban Operasional Dibayar</span>
                        <span>Rp <?php echo number_format($beban_operasional, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pencairan Pinjaman Anggota</span>
                        <span>Rp <?php echo number_format($kas_keluar_pinjaman, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Penarikan Simpanan</span>
                        <span>Rp <?php echo number_format($kas_keluar_simpanan, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mb-4 bg-light p-2 rounded">
                        <span>Total Kas Keluar</span>
                        <span class="text-danger">Rp <?php echo number_format($total_kas_keluar, 0, ',', '.'); ?></span>
                    </div>

                    <div class="d-flex justify-content-between fw-bold p-3 rounded <?php echo $net_cash_flow >= 0 ? 'bg-primary text-white' : 'bg-warning text-dark'; ?>">
                        <span class="fs-5">NET CASH FLOW</span>
                        <span class="fs-5">Rp <?php echo number_format($net_cash_flow, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    @media print {
        body { background-color: #fff; }
        .wrapper { margin: 0; padding: 0; }
        .sidebar, .main-header, .btn, form { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; background-color: white !important; }
        .card { border: none !important; box-shadow: none !important; }
        .bg-light, .bg-success, .bg-danger, .bg-elegant, .bg-dark, .bg-primary { 
            background-color: transparent !important; 
            color: black !important;
            border: 1px solid #ddd;
        }
    }
</style>

<?php include 'views/layouts/footer.php'; ?>
