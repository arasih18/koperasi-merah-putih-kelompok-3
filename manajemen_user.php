<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';

$action = $_GET['action'] ?? '';

if ($action === 'reset') {
    $id = intval($_GET['id']);
    // Hash password default '123456'
    $hashed_password = password_hash('123456', PASSWORD_DEFAULT);
    
    $query = $conn->query("UPDATE users SET password = '$hashed_password' WHERE id_user = $id");
    if ($query) {
        // Catat ke audit log
        $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'UPDATE', 'users', 'Reset password user ID $id menjadi default')");
        $_SESSION['success'] = "Password berhasil di-reset menjadi '123456'.";
    } else {
        $_SESSION['error'] = "Gagal mereset password: " . $conn->error;
    }
    header("Location: manajemen_user.php");
    exit;
}

include 'views/layouts/header.php';
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-users-cog me-2"></i> Manajemen User Sistem</h1>
    </div>
</div>

<section class="content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header border-0 pt-3">
            <h3 class="card-title fw-bold">Daftar Pengguna (Admin, Bendahara, Kasir)</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role / Posisi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = $conn->query("
                            SELECT u.*, r.nama_role 
                            FROM users u 
                            JOIN roles r ON u.id_role = r.id_role 
                            ORDER BY r.id_role ASC, u.nama ASC
                        ");
                        if ($query->num_rows > 0):
                            while ($row = $query->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <?php 
                                    $badge_color = 'bg-secondary';
                                    if($row['nama_role'] == 'Admin') $badge_color = 'bg-danger';
                                    elseif($row['nama_role'] == 'Bendahara') $badge_color = 'bg-primary';
                                    elseif($row['nama_role'] == 'Kasir') $badge_color = 'bg-success';
                                ?>
                                <span class="badge <?php echo $badge_color; ?>"><?php echo htmlspecialchars($row['nama_role']); ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($row['status'] == 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="manajemen_user.php?action=reset&id=<?php echo $row['id_user']; ?>" class="btn btn-sm btn-warning text-dark" onclick="return confirm('Apakah Anda yakin ingin mereset password pengguna ini ke default (123456)?');" title="Reset Password">
                                    <i class="fas fa-key"></i> Reset
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data user.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-muted" style="font-size: 0.9em;">
                <i class="fas fa-info-circle me-1"></i> Jika user lupa password, Admin dapat meresetnya menjadi password default <strong>123456</strong>. User dapat login menggunakan password tersebut.
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
