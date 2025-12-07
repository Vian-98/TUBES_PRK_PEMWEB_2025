<?php
// Script untuk populate data testing
require_once __DIR__ . '/../config/database.php';

echo "=== SEEDING DATABASE ===\n\n";

$conn = getConnection();

// Cek apakah data sudah ada
$check = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions");
$row = mysqli_fetch_assoc($check);

if ($row['total'] > 0) {
    echo "Data transaksi sudah ada: {$row['total']} records\n";
    echo "Hapus dulu? (ya/tidak): ";
    // Auto yes untuk testing
    mysqli_query($conn, "DELETE FROM transaction_payments");
    mysqli_query($conn, "DELETE FROM transaction_items");
    mysqli_query($conn, "DELETE FROM transactions");
    mysqli_query($conn, "DELETE FROM reservations");
    echo "Data lama dihapus!\n\n";
}

// Insert transaksi hari ini
echo "Inserting transaksi hari ini...\n";

$today = date('Y-m-d');

// Transaksi 1 - Pagi
$sql1 = "INSERT INTO transactions (kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at) 
         VALUES ('TRX{$today}001', 'Andi Pratama', '081234567895', 225000, 0, 225000, 250000, 25000, 'paid', 2, '{$today} 09:15:00')";
mysqli_query($conn, $sql1);
$trx1_id = mysqli_insert_id($conn);

mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx1_id, 1, 'Servis Ringan', 1, 150000, 150000, '{$today} 09:15:00')");
mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx1_id, 1, 'Oli Mesin 1L Synthetic', 1, 75000, 75000, '{$today} 09:15:00')");
mysqli_query($conn, "INSERT INTO transaction_payments (transaction_id, metode, jumlah, dibayar_pada, created_by, created_at) 
                     VALUES ($trx1_id, 'tunai', 250000, '{$today} 09:20:00', 2, '{$today} 09:20:00')");

echo "✓ Transaksi 1: Rp 225.000\n";

// Transaksi 2 - Siang
$sql2 = "INSERT INTO transactions (kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at) 
         VALUES ('TRX{$today}002', 'Siti Aminah', '081234567894', 370000, 20000, 350000, 350000, 0, 'paid', 2, '{$today} 11:30:00')";
mysqli_query($conn, $sql2);
$trx2_id = mysqli_insert_id($conn);

mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx2_id, 3, 'Tune Up Mesin', 1, 200000, 200000, '{$today} 11:30:00')");
mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx2_id, 5, 'Busi Standar', 4, 25000, 100000, '{$today} 11:30:00')");
mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx2_id, 3, 'Filter Oli Standar', 1, 40000, 40000, '{$today} 11:30:00')");
mysqli_query($conn, "INSERT INTO transaction_payments (transaction_id, metode, jumlah, dibayar_pada, created_by, created_at) 
                     VALUES ($trx2_id, 'qris', 350000, '{$today} 11:35:00', 2, '{$today} 11:35:00')");

echo "✓ Transaksi 2: Rp 350.000\n";

// Transaksi 3 - Sore
$sql3 = "INSERT INTO transactions (kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at) 
         VALUES ('TRX{$today}003', 'Budi Setiawan', '081234567893', 180000, 0, 180000, 200000, 20000, 'paid', 3, '{$today} 14:45:00')";
mysqli_query($conn, $sql3);
$trx3_id = mysqli_insert_id($conn);

mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx3_id, 1, 'Servis Ringan', 1, 150000, 150000, '{$today} 14:45:00')");
mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, part_id, nama_item, qty, harga_unit, subtotal, created_at) 
                     VALUES ($trx3_id, 4, 'Filter Udara', 1, 50000, 50000, '{$today} 14:45:00')");
mysqli_query($conn, "INSERT INTO transaction_payments (transaction_id, metode, jumlah, dibayar_pada, created_by, created_at) 
                     VALUES ($trx3_id, 'tunai', 200000, '{$today} 14:50:00', 3, '{$today} 14:50:00')");

echo "✓ Transaksi 3: Rp 180.000\n";

// Insert reservasi aktif
echo "\nInserting reservasi aktif...\n";

mysqli_query($conn, "INSERT INTO reservations (kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, tanggal, status, catatan, created_at) 
                     VALUES ('RSV{$today}001', 'Budi Santoso', '08123456789', 'B 1234 XYZ', 'Toyota Avanza', 1, '{$today} 16:00:00', 'booked', 'Servis rutin', NOW())");
mysqli_query($conn, "INSERT INTO reservations (kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, tanggal, status, catatan, created_at) 
                     VALUES ('RSV{$today}002', 'Siti Nurhaliza', '08123456790', 'B 5678 ABC', 'Honda Jazz', 3, '{$today} 17:00:00', 'booked', 'Tune up', NOW())");

echo "✓ 2 Reservasi aktif\n";

mysqli_close($conn);

echo "\n=== SEEDING COMPLETE ===\n";
echo "Total Transaksi Hari Ini: 3\n";
echo "Total Omzet: Rp 755.000\n";
echo "Reservasi Aktif: 2\n";
echo "\nRefresh dashboard sekarang!\n";
