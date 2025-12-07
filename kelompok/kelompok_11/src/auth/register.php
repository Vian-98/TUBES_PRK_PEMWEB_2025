<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cek_login.php';

redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = escape($_POST['nama'] ?? '');
    $email = escape($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role_id = escape($_POST['role_id'] ?? '2'); // Default kasir
    $telepon = escape($_POST['telepon'] ?? '');
    
    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Nama, email, dan password harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        $check_email = query("SELECT id FROM users WHERE email = '$email'");
        
        if (count($check_email) > 0) {
            $error = "Email sudah digunakan!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO users (nama, email, password, role_id, telepon, aktif, created_at, updated_at) 
                    VALUES ('$nama', '$email', '$hashed_password', '$role_id', '$telepon', 1, '$now', '$now')";
            
            $result = execute($sql);
            
            if ($result['success']) {
                $new_user = query("SELECT u.*, r.nama as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = '$email'")[0];
                set_user_session($new_user);
                set_flash("Registrasi berhasil! Selamat datang " . $new_user['nama'], "success");
                
                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $error = "Gagal mendaftar: " . $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - POS UKM</title>
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
        
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #294B93;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .register-header p {
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
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #294B93;
            box-shadow: 0 0 0 3px rgba(41, 75, 147, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .btn-register {
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
        
        .btn-register:hover {
            background: #1f3a75;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 75, 147, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #656565;
        }
        
        .login-link a {
            color: #294B93;
            text-decoration: none;
            font-weight: 600;
        }
        
        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        
        .weak { background: #f44336; width: 33%; }
        .medium { background: #ff9800; width: 66%; }
        .strong { background: #4caf50; width: 100%; }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>📝 Daftar Akun Baru</h1>
            <p>Lengkapi data dan langsung masuk Dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="Masukkan nama lengkap" 
                       value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Username" 
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="email@example.com" 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="role">Role/Jabatan *</label>
                <select id="role" name="role" required>
                    <option value="kasir">Kasir</option>
                    <option value="mekanik">Mekanik</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Minimal 6 karakter">
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Ulangi password">
            </div>
            
            <button type="submit" class="btn-register">Daftar & Masuk Dashboard</button>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
    
    <script>
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if (strength < 3) strengthBar.classList.add('weak');
            else if (strength < 5) strengthBar.classList.add('medium');
            else strengthBar.classList.add('strong');
        });
    </script>
</body>
</html>