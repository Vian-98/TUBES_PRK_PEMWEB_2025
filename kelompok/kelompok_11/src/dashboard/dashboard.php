<?php
// Redirect to new dashboard router
header('Location: index.php');
exit;
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
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
</div>

<!-- Reservasi Table Section -->
<div class="glass-panel rounded-3xl shadow-glass overflow-hidden mb-8">
    <div class="px-8 py-6 border-b border-white/50 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Antrian Reservasi</h3>
            <p class="text-sm text-gray-500">Jadwal kendaraan masuk hari ini</p>
        </div>
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
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-white/40">
                <?php if (empty($reservasi_list)): ?>
                <tr>
                    <td colspan="5" class="px-8 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada reservasi hari ini</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($reservasi_list as $item): 
                        $waktu = date('H:i', strtotime($item['tanggal']));
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
                    ?>
                <tr class="hover:bg-white/40 transition">
                    <td class="px-8 py-5 font-bold text-brand-blue"><?= $waktu ?></td>
                    <td class="px-8 py-5">
                        <div class="font-medium text-gray-900"><?= htmlspecialchars($item['nama_pelanggan']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($item['telepon'] ?? '-') ?></div>
                    </td>
                    <td class="px-8 py-5">
                        <span class="bg-white/60 border border-white/60 px-3 py-1 rounded-lg text-xs font-mono font-medium text-gray-700 shadow-sm">
                            <?= htmlspecialchars($item['plat_kendaraan'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td class="px-8 py-5 text-gray-600"><?= htmlspecialchars($item['layanan_nama'] ?? 'Belum ditentukan') ?></td>
                    <td class="px-8 py-5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $status_class[$item['status']] ?> border">
                            <?= $status_text[$item['status']] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>