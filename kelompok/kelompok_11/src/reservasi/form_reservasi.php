<?php

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

$layanan = fetchAll("SELECT id, nama FROM services ORDER BY nama ASC");
$mekanik = fetchAll("SELECT id, nama FROM users WHERE role_id = 3 ORDER BY nama ASC");

$reservasi = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $reservasi = fetchOne("SELECT * FROM reservations WHERE id = $id");
    if (!$reservasi) {
        $_SESSION['error'] = "Reservasi tidak ditemukan!";
        header("Location: list.php");
        exit;
    }
}

$pageTitle = isset($reservasi) ? 'Edit Reservasi' : 'Buat Reservasi Baru';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto px-8 pb-8">
    <div class="max-w-2xl mx-auto mt-8">
        <div class="glass-panel rounded-2xl shadow-glass p-8">
            <h2 class="text-2xl font-bold mb-6"><?= $pageTitle ?></h2>

            <form method="post" action="proses_reservasi.php">
                <?php if (isset($reservasi)): ?>
                    <input type="hidden" name="id" value="<?= $reservasi['id'] ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Nama Pelanggan</label>
                    <input
                        type="text"
                        name="nama_pelanggan"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        required
                        value="<?= htmlspecialchars($reservasi['nama_pelanggan'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Telepon</label>
                    <input
                        type="text"
                        name="telepon"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        required
                        value="<?= htmlspecialchars($reservasi['telepon'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Plat Kendaraan</label>
                    <input
                        type="text"
                        name="plat_kendaraan"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        required
                        value="<?= htmlspecialchars($reservasi['plat_kendaraan'] ?? '') ?>">
                </div>

                <!-- JENIS KENDARAAN: diambil dari kolom jenis_kendaraan di tabel reservations -->
                <div class="mb-4">
                    <label class="block font-medium mb-1">Jenis Kendaraan</label>
                    <input
                        type="text"
                        name="jenis_kendaraan"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        placeholder="Contoh: Avanza, Jazz, Xenia, dll"
                        value="<?= htmlspecialchars($reservasi['jenis_kendaraan'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Layanan</label>
                    <select
                        name="layanan_id"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        required>
                        <option value="">- Pilih Layanan -</option>
                        <?php foreach ($layanan as $l): ?>
                            <option
                                value="<?= $l['id'] ?>"
                                <?= (isset($reservasi) && $reservasi['layanan_id'] == $l['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($l['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Mekanik</label>
                    <select
                        name="mekanik_id"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        required>
                        <option value="">- Pilih Mekanik -</option>
                        <?php foreach ($mekanik as $m): ?>
                            <option
                                value="<?= $m['id'] ?>"
                                <?= (isset($reservasi) && $reservasi['mekanik_id'] == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Tanggal & Jam</label>
                    <input
                        type="datetime-local"
                        name="tanggal"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        required
                        value="<?= isset($reservasi) ? date('Y-m-d\TH:i', strtotime($reservasi['tanggal'])) : '' ?>">
                </div>

                <!-- CATATAN OPSIONAL -->
                <div class="mb-4">
                    <label class="block font-medium mb-1">Catatan</label>
                    <textarea
                        name="catatan"
                        rows="3"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300"
                        placeholder="Keluhan tambahan, kondisi khusus, dll"><?= htmlspecialchars($reservasi['catatan'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button
                        type="submit"
                        class="bg-brand-blue text-white px-6 py-2 rounded-xl hover:bg-blue-600 transition font-semibold">
                        <?= isset($reservasi) ? 'Update' : 'Simpan' ?>
                    </button>
                    <a
                        href="list.php"
                        class="bg-gray-200 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-300 transition font-semibold">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
