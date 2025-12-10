<?php
// /reservasi/list.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

if (!empty($_SESSION['error'])) { $err = $_SESSION['error']; unset($_SESSION['error']); }
if (!empty($_SESSION['success'])) { $ok = $_SESSION['success']; unset($_SESSION['success']); }

// Ambil data reservasi
$q = "SELECT r.*, s.nama AS layanan_nama, u.nama AS mekanik_nama
      FROM reservations r
      LEFT JOIN services s ON r.layanan_id = s.id
      LEFT JOIN users u ON r.mekanik_id = u.id
      ORDER BY r.tanggal DESC";
$rows = fetchAll($q);

$pageTitle = 'Daftar Reservasi';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto px-8 pb-8">
    <!-- Alerts -->
    <?php if(!empty($err)): ?>
    <div class="mb-6 glass-panel rounded-2xl p-4 border-l-4 border-red-500 bg-red-50/50">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            <p class="text-red-700 font-medium"><?= htmlspecialchars($err) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($ok)): ?>
    <div class="mb-6 glass-panel rounded-2xl p-4 border-l-4 border-green-500 bg-green-50/50">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <p class="text-green-700 font-medium"><?= htmlspecialchars($ok) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="glass-panel rounded-2xl shadow-glass overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-white/50 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Daftar Reservasi</h3>
                <p class="text-sm text-gray-500 mt-1">Kelola semua reservasi bengkel</p>
            </div>
            <a href="form_reservasi.php" class="bg-brand-blue text-white px-4 py-2 rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/30 flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Reservasi Baru</span>
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Telepon</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Layanan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mekanik</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/40">
                    <?php foreach($rows as $i=>$r): ?>
                    <tr class="hover:bg-white/40 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $i+1 ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($r['kode'] ?? '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($r['nama_pelanggan'] ?? '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($r['telepon'] ?? '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($r['plat_kendaraan'] ?? '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($r['layanan_nama'] ?? '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($r['mekanik_nama'] ?? '') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['tanggal'] ?? 'now'))) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $status_class = match($r['status'] ?? '') {
                                'booked' => 'bg-blue-100 text-blue-800',
                                'checked_in' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            ?>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $status_class ?>">
                                <?= htmlspecialchars($r['status'] ?? '') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="edit_reservasi.php?id=<?= $r['id'] ?>" class="text-yellow-600 hover:text-yellow-900 transition">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_reservasi.php?id=<?= $r['id'] ?>" class="text-red-600 hover:text-red-900 transition" onclick="return confirm('Hapus reservasi ini?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <?php if (($r['status'] ?? '') === 'booked'): ?>
                            <form method="post" action="checkin_reservasi.php" class="inline">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="text-green-600 hover:text-green-900 transition" onclick="return confirm('Check-in dan buat draft transaksi?')">
                                    <i class="fas fa-check-circle"></i> Check-in
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($rows)===0): ?>
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">Belum ada reservasi</p>
                            <p class="text-sm">Reservasi pertama akan muncul di sini</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>