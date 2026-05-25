<?php
session_start();
require_once 'config/database.php';

if(isset($_SESSION['user_id'])){
    // Catat log logout
    $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$_SESSION['user_id'].", 'LOGOUT', 'users', 'User berhasil logout')");
}

// Hancurkan semua sesi
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>
