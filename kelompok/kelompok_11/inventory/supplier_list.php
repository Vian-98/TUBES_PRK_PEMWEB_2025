<?php
$pageTitle = 'Daftar Supplier';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY nama");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <?php include __DIR__ . '/../layout/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Daftar Supplier</h2>
                    <div>
                        <a href="part_list.php" class="btn btn-secondary me-2">
                            <i class="bi bi-box-seam me-2"></i>Sparepart
                        </a>
                        <a href="supplier_add.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Supplier
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kontak</th>
                                        <th>Telepon</th>
                                        <th>Alamat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($suppliers)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data supplier.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($suppliers as $supplier): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($supplier['nama']) ?></td>
                                            <td><?= htmlspecialchars($supplier['kontak'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($supplier['telepon'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($supplier['alamat'] ?? '-') ?></td>
                                            <td>
                                                <a href="supplier_edit.php?id=<?= $supplier['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="supplier_delete.php?id=<?= $supplier['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin hapus supplier ini?')">
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
