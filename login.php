<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Koperasi Merah Putih</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            /* Latar belakang warna gradien elegan */
            background: linear-gradient(135deg, #2b3137 0%, #521616 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
            margin: 0;
        }

        .login-box {
            width: 420px;
            padding: 20px;
            z-index: 2;
            position: relative;
        }

        /* Glassmorphism Effect */
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            overflow: hidden;
            color: #ffffff;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            padding-top: 40px;
            padding-bottom: 20px;
        }

        .card-header img {
            width: 80px;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
        }

        .card-header h3 {
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 0.9rem;
        }

        /* Input Glassmorphism */
        .input-group-text {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-right: none !important;
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-left: none !important;
            color: #ffffff !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        .form-control:focus {
            box-shadow: none;
            background: rgba(255, 255, 255, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }

        /* Button Elegant Crimson */
        .btn-crimson {
            background: linear-gradient(135deg, #C62828 0%, #8e0000 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(198, 40, 40, 0.4);
        }

        .btn-crimson:hover {
            background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(198, 40, 40, 0.6);
        }

        .btn-crimson:active {
            transform: scale(0.98);
        }

        /* Alert Glassmorphism */
        .alert-danger {
            background: rgba(198, 40, 40, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            backdrop-filter: blur(10px);
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease-out forwards;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="card fade-in">
            <div class="card-header">
                <img src="assets/img/logo.png" alt="Logo">
                <h3 class="mb-0">Koperasi <span style="color: #ff5252;">MP</span></h3>
                <p class="text-muted mt-1 mb-0">Kelompok 3</p>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger p-2 text-center" style="font-size: 14px; border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <form action="auth.php" method="POST">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-crimson w-100 py-2 rounded-3 fw-bold mt-2">MASUK
                        SISTEM</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>