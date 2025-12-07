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

<!-- Back Button -->
<div class="flex justify-end items-center mb-6">
    <a href="supplier_list.php" class="glass-panel px-4 py-2 rounded-xl hover:shadow-glass transition flex items-center gap-2">
        <i class="fas fa-arrow-left text-brand-blue"></i>
        <span class="font-medium text-brand-dark">Kembali</span>
    </a>
</div>

<?php if (!empty($error)): ?>
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
        <h3 class="text-lg font-bold text-brand-dark">Form Data Supplier</h3>
        <p class="text-sm text-brand-gray">Isi informasi supplier</p>
    </div>
    
    <div class="p-6">
        <form method="POST" class="space-y-6">
            <div>
                <label for="nama" class="block text-sm font-medium text-brand-dark mb-2">
                    Nama Supplier <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="nama" 
                       name="nama" 
                       required 
                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition"
                       placeholder="Nama perusahaan supplier">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="kontak" class="block text-sm font-medium text-brand-dark mb-2">Nama Kontak</label>
                    <input type="text" 
                           id="kontak" 
                           name="kontak" 
                           value="<?= htmlspecialchars($_POST['kontak'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition"
                           placeholder="PIC supplier">
                </div>
                
                <div>
                    <label for="telepon" class="block text-sm font-medium text-brand-dark mb-2">Telepon</label>
                    <input type="text" 
                           id="telepon" 
                           name="telepon" 
                           value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition"
                           placeholder="08xxx atau 021xxx">
                </div>
            </div>
            
            <div>
                <label for="alamat" class="block text-sm font-medium text-brand-dark mb-2">Alamat</label>
                <textarea id="alamat" 
                          name="alamat" 
                          rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition resize-none"
                          placeholder="Alamat lengkap supplier"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" 
                        class="px-6 py-3 bg-brand-blue text-white font-medium rounded-xl hover:bg-brand-blue/90 transition flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan Supplier</span>
                </button>
                <a href="supplier_list.php" 
                   class="px-6 py-3 bg-gray-200 text-brand-dark font-medium rounded-xl hover:bg-gray-300 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
