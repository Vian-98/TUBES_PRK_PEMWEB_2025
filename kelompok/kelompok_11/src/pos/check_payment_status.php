<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$kode_transaksi = $_GET['tx'] ?? '';

if (empty($kode_transaksi)) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction code required']);
    exit;
}

$stmt = $conn->prepare("SELECT t.id, t.kode, t.status, t.grand_total, 
                        tp.dibayar_pada, tp.rincian, tp.created_at as payment_created
                        FROM transactions t
                        LEFT JOIN transaction_payments tp ON tp.transaction_id = t.id AND tp.metode = 'qris'
                        WHERE t.kode = ?");
$stmt->bind_param("s", $kode_transaksi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
}

$transaction = $result->fetch_assoc();
$qris_data = json_decode($transaction['rincian'], true);
$expired_at = $qris_data['expired_at'] ?? null;
$created_time = strtotime($transaction['payment_created']);
$elapsed_time = time() - $created_time;

$payment_status = 'pending';
$is_expired = false;

if (!empty($transaction['dibayar_pada'])) {
    $payment_status = 'paid';
} elseif ($expired_at && strtotime($expired_at) < time()) {
    $payment_status = 'expired';
    $is_expired = true;
}

echo json_encode([
    'status' => 'success',
    'payment_status' => $payment_status,
    'transaction_id' => $transaction['id'],
    'transaction_code' => $transaction['kode'],
    'transaction_status' => $transaction['status'],
    'amount' => $transaction['grand_total'],
    'paid_at' => $transaction['dibayar_pada'],
    'expired_at' => $expired_at,
    'is_expired' => $is_expired,
    'elapsed_time' => $elapsed_time
]);
?>
