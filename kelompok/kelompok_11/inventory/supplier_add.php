<?php
$pageTitle = 'Tambah Supplier';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $kontak = $_POST['kontak'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    
    if (!empty($nama)) {
        $conn = getConnection();
        $nama = mysqli_real_escape_string($conn, $nama);
        $kontak = mysqli_real_escape_string($conn, $kontak);
        $telepon = mysqli_real_escape_string($conn, $telepon);
        $alamat = mysqli_real_escape_string($conn, $alamat);
        
        $sql = "INSERT INTO suppliers (nama, kontak, telepon, alamat, created_at) 
                VALUES ('$nama', '$kontak', '$telepon', '$alamat', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            mysqli_close($conn);
            header('Location: supplier_list.php');
            exit;
        } else {
            $error = 'Gagal menambah supplier: ' . mysqli_error($conn);
        }
        mysqli_close($conn);
    } else {
        $error = 'Nama supplier harus diisi.';
    }
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
                    <h2>Tambah Supplier</h2>
                    <a href="supplier_list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Supplier *</label>
                                <input type="text" class="form-control" id="nama" name="nama" required 
                                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kontak" class="form-label">Nama Kontak</label>
                                    <input type="text" class="form-control" id="kontak" name="kontak" 
                                           value="<?= htmlspecialchars($_POST['kontak'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telepon" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="telepon" name="telepon" 
                                           value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
