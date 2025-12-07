<?php
$pageTitle = 'Daftar Sparepart';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

require_role(['Admin']);

// Get all parts with supplier info
$sql = "SELECT p.*, s.nama as supplier_nama 
        FROM parts p 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        ORDER BY p.nama";
$parts = fetchAll($sql);
?>

<!-- Action Buttons -->
<div class="flex justify-end items-center gap-3 mb-6">
        <a href="supplier_list.php" class="px-5 py-2.5 bg-white/60 backdrop-blur-md text-gray-700 text-sm font-medium rounded-full shadow-sm border border-white/50 hover:bg-white transition">
            <i class="fas fa-truck mr-2"></i>Supplier
        </a>
    <a href="part_add.php" class="px-6 py-2.5 bg-brand-dark text-white text-sm font-medium rounded-full shadow-lg hover:shadow-xl hover:bg-black transition transform hover:-translate-y-0.5">
        <i class="fas fa-plus mr-2"></i>Tambah Sparepart
    </a>
</div>

<!-- Table Card -->
<div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-gray-400 text-xs font-semibold uppercase tracking-wider border-b border-white/40">
                    <th class="px-6 py-4">SKU</th>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Supplier</th>
                    <th class="px-6 py-4">Harga Beli</th>
                    <th class="px-6 py-4">Harga Jual</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4">Min Stok</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-white/40">
                <?php if (empty($parts)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>Tidak ada data sparepart</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($parts as $part): ?>
                    <tr class="hover:bg-white/40 transition cursor-pointer">
                        <td class="px-6 py-4">
                            <span class="bg-white/60 border border-white/60 px-3 py-1 rounded-lg text-xs font-mono font-medium text-gray-700 shadow-sm">
                                <?= htmlspecialchars($part['sku']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($part['nama']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($part['supplier_nama'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-gray-600">Rp <?= number_format($part['harga_beli'], 0, ',', '.') ?></td>
                        <td class="px-6 py-4 font-medium text-green-600">Rp <?= number_format($part['harga_jual'], 0, ',', '.') ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold <?= $part['stok'] <= $part['min_stok'] ? 'text-red-600' : 'text-gray-900' ?>">
                                    <?= $part['stok'] ?>
                                </span>
                                <?php if ($part['stok'] <= $part['min_stok']): ?>
                                <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full border border-red-200">
                                    Low
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= $part['min_stok'] ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="part_edit.php?id=<?= $part['id'] ?>" 
                                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="part_delete.php?id=<?= $part['id'] ?>" 
                                   class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                   onclick="return confirm('Yakin hapus sparepart ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
