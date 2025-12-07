<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

require_role(['Admin']);

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

$pageTitle = 'Edit Sparepart';
require_once __DIR__ . '/../layout/header.php';
?>

<!-- Back Button -->
<div class="flex justify-end items-center mb-6">
    <a href="part_list.php" class="glass-panel px-4 py-2 rounded-xl hover:shadow-glass transition flex items-center gap-2">
        <i class="fas fa-arrow-left text-brand-blue"></i>
        <span class="font-medium text-brand-dark">Kembali</span>
    </a>
</div>

<?php if ($error): ?>
<div class="glass-panel p-4 rounded-2xl border-l-4 border-red-500 mb-6">
    <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
        <p class="text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Form Card -->
<div class="glass-panel rounded-3xl shadow-glass overflow-hidden">
    <div class="px-6 py-4 bg-white/40 border-b border-white/50">
        <h3 class="text-lg font-bold text-brand-dark">Form Edit Sparepart</h3>
        <p class="text-sm text-brand-gray">Update informasi sparepart</p>
    </div>
    
    <div class="p-6">
        <form method="POST" class="space-y-6">
            <!-- Row 1 - Nama & SKU -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-brand-dark mb-2">
                        Nama Sparepart <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           required 
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition">
                </div>
                
                <div>
                    <label for="sku" class="block text-sm font-medium text-brand-dark mb-2">
                        SKU <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="sku" 
                           name="sku" 
                           required 
                           value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition">
                </div>
            </div>
            
            <!-- Row 2 - Deskripsi -->
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-brand-dark mb-2">
                    Deskripsi
                </label>
                <textarea id="deskripsi" 
                          name="deskripsi" 
                          rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition resize-none"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>
            
            <!-- Row 3 - Harga Beli & Harga Jual -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="harga_beli" class="block text-sm font-medium text-brand-dark mb-2">Harga Beli (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-brand-gray">Rp</span>
                        <input type="number" 
                               id="harga_beli" 
                               name="harga_beli" 
                               min="0"
                               value="<?= htmlspecialchars($_POST['harga_beli'] ?? '0') ?>"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition">
                    </div>
                </div>
                
                <div>
                    <label for="harga_jual" class="block text-sm font-medium text-brand-dark mb-2">Harga Jual (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-brand-gray">Rp</span>
                        <input type="number" 
                               id="harga_jual" 
                               name="harga_jual" 
                               min="0"
                               value="<?= htmlspecialchars($_POST['harga_jual'] ?? '0') ?>"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition">
                    </div>
                </div>
            </div>
            
            <!-- Row 4 - Stok, Min Stok, Supplier -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="stok" class="block text-sm font-medium text-brand-dark mb-2">Stok</label>
                    <input type="number" 
                           id="stok" 
                           name="stok" 
                           min="0"
                           value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition">
                </div>
                
                <div>
                    <label for="min_stok" class="block text-sm font-medium text-brand-dark mb-2">Stok Minimum</label>
                    <input type="number" 
                           id="min_stok" 
                           name="min_stok" 
                           min="0"
                           value="<?= htmlspecialchars($_POST['min_stok'] ?? '0') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition">
                </div>
                
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-brand-dark mb-2">Supplier</label>
                    <select id="supplier_id" 
                            name="supplier_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition bg-white">
                        <option value="">-- Pilih Supplier --</option>
                        <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= $supplier['id'] ?>" <?= (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($supplier['nama']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex gap-3 pt-4">
                <button type="submit" 
                        class="px-6 py-3 bg-brand-blue text-white font-medium rounded-xl hover:bg-brand-blue/90 transition flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Update Sparepart</span>
                </button>
                <a href="part_list.php" 
                   class="px-6 py-3 bg-gray-200 text-brand-dark font-medium rounded-xl hover:bg-gray-300 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
