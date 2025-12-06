<?php
$pageTitle = 'Edit Sparepart';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$error = '';
$id = $_GET['id'] ?? 0;

// Get part data
$part = fetchOne("SELECT * FROM parts WHERE id = " . intval($id));
if (!$part) {
    header('Location: part_list.php');
    exit;
}

// Get suppliers
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY nama");

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $harga_beli = $_POST['harga_beli'] ?? 0;
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $stok = $_POST['stok'] ?? 0;
    $supplier_id = $_POST['supplier_id'] ?? null;
    $min_stok = $_POST['min_stok'] ?? 0;
    
    if (!empty($nama) && !empty($sku)) {
        $conn = getConnection();
        $nama = mysqli_real_escape_string($conn, $nama);
        $sku = mysqli_real_escape_string($conn, $sku);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $supplier_id = $supplier_id ? intval($supplier_id) : 'NULL';
        
        $sql = "UPDATE parts SET 
                nama = '$nama', 
                sku = '$sku', 
                deskripsi = '$deskripsi', 
                harga_beli = $harga_beli, 
                harga_jual = $harga_jual, 
                stok = $stok, 
                supplier_id = $supplier_id, 
                min_stok = $min_stok,
                updated_at = NOW()
                WHERE id = " . intval($id);
        
        if (mysqli_query($conn, $sql)) {
            mysqli_close($conn);
            header('Location: part_list.php');
            exit;
        } else {
            $error = 'Gagal update sparepart: ' . mysqli_error($conn);
        }
        mysqli_close($conn);
    } else {
        $error = 'Nama dan SKU harus diisi.';
    }
} else {
    // Populate form with existing data
    $_POST = $part;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <?php include __DIR__ . '/../layout/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit Sparepart</h2>
                    <a href="part_list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Sparepart *</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required 
                                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU *</label>
                                    <input type="text" class="form-control" id="sku" name="sku" required 
                                           value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="harga_beli" class="form-label">Harga Beli</label>
                                    <input type="number" class="form-control" id="harga_beli" name="harga_beli" min="0" 
                                           value="<?= htmlspecialchars($_POST['harga_beli'] ?? '0') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="harga_jual" class="form-label">Harga Jual</label>
                                    <input type="number" class="form-control" id="harga_jual" name="harga_jual" min="0" 
                                           value="<?= htmlspecialchars($_POST['harga_jual'] ?? '0') ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="stok" class="form-label">Stok</label>
                                    <input type="number" class="form-control" id="stok" name="stok" min="0" 
                                           value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="min_stok" class="form-label">Stok Minimum</label>
                                    <input type="number" class="form-control" id="min_stok" name="min_stok" min="0" 
                                           value="<?= htmlspecialchars($_POST['min_stok'] ?? '0') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select" id="supplier_id" name="supplier_id">
                                        <option value="">-- Pilih Supplier --</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>" <?= (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($supplier['nama']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
