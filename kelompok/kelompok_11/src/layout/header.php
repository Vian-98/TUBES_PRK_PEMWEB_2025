<?php
require_once __DIR__ . '/../config/session.php';
requireLogin();

$user = getUser();

// Calculate base URL dynamically
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__ . '/..');
$baseUrl = str_replace($docRoot, '', $basePath);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'POS Bengkel' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/css/custom.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-dark bg-primary-blue">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-tools me-2"></i>POS UMKM Bengkel
            </span>
            <div class="d-flex align-items-center">
                <span class="text-white me-4 small">
                    <i class="bi bi-clock me-1"></i><span id="currentTime"></span>
                </span>
                <div class="dropdown">
                    <button class="btn btn-link text-white text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['nama']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($user['role']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $baseUrl ?>/auth/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-primary-blue">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
        
        <!-- Main Content -->
        <div class="main-content flex-grow-1">
    
    <script>
    function updateTime() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const date = now.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });
        const el = document.getElementById('currentTime');
        if(el) el.textContent = date + ' ' + time;
    }
    updateTime();
    setInterval(updateTime, 1000);
    </script>
