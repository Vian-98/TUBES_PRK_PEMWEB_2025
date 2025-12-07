<?php
session_start();
require_once '../config/database.php';
$conn = getConnection(); // FIX: Initialize connection

// Ambil kode transaksi dari URL
$kode_transaksi = $_GET['tx'] ?? '';

if (empty($kode_transaksi)) {
    die("Invalid transaction code");
}

$stmt = $conn->prepare("SELECT t.*, tp.id as payment_id, tp.dibayar_pada, tp.rincian
                        FROM transactions t
                        LEFT JOIN transaction_payments tp ON tp.transaction_id = t.id AND tp.metode = 'qris'
                        WHERE t.kode = ?");
$stmt->bind_param("s", $kode_transaksi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Transaction not found");
}

$transaction = $result->fetch_assoc();
$qris_data = json_decode($transaction['rincian'], true);
$expired_at = $qris_data['expired_at'] ?? null;
$is_expired = $expired_at && strtotime($expired_at) < time();
$is_paid = !empty($transaction['dibayar_pada']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran QRIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
        <div class="text-center mb-6">
            <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-tools text-4xl text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Bengkel UMKM</h1>
            <p class="text-gray-600 text-sm">Konfirmasi Pembayaran QRIS</p>
        </div>

        <div class="border-t border-b border-gray-200 py-4 mb-6 space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">No. Transaksi:</span>
                <strong class="text-gray-800"><?= htmlspecialchars($transaction['kode']) ?></strong>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Pelanggan:</span>
                <strong class="text-gray-800"><?= htmlspecialchars($transaction['pelanggan_nama']) ?></strong>
            </div>
            <?php if ($transaction['pelanggan_telepon']): ?>
            <div class="flex justify-between">
                <span class="text-gray-600">Telepon:</span>
                <strong class="text-gray-800"><?= htmlspecialchars($transaction['pelanggan_telepon']) ?></strong>
            </div>
            <?php endif; ?>
            <div class="flex justify-between items-center pt-2 border-t">
                <span class="text-lg text-gray-700">Total Bayar:</span>
                <strong class="text-2xl text-blue-600">Rp <?= number_format($transaction['grand_total'], 0, ',', '.') ?></strong>
            </div>
        </div>

        <?php if ($is_paid): ?>
            <div class="bg-green-100 border border-green-300 text-green-800 p-6 rounded-xl text-center">
                <i class="fas fa-check-circle text-5xl mb-3 text-green-600"></i>
                <p class="font-bold text-lg mb-1">Pembayaran Berhasil!</p>
                <p class="text-sm">Transaksi telah diselesaikan</p>
                <p class="text-xs mt-2 text-green-700">
                    Dibayar pada: <?= date('d/m/Y H:i', strtotime($transaction['dibayar_pada'])) ?>
                </p>
            </div>
        <?php elseif ($is_expired): ?>
            <div class="bg-red-100 border border-red-300 text-red-800 p-6 rounded-xl text-center">
                <i class="fas fa-times-circle text-5xl mb-3 text-red-600"></i>
                <p class="font-bold text-lg mb-1">QRIS Expired</p>
                <p class="text-sm">Waktu pembayaran telah habis</p>
                <p class="text-xs mt-2 text-red-700">Silakan buat transaksi baru di kasir</p>
            </div>
        <?php else: ?>
            <div id="buttonContainer">
                <button onclick="confirmPayment()" 
                        id="btnConfirm"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 rounded-xl shadow-lg transform transition hover:scale-105 active:scale-95">
                    <i class="fas fa-check-circle mr-2"></i>
                    Konfirmasi Pembayaran
                </button>
                
                <p class="text-center text-xs text-gray-500 mt-4">
                    <i class="fas fa-clock mr-1"></i>
                    QRIS expired pada: <br>
                    <strong><?= date('d/m/Y H:i:s', strtotime($expired_at)) ?></strong>
                </p>
            </div>

            <div id="loadingState" class="hidden text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-3"></div>
                <p class="text-gray-600">Memproses pembayaran...</p>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center text-xs text-gray-500">
            <p>Simulasi Pembayaran QRIS</p>
            <p class="mt-1">Bengkel UMKM POS System</p>
        </div>
    </div>

    <script>
        async function confirmPayment() {
            const btn = document.getElementById('btnConfirm');
            const buttonContainer = document.getElementById('buttonContainer');
            const loadingState = document.getElementById('loadingState');
            
            btn.disabled = true;
            buttonContainer.classList.add('hidden');
            loadingState.classList.remove('hidden');
            
            try {
                const response = await fetch('confirm_payment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        transaction_code: '<?= htmlspecialchars($kode_transaksi) ?>'
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                    buttonContainer.classList.remove('hidden');
                    loadingState.classList.add('hidden');
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
                buttonContainer.classList.remove('hidden');
                loadingState.classList.add('hidden');
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
