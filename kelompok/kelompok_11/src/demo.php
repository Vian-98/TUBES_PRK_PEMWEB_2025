<?php
$pageTitle = 'Demo UI - iOS Style';
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/config/database.php';

// Demo ini untuk semua role - tidak ada pembatasan akses

// Ambil data statistik
$sql_total_reservasi = "SELECT COUNT(*) as total FROM reservations WHERE DATE(tanggal) = CURDATE()";
$total_reservasi = fetchOne($sql_total_reservasi)['total'] ?? 0;

$sql_total_transaksi = "SELECT COUNT(*) as total, SUM(grand_total) as omzet FROM transactions WHERE DATE(created_at) = CURDATE() AND status = 'paid'";
$stats_transaksi = fetchOne($sql_total_transaksi);
$total_transaksi = $stats_transaksi['total'] ?? 0;
$omzet = $stats_transaksi['omzet'] ?? 0;

$sql_stok_menipis = "SELECT COUNT(*) as total FROM parts WHERE stok <= min_stok";
$stok_menipis = fetchOne($sql_stok_menipis)['total'] ?? 0;

$sql_mekanik_aktif = "SELECT COUNT(*) as total FROM users u JOIN roles r ON u.role_id = r.id WHERE r.nama = 'Mekanik' AND u.aktif = 1";
$mekanik_aktif = fetchOne($sql_mekanik_aktif)['total'] ?? 0;

// Ambil data reservasi hari ini
$sql_reservasi = "SELECT r.*, s.nama as layanan_nama, u.nama as mekanik_nama 
                  FROM reservations r 
                  LEFT JOIN services s ON r.layanan_id = s.id 
                  LEFT JOIN users u ON r.mekanik_id = u.id 
                  WHERE DATE(r.tanggal) = CURDATE() 
                  ORDER BY r.tanggal ASC 
                  LIMIT 5";
$reservasi_list = fetchAll($sql_reservasi);
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Card 1 -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-brand-blue">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <span class="bg-green-100 text-green-600 py-1 px-3 rounded-full text-xs font-bold">Live</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900"><?= $total_reservasi ?></h3>
        <p class="text-sm font-medium text-gray-500 mt-1">Reservasi Hari Ini</p>
    </div>

    <!-- Card 2 -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-green-500/10 flex items-center justify-center text-green-600">
                <i class="fas fa-wallet text-xl"></i>
            </div>
            <span class="bg-green-100 text-green-600 py-1 px-3 rounded-full text-xs font-bold"><?= $total_transaksi ?></span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900">Rp <?= number_format($omzet, 0, ',', '.') ?></h3>
        <p class="text-sm font-medium text-gray-500 mt-1">Pendapatan POS</p>
    </div>

    <!-- Card 3 -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <span class="bg-red-100 text-red-600 py-1 px-3 rounded-full text-xs font-bold">Alert</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900"><?= $stok_menipis ?> Item</h3>
        <p class="text-sm font-medium text-gray-500 mt-1">Stok Menipis</p>
    </div>

    <!-- Card 4 -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-600">
                <i class="fas fa-users text-xl"></i>
            </div>
            <span class="bg-gray-100 text-gray-500 py-1 px-3 rounded-full text-xs font-bold"><?= $mekanik_aktif ?></span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900">Mekanik</h3>
        <p class="text-sm font-medium text-gray-500 mt-1">Terdaftar Aktif</p>
    </div>
</div>

<!-- Big Table Section -->
<div class="glass-panel rounded-3xl shadow-glass overflow-hidden mb-8">
    <div class="px-8 py-6 border-b border-white/50 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Antrian Reservasi</h3>
            <p class="text-sm text-gray-500">Jadwal kendaraan masuk hari ini</p>
        </div>
        <button class="px-6 py-2.5 bg-brand-dark text-white text-sm font-medium rounded-full shadow-lg hover:shadow-xl hover:bg-black transition transform hover:-translate-y-0.5">
            <i class="fas fa-plus mr-2"></i>Tambah Reservasi
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-gray-400 text-xs font-semibold uppercase tracking-wider border-b border-white/40">
                    <th class="px-8 py-5">Waktu</th>
                    <th class="px-8 py-5">Pelanggan</th>
                    <th class="px-8 py-5">Kendaraan</th>
                    <th class="px-8 py-5">Layanan</th>
                    <th class="px-8 py-5">Status</th>
                    <th class="px-8 py-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-white/40">
                <?php if (empty($reservasi_list)): ?>
                <tr>
                    <td colspan="6" class="px-8 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada reservasi hari ini</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($reservasi_list as $item): 
                        $waktu = date('H:i', strtotime($item['tanggal']));
                        
                        // Status badge
                        $status_class = [
                            'booked' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'completed' => 'bg-green-100 text-green-800 border-green-200',
                            'canceled' => 'bg-red-100 text-red-800 border-red-200'
                        ];
                        $status_text = [
                            'booked' => 'Menunggu',
                            'in_progress' => 'Dikerjakan',
                            'completed' => 'Selesai',
                            'canceled' => 'Dibatalkan'
                        ];
                        $status_color = [
                            'booked' => 'bg-yellow-500',
                            'in_progress' => 'bg-blue-500 animate-pulse',
                            'completed' => 'bg-green-500',
                            'canceled' => 'bg-red-500'
                        ];
                    ?>
                <tr class="hover:bg-white/40 transition group cursor-pointer">
                    <td class="px-8 py-5 font-bold text-brand-blue"><?= $waktu ?></td>
                    <td class="px-8 py-5">
                        <div class="font-medium text-gray-900"><?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($item['telepon'] ?? '-') ?></div>
                    </td>
                    <td class="px-8 py-5">
                        <span class="bg-white/60 border border-white/60 px-3 py-1 rounded-lg text-xs font-mono font-medium text-gray-700 shadow-sm"><?= htmlspecialchars($item['plat_kendaraan'] ?? 'N/A') ?></span>
                    </td>
                    <td class="px-8 py-5 text-gray-600"><?= htmlspecialchars($item['layanan_nama'] ?? 'Belum ditentukan') ?></td>
                    <td class="px-8 py-5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $status_class[$item['status']] ?> border">
                            <span class="w-1.5 h-1.5 <?= $status_color[$item['status']] ?> rounded-full mr-1.5"></span>
                            <?= $status_text[$item['status']] ?>
                        </span>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <button class="text-gray-400 hover:text-brand-blue p-2 rounded-full hover:bg-white/50 transition">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-12 text-center text-xs text-gray-500">
    &copy; 2024 Sistem Informasi Bengkel - Demo UI iOS Style
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
