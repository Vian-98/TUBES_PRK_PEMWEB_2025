<?php
$pageTitle = 'Daftar Sparepart';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

// Get all parts with supplier info
$sql = "SELECT p.*, s.nama as supplier_nama 
        FROM parts p 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        ORDER BY p.nama";
$parts = fetchAll($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Daftar Sparepart</h2>
    <a href="part_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Tambah Sparepart
    </a>
</div>

<div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Nama</th>
                                        <th>Supplier</th>
                                        <th>Harga Beli</th>
                                        <th>Harga Jual</th>
                                        <th>Stok</th>
                                        <th>Min Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($parts)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data sparepart.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($parts as $part): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($part['sku']) ?></td>
                                            <td><?= htmlspecialchars($part['nama']) ?></td>
                                            <td><?= htmlspecialchars($part['supplier_nama'] ?? '-') ?></td>
                                            <td>Rp <?= number_format($part['harga_beli'], 0, ',', '.') ?></td>
                                            <td>Rp <?= number_format($part['harga_jual'], 0, ',', '.') ?></td>
                                            <td>
                                                <?= $part['stok'] ?>
                                                <?php if ($part['stok'] <= $part['min_stok']): ?>
                                                <span class="badge badge-low-stock ms-1">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $part['min_stok'] ?></td>
                                            <td>
                                                <a href="part_edit.php?id=<?= $part['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="part_delete.php?id=<?= $part['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin hapus sparepart ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
