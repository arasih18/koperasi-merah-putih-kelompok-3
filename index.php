<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include Konfigurasi Database
require_once 'config/database.php';

// Cek jumlah anggota dari tabel database
$result = $conn->query("SELECT COUNT(*) as total FROM anggota WHERE status = 'aktif'");
$total_anggota = $result ? $result->fetch_assoc()['total'] : 0;

// Hitung total aset (simpanan - penarikan)
$res_simpanan = $conn->query("SELECT SUM(jumlah) as total FROM simpanan");
$total_simpanan_masuk = $res_simpanan ? $res_simpanan->fetch_assoc()['total'] : 0;

$res_penarikan = $conn->query("SELECT SUM(jumlah) as total FROM penarikan_simpanan");
$total_penarikan = $res_penarikan ? $res_penarikan->fetch_assoc()['total'] : 0;

$total_aset = $total_simpanan_masuk - $total_penarikan;

// Menghitung jumlah barang di minimarket
$res_barang = $conn->query("SELECT COUNT(*) as total FROM barang");
$total_barang = $res_barang ? $res_barang->fetch_assoc()['total'] : 0;

// Data untuk Grafik Pertumbuhan (Pendapatan Penjualan 6 Bulan Terakhir)
$chart_labels = [];
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('m', strtotime("-$i months"));
    $y = date('Y', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $q_chart = $conn->query("SELECT SUM(total) as total FROM penjualan WHERE MONTH(tanggal) = '$m' AND YEAR(tanggal) = '$y'");
    $tot = $q_chart ? (float)$q_chart->fetch_assoc()['total'] : 0;
    
    $chart_labels[] = $month_name;
    $chart_data[] = $tot;
}

// Include Header
include 'views/layouts/header.php';
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard Utama</h1>
            </div>
        </div>
        <!-- Welcome Message -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="alert alert-light alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #C62828;">
                    <i class="fas fa-hand-sparkles text-warning me-2"></i>
                    <strong>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?>!</strong> 
                    Anda berhasil login sebagai <span class="badge bg-crimson"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Anggota'); ?></span>. Semoga harimu produktif!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-crimson">
                    <div class="inner">
                        <h3><?php echo $total_anggota; ?></h3>
                        <p>Total Anggota</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <a href="anggota.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-navy">
                    <div class="inner">
                        <h3>Rp <?php echo number_format($total_aset, 0, ',', '.'); ?></h3>
                        <p>Total Aset (Simpanan)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="laporan_keuangan.php" class="small-box-footer">Laporan <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <?php
                $bulan_ini = date('m');
                $tahun_ini = date('Y');
                $q_pendapatan = $conn->query("SELECT SUM(total) as total FROM penjualan WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'");
                $pendapatan_bulan_ini = $q_pendapatan ? $q_pendapatan->fetch_assoc()['total'] : 0;
                ?>
                <div class="small-box bg-success text-white">
                    <div class="inner">
                        <h3>Rp <?php echo number_format($pendapatan_bulan_ini ?? 0, 0, ',', '.'); ?></h3>
                        <p>Pendapatan Bulan Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <a href="laporan_keuangan.php" class="small-box-footer" style="color: rgba(255,255,255,0.8);">Buka Laporan <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-slate">
                    <div class="inner">
                        <h3><?php echo $total_barang; ?></h3>
                        <p>Produk Toko</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <a href="barang.php" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>

        <div class="row mt-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title fw-bold">Grafik Pendapatan Toko (6 Bulan Terakhir)</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; width: 100%;">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title fw-bold">Akses Cepat</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if($_SESSION['role'] === 'Admin'): ?>
                            <li class="list-group-item p-3">
                                <a href="anggota_tambah.php" class="btn btn-elegant w-100 text-start"><i class="fas fa-user-plus me-2"></i> Pendaftaran Anggota</a>
                            </li>
                            <?php endif; ?>

                            <?php if($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Bendahara'): ?>
                            <li class="list-group-item p-3">
                                <a href="simpanan_tambah.php" class="btn btn-elegant w-100 text-start"><i class="fas fa-hand-holding-usd me-2"></i> Input Simpanan</a>
                            </li>
                            <?php endif; ?>

                            <?php if($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Kasir'): ?>
                            <li class="list-group-item p-3">
                                <a href="kasir.php" class="btn btn-elegant w-100 text-start"><i class="fas fa-shopping-cart me-2"></i> Transaksi Kasir</a>
                            </li>
                            <?php endif; ?>

                            <?php if($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Bendahara'): ?>
                            <li class="list-group-item p-3">
                                <a href="kalkulator_shu.php" class="btn btn-elegant w-100 text-start"><i class="fas fa-calculator me-2"></i> Kalkulator SHU</a>
                            </li>
                            <?php endif; ?>
                            <li class="list-group-item p-3">
                                <button class="btn btn-outline-secondary w-100 text-start"><i class="fas fa-print me-2"></i> Cetak Laporan</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('growthChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(198, 40, 40, 0.5)'); // Crimson color transparent
        gradient.addColorStop(1, 'rgba(198, 40, 40, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Pendapatan Toko (Rp)',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: '#C62828',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#C62828',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#C62828',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                return 'Rp ' + value;
                            }
                        },
                        grid: { borderDash: [5, 5], color: '#e9ecef' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>

<?php
// Include Footer
include 'views/layouts/footer.php';
?>
