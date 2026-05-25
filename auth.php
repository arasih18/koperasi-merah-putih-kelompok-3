<?php
session_start();
require_once 'config/database.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $query = $conn->query("SELECT users.*, roles.nama_role FROM users JOIN roles ON users.id_role = roles.id_role WHERE username = '$username' AND status = 'aktif'");
    
    if($query->num_rows > 0){
        $user = $query->fetch_assoc();
        
        // Verifikasi Password Hash
        if(password_verify($password, $user['password'])){
            // Simpan data ke Session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['nama_role'];
            $_SESSION['id_role'] = $user['id_role'];
            
            // Catat ke audit log
            $conn->query("INSERT INTO audit_log (id_user, aksi, nama_tabel, data_baru) VALUES (".$user['id_user'].", 'LOGIN', 'users', 'User berhasil login')");
            
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Password yang Anda masukkan salah!";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan atau akun tidak aktif!";
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
