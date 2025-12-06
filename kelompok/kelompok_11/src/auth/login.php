<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Calculate base URL
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__ . '/..');
$baseUrl = str_replace($docRoot, '', $basePath);

$error = '';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $baseUrl . '/dashboard/dashboard.php');
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $conn = getConnection();
        $email = mysqli_real_escape_string($conn, $email);
        
        $sql = "SELECT u.*, r.nama as role 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.email = '$email' AND u.aktif = 1";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                mysqli_close($conn);
                header('Location: ' . $baseUrl . '/dashboard/dashboard.php');
                exit;
            } else {
                $error = 'Email atau password salah.';
            }
        } else {
            $error = 'Email atau password salah.';
        }
        
        mysqli_close($conn);
    } else {
        $error = 'Semua field harus diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS Bengkel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #294B93;
            --white: #FFFFFF;
        }
        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3870 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        .btn-primary:hover {
            background-color: #1e3870;
            border-color: #1e3870;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <h3 class="text-center mb-4" style="color: var(--primary-blue);">
                    <i class="bi bi-tools me-2"></i>POS UMKM Bengkel
                </h3>
                <h5 class="text-center text-muted mb-4">Login</h5>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <small class="text-muted">
                        Default: admin@posbengkel.com / password
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
