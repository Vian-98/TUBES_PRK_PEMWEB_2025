<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Koneksi database
require_once '../config/database.php';
$conn = getConnection(); // FIX: Initialize connection

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Ambil data dari form
$items = json_decode($_POST['items'], true);
$nama_pelanggan = $_POST['nama_pelanggan'];
$telepon_pelanggan = $_POST['telepon_pelanggan'] ?? null;
$total = floatval($_POST['total']);
$diskon = floatval($_POST['diskon']);
$grand_total = floatval($_POST['grand_total']);
$bayar = floatval($_POST['bayar']);
$kembali = floatval($_POST['kembali']);
$metode_pembayaran = $_POST['metode_pembayaran'];
$reservation_id = !empty($_POST['reservation_id']) ? intval($_POST['reservation_id']) : null;
$draft_transaction_id = !empty($_POST['draft_transaction_id']) ? intval($_POST['draft_transaction_id']) : null;
$kasir_id = $_SESSION['user_id'];

// Validasi
if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Items tidak boleh kosong']);
    exit;
}

if (empty($nama_pelanggan)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama pelanggan harus diisi']);
    exit;
}

if ($bayar < $grand_total) {
    echo json_encode(['status' => 'error', 'message' => 'Jumlah bayar kurang']);
    exit;
}

// Mulai transaksi database
$conn->begin_transaction();

try {
    // 1. Generate kode transaksi
    $kode_transaksi = 'TRX' . date('Ymd') . sprintf('%04d', rand(1, 9999));
    
    // Cek apakah ini update draft atau buat baru
    if ($draft_transaction_id) {
        // Update draft transaction
        $stmt = $conn->prepare("UPDATE transactions SET 
                                pelanggan_nama = ?,
                                pelanggan_telepon = ?,
                                total = ?,
                                diskon = ?,
                                grand_total = ?,
                                bayar = ?,
                                kembali = ?,
                                status = 'paid',
                                kasir_id = ?,
                                updated_at = NOW()
                                WHERE id = ?");
        $stmt->bind_param("ssdddddii", 
            $nama_pelanggan, 
            $telepon_pelanggan, 
            $total, 
            $diskon, 
            $grand_total, 
            $bayar, 
            $kembali,
            $kasir_id,
            $draft_transaction_id
        );
        $stmt->execute();
        $transaction_id = $draft_transaction_id;
        
        // Hapus items lama
        $stmt_del = $conn->prepare("DELETE FROM transaction_items WHERE transaction_id = ?");
        $stmt_del->bind_param("i", $transaction_id);
        $stmt_del->execute();
        
        // Ambil kode transaksi yang sudah ada
        $stmt_kode = $conn->prepare("SELECT kode FROM transactions WHERE id = ?");
        $stmt_kode->bind_param("i", $transaction_id);
        $stmt_kode->execute();
        $result_kode = $stmt_kode->get_result();
        $row_kode = $result_kode->fetch_assoc();
        $kode_transaksi = $row_kode['kode'];
        
    } else {
        // Insert transaksi baru
        $stmt = $conn->prepare("INSERT INTO transactions 
                                (kode, reservation_id, pelanggan_nama, pelanggan_telepon, 
                                 total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, NOW(), NOW())");
        $stmt->bind_param("sissdddddi", 
            $kode_transaksi, 
            $reservation_id, 
            $nama_pelanggan, 
            $telepon_pelanggan, 
            $total, 
            $diskon, 
            $grand_total, 
            $bayar, 
            $kembali,
            $kasir_id
        );
        $stmt->execute();
        $transaction_id = $conn->insert_id;
    }
    
    // 2. Insert transaction items
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
        
        $stmt_item->bind_param("iiisidi", 
            $transaction_id, 
            $service_id, 
            $part_id, 
            $nama_item, 
            $qty, 
            $harga_unit, 
            $subtotal
        );
        $stmt_item->execute();
        
        // 3. Update stok jika sparepart
        if ($item['tipe'] === 'part') {
            // Cek stok DULU
            $stmt_cek = $conn->prepare("SELECT stok FROM parts WHERE id = ?");
            $stmt_cek->bind_param("i", $part_id);
            $stmt_cek->execute();
            $result_stok = $stmt_cek->get_result();
            $row_stok = $result_stok->fetch_assoc();

            if ($row_stok['stok'] < $qty) {
                throw new Exception("Stok tidak mencukupi untuk item: " . $nama_item);
            }

            // BARU update stok
            $stmt_stok = $conn->prepare("UPDATE parts SET stok = stok - ? WHERE id = ?");
            $stmt_stok->bind_param("ii", $qty, $part_id);
            $stmt_stok->execute();
            
            // Insert ke stock_movements
            $tipe_movement = 'keluar';
            $keterangan = "Penjualan transaksi: " . $kode_transaksi;
            
            $stmt_movement = $conn->prepare("INSERT INTO stock_movements 
                                             (part_id, tipe, qty, harga_unit, keterangan, created_by, created_at) 
                                             VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt_movement->bind_param("isidsi", 
                $part_id, 
                $tipe_movement, 
                $qty, 
                $harga_unit, 
                $keterangan,
                $kasir_id
            );
            $stmt_movement->execute();
        }
    }
    
    // 4. Insert pembayaran
    $stmt_payment = $conn->prepare("INSERT INTO transaction_payments 
                                     (transaction_id, metode, jumlah, rincian, dibayar_pada, created_by, created_at) 
                                     VALUES (?, ?, ?, NULL, NOW(), ?, NOW())");
    $stmt_payment->bind_param("isdi", 
        $transaction_id, 
        $metode_pembayaran, 
        $bayar,
        $kasir_id
    );
    $stmt_payment->execute();
    
    // 5. Update status reservasi jika ada
    if ($reservation_id) {
        $stmt_res = $conn->prepare("UPDATE reservations SET status = 'completed', updated_at = NOW() WHERE id = ?");
        $stmt_res->bind_param("i", $reservation_id);
        $stmt_res->execute();
    }
    
    // 6. Insert audit log
    $action = "CREATE_TRANSACTION";
    $tabel = "transactions";
    $after_data = json_encode([
        'kode' => $kode_transaksi,
        'grand_total' => $grand_total,
        'metode' => $metode_pembayaran
    ]);
    
    $stmt_audit = $conn->prepare("INSERT INTO audit_logs 
                                   (user_id, action, tabel, record_id, after_data, created_at) 
                                   VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt_audit->bind_param("issis", 
        $kasir_id, 
        $action, 
        $tabel, 
        $transaction_id, 
        $after_data
    );
    $stmt_audit->execute();
    
    // Commit transaksi
    $conn->commit();
    
    // Return success
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaksi berhasil diproses',
        'transaction_id' => $transaction_id,
        'kode_transaksi' => $kode_transaksi
    ]);
    
} catch (Exception $e) {
    // Rollback jika error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>