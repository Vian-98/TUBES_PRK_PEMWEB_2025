<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Koneksi database
require_once '../config/database.php';

// Ambil data layanan
$query_services = "SELECT * FROM services ORDER BY nama ASC";
$services = $conn->query($query_services);

// Ambil data sparepart dengan gambar
$query_parts = "SELECT id, nama, sku, harga_jual, stok, image_url FROM parts WHERE stok > 0 ORDER BY nama ASC";
$parts = $conn->query($query_parts);

// Ambil draft transaction jika ada (dari check-in reservasi)
$draft_transaction = null;
$draft_items = [];
$reservation_data = null;

if (isset($_GET['draft_id'])) {
    $draft_id = intval($_GET['draft_id']);
    $stmt = $conn->prepare("SELECT t.*, r.kode as reservation_kode, r.nama_pelanggan as res_pelanggan, r.telepon as res_telepon 
                             FROM transactions t 
                             LEFT JOIN reservations r ON t.reservation_id = r.id 
                             WHERE t.id = ? AND t.status = 'draft'");
    $stmt->bind_param("i", $draft_id);
    $stmt->execute();
    $draft_transaction = $stmt->get_result()->fetch_assoc();
    
    if ($draft_transaction) {
        // Ambil items dari draft
        $stmt_items = $conn->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
        $stmt_items->bind_param("i", $draft_id);
        $stmt_items->execute();
        $draft_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Bengkel UMKM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .card-item {
            transition: all 0.3s ease;
        }
        .item-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .tab-active {
            border-bottom: 3px solid #2563eb;
            color: #2563eb;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-cash-register text-blue-600"></i> Point of Sale
                    </h1>
                    <p class="text-gray-600 mt-1">Kasir: <?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                    <?php if ($draft_transaction): ?>
                        <p class="text-green-600 font-semibold mt-2">
                            <i class="fas fa-calendar-check"></i> 
                            Draft dari Reservasi: <?php echo htmlspecialchars($draft_transaction['reservation_kode']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Tanggal</p>
                    <p class="text-lg font-semibold" id="currentDate"></p>
                    <p class="text-lg font-semibold" id="currentTime"></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Katalog Produk -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Tab Navigation -->
                    <div class="flex border-b mb-6">
                        <button onclick="switchTab('layanan')" id="tabLayanan" class="px-6 py-3 font-semibold text-gray-700 tab-active">
                            <i class="fas fa-tools"></i> Layanan
                        </button>
                        <button onclick="switchTab('sparepart')" id="tabSparepart" class="px-6 py-3 font-semibold text-gray-700">
                            <i class="fas fa-box"></i> Sparepart
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="relative">
                            <input type="text" id="searchInput" onkeyup="searchItems()" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Cari layanan atau sparepart...">
                            <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Katalog Layanan -->
                    <div id="katalogLayanan" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto">
                        <?php 
                        $services->data_seek(0); // Reset pointer
                        while ($service = $services->fetch_assoc()): 
                        ?>
                            <div class="card-item bg-white border-2 border-gray-200 rounded-lg overflow-hidden hover:border-blue-500 flex flex-col" data-type="layanan" data-nama="<?php echo strtolower($service['nama']); ?>">
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-32 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-tools text-6xl text-white opacity-50"></i>
                                </div>
                                <div class="p-4 flex flex-col flex-grow">
                                    <h3 class="font-bold text-gray-800 mb-2 h-12 line-clamp-2"><?php echo htmlspecialchars($service['nama']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-2 h-10 line-clamp-2"><?php echo htmlspecialchars(substr($service['deskripsi'], 0, 50)); ?>...</p>
                                    <div class="flex justify-between items-center mb-3 mt-auto">
                                        <span class="text-lg font-bold text-blue-600">Rp <?php echo number_format($service['harga'], 0, ',', '.'); ?></span>
                                        <span class="text-xs text-gray-500"><i class="fas fa-clock"></i> <?php echo $service['durasi_menit']; ?> mnt</span>
                                    </div>
                                    <button onclick="tambahKeKeranjang('service', <?php echo $service['id']; ?>, '<?php echo addslashes($service['nama']); ?>', <?php echo $service['harga']; ?>, null)" 
                                            class="w-full bg-[#294B93] hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition">
                                        <i class="fas fa-cart-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Katalog Sparepart -->
                    <div id="katalogSparepart" class="hidden grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto">
                        <?php 
                        $parts->data_seek(0); // Reset pointer
                        while ($part = $parts->fetch_assoc()): 
                            // Gunakan gambar default lokal atau data URL
                            $imageUrl = !empty($part['image_url']) ? $part['image_url'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="200"%3E%3Crect fill="%23ddd" width="300" height="200"/%3E%3Ctext fill="%23999" font-family="sans-serif" font-size="20" dy="10.5" font-weight="bold" x="50%25" y="50%25" text-anchor="middle"%3ENo Image%3C/text%3E%3C/svg%3E';
                        ?>
                            <div class="card-item bg-white border-2 border-gray-200 rounded-lg overflow-hidden hover:border-green-500 flex flex-col" data-type="sparepart" data-nama="<?php echo strtolower($part['nama']); ?>">
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($part['nama']); ?>" class="item-image flex-shrink-0" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22300%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 font-family=%22sans-serif%22 font-size=%2220%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                                <div class="p-4 flex flex-col flex-grow">
                                    <h3 class="font-bold text-gray-800 mb-1 h-12 line-clamp-2"><?php echo htmlspecialchars($part['nama']); ?></h3>
                                    <p class="text-xs text-gray-500 mb-2 h-5">SKU: <?php echo htmlspecialchars($part['sku']); ?></p>
                                    <div class="flex justify-between items-center mb-3 mt-auto">
                                        <span class="text-lg font-bold text-green-600">Rp <?php echo number_format($part['harga_jual'], 0, ',', '.'); ?></span>
                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $part['stok'] <= 10 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'; ?>">
                                            <i class="fas fa-box"></i> Stok: <?php echo $part['stok']; ?>
                                        </span>
                                    </div>
                                    <button onclick="tambahKeKeranjang('part', <?php echo $part['id']; ?>, '<?php echo addslashes($part['nama']); ?>', <?php echo $part['harga_jual']; ?>, <?php echo $part['stok']; ?>)" 
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition">
                                        <i class="fas fa-cart-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Keranjang Belanja -->
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-shopping-cart text-purple-600"></i> Keranjang Belanja
                        <span id="cartCount" class="ml-2 bg-purple-600 text-white text-sm px-3 py-1 rounded-full">0</span>
                    </h2>
                    
                    <div id="keranjangItems" class="space-y-3">
                        <!-- Items akan di-render oleh JavaScript -->
                        <div id="emptyCart" class="text-center py-12 text-gray-400">
                            <i class="fas fa-shopping-cart text-6xl mb-4"></i>
                            <p>Keranjang masih kosong</p>
                            <p class="text-sm mt-2">Pilih layanan atau sparepart untuk memulai transaksi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pembayaran -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-money-bill-wave text-green-600"></i> Pembayaran
                    </h2>

                    <!-- Info Pelanggan -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pelanggan</label>
                        <input type="text" id="namaPelanggan" 
                               value="<?php echo $draft_transaction ? htmlspecialchars($draft_transaction['res_pelanggan'] ?? $draft_transaction['pelanggan_nama']) : ''; ?>"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Telepon</label>
                        <input type="text" id="teleponPelanggan" 
                               value="<?php echo $draft_transaction ? htmlspecialchars($draft_transaction['res_telepon'] ?? $draft_transaction['pelanggan_telepon']) : ''; ?>"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Ringkasan Harga -->
                    <div class="border-t border-b border-gray-300 py-4 mb-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700">Total</span>
                            <span class="font-semibold" id="totalHarga">Rp 0</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <label class="text-gray-700">Diskon (Rp)</label>
                            <input type="number" id="diskon" value="0" min="0" 
                                   onchange="hitungGrandTotal()"
                                   class="w-32 text-right border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-gray-800">Grand Total</span>
                            <span class="text-blue-600" id="grandTotal">Rp 0</span>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                        <select id="metodePembayaran" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="tunai">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>

                    <!-- Jumlah Bayar -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Bayar</label>
                        <input type="number" id="jumlahBayar" value="0" min="0" 
                               onchange="hitungKembalian()"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Kembalian -->
                    <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-semibold">Kembalian</span>
                            <span class="text-xl font-bold text-yellow-600" id="kembalian">Rp 0</span>
                        </div>
                    </div>

                    <!-- Tombol Proses -->
                    <button onclick="prosesPembayaran()" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 rounded-lg shadow-lg transition transform hover:scale-105">
                        <i class="fas fa-check-circle"></i> Proses Pembayaran
                    </button>

                    <button onclick="resetForm()" class="w-full mt-3 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 rounded-lg transition">
                        <i class="fas fa-redo"></i> Reset
                    </button>

                    <!-- Hidden fields -->
                    <input type="hidden" id="reservationId" value="<?php echo $draft_transaction ? $draft_transaction['reservation_id'] : ''; ?>">
                    <input type="hidden" id="draftTransactionId" value="<?php echo $draft_transaction ? $draft_transaction['id'] : ''; ?>">
                </div>
            </div>
        </div>
    </div>

    <script src="../js/pos.js"></script>
    <script>
        // Load draft items jika ada
        <?php if (!empty($draft_items)): ?>
            <?php foreach ($draft_items as $item): ?>
                tambahItemManual({
                    id: '<?php echo $item['service_id'] ? 's_' . $item['service_id'] : 'p_' . $item['part_id']; ?>',
                    nama: '<?php echo addslashes($item['nama_item']); ?>',
                    harga: <?php echo $item['harga_unit']; ?>,
                    qty: <?php echo $item['qty']; ?>,
                    tipe: '<?php echo $item['service_id'] ? 'service' : 'part'; ?>'
                });
            <?php endforeach; ?>
        <?php endif; ?>

        // Update waktu realtime
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <!-- Modal QRIS -->
    <div id="modalQRIS" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-qrcode text-3xl text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Scan QRIS</h2>
                <p class="text-gray-600 text-sm mb-6">Scan dengan kamera HP untuk melakukan pembayaran</p>
            </div>

            <!-- QR Code Container -->
            <div id="qrCodeContainer" class="mb-6 bg-gray-50 p-4 rounded-xl">
                <!-- QR Code will be injected here -->
            </div>

            <!-- Transaction Info -->
            <div class="bg-blue-50 rounded-xl p-4 mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Total Pembayaran:</span>
                    <strong id="qrAmount" class="text-2xl text-blue-600">Rp 0</strong>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">Kode Transaksi:</span>
                    <strong id="qrCode" class="text-gray-800"></strong>
                </div>
            </div>

            <!-- Status -->
            <div class="text-center mb-4">
                <div class="animate-pulse inline-flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce"></div>
                    <span class="text-blue-600 font-medium">Menunggu pembayaran...</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-clock mr-1"></i>
                    Expired pada: <span id="qrExpiry"></span>
                </p>
            </div>

            <!-- Button Batal -->
            <button onclick="closeQRISModal()" 
                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-xl transition">
                <i class="fas fa-times mr-2"></i>
                Batalkan
            </button>

            <p class="text-center text-xs text-gray-500 mt-4">
                Sistem akan otomatis update setelah pembayaran berhasil
            </p>
        </div>
    </div>
</body>
</html>
