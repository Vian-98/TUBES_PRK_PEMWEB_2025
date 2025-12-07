<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

// Require login untuk semua halaman yang pakai header
require_login();

// Calculate base URL
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__ . '/..');
$baseUrl = str_replace($docRoot, '', $basePath);

$currentPage = basename($_SERVER['PHP_SELF']);

// Get user data
$user = get_user_data();

// Get notifications
$notifications = [];

// 1. Low stock alerts
$sql_low_stock = "SELECT nama, stok, min_stok FROM parts WHERE stok <= min_stok ORDER BY stok ASC LIMIT 5";
$low_stock = fetchAll($sql_low_stock);
foreach ($low_stock as $item) {
    $notifications[] = [
        'type' => 'warning',
        'icon' => 'exclamation-triangle',
        'title' => 'Stok Menipis',
        'message' => "{$item['nama']} tersisa {$item['stok']} unit (min: {$item['min_stok']})",
        'time' => 'Sekarang'
    ];
}

// 2. New reservations today
$sql_new_reservasi = "SELECT nama_pelanggan, plat_kendaraan FROM reservations WHERE DATE(tanggal) = CURDATE() AND status = 'booked' ORDER BY created_at DESC LIMIT 3";
$new_reservasi = fetchAll($sql_new_reservasi);
foreach ($new_reservasi as $item) {
    $notifications[] = [
        'type' => 'info',
        'icon' => 'calendar-check',
        'title' => 'Reservasi Baru',
        'message' => "{$item['nama_pelanggan']} ({$item['plat_kendaraan']})",
        'time' => 'Hari ini'
    ];
}

// 3. Draft transactions
$sql_draft = "SELECT kode, pelanggan_nama FROM transactions WHERE status = 'draft' ORDER BY created_at DESC LIMIT 3";
$draft_transactions = fetchAll($sql_draft);
foreach ($draft_transactions as $item) {
    $notifications[] = [
        'type' => 'primary',
        'icon' => 'file-invoice',
        'title' => 'Transaksi Draft',
        'message' => "{$item['kode']} - {$item['pelanggan_nama']}",
        'time' => 'Menunggu'
    ];
}

$notif_count = count($notifications);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'POS Bengkel' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#007AFF',
                            dark: '#1D1D1F',
                            medium: '#86868B',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f0f2f5;
            background-image: 
                radial-gradient(at 40% 20%, hsla(210, 100%, 93%, 1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(189, 100%, 96%, 1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(341, 100%, 96%, 1) 0px, transparent 50%),
                radial-gradient(at 80% 50%, hsla(260, 100%, 96%, 1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(222, 100%, 96%, 1) 0px, transparent 50%),
                radial-gradient(at 80% 100%, hsla(200, 100%, 96%, 1) 0px, transparent 50%),
                radial-gradient(at 0% 0%, hsla(343, 100%, 96%, 1) 0px, transparent 50%);
            background-attachment: fixed;
            background-size: cover;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .glass-sidebar {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.6);
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }
    </style>
</head>
<body class="text-brand-dark font-sans antialiased h-screen flex overflow-hidden selection:bg-brand-blue selection:text-white">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="glass-sidebar w-72 flex-shrink-0 flex flex-col transition-all duration-300 absolute inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 shadow-glass">
        
        <!-- Logo -->
        <div class="h-20 flex items-center px-8">
            <div class="w-10 h-10 bg-brand-blue rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 mr-3">
                <i class="fas fa-wrench text-lg"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-tight text-gray-900 leading-none">Bengkel<span class="text-brand-blue">App</span></h1>
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-widest mt-1">Management</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
            
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-2">Menu Utama</p>

            <a href="<?= $baseUrl ?>/dashboard/index.php" class="flex items-center px-4 py-3.5 <?= strpos($currentPage, 'dashboard') !== false ? 'bg-brand-dark text-white' : 'text-gray-600 hover:bg-white/50 hover:text-brand-blue' ?> rounded-2xl transition-all group">
                <i class="fas fa-home w-6 text-center"></i>
                <span class="font-medium ml-3">Dashboard</span>
            </a>

            <?php if (check_role(['Kasir', 'Admin'])): ?>
            <a href="<?= $baseUrl ?>/pos/pos.php" class="flex items-center px-4 py-3.5 <?= strpos($currentPage, 'pos') !== false ? 'bg-brand-dark text-white' : 'text-gray-600 hover:bg-white/50 hover:text-brand-blue' ?> rounded-2xl transition-all group">
                <i class="fas fa-cash-register w-6 text-center"></i>
                <span class="font-medium ml-3">Kasir (POS)</span>
            </a>
            <?php endif; ?>

            <a href="<?= $baseUrl ?>/reservasi/list.php" class="flex items-center px-4 py-3.5 <?= strpos($currentPage, 'reservasi') !== false ? 'bg-brand-dark text-white' : 'text-gray-600 hover:bg-white/50 hover:text-brand-blue' ?> rounded-2xl transition-all group">
                <i class="fas fa-calendar-check w-6 text-center"></i>
                <span class="font-medium ml-3">Reservasi</span>
            </a>

            <?php if (check_role(['Admin'])): ?>
            <a href="<?= $baseUrl ?>/inventory/part_list.php" class="flex items-center px-4 py-3.5 <?= strpos($currentPage, 'part') !== false || strpos($currentPage, 'supplier') !== false ? 'bg-brand-dark text-white' : 'text-gray-600 hover:bg-white/50 hover:text-brand-blue' ?> rounded-2xl transition-all group">
                <i class="fas fa-boxes w-6 text-center"></i>
                <span class="font-medium ml-3">Inventory</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- User Profile -->
        <div class="p-4 mx-4 mb-4 glass-panel rounded-2xl flex items-center gap-3 cursor-pointer hover:bg-white/80 transition shadow-sm">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&background=007AFF&color=fff" alt="User" class="w-10 h-10 rounded-full shadow-sm">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($user['nama']) ?></p>
                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($user['role']) ?></p>
            </div>
            <a href="<?= $baseUrl ?>/auth/logout.php" class="text-gray-400 hover:text-red-500 transition">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>

    <!-- Overlay Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Header -->
        <header class="h-20 px-8 flex items-center justify-between z-10">
            <div class="flex items-center">
                <button id="mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:bg-white/50 rounded-xl mr-4 transition">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h2>
                    <p class="text-sm text-gray-500 hidden sm:block" id="current-date"></p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Context-Aware Search (Hidden on Dashboard) -->
                <?php if (strpos($currentPage, 'dashboard') === false && strpos($currentPage, 'pos') === false && $currentPage !== 'index.php'): ?>
                <div class="hidden md:flex items-center bg-white/60 backdrop-blur-md rounded-full px-4 py-2 border border-white/50 shadow-sm focus-within:ring-2 focus-within:ring-brand-blue/50 transition w-64">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" id="global-search" placeholder="<?php 
                        if (strpos($currentPage, 'part_') !== false || strpos($currentPage, 'supplier_') !== false) {
                            echo 'Cari sparepart/supplier...';
                        } elseif (strpos($currentPage, 'reservasi') !== false) {
                            echo 'Cari reservasi...';
                        } elseif (strpos($currentPage, 'pos') !== false || strpos($currentPage, 'transaksi') !== false) {
                            echo 'Cari transaksi...';
                        } else {
                            echo 'Cari...';
                        }
                    ?>" class="bg-transparent border-none outline-none text-sm ml-2 w-full placeholder-gray-500 text-gray-700">
                </div>
                <?php endif; ?>

                <!-- Notification Dropdown -->
                <div class="relative">
                    <button id="notif-btn" class="w-10 h-10 bg-white/60 backdrop-blur-md rounded-full flex items-center justify-center text-gray-600 hover:text-brand-blue shadow-sm border border-white/50 transition hover:scale-105 active:scale-95 relative">
                        <i class="far fa-bell text-lg"></i>
                        <?php if ($notif_count > 0): ?>
                        <span class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                        <?php endif; ?>
                    </button>
                    
                    <!-- Dropdown -->
                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 glass-panel rounded-2xl shadow-glass overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-white/50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900">Notifikasi</h3>
                            <?php if ($notif_count > 0): ?>
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $notif_count ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div class="divide-y divide-white/40">
                                <?php if (empty($notifications)): ?>
                                <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                    <i class="far fa-bell-slash text-3xl mb-2 text-gray-300"></i>
                                    <p>Tidak ada notifikasi</p>
                                </div>
                                <?php else: ?>
                                    <?php 
                                    $color_map = [
                                        'warning' => 'text-yellow-600 bg-yellow-100',
                                        'info' => 'text-blue-600 bg-blue-100',
                                        'primary' => 'text-purple-600 bg-purple-100'
                                    ];
                                    foreach ($notifications as $notif): 
                                    ?>
                                    <div class="px-4 py-3 hover:bg-white/40 transition cursor-pointer">
                                        <div class="flex gap-3">
                                            <div class="w-10 h-10 rounded-full <?= $color_map[$notif['type']] ?> flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-<?= $notif['icon'] ?>"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 text-sm"><?= htmlspecialchars($notif['title']) ?></p>
                                                <p class="text-xs text-gray-600 mt-0.5 truncate"><?= htmlspecialchars($notif['message']) ?></p>
                                                <p class="text-xs text-gray-400 mt-1"><?= $notif['time'] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto px-8 pb-8">

<script>
// Context-Aware Search
document.getElementById('global-search')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    const currentPage = '<?= $currentPage ?>';
    
    // Inventory pages (part & supplier)
    if (currentPage.includes('part_') || currentPage.includes('supplier_')) {
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
    
    // Dashboard - search cards/stats
    else if (currentPage.includes('dashboard')) {
        const cards = document.querySelectorAll('.glass-panel');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.opacity = text.includes(searchTerm) || searchTerm === '' ? '1' : '0.3';
        });
    }
    
    // Other pages - generic search
    else {
        const rows = document.querySelectorAll('tbody tr, .search-item');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
});
</script>
