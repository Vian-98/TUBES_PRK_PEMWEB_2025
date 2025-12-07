<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$conn = getConnection(); // FIX: Initialize connection

// Ambil ID transaksi
$transaction_id = $_GET['id'] ?? null;

if ($transaction_id == 0) {
    die('ID Transaksi tidak valid');
}

// Ambil data transaksi
$query_trx = "SELECT t.*, u.nama as kasir_nama 
              FROM transactions t
              LEFT JOIN users u ON t.kasir_id = u.id
              WHERE t.id = ?";
$stmt_trx = $conn->prepare($query_trx);
$stmt_trx->bind_param("i", $transaction_id);
$stmt_trx->execute();
$transaksi = $stmt_trx->get_result()->fetch_assoc();

if (!$transaksi) {
    die('Transaksi tidak ditemukan');
}

// Ambil items transaksi
$query_items = "SELECT * FROM transaction_items WHERE transaction_id = ? ORDER BY id ASC";
$stmt_items = $conn->prepare($query_items);
$stmt_items->bind_param("i", $transaction_id);
$stmt_items->execute();
$items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil data pembayaran
$query_payment = "SELECT * FROM transaction_payments WHERE transaction_id = ? LIMIT 1";
$stmt_payment = $conn->prepare($query_payment);
$stmt_payment->bind_param("i", $transaction_id);
$stmt_payment->execute();
$payment = $stmt_payment->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - <?php echo htmlspecialchars($transaksi['kode']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
            .struk-container {
                width: 80mm;
                margin: 0 auto;
            }
        }
        
        .struk-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .dotted-line {
            border-bottom: 2px dashed #ccc;
            margin: 10px 0;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Tombol Aksi (tidak diprint) -->
        <div class="no-print mb-6 flex gap-3 justify-center">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                <i class="fas fa-print"></i> Print Struk
            </button>
            <button onclick="window.location.href='pos.php'" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                <i class="fas fa-cash-register"></i> Transaksi Baru
            </button>
            <button onclick="window.history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>

        <!-- Struk -->
        <div class="struk-container bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="p-6">
                <!-- Header Bengkel -->
                <div class="text-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">BENGKEL UMKM</h1>
                    <p class="text-sm text-gray-600">Jl. Contoh No. 123, Kota</p>
                    <p class="text-sm text-gray-600">Telp: (021) 12345678</p>
                </div>

                <div class="dotted-line"></div>

                <!-- Info Transaksi -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-semibold">No. Transaksi:</span>
                        <span><?php echo htmlspecialchars($transaksi['kode']); ?></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-semibold">Tanggal:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($transaksi['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-semibold">Kasir:</span>
                        <span><?php echo htmlspecialchars($transaksi['kasir_nama']); ?></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-semibold">Pelanggan:</span>
                        <span><?php echo htmlspecialchars($transaksi['pelanggan_nama']); ?></span>
                    </div>
                    <?php if ($transaksi['pelanggan_telepon']): ?>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-semibold">Telepon:</span>
                        <span><?php echo htmlspecialchars($transaksi['pelanggan_telepon']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="dotted-line"></div>

                <!-- Items -->
                <div class="mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Item</th>
                                <th class="text-center py-2">Qty</th>
                                <th class="text-right py-2">Harga</th>
                                <th class="text-right py-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="border-b">
                                <td class="py-2">
                                    <?php echo htmlspecialchars($item['nama_item']); ?>
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        <?php echo $item['service_id'] ? 'Layanan' : 'Sparepart'; ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php echo $item['qty']; ?></td>
                                <td class="text-right">
                                    Rp <?php echo number_format($item['harga_unit'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-right font-semibold">
                                    Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="dotted-line"></div>

                <!-- Total -->
                <div class="mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Total:</span>
                        <span>Rp <?php echo number_format($transaksi['total'], 0, ',', '.'); ?></span>
                    </div>
                    <?php if ($transaksi['diskon'] > 0): ?>
                    <div class="flex justify-between mb-2 text-red-600">
                        <span class="font-semibold">Diskon:</span>
                        <span>- Rp <?php echo number_format($transaksi['diskon'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Grand Total:</span>
                        <span>Rp <?php echo number_format($transaksi['grand_total'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="dotted-line"></div>

                <!-- Pembayaran -->
                <div class="mb-4">
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Metode Pembayaran:</span>
                        <span class="uppercase"><?php echo htmlspecialchars($payment['metode']); ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Bayar:</span>
                        <span>Rp <?php echo number_format($transaksi['bayar'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-green-600">
                        <span>Kembalian:</span>
                        <span>Rp <?php echo number_format($transaksi['kembali'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="dotted-line"></div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600 mb-2">Terima kasih atas kunjungan Anda!</p>
                    <p class="text-xs text-gray-500">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                    <div class="mt-4 text-xs text-gray-400">
                        <p>Dicetak: <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mt-4 text-center">
                    <?php if ($transaksi['status'] === 'paid'): ?>
                        <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full font-bold text-sm">
                            <i class="fas fa-check-circle"></i> LUNAS
                        </span>
                    <?php elseif ($transaksi['status'] === 'draft'): ?>
                        <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-bold text-sm">
                            <i class="fas fa-clock"></i> DRAFT
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info Tambahan (tidak diprint) -->
        <div class="no-print mt-6 text-center text-gray-600">
            <p class="text-sm">
                <i class="fas fa-info-circle"></i> 
                Struk ini dapat dicetak dengan menekan tombol "Print Struk" di atas
            </p>
        </div>
    </div>

    <script>
        // Auto focus untuk print (opsional)
        // window.addEventListener('load', function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // });
    </script>
</body>
</html>