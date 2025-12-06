<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

header('Content-Type: application/json');

// Get low stock items
$sql = "SELECT p.id, p.nama, p.sku, p.stok, p.min_stok, s.nama as supplier_nama
        FROM parts p 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        WHERE p.stok <= p.min_stok 
        ORDER BY p.stok ASC 
        LIMIT 10";

$lowStockItems = fetchAll($sql);

echo json_encode([
    'success' => true,
    'data' => $lowStockItems
]);
