<?php
// Script untuk populate data testing
require_once __DIR__ . '/../config/database.php';

echo "=== SEEDING DATABASE ===\n\n";

$conn = getConnection();

/*
|--------------------------------------------------------------------------
| 1. MATIKAN FOREIGN KEY CHECKS
|--------------------------------------------------------------------------
*/
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

echo "=== CLEAN DATABASE ===\n\n";

$tables = [
    "audit_logs",
    "transaction_payments",
    "transaction_items",
    "transactions",
    "reservation_checkins",
    "reservations",
    "stock_movements",
    "parts",
    "services",
    "suppliers",
    "users",
    "roles"
];

// Hapus semua data
foreach ($tables as $t) {
    mysqli_query($conn, "DELETE FROM `$t`");
    mysqli_query($conn, "ALTER TABLE `$t` AUTO_INCREMENT = 1");
    echo "Cleared: $t\n";
}

/*
|--------------------------------------------------------------------------
| 2. HIDUPKAN FOREIGN KEY CHECKS
|--------------------------------------------------------------------------
*/
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== DATABASE CLEARED ===\n\n";

/*
|--------------------------------------------------------------------------
| 3. INSERT ROLES
|--------------------------------------------------------------------------
*/
echo "Inserting Roles...\n";

mysqli_query($conn, "
INSERT INTO roles (nama, deskripsi, created_at) VALUES
('Admin', 'Administrator sistem', NOW()),
('Kasir', 'Operator kasir POS', NOW()),
('Mekanik', 'Teknisi bengkel', NOW()),
('Manager', 'Manager bengkel', NOW())
");

echo "Roles inserted.\n\n";

/*
|--------------------------------------------------------------------------
| 4. INSERT USERS
|--------------------------------------------------------------------------
*/
echo "Inserting Users...\n";

$pw = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

mysqli_query($conn, "
INSERT INTO users (nama, email, password, role_id, telepon, aktif, created_at) VALUES
('Admin System', 'admin@bengkel.com', '$pw', 1, '081234567890', 1, NOW()),
('Kasir Satu', 'kasir1@bengkel.com', '$pw', 2, '081234567891', 1, NOW()),
('Kasir Dua', 'kasir2@bengkel.com', '$pw', 2, '081234567892', 1, NOW()),
('Mekanik Joko', 'joko@bengkel.com', '$pw', 3, '081234567893', 1, NOW()),
('Manager Budi', 'manager@bengkel.com', '$pw', 4, '081234567894', 1, NOW())
");

echo "Users inserted.\n\n";

/*
|--------------------------------------------------------------------------
| 5. INSERT SUPPLIERS
|--------------------------------------------------------------------------
*/
echo "Inserting Suppliers...\n";

mysqli_query($conn, "
INSERT INTO suppliers (nama, kontak, telepon, alamat, created_at) VALUES
('PT Astra Otoparts', 'Bapak Hendra', '021-5551234', 'Jakarta Pusat', NOW()),
('CV Maju Jaya', 'Ibu Siti', '021-5551235', 'Tangerang', NOW()),
('Toko Sparepart Makmur', 'Bapak Anton', '021-5551236', 'Bekasi', NOW())
");

echo "Suppliers inserted.\n\n";

/*
|--------------------------------------------------------------------------
| 6. INSERT SERVICES
|--------------------------------------------------------------------------
*/
echo "Inserting Services...\n";

mysqli_query($conn, "
INSERT INTO services (nama, kode, deskripsi, harga, durasi_menit, created_at) VALUES
('Servis Ringan', 'SRV001', 'Ganti oli mesin, cek rem, cek filter', 150000, 60, NOW()),
('Servis Berat', 'SRV002', 'Overhaul mesin lengkap', 500000, 180, NOW()),
('Tune Up Mesin', 'SRV003', 'Tune up + setting mesin', 200000, 90, NOW()),
('Ganti Kampas Rem', 'SRV004', 'Penggantian kampas rem', 120000, 45, NOW())
");

echo "Services inserted.\n\n";

/*
|--------------------------------------------------------------------------
| 7. INSERT PARTS (ID FIXED!)
|--------------------------------------------------------------------------
*/
echo "Inserting Parts...\n";

mysqli_query($conn, "
INSERT INTO parts (nama, sku, harga_beli, harga_jual, stok, min_stok, supplier_id, created_at) VALUES
('Oli Mesin 1L Synthetic', 'PRT001', 50000, 75000, 50, 10, 1, NOW()),   -- ID 1
('Filter Oli Standar', 'PRT003', 25000, 40000, 30, 10, 1, NOW()),       -- ID 2
('Busi Standar', 'PRT005', 15000, 25000, 50, 10, 2, NOW()),             -- ID 3
('Filter Udara', 'PRT004', 30000, 50000, 25, 8, 1, NOW())               -- ID 4
");

echo "Parts inserted.\n\n";

/*
|--------------------------------------------------------------------------
| 8. INSERT SAMPLE TRANSACTIONS
|--------------------------------------------------------------------------
*/
echo "Inserting Sample Transactions...\n";

$today = date('Y-m-d');

/* =============================
   TRANSAKSI 1 (AMAN)
============================= */

mysqli_query($conn, "
INSERT INTO transactions
(kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at)
VALUES ('TRX{$today}001', 'Andi Pratama', '081234567895', 225000, 0, 225000, 250000, 25000, 'paid', 2, '{$today} 09:15:00')
");
$trx1 = mysqli_insert_id($conn);

mysqli_query($conn, "
INSERT INTO transaction_items 
(transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx1, 1, 'Servis Ringan', 1, 150000, 150000, '{$today} 09:15:00')
");

mysqli_query($conn, "
INSERT INTO transaction_items
(transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx1, 1, 'Oli Mesin 1L Synthetic', 1, 75000, 75000, '{$today} 09:15:00')
");

mysqli_query($conn, "
INSERT INTO transaction_payments 
(transaction_id, metode, jumlah, dibayar_pada, created_by, created_at)
VALUES ($trx1, 'tunai', 250000, '{$today} 09:20:00', 2, '{$today} 09:20:00')
");

echo "✓ Transaksi 1 inserted.\n";

/* =============================
   TRANSAKSI 2 (DIPERBAIKI)
============================= */

mysqli_query($conn, "
INSERT INTO transactions
(kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at)
VALUES ('TRX{$today}002', 'Siti Aminah', '081234567894', 370000, 20000, 350000, 350000, 0, 'paid', 2, '{$today} 11:30:00')
");
$trx2 = mysqli_insert_id($conn);

mysqli_query($conn, "
INSERT INTO transaction_items 
(transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx2, 3, 'Tune Up Mesin', 1, 200000, 200000, '{$today} 11:30:00')
");

mysqli_query($conn, "
INSERT INTO transaction_items
(transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx2, 3, 'Busi Standar', 4, 25000, 100000, '{$today} 11:30:00')
");

mysqli_query($conn, "
INSERT INTO transaction_items
(transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx2, 2, 'Filter Oli Standar', 1, 40000, 40000, '{$today} 11:30:00')
");

mysqli_query($conn, "
INSERT INTO transaction_payments 
(transaction_id, metode, jumlah, dibayar_pada, created_by, created_at)
VALUES ($trx2, 'qris', 350000, '{$today} 11:35:00', 2, '{$today} 11:35:00')
");

echo "✓ Transaksi 2 inserted.\n";

/* =============================
   TRANSAKSI 3 (DIPERBAIKI)
============================= */

mysqli_query($conn, "
INSERT INTO transactions
(kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at)
VALUES ('TRX{$today}003', 'Budi Setiawan', '081234567893', 180000, 0, 180000, 200000, 20000, 'paid', 3, '{$today} 14:45:00')
");
$trx3 = mysqli_insert_id($conn);

mysqli_query($conn, "
INSERT INTO transaction_items 
(transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx3, 1, 'Servis Ringan', 1, 150000, 150000, '{$today} 14:45:00')
");

mysqli_query($conn, "
INSERT INTO transaction_items
(transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at)
VALUES ($trx3, 4, 'Filter Udara', 1, 50000, 50000, '{$today} 14:45:00')
");

mysqli_query($conn, "
INSERT INTO transaction_payments 
(transaction_id, metode, jumlah, dibayar_pada, created_by, created_at)
VALUES ($trx3, 'tunai', 200000, '{$today} 14:50:00', 3, '{$today} 14:50:00')
");

echo "✓ Transaksi 3 inserted.\n";

/*
|--------------------------------------------------------------------------
| 9. INSERT SAMPLE RESERVATIONS
|--------------------------------------------------------------------------
*/

echo "\nInserting sample reservations...\n";

mysqli_query($conn, "
INSERT INTO reservations 
(kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, tanggal, status, catatan, created_at)
VALUES ('RSV{$today}001', 'Budi Santoso', '08123456789', 'B 1234 XYZ', 'Avanza', 1, '{$today} 16:00:00', 'booked', 'Servis rutin', NOW())
");

mysqli_query($conn, "
INSERT INTO reservations 
(kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, tanggal, status, catatan, created_at)
VALUES ('RSV{$today}002', 'Siti Nurhaliza', '08123456790', 'B 5678 ABC', 'Honda Jazz', 3, '{$today} 17:00:00', 'booked', 'Tune up', NOW())
");

echo "✓ 2 Reservations inserted.\n";

echo "\n=== SEEDING COMPLETE ===\n";
echo "Total Transaksi Hari Ini: 3\n";
echo "Reservasi Aktif: 2\n";
echo "Refresh dashboard sekarang!\n\n";

