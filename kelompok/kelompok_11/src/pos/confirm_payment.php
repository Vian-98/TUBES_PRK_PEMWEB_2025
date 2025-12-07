<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';
$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$kode_transaksi = $input['transaction_code'] ?? '';

if (empty($kode_transaksi)) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction code required']);
    exit;
}

try {
    $conn->autocommit(FALSE);
    
    $stmt = $conn->prepare("SELECT t.id as transaction_id, t.grand_total, t.status, 
                            tp.id as payment_id, tp.dibayar_pada, tp.rincian, tp.created_by
                            FROM transactions t
                            LEFT JOIN transaction_payments tp ON tp.transaction_id = t.id AND tp.metode = 'qris'
                            WHERE t.kode = ?");
    $stmt->bind_param("s", $kode_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Transaction not found');
    }
    
    $data = $result->fetch_assoc();
    
    if (!empty($data['dibayar_pada'])) {
        throw new Exception('Transaction already paid');
    }
    
    $qris_data = json_decode($data['rincian'], true);
    $qris_data['verified_at'] = date('Y-m-d H:i:s');
    $qris_data['verified_ip'] = $_SERVER['REMOTE_ADDR'];
    $updated_rincian = json_encode($qris_data);
    
    $stmt_payment = $conn->prepare("UPDATE transaction_payments 
                                     SET dibayar_pada = NOW(), rincian = ?
                                     WHERE id = ?");
    $stmt_payment->bind_param("si", $updated_rincian, $data['payment_id']);
    $stmt_payment->execute();
    
    $grand_total = $data['grand_total'];
    $stmt_transaction = $conn->prepare("UPDATE transactions 
                                        SET status = 'paid', bayar = ?, kembali = 0, updated_at = NOW()
                                        WHERE id = ?");
    $stmt_transaction->bind_param("di", $grand_total, $data['transaction_id']);
    $stmt_transaction->execute();
    
    $stmt_items = $conn->prepare("SELECT part_id, qty, nama_item, harga_unit 
                                   FROM transaction_items 
                                   WHERE transaction_id = ? AND part_id IS NOT NULL");
    $stmt_items->bind_param("i", $data['transaction_id']);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
    
    while ($item = $items_result->fetch_assoc()) {
        $stmt_stok = $conn->prepare("UPDATE parts SET stok = stok - ? WHERE id = ?");
        $stmt_stok->bind_param("ii", $item['qty'], $item['part_id']);
        $stmt_stok->execute();
        
        $stmt_cek = $conn->prepare("SELECT stok FROM parts WHERE id = ?");
        $stmt_cek->bind_param("i", $item['part_id']);
        $stmt_cek->execute();
        $result_stok = $stmt_cek->get_result();
        $row_stok = $result_stok->fetch_assoc();
        
        if ($row_stok['stok'] < 0) {
            throw new Exception("Stok tidak mencukupi untuk item: " . $item['nama_item']);
        }
        
        $tipe_movement = 'keluar';
        $keterangan = "Penjualan transaksi (QRIS): " . $kode_transaksi;
        
        $stmt_movement = $conn->prepare("INSERT INTO stock_movements 
                                         (part_id, tipe, qty, harga_unit, keterangan, created_by, created_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt_movement->bind_param("isidsi", $item['part_id'], $tipe_movement, $item['qty'], 
                                  $item['harga_unit'], $keterangan, $data['created_by']);
        $stmt_movement->execute();
    }
    
    $action = "CONFIRM_QRIS_PAYMENT";
    $tabel = "transaction_payments";
    $after_data = json_encode([
        'transaction_code' => $kode_transaksi,
        'amount' => $grand_total,
        'verified_at' => date('Y-m-d H:i:s'),
        'verified_ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $user_id = $data['created_by'];
    
    $stmt_audit = $conn->prepare("INSERT INTO audit_logs 
                                   (user_id, action, tabel, record_id, after_data, created_at) 
                                   VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt_audit->bind_param("issis", $user_id, $action, $tabel, $data['payment_id'], $after_data);
    $stmt_audit->execute();
    
    $conn->commit();
    $conn->autocommit(TRUE);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Pembayaran berhasil dikonfirmasi',
        'transaction_id' => $data['transaction_id'],
        'transaction_code' => $kode_transaksi
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(TRUE);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
