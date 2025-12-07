<?php
session_start();

$base_path = $_SERVER['DOCUMENT_ROOT'] . '/pos_ukm';
require_once $base_path . '/config/database.php';
require_once $base_path . '/auth/cek_login.php';

require_login();

$user = get_user_data();
$flash = get_flash();

$total_users = query("SELECT COUNT(*) as total FROM users")[0]['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POS UKM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #294B93 0%, #1f3a75 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
        }
        
        .navbar-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .navbar-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .user-role {
            font-size: 12px;
            background: #FFFFFF;
            color: #294B93;
            padding: 3px 10px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-logout:hover {
            background: #c82333;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #294B93 0%, #1f3a75 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-card h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-title {
            color: #656565;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            color: #294B93;
            font-size: 32px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">🏪 POS UKM</div>
        <div class="navbar-menu">
            <a href="/pos_ukm/dashboard.php">Dashboard</a>
            
            <div class="user-info">
                <span><?= $user['full_name'] ?></span>
                <span class="user-role"><?= strtoupper($user['role']) ?></span>
            </div>
            
            <a href="/pos_ukm/auth/logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>" id="flashAlert">
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>
        
        <div class="welcome-card">
            <h1>Selamat Datang, <?= $user['full_name'] ?>! 👋</h1>
            <p>Anda login sebagai <strong><?= strtoupper($user['role']) ?></strong></p>
            <p style="margin-top: 10px; font-size: 14px;">
                <?php
                date_default_timezone_set('Asia/Jakarta');
                echo date('l, d F Y - H:i') . ' WIB';
                ?>
            </p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-title">Total Users</div>
                <div class="stat-value"><?= $total_users ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-title">Total Transaksi</div>
                <div class="stat-value">0</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔧</div>
                <div class="stat-title">Total Parts</div>
                <div class="stat-value">0</div>
            </div>
        </div>
    </div>
    
    <script>
        const flashAlert = document.getElementById('flashAlert');
        if (flashAlert) {
            setTimeout(function() {
                flashAlert.style.transition = 'opacity 0.5s';
                flashAlert.style.opacity = '0';
                setTimeout(() => flashAlert.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>