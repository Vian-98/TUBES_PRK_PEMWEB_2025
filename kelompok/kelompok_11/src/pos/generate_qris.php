<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];
$nama_pelanggan = $input['nama_pelanggan'] ?? '';
$telepon_pelanggan = $input['telepon_pelanggan'] ?? null;
$total = floatval($input['total'] ?? 0);
$diskon = floatval($input['diskon'] ?? 0);
$grand_total = floatval($input['grand_total'] ?? 0);
$reservation_id = !empty($input['reservation_id']) ? intval($input['reservation_id']) : null;
$draft_transaction_id = !empty($input['draft_transaction_id']) ? intval($input['draft_transaction_id']) : null;
$kasir_id = $_SESSION['user_id'];

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Items tidak boleh kosong']);
    exit;
}

if (empty($nama_pelanggan)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama pelanggan harus diisi']);
    exit;
}

try {
    $conn->autocommit(FALSE);
    
    $kode_transaksi = 'TRX-' . date('Ymd') . '-' . sprintf('%04d', rand(1000, 9999));
    
    if ($draft_transaction_id) {
        $stmt = $conn->prepare("UPDATE transactions SET 
                                pelanggan_nama = ?, pelanggan_telepon = ?, total = ?, diskon = ?, 
                                grand_total = ?, bayar = 0, kembali = 0, status = 'draft', 
                                kasir_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssdddii", $nama_pelanggan, $telepon_pelanggan, $total, $diskon, 
                         $grand_total, $kasir_id, $draft_transaction_id);
        $stmt->execute();
        $transaction_id = $draft_transaction_id;
        
        $stmt_del = $conn->prepare("DELETE FROM transaction_items WHERE transaction_id = ?");
        $stmt_del->bind_param("i", $transaction_id);
        $stmt_del->execute();
        
        $stmt_kode = $conn->prepare("SELECT kode FROM transactions WHERE id = ?");
        $stmt_kode->bind_param("i", $transaction_id);
        $stmt_kode->execute();
        $kode_transaksi = $stmt_kode->get_result()->fetch_assoc()['kode'];
    } else {
        $stmt = $conn->prepare("INSERT INTO transactions 
                                (kode, reservation_id, pelanggan_nama, pelanggan_telepon, 
                                 total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, 'draft', ?, NOW(), NOW())");
        $stmt->bind_param("sissdddi", $kode_transaksi, $reservation_id, $nama_pelanggan, 
                         $telepon_pelanggan, $total, $diskon, $grand_total, $kasir_id);
        $stmt->execute();
        $transaction_id = $conn->insert_id;
    }
    
    $stmt_item = $conn->prepare("INSERT INTO transaction_items 
                                  (transaction_id, service_id, part_id, nama_item, qty, harga_unit, subtotal, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    
    foreach ($items as $item) {
        $service_id = $item['tipe'] === 'service' ? intval($item['serviceId']) : null;
        $part_id = $item['tipe'] === 'part' ? intval($item['partId']) : null;
        $nama_item = $item['nama'];
        $qty = intval($item['qty']);
        $harga_unit = floatval($item['harga']);
        $subtotal = floatval($item['subtotal']);
        
        $stmt_item->bind_param("iiisidi", $transaction_id, $service_id, $part_id, 
                              $nama_item, $qty, $harga_unit, $subtotal);
        $stmt_item->execute();
    }
    
    // Generate URL verify QRIS dengan path yang benar
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    
    // SIMPLE: Pakai HTTP_HOST dari request browser (ini IP yang dipake akses POS)
    // Kalau akses http://192.168.1.100:8082 → HTTP_HOST = 192.168.1.100:8082
    $host_with_port = $_SERVER['HTTP_HOST'];
    
    $verify_url = $protocol . '://' . $host_with_port . '/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_11/src/pos/verify_qris.php?tx=' . urlencode($kode_transaksi);
    
    $expired_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    $qris_data = json_encode([
        'qr_url' => $verify_url,
        'expired_at' => $expired_at,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $stmt_payment = $conn->prepare("INSERT INTO transaction_payments 
                                     (transaction_id, metode, jumlah, rincian, dibayar_pada, created_by, created_at) 
                                     VALUES (?, 'qris', ?, ?, NULL, ?, NOW())");
    $stmt_payment->bind_param("idsi", $transaction_id, $grand_total, $qris_data, $kasir_id);
    $stmt_payment->execute();
    
    $conn->commit();
    $conn->autocommit(TRUE);
    
    $qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($verify_url);
    
    echo json_encode([
        'status' => 'success',
        'transaction_id' => $transaction_id,
        'kode_transaksi' => $kode_transaksi,
        'qr_image_url' => $qr_image_url,
        'verify_url' => $verify_url,
        'expired_at' => $expired_at,
        'amount' => $grand_total
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(TRUE);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
