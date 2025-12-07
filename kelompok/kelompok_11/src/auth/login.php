<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cek_login.php';

redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = escape($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } else {
        $sql = "SELECT u.*, r.nama as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = '$email' AND u.aktif = 1";
        $result = query($sql);
        
        if (count($result) > 0) {
            $user = $result[0];
            
            if (password_verify($password, $user['password'])) {
                set_user_session($user);
                set_flash("Login berhasil! Selamat datang " . $user['nama'], "success");
                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan atau akun tidak aktif!";
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS UKM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #294B93 0%, #1a3366 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #294B93;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #656565;
            font-size: 14px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .alert-warning {
            background: #ffeaa7;
            color: #d63031;
            border-left: 4px solid #d63031;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #4C4C4C;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #294B93;
            box-shadow: 0 0 0 3px rgba(41, 75, 147, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #294B93;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #1f3a75;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 75, 147, 0.3);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #656565;
        }
        
        .register-link a {
            color: #294B93;
            text-decoration: none;
            font-weight: 600;
        }
        
        .demo-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #656565;
        }
        
        .demo-info strong {
            color: #294B93;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 POS UKM</h1>
            <p>Silakan login untuk melanjutkan</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Masukkan username" autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn-login">Login ke Dashboard</button>
        </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
        
        <div class="demo-info">
            <strong>🎯 Demo Account:</strong><br>
            Admin: admin / admin123<br>
            Kasir: kasir1 / admin123<br>
            Mekanik: mekanik1 / admin123
        </div>
    </div>
    
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>