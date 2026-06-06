<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Koperasi Merah Putih</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom AdminLTE-style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="index.php" class="brand-link">
                <img src="assets/img/logo.png" alt="Logo Koperasi" class="brand-image">
                <span class="brand-text">Koperasi <b>MP</b></span>
            </a>
            
            <?php $current_role = $_SESSION['role'] ?? ''; ?>
            <nav class="nav-sidebar">
                <div class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>

                <?php if($current_role === 'Admin' || $current_role === 'Bendahara'): ?>
                <div class="nav-item">
                    <a href="anggota.php" class="nav-link">
                        <i class="fas fa-users"></i> Master Anggota
                    </a>
                </div>
                <?php endif; ?>

                <?php if($current_role === 'Admin' || $current_role === 'Bendahara'): ?>
                <div class="nav-item">
                    <a href="simpanan.php" class="nav-link">
                        <i class="fas fa-money-check-alt"></i> Data Simpanan
                    </a>
                </div>
                <div class="nav-item">
                    <a href="pinjaman.php" class="nav-link">
                        <i class="fas fa-hand-holding-usd"></i> Data Pinjaman
                    </a>
                </div>
                <div class="nav-item">
                    <a href="angsuran.php" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i> Data Angsuran
                    </a>
                </div>
                <div class="nav-item">
                    <a href="penarikan.php" class="nav-link">
                        <i class="fas fa-hand-holding-usd"></i> Penarikan Simpanan
                    </a>
                </div>
                <?php endif; ?>

                <?php if($current_role === 'Admin' || $current_role === 'Bendahara'): ?>
                <div class="nav-item">
                    <a href="supplier.php" class="nav-link">
                        <i class="fas fa-truck"></i> Data Supplier
                    </a>
                </div>
                <div class="nav-item">
                    <a href="pembelian.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Pembelian (Restock)
                    </a>
                </div>
                <?php endif; ?>

                <?php if($current_role === 'Admin' || $current_role === 'Bendahara' || $current_role === 'Kasir'): ?>
                <div class="nav-item">
                    <a href="barang.php" class="nav-link">
                        <i class="fas fa-boxes"></i> Data Barang
                    </a>
                </div>
                <?php endif; ?>

                <?php if($current_role === 'Admin' || $current_role === 'Kasir'): ?>
                <div class="nav-item">
                    <a href="kasir.php" class="nav-link">
                        <i class="fas fa-desktop"></i> Transaksi Kasir
                    </a>
                </div>
                <div class="nav-item">
                    <a href="penjualan.php" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i> Riwayat Penjualan
                    </a>
                </div>
                <?php endif; ?>

                <?php if($current_role === 'Admin' || $current_role === 'Bendahara'): ?>
                <div class="nav-item">
                    <a href="kalkulator_shu.php" class="nav-link">
                        <i class="fas fa-calculator"></i> Kalkulator SHU
                    </a>
                </div>
                <div class="nav-item">
                    <a href="laporan_keuangan.php" class="nav-link">
                        <i class="fas fa-chart-pie"></i> Laporan Keuangan
                    </a>
                </div>
                <?php endif; ?>

                <?php if($current_role === 'Admin'): ?>
                <div class="nav-item">
                    <a href="manajemen_user.php" class="nav-link">
                        <i class="fas fa-users-cog"></i> Manajemen User
                    </a>
                </div>
                <?php endif; ?>
                <div class="nav-item mt-5">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Navbar -->
            <header class="main-header">
                <div>
                    <button class="btn btn-light" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y'); ?></span>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama'] ?? 'User'); ?>&background=C62828&color=fff" alt="User" class="rounded-circle" width="32">
                            <span class="ms-2 d-none d-md-inline"><?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?></span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
