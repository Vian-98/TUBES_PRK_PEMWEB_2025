<?php
$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

// === STATISTIK UTAMA ===
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

// === STATISTIK TAMBAHAN ===
// Total pelanggan (dari reservasi unik)
$sql_total_pelanggan = "SELECT COUNT(DISTINCT nama_pelanggan) as total FROM reservations";
$total_pelanggan = fetchOne($sql_total_pelanggan)['total'] ?? 0;

// Total layanan tersedia
$sql_total_layanan = "SELECT COUNT(*) as total FROM services";
$total_layanan = fetchOne($sql_total_layanan)['total'] ?? 0;

// Transaksi pending/draft
$sql_draft_transaksi = "SELECT COUNT(*) as total FROM transactions WHERE status = 'draft'";
$draft_transaksi = fetchOne($sql_draft_transaksi)['total'] ?? 0;

// Nilai total stok parts
$sql_nilai_stok = "SELECT SUM(stok * harga_jual) as total FROM parts";
$nilai_stok = fetchOne($sql_nilai_stok)['total'] ?? 0;

// === DATA RESERVASI HARI INI ===
$sql_reservasi = "SELECT r.*, s.nama as layanan_nama, u.nama as mekanik_nama 
                  FROM reservations r 
                  LEFT JOIN services s ON r.layanan_id = s.id 
                  LEFT JOIN users u ON r.mekanik_id = u.id 
                  WHERE DATE(r.tanggal) = CURDATE() 
                  ORDER BY r.tanggal ASC 
                  LIMIT 5";
$reservasi_list = fetchAll($sql_reservasi);

// === DATA STOK MENIPIS ===
$sql_low_stock = "SELECT p.nama, p.stok, p.min_stok, s.nama as supplier_nama 
                  FROM parts p 
                  LEFT JOIN suppliers s ON p.supplier_id = s.id 
                  WHERE p.stok <= p.min_stok 
                  ORDER BY p.stok ASC 
                  LIMIT 5";
$low_stock = fetchAll($sql_low_stock);

// === LAYANAN TERPOPULER ===
$sql_top_services = "SELECT s.nama, COUNT(r.id) as total_booking 
                     FROM services s 
                     LEFT JOIN reservations r ON s.id = r.layanan_id 
                     WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                     GROUP BY s.id 
                     ORDER BY total_booking DESC 
                     LIMIT 5";
$top_services = fetchAll($sql_top_services);

// === SPAREPART TERLARIS ===
$sql_top_parts = "SELECT p.nama, SUM(ti.qty) as total_terjual 
                  FROM parts p 
                  LEFT JOIN transaction_items ti ON p.id = ti.part_id 
                  LEFT JOIN transactions t ON ti.transaction_id = t.id
                  WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND t.status = 'paid'
                  GROUP BY p.id 
                  ORDER BY total_terjual DESC 
                  LIMIT 5";
$top_parts = fetchAll($sql_top_parts);

// === PERFORMA MEKANIK ===
$sql_mekanik_performa = "SELECT u.nama, COUNT(r.id) as total_pekerjaan,
                         SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) as selesai
                         FROM users u
                         JOIN roles ro ON u.role_id = ro.id
                         LEFT JOIN reservations r ON u.id = r.mekanik_id AND DATE(r.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                         WHERE ro.nama = 'Mekanik' AND u.aktif = 1
                         GROUP BY u.id
                         ORDER BY total_pekerjaan DESC
                         LIMIT 5";
$mekanik_performa = fetchAll($sql_mekanik_performa);

// === AKTIVITAS TERKINI ===
$sql_recent_activity = "SELECT 'reservation' as tipe, kode, nama_pelanggan as detail, created_at 
                        FROM reservations 
                        WHERE DATE(created_at) = CURDATE()
                        UNION ALL
                        SELECT 'transaction' as tipe, kode, pelanggan_nama as detail, created_at 
                        FROM transactions 
                        WHERE DATE(created_at) = CURDATE()
                        ORDER BY created_at DESC 
                        LIMIT 10";
$recent_activity = fetchAll($sql_recent_activity);
?>

<!-- Main Stats Grid - 4 Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Card 1 - Reservasi -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center text-brand-blue">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <span class="bg-brand-blue/10 text-brand-blue py-1 px-3 rounded-full text-xs font-bold">Live</span>
        </div>
        <h3 class="text-3xl font-bold text-brand-dark"><?= $total_reservasi ?></h3>
        <p class="text-sm font-medium text-brand-gray mt-1">Reservasi Hari Ini</p>
    </div>

    <!-- Card 2 - Omzet -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center text-green-600">
                <i class="fas fa-wallet text-xl"></i>
            </div>
            <span class="bg-green-100 text-green-600 py-1 px-3 rounded-full text-xs font-bold"><?= $total_transaksi ?></span>
        </div>
        <h3 class="text-3xl font-bold text-brand-dark">Rp <?= number_format($omzet, 0, ',', '.') ?></h3>
        <p class="text-sm font-medium text-brand-gray mt-1">Pendapatan POS</p>
    </div>

    <!-- Card 3 - Stok Menipis -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center text-red-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <span class="bg-red-100 text-red-600 py-1 px-3 rounded-full text-xs font-bold">Alert</span>
        </div>
        <h3 class="text-3xl font-bold text-brand-dark"><?= $stok_menipis ?> Item</h3>
        <p class="text-sm font-medium text-brand-gray mt-1">Stok Menipis</p>
    </div>

    <!-- Card 4 - Mekanik Aktif -->
    <div class="glass-panel p-6 rounded-3xl shadow-glass hover:-translate-y-1 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-brand-light-gray">
                <i class="fas fa-users text-xl"></i>
            </div>
            <span class="bg-gray-100 text-brand-light-gray py-1 px-3 rounded-full text-xs font-bold"><?= $mekanik_aktif ?></span>
        </div>
        <h3 class="text-3xl font-bold text-brand-dark"><?= $mekanik_aktif ?> Orang</h3>
        <p class="text-sm font-medium text-brand-gray mt-1">Mekanik Aktif</p>
    </div>
</div>

<!-- Secondary Stats Grid - 4 Small Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Mini Card 1 - Total Pelanggan -->
    <div class="glass-panel p-4 rounded-2xl shadow-glass">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-brand-gray mb-1">Total Pelanggan</p>
                <h4 class="text-2xl font-bold text-brand-dark"><?= $total_pelanggan ?></h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                <i class="fas fa-user-friends text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Mini Card 2 - Total Layanan -->
    <div class="glass-panel p-4 rounded-2xl shadow-glass">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-brand-gray mb-1">Layanan Tersedia</p>
                <h4 class="text-2xl font-bold text-brand-dark"><?= $total_layanan ?></h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                <i class="fas fa-list-check text-indigo-600"></i>
            </div>
        </div>
    </div>

    <!-- Mini Card 3 - Draft Transaksi -->
    <div class="glass-panel p-4 rounded-2xl shadow-glass">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-brand-gray mb-1">Transaksi Pending</p>
                <h4 class="text-2xl font-bold text-brand-dark"><?= $draft_transaksi ?></h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-hourglass-half text-yellow-600"></i>
            </div>
        </div>
    </div>

    <!-- Mini Card 4 - Nilai Stok -->
    <div class="glass-panel p-4 rounded-2xl shadow-glass">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-brand-gray mb-1">Nilai Stok Total</p>
                <h4 class="text-xl font-bold text-brand-dark">Rp <?= number_format($nilai_stok, 0, ',', '.') ?></h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                <i class="fas fa-box-open text-teal-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Three Column Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Reservasi Table -->
    <div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
        <div class="px-6 py-4 border-b border-white/50">
            <h3 class="text-lg font-bold text-brand-dark">Reservasi Hari Ini</h3>
            <p class="text-sm text-brand-gray">Jadwal kendaraan masuk</p>
        </div>
        <div class="p-6">
            <?php if (empty($reservasi_list)): ?>
            <div class="text-center py-8 text-brand-gray">
                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                <p>Belum ada reservasi</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($reservasi_list as $item): 
                    $waktu = date('H:i', strtotime($item['tanggal']));
                    $status_class = [
                        'booked' => 'bg-yellow-100 text-yellow-800',
                        'in_progress' => 'bg-blue-100 text-blue-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'canceled' => 'bg-red-100 text-red-800'
                    ][$item['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <div class="flex items-center justify-between p-3 bg-white/40 rounded-xl hover:bg-white/60 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-brand-blue/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-car text-brand-blue"></i>
                        </div>
                        <div>
                            <p class="font-medium text-brand-dark text-sm"><?= htmlspecialchars($item['nama_pelanggan']) ?></p>
                            <p class="text-xs text-brand-gray"><?= htmlspecialchars($item['plat_kendaraan'] ?? 'N/A') ?> • <?= $waktu ?></p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-lg text-xs font-medium <?= $status_class ?>">
                        <?= ucfirst($item['status']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stok Menipis -->
    <div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
        <div class="px-6 py-4 border-b border-white/50">
            <h3 class="text-lg font-bold text-brand-dark">Alert Stok Menipis</h3>
            <p class="text-sm text-brand-gray">Perlu restock segera</p>
        </div>
        <div class="p-6">
            <?php if (empty($low_stock)): ?>
            <div class="text-center py-8 text-brand-gray">
                <i class="fas fa-check-circle text-4xl mb-3 text-green-300"></i>
                <p>Semua stok aman</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($low_stock as $item): ?>
                <div class="flex items-center justify-between p-3 bg-white/40 rounded-xl hover:bg-white/60 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-brand-dark text-sm"><?= htmlspecialchars($item['nama']) ?></p>
                            <p class="text-xs text-brand-gray">Min: <?= $item['min_stok'] ?> • Supplier: <?= htmlspecialchars($item['supplier_nama'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-sm font-bold">
                        <?= $item['stok'] ?> unit
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Column 3 - Aktivitas Terkini -->
    <div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
        <div class="px-6 py-4 border-b border-white/50">
            <h3 class="text-lg font-bold text-brand-dark">Aktivitas Terkini</h3>
            <p class="text-sm text-brand-gray">Log aktivitas hari ini</p>
        </div>
        <div class="p-6">
            <?php if (empty($recent_activity)): ?>
            <div class="text-center py-8 text-brand-gray">
                <i class="fas fa-history text-4xl mb-3 text-gray-300"></i>
                <p>Belum ada aktivitas</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recent_activity as $activity): 
                    $icon = $activity['tipe'] == 'reservation' ? 'fa-calendar-check' : 'fa-receipt';
                    $color = $activity['tipe'] == 'reservation' ? 'blue' : 'green';
                    $waktu = date('H:i', strtotime($activity['created_at']));
                ?>
                <div class="flex items-start gap-3 p-3 bg-white/40 rounded-xl hover:bg-white/60 transition">
                    <div class="w-8 h-8 bg-<?= $color ?>-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas <?= $icon ?> text-<?= $color ?>-600 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-brand-dark text-sm truncate"><?= htmlspecialchars($activity['kode']) ?></p>
                        <p class="text-xs text-brand-gray truncate"><?= htmlspecialchars($activity['detail']) ?></p>
                    </div>
                    <span class="text-xs text-brand-gray whitespace-nowrap"><?= $waktu ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Two Column Grid - Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    <!-- Layanan Terpopuler -->
    <div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
        <div class="px-6 py-4 border-b border-white/50">
            <h3 class="text-lg font-bold text-brand-dark">Layanan Terpopuler</h3>
            <p class="text-sm text-brand-gray">30 hari terakhir</p>
        </div>
        <div class="p-6">
            <?php if (empty($top_services)): ?>
            <div class="text-center py-8 text-brand-gray">
                <i class="fas fa-chart-bar text-4xl mb-3 text-gray-300"></i>
                <p>Belum ada data</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($top_services as $service): ?>
                <div class="flex items-center justify-between p-3 bg-white/40 rounded-xl">
                    <div class="flex-1">
                        <p class="font-medium text-brand-dark text-sm"><?= htmlspecialchars($service['nama']) ?></p>
                        <div class="mt-2 bg-white/60 rounded-full h-2 overflow-hidden">
                            <div class="bg-brand-blue h-full rounded-full" style="width: <?= min(100, ($service['total_booking'] / max($top_services[0]['total_booking'], 1)) * 100) ?>%"></div>
                        </div>
                    </div>
                    <span class="ml-4 px-3 py-1 bg-brand-blue/10 text-brand-blue rounded-lg text-sm font-bold">
                        <?= $service['total_booking'] ?>x
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sparepart Terlaris -->
    <div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
        <div class="px-6 py-4 border-b border-white/50">
            <h3 class="text-lg font-bold text-brand-dark">Sparepart Terlaris</h3>
            <p class="text-sm text-brand-gray">30 hari terakhir</p>
        </div>
        <div class="p-6">
            <?php if (empty($top_parts)): ?>
            <div class="text-center py-8 text-brand-gray">
                <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                <p>Belum ada data</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($top_parts as $part): ?>
                <div class="flex items-center justify-between p-3 bg-white/40 rounded-xl">
                    <div class="flex-1">
                        <p class="font-medium text-brand-dark text-sm"><?= htmlspecialchars($part['nama']) ?></p>
                        <div class="mt-2 bg-white/60 rounded-full h-2 overflow-hidden">
                            <div class="bg-green-500 h-full rounded-full" style="width: <?= min(100, ($part['total_terjual'] / max($top_parts[0]['total_terjual'], 1)) * 100) ?>%"></div>
                        </div>
                    </div>
                    <span class="ml-4 px-3 py-1 bg-green-100 text-green-600 rounded-lg text-sm font-bold">
                        <?= $part['total_terjual'] ?> unit
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Performa Mekanik -->
<div class="glass-panel rounded-3xl shadow-glass overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-white/50">
        <h3 class="text-lg font-bold text-brand-dark">Performa Mekanik</h3>
        <p class="text-sm text-brand-gray">7 hari terakhir</p>
    </div>
    <div class="p-6">
        <?php if (empty($mekanik_performa)): ?>
        <div class="text-center py-8 text-brand-gray">
            <i class="fas fa-user-cog text-4xl mb-3 text-gray-300"></i>
            <p>Belum ada data</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($mekanik_performa as $mekanik): 
                $completion_rate = $mekanik['total_pekerjaan'] > 0 ? round(($mekanik['selesai'] / $mekanik['total_pekerjaan']) * 100) : 0;
            ?>
            <div class="p-4 bg-white/40 rounded-xl hover:bg-white/60 transition">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-brand-blue/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-cog text-brand-blue text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-brand-dark text-sm truncate"><?= htmlspecialchars($mekanik['nama']) ?></p>
                        <p class="text-xs text-brand-gray"><?= $mekanik['total_pekerjaan'] ?> pekerjaan</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-brand-gray">Completion Rate</span>
                        <span class="font-bold text-brand-dark"><?= $completion_rate ?>%</span>
                    </div>
                    <div class="bg-white/60 rounded-full h-2 overflow-hidden">
                        <div class="bg-green-500 h-full rounded-full transition-all" style="width: <?= $completion_rate ?>%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-brand-gray">
                        <span><?= $mekanik['selesai'] ?> selesai</span>
                        <span><?= $mekanik['total_pekerjaan'] - $mekanik['selesai'] ?> pending</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
