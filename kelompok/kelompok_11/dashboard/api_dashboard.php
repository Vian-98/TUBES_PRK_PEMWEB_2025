<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

header('Content-Type: application/json');

$data = [];

// Total transaksi hari ini
$sqlTrans = "SELECT COUNT(*) as total FROM transactions WHERE DATE(created_at) = CURDATE() AND status = 'paid'";
$resultTrans = fetchOne($sqlTrans);
$data['total_transaksi'] = $resultTrans['total'] ?? 0;

// Omzet hari ini
$sqlOmzet = "SELECT COALESCE(SUM(grand_total), 0) as omzet FROM transactions WHERE DATE(created_at) = CURDATE() AND status = 'paid'";
$resultOmzet = fetchOne($sqlOmzet);
$data['omzet_hari_ini'] = $resultOmzet['omzet'] ?? 0;

// Reservasi aktif
$sqlRes = "SELECT COUNT(*) as total FROM reservations WHERE status IN ('booked', 'in_progress')";
$resultRes = fetchOne($sqlRes);
$data['reservasi_aktif'] = $resultRes['total'] ?? 0;

// Low stock items
$sqlLowStock = "SELECT p.nama, p.stok, p.min_stok 
                FROM parts p 
                WHERE p.stok <= p.min_stok 
                ORDER BY p.stok ASC 
                LIMIT 5";
$data['low_stock'] = fetchAll($sqlLowStock);

// Top selling parts
$sqlTopParts = "SELECT p.nama, SUM(ti.qty) as total_qty 
                FROM transaction_items ti 
                JOIN parts p ON ti.part_id = p.id 
                WHERE ti.part_id IS NOT NULL 
                GROUP BY p.id 
                ORDER BY total_qty DESC 
                LIMIT 5";
$data['top_parts'] = fetchAll($sqlTopParts);

// Recent transactions
$sqlRecent = "SELECT kode, pelanggan_nama, grand_total, status, created_at 
              FROM transactions 
              ORDER BY created_at DESC 
              LIMIT 5";
$data['recent_transactions'] = fetchAll($sqlRecent);

echo json_encode([
    'success' => true,
    'data' => $data
]);
