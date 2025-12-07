<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
$conn = getConnection(); // FIX: Initialize connection

$method = $_SERVER['REQUEST_METHOD'];

// Ambil parameter action
$action = isset($_GET['action']) ? $_GET['action'] : 'summary';

// Response data
$response = [
    'status' => 'success',
    'data' => null
];

try {
    switch ($action) {
        
        // ========================================
        // SUMMARY HARI INI
        // ========================================
        case 'summary':
            $today = date('Y-m-d');
            
            // Total transaksi hari ini
            $query_total_trx = "SELECT COUNT(*) as total FROM transactions 
                                WHERE DATE(created_at) = ? AND status = 'paid'";
            $stmt_total = $conn->prepare($query_total_trx);
            $stmt_total->bind_param("s", $today);
            $stmt_total->execute();
            $result_total = $stmt_total->get_result();
            $total_transaksi = $result_total->fetch_assoc()['total'];
            
            // Total omzet hari ini
            $query_omzet = "SELECT COALESCE(SUM(grand_total), 0) as omzet FROM transactions 
                           WHERE DATE(created_at) = ? AND status = 'paid'";
            $stmt_omzet = $conn->prepare($query_omzet);
            $stmt_omzet->bind_param("s", $today);
            $stmt_omzet->execute();
            $result_omzet = $stmt_omzet->get_result();
            $omzet_hari_ini = $result_omzet->fetch_assoc()['omzet'];
            
            // Total item terjual hari ini
            $query_items = "SELECT COALESCE(SUM(ti.qty), 0) as total_items 
                           FROM transaction_items ti
                           JOIN transactions t ON ti.transaction_id = t.id
                           WHERE DATE(t.created_at) = ? AND t.status = 'paid'";
            $stmt_items = $conn->prepare($query_items);
            $stmt_items->bind_param("s", $today);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            $total_items = $result_items->fetch_assoc()['total_items'];
            
            $response['data'] = [
                'tanggal' => $today,
                'total_transaksi' => intval($total_transaksi),
                'omzet_hari_ini' => floatval($omzet_hari_ini),
                'total_items_terjual' => intval($total_items)
            ];
            break;
        
        // ========================================
        // TRANSAKSI TERAKHIR
        // ========================================
        case 'recent':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            
            $query_recent = "SELECT t.*, u.nama as kasir_nama 
                            FROM transactions t
                            LEFT JOIN users u ON t.kasir_id = u.id
                            WHERE t.status = 'paid'
                            ORDER BY t.created_at DESC
                            LIMIT ?";
            $stmt_recent = $conn->prepare($query_recent);
            $stmt_recent->bind_param("i", $limit);
            $stmt_recent->execute();
            $result_recent = $stmt_recent->get_result();
            
            $transaksi_list = [];
            while ($row = $result_recent->fetch_assoc()) {
                $transaksi_list[] = [
                    'id' => $row['id'],
                    'kode' => $row['kode'],
                    'pelanggan_nama' => $row['pelanggan_nama'],
                    'grand_total' => floatval($row['grand_total']),
                    'kasir_nama' => $row['kasir_nama'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $response['data'] = $transaksi_list;
            break;
        
        // ========================================
        // ITEM TERLARIS
        // ========================================
        case 'top_items':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $period = isset($_GET['period']) ? $_GET['period'] : 'today'; // today, week, month, all
            
            $date_condition = "";
            switch ($period) {
                case 'today':
                    $date_condition = "AND DATE(t.created_at) = CURDATE()";
                    break;
                case 'week':
                    $date_condition = "AND YEARWEEK(t.created_at) = YEARWEEK(NOW())";
                    break;
                case 'month':
                    $date_condition = "AND YEAR(t.created_at) = YEAR(NOW()) AND MONTH(t.created_at) = MONTH(NOW())";
                    break;
                default:
                    $date_condition = "";
            }
            
            $query_top = "SELECT 
                            ti.nama_item,
                            SUM(ti.qty) as total_qty,
                            SUM(ti.subtotal) as total_revenue,
                            CASE 
                                WHEN ti.service_id IS NOT NULL THEN 'Layanan'
                                WHEN ti.part_id IS NOT NULL THEN 'Sparepart'
                                ELSE 'Unknown'
                            END as tipe
                          FROM transaction_items ti
                          JOIN transactions t ON ti.transaction_id = t.id
                          WHERE t.status = 'paid' {$date_condition}
                          GROUP BY ti.nama_item, tipe
                          ORDER BY total_qty DESC
                          LIMIT ?";
            
            $stmt_top = $conn->prepare($query_top);
            $stmt_top->bind_param("i", $limit);
            $stmt_top->execute();
            $result_top = $stmt_top->get_result();
            
            $top_items = [];
            while ($row = $result_top->fetch_assoc()) {
                $top_items[] = [
                    'nama_item' => $row['nama_item'],
                    'tipe' => $row['tipe'],
                    'total_qty' => intval($row['total_qty']),
                    'total_revenue' => floatval($row['total_revenue'])
                ];
            }
            
            $response['data'] = $top_items;
            break;
        
        // ========================================
        // STATISTIK BULANAN
        // ========================================
        case 'monthly_stats':
            $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
            
            $query_monthly = "SELECT 
                                MONTH(created_at) as bulan,
                                COUNT(*) as total_transaksi,
                                SUM(grand_total) as omzet
                              FROM transactions
                              WHERE YEAR(created_at) = ? AND status = 'paid'
                              GROUP BY MONTH(created_at)
                              ORDER BY bulan ASC";
            
            $stmt_monthly = $conn->prepare($query_monthly);
            $stmt_monthly->bind_param("i", $year);
            $stmt_monthly->execute();
            $result_monthly = $stmt_monthly->get_result();
            
            $monthly_data = [];
            while ($row = $result_monthly->fetch_assoc()) {
                $monthly_data[] = [
                    'bulan' => intval($row['bulan']),
                    'nama_bulan' => date('F', mktime(0, 0, 0, $row['bulan'], 1)),
                    'total_transaksi' => intval($row['total_transaksi']),
                    'omzet' => floatval($row['omzet'])
                ];
            }
            
            $response['data'] = [
                'year' => $year,
                'data' => $monthly_data
            ];
            break;
        
        // ========================================
        // METODE PEMBAYARAN
        // ========================================
        case 'payment_methods':
            $period = isset($_GET['period']) ? $_GET['period'] : 'today';
            
            $date_condition = "";
            switch ($period) {
                case 'today':
                    $date_condition = "AND DATE(t.created_at) = CURDATE()";
                    break;
                case 'week':
                    $date_condition = "AND YEARWEEK(t.created_at) = YEARWEEK(NOW())";
                    break;
                case 'month':
                    $date_condition = "AND YEAR(t.created_at) = YEAR(NOW()) AND MONTH(t.created_at) = MONTH(NOW())";
                    break;
            }
            
            $query_payment = "SELECT 
                                tp.metode,
                                COUNT(*) as jumlah_transaksi,
                                SUM(tp.jumlah) as total_jumlah
                              FROM transaction_payments tp
                              JOIN transactions t ON tp.transaction_id = t.id
                              WHERE t.status = 'paid' {$date_condition}
                              GROUP BY tp.metode
                              ORDER BY jumlah_transaksi DESC";
            
            $result_payment = $conn->query($query_payment);
            
            $payment_data = [];
            while ($row = $result_payment->fetch_assoc()) {
                $payment_data[] = [
                    'metode' => $row['metode'],
                    'jumlah_transaksi' => intval($row['jumlah_transaksi']),
                    'total_jumlah' => floatval($row['total_jumlah'])
                ];
            }
            
            $response['data'] = $payment_data;
            break;
        
        // ========================================
        // DETAIL TRANSAKSI
        // ========================================
        case 'detail':
            $transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($transaction_id === 0) {
                throw new Exception('ID transaksi tidak valid');
            }
            
            // Data transaksi
            $query_trx = "SELECT t.*, u.nama as kasir_nama 
                         FROM transactions t
                         LEFT JOIN users u ON t.kasir_id = u.id
                         WHERE t.id = ?";
            $stmt_trx = $conn->prepare($query_trx);
            $stmt_trx->bind_param("i", $transaction_id);
            $stmt_trx->execute();
            $result_trx = $stmt_trx->get_result();
            $transaksi = $result_trx->fetch_assoc();
            
            if (!$transaksi) {
                throw new Exception('Transaksi tidak ditemukan');
            }
            
            // Items
            $query_items = "SELECT * FROM transaction_items WHERE transaction_id = ?";
            $stmt_items = $conn->prepare($query_items);
            $stmt_items->bind_param("i", $transaction_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            
            $items = [];
            while ($row = $result_items->fetch_assoc()) {
                $items[] = [
                    'nama_item' => $row['nama_item'],
                    'qty' => intval($row['qty']),
                    'harga_unit' => floatval($row['harga_unit']),
                    'subtotal' => floatval($row['subtotal']),
                    'tipe' => $row['service_id'] ? 'service' : 'part'
                ];
            }
            
            // Payment
            $query_payment = "SELECT * FROM transaction_payments WHERE transaction_id = ?";
            $stmt_payment = $conn->prepare($query_payment);
            $stmt_payment->bind_param("i", $transaction_id);
            $stmt_payment->execute();
            $result_payment = $stmt_payment->get_result();
            $payment = $result_payment->fetch_assoc();
            
            $response['data'] = [
                'transaksi' => [
                    'id' => $transaksi['id'],
                    'kode' => $transaksi['kode'],
                    'pelanggan_nama' => $transaksi['pelanggan_nama'],
                    'pelanggan_telepon' => $transaksi['pelanggan_telepon'],
                    'total' => floatval($transaksi['total']),
                    'diskon' => floatval($transaksi['diskon']),
                    'grand_total' => floatval($transaksi['grand_total']),
                    'bayar' => floatval($transaksi['bayar']),
                    'kembali' => floatval($transaksi['kembali']),
                    'status' => $transaksi['status'],
                    'kasir_nama' => $transaksi['kasir_nama'],
                    'created_at' => $transaksi['created_at']
                ],
                'items' => $items,
                'payment' => [
                    'metode' => $payment['metode'],
                    'jumlah' => floatval($payment['jumlah'])
                ]
            ];
            break;
        
        // ========================================
        // DEFAULT - ERROR
        // ========================================
        default:
            throw new Exception('Action tidak valid');
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    $response['data'] = null;
}

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>